<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para tabela manifestacao_categorias (N:N).
 */
class ManifestacaoCategoriaModel extends Model
{
    protected $table            = 'manifestacao_categorias';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'manifestacao_id',
        'categoria_manifestacao_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation       = true;
    protected $cleanValidationRules = true;

    /**
     * Retorna categorias vinculadas à manifestação (com nome da categoria).
     */
    public function porManifestacao(int $manifestacaoId): array
    {
        return $this->db->table($this->table . ' mc')
            ->select('mc.id, mc.manifestacao_id, mc.categoria_manifestacao_id, cm.nome AS categoria_nome')
            ->join('categorias_manifestacao cm', 'cm.id = mc.categoria_manifestacao_id')
            ->where('mc.manifestacao_id', $manifestacaoId)
            ->get()
            ->getResultArray();
    }

    /**
     * Retorna apenas os IDs das categorias de uma manifestação.
     */
    public function idsPorManifestacao(int $manifestacaoId): array
    {
        $rows = $this->where('manifestacao_id', $manifestacaoId)->findAll();
        return array_map(static fn($r) => (int) $r['categoria_manifestacao_id'], $rows);
    }

    /**
     * Substitui as categorias da manifestação pelas fornecidas.
     */
    public function salvarParaManifestacao(int $manifestacaoId, array $categoriaIds): void
    {
        $this->where('manifestacao_id', $manifestacaoId)->delete();
        $categoriaIds = array_filter(array_map('intval', $categoriaIds));
        foreach ($categoriaIds as $catId) {
            $this->insert([
                'manifestacao_id'            => $manifestacaoId,
                'categoria_manifestacao_id'   => $catId,
            ]);
        }
    }
}
