<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela setores.
 * Representa departamentos/setores da organização.
 */
class SetorModel extends Model
{
    protected $table            = 'setores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nome',
        'ativo',
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
    protected $deletedField  = '';

    protected $validationRules = [
        'nome' => 'required|min_length[2]|max_length[150]',
        'ativo' => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome do setor é obrigatório.',
            'min_length' => 'O nome deve ter pelo menos 2 caracteres.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna apenas setores ativos.
     */
    public function ativos(): self
    {
        return $this->where('ativo', 1);
    }
}
