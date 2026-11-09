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
    private static $colunaAlunoVerificada = false;

    public function __construct()
    {
        $this->db = new Database;
        $this->garantirColunaAluno();
    }

    /**
     * A consulta passou a ter um aluno (opcional). Se o banco ainda
     * não tiver a coluna alun_id, ela é criada aqui (mesmo script do
     * arquivo consulta_aluno.sql na raiz do projeto).
     */
    private function garantirColunaAluno(): void
    {
        if (self::$colunaAlunoVerificada) {
            return;
        }

        $this->db->query("SHOW COLUMNS FROM consulta LIKE 'alun_id'");
        if (!$this->db->resultado()) {
            $this->db->query('ALTER TABLE consulta ADD COLUMN alun_id INT NULL DEFAULT NULL AFTER cons_id, ADD KEY ix_consulta_aluno (alun_id)');
            $this->db->executa();

            try {
                $this->db->query('ALTER TABLE consulta ADD CONSTRAINT fk_consulta_aluno FOREIGN KEY (alun_id) REFERENCES aluno (alun_id) ON DELETE SET NULL');
                $this->db->executa();
            } catch (Throwable $e) {
                // Sem a chave estrangeira o sistema funciona do mesmo jeito.
            }
        }

        self::$colunaAlunoVerificada = true;
    }

    /** Todas as consultas, em ordem cronológica (com o nome do aluno). */
    public function listarConsultas()
    {
        $this->db->query(
            'SELECT c.cons_id, c.cons_data, c.cons_horario, c.cons_tipo,
                    c.cons_responsavel, c.cons_descricao, c.cons_observacao,
                    c.alun_id, a.alun_nome, a.alun_matricula
               FROM consulta c
          LEFT JOIN aluno a ON a.alun_id = c.alun_id
           ORDER BY c.cons_data ASC, c.cons_horario ASC'
        );

        return $this->db->resultados();
    }

    /** Lista de alunos para o campo "Aluno" do formulário. */
    public function listarAlunos()
    {
        $this->db->query(
            'SELECT alun_id, alun_nome, alun_matricula
               FROM aluno
           ORDER BY alun_nome ASC'
        );

        return $this->db->resultados();
    }

    public function alunoExiste(int $id): bool
    {
        $this->db->query('SELECT alun_id FROM aluno WHERE alun_id = :id');
        $this->db->bind(':id', $id);

        return (bool) $this->db->resultado();
    }

    public function buscarPorId(int $id)
    {
        $this->db->query('SELECT * FROM consulta WHERE cons_id = :id');
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /** Espera as chaves: alun_id, cons_data, cons_horario, cons_tipo,
     *  cons_responsavel, cons_descricao, cons_observacao */
    public function cadastrar(array $d): bool
    {
        $this->db->query(
            'INSERT INTO consulta
                (alun_id, cons_data, cons_horario, cons_tipo, cons_responsavel,
                 cons_descricao, cons_observacao)
             VALUES
                (:alun_id, :cons_data, :cons_horario, :cons_tipo, :cons_responsavel,
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
                alun_id          = :alun_id,
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
