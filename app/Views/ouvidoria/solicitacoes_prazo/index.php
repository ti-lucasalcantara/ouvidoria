<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Solicitações de Prorrogação - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-hourglass-half me-2"></i>Solicitações de Prorrogação</h1>
    <a href="<?= url_to('ouvidoria.dashboard') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar ao dashboard</a>
</div>

<?php
$baseUrl = url_to('ouvidoria.solicitacoesPrazo.index');
$statusAtual = $statusFiltro ?? 'pendente';
$linkStatus = function (string $status) use ($baseUrl) {
    return $baseUrl . ($status === '' ? '' : '?status=' . urlencode($status));
};
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= $linkStatus('pendente') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?= $statusAtual === 'pendente' ? 'border-warning border-2' : '' ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Pendentes</p>
                    <h5 class="mb-0 text-warning">Analisar</h5>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $linkStatus('aprovada') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?= $statusAtual === 'aprovada' ? 'border-success border-2' : '' ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Aprovadas</p>
                    <h5 class="mb-0 text-success">Concedidas</h5>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $linkStatus('rejeitada') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?= $statusAtual === 'rejeitada' ? 'border-danger border-2' : '' ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Rejeitadas</p>
                    <h5 class="mb-0 text-danger">Negadas</h5>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= $linkStatus('') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?= $statusAtual === '' ? 'border-primary border-2' : '' ?>">
                <div class="card-body">
                    <p class="text-muted small mb-1">Todas</p>
                    <h5 class="mb-0 text-primary">Histórico</h5>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Solicitações</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tableSolicitacoesPrazo" class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Solicitada em</th>
                        <th>Protocolo</th>
                        <th>Solicitante</th>
                        <th>Dias</th>
                        <th>Status</th>
                        <th>Assunto</th>
                        <th>Motivo</th>
                        <th width="220"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitacoes as $s): ?>
                    <tr>
                        <td><small><?= !empty($s['created_at']) ? date('d/m/Y H:i', strtotime($s['created_at'])) : '-' ?></small></td>
                        <td><strong><?= esc($s['protocolo'] ?? '-') ?></strong></td>
                        <td><?= esc($s['solicitante_nome'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-warning text-dark"><?= (int) ($s['dias_solicitados'] ?? 0) ?> solicitado(s)</span>
                            <?php if (!empty($s['dias_concedidos'])): ?>
                            <span class="badge bg-success"><?= (int) $s['dias_concedidos'] ?> concedido(s)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= ($s['status'] ?? '') === 'pendente' ? 'warning text-dark' : (($s['status'] ?? '') === 'aprovada' ? 'success' : 'danger') ?>">
                                <?= esc(ucfirst($s['status'] ?? '-')) ?>
                            </span>
                        </td>
                        <td><?= esc(obterAssuntoExibicao($s['assunto'] ?? '')) ?></td>
                        <td class="text-break"><?= esc(mb_strimwidth(strip_tags((string) ($s['motivo'] ?? '')), 0, 100, '...')) ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="<?= url_to('ouvidoria.manifestacoes.show', $s['manifestacao_id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                <?php if (($s['status'] ?? '') === 'pendente'): ?>
                                <button type="button" class="btn btn-sm btn-success btn-abrir-aprovar"
                                    data-id="<?= (int) $s['id'] ?>"
                                    data-protocolo="<?= esc($s['protocolo'] ?? '', 'attr') ?>"
                                    data-dias="<?= (int) ($s['dias_solicitados'] ?? 0) ?>"
                                    data-motivo="<?= esc(strip_tags((string) ($s['motivo'] ?? '')), 'attr') ?>">
                                    Aprovar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-abrir-rejeitar"
                                    data-id="<?= (int) $s['id'] ?>"
                                    data-protocolo="<?= esc($s['protocolo'] ?? '', 'attr') ?>">
                                    Rejeitar
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($solicitacoes)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">Nenhuma solicitação encontrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAprovarSolicitacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formAprovarSolicitacao">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Aprovar solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3" id="textoAprovarSolicitacao"></p>
                    <div class="mb-3">
                        <label class="form-label">Dias concedidos <span class="text-danger">*</span></label>
                        <input type="number" name="dias_concedidos" id="diasConcedidosInput" class="form-control" min="1" step="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Observação</label>
                        <textarea name="resposta_analise" class="form-control" rows="4" placeholder="Observações da análise"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprovar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRejeitarSolicitacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formRejeitarSolicitacao">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Rejeitar solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3" id="textoRejeitarSolicitacao"></p>
                    <div class="mb-0">
                        <label class="form-label">Motivo da rejeição <span class="text-danger">*</span></label>
                        <textarea name="resposta_analise" class="form-control" rows="4" required placeholder="Explique o motivo da rejeição"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rejeitar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
$(function() {
    var $table = $('#tableSolicitacoesPrazo');
    if ($table.length && $table.find('tbody tr').length > 0 && $table.find('tbody tr td[colspan]').length === 0) {
        $table.DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            pageLength: 15,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    }

    $(document).on('click', '.btn-abrir-aprovar', function() {
        var id = $(this).data('id');
        var protocolo = $(this).data('protocolo');
        var dias = $(this).data('dias');
        $('#formAprovarSolicitacao').attr('action', '<?= url_to('ouvidoria.solicitacoesPrazo.aprovar', 0) ?>'.replace(/\/0$/, '/' + id));
        $('#textoAprovarSolicitacao').text('Manifestação ' + protocolo + '. O pedido foi de ' + dias + ' dia(s).');
        $('#diasConcedidosInput').val(dias);
        new bootstrap.Modal(document.getElementById('modalAprovarSolicitacao')).show();
    });

    $(document).on('click', '.btn-abrir-rejeitar', function() {
        var id = $(this).data('id');
        var protocolo = $(this).data('protocolo');
        $('#formRejeitarSolicitacao').attr('action', '<?= url_to('ouvidoria.solicitacoesPrazo.rejeitar', 0) ?>'.replace(/\/0$/, '/' + id));
        $('#textoRejeitarSolicitacao').text('Manifestação ' + protocolo + '.');
        new bootstrap.Modal(document.getElementById('modalRejeitarSolicitacao')).show();
    });
});
</script>
<?= $this->endSection() ?>
