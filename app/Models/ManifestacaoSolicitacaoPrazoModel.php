<?php

namespace App\Models;

use CodeIgniter\Model;

class ManifestacaoSolicitacaoPrazoModel extends Model
{
    protected $table            = 'manifestacao_solicitacoes_prazo';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'solicitado_por_usuario_id',
        'manifestacao_atribuicao_id',
        'dias_solicitados',
        'dias_concedidos',
        'motivo',
        'status',
        'analisado_por_usuario_id',
        'resposta',
        'analisado_em',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'manifestacao_id' => 'required|integer',
        'solicitado_por_usuario_id' => 'required|integer',
        'dias_solicitados' => 'permit_empty|integer|greater_than[0]|less_than_equal_to[30]',
        'dias_concedidos' => 'permit_empty|integer|greater_than[0]',
        'motivo' => 'required|min_length[5]',
        'status' => 'required|in_list[pendente,aprovada,rejeitada]',
    ];

    public function pendentesDetalhadas(): array
    {
        return $this->select('manifestacao_solicitacoes_prazo.*, manifestacoes.protocolo, manifestacoes.protocolo_falabr, manifestacoes.assunto, manifestacoes.status as manifestacao_status, manifestacoes.prioridade, manifestacoes.data_limite_sla, manifestacoes.data_manifestacao, manifestacoes.created_at as manifestacao_created_at, usuarios.nome as solicitante_nome')
            ->join('manifestacoes', 'manifestacoes.id = manifestacao_solicitacoes_prazo.manifestacao_id')
            ->join('usuarios', 'usuarios.id = manifestacao_solicitacoes_prazo.solicitado_por_usuario_id')
            ->where('manifestacao_solicitacoes_prazo.status', 'pendente')
            ->orderBy('manifestacao_solicitacoes_prazo.created_at', 'DESC')
            ->findAll();
    }

    public function listarDetalhadas(?string $status = null): array
    {
        $builder = $this->select('manifestacao_solicitacoes_prazo.*, manifestacoes.protocolo, manifestacoes.protocolo_falabr, manifestacoes.assunto, manifestacoes.status as manifestacao_status, manifestacoes.prioridade, manifestacoes.data_limite_sla, manifestacoes.data_manifestacao, manifestacoes.created_at as manifestacao_created_at, solicitante.nome as solicitante_nome, analisador.nome as analisador_nome')
            ->join('manifestacoes', 'manifestacoes.id = manifestacao_solicitacoes_prazo.manifestacao_id')
            ->join('usuarios solicitante', 'solicitante.id = manifestacao_solicitacoes_prazo.solicitado_por_usuario_id')
            ->join('usuarios analisador', 'analisador.id = manifestacao_solicitacoes_prazo.analisado_por_usuario_id', 'left')
            ->orderBy('manifestacao_solicitacoes_prazo.created_at', 'DESC');

        if ($status !== null && $status !== '') {
            $builder->where('manifestacao_solicitacoes_prazo.status', $status);
        }

        return $builder->findAll();
    }

    public function ultimaDaManifestacao(int $manifestacaoId): ?array
    {
        return $this->select('manifestacao_solicitacoes_prazo.*, usuarios.nome as solicitante_nome, analisador.nome as analisador_nome')
            ->join('usuarios', 'usuarios.id = manifestacao_solicitacoes_prazo.solicitado_por_usuario_id')
            ->join('usuarios analisador', 'analisador.id = manifestacao_solicitacoes_prazo.analisado_por_usuario_id', 'left')
            ->where('manifestacao_id', $manifestacaoId)
            ->orderBy('manifestacao_solicitacoes_prazo.created_at', 'DESC')
            ->first();
    }

    public function porManifestacao(int $manifestacaoId): array
    {
        return $this->select('manifestacao_solicitacoes_prazo.*, usuarios.nome as solicitante_nome, analisador.nome as analisador_nome')
            ->join('usuarios', 'usuarios.id = manifestacao_solicitacoes_prazo.solicitado_por_usuario_id')
            ->join('usuarios analisador', 'analisador.id = manifestacao_solicitacoes_prazo.analisado_por_usuario_id', 'left')
            ->where('manifestacao_id', $manifestacaoId)
            ->orderBy('manifestacao_solicitacoes_prazo.created_at', 'DESC')
            ->findAll();
    }
}
