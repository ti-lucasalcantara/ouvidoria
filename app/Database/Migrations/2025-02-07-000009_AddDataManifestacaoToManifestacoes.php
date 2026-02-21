<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para adicionar coluna data_manifestacao na tabela manifestacoes.
 * Data informada pelo usuário (quando ocorreu a manifestação).
 */
class AddDataManifestacaoToManifestacoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('manifestacoes', [
            'data_manifestacao' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'origem',
            ],
        ]);
        $this->forge->addKey('data_manifestacao');
    }

    public function down(): void
    {
        $this->forge->dropColumn('manifestacoes', 'data_manifestacao');
    }
}
