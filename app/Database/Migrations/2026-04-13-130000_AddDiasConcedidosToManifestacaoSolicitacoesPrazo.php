<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiasConcedidosToManifestacaoSolicitacoesPrazo extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('manifestacao_solicitacoes_prazo', [
            'dias_concedidos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'dias_solicitados',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('manifestacao_solicitacoes_prazo', 'dias_concedidos');
    }
}
