<?php

class Consulta
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function cadastrar($dados)
    {
        $this->db->query("
            INSERT INTO consulta
            (
                cons_data,
                cons_horario,
                cons_tipo,
                cons_descricao,
                cons_observacao
            )
            VALUES
            (
                :data,
                :horario,
                :tipo,
                :descricao,
                :observacao
            )
        ");

        $this->db->bind(':data', $dados['cons_data']);
        $this->db->bind(':horario', $dados['cons_horario']);
        $this->db->bind(':tipo', $dados['cons_tipo']);
        $this->db->bind(':descricao', $dados['cons_descricao']);
        $this->db->bind(':observacao', $dados['cons_observacao']);

        return $this->db->executa();
    }
    public function listarConsultas()
{
    $this->db->query("
        SELECT *
        FROM consulta
        ORDER BY cons_data ASC, cons_horario ASC
    ");

    return $this->db->resultados();
}
}