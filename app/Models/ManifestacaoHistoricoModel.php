<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela manifestacao_historico.
 * Log auditável de eventos das manifestações.
 */
class ManifestacaoHistoricoModel extends Model
{
    protected $table            = 'manifestacao_historico';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'usuario_id',
        'tipo_evento',
        'detalhes',
        'criado_em',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'criado_em';
    protected $updatedField  = '';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation       = true;
    protected $cleanValidationRules = true;

    /** Tipos de evento aceitos */
    public const TIPO_CRIACAO = 'CRIACAO';
    public const TIPO_ALTERACAO_STATUS = 'ALTERACAO_STATUS';
    public const TIPO_ENCAMINHAMENTO = 'ENCAMINHAMENTO';
    public const TIPO_COMENTARIO = 'COMENTARIO';
    public const TIPO_COMENTARIO_EDITADO = 'COMENTARIO_EDITADO';
    public const TIPO_COMENTARIO_EXCLUIDO = 'COMENTARIO_EXCLUIDO';
    public const TIPO_ANEXO_ADICIONADO = 'ANEXO_ADICIONADO';
    public const TIPO_REABERTURA = 'REABERTURA';
    public const TIPO_FINALIZACAO = 'FINALIZACAO';
    public const TIPO_ARQUIVAMENTO = 'ARQUIVAMENTO';
    public const TIPO_EDICAO_CAMPOS = 'EDICAO_CAMPOS';
    public const TIPO_SLA_ALERTA = 'SLA_ALERTA';
    public const TIPO_DEVOLUCAO = 'DEVOLUCAO';
    public const TIPO_VOLTA = 'VOLTA';
    public const TIPO_ATRIBUICAO_EDITADA = 'ATRIBUICAO_EDITADA';
    public const TIPO_ATRIBUICAO_EXCLUIDA = 'ATRIBUICAO_EXCLUIDA';

    /**
     * Registra evento no histórico.
     */
    public function registrar(int $manifestacaoId, ?int $usuarioId, string $tipoEvento, ?array $detalhes = null): int|false
    {
        return $this->insert([
            'manifestacao_id' => $manifestacaoId,
            'usuario_id'     => $usuarioId,
            'tipo_evento'    => $tipoEvento,
            'detalhes'       => $detalhes ? json_encode($detalhes) : null,
            'criado_em'      => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Retorna histórico de uma manifestação.
     */
    public function porManifestacao(int $manifestacaoId): array
    {
        return $this->select('manifestacao_historico.*, usuarios.nome as usuario_nome')
            ->join('usuarios', 'usuarios.id = manifestacao_historico.usuario_id', 'left')
            ->where('manifestacao_id', $manifestacaoId)
            ->orderBy('criado_em', 'DESC')
            ->findAll();
    }
}
