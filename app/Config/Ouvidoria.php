<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configurações da Ouvidoria.
 */
class Ouvidoria extends BaseConfig
{
    /**
     * Master key para criptografia (DEK).
     * Obrigatório: pelo menos 32 caracteres.
     * Configure no .env: ouvidoria.encryption.master_key
     */
    public string $encryptionMasterKey = '';

    /**
     * Prazo padrão de SLA em dias.
     */
    public int $slaPrazoPadraoDias = 30;

    /**
     * Horas para alerta "a vencer".
     */
    public int $slaHorasAVencer = 48;

    /**
     * Diretório de uploads (relativo a writable).
     */
    public string $uploadsDir = 'uploads/ouvidoria';

    /**
     * Tamanho máximo de anexo em bytes (10MB).
     */
    public int $anexoMaxSize = 10485760;

    /**
     * Mimes permitidos para anexos.
     */
    public array $anexoMimesPermitidos = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->encryptionMasterKey = (string) env('ouvidoria.encryption.master_key', '');
    }
}
