<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela respostas_ouvidor.
 * Conteúdo (conteudo) é criptografado com a DEK da manifestação.
 */
class RespostaOuvidorModel extends Model
{
    protected $table            = 'respostas_ouvidor';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'respondido_por_usuario_id',
        'conteudo',
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
     * Retorna respostas de uma manifestação (mais antigas primeiro).
     */
    public function porManifestacao(int $manifestacaoId): array
    {
        return $this->where('manifestacao_id', $manifestacaoId)
            ->orderBy('criado_em', 'ASC')
            ->findAll();
    }
}
