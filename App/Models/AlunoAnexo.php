<?php
// =====================================================================
// MODEL: AlunoAnexo   ->   App/Models/AlunoAnexo.php
// Cuida de tudo o que as novas abas do perfil guardam:
// documentos (vacina / SUS / laudos), anotações, número do SUS,
// informações extras e a regra de quem vê o conteúdo psicológico.
// =====================================================================

class AlunoAnexo
{
    // ---------------------------------------------------------
    // QUEM PODE VER/ESCREVER "INFORMAÇÕES PSICOLÓGICAS"
    // São os func_id da tabela "funcao". 2 = Psicólogo (a).
    // Para liberar outra função, acrescente o id aqui. Ex.: [1, 2].
    // ---------------------------------------------------------
    public const FUNCOES_PSICOLOGICO = [2];

    // Conexão com o banco (mesmo padrão do Model Aluno).
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }


    // =========================================================
    // PERMISSÃO
    // =========================================================

    // ---------------------------------------------------------
    // O servidor logado pode ver as informações psicológicas?
    // Consulta a função (func_id) dele na tabela servidor.
    // ATENÇÃO: assume que $_SESSION['usuario_id'] é o serv_id.
    // ---------------------------------------------------------
    public function usuarioPodeVerPsicologico($servId)
    {
        $this->db->query("SELECT func_id FROM servidor WHERE serv_id = :id LIMIT 1");
        $this->db->bind(':id', (int) $servId);

        $servidor = $this->db->resultado();

        return $servidor && in_array((int) $servidor->func_id, self::FUNCOES_PSICOLOGICO, true);
    }


    // =========================================================
    // COMPLEMENTO (número do SUS e informações extras)
    // =========================================================

    // Retorna cartao_sus e info_extras do aluno.
    public function buscarComplemento($alunId)
    {
        $this->db->query("
            SELECT alun_cartao_sus AS cartao_sus,
                   alun_info_extras AS info_extras
            FROM aluno
            WHERE alun_id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $alunId);

        return $this->db->resultado();
    }

    // Salva o número do SUS (null apaga).
    public function atualizarSus($alunId, $numero)
    {
        $this->db->query("UPDATE aluno SET alun_cartao_sus = :n WHERE alun_id = :id");
        $this->db->bind(':n', $numero);
        $this->db->bind(':id', (int) $alunId);

        return $this->db->executa();
    }

    // Salva o texto de "Informações extras" (null apaga).
    public function atualizarInfoExtras($alunId, $texto)
    {
        $this->db->query("UPDATE aluno SET alun_info_extras = :t WHERE alun_id = :id");
        $this->db->bind(':t', $texto);
        $this->db->bind(':id', (int) $alunId);

        return $this->db->executa();
    }


    // =========================================================
    // DOCUMENTOS (vacina, sus, laudo)
    // =========================================================

    // Todos os documentos do aluno, do mais novo para o mais antigo.
    // A View separa por "tipo".
    public function listarDocumentos($alunId)
    {
        $this->db->query("
            SELECT
                d.docu_id AS id,
                d.docu_tipo AS tipo,
                d.docu_titulo AS titulo,
                d.docu_descricao AS descricao,
                d.docu_data_documento AS data_documento,
                d.docu_arquivo AS arquivo,
                d.docu_nome_original AS nome_original,
                d.docu_mime AS mime,
                d.docu_tamanho AS tamanho,
                d.docu_criado_em AS criado_em,
                s.serv_nome
            FROM aluno_documento d
            LEFT JOIN servidor s ON d.serv_id = s.serv_id
            WHERE d.alun_id = :id
            ORDER BY d.docu_criado_em DESC, d.docu_id DESC
        ");
        $this->db->bind(':id', (int) $alunId);

        return $this->db->resultados();
    }

    // Um documento pelo id (usado para abrir e para excluir).
    public function buscarDocumento($docId)
    {
        $this->db->query("
            SELECT
                docu_id AS id,
                alun_id AS aluno_id,
                docu_tipo AS tipo,
                docu_arquivo AS arquivo,
                docu_nome_original AS nome_original,
                docu_mime AS mime
            FROM aluno_documento
            WHERE docu_id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $docId);

        return $this->db->resultado();
    }

    // Registra um documento já salvo em disco.
    public function inserirDocumento(array $d)
    {
        $this->db->query("
            INSERT INTO aluno_documento (
                alun_id, docu_tipo, docu_titulo, docu_descricao, docu_data_documento,
                docu_arquivo, docu_nome_original, docu_mime, docu_tamanho, serv_id
            ) VALUES (
                :alun_id, :tipo, :titulo, :descricao, :data_documento,
                :arquivo, :nome_original, :mime, :tamanho, :serv_id
            )
        ");
        $this->db->bind(':alun_id', (int) $d['aluno_id']);
        $this->db->bind(':tipo', $d['tipo']);
        $this->db->bind(':titulo', $d['titulo'] !== '' ? $d['titulo'] : null);
        $this->db->bind(':descricao', $d['descricao'] !== '' ? $d['descricao'] : null);
        $this->db->bind(':data_documento', $d['data_documento'] !== '' ? $d['data_documento'] : null);
        $this->db->bind(':arquivo', $d['arquivo']);
        $this->db->bind(':nome_original', $d['nome_original']);
        $this->db->bind(':mime', $d['mime']);
        $this->db->bind(':tamanho', (int) $d['tamanho']);
        $this->db->bind(':serv_id', (int) $d['serv_id']);

        return $this->db->executa();
    }

    // Remove o registro (o Controller apaga o arquivo do disco).
    public function excluirDocumento($docId)
    {
        $this->db->query("DELETE FROM aluno_documento WHERE docu_id = :id");
        $this->db->bind(':id', (int) $docId);

        return $this->db->executa();
    }


    // =========================================================
    // ANOTAÇÕES (rapida / psicologica)
    // =========================================================

    // Anotações de um tipo, da mais nova para a mais antiga.
    public function listarAnotacoes($alunId, $tipo)
    {
        $this->db->query("
            SELECT
                n.anot_id AS id,
                n.anot_texto AS texto,
                n.anot_criado_em AS criado_em,
                n.serv_id,
                s.serv_nome,
                f.func_nome
            FROM aluno_anotacao n
            LEFT JOIN servidor s ON n.serv_id = s.serv_id
            LEFT JOIN funcao f ON s.func_id = f.func_id
            WHERE n.alun_id = :id AND n.anot_tipo = :tipo
            ORDER BY n.anot_criado_em DESC, n.anot_id DESC
        ");
        $this->db->bind(':id', (int) $alunId);
        $this->db->bind(':tipo', $tipo);

        return $this->db->resultados();
    }

    // Uma anotação pelo id (para conferir o autor antes de excluir).
    public function buscarAnotacao($anotId)
    {
        $this->db->query("
            SELECT anot_id AS id, alun_id AS aluno_id, anot_tipo AS tipo, serv_id
            FROM aluno_anotacao
            WHERE anot_id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $anotId);

        return $this->db->resultado();
    }

    // Cria uma anotação.
    public function inserirAnotacao($alunId, $tipo, $texto, $servId)
    {
        $this->db->query("
            INSERT INTO aluno_anotacao (alun_id, anot_tipo, anot_texto, serv_id)
            VALUES (:alun_id, :tipo, :texto, :serv_id)
        ");
        $this->db->bind(':alun_id', (int) $alunId);
        $this->db->bind(':tipo', $tipo);
        $this->db->bind(':texto', $texto);
        $this->db->bind(':serv_id', (int) $servId);

        return $this->db->executa();
    }

    // Apaga uma anotação.
    public function excluirAnotacao($anotId)
    {
        $this->db->query("DELETE FROM aluno_anotacao WHERE anot_id = :id");
        $this->db->bind(':id', (int) $anotId);

        return $this->db->executa();
    }
}