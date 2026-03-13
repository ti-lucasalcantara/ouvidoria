<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index', ['as' => 'home.index']);

// SSO
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->get('sair', 'Auth::sair', ['as' => 'sair']);
$routes->get('auth', 'Auth::sso');

// Ouvidoria
$routes->group('ouvidoria', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index', ['as' => 'ouvidoria.dashboard']);

    // Manifestações
    $routes->get('manifestacoes', 'ManifestacoesController::index', ['as' => 'ouvidoria.manifestacoes.index']);
    $routes->get('manifestacoes/create', 'ManifestacoesController::create', ['as' => 'ouvidoria.manifestacoes.create']);
    $routes->post('manifestacoes/store', 'ManifestacoesController::store', ['as' => 'ouvidoria.manifestacoes.store']);
    $routes->get('manifestacoes/show/(:num)', 'ManifestacoesController::show/$1', ['as' => 'ouvidoria.manifestacoes.show']);
    $routes->get('manifestacoes/edit/(:num)', 'ManifestacoesController::edit/$1', ['as' => 'ouvidoria.manifestacoes.edit']);
    $routes->post('manifestacoes/update/(:num)', 'ManifestacoesController::update/$1', ['as' => 'ouvidoria.manifestacoes.update']);
    $routes->post('manifestacoes/updateInscricaoPj/(:num)', 'ManifestacoesController::updateInscricaoPj/$1', ['as' => 'ouvidoria.manifestacoes.updateInscricaoPj']);
    $routes->post('manifestacoes/updateStatus/(:num)', 'ManifestacoesController::updateStatus/$1', ['as' => 'ouvidoria.manifestacoes.updateStatus']);
    $routes->post('manifestacoes/encaminhar/(:num)', 'ManifestacoesController::encaminhar/$1', ['as' => 'ouvidoria.manifestacoes.encaminhar']);
    $routes->post('manifestacoes/devolver/(:num)', 'ManifestacoesController::devolver/$1', ['as' => 'ouvidoria.manifestacoes.devolver']);
    $routes->post('manifestacoes/voltar/(:num)', 'ManifestacoesController::voltar/$1', ['as' => 'ouvidoria.manifestacoes.voltar']);
    $routes->post('manifestacoes/reabrir/(:num)', 'ManifestacoesController::reabrir/$1', ['as' => 'ouvidoria.manifestacoes.reabrir']);
    $routes->post('manifestacoes/comentar/(:num)', 'ManifestacoesController::comentar/$1', ['as' => 'ouvidoria.manifestacoes.comentar']);
    $routes->post('manifestacoes/editarComentario/(:num)', 'ManifestacoesController::editarComentario/$1', ['as' => 'ouvidoria.manifestacoes.editarComentario']);
    $routes->post('manifestacoes/excluirComentario/(:num)', 'ManifestacoesController::excluirComentario/$1', ['as' => 'ouvidoria.manifestacoes.excluirComentario']);
    $routes->post('manifestacoes/editarAtribuicao/(:num)', 'ManifestacoesController::editarAtribuicao/$1', ['as' => 'ouvidoria.manifestacoes.editarAtribuicao']);
    $routes->post('manifestacoes/excluirAtribuicao/(:num)', 'ManifestacoesController::excluirAtribuicao/$1', ['as' => 'ouvidoria.manifestacoes.excluirAtribuicao']);
    $routes->post('manifestacoes/excluirAnexo/(:num)', 'ManifestacoesController::excluirAnexo/$1', ['as' => 'ouvidoria.manifestacoes.excluirAnexo']);
    $routes->post('manifestacoes/responderOuvidor/(:num)', 'ManifestacoesController::responderOuvidor/$1', ['as' => 'ouvidoria.manifestacoes.responderOuvidor']);
    $routes->post('manifestacoes/editarRespostaOuvidor/(:num)', 'ManifestacoesController::editarRespostaOuvidor/$1', ['as' => 'ouvidoria.manifestacoes.editarRespostaOuvidor']);
    $routes->post('manifestacoes/excluirRespostaOuvidor/(:num)', 'ManifestacoesController::excluirRespostaOuvidor/$1', ['as' => 'ouvidoria.manifestacoes.excluirRespostaOuvidor']);

    // Anexos (download e abrir no navegador)
    $routes->get('anexos/download/(:num)', 'AnexosController::download/$1', ['as' => 'ouvidoria.anexos.download']);
    $routes->get('anexos/abrir/(:num)', 'AnexosController::abrir/$1', ['as' => 'ouvidoria.anexos.abrir']);
    $routes->get('anexos/resposta/abrir/(:num)', 'AnexosController::respostaAbrir/$1', ['as' => 'ouvidoria.anexos.respostaAbrir']);
    $routes->get('anexos/resposta/download/(:num)', 'AnexosController::respostaDownload/$1', ['as' => 'ouvidoria.anexos.respostaDownload']);

    // Setores (admin)
    $routes->get('setores', 'SetoresController::index', ['as' => 'ouvidoria.setores.index']);
    $routes->get('setores/create', 'SetoresController::create', ['as' => 'ouvidoria.setores.create']);
    $routes->post('setores/store', 'SetoresController::store', ['as' => 'ouvidoria.setores.store']);
    $routes->get('setores/edit/(:num)', 'SetoresController::edit/$1', ['as' => 'ouvidoria.setores.edit']);
    $routes->post('setores/update/(:num)', 'SetoresController::update/$1', ['as' => 'ouvidoria.setores.update']);
    $routes->get('setores/delete/(:num)', 'SetoresController::delete/$1', ['as' => 'ouvidoria.setores.delete']);

    // Categorias da Manifestação (ouvidor + admin)
    $routes->get('categorias-manifestacao', 'CategoriasManifestacaoController::index', ['as' => 'ouvidoria.categoriasManifestacao.index']);
    $routes->get('categorias-manifestacao/create', 'CategoriasManifestacaoController::create', ['as' => 'ouvidoria.categoriasManifestacao.create']);
    $routes->post('categorias-manifestacao/store', 'CategoriasManifestacaoController::store', ['as' => 'ouvidoria.categoriasManifestacao.store']);
    $routes->get('categorias-manifestacao/edit/(:num)', 'CategoriasManifestacaoController::edit/$1', ['as' => 'ouvidoria.categoriasManifestacao.edit']);
    $routes->post('categorias-manifestacao/update/(:num)', 'CategoriasManifestacaoController::update/$1', ['as' => 'ouvidoria.categoriasManifestacao.update']);
    $routes->get('categorias-manifestacao/delete/(:num)', 'CategoriasManifestacaoController::delete/$1', ['as' => 'ouvidoria.categoriasManifestacao.delete']);

    // Usuários (admin)
    $routes->get('usuarios', 'UsuariosController::index', ['as' => 'ouvidoria.usuarios.index']);
    $routes->get('usuarios/create', 'UsuariosController::create', ['as' => 'ouvidoria.usuarios.create']);
    $routes->post('usuarios/store', 'UsuariosController::store', ['as' => 'ouvidoria.usuarios.store']);
    $routes->get('usuarios/edit/(:num)', 'UsuariosController::edit/$1', ['as' => 'ouvidoria.usuarios.edit']);
    $routes->post('usuarios/update/(:num)', 'UsuariosController::update/$1', ['as' => 'ouvidoria.usuarios.update']);
    $routes->get('usuarios/delete/(:num)', 'UsuariosController::delete/$1', ['as' => 'ouvidoria.usuarios.delete']);
});