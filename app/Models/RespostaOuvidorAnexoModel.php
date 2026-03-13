<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela resposta_ouvidor_anexos.
 */
class RespostaOuvidorAnexoModel extends Model
{
    protected $table            = 'resposta_ouvidor_anexos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'resposta_ouvidor_id',
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
     * Retorna anexos de uma resposta ao ouvidor.
     */
    public function porResposta(int $respostaOuvidorId): array
    {
        return $this->where('resposta_ouvidor_id', $respostaOuvidorId)
            ->orderBy('criado_em', 'ASC')
            ->findAll();
    }
}
