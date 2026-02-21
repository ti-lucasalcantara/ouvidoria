<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Setores - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-sitemap me-2"></i>Setores</h1>
    <a href="<?= url_to('ouvidoria.setores.create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo Setor</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th width="120"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($setores as $s): ?>
                    <tr>
                        <td><?= esc($s['nome']) ?></td>
                        <td><span class="badge bg-<?= $s['ativo'] ? 'success' : 'secondary' ?>"><?= $s['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        <td>
                            <a href="<?= url_to('ouvidoria.setores.edit', $s['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <a href="<?= url_to('ouvidoria.setores.delete', $s['id']) ?>" class="btn btn-sm btn-outline-danger link-excluir-swal" data-mensagem="Excluir este setor?"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($setores)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Nenhum setor cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
