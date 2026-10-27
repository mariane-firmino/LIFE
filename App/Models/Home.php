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

    // ---------------------------------------------------------
    // PESSOAS QUE PRECISAM DE ATENÇÃO
    // Olha a triagem MAIS RECENTE de cada aluno (últimos $dias
    // dias) e aponta os motivos de alerta encontrados nas
    // respostas. Retorna só quem tem pelo menos um motivo,
    // com mais motivos primeiro.
    // ---------------------------------------------------------

    public function listarAlunosEmAtencao($desde, $limite = 8)
    {
        $this->db->query(
            "SELECT t.alun_id, t.tria_pergunta, t.tria_resposta, t.tria_data,
                    a.alun_nome, tu.turm_curso, tu.turm_ano
               FROM triagem t
               INNER JOIN aluno a ON a.alun_id = t.alun_id
               LEFT JOIN turma tu ON tu.turm_id = a.turm_id
              WHERE t.tria_data >= :desde
              ORDER BY t.alun_id, t.tria_data DESC"
        );
        $this->db->bind(':desde', $desde);
        $linhas = $this->db->resultados();

        // Mantém só as respostas da triagem mais recente de cada aluno.
        $alunos = [];
        foreach ($linhas as $l) {
            $id = (int) $l->alun_id;
            if (!isset($alunos[$id])) {
                $alunos[$id] = [
                    'id' => $id,
                    'nome' => $l->alun_nome,
                    'turma' => trim(($l->turm_curso ?? '') . ' ' . ($l->turm_ano ?? '')),
                    'data' => $l->tria_data,
                    'motivos' => [],
                ];
            }
            if ($l->tria_data !== $alunos[$id]['data']) {
                continue; // triagem mais antiga do mesmo aluno
            }

            $motivo = self::motivoDeAtencao((string) $l->tria_pergunta, trim((string) $l->tria_resposta));
            if ($motivo !== null && !in_array($motivo, $alunos[$id]['motivos'], true)) {
                $alunos[$id]['motivos'][] = $motivo;
            }
        }

        $alunos = array_filter($alunos, function ($a) {
            return count($a['motivos']) > 0;
        });

        usort($alunos, function ($a, $b) {
            return [count($b['motivos']), $b['data']] <=> [count($a['motivos']), $a['data']];
        });

        return array_slice($alunos, 0, (int) $limite);
    }

    // Regras de alerta. Para ajustar, basta editar aqui.
    private static function motivoDeAtencao($pergunta, $resposta)
    {
        if ($resposta === '') {
            return null;
        }

        if (strpos($pergunta, 'como são seus pensamentos') !== false
            && stripos($resposta, 'Ruins') === 0) {
            return 'Pensamentos desanimadores';
        }
        if (strpos($pergunta, 'Diariamente você se sente') !== false
            && in_array($resposta, ['Desmotivado', 'Irritado'], true)) {
            return 'Sente-se ' . mb_strtolower($resposta, 'UTF-8');
        }
        if (strpos($pergunta, 'objetivo de vida') !== false && stripos($resposta, 'Nenhum') === 0) {
            return 'Sem objetivo de vida';
        }
        if (strpos($pergunta, 'olha no espelho') !== false && $resposta === 'Insatisfeito') {
            return 'Insatisfeito com a imagem';
        }
        if (strpos($pergunta, 'intensidade das cólicas') !== false
            && (int) $resposta >= 7 && ctype_digit(substr($resposta, 0, 1))) {
            return 'Cólicas intensas';
        }
        if (strpos($pergunta, 'faz uso de preservativo') !== false && $resposta === 'Não') {
            return 'Não usa preservativo';
        }

        return null;
    }
}
