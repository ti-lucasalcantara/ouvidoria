<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela manifestacoes.
 * Representa as manifestações da ouvidoria.
 */
class ManifestacaoModel extends Model
{
    protected $table            = 'manifestacoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'protocolo',
        'protocolo_falabr',
        'origem',
        'data_manifestacao',
        'assunto',
        'descricao',
        'dados_identificacao',
        'status',
        'prioridade',
        'sla_prazo_em_dias',
        'data_limite_sla',
        'data_primeiro_encaminhamento',
        'data_finalizacao',
        'criado_por_usuario_id',
        'inscricao_pj_id',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    protected $validationRules = [
        'protocolo' => 'required|max_length[50]',
        'origem' => 'max_length[150]',
        'status' => 'required|in_list[recebida,encaminhada,em_atendimento,respondida,finalizada,arquivada]',
        'prioridade' => 'in_list[baixa,media,alta]',
        'sla_prazo_em_dias' => 'permit_empty|integer|greater_than[0]',
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Gera próximo protocolo no formato OUV-YYYY-000001.
     */
    public function gerarProtocolo(): string
    {
        $ano = date('Y');
        $ultimo = $this->select('protocolo')
            ->like('protocolo', "OUV-{$ano}-")
            ->orderBy('id', 'DESC')
            ->first();

        $numero = 1;
        if ($ultimo && preg_match('/OUV-\d{4}-(\d+)/', $ultimo['protocolo'], $m)) {
            $numero = (int) $m[1] + 1;
        }

        return sprintf('OUV-%s-%06d', $ano, $numero);
    }

    /**
     * Retorna status possíveis para reabertura.
     */
    public static function statusReabertura(): array
    {
        return ['em_atendimento', 'encaminhada'];
    }

    /**
     * Verifica se status permite reabertura.
     */
    public static function permiteReabertura(string $status): bool
    {
        return in_array($status, ['finalizada', 'arquivada']);
    }
}
