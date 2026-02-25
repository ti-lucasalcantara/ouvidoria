<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\SetorModel;

/**
 * Controller de Usuários.
 * CRUD somente para administrador.
 */
class UsuariosController extends BaseController
{
    protected $helpers = ['ouvidoria', 'form', 'url'];

    /**
     * Listagem de usuários.
     */
    public function index()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarUsuarios($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!']));
            return redirect()->back();
        }

        $usuarioModel = model(UsuarioModel::class);
        $usuarios = $usuarioModel->select('usuarios.*, setores.nome as setor_nome')
            ->join('setores', 'setores.id = usuarios.setor_id', 'left')
            ->orderBy('usuarios.nome')
            ->findAll();

        return view('ouvidoria/usuarios/index', ['usuarios' => $usuarios]);
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarUsuarios($usuario ?? [])) {
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        $setores = $setorModel->ativos()->findAll();

        return view('ouvidoria/usuarios/form', ['usuario' => null, 'setores' => $setores]);
    }

    /**
     * Salva novo usuário.
     */
    public function store()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarUsuarios($usuario ?? [])) {
            return redirect()->back();
        }

        $usuarioModel = model(UsuarioModel::class);
        $dados = $this->request->getPost();
        $dados['ativo'] = $dados['ativo'] ?? 1;

        $regras = ['nome' => 'required', 'email' => 'required|valid_email|is_unique[usuarios.email]', 'role' => 'required|in_list[administrador,ouvidor,gerente,usuario,fiscal]'];
        if (!$this->validate($regras)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $this->validator->getErrors())]));
            return redirect()->back()->withInput();
        }

        if (!$usuarioModel->save($dados)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $usuarioModel->errors())]));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Usuário cadastrado.']));
        return redirect()->to(site_url('ouvidoria/usuarios'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarUsuarios($usuario ?? [])) {
            return redirect()->back();
        }

        $usuarioModel = model(UsuarioModel::class);
        $usuarioEdit = $usuarioModel->find($id);
        if (!$usuarioEdit) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Usuário não encontrado.']));
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        $setores = $setorModel->ativos()->findAll();

        return view('ouvidoria/usuarios/form', ['usuario' => $usuarioEdit, 'setores' => $setores]);
    }

    /**
     * Atualiza usuário.
     */
    public function update(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarUsuarios($usuario ?? [])) {
            return redirect()->back();
        }

        $usuarioModel = model(UsuarioModel::class);
        $dados = $this->request->getPost();
        $dados['id'] = $id;
        $dados['ativo'] = $dados['ativo'] ?? 0;

        if (!$usuarioModel->save($dados)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $usuarioModel->errors())]));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Usuário atualizado.']));
        return redirect()->to(site_url('ouvidoria/usuarios'));
    }

    /**
     * Exclui usuário.
     */
    public function delete(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarUsuarios($usuario ?? [])) {
            return redirect()->back();
        }

        $usuarioModel = model(UsuarioModel::class);
        $usuarioModel->delete($id);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Usuário excluído.']));
        return redirect()->to(site_url('ouvidoria/usuarios'));
    }
}
