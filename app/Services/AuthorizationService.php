<?php

namespace App\Services;

use App\Models\ManifestacaoAtribuicaoModel;

/**
 * Serviço de autorização para manifestações.
 * Controla quem pode visualizar, encaminhar e alterar status.
 */
class AuthorizationService
{
    private ManifestacaoAtribuicaoModel $atribuicaoModel;

    public function __construct()
    {
        $this->atribuicaoModel = model(ManifestacaoAtribuicaoModel::class);
    }

    /**
     * Roles com acesso total (veem tudo).
     */
    private function rolesAcessoTotal(): array
    {
        return ['administrador', 'ouvidor'];
    }

    /**
     * Verifica se usuário pode acessar a página da manifestação (navegar até ela).
     * Admin e Ouvidor: sempre. Outros: apenas se criou ou está vinculado.
     */
    public function podeAcessarPaginaManifestacao(array $usuario, array $manifestacao): bool
    {
        if (in_array($usuario['role'] ?? '', $this->rolesAcessoTotal())) {
            return true;
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);
        if ($usuarioId === 0) {
            return false;
        }

        return $usuarioId === (int) ($manifestacao['criado_por_usuario_id'] ?? 0)
            || $this->atribuicaoModel->usuarioVinculado((int) $manifestacao['id'], $usuarioId);
    }

    /**
     * Verifica se usuário pode visualizar o conteúdo (assunto e descrição) da manifestação.
     * Ouvidor: sempre. Admin e outros: apenas se criou ou está vinculado (atribuído a ele).
     */
    public function podeVisualizarManifestacao(array $usuario, array $manifestacao): bool
    {
        if (($usuario['role'] ?? '') === 'ouvidor') {
            return true;
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);
        if ($usuarioId === 0) {
            return false;
        }

        return $usuarioId === (int) ($manifestacao['criado_por_usuario_id'] ?? 0)
            || $this->atribuicaoModel->usuarioVinculado((int) $manifestacao['id'], $usuarioId);
    }

    /**
     * Verifica se usuário pode editar manifestação (campos da manifestação).
     * Somente o ouvidor que cadastrou a manifestação.
     */
    public function podeEditarManifestacao(array $usuario, array $manifestacao): bool
    {
        if (($usuario['role'] ?? '') !== 'ouvidor') {
            return false;
        }
        return (int) ($usuario['id'] ?? 0) === (int) ($manifestacao['criado_por_usuario_id'] ?? 0);
    }

    /**
     * Verifica se usuário pode criar manifestação.
     */
    public function podeCriarManifestacao(array $usuario): bool
    {
        return in_array($usuario['role'] ?? '', ['administrador', 'ouvidor']);
    }

    /**
     * Verifica se usuário pode encaminhar manifestação.
     */
    public function podeEncaminhar(array $usuario, array $manifestacao): bool
    {
        if (in_array($usuario['role'] ?? '', $this->rolesAcessoTotal())) {
            return true;
        }

        if (($usuario['role'] ?? '') === 'gerente') {
            return $this->podeVisualizarManifestacao($usuario, $manifestacao);
        }

        // Usuário comum não pode encaminhar
        return false;
    }

    /**
     * Verifica se usuário pode alterar status.
     * Somente administrador e ouvidor.
     */
    public function podeAlterarStatus(array $usuario, array $manifestacao): bool
    {
        return in_array($usuario['role'] ?? '', $this->rolesAcessoTotal());
    }

    /**
     * Verifica se usuário pode "voltar" (reverter devolução) manifestação que devolveu.
     * Somente perfil usuario, quando devolveu e status ainda é encaminhada/respondida.
     */
    public function podeVoltar(array $usuario, array $manifestacao, ?array $atribuicaoDevolucaoUsuario): bool
    {
        if (($usuario['role'] ?? '') !== 'usuario' || empty($atribuicaoDevolucaoUsuario)) {
            return false;
        }
        return in_array($manifestacao['status'] ?? '', ['encaminhada', 'respondida', 'em_atendimento']);
    }

