# Plano de Build – Ouvidoria

Este documento descreve o plano de implementação para duas funcionalidades: **Responder para o ouvidor** e **Categorias da Manifestação**.

---

## 1. Responder para o ouvidor

### 1.1 Objetivo
Permitir que usuários autorizados (ouvidor/administrador) registrem respostas direcionadas ao ouvidor na tela de visualização da manifestação. Cada resposta pode ter conteúdo em editor rico e anexos; o conteúdo da resposta deve ser criptografado com a mesma DEK da manifestação. Ao registrar a primeira resposta (ou qualquer resposta), o status da manifestação deve ser alterado para **respondida** e exibir SweetAlert de sucesso.

### 1.2 Migration e tabela

**Arquivo:** `app/Database/Migrations/YYYY-MM-DD_HHMMSS_CreateRespostasOuvidor.php`

- **Tabela:** `respostas_ouvidor`
- **Campos:**
  - `id` (INT, PK, auto_increment)
  - `manifestacao_id` (INT, FK → manifestacoes.id, CASCADE)
  - `respondido_por_usuario_id` (INT, FK → usuarios.id, SET NULL ou restrito)
  - `conteudo` (TEXT) — conteúdo criptografado (mesmo padrão da manifestação: JSON com iv, tag, ciphertext)
  - `criado_em` (DATETIME, null => true)
- **Índices:** `manifestacao_id`, `criado_em` (para ordenação)

**Tabela de anexos das respostas:**

**Arquivo:** `app/Database/Migrations/YYYY-MM-DD_HHMMSS_CreateRespostaOuvidorAnexos.php`

- **Tabela:** `resposta_ouvidor_anexos`
- **Campos:** análogos a `manifestacao_anexos`: `id`, `resposta_ouvidor_id` (FK), `enviado_por_usuario_id`, `nome_original`, `caminho_arquivo`, `mime`, `tamanho`, `hash`, `criado_em`
- **Diretório de upload:** seguir padrão do projeto, ex.: `uploads/ouvidoria/respostas/{resposta_id}/` (definir em `app/Config/Ouvidoria.php` se necessário)

### 1.3 Models

- **RespostaOuvidorModel** (`app/Models/RespostaOuvidorModel.php`)
  - `allowedFields`: `manifestacao_id`, `respondido_por_usuario_id`, `conteudo`, `criado_em`
  - Método `porManifestacao(int $manifestacaoId): array` ordenado por `criado_em` ASC ou DESC conforme exibição desejada.

- **RespostaOuvidorAnexoModel** (`app/Models/RespostaOuvidorAnexoModel.php`)
  - Método `porResposta(int $respostaOuvidorId): array`.

### 1.4 Criptografia

- Reutilizar a **DEK da manifestação** já armazenada em `manifestacao_chaves`:
  - No controller, ao salvar resposta: `$dek = $encryptionService->obterDEK($manifestacaoId);`
  - Criptografar apenas o campo `conteudo` com `$encryptionService->criptografarCampo($conteudo, $dek)` e gravar o retorno (JSON) em `respostas_ouvidor.conteudo`.
- Ao exibir no show: para cada resposta, obter DEK da manifestação e usar `$encryptionService->descriptografarCampo($resposta['conteudo'], $dek)` (apenas para quem pode visualizar conteúdo da manifestação).

**Nota:** Não é necessário nova tabela de chaves; a resposta pertence à manifestação e usa a mesma DEK.

### 1.5 Serviço de autorização

- **AuthorizationService:** adicionar método `podeResponderOuvidor(array $usuario, array $manifestacao): bool`
  - Retornar `true` para perfis `administrador` e `ouvidor` (mesmo critério de quem pode acessar a página e alterar status).
- Usar esse método no `ManifestacoesController::show` para passar uma variável à view (ex.: `$podeResponderOuvidor`) e no método que processa o POST da resposta.

### 1.6 Rotas

Em `app/Config/Routes.php`, dentro do grupo `ouvidoria`:

- `POST manifestacoes/responderOuvidor/(:num)` → `ManifestacoesController::responderOuvidor/$1`  
  Nome da rota: `ouvidoria.manifestacoes.responderOuvidor`

### 1.7 Controller

