<?php

class Notificacao
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }
    public function criarNotificacao($dados)
    {
        $this->db->query("
            INSERT INTO notificacao (
                noti_titulo, noti_descricao, noti_status, noti_tipo, serv_id
            ) VALUES (
                :titulo, :descricao, :status, :tipo, :id
            )
        ");

        $this->db->bind(':id', (int) $dados['id']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':status', $dados['status'] ?? 'Pendente');
        $this->db->bind(':tipo', $dados['tipo'] ?? null);

        return $this->db->executa();
    }

    public function contarNotificacao($id)
    {
        $this->db->query("
        SELECT COUNT(*) AS total
        FROM notificacao
        WHERE serv_id = :id
        AND noti_status IN ('Pendente', 'Não lido')
    ");

        $this->db->bind('id', $id);

        return $this->db->resultado();
    }


    public function buscarPorId($id, $servidorId)
    {
        $this->db->query("
            SELECT *
            FROM notificacao
            WHERE noti_id = :id AND serv_id = :servidor_id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':servidor_id', (int) $servidorId);
        return $this->db->resultado();
    }
    public function excluirNotificacao($id, $servidorId)
    {
        $this->db->query("
            DELETE FROM notificacao
            WHERE noti_id = :id AND serv_id = :servidor_id
        ");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':servidor_id', (int) $servidorId);
        return $this->db->executa();
    }
    public function marcarComoLida($id, $servidorId)
    {
        $this->db->query("
            UPDATE notificacao
            SET noti_status = 'Lida'
            WHERE noti_id = :id AND serv_id = :servidor_id
        ");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':servidor_id', (int) $servidorId);
        return $this->db->executa();
    }

    public function listarUsuariosAtivos(string $categoria): array
    {
        if (!in_array($categoria, ['consulta', 'triagem'], true)) {
            throw new InvalidArgumentException('Categoria de notificação inválida.');
        }

        $coluna = $categoria === 'consulta' ? 'email_consultas' : 'email_triagens';

        $this->db->query("
            SELECT
                s.serv_id AS id,
                s.serv_nome AS nome,
                s.serv_email AS email,
                COALESCE(p.{$coluna}, 0) AS email_habilitado
            FROM servidor s
            LEFT JOIN preferencia_notificacao p ON p.serv_id = s.serv_id
            WHERE s.serv_status = 'ativo'
        ");

        return $this->db->resultados();
    }

    public function listarAdministradoresAtivos(): array
    {
        $this->db->query("
            SELECT s.serv_id AS id, s.serv_nome AS nome, s.serv_email AS email,
                   COALESCE(p.email_cadastros, 0) AS email_habilitado
            FROM servidor s
            LEFT JOIN preferencia_notificacao p ON p.serv_id = s.serv_id
            WHERE s.serv_perfil = 'admin' AND s.serv_status = 'ativo'
        ");

        return $this->db->resultados();
    }

    public function buscarPreferenciasEmail(int $servidorId): object
    {
        $this->db->query("
            SELECT email_consultas, email_triagens, email_cadastros
            FROM preferencia_notificacao
            WHERE serv_id = :servidor_id
            LIMIT 1
        ");
        $this->db->bind(':servidor_id', $servidorId);
        $preferencias = $this->db->resultado();

        return $preferencias ?: (object) [
            'email_consultas' => 0,
            'email_triagens' => 0,
            'email_cadastros' => 0,
        ];
    }

    public function salvarPreferenciasEmail(
        int $servidorId,
        bool $emailConsultas,
        bool $emailTriagens,
        bool $emailCadastros
    ): bool {
        $this->db->query("
            INSERT INTO preferencia_notificacao (
                serv_id, email_consultas, email_triagens, email_cadastros
            ) VALUES (
                :servidor_id, :email_consultas, :email_triagens, :email_cadastros
            )
            ON DUPLICATE KEY UPDATE
                email_consultas = VALUES(email_consultas),
                email_triagens = VALUES(email_triagens),
                email_cadastros = VALUES(email_cadastros)
        ");
        $this->db->bind(':servidor_id', $servidorId);
        $this->db->bind(':email_consultas', $emailConsultas);
        $this->db->bind(':email_triagens', $emailTriagens);
        $this->db->bind(':email_cadastros', $emailCadastros);

        return $this->db->executa();
    }
    public function mostarNotificacao($id, $pesquisa = '', $data = '', $status = '')
    {
        $sql = "SELECT * FROM notificacao 
            WHERE serv_id = :id";

        // Pesquisa por título ou descrição
        if (!empty($pesquisa)) {
            $sql .= " AND (
                    noti_titulo LIKE :pesquisa 
                    OR noti_descricao LIKE :pesquisa
                  )";
        }

        // Filtro por data
        if (!empty($data)) {
            $sql .= " AND DATE(noti_data) = :data";
        }

        // Filtro por status
        if ($status === 'nao_lidas') {
            $sql .= " AND noti_status IN ('Pendente', 'Não lido')";
        }

        if ($status === 'lidas') {
            $sql .= " AND noti_status = 'Lida'";
        }

        $sql .= " ORDER BY noti_data DESC";

        $this->db->query($sql);

        $this->db->bind('id', $id);

        if (!empty($pesquisa)) {
            $this->db->bind('pesquisa', '%' . $pesquisa . '%');
        }

        if (!empty($data)) {
            $this->db->bind('data', $data);
        }

        return $this->db->resultados();
    }
}
