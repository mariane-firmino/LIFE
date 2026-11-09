<?php

class EmailSender
{
    // Conexão SMTP reaproveitada quando vários e-mails saem na mesma requisição.
    private $mail = null;

    public function __destruct()
    {
        if ($this->mail !== null) {
            try {
                $this->mail->smtpClose();
            } catch (Throwable $e) {
                // nada a fazer
            }
        }
    }

    // Endereço usado nos links dos e-mails.
    // "localhost" só abre no próprio computador do servidor; por isso,
    // quando o sistema é acessado por outro endereço (IP da rede, domínio),
    // os links usam esse mesmo endereço. Para fixar um endereço, defina a
    // variável de ambiente LIFE_URL_PUBLICA (ex.: http://192.168.0.10/life).
    private function urlBase(): string
    {
        $publica = trim((string) getenv('LIFE_URL_PUBLICA'));
        if ($publica !== '') {
            return rtrim($publica, '/');
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '' && preg_match('/^[A-Za-z0-9.\-\[\]:]+$/', $host)) {
            $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
            $caminho = (string) parse_url(URL, PHP_URL_PATH);
            return ($https ? 'https' : 'http') . '://' . $host . rtrim($caminho, '/');
        }

        return URL;
    }

    public function enviarRecuperacao(string $email, string $nome, string $token): bool
    {
        $link = $this->urlBase() . '/usuarios/redefinirSenha/' . rawurlencode($token);
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
        $link = $this->urlBase() . '/' . ltrim($rota, '/');
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
        // Senha de app do Gmail: é mostrada com espaços, mas é usada sem eles.
        $senha = str_replace(' ', '', (string) (getenv('LIFE_SMTP_PASSWORD') ?: 'kekf kxtf ykxp iqyw'));
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log('E-mail não enviado: endereço inválido (' . $email . ').');
            return false;
        }

        $mail = null;
        try {
            if ($this->mail === null) {
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
                // Sem isso o PHPMailer espera até 5 minutos e a página "trava".
                $mail->Timeout = 15;
                // Reaproveita a conexão para avisar vários usuários de uma vez.
                $mail->SMTPKeepAlive = true;
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);
                $mail->setFrom($remetente, $nomeRemetente);
                $this->mail = $mail;
            }

            $mail = $this->mail;
            $mail->clearAddresses();
            $mail->addAddress($email, $nome);
            $mail->Subject = $assunto;
            $mail->Body = $html;
            $mail->AltBody = $texto;
            $mail->send();

            return true;
        } catch (Throwable $e) {
            $detalhe = ($mail !== null && $mail->ErrorInfo !== '') ? $mail->ErrorInfo : $e->getMessage();
            error_log('Falha ao enviar e-mail para ' . $email . ': ' . $detalhe);

            // Conexão pode ter caído: a próxima tentativa abre outra.
            if ($this->mail !== null) {
                try {
                    $this->mail->smtpClose();
                } catch (Throwable $ignorado) {
                }
                $this->mail = null;
            }
            return false;
        }
    }
}