- **ManifestacoesController::responderOuvidor(int $id)**
  1. Obter usuário logado e manifestação; verificar `podeAcessarPaginaManifestacao` e `podeResponderOuvidor`.
  2. Validar POST: `conteudo` (texto do editor, obrigatório ou conforme regra de negócio).
  3. Iniciar transação:
     - Inserir em `respostas_ouvidor`: `manifestacao_id`, `respondido_por_usuario_id`, `conteudo` (criptografado via DEK da manifestação), `criado_em`.
     - Processar anexos da resposta (upload para pasta de respostas, inserir em `resposta_ouvidor_anexos`).
     - Atualizar `manifestacoes.status` para `respondida`.
     - Registrar evento no histórico da manifestação (ex.: novo tipo `RESPOSTA_OUVIDOR`).
  4. Retornar resposta JSON para o front (sucesso) para que o modal feche, o SweetAlert seja exibido e a página possa ser recarregada ou a seção de respostas atualizada via AJAX (conforme implementação escolhida).

### 1.8 View – Show da manifestação

- **Botão “Responder para o ouvidor”**
  - Exibir apenas se `$podeResponderOuvidor` for verdadeiro (ex.: ao lado de “Editar” / “Alterar status” no cabeçalho ou na coluna de ações).
  - Ao clicar: abrir modal (ex.: `#modalResponderOuvidor`).

- **Modal “Responder para o ouvidor”**
  - Título: “Responder para o ouvidor”.
  - Conteúdo:
    - Editor rico (Quill) para o texto da resposta (mesmo padrão do modal de encaminhamento/comentário já existente na show).
    - Campo de anexos: `<input type="file" name="anexos_resposta[]" multiple>` (e preview opcional).
    - Botão “Responder” (submit do form).
  - Form: `form_open_multipart(url_to('ouvidoria.manifestacoes.responderOuvidor', $manifestacao['id']))` com `csrf_field()`.
  - Envio: via AJAX (fetch/axios) para que a resposta seja JSON e se possa exibir SweetAlert e atualizar a página/box sem reload completo; ou submit tradicional com redirect e flash + SweetAlert na próxima carga.

- **Box “Respostas ao ouvidor”**
  - Posição: abaixo do bloco “Conteúdo” (e abaixo dos “Anexos” da manifestação, se fizer sentido), dentro da mesma coluna.
  - Exibir apenas se existir pelo menos uma resposta (`count($respostasOuvidor) > 0`).
  - Para cada resposta:
    - Nome do usuário que respondeu (buscar em `usuarios` por `respondido_por_usuario_id`).
    - Data/hora (formatação amigável).
    - Conteúdo descriptografado (HTML do editor), somente se `usuarioPodeVisualizar($manifestacao)`.
    - Lista de anexos da resposta com links para abrir/download (criar rotas e método em `AnexosController` ou controller dedicado para anexos de resposta, com checagem de permissão equivalente a “pode visualizar manifestação”).
  - Ordenação: por `criado_em` ascendente (mais antiga primeiro) ou descendente (mais recente primeiro), conforme padrão desejado.

### 1.9 Fluxo no show – dados e histórico

- **ManifestacoesController::show(int $id):**
  - Carregar respostas: `$respostasOuvidor = $respostaOuvidorModel->porManifestacao($id)`.
  - Para cada resposta, buscar nome do usuário (e anexos da resposta).
  - Se o usuário logado pode visualizar a manifestação, descriptografar o campo `conteudo` de cada resposta (usando DEK da manifestação) antes de enviar à view.
  - Passar `$podeResponderOuvidor`, `$respostasOuvidor` (e anexos por resposta) para a view.

### 1.10 SweetAlert e status

- Após gravar a resposta com sucesso:
  - Opção A (recomendada): retorno JSON `{ "success": true }` e no front (JavaScript) exibir SweetAlert “Resposta registrada com sucesso” e então `window.location.reload()` ou atualizar apenas o box de respostas e o badge de status.
  - Opção B: redirect para `ouvidoria.manifestacoes.show` com flash message e na view show exibir SweetAlert quando a flash estiver presente.
- O status da manifestação deve ser atualizado para `respondida` no mesmo request que persiste a resposta (no controller, como descrito acima).

### 1.11 Anexos das respostas – download/abertura

- Criar rotas, por exemplo:
  - `GET ouvidoria/anexos/resposta/abrir/(:num)` → método que recebe ID do anexo de resposta.
  - `GET ouvidoria/anexos/resposta/download/(:num)`.
- No controller (ex.: `AnexosController` ou `RespostaOuvidorController`): carregar anexo por ID, obter `resposta_ouvidor_id` → `manifestacao_id`, checar `podeVisualizarManifestacao` e então servir o arquivo (mesmo padrão de `AnexosController::abrir` para anexos da manifestação).

---

## 2. Categorias da Manifestação

