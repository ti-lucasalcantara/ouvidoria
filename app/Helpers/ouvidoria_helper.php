<?php

/**
 * Helper da Ouvidoria.
 * Funções auxiliares para criptografia, autorização e usuário logado.
 */

if (!function_exists('obterUsuarioLogado')) {
    /**
     * Retorna usuário logado da sessão.
     * Sincroniza com tabela usuarios para obter role e setor_id.
     * Se usuário não existir na tabela, cadastra com perfil padrão 'usuario'.
     */
    function obterUsuarioLogado(): ?array
    {
        $usuario = session('usuario_logado');
        if (empty($usuario)) {
            return null;
        }

        $email = obterEmailDoUsuario($usuario);
        if (!$email) {
            return $usuario;
        }

        $usuarioModel = model(\App\Models\UsuarioModel::class);
        $interno = $usuarioModel->porEmail($email);

        if (!$interno) {
            // Cadastra usuário com perfil padrão 'usuario'
            sincronizarUsuarioNaTabela($usuario, $email);
            $interno = $usuarioModel->porEmail($email);
        }

        if ($interno) {
            $usuario['id'] = $interno['id'];
            $usuario['role'] = $interno['role'];
            $usuario['setor_id'] = $interno['setor_id'] ?? null;
        }

        return $usuario;
    }
}

if (!function_exists('obterEmailDoUsuario')) {
    /**
     * Extrai email do array de usuário (suporta variações do JWT/SSO).
     */
    function obterEmailDoUsuario(array $usuario): ?string
    {
        $chaves = ['email', 'Email', 'mail', 'e_mail', 'e-mail', 'emailAddress'];
        foreach ($chaves as $chave) {
            $email = $usuario[$chave] ?? null;
            if (!empty($email) && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }
        // Busca em subarrays (ex: user.profile.email)
        foreach ($usuario as $valor) {
            if (is_array($valor)) {
                $email = obterEmailDoUsuario($valor);
                if ($email) {
                    return $email;
                }
            }
        }
        return null;
    }
}

if (!function_exists('sincronizarUsuarioNaTabela')) {
    /**
     * Cadastra usuário na tabela usuarios com perfil padrão 'usuario'.
     */
    function sincronizarUsuarioNaTabela(array $usuario, string $email): void
    {
        $usuarioModel = model(\App\Models\UsuarioModel::class);
        if ($usuarioModel->porEmail($email)) {
            return; // Já existe
        }

        $nome = $usuario['nome'] ?? $usuario['name'] ?? $usuario['Nome'] ?? $usuario['displayName'] ?? $email;
        if (strlen($nome) < 2) {
            $nome = $email;
        }

        try {
            $usuarioModel->skipValidation(true);
            $usuarioModel->insert([
                'setor_id'  => null,
                'nome'      => $nome,
                'email'     => $email,
                'login'     => $usuario['login'] ?? $usuario['username'] ?? $usuario['preferred_username'] ?? null,
                'role'      => 'usuario',
                'ativo'     => 1,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Ouvidoria sincronizarUsuario: ' . $e->getMessage());
        } finally {
            $usuarioModel->skipValidation(false);
        }
    }
}

if (!function_exists('criptografarCampo')) {
    /**
     * Criptografa um campo com a DEK da manifestação.
     */
    function criptografarCampo(string $texto, string $dek): string
    {
        return service('encryption')->criptografarCampo($texto, $dek);
    }
}

if (!function_exists('descriptografarCampo')) {
    /**
     * Descriptografa um campo.
     */
    function descriptografarCampo(string $dadosCriptografados, string $dek): string
    {
        return service('encryption')->descriptografarCampo($dadosCriptografados, $dek);
    }
}

if (!function_exists('obterAssuntoExibicao')) {
    /**
     * Retorna o assunto para exibição na tabela.
     * Se o assunto estiver criptografado (JSON), retorna "Assunto protegido".
     */
    function obterAssuntoExibicao(string $assunto): string
    {
        $trimmed = trim($assunto);
        if ($trimmed === '') {
            return '';
        }
        // Dados criptografados são JSON com iv/tag/ciphertext
        if (str_starts_with($trimmed, '{"iv":') || str_starts_with($trimmed, '{"tag":')) {
            return 'Assunto protegido';
        }
        return $trimmed;
    }
}

if (!function_exists('statusLabelGerente')) {
    /**
     * Retorna label de status para a visão do gerente.
     * Quando ouvidor encaminha: gerente vê "Recebido".
     * Quando gerente já encaminhou para usuário: gerente vê "Encaminhado".
     * Quando usuário devolve: gerente vê "Devolvido".
     */
    function statusLabelGerente(array $item, bool $eDevolucao = false, bool $gerenteJaEncaminhou = false): string
    {
        if ($eDevolucao) {
            return 'Devolvido';
        }
        $status = $item['status'] ?? '';
        if ($status === 'encaminhada') {
            return $gerenteJaEncaminhou ? 'Encaminhado' : 'Recebido';
        }
        $map = [
            'recebida' => 'Recebida',
            'em_atendimento' => 'Em atendimento',
            'respondida' => 'Respondida',
            'finalizada' => 'Finalizada',
            'arquivada' => 'Arquivada',
        ];
        return $map[$status] ?? $status;
    }
}

if (!function_exists('statusLabelUsuario')) {
    /**
     * Retorna label de status para a visão do usuário (perfil usuario).
     * Quando recebe do gerente: "Recebido".
     * Quando gerente devolve/retorna (e_devolucao=1): "Devolvido".
     */
    function statusLabelUsuario(array $item, bool $eDevolucao = false): string
    {
        return $eDevolucao ? 'Devolvido' : 'Recebido';
    }
}

if (!function_exists('usuarioPodeVisualizar')) {
    /**
     * Verifica se usuário pode visualizar o conteúdo da manifestação.
     */
    function usuarioPodeVisualizar(array $manifestacao): bool
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return false;
        }
        return service('authorization')->podeVisualizarManifestacao($usuario, $manifestacao);
    }
}
