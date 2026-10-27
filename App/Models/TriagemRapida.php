<?php
// =====================================================================
// MODEL: TriagemRapida   ->   App/Models/TriagemRapida.php
// Atendimentos rápidos da enfermaria: queixa, gravidade, relato do
// aluno, observações da enfermeira e como o caso foi conduzido.
// =====================================================================

class TriagemRapida
{
    // Opções do menu de gravidade (iguais ao ENUM da tabela).
    public const GRAVIDADES = ['Leve', 'Média', 'Grave'];

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }


    // ---------------------------------------------------------
    // SALVAR
    // Grava uma triagem rápida. Textos vazios viram NULL.
    // ---------------------------------------------------------
    public function salvar(array $d)
    {
        $this->db->query("
            INSERT INTO triagem_rapida (
                alun_id, serv_id, trap_queixa, trap_gravidade,
                trap_relato_aluno, trap_observacao, trap_conduta
            ) VALUES (
                :alun_id, :serv_id, :queixa, :gravidade,
                :relato, :observacao, :conduta
            )
        ");
        $this->db->bind(':alun_id', (int) $d['aluno_id']);
        $this->db->bind(':serv_id', (int) $d['serv_id']);
        $this->db->bind(':queixa', $d['queixa']);
        $this->db->bind(':gravidade', $d['gravidade']);
        $this->db->bind(':relato', $d['relato_aluno'] !== '' ? $d['relato_aluno'] : null);
        $this->db->bind(':observacao', $d['observacao'] !== '' ? $d['observacao'] : null);
        $this->db->bind(':conduta', $d['conduta']);

        return $this->db->executa();
    }


    // ---------------------------------------------------------
    // LISTAR POR ALUNO
    // Todas as triagens rápidas do aluno, da mais nova para a
    // mais antiga, com o nome e a função de quem atendeu.
    // ---------------------------------------------------------
    public function listarPorAluno($alunId)
    {
        $this->db->query("
            SELECT
                t.trap_id AS id,
                t.trap_queixa AS queixa,
                t.trap_gravidade AS gravidade,
                t.trap_relato_aluno AS relato_aluno,
                t.trap_observacao AS observacao,
                t.trap_conduta AS conduta,
                t.trap_criado_em AS criado_em,
                s.serv_nome,
                f.func_nome
            FROM triagem_rapida t
            LEFT JOIN servidor s ON t.serv_id = s.serv_id
            LEFT JOIN funcao f ON s.func_id = f.func_id
            WHERE t.alun_id = :id
            ORDER BY t.trap_criado_em DESC, t.trap_id DESC
        ");
        $this->db->bind(':id', (int) $alunId);

        return $this->db->resultados();
    }
}
