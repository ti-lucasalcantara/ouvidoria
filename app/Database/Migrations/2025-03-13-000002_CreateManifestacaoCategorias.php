<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para tabela N:N manifestacao_categorias.
 */
class CreateManifestacaoCategorias extends Migration
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
            'categoria_manifestacao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['manifestacao_id', 'categoria_manifestacao_id']);
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('categoria_manifestacao_id', 'categorias_manifestacao', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('manifestacao_categorias');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_categorias');
    }
}
