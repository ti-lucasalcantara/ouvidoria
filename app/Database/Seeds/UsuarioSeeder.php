<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder para tabela usuarios.
 * Insere usuário administrador inicial.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $primeiroSetor = $this->db->table('setores')->select('id')->limit(1)->get()->getRow();
        $setorId = $primeiroSetor ? $primeiroSetor->id : null;

        $admin = [
            'setor_id'              => $setorId,
            'nome'                  => 'Administrador Sistema',
            'email'                 => 'admin@ouvidoria.local',
            'login'                 => 'admin',
            'role'                  => 'administrador',
            'ativo'                 => 1,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ];

        $this->db->table('usuarios')->insert($admin);
    }
}
