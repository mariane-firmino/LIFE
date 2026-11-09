<?php

class Home
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
        RotinasBanco::garantir($this->db);
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
    // Junta três fontes:
    //  1. Ficha de saúde (condição especial, doença crônica,
    //     alergia, medicação contínua) - function fn_motivos_atencao;
    //  2. Triagem rápida Média/Grave desde $desde;
    //     (1 e 2 vêm da procedure sp_alunos_atencao)
    //  3. Respostas de alerta na triagem completa mais recente.
    // Retorna só quem tem pelo menos um motivo, com mais motivos
    // primeiro.
    // ---------------------------------------------------------

    public function listarAlunosEmAtencao($desde, $limite = 8)
    {
        $alunos = $this->alertasDaTriagem($desde);

        try {
            $this->db->query('CALL sp_alunos_atencao(:desde)');
            $this->db->bind(':desde', $desde);
            $linhas = $this->db->resultados();
        } catch (PDOException $e) {
            error_log('sp_alunos_atencao: ' . $e->getMessage());
            $linhas = [];
        }

        foreach ($linhas as $l) {
            $id = (int) $l->alun_id;
            if (!isset($alunos[$id])) {
                $alunos[$id] = [
                    'id' => $id,
                    'nome' => $l->alun_nome,
                    'turma' => trim(($l->turm_curso ?? '') . ' ' . ($l->turm_ano ?? '')),
                    'data' => null,
                    'motivos' => [],
                ];
            }

            if (!empty($l->rapida_data)) {
                $queixa = html_entity_decode((string) $l->rapida_queixa, ENT_QUOTES, 'UTF-8');
                array_unshift($alunos[$id]['motivos'], 'Triagem rápida ' . mb_strtolower((string) $l->rapida_gravidade, 'UTF-8') . ($queixa !== '' ? ': ' . $queixa : ''));
                if ($alunos[$id]['data'] === null || $l->rapida_data > $alunos[$id]['data']) {
                    $alunos[$id]['data'] = $l->rapida_data;
                }
            }

            foreach (self::traduzirMotivosFicha((string) ($l->motivos_ficha ?? '')) as $motivo) {
                if (!in_array($motivo, $alunos[$id]['motivos'], true)) {
                    $alunos[$id]['motivos'][] = $motivo;
                }
            }
        }

        $alunos = array_filter($alunos, function ($a) {
            return count($a['motivos']) > 0;
        });

        usort($alunos, function ($a, $b) {
            return [count($b['motivos']), (string) $b['data']] <=> [count($a['motivos']), (string) $a['data']];
        });

        return array_slice($alunos, 0, (int) $limite);
    }

    // Códigos devolvidos por fn_motivos_atencao -> textos para a tela.
    private static function traduzirMotivosFicha($codigos)
    {
        $rotulos = [
            'condicao'  => 'Condição especial',
            'doenca'    => 'Doença crônica',
            'alergia'   => 'Alergia alimentar',
            'medicacao' => 'Medicação contínua',
        ];

        $motivos = [];
        foreach (array_filter(explode('|', $codigos)) as $item) {
            [$chave, $detalhe] = array_pad(explode('=', $item, 2), 2, '');
            if (!isset($rotulos[$chave])) {
                continue;
            }
            $detalhe = trim(str_replace(';', ', ', html_entity_decode($detalhe, ENT_QUOTES, 'UTF-8')));
            $motivos[] = $rotulos[$chave] . ($detalhe !== '' ? ': ' . $detalhe : '');
        }

        return $motivos;
    }

    // Motivos encontrados nas respostas da triagem completa mais
    // recente de cada aluno (desde $desde).
    private function alertasDaTriagem($desde)
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

        return array_filter($alunos, function ($a) {
            return count($a['motivos']) > 0;
        });
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
            && in_array($resposta, ['Desmotivado', 'Irritado', 'Desmotivada', 'Irritada'], true)) {
            return 'Sente-se ' . mb_strtolower($resposta, 'UTF-8');
        }
        if (strpos($pergunta, 'objetivo de vida') !== false && stripos($resposta, 'Nenhum') === 0) {
            return 'Sem objetivo de vida';
        }
        if (strpos($pergunta, 'olha no espelho') !== false
            && in_array($resposta, ['Insatisfeito', 'Insatisfeita'], true)) {
            return 'Insatisfeito(a) com a imagem';
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
