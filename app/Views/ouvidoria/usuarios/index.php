<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Usuários - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-users me-2"></i>Usuários</h1>
    <a href="<?= url_to('ouvidoria.usuarios.create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo Usuário</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Setor</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th width="120"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= esc($u['nome']) ?></td>
                        <td><?= esc($u['email']) ?></td>
                        <td><?= esc($u['setor_nome'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= esc($u['role']) ?></span></td>
                        <td><span class="badge bg-<?= $u['ativo'] ? 'success' : 'secondary' ?>"><?= $u['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        <td>
                            <a href="<?= url_to('ouvidoria.usuarios.edit', $u['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <a href="<?= url_to('ouvidoria.usuarios.delete', $u['id']) ?>" class="btn btn-sm btn-outline-danger link-excluir-swal" data-mensagem="Excluir este usuário?"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
