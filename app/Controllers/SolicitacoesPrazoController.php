<?php

namespace App\Controllers;

use App\Models\ManifestacaoHistoricoModel;
use App\Models\ManifestacaoModel;
use App\Models\ManifestacaoSolicitacaoPrazoModel;

class SolicitacoesPrazoController extends BaseController
{
    protected $helpers = ['ouvidoria', 'form', 'url'];

    public function index()
    {
        $usuario = obterUsuarioLogado();
        $authService = service('authorization');
        if (!$authService->podeGerenciarSolicitacoesProrrogacao($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->to(url_to('ouvidoria.dashboard'));
        }

        $statusFiltro = (string) ($this->request->getGet('status') ?? 'pendente');
        if (!in_array($statusFiltro, ['', 'pendente', 'aprovada', 'rejeitada'], true)) {
            $statusFiltro = 'pendente';
        }

        $solicitacaoModel = model(ManifestacaoSolicitacaoPrazoModel::class);
        $solicitacoes = $solicitacaoModel->listarDetalhadas($statusFiltro === '' ? null : $statusFiltro);

        try {
            $encryptionService = service('encryption');
            $solicitacoes = array_map(function ($s) use ($encryptionService) {
                $s['id'] = $s['manifestacao_id'] ?? $s['id'] ?? 0;
                return $encryptionService->descriptografarManifestacao($s);
            }, $solicitacoes);
        } catch (\Throwable $e) {
            // Ignora quando criptografia não estiver disponível.
        }

        return view('ouvidoria/solicitacoes_prazo/index', [
            'solicitacoes' => $solicitacoes,
            'statusFiltro' => $statusFiltro,
            'menu_ativo' => 'ouvidoria.solicitacoesPrazo.index',
        ]);
    }

    public function aprovar(int $id)
    {
        $usuario = obterUsuarioLogado();
        $authService = service('authorization');
        if (!$authService->podeGerenciarSolicitacoesProrrogacao($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $diasConcedidos = (int) ($this->request->getPost('dias_concedidos') ?? 0);
        $resposta = trim((string) ($this->request->getPost('resposta_analise') ?? ''));
        if ($diasConcedidos <= 0) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Informe a quantidade de dias concedidos.']));
            return redirect()->back();
        }

        $solicitacaoModel = model(ManifestacaoSolicitacaoPrazoModel::class);
        $manifestacaoModel = model(ManifestacaoModel::class);
        $historicoModel = model(ManifestacaoHistoricoModel::class);

        $solicitacao = $solicitacaoModel->find($id);
        if (!$solicitacao || ($solicitacao['status'] ?? '') !== 'pendente') {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Solicitação não encontrada ou já analisada.']));
            return redirect()->back();
        }

        $manifestacao = $manifestacaoModel->find((int) $solicitacao['manifestacao_id']);
        if (!$manifestacao) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Manifestação vinculada não encontrada.']));
            return redirect()->back();
        }

        $dataBase = $manifestacao['data_limite_sla']
            ?: (($manifestacao['data_manifestacao'] ?? null) ? ($manifestacao['data_manifestacao'] . ' 00:00:00') : ($manifestacao['created_at'] ?? date('Y-m-d H:i:s')));

        $novoPrazo = new \DateTime($dataBase);
        $novoPrazo->modify('+' . $diasConcedidos . ' days');

        $manifestacaoModel->update((int) $manifestacao['id'], [
            'sla_prazo_em_dias' => (int) ($manifestacao['sla_prazo_em_dias'] ?? 0) + $diasConcedidos,
            'data_limite_sla' => $novoPrazo->format('Y-m-d H:i:s'),
        ]);

        $solicitacaoModel->update($id, [
            'status' => 'aprovada',
            'dias_concedidos' => $diasConcedidos,
            'resposta' => $resposta !== '' ? $resposta : null,
            'analisado_por_usuario_id' => (int) ($usuario['id'] ?? 0),
            'analisado_em' => date('Y-m-d H:i:s'),
        ]);

        $historicoModel->registrar((int) $manifestacao['id'], $usuario['id'] ?? null, ManifestacaoHistoricoModel::TIPO_ANALISE_SOLICITACAO_PRORROGACAO_PRAZO, [
            'solicitacao_id' => $id,
            'decisao' => 'aprovada',
            'dias_concedidos' => $diasConcedidos,
            'novo_prazo' => $novoPrazo->format('Y-m-d H:i:s'),
            'resposta' => $resposta,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Solicitação aprovada e prazo da manifestação atualizado.']));
        return redirect()->back();
    }

    public function rejeitar(int $id)
    {
        $usuario = obterUsuarioLogado();
        $authService = service('authorization');
        if (!$authService->podeGerenciarSolicitacoesProrrogacao($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $resposta = trim((string) ($this->request->getPost('resposta_analise') ?? ''));
        if ($resposta === '') {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Informe o motivo da rejeição.']));
            return redirect()->back();
        }

        $solicitacaoModel = model(ManifestacaoSolicitacaoPrazoModel::class);
        $historicoModel = model(ManifestacaoHistoricoModel::class);

        $solicitacao = $solicitacaoModel->find($id);
        if (!$solicitacao || ($solicitacao['status'] ?? '') !== 'pendente') {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Solicitação não encontrada ou já analisada.']));
            return redirect()->back();
        }

        $solicitacaoModel->update($id, [
            'status' => 'rejeitada',
            'dias_concedidos' => null,
            'resposta' => $resposta,
            'analisado_por_usuario_id' => (int) ($usuario['id'] ?? 0),
            'analisado_em' => date('Y-m-d H:i:s'),
        ]);

        $historicoModel->registrar((int) $solicitacao['manifestacao_id'], $usuario['id'] ?? null, ManifestacaoHistoricoModel::TIPO_ANALISE_SOLICITACAO_PRORROGACAO_PRAZO, [
            'solicitacao_id' => $id,
            'decisao' => 'rejeitada',
            'resposta' => $resposta,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Solicitação rejeitada.']));
        return redirect()->back();
    }
}
