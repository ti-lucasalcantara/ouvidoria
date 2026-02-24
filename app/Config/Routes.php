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

    // Anexos (download e abrir no navegador)
    $routes->get('anexos/download/(:num)', 'AnexosController::download/$1', ['as' => 'ouvidoria.anexos.download']);
    $routes->get('anexos/abrir/(:num)', 'AnexosController::abrir/$1', ['as' => 'ouvidoria.anexos.abrir']);

    // Setores (admin)
    $routes->get('setores', 'SetoresController::index', ['as' => 'ouvidoria.setores.index']);
    $routes->get('setores/create', 'SetoresController::create', ['as' => 'ouvidoria.setores.create']);
    $routes->post('setores/store', 'SetoresController::store', ['as' => 'ouvidoria.setores.store']);
    $routes->get('setores/edit/(:num)', 'SetoresController::edit/$1', ['as' => 'ouvidoria.setores.edit']);
    $routes->post('setores/update/(:num)', 'SetoresController::update/$1', ['as' => 'ouvidoria.setores.update']);
    $routes->get('setores/delete/(:num)', 'SetoresController::delete/$1', ['as' => 'ouvidoria.setores.delete']);

    // Usuários (admin)
    $routes->get('usuarios', 'UsuariosController::index', ['as' => 'ouvidoria.usuarios.index']);
    $routes->get('usuarios/create', 'UsuariosController::create', ['as' => 'ouvidoria.usuarios.create']);
    $routes->post('usuarios/store', 'UsuariosController::store', ['as' => 'ouvidoria.usuarios.store']);
    $routes->get('usuarios/edit/(:num)', 'UsuariosController::edit/$1', ['as' => 'ouvidoria.usuarios.edit']);
    $routes->post('usuarios/update/(:num)', 'UsuariosController::update/$1', ['as' => 'ouvidoria.usuarios.update']);
    $routes->get('usuarios/delete/(:num)', 'UsuariosController::delete/$1', ['as' => 'ouvidoria.usuarios.delete']);
});