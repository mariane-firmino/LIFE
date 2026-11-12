<?php

// =========================================================
// DISPOSITIVO
// Controla as sessões (dispositivos) em que cada servidor
// está logado. Usado pela página "Gerenciar dispositivos".
// =========================================================

class Dispositivo
{
    private $db;
    private static $tabelaVerificada = false;

    public function __construct()
    {
        $this->db = new Database();
        $this->garantirTabela();
    }

    // Cria a tabela caso ainda não exista no banco.
    private function garantirTabela()
    {
        if (self::$tabelaVerificada) {
            return;
        }

        $this->db->query("
            CREATE TABLE IF NOT EXISTS sessao_dispositivo (
                sess_id int NOT NULL AUTO_INCREMENT,
                serv_id int NOT NULL,
                sess_token char(64) NOT NULL,
                sess_dispositivo varchar(60) NOT NULL,
                sess_navegador varchar(60) NOT NULL,
                sess_ip varchar(45) NOT NULL,
                sess_user_agent varchar(500) DEFAULT NULL,
                sess_criado_em datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                sess_ultima_atividade datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                sess_ativa tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (sess_id),
                UNIQUE KEY uk_sessao_token (sess_token),
                KEY ix_sessao_servidor (serv_id, sess_ativa),
                CONSTRAINT fk_sessao_servidor FOREIGN KEY (serv_id)
                    REFERENCES servidor (serv_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->db->executa();

        self::$tabelaVerificada = true;
    }

    // Registra o dispositivo atual e devolve o token da sessão.
    public function registrar($servId)
    {
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $token = bin2hex(random_bytes(32));

        $this->db->query("
            INSERT INTO sessao_dispositivo
                (serv_id, sess_token, sess_dispositivo, sess_navegador, sess_ip, sess_user_agent)
            VALUES
                (:serv, :token, :dispositivo, :navegador, :ip, :ua)
        ");
        $this->db->bind(':serv', (int) $servId);
        $this->db->bind(':token', $token);
        $this->db->bind(':dispositivo', self::detectarDispositivo($userAgent));
        $this->db->bind(':navegador', self::detectarNavegador($userAgent));
        $this->db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? 'desconhecido');
        $this->db->bind(':ua', $userAgent);
        $this->db->executa();

        return $token;
    }

    // Verifica se a sessão ainda é válida (não foi encerrada remotamente).
    // O servidor ainda existe e está ativo? (desativado pelo admin = sem acesso)
    public function servidorAtivo($servId)
    {
        $this->db->query("SELECT 1 AS ok FROM servidor WHERE serv_id = :serv AND serv_status = 'ativo' LIMIT 1");
        $this->db->bind(':serv', (int) $servId);

        return (bool) $this->db->resultado();
    }

    public function sessaoAtiva($servId, $token)
    {
        $this->db->query("
            SELECT sess_id, TIMESTAMPDIFF(SECOND, sess_ultima_atividade, NOW()) AS segundos_inativo
            FROM sessao_dispositivo
            WHERE serv_id = :serv AND sess_token = :token AND sess_ativa = 1
            LIMIT 1
        ");
        $this->db->bind(':serv', (int) $servId);
        $this->db->bind(':token', $token);

        return $this->db->resultado();
    }

    // Atualiza a data da última atividade da sessão.
    public function atualizarAtividade($token)
    {
        $this->db->query("
            UPDATE sessao_dispositivo
            SET sess_ultima_atividade = NOW(), sess_ip = :ip
            WHERE sess_token = :token
        ");
        $this->db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? 'desconhecido');
        $this->db->bind(':token', $token);

        return $this->db->executa();
    }

    // Lista os dispositivos ativos do servidor (o atual primeiro).
    public function listarAtivos($servId, $tokenAtual)
    {
        $this->db->query("
            SELECT sess_id, sess_dispositivo, sess_navegador, sess_ip,
                   sess_criado_em, sess_ultima_atividade,
                   DATE_FORMAT(sess_criado_em, '%d/%m/%Y %H:%i') AS criado_formatado,
                   TIMESTAMPDIFF(SECOND, sess_ultima_atividade, NOW()) AS segundos_inativo,
                   DATE_FORMAT(sess_ultima_atividade, '%d/%m/%Y %H:%i') AS atividade_formatada,
                   (sess_token = :token) AS atual
            FROM sessao_dispositivo
            WHERE serv_id = :serv AND sess_ativa = 1
            ORDER BY atual DESC, sess_ultima_atividade DESC
        ");
        $this->db->bind(':serv', (int) $servId);
        $this->db->bind(':token', (string) $tokenAtual);

        return $this->db->resultados();
    }

    // Encerra uma sessão específica (somente se for do próprio servidor).
    public function encerrar($sessId, $servId)
    {
        $this->db->query("
            UPDATE sessao_dispositivo
            SET sess_ativa = 0
            WHERE sess_id = :id AND serv_id = :serv
        ");
        $this->db->bind(':id', (int) $sessId);
        $this->db->bind(':serv', (int) $servId);
        $this->db->executa();

        return $this->db->totalResultados() > 0;
    }

    // Encerra a sessão pelo token (usado no logout).
    public function encerrarPorToken($token)
    {
        $this->db->query("UPDATE sessao_dispositivo SET sess_ativa = 0 WHERE sess_token = :token");
        $this->db->bind(':token', $token);

        return $this->db->executa();
    }

    // Encerra todas as sessões, exceto a atual. Retorna quantas foram encerradas.
    public function encerrarOutras($servId, $tokenAtual)
    {
        $this->db->query("
            UPDATE sessao_dispositivo
            SET sess_ativa = 0
            WHERE serv_id = :serv AND sess_ativa = 1 AND sess_token <> :token
        ");
        $this->db->bind(':serv', (int) $servId);
        $this->db->bind(':token', (string) $tokenAtual);
        $this->db->executa();

        return $this->db->totalResultados();
    }

    // ---------------------------------------------------------
    // Identificação simples a partir do User-Agent
    // ---------------------------------------------------------

    public static function detectarDispositivo($ua)
    {
        $sistemas = [
            '/iPad/i' => 'iPad',
            '/iPhone/i' => 'iPhone',
            '/Android.*Mobile/i' => 'Celular Android',
            '/Android/i' => 'Tablet Android',
            '/Windows/i' => 'Windows',
            '/CrOS/i' => 'Chromebook',
            '/Macintosh|Mac OS X/i' => 'Mac',
            '/Linux/i' => 'Linux',
        ];

        foreach ($sistemas as $padrao => $nome) {
            if (preg_match($padrao, $ua)) {
                return $nome;
            }
        }

        return 'Desconhecido';
    }

    public static function detectarNavegador($ua)
    {
        // A ordem importa: Edge e Opera também dizem "Chrome".
        $navegadores = [
            '/Edg\//i' => 'Edge',
            '/OPR\/|Opera/i' => 'Opera',
            '/SamsungBrowser/i' => 'Samsung Internet',
            '/Firefox|FxiOS/i' => 'Firefox',
            '/Chrome|CriOS/i' => 'Chrome',
            '/Safari/i' => 'Safari',
        ];

        foreach ($navegadores as $padrao => $nome) {
            if (preg_match($padrao, $ua)) {
                return $nome;
            }
        }

        return 'Desconhecido';
    }
}
