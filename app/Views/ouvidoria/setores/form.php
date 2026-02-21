<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
<?= $setor ? 'Editar' : 'Novo' ?> Setor - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-sitemap me-2"></i><?= $setor ? 'Editar' : 'Novo' ?> Setor</h1>
    <a href="<?= url_to('ouvidoria.setores.index') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
</div>

<?= form_open($setor ? url_to('ouvidoria.setores.update', $setor['id']) : url_to('ouvidoria.setores.store')) ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" value="<?= esc(old('nome', $setor['nome'] ?? '')) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Ativo</label>
                <select name="ativo" class="form-select">
                    <option value="1" <?= ($setor['ativo'] ?? 1) ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= !($setor['ativo'] ?? 1) ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
        <a href="<?= url_to('ouvidoria.setores.index') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
