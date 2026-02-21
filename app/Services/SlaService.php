<?php

namespace App\Services;

/**
 * Serviço de SLA (Service Level Agreement).
 * Calcula prazos, data limite e flags de alerta.
 */
class SlaService
{
    /** Horas para considerar "a vencer" (padrão 48h). */
    private int $horasAVencer = 48;

    /**
     * Calcula data_limite_sla a partir da data base e prazo em dias.
     */
    public function calcularDataLimite(string $dataBase, int $prazoDias = 30): string
    {
        $data = new \DateTime($dataBase);
        $data->modify("+{$prazoDias} days");
        return $data->format('Y-m-d H:i:s');
    }

    /**
     * Retorna flags de SLA: em_atraso, a_vencer, no_prazo.
     * Status finalizados não contam SLA.
     */
    public function obterFlagsSla(array $manifestacao): array
    {
        $status = $manifestacao['status'] ?? '';
        if (in_array($status, ['finalizada', 'arquivada', 'respondida'])) {
            return [
                'em_atraso' => false,
                'a_vencer' => false,
                'no_prazo' => true,
                'sla_parado' => true,
            ];
        }

        $dataLimite = $manifestacao['data_limite_sla'] ?? null;
        if (!$dataLimite) {
            return [
                'em_atraso' => false,
                'a_vencer' => false,
                'no_prazo' => true,
                'sla_parado' => false,
            ];
        }

        $agora = new \DateTime();
        $limite = new \DateTime($dataLimite);
        $diff = $agora->diff($limite);

        $emAtraso = $agora > $limite;
        $horasRestantes = $diff->h + ($diff->days * 24);
        $aVencer = !$emAtraso && $horasRestantes <= $this->horasAVencer;

        return [
            'em_atraso' => $emAtraso,
            'a_vencer' => $aVencer,
            'no_prazo' => !$emAtraso && !$aVencer,
            'sla_parado' => false,
        ];
    }

    /**
     * Retorna label de status SLA para exibição.
     */
    public function obterLabelSla(array $manifestacao): string
    {
        $flags = $this->obterFlagsSla($manifestacao);
        if ($flags['sla_parado']) {
            return 'Encerrado';
        }
        if ($flags['em_atraso']) {
            return 'Em atraso';
        }
        if ($flags['a_vencer']) {
            return 'A vencer';
        }
        return 'No prazo';
    }

    /**
     * Retorna classe CSS para badge de SLA.
     */
    public function obterClasseSla(array $manifestacao): string
    {
        $flags = $this->obterFlagsSla($manifestacao);
        if ($flags['sla_parado']) {
            return 'secondary';
        }
        if ($flags['em_atraso']) {
            return 'danger';
        }
        if ($flags['a_vencer']) {
            return 'warning';
        }
        return 'success';
    }

    /**
     * Define horas para considerar "a vencer".
     */
    public function setHorasAVencer(int $horas): self
    {
        $this->horasAVencer = $horas;
        return $this;
    }
}
