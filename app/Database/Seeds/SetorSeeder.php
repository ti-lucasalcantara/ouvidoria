<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder para tabela setores.
 * Insere setores iniciais da organização.
 */
class SetorSeeder extends Seeder
{
    public function run(): void
    {
        $dados = [
            ['nome' => 'Ouvidoria', 'ativo' => 1],
            ['nome' => 'Administração', 'ativo' => 1],
            ['nome' => 'Recursos Humanos', 'ativo' => 1],
            ['nome' => 'Ti', 'ativo' => 1],
        ];

        foreach ($dados as $row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('setores')->insert($row);
        }
    }
}
