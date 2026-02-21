<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder principal da ouvidoria.
 * Executa todos os seeders na ordem correta.
 */
class OuvidoriaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('SetorSeeder');
        $this->call('UsuarioSeeder');
    }
}
