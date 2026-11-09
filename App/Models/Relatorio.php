<?php

/**
 * Model Relatorio
 *
 * Reúne as consultas SQL da página de relatório (paginas/relatorio).
 *
 * Observações sobre o banco (bd_life):
 *  - A tabela `consulta` NÃO tem vínculo com aluno. Por isso os números de
 *    consultas não podem ser filtrados por turma.
 *  - A tabela `triagem` grava UMA LINHA POR PERGUNTA respondida, todas com
 *    o mesmo aluno e a mesma data/hora (veja Triagem::salvar). Então uma
 *    "triagem realizada" = um par (aluno, data/hora) distinto.
 *  - "Estudante atendido" = aluno que tem pelo menos uma triagem no período.
 *
 * Todos os períodos usam intervalo [inicio, fim): o início é inclusivo e o
 * fim é exclusivo (primeiro dia do mês seguinte). Assim funciona tanto para
 * colunas DATE (consulta) quanto DATETIME (triagem).
 */
class Relatorio
{
    /** Tipos de relatório aceitos no filtro (valor => rótulo). */
    public const TIPOS = [
        'todos'      => 'Todos',
        'consultas'  => 'Consultas',
        'estudantes' => 'Estudantes atendidos',
        'triagens'   => 'Triagens realizadas',
    ];

    /** Descrição de cada relatório (usada na tabela de relatórios). */
    public const DESCRICOES = [
        'consultas'  => 'Lista de todas as consultas do período (realizadas e marcadas).',
        'estudantes' => 'Relação de estudantes atendidos.',
        'triagens'   => 'Relação de triagens realizadas.',
    ];

    /** Limite de linhas na tabela de detalhes (evita página gigante). */
    public const LIMITE_DETALHES = 300;

    private $db;

    public function __construct()
    {
        $this->db = new Database();

        // Garante a coluna consulta.alun_id (aluno da consulta).
        require_once __DIR__ . '/Consulta.php';
        new Consulta();
    }

    // =========================================================
    // PERÍODO
    // =========================================================

    /**
     * Opções do select de período: últimos 12 meses + "todo o período".
     * Retorna [valor => rótulo]. O valor é 'AAAA-MM' ou 'todos'.
     */
    public static function opcoesPeriodo(DateTimeImmutable $hoje, int $meses = 12): array
    {
        $nomes = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        $opcoes = [];
        $mes = $hoje->modify('first day of this month')->setTime(0, 0);

        for ($i = 0; $i < $meses; $i++) {
            $opcoes[$mes->format('Y-m')] = sprintf(
                '%s/%s',
                $nomes[(int) $mes->format('n')],
                $mes->format('Y')
            );
            $mes = $mes->modify('first day of previous month');
        }

        $opcoes['todos'] = 'Todo o período';

        return $opcoes;
    }

    /**
     * Converte o valor do select em [inicio, fim) no formato Y-m-d.
     * 'todos' abrange qualquer data.
     */
    public static function intervalo(string $periodo): array
    {
        if ($periodo === 'todos') {
            return ['0001-01-01', '9999-12-31'];
        }

        $inicio = DateTimeImmutable::createFromFormat('!Y-m', $periodo);
        $fim = $inicio->modify('first day of next month');

        return [$inicio->format('Y-m-d'), $fim->format('Y-m-d')];
    }

    // =========================================================
    // FILTRO DE TURMA
    // =========================================================

    /** Todas as turmas cadastradas, para preencher o select. */
    public function listarTurmas(): array
    {
        $this->db->query(
            'SELECT turm_id, turm_curso, turm_ano, turm_turno
               FROM turma
           ORDER BY turm_curso ASC, turm_ano ASC, turm_turno ASC'
        );

        return $this->db->resultados();
    }

    /** Fragmento SQL do filtro de turma (usa o alias `a` para aluno). */
    private function filtroTurma(?int $turmaId): string
    {
        return $turmaId ? ' AND a.turm_id = :turma' : '';
    }

    private function bindTurma(?int $turmaId): void
    {
        if ($turmaId) {
            $this->db->bind(':turma', $turmaId, PDO::PARAM_INT);
        }
    }

    // =========================================================
    // CARDS (totais)
    // =========================================================

    /** Todas as consultas (realizadas + marcadas) do período. */
    public function totalConsultas(string $inicio, string $fim): int
    {
        $this->db->query(
            'SELECT COUNT(*) AS total
               FROM consulta
              WHERE cons_data >= :inicio
                AND cons_data < :fim'
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);

        return (int) ($this->db->resultado()->total ?? 0);
    }

