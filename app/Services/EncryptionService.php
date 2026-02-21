<?php

namespace App\Services;

use App\Models\ManifestacaoChaveModel;
use InvalidArgumentException;

/**
 * Serviço de criptografia para manifestações.
 * Utiliza DEK (Data Encryption Key) única por manifestação, criptografada com Master Key.
 * Campos sensíveis: assunto, descricao, dados_identificacao.
 */
class EncryptionService
{
    private const ALGORITMO = 'aes-256-gcm';
    private const IV_LENGTH = 12;
    private const TAG_LENGTH = 16;

    private string $masterKey;
    private ManifestacaoChaveModel $chaveModel;

    public function __construct()
    {
        $config = config('Ouvidoria');
        $this->masterKey = $config->encryptionMasterKey ?? '';
        if (strlen($this->masterKey) < 32) {
            throw new InvalidArgumentException('Master key deve ter pelo menos 32 caracteres. Configure ouvidoria.encryption.master_key no .env');
        }
        $this->chaveModel = model(ManifestacaoChaveModel::class);
    }

    /**
     * Gera uma nova DEK (32 bytes para AES-256).
     */
    public function gerarDEK(): string
    {
        return random_bytes(32);
    }

    /**
     * Criptografa a DEK com a Master Key para armazenamento.
     */
    public function criptografarDEK(string $dek): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $dek,
            self::ALGORITMO,
            $this->masterKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new InvalidArgumentException('Falha ao criptografar DEK');
        }

        return json_encode([
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'ciphertext' => base64_encode($ciphertext),
        ]);
    }

    /**
     * Descriptografa a DEK armazenada.
     */
    public function descriptografarDEK(string $chaveEncriptada): string
    {
        $dados = json_decode($chaveEncriptada, true);
        if (!$dados || !isset($dados['iv'], $dados['tag'], $dados['ciphertext'])) {
            throw new InvalidArgumentException('Formato de chave inválido');
        }

        $iv = base64_decode($dados['iv'], true);
        $tag = base64_decode($dados['tag'], true);
        $ciphertext = base64_decode($dados['ciphertext'], true);

        $dek = openssl_decrypt(
            $ciphertext,
            self::ALGORITMO,
            $this->masterKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($dek === false) {
            throw new InvalidArgumentException('Falha ao descriptografar DEK');
        }

        return $dek;
    }

    /**
     * Criptografa um campo com a DEK.
     * Retorna JSON com iv, tag e ciphertext em base64.
     */
    public function criptografarCampo(string $texto, string $dek): string
    {
        if ($texto === '') {
            return '';
        }

        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $texto,
            self::ALGORITMO,
            $dek,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new InvalidArgumentException('Falha ao criptografar campo');
        }

        return json_encode([
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'ciphertext' => base64_encode($ciphertext),
        ]);
    }

    /**
     * Descriptografa um campo.
     * Aceita string JSON ou texto vazio.
     */
    public function descriptografarCampo(string $dadosCriptografados, string $dek): string
    {
        if ($dadosCriptografados === '') {
            return '';
        }

        $dados = json_decode($dadosCriptografados, true);
        if (!$dados || !isset($dados['iv'], $dados['tag'], $dados['ciphertext'])) {
            return $dadosCriptografados; // Possível dado legado não criptografado
        }

        $iv = base64_decode($dados['iv'], true);
        $tag = base64_decode($dados['tag'], true);
        $ciphertext = base64_decode($dados['ciphertext'], true);

        $texto = openssl_decrypt(
            $ciphertext,
            self::ALGORITMO,
            $dek,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $texto !== false ? $texto : '';
    }

    /**
     * Gera e armazena DEK para nova manifestação.
     */
    public function gerarEArmazenarDEK(int $manifestacaoId): string
    {
        $dek = $this->gerarDEK();
        $chaveEncriptada = $this->criptografarDEK($dek);

        $this->chaveModel->insert([
            'manifestacao_id' => $manifestacaoId,
            'chave_encriptada_por_sistema' => $chaveEncriptada,
            'algoritmo' => self::ALGORITMO,
            'criado_em' => date('Y-m-d H:i:s'),
        ]);

        return $dek;
    }

    /**
     * Obtém a DEK de uma manifestação (descriptografada).
     */
    public function obterDEK(int $manifestacaoId): ?string
    {
        $chave = $this->chaveModel->porManifestacao($manifestacaoId);
        if (!$chave) {
            return null;
        }

        return $this->descriptografarDEK($chave['chave_encriptada_por_sistema']);
    }

    /**
     * Criptografa campos sensíveis de uma manifestação.
     */
    public function criptografarManifestacao(array $dados, string $dek): array
    {
        $campos = ['assunto', 'descricao', 'dados_identificacao'];
        foreach ($campos as $campo) {
            if (!empty($dados[$campo]) && is_string($dados[$campo])) {
                $dados[$campo] = $this->criptografarCampo($dados[$campo], $dek);
            }
            if (isset($dados[$campo]) && is_array($dados[$campo])) {
                $dados[$campo] = $this->criptografarCampo(json_encode($dados[$campo]), $dek);
            }
        }
        return $dados;
    }

    /**
     * Descriptografa campos sensíveis de uma manifestação.
     */
    public function descriptografarManifestacao(array $manifestacao, ?string $dek = null): array
    {
        if ($dek === null) {
            $dek = $this->obterDEK((int) $manifestacao['id']);
        }
        if (!$dek) {
            return $manifestacao;
        }

        $campos = ['assunto', 'descricao', 'dados_identificacao'];
        foreach ($campos as $campo) {
            if (!empty($manifestacao[$campo])) {
                $manifestacao[$campo] = $this->descriptografarCampo($manifestacao[$campo], $dek);
            }
        }
        return $manifestacao;
    }
}
