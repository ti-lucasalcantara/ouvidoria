<?php

namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UsuarioModel;

class Auth extends BaseController
{
    private $dados;

    public function login()
    {
        $redirectTo = $this->request->getGet('redirect_to') ?? '';
        if($redirectTo == 'sair'){
            return redirect()->to( getenv('sso.login_url') );
        }
        return redirect()->to( getenv('sso.login_url') . '?redirect='. urlencode(base64_encode(base_url('auth'))) .'&url_to=' .  urlencode(base64_encode($redirectTo)) );
    }

    public function sair()
    {
        session()->destroy();
        return redirect()->route('home.index');
    }

    public function sso()
    {
        $token = $this->request->getGet('token');
        $url_to = $this->request->getGet('url_to') ?? '';
        
        /* 
        $usuarioModel = new UsuarioModel();
        $user = $usuarioModel->where('login', 'rodrigo.mota')->first();
        $session = [
            'usuario_logado'  => $user
        ];
        session()->set($session);
        if($url_to){
            return redirect()->to(site_url( $url_to ));
        }
        return redirect()->route('home.index');
       */

        if (!$token) {
            session()->setFlashdata( getMessageFail('toast', ['title' => 'Acesso negado!', 'text' => 'Token ausente.']) );
            return redirect()->route('login');
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('jwt.secret'), 'HS256'));

            $decodedArray = json_decode(json_encode($decoded), true);

            if ($decodedArray['exp'] < time()) {
                throw new \Exception('Token expirado');
            }

            $usuarioModel = new UsuarioModel();

            $user = $usuarioModel->where('login', $decodedArray['user']['login'])->first();
            $dados['role']  = $decodedArray['user']['role'] ?? 'usuario';
            $dados['email'] = $decodedArray['user']['email'] ?? $decodedArray['user']['login']."@crfmg.org.br";
            $dados['ativo'] = 1;
            $dados['nome']  = $decodedArray['user']['nome'];
            $dados['login'] = $decodedArray['user']['login'];
            
            if (!empty($user)) {

                if ($user['ativo'] != 1) {
                    session()->setFlashdata(getMessageFail('sweetalert', ['title' => 'Falha no login', 'text' => 'Usuário sem permissão de acesso']));
                    return redirect()->back()->withInput();
                }

                $dados['id']    = $user['id'];
                $dados['role']  = $user['role'];
                $dados['email'] = $user['email'];
            }
           
          
            if (!$usuarioModel->save($dados)) {
                session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $usuarioModel->errors())]));
                return redirect()->back()->withInput();
            }
            
            $session = [
                'usuario_logado'  => $dados
            ];

            // Cria sessão local
            session()->set($session);

            if($url_to){
                return redirect()->to(site_url( $url_to ));
            }

            return redirect()->route('home.index');

        } catch (\Exception $e) {
            session()->setFlashdata( getMessageFail('toast', ['title' => 'Acesso negado!', 'text' => 'Token inválido ou expirado']) );
            return redirect()->route('login');
        }
    }

    /**
     * Sincroniza usuário da sessão com a tabela usuarios.
     * Se não existir, cadastra com perfil padrão 'usuario'.
     */
    private function sincronizarUsuario(array $user): void
    {
        helper('ouvidoria');
        $email = obterEmailDoUsuario($user);
        if (!$email) {
            return;
        }

        sincronizarUsuarioNaTabela($user, $email);
    }
}