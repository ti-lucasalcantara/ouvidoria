<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela manifestacao_atribuicoes.
 * Representa as atribuições/encaminhamentos de manifestações.
 */
class ManifestacaoAtribuicaoModel extends Model
{
    protected $table            = 'manifestacao_atribuicoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'atribuido_por_usuario_id',
        'atribuido_para_usuario_id',
        'mensagem_encaminhamento',
        'e_devolucao',
        'status_no_momento',
        'ativo',
        'encerrado_em',
        'criado_em',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'criado_em';
    protected $updatedField  = '';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation       = true;
    protected $cleanValidationRules = true;

    /**
     * Retorna atribuições ativas de uma manifestação.
     */
    public function ativasPorManifestacao(int $manifestacaoId): array
    {
        return $this->where('manifestacao_id', $manifestacaoId)
            ->where('ativo', 1)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }

    /**
     * Verifica se o usuário já tem atribuição ativa para a manifestação (não pode encaminhar duas vezes para o mesmo).
     */
    public function usuarioJaAtribuidoAtivo(int $manifestacaoId, int $usuarioId): bool
    {
        return $this->where('manifestacao_id', $manifestacaoId)
            ->where('atribuido_para_usuario_id', $usuarioId)
            ->where('ativo', 1)
            ->first() !== null;
    }

    /**
     * Verifica se usuário está vinculado à manifestação (atribuído em algum momento).
     */
    public function usuarioVinculado(int $manifestacaoId, int $usuarioId): bool
    {
        return $this->where('manifestacao_id', $manifestacaoId)
            ->groupStart()
                ->where('atribuido_para_usuario_id', $usuarioId)
                ->orWhere('atribuido_por_usuario_id', $usuarioId)
            ->groupEnd()
            ->first() !== null;
    }

    /**
     * Retorna manifestações atribuídas ao usuário (ativas).
     */
    public function manifestacoesAtribuidasAoUsuario(int $usuarioId): array
    {
        return $this->select('manifestacao_atribuicoes.*, manifestacoes.protocolo, manifestacoes.status, manifestacoes.data_limite_sla')
            ->join('manifestacoes', 'manifestacoes.id = manifestacao_atribuicoes.manifestacao_id')
            ->where('atribuido_para_usuario_id', $usuarioId)
            ->where('manifestacao_atribuicoes.ativo', 1)
            ->orderBy('manifestacao_atribuicoes.criado_em', 'DESC')
            ->findAll();
    }
}
