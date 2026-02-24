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
            
            ['role' => 'usuario', 'login' => 'alberto.moreira'],
            ['role' => 'usuario', 'login' => 'alexandre.leal'],
            ['role' => 'usuario', 'login' => 'aline.fachetti'],
            ['role' => 'usuario', 'login' => 'andre.silva'],
            ['role' => 'usuario', 'login' => 'andreza.kelly'],
            ['role' => 'usuario', 'login' => 'bruno.souto'],
            ['role' => 'usuario', 'login' => 'gean.alves'],
            ['role' => 'usuario', 'login' => 'isadora.alves'],
            ['role' => 'usuario', 'login' => 'ivan.stoupa'],
            ['role' => 'usuario', 'login' => 'julian.costa'],
            ['role' => 'usuario', 'login' => 'juliana.bastos'],
            ['role' => 'usuario', 'login' => 'leonardo.correa'],
            ['role' => 'usuario', 'login' => 'lilia.ferreira'],
            ['role' => 'usuario', 'login' => 'luana.silva'],
            ['role' => 'usuario', 'login' => 'luana.ferreira'],
            ['role' => 'usuario', 'login' => 'lucas.arantes'],
            ['role' => 'usuario', 'login' => 'maryellen.almeida'],
            ['role' => 'usuario', 'login' => 'marina.xavier'],
            ['role' => 'usuario', 'login' => 'nadyne.almeida'],
            ['role' => 'usuario', 'login' => 'natalia.silva'],
            ['role' => 'usuario', 'login' => 'osmar.agostini'],
            ['role' => 'usuario', 'login' => 'renata.oliveira'],
            ['role' => 'usuario', 'login' => 'rodrigo.mota'],
            ['role' => 'usuario', 'login' => 'ricardo.borges'],
            ['role' => 'usuario', 'login' => 'wilson.coimbra'],
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
