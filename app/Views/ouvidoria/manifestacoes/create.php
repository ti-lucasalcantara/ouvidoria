<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Nova Manifestação - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-plus-circle me-2"></i>Nova Manifestação</h1>
    <a href="<?= url_to('ouvidoria.manifestacoes.index') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
</div>

<?= form_open_multipart(url_to('ouvidoria.manifestacoes.store'), ['class' => 'needs-validation']) ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Dados da manifestação</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4 d-none">
                <label class="form-label">Protocolo <small class="text-muted">(interno)</small></label>
                <input type="text" name="protocolo" class="form-control" value="<?= esc(old('protocolo')) ?>" placeholder="OUV-2025-000001">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Protocolo Fala.BR</label>
                <input type="text" name="protocolo_falabr" class="form-control" value="<?= esc(old('protocolo_falabr')) ?>" placeholder="Protocolo gerado pelo Fala.BR">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Origem</label>
                <input type="text" name="origem" class="form-control" value="<?= esc(old('origem', 'Fala.BR')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Data da manifestação <span class="text-danger">*</span></label>
                <input type="date" name="data_manifestacao" class="form-control" value="<?= esc(old('data_manifestacao', date('Y-m-d'))) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label">Assunto <span class="text-danger">*</span></label>
                <textarea name="assunto" id="assunto" class="form-control" rows="2" required><?= esc(old('assunto')) ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Descrição <span class="text-danger">*</span></label>
                <div id="editor-descricao" style="height: 200px;"></div>
                <input type="hidden" name="descricao" id="descricao" required>
            </div>
            <div class="col-12 d-none">
                <label class="form-label">Dados de identificação <small class="text-muted">(JSON ou texto livre)</small></label>
                <textarea name="dados_identificacao" class="form-control" rows="2" placeholder='{"nome":"...","contato":"..."}'><?= esc(old('dados_identificacao')) ?></textarea>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Prioridade</label>
                <select name="prioridade" class="form-select">
                    <option value="baixa" <?= old('prioridade') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                    <option value="media" <?= old('prioridade') === 'media' || !old('prioridade') ? 'selected' : '' ?>>Média</option>
                    <option value="alta" <?= old('prioridade') === 'alta' ? 'selected' : '' ?>>Alta</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Prazo (dias)</label>
                <input type="number" name="sla_prazo_em_dias" class="form-control" value="<?= esc(old('sla_prazo_em_dias', 30)) ?>" min="1">
            </div>
            <div class="col-12">
                <label class="form-label">Anexos</label>
                <input type="file" id="anexosInput" name="anexos[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt">
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAdicionarAnexo"><i class="fas fa-plus me-1"></i>Adicionar arquivos</button>
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered" id="tabelaAnexosPreview">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Tamanho</th>
                                <th width="80">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAnexosPreview">
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Os anexos serão salvos ao clicar em Salvar.</small>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
        <a href="<?= url_to('ouvidoria.manifestacoes.index') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var anexosPendentes = [];
    var inputAnexos = document.getElementById('anexosInput');

    var quill = new Quill('#editor-descricao', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    });
    quill.on('text-change', function() {
        document.getElementById('descricao').value = quill.root.innerHTML;
    });

    function syncInputAnexos() {
        var dt = new DataTransfer();
        anexosPendentes.forEach(function(f) { dt.items.add(f); });
        inputAnexos.files = dt.files;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024, s = ['B', 'KB', 'MB'], i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + s[i];
    }

    function renderTabelaAnexos() {
        var tbody = document.getElementById('tbodyAnexosPreview');
        tbody.innerHTML = '';
        anexosPendentes.forEach(function(f, i) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + (f.name || 'Arquivo') + '</td><td>' + (f.type || '-') + '</td><td>' + formatBytes(f.size) + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remover-anexo" data-idx="' + i + '"><i class="fas fa-trash"></i></button></td>';
            tbody.appendChild(tr);
        });
        document.querySelectorAll('.btn-remover-anexo').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.getAttribute('data-idx'), 10);
                anexosPendentes.splice(idx, 1);
                syncInputAnexos();
                renderTabelaAnexos();
            });
        });
    }

    inputAnexos.addEventListener('change', function() {
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            anexosPendentes.push(files[i]);
        }
        syncInputAnexos();
        renderTabelaAnexos();
        this.value = '';
    });

    document.getElementById('btnAdicionarAnexo').addEventListener('click', function() {
        inputAnexos.click();
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        document.getElementById('descricao').value = quill.root.innerHTML;
        syncInputAnexos();
    });
});
</script>
<?= $this->endSection() ?>
