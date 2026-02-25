<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela usuarios.
 * Usuários internos do sistema de ouvidoria.
 */
class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'setor_id',
        'nome',
        'email',
        'login',
        'role',
        'fiscal_id',
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
        'nome'  => 'required|min_length[2]|max_length[150]',
        'email' => 'required|valid_email|max_length[150]',
        'role'  => 'required|in_list[administrador,ouvidor,gerente,usuario,fiscal]',
        'ativo' => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome é obrigatório.',
        ],
        'email' => [
            'required' => 'O e-mail é obrigatório.',
            'valid_email' => 'Informe um e-mail válido.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $with = [];

    /**
     * Relacionamento com setor.
     */
    public function setor()
    {
        return $this->belongsTo(SetorModel::class, 'setor_id');
    }

    /**
     * Retorna apenas usuários ativos.
     */
    public function ativos(): self
    {
        return $this->where('ativo', 1);
    }

    /**
     * Busca usuário por email.
     */
    public function porEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Lista usuários por role (para selects de encaminhamento).
     */
    public function porRole(string $role): self
    {
        return $this->where('role', $role)->where('ativo', 1);
    }

    /**
     * Lista gerentes e usuários (para encaminhamento).
     */
    public function gerentesEUsuarios(): self
    {
        return $this->whereIn('role', ['gerente', 'usuario'])->where('ativo', 1);
    }
}