### 2.1 Objetivo
Manter uma tabela de categorias (CRUD) acessível por menu, restrita a perfis **ouvidor** e **administrador**. Na criação/edição da manifestação, o ouvidor deve selecionar uma ou mais categorias (Select2 múltiplo). No show da manifestação, exibir as categorias da manifestação como TAGs.

### 2.2 Migration e tabelas

**Arquivo:** `app/Database/Migrations/YYYY-MM-DD_HHMMSS_CreateCategoriasManifestacao.php`

- **Tabela:** `categorias_manifestacao`
  - `id` (INT, PK, auto_increment)
  - `nome` (VARCHAR 150, único ou não conforme regra)
  - `ativo` (TINYINT 1, default 1) — opcional, para soft delete ou desativar categoria
  - `created_at`, `updated_at` (DATETIME, null => true)

**Arquivo:** `app/Database/Migrations/YYYY-MM-DD_HHMMSS_CreateManifestacaoCategorias.php`

- **Tabela:** `manifestacao_categorias` (N:N entre manifestação e categoria)
  - `id` (INT, PK, auto_increment)
  - `manifestacao_id` (INT, FK → manifestacoes.id, CASCADE)
  - `categoria_manifestacao_id` (INT, FK → categorias_manifestacao.id, CASCADE)
  - Chave única `(manifestacao_id, categoria_manifestacao_id)` para evitar duplicidade

### 2.3 Models

- **CategoriaManifestacaoModel** (`app/Models/CategoriaManifestacaoModel.php`)
  - CRUD básico; método `listarAtivas()` ou `findAll()` para popular Select2.

- **ManifestacaoCategoriaModel** (`app/Models/ManifestacaoCategoriaModel.php`)
  - `porManifestacao(int $manifestacaoId): array` — retorna linhas de `manifestacao_categorias` com dados da categoria (join ou busca separada).
  - `salvarParaManifestacao(int $manifestacaoId, array $categoriaIds): void` — remove associações atuais e insere novas (ou merge conforme regra).

### 2.4 Menu e permissão

- **Menu** (`app/Views/fixo/menu.php`):
  - Adicionar item **abaixo de “Usuários”**: “Categorias da Manifestação” (ou “Categorias”) com link para `url_to('ouvidoria.categoriasManifestacao.index')`.
  - Exibir apenas para `in_array($role, ['administrador', 'ouvidor'])`.

- **Rotas** (em `app/Config/Routes.php`, dentro do grupo `ouvidoria`):
  - `GET categorias-manifestacao` → `CategoriasManifestacaoController::index` — `ouvidoria.categoriasManifestacao.index`
  - `GET categorias-manifestacao/create` → `create` — `ouvidoria.categoriasManifestacao.create`
  - `POST categorias-manifestacao/store` → `store` — `ouvidoria.categoriasManifestacao.store`
  - `GET categorias-manifestacao/edit/(:num)` → `edit/$1` — `ouvidoria.categoriasManifestacao.edit`
  - `POST categorias-manifestacao/update/(:num)` → `update/$1` — `ouvidoria.categoriasManifestacao.update`
  - `GET categorias-manifestacao/delete/(:num)` → `delete/$1` — `ouvidoria.categoriasManifestacao.delete`

- **Filtro de perfil:** usar `['filter' => 'role:administrador,ouvidor']` nesse grupo de rotas ou checar no controller com `AuthorizationService`: método `podeGerenciarCategoriasManifestacao(array $usuario): bool` retornando `in_array($usuario['role'], ['administrador', 'ouvidor'])`.

### 2.5 Controller e views – CRUD Categorias

- **CategoriasManifestacaoController** (`app/Controllers/CategoriasManifestacaoController.php`)
  - Em cada método, verificar permissão (ouvidor/administrador); caso contrário, redirect com mensagem.
  - Index: listar categorias (tabela com nome, ativo, ações Editar/Excluir).
  - Create/Edit: formulário com campo `nome` (e `ativo` se houver).
  - Store/Update: validar e persistir em `categorias_manifestacao`.
  - Delete: remover categoria (e, por FK, as linhas de `manifestacao_categorias` serão removidas se CASCADE, ou tratar manualmente).

- **Views:** criar em `app/Views/ouvidoria/categorias_manifestacao/` (ou pasta equivalente): `index.php`, `form.php` (create/edit), seguindo o padrão visual de Setores/Usuários.

### 2.6 Manifestação – formulários create/edit

