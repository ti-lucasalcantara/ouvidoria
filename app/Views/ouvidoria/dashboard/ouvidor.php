<?php
$titulo = 'Dashboard - Ouvidoria';
$this->extend('fixo/layout');
?>

<?= $this->section('titulo') ?><?= esc($titulo) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-chart-line me-2"></i>Dashboard Ouvidoria</h1>
</div>

<?php
$baseUrl = url_to('ouvidoria.dashboard');
$query = [];
if (!empty($prioridadeFiltro ?? '')) $query['prioridade'] = $prioridadeFiltro;
if (!empty($dataInicio ?? '')) $query['data_inicio'] = $dataInicio;
if (!empty($dataFim ?? '')) $query['data_fim'] = $dataFim;
$link = function ($status = '') use ($baseUrl, $query) {
    $q = $query;
    if ($status !== '') $q['status'] = $status;
    return $baseUrl . (empty($q) ? '' : '?' . http_build_query($q));
};
$ativo = $statusFiltro ?? '';
?>
<?php
$cardClass = function ($key) use ($ativo) {
    $base = 'card border-0 shadow-sm h-100 card-hover';
    return $base . ($ativo === $key ? ' active border-primary border-2 bg-primary text-white' : '');
};
?>
<!-- Cards de resumo (clicáveis para filtrar) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Todos</p>
                    <h4 class="mb-0 text-dark"><?= $totalTodos ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2 d-none">
        <a href="<?= $link('abertas') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('abertas') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Abertas</p>
                    <h4 class="mb-0 text-primary"><?= $totalAbertas ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('recebida') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('recebida') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Recebidas</p>
                    <h4 class="mb-0 text-primary"><?= $totalRecebidas ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('encaminhada') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('encaminhada') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Encaminhadas</p>
                    <h4 class="mb-0 text-secondary"><?= $totalEncaminhadas ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('respondida') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('respondida') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Respondidas</p>
                    <h4 class="mb-0 text-info"><?= $totalRespondidas ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('em_atraso') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('em_atraso') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Em atraso</p>
                    <h4 class="mb-0 text-danger"><?= $emAtraso ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('a_vencer') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('a_vencer') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">A vencer</p>
                    <h4 class="mb-0 text-warning"><?= $aVencer ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $link('finalizadas') ?>" class="text-decoration-none">
            <div class="<?= $cardClass('finalizadas') ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Finalizadas</p>
                    <h4 class="mb-0 text-success"><?= $finalizadasMes ?></h4>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filtros adicionais (data e prioridade) -->
<form method="get" class="mb-4">
    <?php if (!empty($ativo)): ?><input type="hidden" name="status" value="<?= esc($ativo) ?>"><?php endif; ?>
    <div class="row g-2 align-items-center">
        <div class="col-auto">
            <label class="form-label small mb-0">Prioridade</label>
            <select name="prioridade" class="form-select form-select-sm">
                <option value="">Todas</option>
                <option value="baixa" <?= ($prioridadeFiltro ?? '') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                <option value="media" <?= ($prioridadeFiltro ?? '') === 'media' ? 'selected' : '' ?>>Média</option>
                <option value="alta" <?= ($prioridadeFiltro ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">Data início</label>
            <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= esc($dataInicio ?? '') ?>">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">Data fim</label>
            <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= esc($dataFim ?? '') ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary mt-3"><i class="fas fa-filter me-1"></i>Filtrar</button>
        </div>
        <?php if (!empty($ativo) || !empty($prioridadeFiltro ?? '') || !empty($dataInicio ?? '') || !empty($dataFim ?? '')): ?>
        <div class="col-auto">
            <a href="<?= $baseUrl ?>" class="btn btn-sm btn-outline-secondary mt-3">Limpar</a>
        </div>
        <?php endif; ?>
    </div>
</form>
<style>
.card-hover:hover { transform: translateY(-2px); transition: transform 0.15s ease; }
.card.active.bg-primary .card-body p,
.card.active.bg-primary .card-body h4 { color: white !important; }
</style>

<!-- Últimas manifestações -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Últimas manifestações</h5>
        <a href="<?= url_to('ouvidoria.manifestacoes.create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nova</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tableOuvidorUltimas" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Data manifestação</th>
                        <th>Protocolo</th>
                        <th>Protocolo Falabr</th>
                        <th>Assunto</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($ultimas as $m): 
                    ?>
                    <tr>
                        <td><small><?= ($m['data_manifestacao'] ?? $m['created_at']) ? date('d/m/Y', strtotime($m['data_manifestacao'] ?? $m['created_at'])) : '-' ?></small></td>
                        <td><strong><?= esc($m['protocolo']) ?></strong></td>
                        <td><strong><?= esc($m['protocolo_falabr']) ?></strong></td>
                        <td><?= usuarioPodeVisualizar($m) ? esc(obterAssuntoExibicao($m['assunto'] ?? '')) : '<em>Conteúdo protegido</em>' ?></td>
                        <td><span class="badge bg-<?= ($m['prioridade'] ?? '') === 'alta' ? 'danger' : (($m['prioridade'] ?? '') === 'media' ? 'warning' : 'info') ?>"><?= esc($m['prioridade'] ?? 'media') ?></span></td>
                        <td><span class="badge bg-<?= (in_array($m['id'], $idsDevolvidoOuvidor ?? []) ? 'info' : 'secondary') ?>"><?= esc(in_array($m['id'], $idsDevolvidoOuvidor ?? []) ? 'Devolvido' : statusLabelGerente($m, false)) ?></span></td>
                        <td><span class="badge bg-<?= $slaService->obterClasseSla($m) ?>"><?= $slaService->obterLabelSla($m) ?></span></td>
                        <td><a href="<?= url_to('ouvidoria.manifestacoes.show', $m['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ultimas)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma manifestação encontrada.</td></tr>
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
    var $t = $('#tableOuvidorUltimas');
    if ($t.length && $t.find('tbody tr').length > 0 && $t.find('tbody tr td[colspan]').length === 0) {
        $t.DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
                search: 'Buscar:',
                lengthMenu: 'Exibir _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_',
                infoEmpty: 'Nenhuma manifestação',
                paginate: { first: 'Primeiro', last: 'Último', next: 'Próximo', previous: 'Anterior' }
            },
            pageLength: 10,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    }
});
</script>
<?= $this->endSection() ?>
