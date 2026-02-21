<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela manifestacao_chaves.
 * Armazena a DEK (Data Encryption Key) criptografada por manifestação.
 */
class CreateManifestacaoChaves extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'manifestacao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'chave_encriptada_por_sistema' => [
                'type' => 'TEXT',
            ],
            'algoritmo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'AES-256-GCM',
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('manifestacao_id', true);
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('manifestacao_chaves');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_chaves');
    }
}
