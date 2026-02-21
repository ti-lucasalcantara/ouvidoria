<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela manifestacao_atribuicoes.
 * Pivot N:N com trilha de encaminhamento entre usuários.
 */
class CreateManifestacaoAtribuicoes extends Migration
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
            'atribuido_por_usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'atribuido_para_usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'mensagem_encaminhamento' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status_no_momento' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'encerrado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('manifestacao_id');
        $this->forge->addKey(['manifestacao_id', 'ativo']);
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('atribuido_por_usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('atribuido_para_usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('manifestacao_atribuicoes');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_atribuicoes');
    }
}
