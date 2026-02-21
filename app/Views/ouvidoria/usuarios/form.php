<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
<?= $usuario ? 'Editar' : 'Novo' ?> Usuário - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-user me-2"></i><?= $usuario ? 'Editar' : 'Novo' ?> Usuário</h1>
    <a href="<?= url_to('ouvidoria.usuarios.index') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
</div>

<?= form_open($usuario ? url_to('ouvidoria.usuarios.update', $usuario['id']) : url_to('ouvidoria.usuarios.store')) ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" value="<?= esc(old('nome', $usuario['nome'] ?? '')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email', $usuario['email'] ?? '')) ?>" <?= $usuario ? 'readonly' : '' ?> required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Login</label>
                <input type="text" name="login" class="form-control" value="<?= esc(old('login', $usuario['login'] ?? '')) ?>" placeholder="Opcional">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Setor</label>
                <select name="setor_id" class="form-select select2">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($setores as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($usuario['setor_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= esc($s['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Perfil <span class="text-danger">*</span></label>
                <select name="role" class="form-select">
                    <option value="usuario" <?= ($usuario['role'] ?? '') === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                    <option value="gerente" <?= ($usuario['role'] ?? '') === 'gerente' ? 'selected' : '' ?>>Gerente</option>
                    <option value="ouvidor" <?= ($usuario['role'] ?? '') === 'ouvidor' ? 'selected' : '' ?>>Ouvidor</option>
                    <option value="administrador" <?= ($usuario['role'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Ativo</label>
                <select name="ativo" class="form-select">
                    <option value="1" <?= ($usuario['ativo'] ?? 1) ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= !($usuario['ativo'] ?? 1) ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
        <a href="<?= url_to('ouvidoria.usuarios.index') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
$(function() { $('.select2').select2({ width: '100%' }); });
</script>
<?= $this->endSection() ?>
