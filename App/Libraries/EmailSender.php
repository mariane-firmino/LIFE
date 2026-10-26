<?php

class EmailSender
{
    public function enviarRecuperacao(string $email, string $nome, string $token): bool
    {
        $link = URL . '/usuarios/redefinirSenha/' . rawurlencode($token);
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $linkSeguro = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "
            <div style='font-family: Arial, sans-serif'>
                <h2>Enfermaria IFRO</h2>
                <p>Olá, <strong>{$nomeSeguro}</strong>.</p>
                <p>Recebemos uma solicitação para redefinir sua senha.</p>
                <p><a href='{$linkSeguro}'>Redefinir minha senha</a></p>
                <p>Este link será válido por 30 minutos. Se você não solicitou a recuperação, ignore este e-mail.</p>
            </div>
        ";

        return $this->enviar(
            $email,
            $nome,
            'Recuperação de senha - Enfermaria IFRO',
            $html,
            "Acesse o seguinte link para redefinir sua senha: {$link}"
        );
    }

    public function enviarNotificacao(
        string $email,
        string $nome,
        string $titulo,
        string $mensagem,
        string $rota
    ): bool {
        $link = URL . '/' . ltrim($rota, '/');
        $tituloSeguro = htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $mensagemSegura = htmlspecialchars($mensagem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $linkSeguro = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "
            <div style='font-family: Arial, sans-serif'>
                <h2>Enfermaria IFRO</h2>
                <h3>{$tituloSeguro}</h3>
                <p>{$mensagemSegura}</p>
                <p><a href='{$linkSeguro}'>Acessar o sistema</a></p>
            </div>
        ";

        return $this->enviar(
            $email,
            $nome,
            $titulo,
            $html,
            "{$titulo}\n\n{$mensagem}\n\nAcesse o sistema: {$link}"
        );
    }

    private function enviar(
        string $email,
        string $nome,
        string $assunto,
        string $html,
        string $texto
    ): bool {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            error_log('E-mail não enviado: dependências do PHPMailer não estão instaladas.');
            return false;
        }

        require_once $autoload;

        $host = getenv('LIFE_SMTP_HOST') ?: 'smtp.gmail.com';
        $usuario = getenv('LIFE_SMTP_USERNAME') ?: 'enfermariaifro@gmail.com';
        $senha = getenv('LIFE_SMTP_PASSWORD') ?: 'kekf kxtf ykxp iqyw';
        $remetente = getenv('LIFE_SMTP_FROM') ?: $usuario;
        $nomeRemetente = getenv('LIFE_SMTP_FROM_NAME') ?: 'LIFE IFRO';
        $porta = (int) (getenv('LIFE_SMTP_PORT') ?: 587);
        $seguranca = strtolower(getenv('LIFE_SMTP_SECURE') ?: 'tls');

        if ($usuario === '' || $senha === '' || $remetente === '' || $porta < 1) {
            error_log('E-mail não enviado: configure LIFE_SMTP_USERNAME e LIFE_SMTP_PASSWORD; LIFE_SMTP_FROM é opcional.');
            return false;
        }

        if (!in_array($seguranca, ['tls', 'ssl'], true)) {
            error_log('E-mail não enviado: LIFE_SMTP_SECURE deve ser tls ou ssl.');
            return false;
        }

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $usuario;
            $mail->Password = $senha;
            $mail->SMTPSecure = $seguranca === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $porta;
            $mail->setFrom($remetente, $nomeRemetente);
            $mail->addAddress($email, $nome);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $assunto;
            $mail->Body = $html;
            $mail->AltBody = $texto;
            $mail->send();

            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('Falha ao enviar e-mail: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
