<?php
// =====================================================================
// MODEL: TriagemPsicologica   ->   App/Models/TriagemPsicologica.php
// Triagem psicológica rápida: motivo do atendimento, gravidade,
// sintomas apresentados e observações de quem atendeu.
//
// A tabela é criada automaticamente (arquivo triagem_psicologica.sql na
// raiz do projeto) quando ainda não existe no banco — por exemplo, num
// banco importado de outro computador.
// =====================================================================

class TriagemPsicologica
{
    // Opções do menu de gravidade (iguais ao ENUM da tabela).
    public const GRAVIDADES = ['Leve', 'Média', 'Grave'];

    // ---------------------------------------------------------
    // ETAPA 2 - HISTÓRICO PESSOAL
    // Opções de cada pergunta (valor gravado => texto exibido).
    // ---------------------------------------------------------
    public const SIM_NAO = [
        'sim' => 'Sim',
        'nao' => 'Não',
    ];
    public const SIM_NAO_NAO_SABE = [
        'sim' => 'Sim',
        'nao' => 'Não',
        'nao_sabe' => 'Não sabe informar',
    ];
    public const ACOMPANHAMENTOS = [
        'psicologico' => 'Psicológico',
        'psiquiatrico' => 'Psiquiátrico',
        'ambos' => 'Ambos',
        'nao' => 'Não faz acompanhamento',
    ];
    public const GATILHOS = [
        'familia' => 'Problemas familiares',
        'escola' => 'Pressão ou dificuldades escolares',
        'bullying' => 'Bullying',
        'relacionamento' => 'Relacionamentos',
        'social' => 'Situações sociais',
        'nao_sabe' => 'Não sabe identificar',
    ];
    public const RELACOES_FAMILIARES = [
        'boa' => 'Boa',
        'regular' => 'Regular',
        'dificil' => 'Difícil',
        'prefiro_nao_responder' => 'Prefere não responder',
    ];
    public const ALIMENTACAO = [
        'sim' => 'Sim',
        'nao' => 'Não',
        'as_vezes' => 'Às vezes',
        'prefiro_nao_responder' => 'Prefere não responder',
    ];

    // Verifica a tabela uma vez por requisição.
    private static $tabelaVerificada = false;

