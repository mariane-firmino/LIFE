<?php

// =========================================================
// RELATÓRIO
// Junta os atendimentos do período (consultas da agenda,
// triagens rápidas e triagens completas) com o nome do aluno.
// =========================================================

class Relatorio
{
    public const TIPOS = [
        'todos'          => 'Todos',
        'consulta'       => 'Consultas',
        'triagem_rapida' => 'Triagens rápidas',
        'triagem'        => 'Triagens completas',
    ];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function listarTurmas()
    {
        $this->db->query("
            SELECT turm_id, turm_curso, turm_ano, turm_turno
              FROM turma
          ORDER BY turm_curso, turm_ano, turm_turno
        ");

        return $this->db->resultados();
    }

    // Retorna a lista de atendimentos, do mais recente para o mais antigo.
    // $inicio e $fim no formato AAAA-MM-DD; $turmaId = 0 para todas.
    public function atendimentos($inicio, $fim, $tipo = 'todos', $turmaId = 0)
    {
        $linhas = [];

        if ($tipo === 'todos' || $tipo === 'consulta') {
            $linhas = array_merge($linhas, $this->consultas($inicio, $fim, $turmaId));
        }
        if ($tipo === 'todos' || $tipo === 'triagem_rapida') {
            $linhas = array_merge($linhas, $this->triagensRapidas($inicio, $fim, $turmaId));
        }
        if ($tipo === 'todos' || $tipo === 'triagem') {
            $linhas = array_merge($linhas, $this->triagens($inicio, $fim, $turmaId));
        }

        usort($linhas, function ($a, $b) {
            return [$b->data, $b->hora] <=> [$a->data, $a->hora];
        });

        return $linhas;
    }

    private function filtroTurma($turmaId)
    {
        return $turmaId > 0 ? ' AND a.turm_id = :turma' : '';
    }

    private function bindPeriodo($inicio, $fim, $turmaId)
    {
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        if ($turmaId > 0) {
            $this->db->bind(':turma', (int) $turmaId);
        }
    }

    private function consultas($inicio, $fim, $turmaId)
    {
        $this->db->query("
            SELECT 'consulta' AS origem,
                   c.cons_tipo AS tipo,
                   c.cons_data AS data,
                   c.cons_horario AS hora,
                   a.alun_id, a.alun_nome, a.alun_matricula,
                   t.turm_curso, t.turm_ano, t.turm_turno,
                   c.cons_responsavel AS profissional,
                   c.cons_descricao AS descricao
              FROM consulta c
         LEFT JOIN aluno a ON a.alun_id = c.alun_id
         LEFT JOIN turma t ON t.turm_id = a.turm_id
             WHERE c.cons_data BETWEEN :inicio AND :fim" . $this->filtroTurma($turmaId)
        );
        $this->bindPeriodo($inicio, $fim, $turmaId);

        return $this->db->resultados();
    }

    private function triagensRapidas($inicio, $fim, $turmaId)
    {
        $this->db->query("
            SELECT 'triagem_rapida' AS origem,
                   CONCAT('Triagem rápida (', tr.trap_gravidade, ')') AS tipo,
                   DATE(tr.trap_criado_em) AS data,
                   TIME(tr.trap_criado_em) AS hora,
                   a.alun_id, a.alun_nome, a.alun_matricula,
                   t.turm_curso, t.turm_ano, t.turm_turno,
                   s.serv_nome AS profissional,
                   tr.trap_queixa AS descricao
              FROM triagem_rapida tr
              JOIN aluno a ON a.alun_id = tr.alun_id
         LEFT JOIN turma t ON t.turm_id = a.turm_id
         LEFT JOIN servidor s ON s.serv_id = tr.serv_id
             WHERE DATE(tr.trap_criado_em) BETWEEN :inicio AND :fim" . $this->filtroTurma($turmaId)
        );
        $this->bindPeriodo($inicio, $fim, $turmaId);

        return $this->db->resultados();
    }

    private function triagens($inicio, $fim, $turmaId)
    {
        // Cada triagem grava uma linha por pergunta; agrupa por envio.
        $this->db->query("
            SELECT 'triagem' AS origem,
                   'Triagem' AS tipo,
                   DATE(tg.tria_data) AS data,
                   TIME(tg.tria_data) AS hora,
                   a.alun_id, a.alun_nome, a.alun_matricula,
                   t.turm_curso, t.turm_ano, t.turm_turno,
                   s.serv_nome AS profissional,
                   CONCAT(COUNT(tg.tria_id), ' respostas') AS descricao
              FROM triagem tg
              JOIN aluno a ON a.alun_id = tg.alun_id
         LEFT JOIN turma t ON t.turm_id = a.turm_id
         LEFT JOIN servidor s ON s.serv_id = tg.serv_id
             WHERE DATE(tg.tria_data) BETWEEN :inicio AND :fim" . $this->filtroTurma($turmaId) . "
          GROUP BY tg.alun_id, tg.serv_id, tg.tria_data,
                   a.alun_id, a.alun_nome, a.alun_matricula,
                   t.turm_curso, t.turm_ano, t.turm_turno, s.serv_nome"
        );
        $this->bindPeriodo($inicio, $fim, $turmaId);

        return $this->db->resultados();
    }
}
