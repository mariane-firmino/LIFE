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

        return $this->db->executa();
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
        foreach ($lista as $linha) {
            $sintomas = json_decode((string) $linha->sintomas, true);
            $linha->sintomas = is_array($sintomas) ? $sintomas : [];
        }

        return $lista;
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

        try {
            $this->db->query("
                SELECT COUNT(*) AS total
                FROM information_schema.tables
                WHERE table_schema = DATABASE() AND table_name = 'triagem_psicologica'
            ");
            if ((int) $this->db->resultado()->total > 0) {
                return;
            }

            $arquivo = dirname(__DIR__, 2) . '/triagem_psicologica.sql';
            $sql = is_file($arquivo) ? file_get_contents($arquivo) : '';
            if ($sql === '') {
                error_log('Triagem psicológica: arquivo triagem_psicologica.sql não encontrado.');
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
