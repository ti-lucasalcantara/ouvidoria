<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela usuarios.
 * Usuários internos do sistema de ouvidoria (vinculados ao SSO por email).
 */
class CreateUsuarios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'setor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'login' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['administrador', 'ouvidor', 'gerente', 'usuario'],
                'default'    => 'usuario',
            ],
            'ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('login');
        $this->forge->addKey('role');
        $this->forge->addKey('ativo');
        $this->forge->addForeignKey('setor_id', 'setores', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('usuarios');
    }

    public function down(): void
    {
        $this->forge->dropTable('usuarios');
    }
}
