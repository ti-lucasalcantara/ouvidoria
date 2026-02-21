<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= $this->renderSection('titulo') ?? 'CRFMG' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="<?= base_url('assets/bootstrap-5.0.2/dist/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/fontawesome-free-6.7.2-web/css/fontawesome.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/fontawesome-free-6.7.2-web/css/brands.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/fontawesome-free-6.7.2-web/css/solid.css') ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/fonts/SpaceGrotesk/fonts.css') ?>" rel="stylesheet">
  <link href="<?= base_url('assets/plugins/select2-4.1.0/dist/css/select2.min.css') ?>" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Space Grotesk', sans-serif;
      background-color: #f1f4f9;
    }

    .navbar {
      background-color: #0056b3;
      color: white;
    }

    .navbar-brand {
      color: white;
      font-weight: bold;
      font-size: 1.4rem;
    }

    .navbar-brand:hover {
      color: #e2e6ea;
    }

    .avatar {
      width: 42px;
      height: 42px;
      object-fit: cover;
      border-radius: 50%;
      transition: transform 0.2s ease-in-out;
    }

    .avatar:hover {
      transform: scale(1.05);
    }

    .sidebar-menu a {
      color: #333;
      font-weight: 500;
      transition: background 0.2s;
    }

    .sidebar-menu a:hover {
      background-color: #e9ecef;
      border-radius: 0.3rem;
    }

    .content {
      margin-top: 80px;
      padding: 2rem;
    }

    .nav-link.active_menu {
      background-color: #e9f2ff;     
      color: #0d6efd !important;     
      font-weight: 600;
      border-radius: 0.375rem;
    }

    .nav-link.active_menu i {
      color: #0d6efd !important;
    }

    @media (min-width: 992px) {
      .ms-lg-sidebar {
        margin-left: 250px !important;
      }
    }
  </style>
  <?= $this->renderSection('css') ?>

</head>
<body>

<!-- Topbar -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm px-3">
  <div class="container-fluid">
    <!-- Botão para abrir menu em mobile -->
    <button class="btn btn-light d-lg-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
      <i class="fas fa-bars"></i>
    </button>

    <a class="navbar-brand" href="<?=url_to('home.index')?>">Gestão de Ouvidoria - CRF/MG</a>

    <div class="dropdown ms-auto">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
      <?=renderAvatar()?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="#">Meu Perfil</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="<?=url_to('sair')?>">Sair</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar Offcanvas (mobile) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body sidebar-menu">
    <ul class="nav flex-column">
      <?=$this->include('fixo/menu')?>
    </ul>
  </div>
</div>

<!-- Sidebar Desktop -->
<div class="d-none d-lg-block bg-white border-end position-fixed" style="top: 56px; bottom: 0; width: 250px; padding-top: 1rem;">
  <div class="sidebar-menu px-3">
    <ul class="nav flex-column">
        <?=$this->include('fixo/menu')?>
    </ul>
  </div>
</div>

<!-- Conteúdo dinâmico -->
<main class="content ms-0 ms-lg-sidebar">
  <div class="container-fluid">
    <?= $this->renderSection('content') ?>
  </div>
</main>

<script src="<?= base_url('assets/js/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= base_url('assets/bootstrap-5.0.2/dist/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2-4.1.0/dist/js/select2.min.js') ?>"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<?= $this->include('_componentes/toast') ?>
<?= $this->include('_componentes/sweet-alert') ?>
<?= $this->include('_componentes/modal-excluir') ?>

<?= $this->renderSection('js') ?>
</body>
</html>
