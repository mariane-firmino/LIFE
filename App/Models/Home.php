<?php

class Home
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    private function contar($sql, array $parametros = [])
    {
        $this->db->query($sql);
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }

        $linha = $this->db->resultado();
        return (int) ($linha->total ?? 0);
    }

    public function contarNotificacoesNaoLidas($servidorId)
    {
        return $this->contar(
            "SELECT COUNT(*) AS total
               FROM notificacao
              WHERE serv_id = :serv_id
                AND noti_status IN ('Pendente', 'Não lido')",
            [':serv_id' => (int) $servidorId]
        );
    }

    public function contarAlunos()
    {
        return $this->contar('SELECT COUNT(*) AS total FROM aluno');
    }

    public function contarAtendimentosHoje($hoje)
    {
        return $this->contar(
            'SELECT COUNT(*) AS total
               FROM consulta
              WHERE cons_data = :hoje',
            [':hoje' => $hoje]
        );
    }

    public function contarAtendimentosPeriodicos($inicioMes, $inicioProximoMes)
    {
        return $this->contar(
            "SELECT COUNT(*) AS total
               FROM consulta
              WHERE cons_tipo LIKE :tipo
                AND cons_data >= :inicio
                AND cons_data < :fim",
            [
                ':tipo' => '%Periódica%',
                ':inicio' => $inicioMes,
                ':fim' => $inicioProximoMes,
            ]
        );
    }
}
