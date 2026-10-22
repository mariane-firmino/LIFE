<?php

class Usuario
{
    // =========================================================
    // CONEXÃO COM O BANCO DE DADOS
    // Objeto usado para executar as consultas (queries).
    // =========================================================

    private $db;


    // =========================================================
    // CONSTRUTOR
    // Cria a conexão com o banco de dados.
    // =========================================================

    public function __construct()
    {
        $this->db = new Database();
    }


    // =========================================================
    // VERIFICAÇÕES DE CADASTRO
    // Usadas para saber se um dado já está em uso.
    // =========================================================

    // ---------------------------------------------------------
    // CHECAR SIAPE
    // Verifica se o SIAPE já está cadastrado.
    // Retorna o registro encontrado ou false/null se estiver
    // livre.
    // ---------------------------------------------------------

    public function checarSiape($siape)
    {
        $this->db->query("
            SELECT serv_id
            FROM servidor
            WHERE serv_siape = :siape
            LIMIT 1
        ");

        $this->db->bind(':siape', $siape);

        return $this->db->resultado();
    }


    // ---------------------------------------------------------
    // CHECAR E-MAIL
    // Verifica se o e-mail já está cadastrado.
    // Retorna true se já existir, false se estiver livre.
    // ---------------------------------------------------------

    public function checarEmail($email)
    {
        $this->db->query("
            SELECT serv_email
            FROM servidor
            WHERE serv_email = :e
        ");

        $this->db->bind(":e", $email);

        if ($this->db->resultado()) {
            return true;
        }

        return false;
    }


    // ---------------------------------------------------------
    // CHECAR CPF
    // Verifica se o CPF já está cadastrado.
    // Retorna o registro encontrado ou false/null se estiver
    // livre.
    // ---------------------------------------------------------

    public function checarCpf($cpf)
    {
        $this->db->query("
            SELECT serv_id
            FROM servidor
            WHERE serv_cpf = :cpf
            LIMIT 1
        ");

        $this->db->bind(':cpf', $cpf);

        return $this->db->resultado();
    }


    // =========================================================
    // CADASTRO
    // =========================================================

    // ---------------------------------------------------------
    // ARMAZENAR (CADASTRAR USUÁRIO)
    // Cadastra o servidor e, em seguida, o telefone dele.
    // Retorna true se tudo foi salvo, false se falhou.
    // ---------------------------------------------------------

    public function armazenar($dados)
    {
        // Insere o servidor.
        $this->db->query("
            INSERT INTO servidor(
                serv_nome,
                serv_siape,
                serv_email,
                serv_cpf,
                serv_dt_nascimento,
                func_id,
                serv_senha
            )
            VALUES (
                :nome,
                :siape,
                :email,
                :cpf,
                :data_nascimento,
                :funcao,
                :senha
            )
        ");

        $this->db->bind('nome', $dados['nome']);
        $this->db->bind('siape', $dados['siape']);
        $this->db->bind('email', $dados['email']);
        $this->db->bind('cpf', $dados['cpf']);
        $this->db->bind('data_nascimento', $dados['data_nascimento']);
        $this->db->bind('funcao', $dados['funcao']);
        $this->db->bind('senha', $dados['senha']);

        if (!$this->db->executa()) {
            return false;
        }

        // ID do usuário recém-cadastrado.
        $idUsuario = $this->db->ultimoIdInserido();

        // Cadastra o telefone do usuário.
        $this->db->query("
            INSERT INTO telefone(
                tele_numero,
                serv_id
            )
            VALUES (
                :celular,
                :id_usuario
            )
        ");

        $this->db->bind("celular", $dados['telefone']);
        $this->db->bind("id_usuario", $idUsuario);

        return $this->db->executa();
    }


    // ---------------------------------------------------------
    // LISTAR FUNÇÕES
    // Retorna todas as funções (cargos) em ordem alfabética.
    // Usada no formulário de cadastro.
    // ---------------------------------------------------------

    public function listarFuncoes()
    {
        $this->db->query("
            SELECT *
            FROM funcao
            ORDER BY func_nome ASC
        ");

        return $this->db->resultados();
    }


    // =========================================================
    // LOGIN
    // =========================================================

    // ---------------------------------------------------------
    // CHECAR LOGIN
    // Busca o usuário pelo e-mail e confere a senha.
    // Retorna os dados do usuário (com telefone e nome da
    // função) se estiver correto, ou false se não estiver.
    // ---------------------------------------------------------

    public function checarLogin($email, $senha)
    {
        $this->db->query("
            SELECT
                servidor.*,
                telefone.tele_numero,
                funcao.func_nome
            FROM servidor
            LEFT JOIN telefone
                ON servidor.serv_id = telefone.serv_id
            LEFT JOIN funcao
                ON servidor.func_id = funcao.func_id
            WHERE servidor.serv_email = :email
            LIMIT 1
        ");

        $this->db->bind(':email', $email);

        $usuario = $this->db->resultado();

        // Compara a senha digitada com a senha criptografada.
        if (
            $usuario &&
            password_verify($senha, $usuario->serv_senha)
        ) {
            return $usuario;
        }

        return false;
    }


    // =========================================================
    // BUSCAS
    // =========================================================

    // ---------------------------------------------------------
    // BUSCAR USUÁRIO PELO ID
    // Retorna os dados do usuário, com telefone e a data de
    // nascimento no formato Y-m-d (para o campo de data).
    // Inclui a senha criptografada (serv_senha), pois ela é
    // usada para conferir a senha atual ao alterá-la.
    // ---------------------------------------------------------

    public function buscarPorId($id)
    {
        $this->db->query("
            SELECT
                s.serv_id,
                s.serv_nome,
                s.serv_email,
                s.serv_senha,
                DATE_FORMAT(s.serv_dt_nascimento, '%Y-%m-%d') AS serv_dt_nascimento,
                s.serv_foto,
                t.tele_numero
            FROM servidor s
            LEFT JOIN telefone t
                ON t.serv_id = s.serv_id
            WHERE s.serv_id = :id
            LIMIT 1
        ");

        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }


    // ---------------------------------------------------------
    // BUSCAR USUÁRIO PELO E-MAIL
    // Usada na recuperação de senha.
    // ---------------------------------------------------------

    public function buscarPorEmail($email)
    {
        $this->db->query("
            SELECT *
            FROM servidor
            WHERE serv_email = :email
            LIMIT 1
        ");

        $this->db->bind(':email', $email);

        return $this->db->resultado();
    }


    // =========================================================
    // ATUALIZAÇÕES
    // =========================================================

    // ---------------------------------------------------------
    // ALTERAR SENHA
    // Salva a nova senha (já criptografada) do usuário.
    // ---------------------------------------------------------

    public function alterarSenha($id, $senha)
    {
        $this->db->query("
            UPDATE servidor
            SET serv_senha = :senha
            WHERE serv_id = :id
        ");

        $this->db->bind(':senha', $senha);
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }


    // ---------------------------------------------------------
    // ATUALIZAR PERFIL
    // Atualiza nome, e-mail, data de nascimento e telefone.
    // A foto só é alterada se uma nova for informada.
    // ---------------------------------------------------------

    public function atualizarPerfil(
        $id,
        $nome,
        $email,
        $dataNascimento,
        $telefone,
        $foto = null
    ) {
        // Atualiza os dados do servidor.
        if ($foto !== null) {

            // Com foto: atualiza também a foto.
            $this->db->query("
                UPDATE servidor
                SET
                    serv_nome = :nome,
                    serv_email = :email,
                    serv_dt_nascimento = :data_nascimento,
                    serv_foto = :foto
                WHERE serv_id = :id
            ");

            $this->db->bind(':nome', $nome);
            $this->db->bind(':email', $email);
            $this->db->bind(':data_nascimento', $dataNascimento);
            $this->db->bind(':foto', $foto);
            $this->db->bind(':id', $id);

        } else {

            // Sem foto: mantém a foto atual.
            $this->db->query("
                UPDATE servidor
                SET
                    serv_nome = :nome,
                    serv_email = :email,
                    serv_dt_nascimento = :data_nascimento
                WHERE serv_id = :id
            ");

            $this->db->bind(':nome', $nome);
            $this->db->bind(':email', $email);
            $this->db->bind(':data_nascimento', $dataNascimento);
            $this->db->bind(':id', $id);
        }

        // Executa a atualização do servidor.
        if (!$this->db->executa()) {
            return false;
        }

        // Atualiza o telefone.
        $this->db->query("
            UPDATE telefone
            SET tele_numero = :telefone
            WHERE serv_id = :id
        ");

        $this->db->bind(':telefone', $telefone);
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }


    // =========================================================
    // RECUPERAÇÃO DE SENHA
    // Funções usadas no fluxo "esqueci minha senha".
    // =========================================================

    // ---------------------------------------------------------
    // CRIAR TOKEN DE RECUPERAÇÃO
    // Salva o token no banco. Antes, remove os tokens antigos
    // do mesmo usuário, para que só o mais recente funcione.
    // ---------------------------------------------------------

    public function criarTokenRecuperacao($serv_id, $token, $expira)
    {
        // Remove tokens antigos desse usuário.
        $this->db->query("
            DELETE FROM recuperacao_senha
            WHERE serv_id = :serv_id
        ");

        $this->db->bind(':serv_id', $serv_id);
        $this->db->executa();

        // Cria o novo token (rec_usado = 0 significa "não usado").
        $this->db->query("
            INSERT INTO recuperacao_senha
            (
                serv_id,
                rec_token,
                rec_expira,
                rec_usado
            )
            VALUES
            (
                :serv_id,
                :token,
                :expira,
                0
            )
        ");

        $this->db->bind(':serv_id', $serv_id);
        $this->db->bind(':token', $token);
        $this->db->bind(':expira', $expira);

        return $this->db->executa();
    }


    // ---------------------------------------------------------
    // BUSCAR TOKEN DE RECUPERAÇÃO
    // Retorna o token somente se ele existir, ainda não foi
    // usado e não expirou. Também traz o e-mail e o nome do
    // usuário.
    // ---------------------------------------------------------

    public function buscarTokenRecuperacao($token)
    {
        $this->db->query("
            SELECT
                r.*,
                s.serv_email,
                s.serv_nome
            FROM recuperacao_senha r
            INNER JOIN servidor s
                ON r.serv_id = s.serv_id
            WHERE r.rec_token = :token
            AND r.rec_usado = 0
            AND r.rec_expira > NOW()
            LIMIT 1
        ");

        $this->db->bind(':token', $token);

        return $this->db->resultado();
    }


    // ---------------------------------------------------------
    // MARCAR TOKEN COMO USADO
    // Impede que o mesmo link seja usado mais de uma vez.
    // ---------------------------------------------------------

    public function marcarTokenUsado($token)
    {
        $this->db->query("
            UPDATE recuperacao_senha
            SET rec_usado = 1
            WHERE rec_token = :token
        ");

        $this->db->bind(':token', $token);

        return $this->db->executa();
    }
}