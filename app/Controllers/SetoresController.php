<?php

namespace App\Controllers;

use App\Models\SetorModel;

/**
 * Controller de Setores.
 * CRUD somente para administrador.
 */
class SetoresController extends BaseController
{
    protected $helpers = ['ouvidoria', 'form', 'url'];

    /**
     * Listagem de setores.
     */
    public function index()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarSetores($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!']));
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        $setores = $setorModel->orderBy('nome')->findAll();

        return view('ouvidoria/setores/index', ['setores' => $setores]);
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarSetores($usuario ?? [])) {
            return redirect()->back();
        }

        return view('ouvidoria/setores/form', ['setor' => null]);
    }

    /**
     * Salva novo setor.
     */
    public function store()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarSetores($usuario ?? [])) {
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        if (!$setorModel->save($this->request->getPost())) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $setorModel->errors())]));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Setor cadastrado.']));
        return redirect()->to(site_url('ouvidoria/setores'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarSetores($usuario ?? [])) {
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        $setor = $setorModel->find($id);
        if (!$setor) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Setor não encontrado.']));
            return redirect()->back();
        }

        return view('ouvidoria/setores/form', ['setor' => $setor]);
    }

    /**
     * Atualiza setor.
     */
    public function update(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarSetores($usuario ?? [])) {
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        $dados = $this->request->getPost();
        $dados['id'] = $id;

        if (!$setorModel->save($dados)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $setorModel->errors())]));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Setor atualizado.']));
        return redirect()->to(site_url('ouvidoria/setores'));
    }

    /**
     * Exclui setor.
     */
    public function delete(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarSetores($usuario ?? [])) {
            return redirect()->back();
        }

        $setorModel = model(SetorModel::class);
        $setorModel->delete($id);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Setor excluído.']));
        return redirect()->to(site_url('ouvidoria/setores'));
    }
}
