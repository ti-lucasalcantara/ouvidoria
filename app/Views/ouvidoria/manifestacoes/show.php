<?= $this->extend('fixo/layout') ?>

<?= $this->section('titulo') ?>
Manifestação <?= esc($manifestacao['protocolo']) ?> - Ouvidoria
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0 text-dark"><i class="fas fa-folder-open me-2"></i><?= esc($manifestacao['protocolo']) ?></h1>
    <div class="d-flex gap-2">
        <?php if (!empty($podeEditarManifestacao)): ?>
        <a href="<?= url_to('ouvidoria.manifestacoes.edit', $manifestacao['id']) ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Editar</a>
        <?php endif; ?>
        <a href="<?= url_to('ouvidoria.manifestacoes.index') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
    </div>
</div>

<!-- Cabeçalho -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-2">
                <small class="text-muted d-block">Status</small>
                <?php $statusExibicao = $statusLabelGerente ?? $statusLabelOuvidor ?? $statusLabelUsuario ?? statusLabelGerente($manifestacao, false); ?>
<span class="badge bg-<?= $statusExibicao === 'Recebido' ? 'primary' : ($statusExibicao === 'Devolvido' ? 'info' : ($statusExibicao === 'Encaminhado' ? 'info' : 'secondary')) ?> fs-6"><?= esc($statusExibicao) ?></span>
            </div>
            <div class="col-6 col-md-2">
                <small class="text-muted d-block">Prioridade</small>
                <span class="badge bg-<?= $manifestacao['prioridade'] === 'alta' ? 'danger' : ($manifestacao['prioridade'] === 'media' ? 'warning' : 'info') ?>"><?= esc($manifestacao['prioridade']) ?></span>
            </div>
            <div class="col-6 col-md-2">
                <small class="text-muted d-block">SLA</small>
                <span class="badge bg-<?= $slaService->obterClasseSla($manifestacao) ?>"><?= $slaService->obterLabelSla($manifestacao) ?></span>
            </div>
            <div class="col-6 col-md-2">
                <small class="text-muted d-block">Prazo limite</small>
                <?= $manifestacao['data_limite_sla'] ? date('d/m/Y H:i', strtotime($manifestacao['data_limite_sla'])) : '-' ?>
            </div>
            <div class="col-6 col-md-2">
                <small class="text-muted d-block">Data manifestação</small>
                <?= ($manifestacao['data_manifestacao'] ?? $manifestacao['created_at'] ?? '') ? date('d/m/Y', strtotime($manifestacao['data_manifestacao'] ?? $manifestacao['created_at'] ?? '')) : '-' ?>
            </div>
            <div class="col-12 col-md-4">
                <small class="text-muted d-block">Origem</small>
                <?= esc($manifestacao['origem'] ?? 'Fala.BR') ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Conteúdo (descriptografado se autorizado) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Conteúdo</h5></div>
            <div class="card-body">
                <h6>Assunto</h6>
                <p class="mb-3"><?= usuarioPodeVisualizar($manifestacao) ? esc($manifestacao['assunto'] ?? '') : '<em>Conteúdo protegido</em>' ?></p>
                <h6>Descrição</h6>
                <div><?= usuarioPodeVisualizar($manifestacao) ? ($manifestacao['descricao'] ?? '') : '<em>Conteúdo protegido</em>' ?></div>
            </div>
        </div>

        <!-- Anexos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Anexos</h5></div>
            <div class="card-body">
                <?php $anexos = isset($anexos) && is_array($anexos) ? $anexos : []; ?>
                <?php foreach ($anexos as $a): ?>
                <span class="d-inline-flex align-items-center gap-1 border rounded px-2 py-1 bg-light me-2 mb-2">
                    <a href="<?= url_to('ouvidoria.anexos.abrir', $a['id']) ?>" class="btn btn-sm btn-outline-secondary text-nowrap" target="_blank" rel="noopener" title="Abrir">
                        <i class="fas fa-external-link-alt me-1"></i><?= esc($a['nome_original'] ?? 'Anexo') ?>
                    </a>
                </span>
                <?php endforeach; ?>
                <?php if (empty($anexos)): ?>
                <p class="text-muted small mb-0">Nenhum anexo.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline Histórico -->
        <?php
        $detalhesHist = function ($h) {
            $d = $h['detalhes'] ?? null;
            if (is_string($d)) return json_decode($d, true) ?: [];
            return is_array($d) ? $d : [];
        };
        $labelEvento = function ($tipo) {
            return match($tipo) {
                'CRIACAO' => 'Criação',
                'ENCAMINHAMENTO' => 'Encaminhamento',
                'DEVOLUCAO' => 'Devolução',
                'VOLTA' => 'Volta (retomada)',
                'ALTERACAO_STATUS' => 'Alteração de status',
                'COMENTARIO' => 'Comentário adicionado',
                'COMENTARIO_EDITADO' => 'Comentário editado',
                'COMENTARIO_EXCLUIDO' => 'Comentário excluído',
                'ATRIBUICAO_EDITADA' => 'Atribuição editada',
                'ATRIBUICAO_EXCLUIDA' => 'Atribuição excluída',
                'REABERTURA' => 'Reabertura',
                'ANEXO_ADICIONADO' => 'Anexo adicionado',
                default => $tipo,
            };
        };
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Histórico</h5></div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($historico as $h): ?>
                    <?php
                    $tipo = $h['tipo_evento'] ?? '';
                    $detalhes = $detalhesHist($h);
                    $icone = match($tipo) {
                        'CRIACAO' => 'plus',
                        'ENCAMINHAMENTO' => 'share',
                        'DEVOLUCAO' => 'reply',
                        'VOLTA' => 'undo',
                        'ALTERACAO_STATUS' => 'exchange-alt',
                        'COMENTARIO', 'COMENTARIO_EDITADO', 'COMENTARIO_EXCLUIDO' => 'comment',
                        'ATRIBUICAO_EDITADA', 'ATRIBUICAO_EXCLUIDA' => 'share',
                        default => 'circle',
                    };
                    $txtComentario = '';
                    if ($tipo === 'COMENTARIO') {
                        $txtComentario = $detalhes['comentario'] ?? '';
                    } elseif ($tipo === 'COMENTARIO_EDITADO') {
                        $txtComentario = $detalhes['comentario'] ?? '';
                    } elseif ($tipo === 'COMENTARIO_EXCLUIDO') {
                        $txtComentario = $detalhes['comentario'] ?? '';
                    }
                    if (trim(strip_tags($txtComentario ?? '')) === '' || trim(strip_tags($txtComentario ?? '')) === 'Comentário adicionado') {
                        $txtComentario = '';
                    }
                    $txtAtribuicao = '';
                    if (in_array($tipo, ['ATRIBUICAO_EDITADA', 'ATRIBUICAO_EXCLUIDA'])) {
                        $de = $detalhes['de_nome'] ?? 'N/A';
                        $para = $detalhes['para_nome'] ?? 'N/A';
                        $txtAtribuicao = 'DE: ' . $de . ' → PARA: ' . $para;
                        if (!empty($detalhes['mensagem'])) {
                            $txtAtribuicao .= ' · ' . (strip_tags($detalhes['mensagem']) ?: '(sem mensagem)');
                        }
                    }
                    $txtEncaminhamento = '';
                    if ($tipo === 'ENCAMINHAMENTO') {
                        $de = $detalhes['de_nome'] ?? ($h['usuario_nome'] ?? 'Sistema');
                        $para = !empty($detalhes['para_nomes']) ? implode(', ', $detalhes['para_nomes']) : 'N/A';
                        $txtEncaminhamento = 'DE: ' . $de . ' → PARA: ' . $para;
                    }
                    $txtDevolucao = '';
                    if ($tipo === 'DEVOLUCAO') {
                        $de = $detalhes['de_nome'] ?? ($h['usuario_nome'] ?? 'Sistema');
                        $para = $detalhes['para_nome'] ?? 'N/A';
                        $txtDevolucao = 'DE: ' . $de . ' → PARA: ' . $para;
                    }
                    $txtVolta = '';
                    if ($tipo === 'VOLTA') {
                        $de = $detalhes['de_nome'] ?? ($h['usuario_nome'] ?? 'Sistema');
                        $para = $detalhes['para_nome'] ?? 'N/A';
                        $txtVolta = 'DE: ' . $de . ' → PARA: ' . $para;
                    }
                    ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-<?= $icone ?> text-primary"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?> · <?= esc($h['usuario_nome'] ?? 'Sistema') ?></small>
                            <p class="mb-0 small"><strong><?= esc($labelEvento($tipo)) ?></strong></p>
                            <?php if ($txtComentario !== ''): ?>
                            <div class="mt-1 p-2 bg-light rounded small"><?= $txtComentario ?></div>
                            <?php elseif ($tipo === 'ALTERACAO_STATUS' && isset($detalhes['status_anterior'], $detalhes['status_novo'])): ?>
                            <p class="mb-0 small text-muted"><?= esc($detalhes['status_anterior']) ?> → <?= esc($detalhes['status_novo']) ?></p>
                            <?php elseif ($txtAtribuicao !== ''): ?>
                            <div class="mt-1 p-2 bg-light rounded small"><?= esc($txtAtribuicao) ?></div>
                            <?php elseif ($txtEncaminhamento !== ''): ?>
                            <div class="mt-1 p-2 bg-light rounded small"><?= esc($txtEncaminhamento) ?></div>
                            <?php elseif ($txtDevolucao !== ''): ?>
                            <div class="mt-1 p-2 bg-light rounded small"><?= esc($txtDevolucao) ?></div>
                            <?php elseif ($txtVolta !== ''): ?>
                            <div class="mt-1 p-2 bg-light rounded small"><?= esc($txtVolta) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($historico)): ?>
                    <p class="text-muted small">Nenhum registro no histórico.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
    </div>

    <div class="col-lg-4">
        <!-- Ações -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Ações</h5></div>
            <div class="card-body">
                <?php if ($authService->podeEncaminhar(obterUsuarioLogado(), $manifestacao)): ?>
                <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalEncaminhar">
                    <i class="fas fa-share me-1"></i>Encaminhar
                </button>
                <?php endif; ?>
                <?php if (!empty($podeDevolver) && $usuarioDevolverPara): ?>
                <button type="button" class="btn btn-outline-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalDevolver">
                    <i class="fas fa-reply me-1"></i>Devolver para <?= esc($usuarioDevolverPara['nome']) ?>
                </button>
                <?php elseif ((obterUsuarioLogado()['role'] ?? '') === 'usuario' && $authService->podeVisualizarManifestacao(obterUsuarioLogado(), $manifestacao)): ?>
                <div class="alert alert-light border mb-2 py-2">
                    <small class="text-muted d-block mb-2"><i class="fas fa-info-circle me-1"></i>Esta manifestação não está sob sua responsabilidade no momento.</small>
                    <div class="d-flex flex-wrap gap-1">
                    <?php if ($authService->podeVoltar(obterUsuarioLogado(), $manifestacao, $atribuicaoDevolucaoUsuario ?? null)): ?>
                    <?= form_open(url_to('ouvidoria.manifestacoes.voltar', $manifestacao['id']), ['class' => 'd-inline form-voltar-manifestacao']) ?>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-undo me-1"></i>Retomar
                    </button>
                    <?= form_close() ?>
                    <?php endif; ?>
                    <?php if ($authService->podeReabrir(obterUsuarioLogado(), $manifestacao)): ?>
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalReabrir">
                        <i class="fas fa-external-link-alt me-1"></i>Reabrir
                    </button>
                    <?php endif; ?>
                    <?php if (!empty($atribuicaoDevolucaoUsuario) && $authService->podeEditarExcluirAtribuicao(obterUsuarioLogado(), $manifestacao, $atribuicaoDevolucaoUsuario)): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-editar-atribuicao" data-atribuicao-id="<?= (int) $atribuicaoDevolucaoUsuario['id'] ?>">
                        <i class="fas fa-edit me-1"></i>Editar mensagem de devolução
                    </button>
                    <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($authService->podeAlterarStatus(obterUsuarioLogado(), $manifestacao)): ?>
                <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalStatus">
                    <i class="fas fa-exchange-alt me-1"></i>Alterar status
                </button>
                <?php endif; ?>
                <?php if ($authService->podeReabrir(obterUsuarioLogado(), $manifestacao)): ?>
                <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalReabrir">
                    <i class="fas fa-undo me-1"></i>Reabrir
                </button>
                <?php endif; ?>
                <?php if ($authService->podeComentar(obterUsuarioLogado(), $manifestacao)): ?>
                <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#modalComentar">
                    <i class="fas fa-comment me-1"></i>Comentar
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comentários -->
        <?php
        $detalhesJson = function ($h) {
            $d = $h['detalhes'] ?? null;
            if (is_string($d)) return json_decode($d, true) ?: [];
            return is_array($d) ? $d : [];
        };
        $comentariosRaw = array_filter($historico ?? [], fn($h) => ($h['tipo_evento'] ?? '') === 'COMENTARIO');
        $comentarios = array_filter($comentariosRaw, function ($c) use ($detalhesJson) {
            $d = $detalhesJson($c);
            return !($d['excluido'] ?? false);
        });
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-comments me-2"></i>Comentários</h5></div>
            <div class="card-body">
                <?php foreach (array_reverse($comentarios) as $c): ?>
                <?php
                $detalhes = $detalhesJson($c);
                $txt = $detalhes['comentario'] ?? '';
                if (trim(strip_tags($txt)) === '' || trim(strip_tags($txt)) === 'Comentário adicionado') {
                    $txt = '';
                }
                $podeEditarExcluir = $authService->podeEditarExcluirComentario(obterUsuarioLogado(), $manifestacao, $c);
                ?>
                <div class="mb-3 pb-3 border-bottom comentario-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?> · <?= esc($c['usuario_nome'] ?? 'Sistema') ?></small>
                        <?php if ($podeEditarExcluir): ?>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-editar-comentario" data-comentario-id="<?= (int) $c['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?= form_open(url_to('ouvidoria.manifestacoes.excluirComentario', $c['id']), ['class' => 'd-inline form-excluir-comentario']) ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                            <?= form_close() ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-1"><?= $txt ?></div>
                    <div class="comentario-conteudo" data-id="<?= (int) $c['id'] ?>" style="display:none"><?= $txt ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($comentarios)): ?>
                <p class="text-muted small mb-0">Nenhum comentário.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Atribuições -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Atribuições</h5></div>
            <div class="card-body">
                <?php foreach ($atribuicoes as $a): ?>
                <?php $podeEditarExcluirAtrib = $authService->podeEditarExcluirAtribuicao(obterUsuarioLogado(), $manifestacao, $a); ?>
                <div class="mb-2 p-2 rounded <?= $a['ativo'] ? 'bg-light' : '' ?> atribuicao-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1">
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($a['criado_em'])) ?></small>
                            <p class="mb-0 small">
                                DE: <strong><?= esc($a['de_nome'] ?? 'N/A') ?></strong> → <br> PARA: <strong><?= esc($a['para_nome'] ?? 'N/A') ?></strong>
                            </p>
                            <?php if (!empty($a['mensagem_encaminhamento'])): ?>
                            <div class="mt-1 atribuicao-mensagem small text-muted"><?= $a['mensagem_encaminhamento'] ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($podeEditarExcluirAtrib && $a['ativo']): ?>
                        <div class="btn-group btn-group-sm flex-shrink-0">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-editar-atribuicao" data-atribuicao-id="<?= (int) $a['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?= form_open(url_to('ouvidoria.manifestacoes.excluirAtribuicao', $a['id']), ['class' => 'd-inline form-excluir-atribuicao']) ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                            <?= form_close() ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="atribuicao-conteudo" data-id="<?= (int) $a['id'] ?>" style="display:none"><?= $a['mensagem_encaminhamento'] ?? '' ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($atribuicoes)): ?>
                <p class="text-muted small mb-0">Nenhuma atribuição.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Encaminhar -->
<div class="modal fade" id="modalEncaminhar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?= form_open(url_to('ouvidoria.manifestacoes.encaminhar', $manifestacao['id'])) ?>
            <div class="modal-header">
                <h5 class="modal-title">Encaminhar manifestação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Usuários <span class="text-danger">*</span></label>
                    <select name="usuarios[]" id="selectUsuariosEncaminhar" class="form-select select2-multiple" multiple <?= empty($usuariosParaEncaminhar) ? 'disabled' : 'required' ?>>
                        <?php foreach ($usuariosParaEncaminhar as $u): ?>
                        <option value="<?= (int) $u['id'] ?>"><?= esc($u['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($usuariosParaEncaminhar)): ?>
                    <p class="text-muted small mt-1 mb-0">Todos os usuários já possuem esta manifestação atribuída.</p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensagem</label>
                    <div id="editor-mensagem" style="height: 120px;"></div>
                    <input type="hidden" name="mensagem_encaminhamento" id="mensagem_encaminhamento">
                </div>
                <?php if ((obterUsuarioLogado()['role'] ?? '') === 'gerente'): ?>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="marcar_respondida" id="marcar_respondida" value="1">
                        <label class="form-check-label" for="marcar_respondida">
                            Marcar como respondida (status da manifestação será "Respondida")
                        </label>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" <?= empty($usuariosParaEncaminhar) ? 'disabled' : '' ?>><i class="fas fa-share me-1"></i>Encaminhar</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open(url_to('ouvidoria.manifestacoes.updateStatus', $manifestacao['id'])) ?>
            <div class="modal-header">
                <h5 class="modal-title">Alterar status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Novo status</label>
                    <select name="status" class="form-select">
                        <option value="recebida">Recebida</option>
                        <option value="encaminhada">Encaminhada</option>
                        <option value="em_atendimento">Em atendimento</option>
                        <option value="respondida">Respondida</option>
                        <option value="finalizada">Finalizada</option>
                        <option value="arquivada">Arquivada</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Alterar</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal Devolver -->
<?php if (!empty($podeDevolver) && !empty($usuarioDevolverPara)): ?>
<div class="modal fade" id="modalDevolver" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?= form_open(url_to('ouvidoria.manifestacoes.devolver', $manifestacao['id'])) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-reply me-1"></i>Devolver manifestação para <?= esc($usuarioDevolverPara['nome']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Mensagem <span class="text-danger">*</span></label>
                    <div id="editor-devolucao" style="height: 200px;"></div>
                    <input type="hidden" name="mensagem_devolucao" id="mensagem_devolucao" required>
                </div>
                <?php if ($authService->podeReabrir(obterUsuarioLogado(), $manifestacao)): ?>
                <div class="border-top pt-3 mt-3">
                    <p class="text-muted small mb-2">Ou prefere reabrir a manifestação?</p>
                    <button type="button" class="btn btn-outline-warning btn-sm btn-reabrir-dentro-devolver">
                        <i class="fas fa-undo me-1"></i>Reabrir manifestação
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info"><i class="fas fa-reply me-1"></i>Devolver</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Reabrir -->
<div class="modal fade" id="modalReabrir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open(url_to('ouvidoria.manifestacoes.reabrir', $manifestacao['id'])) ?>
            <div class="modal-header">
                <h5 class="modal-title">Reabrir manifestação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Status para reabrir</label>
                    <select name="novo_status" class="form-select">
                        <option value="em_atendimento">Em atendimento</option>
                        <option value="encaminhada">Encaminhada</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning">Reabrir</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal Comentar -->
<div class="modal fade" id="modalComentar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?= form_open(url_to('ouvidoria.manifestacoes.comentar', $manifestacao['id'])) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-comment me-1"></i>Comentar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Comentário <span class="text-danger">*</span></label>
                    <div id="editor-comentario" style="height: 200px;"></div>
                    <input type="hidden" name="comentario" id="comentario_input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Registrar</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal Editar Comentário -->
<div class="modal fade" id="modalEditarComentario" tabindex="-1" data-base-url="<?= esc(url_to('ouvidoria.manifestacoes.editarComentario', 0)) ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditarComentario" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-1"></i>Editar comentário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Comentário <span class="text-danger">*</span></label>
                        <div id="editor-editar-comentario" style="height: 200px;"></div>
                        <input type="hidden" name="comentario" id="comentario_editar_input" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Atribuição -->
<div class="modal fade" id="modalEditarAtribuicao" tabindex="-1" data-base-url="<?= esc(url_to('ouvidoria.manifestacoes.editarAtribuicao', 0)) ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditarAtribuicao" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-1"></i>Editar atribuição</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mensagem de encaminhamento</label>
                        <div id="editor-editar-atribuicao" style="height: 150px;"></div>
                        <input type="hidden" name="mensagem_encaminhamento" id="atribuicao_mensagem_input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
$(function() {
    $('#selectUsuariosEncaminhar').select2({
        width: '100%',
        placeholder: 'Selecione os usuários',
        dropdownParent: $('#modalEncaminhar')
    });
    var quillMsg = new Quill('#editor-mensagem', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    });
    quillMsg.on('text-change', function() {
        $('#mensagem_encaminhamento').val(quillMsg.root.innerHTML);
    });
    $('#modalEncaminhar form').on('submit', function() {
        $('#mensagem_encaminhamento').val(quillMsg.root.innerHTML);
    });

    var quillDevolucao = $('#editor-devolucao').length ? new Quill('#editor-devolucao', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    }) : null;
    if (quillDevolucao) {
        quillDevolucao.on('text-change', function() {
            $('#mensagem_devolucao').val(quillDevolucao.root.innerHTML);
        });
        $('#modalDevolver form').on('submit', function() {
            $('#mensagem_devolucao').val(quillDevolucao.root.innerHTML);
        });
    }
    $('.btn-reabrir-dentro-devolver').on('click', function() {
        var modalDevolver = document.getElementById('modalDevolver');
        var modalReabrir = document.getElementById('modalReabrir');
        if (modalDevolver && modalReabrir) {
            bootstrap.Modal.getInstance(modalDevolver).hide();
            modalDevolver.addEventListener('hidden.bs.modal', function handler() {
                modalDevolver.removeEventListener('hidden.bs.modal', handler);
                new bootstrap.Modal(modalReabrir).show();
            }, { once: true });
        }
    });

    var quillComentario = $('#editor-comentario').length ? new Quill('#editor-comentario', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    }) : null;
    if (quillComentario) {
        quillComentario.on('text-change', function() {
            $('#comentario_input').val(quillComentario.root.innerHTML);
        });
        $('#modalComentar form').on('submit', function() {
            $('#comentario_input').val(quillComentario.root.innerHTML);
        });
    }

    var quillEditarComentario = $('#editor-editar-comentario').length ? new Quill('#editor-editar-comentario', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    }) : null;
    if (quillEditarComentario) {
        quillEditarComentario.on('text-change', function() {
            $('#comentario_editar_input').val(quillEditarComentario.root.innerHTML);
        });
        $('#formEditarComentario').on('submit', function() {
            $('#comentario_editar_input').val(quillEditarComentario.root.innerHTML);
        });
    }

    $('.btn-editar-comentario').on('click', function() {
        var id = $(this).data('comentario-id');
        var $conteudo = $('.comentario-conteudo[data-id="' + id + '"]');
        var texto = $conteudo.length ? $conteudo.html() : '';
        var baseUrl = $('#modalEditarComentario').data('base-url');
        $('#formEditarComentario').attr('action', baseUrl.replace(/\/0$/, '/' + id));
        if (quillEditarComentario) {
            quillEditarComentario.root.innerHTML = texto;
            $('#comentario_editar_input').val(texto);
        }
        new bootstrap.Modal(document.getElementById('modalEditarComentario')).show();
    });

    $(document).on('submit', '.form-voltar-manifestacao', function(e) {
        if ($(this).data('confirmed')) {
            $(this).data('confirmed', false);
            return;
        }
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: "Retomar manifestação?",
            text: "Ela voltará para suas mãos.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#f0ad4e",
            confirmButtonText: "Retomar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $(form).data('confirmed', true);
                form.submit();
            }
        });
    });

    $(document).on('submit', '.form-excluir-comentario', function(e) {
        if ($(this).data('confirmed')) {
            $(this).data('confirmed', false);
            return;
        }
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: "Excluir comentário?",
            text: "Esta operação não poderá ser desfeita.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Excluir",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $(form).data('confirmed', true);
                form.submit();
            }
        });
    });

    var quillEditarAtribuicao = $('#editor-editar-atribuicao').length ? new Quill('#editor-editar-atribuicao', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic'], ['link'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
    }) : null;
    if (quillEditarAtribuicao) {
        quillEditarAtribuicao.on('text-change', function() {
            $('#atribuicao_mensagem_input').val(quillEditarAtribuicao.root.innerHTML);
        });
        $('#formEditarAtribuicao').on('submit', function() {
            $('#atribuicao_mensagem_input').val(quillEditarAtribuicao.root.innerHTML);
        });
    }
    $(document).on('click', '.btn-editar-atribuicao', function() {
        var id = $(this).data('atribuicao-id');
        var $conteudo = $('.atribuicao-conteudo[data-id="' + id + '"]');
        var texto = $conteudo.length ? $conteudo.html() : '';
        var baseUrl = $('#modalEditarAtribuicao').data('base-url');
        $('#formEditarAtribuicao').attr('action', baseUrl.replace(/\/0$/, '/' + id));
        if (quillEditarAtribuicao) {
            quillEditarAtribuicao.root.innerHTML = texto;
            $('#atribuicao_mensagem_input').val(texto);
        }
        new bootstrap.Modal(document.getElementById('modalEditarAtribuicao')).show();
    });
    $(document).on('submit', '.form-excluir-atribuicao', function(e) {
        if ($(this).data('confirmed')) {
            $(this).data('confirmed', false);
            return;
        }
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: "Excluir atribuição?",
            text: "Esta operação não poderá ser desfeita.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Excluir",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $(form).data('confirmed', true);
                form.submit();
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
