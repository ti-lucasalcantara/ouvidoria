<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para alterar o campo role da tabela usuarios (incluir 'fiscal')
 * e adicionar a coluna fiscal_id (pode ser nulo).
 */
class AlterUsuariosRoleAndAddFiscalId extends Migration
{
    public function up(): void
    {
        // Alterar ENUM role para incluir 'fiscal'
        $this->forge->modifyColumn('usuarios', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['administrador', 'ouvidor', 'gerente', 'usuario', 'fiscal'],
                'default'    => 'usuario',
            ],
        ]);

        // Adicionar coluna fiscal_id (nullable)
        $this->forge->addColumn('usuarios', [
            'fiscal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        // Remover coluna fiscal_id
        $this->forge->dropColumn('usuarios', 'fiscal_id');

        // Reverter ENUM role (remover 'fiscal')
        $this->forge->modifyColumn('usuarios', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['administrador', 'ouvidor', 'gerente', 'usuario'],
                'default'    => 'usuario',
            ],
        ]);
    }
}
