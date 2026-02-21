<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Manifestações - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
.table td.text-break { max-width: 280px; word-wrap: break-word; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-folder-open me-2"></i>Manifestações</h1>
    <?php 
    $auth = $authService ?? service('authorization');
    if ($auth->podeCriarManifestacao(obterUsuarioLogado() ?? [])): ?>
    <a href="<?= url_to('ouvidoria.manifestacoes.create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nova Manifestação</a>
    <?php endif; ?>
</div>

<form method="get" class="mb-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h6 class="text-muted mb-3"><i class="fas fa-filter me-1"></i>Filtros</h6>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">Protocolo</label>
                    <input type="text" name="protocolo" class="form-control form-control-sm" value="<?= esc($_GET['protocolo'] ?? '') ?>" placeholder="Buscar por protocolo">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">Prioridade</label>
                    <select name="prioridade" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="baixa" <?= (isset($_GET['prioridade']) && $_GET['prioridade'] === 'baixa') ? 'selected' : '' ?>>Baixa</option>
                        <option value="media" <?= (isset($_GET['prioridade']) && $_GET['prioridade'] === 'media') ? 'selected' : '' ?>>Média</option>
                        <option value="alta" <?= (isset($_GET['prioridade']) && $_GET['prioridade'] === 'alta') ? 'selected' : '' ?>>Alta</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="recebida" <?= (isset($_GET['status']) && $_GET['status'] === 'recebida') ? 'selected' : '' ?>>Recebida</option>
                        <option value="encaminhada" <?= (isset($_GET['status']) && $_GET['status'] === 'encaminhada') ? 'selected' : '' ?>>Encaminhada</option>
                        <option value="em_atendimento" <?= (isset($_GET['status']) && $_GET['status'] === 'em_atendimento') ? 'selected' : '' ?>>Em atendimento</option>
                        <option value="respondida" <?= (isset($_GET['status']) && $_GET['status'] === 'respondida') ? 'selected' : '' ?>>Respondida</option>
                        <option value="finalizada" <?= (isset($_GET['status']) && $_GET['status'] === 'finalizada') ? 'selected' : '' ?>>Finalizada</option>
                        <option value="arquivada" <?= (isset($_GET['status']) && $_GET['status'] === 'arquivada') ? 'selected' : '' ?>>Arquivada</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">Data início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= esc($_GET['data_inicio'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">Data fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= esc($_GET['data_fim'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search me-1"></i>Filtrar</button>
                </div>
                <?php if (!empty($_GET['protocolo'] ?? '') || !empty($_GET['status'] ?? '') || !empty($_GET['prioridade'] ?? '') || !empty($_GET['data_inicio'] ?? '') || !empty($_GET['data_fim'] ?? '')): ?>
                <div class="col-12 col-md-1">
                    <a href="<?= url_to('ouvidoria.manifestacoes.index') ?>" class="btn btn-sm btn-outline-secondary w-100">Limpar</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-muted"><?= count($manifestacoes) ?> manifestação(ões)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tableManifestacoes" class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data manifestação</th>
                        <th>Protocolo</th>
                        <th>Protocolo Falabr</th>
                        <th>Assunto</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                        <th>SLA</th>
                        <th width="90"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manifestacoes as $m): ?>
                    <tr>
                        <td><small><?= ($m['data_manifestacao'] ?? $m['created_at']) ? date('d/m/Y', strtotime($m['data_manifestacao'] ?? $m['created_at'])) : '-' ?></small></td>
                        <td><strong><?= esc($m['protocolo']) ?></strong></td>
                        <td><span class="text-muted"><?= esc($m['protocolo_falabr'] ?? '-') ?></span></td>
                        <td class="text-break"><?= usuarioPodeVisualizar($m) ? esc(obterAssuntoExibicao($m['assunto'] ?? '')) : '<em class="text-muted">Conteúdo protegido</em>' ?></td>
                        <td><span class="badge bg-<?= ($m['status_label'] ?? '') === 'Devolvido' ? 'info' : (($m['status_label'] ?? '') === 'Recebido' ? 'primary' : 'secondary') ?>"><?= esc($m['status_label'] ?? $m['status']) ?></span></td>
                        <td><span class="badge bg-<?= $m['prioridade'] === 'alta' ? 'danger' : ($m['prioridade'] === 'media' ? 'warning' : 'info') ?>"><?= esc($m['prioridade']) ?></span></td>
                        <td><span class="badge bg-<?= $slaService->obterClasseSla($m) ?>"><?= $slaService->obterLabelSla($m) ?></span></td>
                        <td><a href="<?= url_to('ouvidoria.manifestacoes.show', $m['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($manifestacoes)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">Nenhuma manifestação encontrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
$(function() {
    if ($('#tableManifestacoes').length && $('#tableManifestacoes tbody tr').length > 0) {
        $('#tableManifestacoes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
                search: 'Buscar:',
                lengthMenu: 'Exibir _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ manifestações',
                infoEmpty: 'Nenhuma manifestação',
                infoFiltered: '(filtrado de _MAX_ no total)',
                paginate: { first: 'Primeiro', last: 'Último', next: 'Próximo', previous: 'Anterior' }
            },
            pageLength: 15,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
