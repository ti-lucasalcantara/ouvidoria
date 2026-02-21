<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela manifestacao_anexos.
 * Armazena anexos das manifestações (caminho no filesystem).
 */
class CreateManifestacaoAnexos extends Migration
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
            'enviado_por_usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nome_original' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'caminho_arquivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'mime' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tamanho' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('manifestacao_id');
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('enviado_por_usuario_id', 'usuarios', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('manifestacao_anexos');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_anexos');
    }
}
