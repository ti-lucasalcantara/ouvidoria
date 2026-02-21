<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adiciona coluna e_devolucao na tabela manifestacao_atribuicoes.
 * Indica quando a atribuição é uma devolução (usuario devolveu para gerente).
 */
class AddEdevolucaoToAtribuicoes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('manifestacao_atribuicoes', [
            'e_devolucao' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('manifestacao_atribuicoes', 'e_devolucao');
    }
}
