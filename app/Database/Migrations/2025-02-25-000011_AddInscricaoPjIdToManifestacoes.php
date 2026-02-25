<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para adicionar o campo inscricao_pj_id na tabela manifestacoes.
 * Registro do estabelecimento que recebeu a manifestação (pode ser nulo).
 */
class AddInscricaoPjIdToManifestacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('manifestacoes', [
            'inscricao_pj_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('manifestacoes', 'inscricao_pj_id');
    }
}
