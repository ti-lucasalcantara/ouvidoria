<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela resposta_ouvidor_anexos.
 */
class CreateRespostaOuvidorAnexos extends Migration
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
            'resposta_ouvidor_id' => [
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
        $this->forge->addKey('resposta_ouvidor_id');
        $this->forge->addForeignKey('resposta_ouvidor_id', 'respostas_ouvidor', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('enviado_por_usuario_id', 'usuarios', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('resposta_ouvidor_anexos');
    }

    public function down(): void
    {
        $this->forge->dropTable('resposta_ouvidor_anexos');
    }
}
