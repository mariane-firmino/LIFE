<?php

/**
 * Model Consulta
 *
 * Acesso à tabela `consulta`.
 * Usa a classe Database do seu framework (query / bind / executa /
 * resultado / resultados). Se os nomes dos métodos forem diferentes
 * no seu Database.php, ajuste só aqui.
 */
class Consulta
{
    /**
     * Fonte única de verdade dos tipos de consulta e suas cores.
     * Usada pelo Controller (JSON), pelos formulários e pela legenda.
     */
    public const TIPOS = [
        'Consulta - Periódica'   => '#198754',
        'Consulta - Emergencial' => '#dc3545',
        'Avaliação'              => '#0d6efd',
        'Retorno'                => '#6f42c1',
        'Psicóloga'              => '#e0a800',
    ];

    public const RESPONSAVEIS = [
        'Enfermeira',
        'Médico',
        'Técnico de enfermagem',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /** Todas as consultas, em ordem cronológica. */
    public function listarConsultas()
    {
        $this->db->query(
            'SELECT cons_id, cons_data, cons_horario, cons_tipo,
                    cons_responsavel, cons_descricao, cons_observacao
               FROM consulta
           ORDER BY cons_data ASC, cons_horario ASC'
        );

        return $this->db->resultados();
    }

    public function buscarPorId(int $id)
    {
        $this->db->query('SELECT * FROM consulta WHERE cons_id = :id');
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /** Espera as chaves: cons_data, cons_horario, cons_tipo,
     *  cons_responsavel, cons_descricao, cons_observacao */
    public function cadastrar(array $d): bool
    {
        $this->db->query(
            'INSERT INTO consulta
                (cons_data, cons_horario, cons_tipo, cons_responsavel,
                 cons_descricao, cons_observacao)
             VALUES
                (:cons_data, :cons_horario, :cons_tipo, :cons_responsavel,
                 :cons_descricao, :cons_observacao)'
        );

        foreach ($d as $campo => $valor) {
            $this->db->bind(':' . $campo, $valor);
        }

        return (bool) $this->db->executa();
    }

    /** Mesmas chaves do cadastrar() + cons_id. */
    public function atualizar(array $d): bool
    {
        $this->db->query(
            'UPDATE consulta SET
                cons_data        = :cons_data,
                cons_horario     = :cons_horario,
                cons_tipo        = :cons_tipo,
                cons_responsavel = :cons_responsavel,
                cons_descricao   = :cons_descricao,
                cons_observacao  = :cons_observacao
              WHERE cons_id = :cons_id'
        );

        foreach ($d as $campo => $valor) {
            $this->db->bind(':' . $campo, $valor);
        }

        return (bool) $this->db->executa();
    }

    /** Usado ao arrastar o compromisso no calendário. */
    public function reagendar(int $id, string $data, string $horario): bool
    {
        $this->db->query(
            'UPDATE consulta
                SET cons_data = :data, cons_horario = :horario
              WHERE cons_id = :id'
        );
        $this->db->bind(':data', $data);
        $this->db->bind(':horario', $horario);
        $this->db->bind(':id', $id);

        return (bool) $this->db->executa();
    }

    public function excluir(int $id): bool
    {
        $this->db->query('DELETE FROM consulta WHERE cons_id = :id');
        $this->db->bind(':id', $id);

        return (bool) $this->db->executa();
    }
}