    private $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->garantirTabela();
    }


    // ---------------------------------------------------------
    // SALVAR
    // Grava uma triagem. Sintomas viram JSON; textos vazios, NULL.
    // ---------------------------------------------------------
    public function salvar(array $d)
    {
        $sintomas = array_values((array) ($d['sintomas'] ?? []));

        $this->db->query("
            INSERT INTO triagem_psicologica (
                alun_id, serv_id, trps_motivo, trps_gravidade,
                trps_sintomas, trps_observacoes
            ) VALUES (
                :alun_id, :serv_id, :motivo, :gravidade,
                :sintomas, :observacoes
            )
        ");
        $this->db->bind(':alun_id', (int) $d['aluno_id']);
        $this->db->bind(':serv_id', (int) $d['serv_id']);
        $this->db->bind(':motivo', $d['motivo']);
        $this->db->bind(':gravidade', $d['gravidade']);
        $this->db->bind(':sintomas', $sintomas ? json_encode($sintomas, JSON_UNESCAPED_UNICODE) : null);
        $this->db->bind(':observacoes', $d['observacoes'] !== '' ? $d['observacoes'] : null);

        // Devolve o ID da triagem criada (usado para abrir a Etapa 2),
        // ou false se não gravou.
        return $this->db->executa() ? (int) $this->db->ultimoIdInserido() : false;
    }


    // ---------------------------------------------------------
    // LISTAR POR ALUNO
    // Triagens do aluno, da mais nova para a mais antiga, com o
    // nome e a função de quem atendeu. "sintomas" volta como array.
    // ---------------------------------------------------------
    public function listarPorAluno($alunId)
    {
        $this->db->query("
            SELECT
                t.trps_id AS id,
                t.trps_motivo AS motivo,
                t.trps_gravidade AS gravidade,
                t.trps_sintomas AS sintomas,
                t.trps_observacoes AS observacoes,
                t.trps_criado_em AS criado_em,
                s.serv_nome,
                f.func_nome
            FROM triagem_psicologica t
            LEFT JOIN servidor s ON t.serv_id = s.serv_id
            LEFT JOIN funcao f ON s.func_id = f.func_id
            WHERE t.alun_id = :id
            ORDER BY t.trps_criado_em DESC, t.trps_id DESC
        ");
        $this->db->bind(':id', (int) $alunId);

        $lista = $this->db->resultados();
        $historicos = $this->historicosPorAluno($alunId);
        foreach ($lista as $linha) {
            $sintomas = json_decode((string) $linha->sintomas, true);
            $linha->sintomas = is_array($sintomas) ? $sintomas : [];
            $linha->historico = $historicos[(int) $linha->id] ?? null;
        }

        return $lista;
    }


    // ---------------------------------------------------------
    // BUSCAR UMA TRIAGEM DO ALUNO
    // Usado pela Etapa 2 para conferir se a triagem existe e é
    // mesmo deste aluno.
    // ---------------------------------------------------------
    public function buscarDoAluno($trpsId, $alunId)
    {
        $this->db->query("
            SELECT trps_id AS id, trps_motivo AS motivo, trps_gravidade AS gravidade
            FROM triagem_psicologica
            WHERE trps_id = :trps AND alun_id = :alun
        ");
        $this->db->bind(':trps', (int) $trpsId);
        $this->db->bind(':alun', (int) $alunId);

        return $this->db->resultado() ?: null;
    }


    // ---------------------------------------------------------
    // A TRIAGEM JÁ TEM HISTÓRICO PESSOAL?
    // ---------------------------------------------------------
    public function temHistorico($trpsId)
    {
        $this->db->query("SELECT COUNT(*) AS total FROM triagem_psicologica_historico WHERE trps_id = :trps");
        $this->db->bind(':trps', (int) $trpsId);

        return (int) $this->db->resultado()->total > 0;
    }


    // ---------------------------------------------------------
    // SALVAR HISTÓRICO PESSOAL (ETAPA 2)
    // Gatilhos viram JSON; textos vazios, NULL. Transtorno e
    // medicamento só são gravados quando a resposta foi "Sim".
    // ---------------------------------------------------------
    public function salvarHistorico(array $d)
    {
        $gatilhos = array_values((array) ($d['gatilhos'] ?? []));
        $vazioNull = function ($valor) {
            $valor = trim((string) $valor);
            return $valor !== '' ? $valor : null;
        };

        $this->db->query("
            INSERT INTO triagem_psicologica_historico (
                alun_id, trps_id, serv_id,
                trph_diagnostico, trph_transtorno,
                trph_medicamento, trph_nome_medicamento,
                trph_acompanhamento, trph_crises, trph_gatilhos,
                trph_relacao_familiar, trph_historico_familiar,
                trph_alimentacao, trph_observacoes
            ) VALUES (
                :alun_id, :trps_id, :serv_id,
                :diagnostico, :transtorno,
                :medicamento, :nome_medicamento,
                :acompanhamento, :crises, :gatilhos,
                :relacao_familiar, :historico_familiar,
                :alimentacao, :observacoes
            )
        ");
        $this->db->bind(':alun_id', (int) $d['aluno_id']);
        $this->db->bind(':trps_id', (int) $d['trps_id']);
        $this->db->bind(':serv_id', (int) $d['serv_id']);
        $this->db->bind(':diagnostico', $d['diagnostico']);
        $this->db->bind(':transtorno', $d['diagnostico'] === 'sim' ? $vazioNull($d['transtorno']) : null);
        $this->db->bind(':medicamento', $d['medicamento']);
        $this->db->bind(':nome_medicamento', $d['medicamento'] === 'sim' ? $vazioNull($d['nome_medicamento']) : null);
        $this->db->bind(':acompanhamento', $d['acompanhamento']);
        $this->db->bind(':crises', $d['crises']);
        $this->db->bind(':gatilhos', $gatilhos ? json_encode($gatilhos, JSON_UNESCAPED_UNICODE) : null);
        $this->db->bind(':relacao_familiar', $d['relacao_familiar']);
        $this->db->bind(':historico_familiar', $d['historico_familiar']);
        $this->db->bind(':alimentacao', $d['alimentacao']);
        $this->db->bind(':observacoes', $vazioNull($d['observacoes']));

        return $this->db->executa();
    }


    // ---------------------------------------------------------
    // HISTÓRICOS DO ALUNO, POR TRIAGEM
    // Devolve [trps_id => objeto] com as respostas já em texto
    // (ex.: "nao" -> "Não") para exibir no perfil.
    // ---------------------------------------------------------
    private function historicosPorAluno($alunId)
    {
        try {
            $this->db->query("
                SELECT * FROM triagem_psicologica_historico
                WHERE alun_id = :id AND trps_id IS NOT NULL
            ");
            $this->db->bind(':id', (int) $alunId);
            $linhas = $this->db->resultados();
        } catch (Throwable $e) {
            error_log('Triagem psicológica: erro ao ler históricos: ' . $e->getMessage());
            return [];
        }

        $rotulo = function ($lista, $valor) {
            return $lista[$valor] ?? (string) $valor;
        };

        $porTriagem = [];
        foreach ($linhas as $l) {
            $gatilhos = json_decode((string) $l->trph_gatilhos, true);
            $gatilhos = is_array($gatilhos) ? $gatilhos : [];

            $porTriagem[(int) $l->trps_id] = (object) [
                'diagnostico' => $rotulo(self::SIM_NAO, $l->trph_diagnostico),
                'transtorno' => $l->trph_transtorno,
                'medicamento' => $rotulo(self::SIM_NAO, $l->trph_medicamento),
                'nome_medicamento' => $l->trph_nome_medicamento,
                'acompanhamento' => $rotulo(self::ACOMPANHAMENTOS, $l->trph_acompanhamento),
                'crises' => $rotulo(self::SIM_NAO_NAO_SABE, $l->trph_crises),
                'gatilhos' => array_map(function ($g) use ($rotulo) {
                    return $rotulo(self::GATILHOS, $g);
                }, $gatilhos),
                'relacao_familiar' => $rotulo(self::RELACOES_FAMILIARES, $l->trph_relacao_familiar),
                'historico_familiar' => $rotulo(self::SIM_NAO_NAO_SABE, $l->trph_historico_familiar),
                'alimentacao' => $rotulo(self::ALIMENTACAO, $l->trph_alimentacao),
                'observacoes' => $l->trph_observacoes,
            ];
        }

        return $porTriagem;
    }


    // ---------------------------------------------------------
    // CRIA A TABELA SE ELA NÃO EXISTIR
    // Executa o arquivo triagem_psicologica.sql (tabela + opções
    // iniciais). Só roda quando a tabela falta, para não recriar
    // opções que o administrador tenha apagado depois.
    // ---------------------------------------------------------
    private function garantirTabela()
    {
        if (self::$tabelaVerificada) {
            return;
        }
        self::$tabelaVerificada = true;

        // Etapa 1 (triagem) e Etapa 2 (histórico pessoal). A ordem importa:
        // o histórico referencia a triagem.
        $this->criarSeNaoExistir('triagem_psicologica', 'triagem_psicologica.sql');
        $this->criarSeNaoExistir('triagem_psicologica_historico', 'triagem_psicologica_historico.sql');
    }

    private function criarSeNaoExistir($tabela, $arquivoSql)
    {
        try {
            $this->db->query("
                SELECT COUNT(*) AS total
                FROM information_schema.tables
                WHERE table_schema = DATABASE() AND table_name = :tabela
            ");
            $this->db->bind(':tabela', $tabela);
            if ((int) $this->db->resultado()->total > 0) {
                return;
            }

            $arquivo = dirname(__DIR__, 2) . '/' . $arquivoSql;
            $sql = is_file($arquivo) ? file_get_contents($arquivo) : '';
            if ($sql === '') {
                error_log('Triagem psicológica: arquivo ' . $arquivoSql . ' não encontrado.');
                return;
            }

            // Tira os comentários e executa cada comando (separados por ";").
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);
            foreach (explode(';', $sql) as $comando) {
                $comando = trim($comando);
                if ($comando === '') {
                    continue;
                }
                $this->db->query($comando);
                $this->db->executa();
            }
        } catch (Throwable $e) {
            error_log('Triagem psicológica: não foi possível criar a tabela: ' . $e->getMessage());
        }
    }
}
