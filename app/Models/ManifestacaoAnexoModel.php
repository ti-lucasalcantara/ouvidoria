<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela manifestacao_anexos.
 * Representa anexos das manifestações.
 */
class ManifestacaoAnexoModel extends Model
{
    protected $table            = 'manifestacao_anexos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'enviado_por_usuario_id',
        'nome_original',
        'caminho_arquivo',
        'mime',
        'tamanho',
        'hash',
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
     * Retorna anexos de uma manifestação.
     */
    public function porManifestacao(int $manifestacaoId): array
    {
        return $this->where('manifestacao_id', $manifestacaoId)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }
}
