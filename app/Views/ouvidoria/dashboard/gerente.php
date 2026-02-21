<?php
$titulo = 'Dashboard - Minhas Manifestações';
$this->extend('fixo/layout');
?>

<?= $this->section('titulo') ?><?= esc($titulo) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-tasks me-2"></i>Minhas Manifestações</h1>
</div>

<?php
$baseUrl = url_to('ouvidoria.dashboard');
$ativo = $statusFiltro ?? '';
$cardClass = function ($key) use ($ativo) {
    $base = 'card border-0 shadow-sm h-100 card-hover';
    return $base . ($ativo === $key ? ' active border-primary border-2 bg-primary text-white' : '');
};
?>
<!-- Cards KPIs (clicáveis) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= $baseUrl ?>" class="text-decoration-none">
            <div class="<?= $cardClass('') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Todos</p>
                    <h4 class="mb-0 text-dark"><?= $totalTodos ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $baseUrl ?>?status=atribuidas" class="text-decoration-none">
            <div class="<?= $cardClass('atribuidas') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Atribuídas</p>
                    <h4 class="mb-0 text-primary"><?= $totalAtivas ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $baseUrl ?>?status=encaminhadas" class="text-decoration-none">
            <div class="<?= $cardClass('encaminhadas') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Encaminhadas</p>
                    <h4 class="mb-0 text-info"><?= $totalEncaminhadas ?? 0 ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $baseUrl ?>?status=devolvidas" class="text-decoration-none">
            <div class="<?= $cardClass('devolvidas') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Devolvidas</p>
                    <h4 class="mb-0 text-dark"><?= $totalDevolvidos ?? 0 ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $baseUrl ?>?status=em_atraso" class="text-decoration-none">
            <div class="<?= $cardClass('em_atraso') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Em atraso</p>
                    <h4 class="mb-0 text-danger"><?= $emAtraso ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $baseUrl ?>?status=a_vencer" class="text-decoration-none">
            <div class="<?= $cardClass('a_vencer') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">A vencer</p>
                    <h4 class="mb-0 text-warning"><?= $aVencer ?></h4>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.card-hover:hover { transform: translateY(-2px); transition: transform 0.15s ease; }
.card.active.bg-primary .card-body p,
.card.active.bg-primary .card-body h4 { color: white !important; }
</style>

<?php if (!empty($devolvidos)): ?>
<!-- Devolvidas -->
<div class="card border-0 shadow-sm border-info mb-4">
    <div class="card-header bg-info bg-opacity-10">
        <h5 class="mb-0 text-dark"><i class="fas fa-reply me-2"></i>Devolvidas para mim</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-datatable">
                <thead class="table-light">
                    <tr>
                        <th>Data manifestação</th>
                        <th>Protocolo</th>
                        <th>Protocolo Falabr</th>
                        <th>Assunto</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th>Prazo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devolvidos as $m): ?>
                    <tr>
                        <td><small><?= ($m['data_manifestacao'] ?? $m['created_at']) ? date('d/m/Y', strtotime($m['data_manifestacao'] ?? $m['created_at'])) : '-' ?></small></td>
                        <td><strong><?= esc($m['protocolo']) ?></strong></td>
                        <td><strong><?= esc($m['protocolo_falabr'] ?? '-') ?></strong></td>
                        <td><?= esc(obterAssuntoExibicao($m['assunto'] ?? '')) ?></td>
                        <td><span class="badge bg-<?= ($m['prioridade'] ?? '') === 'alta' ? 'danger' : (($m['prioridade'] ?? '') === 'media' ? 'warning' : 'info') ?>"><?= esc($m['prioridade'] ?? 'media') ?></span></td>
                        <td><span class="badge bg-info"><?= esc(statusLabelGerente($m, true)) ?></span></td>
                        <td><span class="badge bg-<?= $slaService->obterClasseSla($m) ?>"><?= $slaService->obterLabelSla($m) ?></span></td>
                        <td><?= $m['data_limite_sla'] ? date('d/m/Y', strtotime($m['data_limite_sla'])) : '-' ?></td>
                        <td><a href="<?= url_to('ouvidoria.manifestacoes.show', $m['manifestacao_id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($encaminhadas)): ?>