    /**
     * Verifica se usuário pode reabrir manifestação.
     * Admin/ouvidor: sempre que status for finalizada/arquivada.
     * Usuário: quando pode visualizar E status for finalizada/arquivada.
     */
    public function podeReabrir(array $usuario, array $manifestacao): bool
    {
        if (!in_array($manifestacao['status'] ?? '', ['finalizada', 'arquivada'])) {
            return false;
        }
        if (in_array($usuario['role'] ?? '', $this->rolesAcessoTotal())) {
            return true;
        }
        if (($usuario['role'] ?? '') === 'usuario') {
            return $this->podeVisualizarManifestacao($usuario, $manifestacao);
        }
        return false;
    }

    /**
     * Verifica se usuário pode adicionar comentário.
     */
    public function podeComentar(array $usuario, array $manifestacao): bool
    {
        return $this->podeVisualizarManifestacao($usuario, $manifestacao);
    }

    /**
     * Verifica se usuário pode editar ou excluir um comentário.
     * Somente o autor do comentário ou o administrador do sistema.
     */
    public function podeEditarExcluirComentario(array $usuario, array $manifestacao, array $historicoComentario): bool
    {
        if (($usuario['role'] ?? '') === 'administrador') {
            return true;
        }
        return (int) ($usuario['id'] ?? 0) === (int) ($historicoComentario['usuario_id'] ?? 0);
    }

    /**
     * Verifica se usuário pode editar ou excluir uma atribuição.
     * Somente o autor da atribuição (quem fez o encaminhamento) ou o administrador do sistema.
     */
    public function podeEditarExcluirAtribuicao(array $usuario, array $manifestacao, array $atribuicao): bool
    {
        if (($usuario['role'] ?? '') === 'administrador') {
            return true;
        }
        return (int) ($usuario['id'] ?? 0) === (int) ($atribuicao['atribuido_por_usuario_id'] ?? 0);
    }

    /**
     * Verifica se usuário pode adicionar anexo.
     */
    public function podeAdicionarAnexo(array $usuario, array $manifestacao): bool
    {
        return $this->podeVisualizarManifestacao($usuario, $manifestacao);
    }

    /**
     * Verifica se usuário pode fazer download de anexo.
     */
    public function podeDownloadAnexo(array $usuario, array $manifestacao): bool
    {
        return $this->podeVisualizarManifestacao($usuario, $manifestacao);
    }

    /**
     * Verifica se usuário pode gerenciar setores (CRUD).
     */
    public function podeGerenciarSetores(array $usuario): bool
    {
        return ($usuario['role'] ?? '') === 'administrador';
    }

    /**
     * Verifica se usuário pode gerenciar usuários (CRUD).
     */
    public function podeGerenciarUsuarios(array $usuario): bool
    {
        return ($usuario['role'] ?? '') === 'administrador';
    }

    /**
     * Verifica se usuário pode devolver manifestação ao quem lhe encaminhou.
     * Somente perfil 'usuario' pode devolver. Retorna a atribuição ativa onde o usuário é o destinatário.
     */
    public function obterAtribuicaoParaDevolver(array $usuario, int $manifestacaoId): ?array
    {
        if (($usuario['role'] ?? '') !== 'usuario') {
            return null;
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);
        if ($usuarioId === 0) {
            return null;
        }

        return $this->atribuicaoModel
            ->where('manifestacao_id', $manifestacaoId)
            ->where('atribuido_para_usuario_id', $usuarioId)
            ->where('ativo', 1)
            ->orderBy('criado_em', 'DESC')
            ->first();
    }

    /**
     * Verifica se usuário pode devolver manifestação (somente perfil usuario).
     */
    public function podeDevolver(array $usuario, array $manifestacao): bool
    {
        return $this->obterAtribuicaoParaDevolver($usuario, (int) $manifestacao['id']) !== null;
    }
}
