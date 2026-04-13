<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManifestacaoSolicitacoesPrazo extends Migration
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
            'solicitado_por_usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'manifestacao_atribuicao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'dias_solicitados' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'motivo' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pendente', 'aprovada', 'rejeitada'],
                'default'    => 'pendente',
            ],
            'analisado_por_usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'resposta' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'analisado_em' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey(['manifestacao_id', 'status']);
        $this->forge->addKey('solicitado_por_usuario_id');
        $this->forge->addKey('manifestacao_atribuicao_id');
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE', 'fk_man_pr_man' );
        $this->forge->addForeignKey('solicitado_por_usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE','fk_man_pr_sol_usu' );
        $this->forge->addForeignKey('manifestacao_atribuicao_id', 'manifestacao_atribuicoes', 'id', 'SET NULL', 'SET NULL','fk_man_pr_man_ati' );
        $this->forge->addForeignKey('analisado_por_usuario_id', 'usuarios', 'id', 'SET NULL', 'SET NULL','fk_man_pr_an_usu' );

       

        
        $this->forge->createTable('manifestacao_solicitacoes_prazo');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_solicitacoes_prazo');
    }
}
