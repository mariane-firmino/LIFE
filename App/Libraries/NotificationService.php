<?php

require_once __DIR__ . '/EmailSender.php';

class NotificationService
{
    private $notificationModel;
    private $emailSender;

    public function __construct(Notificacao $notificationModel)
    {
        $this->notificationModel = $notificationModel;
        $this->emailSender = new EmailSender();
    }

    public function avisarUsuariosAtivos(
        string $categoria,
        string $titulo,
        string $descricao,
        string $rota,
        ?int $atorId = null
    ): void {
        try {
            $destinatarios = $this->notificationModel->listarUsuariosAtivos($categoria);
        } catch (PDOException $e) {
            error_log('Falha ao localizar destinatários de notificação: ' . $e->getMessage());
            return;
        }

        foreach ($destinatarios as $destinatario) {
            if ($atorId !== null && (int) $destinatario->id === $atorId) {
                continue;
            }

            $this->criar($destinatario, $titulo, $descricao, $categoria);

            if ((int) $destinatario->email_habilitado === 1) {
                $this->emailSender->enviarNotificacao(
                    $destinatario->email,
                    $destinatario->nome,
                    $titulo,
                    $descricao,
                    $rota
                );
            }
        }
    }

    public function avisarAdministradores(
        string $titulo,
        string $descricao,
        string $rota
    ): void {
        try {
            $destinatarios = $this->notificationModel->listarAdministradoresAtivos();
        } catch (PDOException $e) {
            error_log('Falha ao localizar administradores para notificação: ' . $e->getMessage());
            return;
        }

        foreach ($destinatarios as $destinatario) {
            $this->criar($destinatario, $titulo, $descricao, 'conta');

            if ((int) $destinatario->email_habilitado === 1) {
                $this->emailSender->enviarNotificacao(
                    $destinatario->email,
                    $destinatario->nome,
                    $titulo,
                    $descricao,
                    $rota
                );
            }
        }
    }

    public function avisarUsuario(
        object $destinatario,
        string $titulo,
        string $descricao,
        string $rota,
        bool $enviarEmail = false
    ): void {
        $this->criar($destinatario, $titulo, $descricao, 'conta');

        if ($enviarEmail && !empty($destinatario->email)) {
            $this->emailSender->enviarNotificacao(
                $destinatario->email,
                $destinatario->nome,
                $titulo,
                $descricao,
                $rota
            );
        }
    }

    private function criar(object $destinatario, string $titulo, string $descricao, string $tipo): void
    {
        try {
            $this->notificationModel->criarNotificacao([
                'id' => (int) $destinatario->id,
                'titulo' => $titulo,
                'descricao' => $descricao,
                'status' => 'Pendente',
                'tipo' => $tipo,
            ]);
        } catch (PDOException $e) {
            error_log('Falha ao gravar notificação: ' . $e->getMessage());
        }
    }
}
