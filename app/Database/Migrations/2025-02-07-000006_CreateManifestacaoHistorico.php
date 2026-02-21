<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela manifestacao_historico.
 * Log auditável de todos os eventos das manifestações.
 */
class CreateManifestacaoHistorico extends Migration
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
            'manifestacao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tipo_evento' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'detalhes' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('manifestacao_id');
        $this->forge->addKey(['manifestacao_id', 'criado_em']);
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('manifestacao_historico');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_historico');
    }
}
