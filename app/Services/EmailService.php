<?php

namespace App\Services;

use CodeIgniter\Email\Email;

/**
 * Serviço de e-mail para notificações da ouvidoria.
 * Usa configuração do CI4 (Config\Email).
 */
class EmailService
{
    private Email $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
    }

    /**
     * Envia e-mail quando manifestação é encaminhada para usuários.
     */
    public function notificarEncaminhamento(array $destinatarios, string $protocolo, string $mensagem, string $remetenteNome): bool
    {
        $assunto = "[Ouvidoria] Manifestação {$protocolo} - Encaminhamento";
        $corpo = $this->montarCorpoEncaminhamento($protocolo, $mensagem, $remetenteNome);

        foreach ($destinatarios as $email => $nome) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->enviar($email, $assunto, $corpo, $nome);
            }
        }

        return true;
    }

    /**
     * Envia e-mail quando status da manifestação é alterado.
     */
    public function notificarAlteracaoStatus(string $emailDestino, string $nomeDestino, string $protocolo, string $statusAnterior, string $statusNovo): bool
    {
        if (!filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $assunto = "[Ouvidoria] Manifestação {$protocolo} - Status alterado";
        $corpo = "Prezado(a) {$nomeDestino},\n\n";
        $corpo .= "A manifestação {$protocolo} teve seu status alterado.\n";
        $corpo .= "Status anterior: {$statusAnterior}\n";
        $corpo .= "Novo status: {$statusNovo}\n\n";
        $corpo .= "Acesse o sistema para mais detalhes.\n";

        return $this->enviar($emailDestino, $assunto, $corpo, $nomeDestino);
    }

    /**
     * Envia e-mail de alerta de SLA próxima do vencimento.
     */
    public function notificarAlertaSla(string $emailDestino, string $nomeDestino, string $protocolo, string $dataLimite): bool
    {
        if (!filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $assunto = "[Ouvidoria] ALERTA SLA - Manifestação {$protocolo} próxima do vencimento";
        $corpo = "Prezado(a) {$nomeDestino},\n\n";
        $corpo .= "A manifestação {$protocolo} está próxima do prazo de atendimento.\n";
        $corpo .= "Data limite: {$dataLimite}\n\n";
        $corpo .= "Por favor, verifique o atendimento.\n";

        return $this->enviar($emailDestino, $assunto, $corpo, $nomeDestino);
    }

    /**
     * Monta corpo do e-mail de encaminhamento.
     */
    private function montarCorpoEncaminhamento(string $protocolo, string $mensagem, string $remetenteNome): string
    {
        $corpo = "Prezado(a),\n\n";
        $corpo .= "A manifestação {$protocolo} foi encaminhada para você por {$remetenteNome}.\n\n";
        if (!empty($mensagem)) {
            $corpo .= "Mensagem:\n{$mensagem}\n\n";
        }
        $corpo .= "Acesse o sistema para visualizar os detalhes.\n";

        return $corpo;
    }

    /**
     * Envia e-mail individual.
     */
    private function enviar(string $para, string $assunto, string $corpo, ?string $nomeDestino = null): bool
    {
        $this->email->clear();
        $this->email->setTo($para, $nomeDestino ?? '');
        $this->email->setSubject($assunto);
        $this->email->setMessage($corpo);

        return $this->email->send(false);
    }
}
