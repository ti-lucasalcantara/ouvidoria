<?php

namespace App\Controllers;

use App\Models\ManifestacaoModel;
use App\Models\ManifestacaoAtribuicaoModel;

/**
 * Controller do Dashboard.
 * Exibe visão diferente conforme role do usuário.
 */
class DashboardController extends BaseController
{
    protected $helpers  = ['ouvidoria', 'form', 'url'];
    private $dados;

    public function __construct(){
        $this->dados = array();
        $this->dados['menu_ativo'] = "ouvidoria.dashboard";
    }

    /**
     * Remove duplicatas mantendo apenas uma entrada por manifestacao_id (a primeira da lista ordenada).
     */
    private function removerDuplicatasPorManifestacao(array $lista): array
    {
        $vistos = [];
        $resultado = [];
        foreach ($lista as $item) {
            $id = $item['manifestacao_id'] ?? null;
            if ($id !== null && !isset($vistos[$id])) {
                $vistos[$id] = true;
                $resultado[] = $item;
            }
        }
        return $resultado;
    }

    /**
     * Redireciona para o dashboard apropriado ao role.
     */
    public function index()
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return redirect()->to(site_url('login'));
        }

        $role = $usuario['role'] ?? 'usuario';
        if (empty($role)) {
            $role = 'usuario';
        }

        return match ($role) {
            'administrador', 'ouvidor' => $this->dashboardOuvidor(),
            'gerente' => $this->dashboardGerente(),
            default => $this->dashboardUsuario(),
        };
    }

    /**
     * Dashboard do Ouvidor/Admin: vê tudo.
     */
    private function dashboardOuvidor()
    {
        $usuario = obterUsuarioLogado();
        $manifestacaoModel = model(ManifestacaoModel::class);
        $slaService = service('sla');

        // Contadores (usa $db para evitar estado acumulado no model)
        $db = \Config\Database::connect();
        $totalTodos = $db->table('manifestacoes')->countAllResults();
        $totalRecebidas = $db->table('manifestacoes')->where('status', 'recebida')->countAllResults();
        $totalAbertas = $db->table('manifestacoes')->whereIn('status', ['recebida', 'encaminhada', 'em_atendimento'])->countAllResults();
        $totalEncaminhadas = $db->table('manifestacoes')->where('status', 'encaminhada')->countAllResults();
        $totalRespondidas = $db->table('manifestacoes')->where('status', 'respondida')->countAllResults();

        $agora = date('Y-m-d H:i:s');
        $em48h = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $emAtraso = $db->table('manifestacoes')->whereNotIn('status', ['finalizada', 'arquivada', 'respondida'])
            ->where('data_limite_sla <', $agora)->where('data_limite_sla IS NOT NULL')->countAllResults();
        $aVencer = $db->table('manifestacoes')->whereNotIn('status', ['finalizada', 'arquivada', 'respondida'])
            ->where('data_limite_sla >=', $agora)->where('data_limite_sla <=', $em48h)->countAllResults();

        $inicioMes = date('Y-m-01 00:00:00');
        $finalizadasMes = $db->table('manifestacoes')->whereIn('status', ['finalizada', 'arquivada'])
            ->where('data_finalizacao >=', $inicioMes)
            ->countAllResults();

        // Filtro por clique nos cards ou formulário
        $statusFiltro = $this->request->getGet('status') ?? '';
        $prioridadeFiltro = $this->request->getGet('prioridade') ?? '';
        $dataInicio = $this->request->getGet('data_inicio') ?? '';
        $dataFim = $this->request->getGet('data_fim') ?? '';

        $manifestacaoModel = model(ManifestacaoModel::class);
        $builder = $manifestacaoModel->select('manifestacoes.*')
            ->orderBy('COALESCE(manifestacoes.data_manifestacao, manifestacoes.created_at)', 'DESC', false);

        if ($prioridadeFiltro !== '') {
            $builder->where('manifestacoes.prioridade', $prioridadeFiltro);
        }

        switch ($statusFiltro) {
            case 'abertas':
                $builder->whereIn('status', ['recebida', 'encaminhada', 'em_atendimento']);
                break;
            case 'recebida':
                $builder->where('status', 'recebida');
                break;
            case 'encaminhada':
                $builder->where('status', 'encaminhada');
                break;
            case 'respondida':
                $builder->where('status', 'respondida');
                break;
            case 'em_atraso':
                $builder->whereNotIn('status', ['finalizada', 'arquivada', 'respondida'])
                    ->where('data_limite_sla <', $agora)->where('data_limite_sla IS NOT NULL');
                break;
            case 'a_vencer':
                $builder->whereNotIn('status', ['finalizada', 'arquivada', 'respondida'])
                    ->where('data_limite_sla >=', $agora)->where('data_limite_sla <=', $em48h);
                break;
            case 'finalizadas':
                $builder->whereIn('status', ['finalizada', 'arquivada']);
                break;
        }

        if ($dataInicio !== '') {
            $builder->where('DATE(COALESCE(manifestacoes.data_manifestacao, manifestacoes.created_at)) >=', $dataInicio);
        }
        if ($dataFim !== '') {
            $builder->where('DATE(COALESCE(manifestacoes.data_manifestacao, manifestacoes.created_at)) <=', $dataFim);
        }

        $ultimas = $builder->limit(50)->findAll();

        // Descriptografa assunto para exibição na tabela (ouvidor/admin podem ver)
        try {
            $encryptionService = service('encryption');
            $ultimas = array_map(fn($m) => $encryptionService->descriptografarManifestacao($m), $ultimas);
        } catch (\Throwable $e) {
            // Master key não configurada - assunto permanece criptografado
        }

        // IDs de manifestações onde ouvidor tem atribuição com e_devolucao=1 (gerente marcou "respondida")
        $idsDevolvidoOuvidor = [];
        if (($usuario['role'] ?? '') === 'ouvidor' && $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes')) {
            $atribModel = model(ManifestacaoAtribuicaoModel::class);
            $atribs = $atribModel->select('manifestacao_id')
                ->where('atribuido_para_usuario_id', $usuario['id'])
                ->where('ativo', 1)
                ->where('e_devolucao', 1)
                ->findAll();
            $idsDevolvidoOuvidor = array_column($atribs, 'manifestacao_id');
        }

        return view('ouvidoria/dashboard/ouvidor', [
            'totalTodos' => $totalTodos,
            'totalRecebidas' => $totalRecebidas,
            'totalAbertas' => $totalAbertas,
            'totalEncaminhadas' => $totalEncaminhadas,
            'totalRespondidas' => $totalRespondidas,
            'emAtraso' => $emAtraso,
            'aVencer' => $aVencer,
            'finalizadasMes' => $finalizadasMes,
            'ultimas' => $ultimas,
            'slaService' => $slaService,
            'statusFiltro' => $statusFiltro,
            'prioridadeFiltro' => $prioridadeFiltro,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'idsDevolvidoOuvidor' => $idsDevolvidoOuvidor,
            'menu_ativo' => $this->dados['menu_ativo'],
        ]);
    }

    /**
     * Dashboard do Gerente: vê manifestações atribuídas.
     */
    private function dashboardGerente()
    {
        $usuario = obterUsuarioLogado();
        $usuarioId = (int) $usuario['id'];

        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $manifestacaoModel = model(ManifestacaoModel::class);
        $slaService = service('sla');

        $db = \Config\Database::connect();
        $temColunaDevolucao = $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes');

        $statusFiltro = $this->request->getGet('status') ?? '';

        // Devolvidas: somente atribuições com e_devolucao=1 (uma por manifestação)
        $devolvidos = [];
        if ($temColunaDevolucao) {
            $devolvidosRaw = $atribuicaoModel->select('manifestacao_atribuicoes.*, manifestacoes.protocolo, manifestacoes.protocolo_falabr, manifestacoes.assunto, manifestacoes.status, manifestacoes.data_limite_sla, manifestacoes.prioridade, manifestacoes.data_manifestacao, manifestacoes.created_at')
                ->join('manifestacoes', 'manifestacoes.id = manifestacao_atribuicoes.manifestacao_id')
                ->where('manifestacao_atribuicoes.atribuido_para_usuario_id', $usuarioId)
                ->where('manifestacao_atribuicoes.ativo', 1)
                ->where('manifestacao_atribuicoes.e_devolucao', 1)
                ->orderBy('manifestacao_atribuicoes.criado_em', 'DESC')
                ->findAll();
            $devolvidos = $this->removerDuplicatasPorManifestacao($devolvidosRaw);
        }

        // Atribuídas: exclui devolvidas e remove duplicatas (uma por manifestação)
        $builderAtribuidas = $atribuicaoModel->select('manifestacao_atribuicoes.*, manifestacoes.protocolo, manifestacoes.protocolo_falabr, manifestacoes.assunto, manifestacoes.status, manifestacoes.data_limite_sla, manifestacoes.prioridade, manifestacoes.data_manifestacao, manifestacoes.created_at')
            ->join('manifestacoes', 'manifestacoes.id = manifestacao_atribuicoes.manifestacao_id')
            ->where('manifestacao_atribuicoes.atribuido_para_usuario_id', $usuarioId)
            ->where('manifestacao_atribuicoes.ativo', 1);
        if ($temColunaDevolucao) {
            $builderAtribuidas->groupStart()
                ->where('manifestacao_atribuicoes.e_devolucao', 0)
                ->orWhere('manifestacao_atribuicoes.e_devolucao IS NULL')
            ->groupEnd();
        }
        $minhasAtribuidasRaw = $builderAtribuidas->orderBy('manifestacao_atribuicoes.criado_em', 'DESC')->findAll();
        $idsDevolvidos = array_column($devolvidos, 'manifestacao_id');
        $todasRecebidas = $this->removerDuplicatasPorManifestacao(
            array_filter($minhasAtribuidasRaw, fn($a) => !in_array($a['manifestacao_id'], $idsDevolvidos))
        );

        // IDs de manifestações onde o gerente já encaminhou para alguém (atribuido_por = gerente, ativo = 1)
        $idsGerenteEncaminhou = [];
        $encaminhadosPeloGerente = $atribuicaoModel->select('manifestacao_id')
            ->where('atribuido_por_usuario_id', $usuarioId)
            ->where('ativo', 1)
            ->findAll();
        $idsGerenteEncaminhou = array_unique(array_column($encaminhadosPeloGerente, 'manifestacao_id'));

        // Atribuídas: recebidas do ouvidor, gerente AINDA NÃO encaminhou para usuário
        $minhasAtribuidas = array_filter($todasRecebidas, fn($a) => !in_array($a['manifestacao_id'], $idsGerenteEncaminhou));
        // Encaminhadas: gerente já encaminhou para usuário
        $encaminhadas = array_filter($todasRecebidas, fn($a) => in_array($a['manifestacao_id'], $idsGerenteEncaminhou));

        $emAtraso = 0;
        $aVencer = 0;
        foreach ($minhasAtribuidas as $a) {
            $flags = $slaService->obterFlagsSla($a);
            if ($flags['em_atraso']) $emAtraso++;
            if ($flags['a_vencer']) $aVencer++;
        }
        foreach ($encaminhadas as $a) {
            $flags = $slaService->obterFlagsSla($a);
            if ($flags['em_atraso']) $emAtraso++;
            if ($flags['a_vencer']) $aVencer++;
        }
        foreach ($devolvidos as $a) {
            $flags = $slaService->obterFlagsSla($a);
            if ($flags['em_atraso']) $emAtraso++;
            if ($flags['a_vencer']) $aVencer++;
        }

        // Aplicar filtro por KPI
        $devolvidosFiltrados = $devolvidos;
        $atribuidasFiltradas = $minhasAtribuidas;
        $encaminhadasFiltradas = $encaminhadas;
        if ($statusFiltro === 'em_atraso') {
            $devolvidosFiltrados = array_filter($devolvidos, fn($a) => $slaService->obterFlagsSla($a)['em_atraso']);
            $atribuidasFiltradas = array_filter($minhasAtribuidas, fn($a) => $slaService->obterFlagsSla($a)['em_atraso']);
            $encaminhadasFiltradas = array_filter($encaminhadas, fn($a) => $slaService->obterFlagsSla($a)['em_atraso']);
        } elseif ($statusFiltro === 'a_vencer') {
            $devolvidosFiltrados = array_filter($devolvidos, fn($a) => $slaService->obterFlagsSla($a)['a_vencer']);
            $atribuidasFiltradas = array_filter($minhasAtribuidas, fn($a) => $slaService->obterFlagsSla($a)['a_vencer']);
            $encaminhadasFiltradas = array_filter($encaminhadas, fn($a) => $slaService->obterFlagsSla($a)['a_vencer']);
        } elseif ($statusFiltro === 'devolvidas') {
            $atribuidasFiltradas = [];
            $encaminhadasFiltradas = [];
        } elseif ($statusFiltro === 'atribuidas') {
            $devolvidosFiltrados = [];
            $encaminhadasFiltradas = [];
        } elseif ($statusFiltro === 'encaminhadas') {
            $devolvidosFiltrados = [];
            $atribuidasFiltradas = [];
        }

        $totalTodos = count($minhasAtribuidas) + count($encaminhadas) + count($devolvidos);

        // Descriptografa assunto para exibição (manifestacao_id -> id para o service)
        try {
            $encryptionService = service('encryption');
            $descriptografar = function ($m) use ($encryptionService) {
                $m['id'] = $m['manifestacao_id'] ?? $m['id'] ?? 0;
                return $encryptionService->descriptografarManifestacao($m);
            };
            $devolvidosFiltrados = array_values(array_map($descriptografar, $devolvidosFiltrados));
            $atribuidasFiltradas = array_values(array_map($descriptografar, $atribuidasFiltradas));
            $encaminhadasFiltradas = array_values(array_map($descriptografar, $encaminhadasFiltradas));
        } catch (\Throwable $e) {
            // Master key não configurada
        }

        return view('ouvidoria/dashboard/gerente', [
            'minhasAtribuidas' => $atribuidasFiltradas,
            'encaminhadas' => $encaminhadasFiltradas,
            'devolvidos' => $devolvidosFiltrados,
            'totalAtivas' => count($minhasAtribuidas),
            'totalEncaminhadas' => count($encaminhadas),
            'totalDevolvidos' => count($devolvidos),
            'totalTodos' => $totalTodos,
            'emAtraso' => $emAtraso,
            'aVencer' => $aVencer,
            'slaService' => $slaService,
            'statusFiltro' => $statusFiltro,
            'menu_ativo' => $this->dados['menu_ativo'],
        ]);
    }

    /**
     * Dashboard do Usuário: vê manifestações com acesso.
     * Recebidas e devolvidas.
     */
    private function dashboardUsuario()
    {
        $usuario = obterUsuarioLogado();
        $usuarioId = (int) $usuario['id'];

        $atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
        $slaService = service('sla');
        $db = \Config\Database::connect();
        $temColunaDevolucao = $db->fieldExists('e_devolucao', 'manifestacao_atribuicoes');

        // Recebidas: atribuído PARA o usuário (ele tem a manifestação)
        $builderRecebidas = $atribuicaoModel->select('manifestacao_atribuicoes.*, manifestacoes.protocolo, manifestacoes.protocolo_falabr, manifestacoes.assunto, manifestacoes.status, manifestacoes.data_limite_sla, manifestacoes.prioridade, manifestacoes.data_manifestacao, manifestacoes.created_at')
            ->join('manifestacoes', 'manifestacoes.id = manifestacao_atribuicoes.manifestacao_id')
            ->where('manifestacao_atribuicoes.atribuido_para_usuario_id', $usuarioId)
            ->where('manifestacao_atribuicoes.ativo', 1)
            ->orderBy('manifestacao_atribuicoes.criado_em', 'DESC');
        $minhasRaw = $builderRecebidas->findAll();

        $devolvidasParaMim = [];
        $recebidas = [];
        if ($temColunaDevolucao) {
            $devolvidasParaMimRaw = array_filter($minhasRaw, fn($a) => ($a['e_devolucao'] ?? 0));
            $recebidasRaw = array_filter($minhasRaw, fn($a) => !($a['e_devolucao'] ?? 0));
            $devolvidasParaMim = $this->removerDuplicatasPorManifestacao($devolvidasParaMimRaw);
            $recebidas = $this->removerDuplicatasPorManifestacao($recebidasRaw);
        } else {
            $recebidas = $this->removerDuplicatasPorManifestacao($minhasRaw);
        }

        // Que devolvi: o usuário devolveu para o gerente (atribuído POR ele, e_devolucao=1, ativo=1)
        // Exclui quando usuário retomou (atribuição inativada) - manifestação volta para recebidas
        $devolvidas = [];
        if ($temColunaDevolucao) {
            $queDevolviRaw = $atribuicaoModel->select('manifestacao_atribuicoes.*, manifestacoes.protocolo, manifestacoes.protocolo_falabr, manifestacoes.assunto, manifestacoes.status, manifestacoes.data_limite_sla, manifestacoes.prioridade, manifestacoes.data_manifestacao, manifestacoes.created_at')
                ->join('manifestacoes', 'manifestacoes.id = manifestacao_atribuicoes.manifestacao_id')
                ->where('manifestacao_atribuicoes.atribuido_por_usuario_id', $usuarioId)
                ->where('manifestacao_atribuicoes.e_devolucao', 1)
                ->where('manifestacao_atribuicoes.ativo', 1)
                ->orderBy('manifestacao_atribuicoes.criado_em', 'DESC')
                ->findAll();
            $devolvidas = $this->removerDuplicatasPorManifestacao($queDevolviRaw);
        }

        $statusFiltro = $this->request->getGet('status') ?? '';
        $recebidasFiltradas = $recebidas;
        $devolvidasParaMimFiltradas = $devolvidasParaMim;
        $devolvidasFiltradas = $devolvidas;
        if ($statusFiltro === 'em_atraso') {
            $recebidasFiltradas = array_filter($recebidas, fn($a) => $slaService->obterFlagsSla($a)['em_atraso']);
            $devolvidasParaMimFiltradas = array_filter($devolvidasParaMim, fn($a) => $slaService->obterFlagsSla($a)['em_atraso']);
            $devolvidasFiltradas = array_filter($devolvidas, fn($a) => $slaService->obterFlagsSla($a)['em_atraso']);
        } elseif ($statusFiltro === 'a_vencer') {
            $recebidasFiltradas = array_filter($recebidas, fn($a) => $slaService->obterFlagsSla($a)['a_vencer']);
            $devolvidasParaMimFiltradas = array_filter($devolvidasParaMim, fn($a) => $slaService->obterFlagsSla($a)['a_vencer']);
            $devolvidasFiltradas = array_filter($devolvidas, fn($a) => $slaService->obterFlagsSla($a)['a_vencer']);
        } elseif ($statusFiltro === 'devolvidas') {
            $recebidasFiltradas = [];
            $devolvidasParaMimFiltradas = [];
        } elseif ($statusFiltro === 'recebidas') {
            $devolvidasFiltradas = [];
        }

        $emAtraso = 0;
        $aVencer = 0;
        foreach ($recebidas as $a) {
            $flags = $slaService->obterFlagsSla($a);
            if ($flags['em_atraso']) $emAtraso++;
            if ($flags['a_vencer']) $aVencer++;
        }
        foreach ($devolvidasParaMim as $a) {
            $flags = $slaService->obterFlagsSla($a);
            if ($flags['em_atraso']) $emAtraso++;
            if ($flags['a_vencer']) $aVencer++;
        }
        foreach ($devolvidas as $a) {
            $flags = $slaService->obterFlagsSla($a);
            if ($flags['em_atraso']) $emAtraso++;
            if ($flags['a_vencer']) $aVencer++;
        }

        try {
            $encryptionService = service('encryption');
            $descriptografar = function ($m) use ($encryptionService) {
                $m['id'] = $m['manifestacao_id'] ?? $m['id'] ?? 0;
                return $encryptionService->descriptografarManifestacao($m);
            };
            $recebidasFiltradas = array_values(array_map($descriptografar, $recebidasFiltradas));
            $devolvidasParaMimFiltradas = array_values(array_map($descriptografar, $devolvidasParaMimFiltradas));
            $devolvidasFiltradas = array_values(array_map($descriptografar, $devolvidasFiltradas));
        } catch (\Throwable $e) {
            // Master key não configurada
        }

        return view('ouvidoria/dashboard/usuario', [
            'recebidas' => $recebidasFiltradas,
            'devolvidasParaMim' => $devolvidasParaMimFiltradas,
            'devolvidas' => $devolvidasFiltradas,
            'totalRecebidas' => count($recebidas),
            'totalDevolvidas' => count($devolvidas),
            'totalDevolvidasParaMim' => count($devolvidasParaMim),
            'totalTodos' => count($recebidas) + count($devolvidasParaMim) + count($devolvidas),
            'emAtraso' => $emAtraso,
            'aVencer' => $aVencer,
            'slaService' => $slaService,
            'statusFiltro' => $statusFiltro,
            'menu_ativo' => $this->dados['menu_ativo'],
        ]);
    }
}