- **Create** (`app/Views/ouvidoria/manifestacoes/create.php`):
  - Adicionar campo: `<select name="categorias_ids[]" id="selectCategorias" class="form-select select2-multiple" multiple>`
  - Popular com `<?php foreach ($categorias as $c): ?> <option value="<?= $c['id'] ?>"><?= esc($c['nome']) ?></option> <?php endforeach; ?>`
  - Inicializar Select2 no script (placeholder “Selecione uma ou mais categorias”, `dropdownParent` se estiver em algum container que precise).

- **Edit** (`app/Views/ouvidoria/manifestacoes/edit.php`):
  - Mesmo select; no PHP, marcar como selected os `categorias_ids` já vinculados à manifestação (variável `$categoriasManifestacao` passada pelo controller).

- **ManifestacoesController::create:** passar `$categorias = $categoriaManifestacaoModel->listarAtivas()` (ou equivalente) para a view.

- **ManifestacoesController::store:** receber `$this->request->getPost('categorias_ids')` (array de IDs), validar (opcional) e após inserir a manifestação chamar `ManifestacaoCategoriaModel::salvarParaManifestacao($manifestacaoId, $categoriasIds)`.

- **ManifestacoesController::edit:** carregar categorias da manifestação e passar para a view; passar também lista de todas as categorias para o select.

- **ManifestacoesController::update:** mesmo tratamento: receber `categorias_ids[]`, chamar `salvarParaManifestacao($manifestacaoId, $categoriasIds)`.

### 2.7 Show da manifestação – TAGs de categorias

- No `ManifestacoesController::show`, carregar categorias da manifestação: `$categoriasManifestacao = $manifestacaoCategoriaModel->porManifestacao($id)` (com nome da categoria).
- Na view `show.php`, abaixo do cabeçalho (ou ao lado do status/origem), exibir um bloco “Categorias” com TAGs: para cada item de `$categoriasManifestacao`, exibir `<span class="badge bg-secondary me-1"><?= esc($categoria['nome']) ?></span>` (ou classe CSS desejada).

---

## 3. Ordem sugerida de implementação

1. **Categorias da Manifestação** (independente)
   - Migrations `categorias_manifestacao` e `manifestacao_categorias`
   - Models `CategoriaManifestacaoModel` e `ManifestacaoCategoriaModel`
   - Controller e views CRUD de categorias; rotas e menu (ouvidor + admin)
   - Integração em create/edit de manifestação (Select2 múltiplo) e em show (TAGs)

2. **Responder para o ouvidor**
   - Migrations `respostas_ouvidor` e `resposta_ouvidor_anexos`
   - Models `RespostaOuvidorModel` e `RespostaOuvidorAnexoModel`
   - Método de criptografia/descriptografia do conteúdo da resposta (EncryptionService: reutilizar DEK; pode adicionar método helper `criptografarRespostaOuvidor` / `descriptografarRespostaOuvidor` que usam `obterDEK` + `criptografarCampo`/`descriptografarCampo`)
   - AuthorizationService: `podeResponderOuvidor`
   - Rota e `ManifestacoesController::responderOuvidor` (incluindo upload de anexos, atualização de status, histórico)
   - View show: botão, modal com editor Quill e anexos, box de respostas, SweetAlert
   - Rotas e controller para abrir/download anexos das respostas

---

## 4. Checklist rápido

- [ ] Migration `respostas_ouvidor` e `resposta_ouvidor_anexos`
- [ ] Models RespostaOuvidor e RespostaOuvidorAnexo
- [ ] EncryptionService: usar DEK da manifestação para criptografar/descriptografar conteúdo da resposta
- [ ] AuthorizationService: `podeResponderOuvidor`
- [ ] Rota POST `manifestacoes/responderOuvidor/(:num)` e método `responderOuvidor`
- [ ] Show: botão “Responder para o ouvidor”, modal (Quill + anexos), box de respostas (usuário, conteúdo, anexos), SweetAlert e status “respondida”
- [ ] Anexos de resposta: rotas e controller para abrir/download
- [ ] Migration `categorias_manifestacao` e `manifestacao_categorias`
- [ ] Models CategoriaManifestacao e ManifestacaoCategoria
- [ ] Menu “Categorias da Manifestação” abaixo de Usuários (ouvidor + admin)
- [ ] CRUD CategoriasManifestacao (controller + views + rotas + filtro role)
- [ ] Create/Edit manifestação: Select2 múltiplo de categorias (1 ou N)
- [ ] Show manifestação: TAGs das categorias

---

*Documento gerado para o projeto Ouvidoria (CodeIgniter 4).*
