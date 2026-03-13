<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Categorias da Manifestação - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-tags me-2"></i>Categorias da Manifestação</h1>
    <a href="<?= url_to('ouvidoria.categoriasManifestacao.create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nova Categoria</a>
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
                    <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td><?= esc($c['nome']) ?></td>
                        <td><span class="badge bg-<?= $c['ativo'] ? 'success' : 'secondary' ?>"><?= $c['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        <td>
                            <a href="<?= url_to('ouvidoria.categoriasManifestacao.edit', $c['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <a href="<?= url_to('ouvidoria.categoriasManifestacao.delete', $c['id']) ?>" class="btn btn-sm btn-outline-danger link-excluir-swal" data-mensagem="Excluir esta categoria?"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categorias)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
