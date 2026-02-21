<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela manifestacao_chaves.
 * Armazena a DEK criptografada por manifestação.
 */
class ManifestacaoChaveModel extends Model
{
    protected $table            = 'manifestacao_chaves';
    protected $primaryKey       = 'manifestacao_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'chave_encriptada_por_sistema',
        'algoritmo',
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
     * Retorna chave de uma manifestação.
     */
    public function porManifestacao(int $manifestacaoId): ?array
    {
        return $this->where('manifestacao_id', $manifestacaoId)->first();
    }
}
