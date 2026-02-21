<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de role/perfil.
 * Verifica se usuário logado possui um dos roles permitidos.
 * Uso: ['filter' => 'role:administrador,ouvidor']
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('ouvidoria', 'menssagens');
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!', 'text' => 'Faça login para acessar.']));
            return redirect()->to(site_url('login'));
        }

        $rolesPermitidos = $arguments ? explode(',', $arguments[0]) : [];
        $roleUsuario = $usuario['role'] ?? '';

        if (!empty($rolesPermitidos) && !in_array($roleUsuario, $rolesPermitidos)) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!', 'text' => 'Você não tem permissão para acessar este recurso.']));
            return redirect()->back();
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
