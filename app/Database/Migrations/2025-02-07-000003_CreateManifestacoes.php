<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar a tabela manifestacoes.
 * Armazena as manifestações cadastradas pela ouvidoria.
 */
class CreateManifestacoes extends Migration
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
            'protocolo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'protocolo_falabr' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'origem' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'default'    => 'Fala.BR',
            ],
            'assunto' => [
                'type' => 'TEXT',
            ],
            'descricao' => [
                'type' => 'LONGTEXT',
            ],
            'dados_identificacao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['recebida', 'encaminhada', 'em_atendimento', 'respondida', 'finalizada', 'arquivada'],
                'default'    => 'recebida',
            ],
            'prioridade' => [
                'type'       => 'ENUM',
                'constraint' => ['baixa', 'media', 'alta'],
                'default'    => 'media',
            ],
            'sla_prazo_em_dias' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 30,
            ],
            'data_limite_sla' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'data_primeiro_encaminhamento' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'data_finalizacao' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'criado_por_usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey('protocolo');
        $this->forge->addKey('protocolo_falabr');
        $this->forge->addKey('status');
        $this->forge->addKey('data_limite_sla');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('criado_por_usuario_id', 'usuarios', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('manifestacoes');
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacoes');
    }
}
