<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela categorias_manifestacao.
 */
class CategoriaManifestacaoModel extends Model
{
    protected $table            = 'categorias_manifestacao';
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
        'nome'   => 'required|min_length[2]|max_length[150]',
        'ativo'  => 'in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Retorna categorias ativas para select (create/edit manifestação).
     */
    public function listarAtivas(): array
    {
        return $this->where('ativo', 1)->orderBy('nome', 'ASC')->findAll();
    }
}