<!-- Encaminhadas -->
<div class="card border-0 shadow-sm border-info mb-4">
    <div class="card-header bg-info bg-opacity-10">
        <h5 class="mb-0 text-dark"><i class="fas fa-share me-2"></i>Manifestações encaminhadas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-datatable">
                <thead class="table-light">
                    <tr>
                        <th>Data manifestação</th>
                        <th>Protocolo</th>
                        <th>Protocolo Falabr</th>
                        <th>Assunto</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th>Prazo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($encaminhadas as $m): ?>
                    <tr>
                        <td><small><?= ($m['data_manifestacao'] ?? $m['created_at']) ? date('d/m/Y', strtotime($m['data_manifestacao'] ?? $m['created_at'])) : '-' ?></small></td>
                        <td><strong><?= esc($m['protocolo']) ?></strong></td>
                        <td><strong><?= esc($m['protocolo_falabr'] ?? '-') ?></strong></td>
                        <td><?= esc(obterAssuntoExibicao($m['assunto'] ?? '')) ?></td>
                        <td><span class="badge bg-<?= ($m['prioridade'] ?? '') === 'alta' ? 'danger' : (($m['prioridade'] ?? '') === 'media' ? 'warning' : 'info') ?>"><?= esc($m['prioridade'] ?? 'media') ?></span></td>
                        <td><span class="badge bg-info"><?= esc(statusLabelGerente($m, false, true)) ?></span></td>
                        <td><span class="badge bg-<?= $slaService->obterClasseSla($m) ?>"><?= $slaService->obterLabelSla($m) ?></span></td>
                        <td><?= $m['data_limite_sla'] ? date('d/m/Y', strtotime($m['data_limite_sla'])) : '-' ?></td>
                        <td><a href="<?= url_to('ouvidoria.manifestacoes.show', $m['manifestacao_id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Lista atribuídas -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Manifestações atribuídas a mim</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-datatable">
                <thead class="table-light">
                    <tr>
                        <th>Data manifestação</th>
                        <th>Protocolo</th>
                        <th>Protocolo Falabr</th>
                        <th>Assunto</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th>Prazo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($minhasAtribuidas as $m): ?>
                    <tr>
                        <td><small><?= ($m['data_manifestacao'] ?? $m['created_at']) ? date('d/m/Y', strtotime($m['data_manifestacao'] ?? $m['created_at'])) : '-' ?></small></td>
                        <td><strong><?= esc($m['protocolo']) ?></strong></td>
                        <td><strong><?= esc($m['protocolo_falabr'] ?? '-') ?></strong></td>
                        <td><?= esc(obterAssuntoExibicao($m['assunto'] ?? '')) ?></td>
                        <td><span class="badge bg-<?= ($m['prioridade'] ?? '') === 'alta' ? 'danger' : (($m['prioridade'] ?? '') === 'media' ? 'warning' : 'info') ?>"><?= esc($m['prioridade'] ?? 'media') ?></span></td>
                        <td><span class="badge bg-<?= ($m['status'] ?? '') === 'encaminhada' ? 'primary' : 'secondary' ?>"><?= esc(statusLabelGerente($m, false)) ?></span></td>
                        <td><span class="badge bg-<?= $slaService->obterClasseSla($m) ?>"><?= $slaService->obterLabelSla($m) ?></span></td>
                        <td><?= $m['data_limite_sla'] ? date('d/m/Y', strtotime($m['data_limite_sla'])) : '-' ?></td>
                        <td><a href="<?= url_to('ouvidoria.manifestacoes.show', $m['manifestacao_id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($minhasAtribuidas)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Nenhuma manifestação atribuída.</td></tr>
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
    $('.table-datatable').each(function() {
        var $table = $(this);
        if ($table.find('tbody tr').length > 0 && $table.find('tbody tr td[colspan]').length === 0) {
            $table.DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
                    search: 'Buscar:',
                    lengthMenu: 'Exibir _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_',
                    infoEmpty: 'Nenhum registro',
                    paginate: { first: 'Primeiro', last: 'Último', next: 'Próximo', previous: 'Anterior' }
                },
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: -1 }]
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
