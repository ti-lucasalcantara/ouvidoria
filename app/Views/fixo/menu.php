<?php
if (!isset($menu_ativo)) {
    $menu_ativo = '';
}
helper('ouvidoria');
$usuario = obterUsuarioLogado();
$role = $usuario['role'] ?? 'usuario';
?>
<style>
    .menu-titulo span {
        color: #069;
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
    }
</style>
<ul class="nav flex-column">
    <li class="nav-item mb-2">
        <a class="nav-link <?= $menu_ativo == 'ouvidoria.dashboard' ? 'active_menu' : '' ?>" href="<?= url_to('ouvidoria.dashboard') ?>">
            <i class="fas fa-chart-line me-2"></i>Dashboard Ouvidoria
        </a>
    </li>

    <li class="nav-item mt-2">
        <a class="nav-link d-flex justify-content-between align-items-center menu-titulo" data-bs-toggle="collapse" href="#menuOuvidoria" role="button" aria-expanded="true" aria-controls="menuOuvidoria">
            <span><i class="fas fa-folder-open me-2"></i>Ouvidoria</span>
            <i class="fas fa-chevron-down small"></i>
        </a>
        <div class="collapse show ps-3" id="menuOuvidoria">
            <a class="nav-link <?= $menu_ativo == 'ouvidoria.manifestacoes.index' ? 'active_menu' : '' ?>" href="<?= url_to('ouvidoria.manifestacoes.index') ?>"><i class="fas fa-list me-2"></i>Manifestações</a>
            <?php if (in_array($role, ['administrador', 'ouvidor'])): ?>
            <a class="nav-link <?= $menu_ativo == 'ouvidoria.manifestacoes.create' ? 'active_menu' : '' ?>" href="<?= url_to('ouvidoria.manifestacoes.create') ?>"><i class="fas fa-plus me-2"></i>Nova Manifestação</a>
            <?php endif; ?>
            <?php if ($role === 'administrador'): ?>
            <a class="nav-link <?= $menu_ativo == 'ouvidoria.setores.index' ? 'active_menu' : '' ?>" href="<?= url_to('ouvidoria.setores.index') ?>"><i class="fas fa-sitemap me-2"></i>Setores</a>
            <a class="nav-link <?= $menu_ativo == 'ouvidoria.usuarios.index' ? 'active_menu' : '' ?>" href="<?= url_to('ouvidoria.usuarios.index') ?>"><i class="fas fa-users me-2"></i>Usuários</a>
            <?php endif; ?>
        </div>
    </li>
</ul>
