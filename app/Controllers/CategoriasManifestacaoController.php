<?php

namespace App\Controllers;

use App\Models\CategoriaManifestacaoModel;

/**
 * Controller de Categorias da Manifestação.
 * CRUD somente para administrador e ouvidor.
 */
class CategoriasManifestacaoController extends BaseController
{
    protected $helpers = ['ouvidoria', 'form', 'url'];

    public function index()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarCategoriasManifestacao($usuario ?? [])) {
            session()->setFlashdata(getMessageFail('toast', ['title' => 'Acesso negado!']));
            return redirect()->back();
        }

        $model = model(CategoriaManifestacaoModel::class);
        $categorias = $model->orderBy('nome')->findAll();

        return view('ouvidoria/categorias_manifestacao/index', ['categorias' => $categorias]);
    }

    public function create()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarCategoriasManifestacao($usuario ?? [])) {
            return redirect()->back();
        }

        return view('ouvidoria/categorias_manifestacao/form', ['categoria' => null]);
    }

    public function store()
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarCategoriasManifestacao($usuario ?? [])) {
            return redirect()->back();
        }

        $model = model(CategoriaManifestacaoModel::class);
        if (!$model->save($this->request->getPost())) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $model->errors())]));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Categoria cadastrada.']));
        return redirect()->to(site_url('ouvidoria/categorias-manifestacao'));
    }

    public function edit(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarCategoriasManifestacao($usuario ?? [])) {
            return redirect()->back();
        }

        $model = model(CategoriaManifestacaoModel::class);
        $categoria = $model->find($id);
        if (!$categoria) {
            session()->setFlashdata(getMessageFail('toast', ['text' => 'Categoria não encontrada.']));
            return redirect()->back();
        }

        return view('ouvidoria/categorias_manifestacao/form', ['categoria' => $categoria]);
    }

    public function update(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarCategoriasManifestacao($usuario ?? [])) {
            return redirect()->back();
        }

        $model = model(CategoriaManifestacaoModel::class);
        $dados = $this->request->getPost();
        $dados['id'] = $id;

        if (!$model->save($dados)) {
            session()->setFlashdata(getMessageFail('toast', ['text' => implode(' ', $model->errors())]));
            return redirect()->back()->withInput();
        }

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Categoria atualizada.']));
        return redirect()->to(site_url('ouvidoria/categorias-manifestacao'));
    }

    public function delete(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!service('authorization')->podeGerenciarCategoriasManifestacao($usuario ?? [])) {
            return redirect()->back();
        }

        $model = model(CategoriaManifestacaoModel::class);
        $model->delete($id);

        session()->setFlashdata(getMessageSucess('toast', ['text' => 'Categoria excluída.']));
        return redirect()->to(site_url('ouvidoria/categorias-manifestacao'));
    }
}
