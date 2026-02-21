<?php

namespace App\Controllers;

use App\Models\ManifestacaoModel;
use App\Models\ManifestacaoAtribuicaoModel;
use App\Models\ManifestacaoHistoricoModel;
use App\Models\UsuarioModel;

/**
 * Controller de Manifestações.
 * CRUD + encaminhamento, alteração de status, reabertura.
 */
class ManifestacoesController extends BaseController
{
    protected $helpers = ['ouvidoria', 'form', 'url'];

    /**
     * Listagem com filtros.
     */
    public function index()
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return redirect()->to(site_url('login'));
        }

        $manifestacaoModel = model(ManifestacaoModel::class);
        $authService = service('authorization');
        $slaService = service('sla');
        $db = \Config\Database::connect();

        // Filtro por visibilidade (gerente e usuario veem apenas vinculados)
        // Para usuario: mesma lógica do Dashboard (recebidas + devolvidasParaMim + que devolvi com ativo=1)
        if (!in_array($usuario['role'] ?? '', ['administrador', 'ouvidor'])) {
            $usuarioId = (int) $usuario['id'];
            $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
            $temColunaDevolucao = $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes');
            $ids = [];
            if (($usuario['role'] ?? '') === 'usuario' && $temColunaDevolucao) {
                $idsRecebidas = $atribuicaoModel->select('manifestacao_id')
                    ->where('atribuido_para_usuario_id', $usuarioId)
                    ->where('ativo', 1)
                    ->findAll();
                $idsQueDevolvi = $atribuicaoModel->select('manifestacao_id')
                    ->where('atribuido_por_usuario_id', $usuarioId)
                    ->where('e_devolucao', 1)
                    ->where('ativo', 1)
                    ->findAll();
                $ids = array_unique(array_merge(
                    array_column($idsRecebidas, 'manifestacao_id'),
                    array_column($idsQueDevolvi, 'manifestacao_id')
                ));
            } else {
                $ids = $db->table('manifestacao_atribuicoes')
                    ->select('manifestacao_id')
                    ->groupStart()
                        ->where('atribuido_para_usuario_id', $usuarioId)
                        ->orWhere('atribuido_por_usuario_id', $usuarioId)
                    ->groupEnd()
                    ->get()
                    ->getResultArray();
                $ids = array_unique(array_column($ids, 'manifestacao_id'));
            }
            $manifestacaoModel->groupStart()
                ->where('criado_por_usuario_id', $usuarioId)
                ->orWhereIn('id', $ids ?: [0])
            ->groupEnd();
        }

        // Filtros da requisição
        if ($status = $this->request->getGet('status')) {
            $manifestacaoModel->where('status', $status);
        }
        if ($prioridade = $this->request->getGet('prioridade')) {
            $manifestacaoModel->where('prioridade', $prioridade);
        }
        if ($protocolo = $this->request->getGet('protocolo')) {
            $manifestacaoModel->like('protocolo', $protocolo);
        }
        if ($setor = $this->request->getGet('setor')) {
            $manifestacaoModel->join('usuarios u', 'u.id = manifestacoes.criado_por_usuario_id', 'left')
                ->where('u.setor_id', $setor);
        }
        if ($dataInicio = $this->request->getGet('data_inicio')) {
            $manifestacaoModel->groupStart()
                ->where('DATE(COALESCE(manifestacoes.data_manifestacao, manifestacoes.created_at)) >=', $dataInicio)
            ->groupEnd();
        }
        if ($dataFim = $this->request->getGet('data_fim')) {
            $manifestacaoModel->groupStart()
                ->where('DATE(COALESCE(manifestacoes.data_manifestacao, manifestacoes.created_at)) <=', $dataFim)
            ->groupEnd();
        }

        $manifestacoes = $manifestacaoModel->orderBy('COALESCE(manifestacoes.data_manifestacao, manifestacoes.created_at)', 'DESC', false)
            ->limit(500)
            ->findAll();

        // Descriptografa assunto para exibição (quem pode visualizar)
        try {
            $encryptionService = service('encryption');
            $manifestacoes = array_map(function ($m) use ($encryptionService, $authService, $usuario) {
                if ($authService->podeVisualizarManifestacao($usuario ?? [], $m)) {
                    return $encryptionService->descriptografarManifestacao($m);
                }
                return $m;
            }, $manifestacoes);
        } catch (\Throwable $e) {
            // Master key não configurada
        }

        // Status label contextual (Devolvido, Encaminhado, etc.) como no Dashboard
        $idsManif = array_column($manifestacoes, 'id');
        $atribuicoesAtivas = [];
        if ($idsManif && $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes')) {
            $atribuicoesAtivas = model(ManifestacaoAtribuicaoModel::class)
                ->whereIn('manifestacao_id', $idsManif)
                ->where('ativo', 1)
                ->findAll();
        }
        $atribPorManif = [];
        foreach ($atribuicoesAtivas as $a) {
            $atribPorManif[(int) $a['manifestacao_id']] = $a;
        }
        $gerenteIds = [];
        if (($usuario['role'] ?? '') === 'gerente' && $idsManif) {
            $gerenteIds = $db->table('manifestacao_atribuicoes')
                ->select('manifestacao_id')
                ->where('atribuido_por_usuario_id', (int) $usuario['id'])
                ->whereIn('manifestacao_id', $idsManif)
                ->get()
                ->getResultArray();
            $gerenteIds = array_flip(array_column($gerenteIds, 'manifestacao_id'));
        }
        $idsQueDevolvi = [];
        if (($usuario['role'] ?? '') === 'usuario' && $idsManif && $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes')) {
            $idsQueDevolvi = $db->table('manifestacao_atribuicoes')
                ->select('manifestacao_id')
                ->where('atribuido_por_usuario_id', (int) $usuario['id'])
                ->where('e_devolucao', 1)
                ->whereIn('manifestacao_id', $idsManif)
                ->get()
                ->getResultArray();
            $idsQueDevolvi = array_flip(array_column($idsQueDevolvi, 'manifestacao_id'));
        }
        $role = $usuario['role'] ?? '';
        foreach ($manifestacoes as &$m) {
            $atrib = $atribPorManif[(int) $m['id']] ?? null;
            $eDevolucao = (bool) ($atrib['e_devolucao'] ?? 0);
            $gerenteJaEncaminhou = isset($gerenteIds[(int) $m['id']]);
            if ($role === 'gerente') {
                $m['status_label'] = statusLabelGerente($m, $eDevolucao, $gerenteJaEncaminhou);
            } elseif ($role === 'usuario') {
                $queDevolvi = isset($idsQueDevolvi[(int) $m['id']]);
                $m['status_label'] = statusLabelUsuario($m, $eDevolucao || $queDevolvi);
            } elseif (in_array($role, ['ouvidor', 'administrador'])) {
                $m['status_label'] = $eDevolucao ? 'Devolvido' : statusLabelGerente($m, false, true);
            } else {
                $m['status_label'] = statusLabelGerente($m, $eDevolucao, true);
            }
        }
        unset($m);

        return view('ouvidoria/manifestacoes/index', [
            'manifestacoes' => $manifestacoes,
            'slaService' => $slaService,
            'authService' => $authService,
        ]);
    }

    /**
     * Formulário de criação (somente ouvidor/admin).
     */
    public function create()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeCriarManifestacao($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!', 'text' => 'Você não pode criar manifestações.']));
            return redirect()->back();
        }

        $setorModel = model(\App\Models\SetorModel::class);
        $setores = $setorModel->ativos()->findAll();

        return view('ouvidoria/manifestacoes/create', [
            'setores' => $setores,
        ]);
    }

    /**
     * Salva nova manifestação.
     */
    public function store()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeCriarManifestacao($usuario ?? [])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acesso negado'])->setStatusCode(403);
        }

        $manifestacaoModel = model(ManifestacaoModel::class);
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $slaService = service('sla');

        $regras = [
            'origem' => 'permit_empty|max_length[150]',
            'data_manifestacao' => 'permit_empty|valid_date',
            'assunto' => 'required',
            'descricao' => 'required',
            'prioridade' => 'in_list[baixa,media,alta]',
            'sla_prazo_em_dias' => 'permit_empty|integer|greater_than[0]',
        ];

        if (!$this->validate($regras)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $this->validator->getErrors())]));
            return redirect()->back()->withInput();
        }

        $protocolo = $this->request->getPost('protocolo') ?: $manifestacaoModel->gerarProtocolo();
        $prazoDias = (int) ($this->request->getPost('sla_prazo_em_dias') ?: 30);
        $dataManifestacao = $this->request->getPost('data_manifestacao') ?: date('Y-m-d');
        $dataLimite = $slaService->calcularDataLimite($dataManifestacao . ' 00:00:00', $prazoDias);

        $dados = [
            'protocolo' => $protocolo,
            'protocolo_falabr' => $this->request->getPost('protocolo_falabr'),
            'origem' => $this->request->getPost('origem') ?: 'Fala.BR',
            'data_manifestacao' => $dataManifestacao,
            'assunto' => $this->request->getPost('assunto'),
            'descricao' => $this->request->getPost('descricao'),
            'dados_identificacao' => $this->request->getPost('dados_identificacao') ? (is_array($this->request->getPost('dados_identificacao')) ? json_encode($this->request->getPost('dados_identificacao')) : $this->request->getPost('dados_identificacao')) : null,
            'status' => 'recebida',
            'prioridade' => $this->request->getPost('prioridade') ?: 'media',
            'sla_prazo_em_dias' => $prazoDias,
            'data_limite_sla' => $dataLimite,
            'criado_por_usuario_id' => $usuario['id'],
        ];

        $db = \Config\Database::connect();
        try {
            $encryptionService = service('encryption');
        } catch (\InvalidArgumentException $e) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Configure ouvidoria.encryption.master_key no arquivo .env (mínimo 32 caracteres).']));
            return redirect()->back()->withInput();
        }
        $db->transStart();
        try {
            $manifestacaoId = $manifestacaoModel->insert($dados, true);
            if (!$manifestacaoId) {
                throw new \RuntimeException('Falha ao inserir manifestação');
            }

            $dek = $encryptionService->gerarEArmazenarDEK($manifestacaoId);
            $dadosCripto = $encryptionService->criptografarManifestacao([
                'assunto' => $dados['assunto'],
                'descricao' => $dados['descricao'],
                'dados_identificacao' => $dados['dados_identificacao'],
            ], $dek);

            $manifestacaoModel->update($manifestacaoId, $dadosCripto);

            $historicoModel->registrar($manifestacaoId, $usuario['id'], ManifestacaoHistoricoModel::TIPO_CRIACAO, [
                'protocolo' => $protocolo,
            ]);

            // Upload de anexos
            $this->processarAnexos($manifestacaoId);

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', $e->getMessage());
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Erro ao salvar manifestação.']));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Manifestação cadastrada com sucesso.']));
        return redirect()->to(url_to('ouvidoria.manifestacoes.show', $manifestacaoId));
    }

    /**
     * Detalhes da manifestação.
     */
    public function show(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Manifestação não encontrada.']));
            return redirect()->back();
        }

        if (!service('authorization')->podeAcessarPaginaManifestacao($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!', 'text' => 'Você não tem permissão para visualizar esta manifestação.']));
            return redirect()->back();
        }

        $authService = service('authorization');
        $slaService = service('sla');

        if ($authService->podeVisualizarManifestacao($usuario, $manifestacao)) {
            try {
                $encryptionService = service('encryption');
                $manifestacao = $encryptionService->descriptografarManifestacao($manifestacao);
            } catch (\InvalidArgumentException $e) {
                // Master key não configurada - conteúdo permanece criptografado
            }
        }

        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $anexoModel = model(\App\Models\ManifestacaoAnexoModel::class);
        $usuarioModel = model(UsuarioModel::class);

        $historico = $historicoModel->porManifestacao($id);
        foreach ($historico as &$h) {
            $d = is_string($h['detalhes'] ?? null) ? json_decode($h['detalhes'], true) : ($h['detalhes'] ?? []);
            if (!is_array($d)) $d = [];
            if (($h['tipo_evento'] ?? '') === 'ENCAMINHAMENTO' && empty($d['para_nomes']) && !empty($d['para_usuarios'])) {
                $d['de_nome'] = $d['de_nome'] ?? ($h['usuario_nome'] ?? 'Sistema');
                $d['para_nomes'] = array_map(fn($uid) => $usuarioModel->find($uid)['nome'] ?? "ID {$uid}", $d['para_usuarios']);
                $h['detalhes'] = json_encode($d);
            }
            if (($h['tipo_evento'] ?? '') === 'DEVOLUCAO' && empty($d['para_nome']) && !empty($d['para_usuario_id'])) {
                $d['de_nome'] = $d['de_nome'] ?? ($h['usuario_nome'] ?? 'Sistema');
                $d['para_nome'] = $usuarioModel->find($d['para_usuario_id'])['nome'] ?? 'N/A';
                $h['detalhes'] = json_encode($d);
            }
        }
        unset($h);
        $atribuicoes = $atribuicaoModel->select('manifestacao_atribuicoes.*, u_de.nome as de_nome, u_para.nome as para_nome')
            ->join('usuarios u_de', 'u_de.id = manifestacao_atribuicoes.atribuido_por_usuario_id', 'left')
            ->join('usuarios u_para', 'u_para.id = manifestacao_atribuicoes.atribuido_para_usuario_id', 'left')
            ->where('manifestacao_atribuicoes.manifestacao_id', $id)
            ->orderBy('manifestacao_atribuicoes.criado_em', 'DESC')
            ->findAll();
        $anexos = $anexoModel->porManifestacao($id);

        $todosUsuarios = $usuarioModel->ativos()->orderBy('nome')->findAll();
        $idsJaAtribuidos = array_column(
            $atribuicaoModel->select('atribuido_para_usuario_id')->where('manifestacao_id', $id)->where('ativo', 1)->findAll(),
            'atribuido_para_usuario_id'
        );
        $meuId = (int) ($usuario['id'] ?? 0);
        $usuariosParaEncaminhar = array_values(array_filter($todosUsuarios, fn($u) => (int) $u['id'] !== $meuId && !in_array((int) $u['id'], $idsJaAtribuidos)));

        $atribuicaoDevolver = $authService->obterAtribuicaoParaDevolver($usuario, (int) $manifestacao['id']);
        $podeDevolver = $atribuicaoDevolver !== null;
        $usuarioDevolverPara = ($podeDevolver && $atribuicaoDevolver) ? $usuarioModel->find($atribuicaoDevolver['atribuido_por_usuario_id']) : null;

        $atribuicaoDevolucaoUsuario = null;
        $db = \Config\Database::connect();
        if (($usuario['role'] ?? '') === 'usuario' && $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes')) {
            $atribuicaoDevolucaoUsuario = $atribuicaoModel->select('manifestacao_atribuicoes.*, u_para.nome as para_nome')
                ->join('usuarios u_para', 'u_para.id = manifestacao_atribuicoes.atribuido_para_usuario_id', 'left')
                ->where('manifestacao_atribuicoes.manifestacao_id', $id)
                ->where('manifestacao_atribuicoes.atribuido_por_usuario_id', (int) $usuario['id'])
                ->where('manifestacao_atribuicoes.e_devolucao', 1)
                ->where('manifestacao_atribuicoes.ativo', 1)
                ->orderBy('manifestacao_atribuicoes.criado_em', 'DESC')
                ->first();
        }

        $statusLabelGerente = null;
        $statusLabelOuvidor = null;
        $statusLabelUsuario = null;
        if ($db->fieldExists('e_devolucao', 'manifestacao_atribuicoes')) {
            if (($usuario['role'] ?? '') === 'gerente') {
                $atribAtiva = $atribuicaoModel->where('manifestacao_id', $id)
                    ->where('atribuido_para_usuario_id', $usuario['id'])
                    ->where('ativo', 1)
                    ->first();
                $gerenteJaEncaminhou = $atribuicaoModel->where('manifestacao_id', $id)
                    ->where('atribuido_por_usuario_id', $usuario['id'])
                    ->where('ativo', 1)
                    ->first() !== null;
                $statusLabelGerente = $atribAtiva && ($atribAtiva['e_devolucao'] ?? 0)
                    ? statusLabelGerente($manifestacao, true)
                    : statusLabelGerente($manifestacao, false, $gerenteJaEncaminhou);
            }
            if (($usuario['role'] ?? '') === 'ouvidor' && ($manifestacao['status'] ?? '') === 'respondida') {
                $atribAtiva = $atribuicaoModel->where('manifestacao_id', $id)
                    ->where('atribuido_para_usuario_id', $usuario['id'])
                    ->where('ativo', 1)
                    ->first();
                if ($atribAtiva && ($atribAtiva['e_devolucao'] ?? 0)) {
                    $statusLabelOuvidor = 'Devolvido';
                }
            }
            if (($usuario['role'] ?? '') === 'usuario') {
                $atribAtiva = $atribuicaoModel->where('manifestacao_id', $id)
                    ->where('atribuido_para_usuario_id', $usuario['id'])
                    ->where('ativo', 1)
                    ->first();
                if ($atribAtiva) {
                    $statusLabelUsuario = statusLabelUsuario($manifestacao, (bool) ($atribAtiva['e_devolucao'] ?? 0));
                }
            }
        }

        $podeEditarManifestacao = $authService->podeEditarManifestacao($usuario ?? [], $manifestacao);

        return view('ouvidoria/manifestacoes/show', [
            'manifestacao' => $manifestacao,
            'historico' => $historico,
            'atribuicoes' => $atribuicoes,
            'anexos' => $anexos,
            'usuariosParaEncaminhar' => $usuariosParaEncaminhar,
            'authService' => $authService,
            'slaService' => $slaService,
            'podeDevolver' => $podeDevolver,
            'usuarioDevolverPara' => $usuarioDevolverPara,
            'statusLabelGerente' => $statusLabelGerente,
            'statusLabelOuvidor' => $statusLabelOuvidor,
            'statusLabelUsuario' => $statusLabelUsuario,
            'atribuicaoDevolucaoUsuario' => $atribuicaoDevolucaoUsuario,
            'podeEditarManifestacao' => $podeEditarManifestacao,
        ]);
    }

    /**
     * Formulário de edição da manifestação (somente ouvidor que cadastrou).
     */
    public function edit(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao || !service('authorization')->podeEditarManifestacao($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado ou manifestação não encontrada.']));
            return redirect()->back();
        }

        try {
            $encryptionService = service('encryption');
            $manifestacao = $encryptionService->descriptografarManifestacao($manifestacao);
        } catch (\InvalidArgumentException $e) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Erro ao carregar manifestação.']));
            return redirect()->back();
        }

        $anexoModel = model(\App\Models\ManifestacaoAnexoModel::class);
        $anexos = $anexoModel->porManifestacao($id);

        return view('ouvidoria/manifestacoes/edit', [
            'manifestacao' => $manifestacao,
            'anexos' => $anexos,
        ]);
    }

    /**
     * Atualiza manifestação (somente ouvidor que cadastrou).
     */
    public function update(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao || !service('authorization')->podeEditarManifestacao($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado ou manifestação não encontrada.']));
            return redirect()->back();
        }

        $regras = [
            'protocolo' => 'required|max_length[50]',
            'origem' => 'permit_empty|max_length[150]',
            'data_manifestacao' => 'permit_empty|valid_date',
            'assunto' => 'required',
            'descricao' => 'required',
            'prioridade' => 'in_list[baixa,media,alta]',
            'sla_prazo_em_dias' => 'permit_empty|integer|greater_than[0]',
        ];

        if (!$this->validate($regras)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $this->validator->getErrors())]));
            return redirect()->to(url_to('ouvidoria.manifestacoes.edit', $id))->withInput();
        }

        $slaService = service('sla');
        $prazoDias = (int) ($this->request->getPost('sla_prazo_em_dias') ?: 30);
        $dataManifestacao = $this->request->getPost('data_manifestacao') ?: ($manifestacao['data_manifestacao'] ?? date('Y-m-d'));
        $dataLimite = $slaService->calcularDataLimite($dataManifestacao . ' 00:00:00', $prazoDias);

        $dados = [
            'protocolo' => $this->request->getPost('protocolo'),
            'protocolo_falabr' => $this->request->getPost('protocolo_falabr'),
            'origem' => $this->request->getPost('origem') ?: 'Fala.BR',
            'data_manifestacao' => $dataManifestacao,
            'assunto' => $this->request->getPost('assunto'),
            'descricao' => $this->request->getPost('descricao'),
            'dados_identificacao' => $this->request->getPost('dados_identificacao') ? (is_array($this->request->getPost('dados_identificacao')) ? json_encode($this->request->getPost('dados_identificacao')) : $this->request->getPost('dados_identificacao')) : null,
            'prioridade' => $this->request->getPost('prioridade') ?: 'media',
            'sla_prazo_em_dias' => $prazoDias,
            'data_limite_sla' => $dataLimite,
        ];

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            $encryptionService = service('encryption');
            $dek = $encryptionService->obterDEK($id);
            if (!$dek) {
                throw new \RuntimeException('Chave de criptografia não encontrada.');
            }
            $dadosCripto = $encryptionService->criptografarManifestacao([
                'assunto' => $dados['assunto'],
                'descricao' => $dados['descricao'],
                'dados_identificacao' => $dados['dados_identificacao'],
            ], $dek);

            $manifestacaoModel->update($id, array_merge($dados, $dadosCripto));

            $historicoModel = model(ManifestacaoHistoricoModel::class);
            $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_EDICAO_CAMPOS, []);

            $this->processarAnexos($id);

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', $e->getMessage());
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Erro ao atualizar manifestação.']));
            return redirect()->to(url_to('ouvidoria.manifestacoes.edit', $id))->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Manifestação atualizada com sucesso.']));
        return redirect()->to(url_to('ouvidoria.manifestacoes.show', $id));
    }

    /**
     * Altera status da manifestação.
     */
    public function updateStatus(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao || !service('authorization')->podeAlterarStatus($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado ou manifestação não encontrada.']));
            return redirect()->back();
        }

        $novoStatus = $this->request->getPost('status');
        $statusValidos = ['recebida', 'encaminhada', 'em_atendimento', 'respondida', 'finalizada', 'arquivada'];
        if (!in_array($novoStatus, $statusValidos)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Status inválido.']));
            return redirect()->back();
        }

        $statusAnterior = $manifestacao['status'];
        $historicoModel = model(ManifestacaoHistoricoModel::class);

        $dadosUpdate = ['status' => $novoStatus];
        if (in_array($novoStatus, ['finalizada', 'arquivada'])) {
            $dadosUpdate['data_finalizacao'] = date('Y-m-d H:i:s');
        }

        $manifestacaoModel->update($id, $dadosUpdate);

        $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_ALTERACAO_STATUS, [
            'status_anterior' => $statusAnterior,
            'status_novo' => $novoStatus,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Status atualizado.']));
        return redirect()->back();
    }

    /**
     * Encaminha manifestação para usuários.
     */
    public function encaminhar(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao || !service('authorization')->podeEncaminhar($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $usuariosIds = $this->request->getPost('usuarios');
        if (empty($usuariosIds) || !is_array($usuariosIds)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Selecione pelo menos um usuário.']));
            return redirect()->back();
        }

        $usuariosIds = array_unique(array_map('intval', $usuariosIds));
        $usuariosIds = array_values(array_filter($usuariosIds, fn($uid) => $uid > 0));

        if (empty($usuariosIds)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Selecione pelo menos um usuário válido.']));
            return redirect()->back();
        }

        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);

        $usuariosJaAtribuidos = [];
        foreach ($usuariosIds as $userId) {
            if ($atribuicaoModel->usuarioJaAtribuidoAtivo($id, $userId)) {
                $usuariosJaAtribuidos[] = $userId;
            }
        }
        if (!empty($usuariosJaAtribuidos)) {
            $usuarioModel = model(UsuarioModel::class);
            $nomes = array_map(fn($uid) => $usuarioModel->find($uid)['nome'] ?? "ID {$uid}", $usuariosJaAtribuidos);
            session()->setFlashdata(getMessageFail('toast', [
                'text' => 'Não é possível encaminhar: o(s) usuário(s) ' . implode(', ', $nomes) . ' já possuem esta manifestação atribuída.',
            ]));
            return redirect()->back();
        }
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $usuarioModel = model(UsuarioModel::class);
        $emailService = service('emailOuvidoria');

        $mensagem = $this->request->getPost('mensagem_encaminhamento') ?? '';
        $statusAtual = $manifestacao['status'];

        // Gerente: encaminhar sem marcar = status "encaminhada"; com "marcar respondida" = status "respondida" (ouvidor vê "Devolvido")
        $marcarRespondida = ($usuario['role'] ?? '') === 'gerente' && $this->request->getPost('marcar_respondida') === '1';
        $eDevolucao = $marcarRespondida ? 1 : 0;

        $destinatarios = [];

        foreach ($usuariosIds as $userId) {
            $atribuicaoModel->insert([
                'manifestacao_id' => $id,
                'atribuido_por_usuario_id' => $usuario['id'],
                'atribuido_para_usuario_id' => (int) $userId,
                'mensagem_encaminhamento' => $mensagem,
                'status_no_momento' => $statusAtual,
                'e_devolucao' => $eDevolucao,
                'ativo' => 1,
                'criado_em' => date('Y-m-d H:i:s'),
            ]);

            $dest = $usuarioModel->find((int) $userId);
            if ($dest) {
                $destinatarios[$dest['email']] = $dest['nome'];
            }
        }

        $novoStatus = $marcarRespondida ? 'respondida' : 'encaminhada';

        $manifestacaoModel->update($id, [
            'status' => $novoStatus,
            'data_primeiro_encaminhamento' => $manifestacao['data_primeiro_encaminhamento'] ?: date('Y-m-d H:i:s'),
        ]);

        $paraNomes = array_values($destinatarios);
        $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_ENCAMINHAMENTO, [
            'para_usuarios' => $usuariosIds,
            'de_nome' => $usuario['nome'] ?? $usuarioModel->find($usuario['id'])['nome'] ?? 'Sistema',
            'para_nomes' => $paraNomes,
        ]);

        $emailService->notificarEncaminhamento($destinatarios, $manifestacao['protocolo'], $mensagem, $usuario['nome'] ?? 'Sistema');

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Manifestação encaminhada.']));
        return redirect()->back();
    }

    /**
     * Devolve manifestação para quem encaminhou ao usuário atual.
     */
    public function devolver(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        $authService = service('authorization');
        $atribuicaoDevolver = $authService->obterAtribuicaoParaDevolver($usuario ?? [], (int) ($manifestacao['id'] ?? 0));

        if (!$manifestacao || !$atribuicaoDevolver) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado ou você não pode devolver esta manifestação.']));
            return redirect()->back();
        }

        $usuarioDevolverParaId = (int) $atribuicaoDevolver['atribuido_por_usuario_id'];
        $mensagem = $this->request->getPost('mensagem_devolucao') ?? '';

        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $usuarioModel = model(UsuarioModel::class);
        $emailService = service('emailOuvidoria');

        // Encerra a atribição atual (manifestação deixa de estar com o usuário)
        $atribuicaoModel->update($atribuicaoDevolver['id'], [
            'ativo' => 0,
            'encerrado_em' => date('Y-m-d H:i:s'),
        ]);

        // Cria nova atribuição para quem receberá a devolução (marcada como devolução)
        $atribuicaoModel->insert([
            'manifestacao_id' => $id,
            'atribuido_por_usuario_id' => $usuario['id'],
            'atribuido_para_usuario_id' => $usuarioDevolverParaId,
            'mensagem_encaminhamento' => $mensagem,
            'e_devolucao' => 1,
            'status_no_momento' => $manifestacao['status'],
            'ativo' => 1,
            'criado_em' => date('Y-m-d H:i:s'),
        ]);

        $manifestacaoModel->update($id, [
            'status' => 'encaminhada',
        ]);

        $dest = $usuarioModel->find($usuarioDevolverParaId);
        $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_DEVOLUCAO, [
            'para_usuario_id' => $usuarioDevolverParaId,
            'de_atribuicao_id' => $atribuicaoDevolver['id'],
            'de_nome' => $usuario['nome'] ?? $usuarioModel->find($usuario['id'])['nome'] ?? 'Sistema',
            'para_nome' => $dest['nome'] ?? 'N/A',
        ]);

        if ($dest) {
            $emailService->notificarEncaminhamento(
                [$dest['email'] => $dest['nome']],
                $manifestacao['protocolo'],
                $mensagem,
                $usuario['nome'] ?? 'Sistema'
            );
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Manifestação devolvida com sucesso.']));
        return redirect()->back();
    }

    /**
     * Usuário retoma manifestação que devolveu (volta para suas mãos).
     */
    public function voltar(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);
        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $authService = service('authorization');

        $atribuicaoDevolucaoUsuario = null;
        $db = \Config\Database::connect();
        if (($usuario['role'] ?? '') === 'usuario' && $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes')) {
            $atribuicaoDevolucaoUsuario = $atribuicaoModel
                ->where('manifestacao_id', $id)
                ->where('atribuido_por_usuario_id', (int) $usuario['id'])
                ->where('e_devolucao', 1)
                ->where('ativo', 1)
                ->orderBy('criado_em', 'DESC')
                ->first();
        }

        if (!$manifestacao || !$authService->podeVoltar($usuario ?? [], $manifestacao, $atribuicaoDevolucaoUsuario)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado ou manifestação não pode ser retomada.']));
            return redirect()->back();
        }

        $gerenteId = (int) $atribuicaoDevolucaoUsuario['atribuido_para_usuario_id'];
        $usuarioModel = model(UsuarioModel::class);
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $gerente = $usuarioModel->find($gerenteId);

        // Encerra a atribuição de devolução
        $atribuicaoModel->update($atribuicaoDevolucaoUsuario['id'], [
            'ativo' => 0,
            'encerrado_em' => date('Y-m-d H:i:s'),
        ]);

        // Cria nova atribuição: gerente "devolve" de volta para o usuário
        $atribuicaoModel->insert([
            'manifestacao_id' => $id,
            'atribuido_por_usuario_id' => $gerenteId,
            'atribuido_para_usuario_id' => $usuario['id'],
            'mensagem_encaminhamento' => '',
            'e_devolucao' => 0,
            'status_no_momento' => $manifestacao['status'],
            'ativo' => 1,
            'criado_em' => date('Y-m-d H:i:s'),
        ]);

        $manifestacaoModel->update($id, [
            'status' => 'em_atendimento',
        ]);

        $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_VOLTA, [
            'de_nome' => $gerente['nome'] ?? 'Sistema',
            'para_nome' => $usuario['nome'] ?? ($usuarioModel->find($usuario['id'])['nome'] ?? 'Sistema'),
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Manifestação retomada com sucesso.']));
        return redirect()->back();
    }

    /**
     * Reabre manifestação finalizada/arquivada.
     */
    public function reabrir(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao || !service('authorization')->podeReabrir($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado ou manifestação não pode ser reaberta.']));
            return redirect()->back();
        }

        $novoStatus = $this->request->getPost('novo_status') ?: 'em_atendimento';

        $manifestacaoModel->update($id, [
            'status' => $novoStatus,
            'data_finalizacao' => null,
        ]);

        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_REABERTURA, [
            'status_anterior' => $manifestacao['status'],
            'status_novo' => $novoStatus,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Manifestação reaberta.']));
        return redirect()->back();
    }

    /**
     * Adiciona comentário ao histórico.
     */
    public function comentar(int $id)
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($id);

        if (!$manifestacao || !service('authorization')->podeComentar($usuario ?? [], $manifestacao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $comentario = $this->request->getPost('comentario');
        if (empty($comentario) || trim(strip_tags($comentario)) === '') {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Informe o comentário.']));
            return redirect()->back();
        }

        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $historicoModel->registrar($id, $usuario['id'], ManifestacaoHistoricoModel::TIPO_COMENTARIO, [
            'comentario' => $comentario,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Comentário registrado.']));
        return redirect()->back();
    }

    /**
     * Edita um comentário existente.
     */
    public function editarComentario(int $historicoId)
    {
        $usuario = obterUsuarioLogado();
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $registro = $historicoModel->find($historicoId);

        if (!$registro || ($registro['tipo_evento'] ?? '') !== ManifestacaoHistoricoModel::TIPO_COMENTARIO) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Comentário não encontrado.']));
            return redirect()->back();
        }

        $manifestacaoId = (int) $registro['manifestacao_id'];
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($manifestacaoId);

        if (!$manifestacao || !service('authorization')->podeEditarExcluirComentario($usuario ?? [], $manifestacao, $registro)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $detalhes = is_string($registro['detalhes'] ?? null) ? json_decode($registro['detalhes'], true) : ($registro['detalhes'] ?? []);
        if (($detalhes['excluido'] ?? false)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Comentário excluído não pode ser editado.']));
            return redirect()->back();
        }

        $comentarioNovo = $this->request->getPost('comentario');
        if (empty($comentarioNovo) || trim(strip_tags($comentarioNovo)) === '') {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Informe o comentário.']));
            return redirect()->back();
        }

        $comentarioAnterior = $detalhes['comentario'] ?? '';

        $detalhes['comentario'] = $comentarioNovo;
        $historicoModel->update($historicoId, [
            'detalhes' => json_encode($detalhes),
        ]);

        $historicoModel->registrar($manifestacaoId, $usuario['id'], ManifestacaoHistoricoModel::TIPO_COMENTARIO_EDITADO, [
            'comentario' => $comentarioNovo,
            'comentario_anterior' => $comentarioAnterior,
            'comentario_historico_id' => $historicoId,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Comentário atualizado.']));
        return redirect()->back();
    }

    /**
     * Exclui um comentário (marca como excluído, preserva no histórico).
     */
    public function excluirComentario(int $historicoId)
    {
        $usuario = obterUsuarioLogado();
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $registro = $historicoModel->find($historicoId);

        if (!$registro || ($registro['tipo_evento'] ?? '') !== ManifestacaoHistoricoModel::TIPO_COMENTARIO) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Comentário não encontrado.']));
            return redirect()->back();
        }

        $manifestacaoId = (int) $registro['manifestacao_id'];
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($manifestacaoId);

        if (!$manifestacao || !service('authorization')->podeEditarExcluirComentario($usuario ?? [], $manifestacao, $registro)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $detalhes = is_string($registro['detalhes'] ?? null) ? json_decode($registro['detalhes'], true) : ($registro['detalhes'] ?? []);
        if (($detalhes['excluido'] ?? false)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Comentário já excluído.']));
            return redirect()->back();
        }

        $comentario = $detalhes['comentario'] ?? '';
        $detalhes['excluido'] = true;
        $detalhes['excluido_em'] = date('Y-m-d H:i:s');
        $detalhes['excluido_por_usuario_id'] = $usuario['id'];

        $historicoModel->update($historicoId, [
            'detalhes' => json_encode($detalhes),
        ]);

        $historicoModel->registrar($manifestacaoId, $usuario['id'], ManifestacaoHistoricoModel::TIPO_COMENTARIO_EXCLUIDO, [
            'comentario' => $comentario,
            'comentario_historico_id' => $historicoId,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Comentário excluído.']));
        return redirect()->back();
    }

    /**
     * Edita mensagem de uma atribuição.
     */
    public function editarAtribuicao(int $atribuicaoId)
    {
        $usuario = obterUsuarioLogado();
        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $atribuicao = $atribuicaoModel->find($atribuicaoId);

        if (!$atribuicao) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Atribuição não encontrada.']));
            return redirect()->back();
        }

        $manifestacaoId = (int) $atribuicao['manifestacao_id'];
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($manifestacaoId);

        if (!$manifestacao || !service('authorization')->podeEditarExcluirAtribuicao($usuario ?? [], $manifestacao, $atribuicao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        $mensagemNova = $this->request->getPost('mensagem_encaminhamento');
        $mensagemAnterior = $atribuicao['mensagem_encaminhamento'] ?? '';

        $atribuicaoModel->update($atribuicaoId, [
            'mensagem_encaminhamento' => $mensagemNova,
        ]);

        $usuarioModel = model(UsuarioModel::class);
        $deNome = $usuarioModel->find($atribuicao['atribuido_por_usuario_id'])['nome'] ?? 'N/A';
        $paraNome = $usuarioModel->find($atribuicao['atribuido_para_usuario_id'])['nome'] ?? 'N/A';

        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $historicoModel->registrar($manifestacaoId, $usuario['id'], ManifestacaoHistoricoModel::TIPO_ATRIBUICAO_EDITADA, [
            'atribuicao_id' => $atribuicaoId,
            'de_nome' => $deNome,
            'para_nome' => $paraNome,
            'mensagem_anterior' => $mensagemAnterior,
            'mensagem' => $mensagemNova,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Atribuição atualizada.']));
        return redirect()->back();
    }

    /**
     * Exclui uma atribuição (encerra, marca ativo=0).
     */
    public function excluirAtribuicao(int $atribuicaoId)
    {
        $usuario = obterUsuarioLogado();
        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $atribuicao = $atribuicaoModel->find($atribuicaoId);

        if (!$atribuicao) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Atribuição não encontrada.']));
            return redirect()->back();
        }

        $manifestacaoId = (int) $atribuicao['manifestacao_id'];
        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($manifestacaoId);

        if (!$manifestacao || !service('authorization')->podeEditarExcluirAtribuicao($usuario ?? [], $manifestacao, $atribuicao)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Acesso negado.']));
            return redirect()->back();
        }

        if (!$atribuicao['ativo']) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Atribuição já encerrada.']));
            return redirect()->back();
        }

        $usuarioModel = model(UsuarioModel::class);
        $deNome = $usuarioModel->find($atribuicao['atribuido_por_usuario_id'])['nome'] ?? 'N/A';
        $paraNome = $usuarioModel->find($atribuicao['atribuido_para_usuario_id'])['nome'] ?? 'N/A';
        $mensagem = $atribuicao['mensagem_encaminhamento'] ?? '';

        $atribuicaoModel->update($atribuicaoId, [
            'ativo' => 0,
            'encerrado_em' => date('Y-m-d H:i:s'),
        ]);

        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $historicoModel->registrar($manifestacaoId, $usuario['id'], ManifestacaoHistoricoModel::TIPO_ATRIBUICAO_EXCLUIDA, [
            'atribuicao_id' => $atribuicaoId,
            'de_nome' => $deNome,
            'para_nome' => $paraNome,
            'mensagem' => $mensagem,
        ]);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Atribuição excluída.']));
        return redirect()->back();
    }

    /**
     * Processa upload de anexos na criação.
     */
    private function processarAnexos(int $manifestacaoId): void
    {
        $arquivos = $this->request->getFileMultiple('anexos');
        if (empty($arquivos)) {
            $files = $this->request->getFiles();
            $arquivos = $files['anexos'] ?? [];
            if (!is_array($arquivos)) {
                $arquivos = $arquivos ? [$arquivos] : [];
            }
        }
        if (empty($arquivos)) {
            return;
        }

        $config = config('Ouvidoria');
        $anexoModel = model(\App\Models\ManifestacaoAnexoModel::class);
        $historicoModel = model(ManifestacaoHistoricoModel::class);
        $usuario = obterUsuarioLogado();

        $uploadDir = WRITEPATH . $config->uploadsDir . '/' . $manifestacaoId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($arquivos as $file) {
            if (!$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }
            if ($file->getSize() > $config->anexoMaxSize) {
                continue;
            }
            if (!in_array($file->getMimeType(), $config->anexoMimesPermitidos)) {
                continue;
            }

            $nomeRandom = bin2hex(random_bytes(16)) . '_' . $file->getClientName();
            $caminho = $config->uploadsDir . '/' . $manifestacaoId . '/' . $nomeRandom;

            if ($file->move($uploadDir, $nomeRandom)) {
                $conteudo = file_get_contents($uploadDir . $nomeRandom);
                $hash = $conteudo ? hash('sha256', $conteudo) : null;

                $anexoModel->insert([
                    'manifestacao_id' => $manifestacaoId,
                    'enviado_por_usuario_id' => $usuario['id'] ?? null,
                    'nome_original' => $file->getClientName(),
                    'caminho_arquivo' => $caminho,
                    'mime' => $file->getMimeType(),
                    'tamanho' => $file->getSize(),
                    'hash' => $hash,
                    'criado_em' => date('Y-m-d H:i:s'),
                ]);

                $historicoModel->registrar($manifestacaoId, $usuario['id'] ?? null, ManifestacaoHistoricoModel::TIPO_ANEXO_ADICIONADO, [
                    'nome_arquivo' => $file->getClientName(),
                ]);
            }
        }
    }
}