    /**
     * Consultas do período que ainda vão acontecer (data/hora depois de
     * "agora"). Não existe coluna de status na tabela, então "marcada"
     * é uma consulta futura.
     */
    public function consultasMarcadas(string $inicio, string $fim, string $agora): int
    {
        $this->db->query(
            'SELECT COUNT(*) AS total
               FROM consulta
              WHERE cons_data >= :inicio
                AND cons_data < :fim
                AND TIMESTAMP(cons_data, cons_horario) > :agora'
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        $this->db->bind(':agora', $agora);

        return (int) ($this->db->resultado()->total ?? 0);
    }

    /** Alunos distintos que tiveram triagem no período. */
    public function totalEstudantesAtendidos(string $inicio, string $fim, ?int $turmaId): int
    {
        $this->db->query(
            'SELECT COUNT(DISTINCT t.alun_id) AS total
               FROM triagem t
         INNER JOIN aluno a ON a.alun_id = t.alun_id
              WHERE t.tria_data >= :inicio
                AND t.tria_data < :fim'
            . $this->filtroTurma($turmaId)
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        $this->bindTurma($turmaId);

        return (int) ($this->db->resultado()->total ?? 0);
    }

    /** Triagens do período (cada par aluno + data/hora conta uma vez). */
    public function totalTriagens(string $inicio, string $fim, ?int $turmaId): int
    {
        $this->db->query(
            'SELECT COUNT(*) AS total
               FROM (
                    SELECT t.alun_id, t.tria_data
                      FROM triagem t
                INNER JOIN aluno a ON a.alun_id = t.alun_id
                     WHERE t.tria_data >= :inicio
                       AND t.tria_data < :fim'
            . $this->filtroTurma($turmaId) . '
                  GROUP BY t.alun_id, t.tria_data
               ) AS triagens'
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        $this->bindTurma($turmaId);

        return (int) ($this->db->resultado()->total ?? 0);
    }

    // =========================================================
    // DETALHES (linhas de cada relatório)
    // =========================================================

    /**
     * Consultas do período, da mais recente para a mais antiga.
     * A coluna `marcada` vale 1 quando a data/hora ainda não passou
     * (consulta futura) e 0 quando já foi realizada.
     */
    public function listarConsultas(string $inicio, string $fim, string $agora): array
    {
        $this->db->query(
            'SELECT c.cons_id, c.cons_data, c.cons_horario, c.cons_tipo,
                    c.cons_responsavel, c.cons_descricao,
                    a.alun_id, a.alun_nome,
                    (TIMESTAMP(c.cons_data, c.cons_horario) > :agora) AS marcada
               FROM consulta c
          LEFT JOIN aluno a ON a.alun_id = c.alun_id
              WHERE c.cons_data >= :inicio
                AND c.cons_data < :fim
           ORDER BY c.cons_data DESC, c.cons_horario DESC
              LIMIT ' . self::LIMITE_DETALHES
        );
        $this->db->bind(':agora', $agora);
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);

        return $this->db->resultados();
    }

    /** Estudantes atendidos (com triagem) no período, um por linha. */
    public function listarEstudantesAtendidos(string $inicio, string $fim, ?int $turmaId): array
    {
        $this->db->query(
            'SELECT a.alun_id, a.alun_nome, a.alun_matricula,
                    tu.turm_curso, tu.turm_ano, tu.turm_turno,
                    COUNT(DISTINCT t.tria_data) AS atendimentos,
                    MAX(t.tria_data) AS ultimo_atendimento
               FROM triagem t
         INNER JOIN aluno a  ON a.alun_id  = t.alun_id
         INNER JOIN turma tu ON tu.turm_id = a.turm_id
              WHERE t.tria_data >= :inicio
                AND t.tria_data < :fim'
            . $this->filtroTurma($turmaId) . '
           GROUP BY a.alun_id, a.alun_nome, a.alun_matricula,
                    tu.turm_curso, tu.turm_ano, tu.turm_turno
           ORDER BY ultimo_atendimento DESC
              LIMIT ' . self::LIMITE_DETALHES
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        $this->bindTurma($turmaId);

        return $this->db->resultados();
    }

    /**
     * Triagens do período, uma por linha. Mostra só quem foi triado, a
     * turma, quem atendeu e quantas perguntas foram respondidas — o
     * conteúdo das respostas (dado sensível) não aparece no relatório.
     */
    public function listarTriagens(string $inicio, string $fim, ?int $turmaId): array
    {
        $this->db->query(
            'SELECT a.alun_id, a.alun_nome,
                    tu.turm_curso, tu.turm_ano, tu.turm_turno,
                    s.serv_nome,
                    t.tria_data,
                    COUNT(*) AS respostas
               FROM triagem t
         INNER JOIN aluno a    ON a.alun_id  = t.alun_id
         INNER JOIN turma tu   ON tu.turm_id = a.turm_id
         INNER JOIN servidor s ON s.serv_id  = t.serv_id
              WHERE t.tria_data >= :inicio
                AND t.tria_data < :fim'
            . $this->filtroTurma($turmaId) . '
           GROUP BY t.alun_id, t.tria_data, t.serv_id,
                    a.alun_id, a.alun_nome,
                    tu.turm_curso, tu.turm_ano, tu.turm_turno,
                    s.serv_nome
           ORDER BY t.tria_data DESC
              LIMIT ' . self::LIMITE_DETALHES
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        $this->bindTurma($turmaId);

        return $this->db->resultados();
    }
}