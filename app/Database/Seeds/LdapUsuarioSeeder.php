<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UsuarioModel;

/**
 * Seeder que consulta o LDAP (API) por uma lista de logins e insere/atualiza na tabela usuarios.
 * A API de login exige senha: use a mesma senha para todos ou uma conta de serviço.
 */
class LdapUsuarioSeeder extends Seeder
{
    private string $apiUrl = 'http://api.crfmg.org.br/apildap/sessions/checarUsuarioExiste';

    public function run(): void
    {
        $logins = [
            ['role' => 'administrador', 'login' => 'lucas.pereira'],
            
            ['role' => 'ouvidor', 'login' => 'erika.nolli'],

            ['role' => 'gerente', 'login' => 'amanda.teixeira'],
            ['role' => 'gerente', 'login' => 'lucianorodrigo'],
            ['role' => 'gerente', 'login' => 'jean.jadir'],
            ['role' => 'gerente', 'login' => 'eli'],
            ['role' => 'gerente', 'login' => 'cintia.amaral'],
            ['role' => 'gerente', 'login' => 'daniela.duarte'],
            ['role' => 'gerente', 'login' => 'marcio.vale'],
            ['role' => 'gerente', 'login' => 'debora.silva'],
            ['role' => 'gerente', 'login' => 'claudia.machado'],
            
            ['role' => 'usuario', 'login' => 'heitor.pereira'],
            ['role' => 'usuario', 'login' => 'priscila'],
            ['role' => 'usuario', 'login' => 'thais.silverio'],
            ['role' => 'usuario', 'login' => 'danyella.domingues'],
            ['role' => 'usuario', 'login' => 'gabriel.dias'],
            ['role' => 'usuario', 'login' => 'samuel.goes'],
            
            ['role' => 'fiscal', 'login' => 'alberto.moreira',  'fiscal_id' => 53],
            ['role' => 'fiscal', 'login' => 'alexandre.leal',   'fiscal_id' => 37],
            ['role' => 'fiscal', 'login' => 'aline.fachetti',   'fiscal_id' => 44],
            ['role' => 'fiscal', 'login' => 'andre.silva',      'fiscal_id' => 33],
            ['role' => 'fiscal', 'login' => 'andreza.kelly',    'fiscal_id' => 35],
            ['role' => 'fiscal', 'login' => 'bruno.souto',      'fiscal_id' => 54],
            ['role' => 'fiscal', 'login' => 'gean.alves',       'fiscal_id' => 65],
            ['role' => 'fiscal', 'login' => 'isadora.alves',    'fiscal_id' => 48],
            ['role' => 'fiscal', 'login' => 'ivan.stoupa',      'fiscal_id' => 40],
            ['role' => 'fiscal', 'login' => 'julian.costa',     'fiscal_id' => 52],
            ['role' => 'fiscal', 'login' => 'juliana.bastos',   'fiscal_id' => 64],
            ['role' => 'fiscal', 'login' => 'leonardo.correa',  'fiscal_id' => 46],
            ['role' => 'fiscal', 'login' => 'lilia.ferreira',   'fiscal_id' => 55],
            ['role' => 'fiscal', 'login' => 'luana.silva',      'fiscal_id' => 49],
            ['role' => 'fiscal', 'login' => 'luana.ferreira',   'fiscal_id' => 47],
            ['role' => 'fiscal', 'login' => 'lucas.arantes',    'fiscal_id' => 51],
            ['role' => 'fiscal', 'login' => 'maryellen.almeida','fiscal_id' => 67],
            ['role' => 'fiscal', 'login' => 'marina.xavier',    'fiscal_id' => 68],
            ['role' => 'fiscal', 'login' => 'nadyne.almeida',   'fiscal_id' => 69],
            ['role' => 'fiscal', 'login' => 'natalia.silva',    'fiscal_id' => 66],
            ['role' => 'fiscal', 'login' => 'osmar.agostini',   'fiscal_id' => 36],
            ['role' => 'fiscal', 'login' => 'renata.oliveira',  'fiscal_id' => 57],
            ['role' => 'fiscal', 'login' => 'rodrigo.mota',     'fiscal_id' => 30],
            ['role' => 'fiscal', 'login' => 'ricardo.borges',   'fiscal_id' => 16],
            ['role' => 'fiscal', 'login' => 'wilson.coimbra',   'fiscal_id' => 15],
        ];

        $usuarioModel = new UsuarioModel();
        $usuarioModel->skipValidation(true);

        foreach ($logins as $item) {
            $role  = $item['role'];
            $login = $item['login'];
            echo "Consultando LDAP para {$login}" . PHP_EOL;
            $response = $this->consultarLdap($login);
            if (! $response || ($response['type'] ?? '') === 'error') {
                echo "LDAP falhou para {$login}: " . ($response['message'] ?? 'sem resposta') . PHP_EOL;
                continue;
            }

            $nome  = $response['dados']['nome'] ?? $login;
            $email = $response['dado']['email'] ?? $login . '@crfmg.org.br';

            $user = $usuarioModel->where('login', $response['dados']['usuario'] ?? $login)->first();
            $dados = [
                'nome'   => $nome,
                'email'  => $email,
                'login'  => $response['dados']['usuario'] ?? $login,
                'role'   => $role ?? 'usuario',
                'ativo'  => 1,
            ];
            if (! empty($user['id'])) {
                $dados['id'] = $user['id'];
            }

            $usuarioModel->save($dados);
            echo "OK: {$login}" . PHP_EOL;
        }
    }

    private function consultarLdap(string $login): ?array
    {
        sleep(1); // 1 segundo
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['usuario' => $login],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        if ($response === false) {
            return null;
        }
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }
}
