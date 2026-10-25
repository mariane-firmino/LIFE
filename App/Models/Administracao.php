<?php
// =====================================================================
// MODEL: Administracao   ->   App/Models/Administracao.php
//
// Acesso ao banco para o perfil de administrador:
//  - conferir se o usuário é admin;
//  - totais da página inicial;
//  - listar, ativar, desativar, recusar e cadastrar servidores.
// =====================================================================

class Administracao
{
    // Conexão com o banco (mesmo padrão dos outros Models).
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }


    // =========================================================
    // PERMISSÃO
    // =========================================================

    // ---------------------------------------------------------
    // USUÁRIO É ADMIN?
    // Confere NO BANCO (não só na sessão): precisa ter perfil
    // 'admin' e estar 'ativo'. Assim, se alguém perder o acesso,
    // o efeito é imediato.
    // ATENÇÃO: assume que $_SESSION['usuario_id'] é o serv_id.
    // ---------------------------------------------------------
    public function usuarioEhAdmin($servId)
    {
        $this->db->query("
            SELECT serv_id
            FROM servidor
            WHERE serv_id = :id
              AND serv_perfil = 'admin'
              AND serv_status = 'ativo'
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $servId);

        return $this->db->resultado() ? true : false;
    }


    // =========================================================
    // PÁGINA INICIAL (totais)
    // =========================================================

    // ---------------------------------------------------------
    // TOTAIS GERAIS
    // Uma consulta só devolve todos os números dos cartões.
    // (Administradores não entram na contagem de servidores.)
    // ---------------------------------------------------------
    public function totais()
    {
        $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM servidor WHERE serv_perfil = 'servidor') AS servidores,
                (SELECT COUNT(*) FROM servidor WHERE serv_perfil = 'servidor' AND serv_status = 'ativo') AS servidores_ativos,
                (SELECT COUNT(*) FROM servidor WHERE serv_perfil = 'servidor' AND serv_status = 'inativo') AS servidores_inativos,
                (SELECT COUNT(*) FROM servidor WHERE serv_perfil = 'admin') AS administradores,
                (SELECT COUNT(*) FROM aluno) AS alunos
        ");

        return $this->db->resultado();
    }

    // Quantos servidores esperam ativação (usado no sino do menu).
    public function contarPendentes()
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM servidor
            WHERE serv_perfil = 'servidor' AND serv_status = 'inativo'
        ");

        return (int) $this->db->resultado()->total;
    }

    // Alunos por curso (do maior para o menor).
    public function alunosPorCurso()
    {
        $this->db->query("
            SELECT t.turm_curso AS nome, COUNT(a.alun_id) AS total
            FROM aluno a
            JOIN turma t ON a.turm_id = t.turm_id
            GROUP BY t.turm_curso
            ORDER BY total DESC, nome ASC
        ");

        return $this->db->resultados();
    }

    // Servidores ativos por função (Enfermeiro, Psicólogo...).
    public function servidoresPorFuncao()
    {
        $this->db->query("
            SELECT f.func_nome AS nome, COUNT(s.serv_id) AS total
            FROM servidor s
            JOIN funcao f ON s.func_id = f.func_id
            WHERE s.serv_perfil = 'servidor' AND s.serv_status = 'ativo'
            GROUP BY f.func_nome
            ORDER BY total DESC, nome ASC
        ");

        return $this->db->resultados();
    }


    // =========================================================
    // GERENCIAR SERVIDORES
    // =========================================================

    // ---------------------------------------------------------
    // LISTAR SERVIDORES
    // Todos os servidores (sem os admins), com função e telefone.
    // Os mais recentes primeiro.
    // ---------------------------------------------------------
    public function listarServidores()
    {
        $this->db->query("
            SELECT
                s.serv_id AS id,
                s.serv_nome AS nome,
                s.serv_siape AS siape,
                s.serv_email AS email,
                s.serv_status AS status,
                s.serv_criado_em AS criado_em,
                f.func_nome AS funcao,
                (SELECT tele_numero FROM telefone WHERE serv_id = s.serv_id LIMIT 1) AS telefone
            FROM servidor s
            LEFT JOIN funcao f ON s.func_id = f.func_id
            WHERE s.serv_perfil = 'servidor'
            ORDER BY s.serv_criado_em DESC, s.serv_id DESC
        ");

        return $this->db->resultados();
    }

    // Funções para o <select> do cadastro de servidor.
    public function listarFuncoes()
    {
        $this->db->query("SELECT func_id AS id, func_nome AS nome FROM funcao ORDER BY func_nome ASC");

        return $this->db->resultados();
    }

    // ---------------------------------------------------------
    // ALTERAR STATUS
    // Ativa ou desativa um servidor. O "serv_perfil = 'servidor'"
    // impede mexer em contas de administrador por aqui.
    // ---------------------------------------------------------
    public function alterarStatus($servId, $status)
    {
        $this->db->query("
            UPDATE servidor
            SET serv_status = :status
            WHERE serv_id = :id AND serv_perfil = 'servidor'
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', (int) $servId);

        return $this->db->executa();
    }

    public function buscarServidor($servId)
    {
        $this->db->query("
            SELECT serv_id AS id, serv_nome AS nome, serv_email AS email,
                   serv_status AS status, serv_perfil AS perfil
            FROM servidor
            WHERE serv_id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $servId);

        return $this->db->resultado();
    }

    // ---------------------------------------------------------
    // RECUSAR CADASTRO
    // Apaga um servidor INATIVO (cadastro não aprovado) e os
    // registros ligados a ele (telefone e notificações).
    // Se ele já tiver histórico no sistema (ex.: triagens), o banco
    // recusa e a função devolve false: nesse caso, mantenha inativo.
    // ---------------------------------------------------------
    public function recusarCadastro($servId)
    {
        try {
            $this->db->beginTransaction();

            // Só apaga se for servidor E estiver inativo.
            $this->db->query("
                SELECT serv_id FROM servidor
                WHERE serv_id = :id AND serv_perfil = 'servidor' AND serv_status = 'inativo'
                LIMIT 1
            ");
            $this->db->bind(':id', (int) $servId);

            if (!$this->db->resultado()) {
                $this->db->rollBack();
                return false;
            }

            // Registros que apontam para o servidor.
            foreach (['telefone', 'notificacao'] as $tabela) {
                $this->db->query("DELETE FROM {$tabela} WHERE serv_id = :id");
                $this->db->bind(':id', (int) $servId);
                $this->db->executa();
            }

            // O próprio servidor (recuperacao_senha apaga junto, por CASCADE).
            $this->db->query("DELETE FROM servidor WHERE serv_id = :id");
            $this->db->bind(':id', (int) $servId);
            $this->db->executa();

            $this->db->commit();

            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();

            return false;
        }
    }


    // =========================================================
    // CADASTRO DE SERVIDOR PELO ADMIN
    // =========================================================

    // Verificações de duplicidade (retornam true se já existe).
    public function emailExiste($email)
    {
        $this->db->query("SELECT serv_id FROM servidor WHERE serv_email = :v LIMIT 1");
        $this->db->bind(':v', $email);

        return $this->db->resultado() ? true : false;
    }

    public function siapeExiste($siape)
    {
        $this->db->query("SELECT serv_id FROM servidor WHERE serv_siape = :v LIMIT 1");
        $this->db->bind(':v', (int) $siape);

        return $this->db->resultado() ? true : false;
    }

    public function cpfExiste($cpf)
    {
        $this->db->query("SELECT serv_id FROM servidor WHERE serv_cpf = :v LIMIT 1");
        $this->db->bind(':v', $cpf);

        return $this->db->resultado() ? true : false;
    }

    // ---------------------------------------------------------
    // CRIAR SERVIDOR
    // Cadastra já ATIVO (cadastro feito pelo admin não precisa de
    // aprovação). Servidor + telefone na mesma transação.
    // $d['senha_hash'] já deve vir criptografada (password_hash).
    // ---------------------------------------------------------
    public function criarServidor(array $d)
    {
        try {
            $this->db->beginTransaction();

            $this->db->query("
                INSERT INTO servidor (
                    serv_nome, serv_siape, serv_email, serv_cpf,
                    serv_dt_nascimento, func_id, serv_senha, serv_status, serv_perfil
                ) VALUES (
                    :nome, :siape, :email, :cpf,
                    :nascimento, :func_id, :senha, 'ativo', 'servidor'
                )
            ");
            $this->db->bind(':nome', $d['nome']);
            $this->db->bind(':siape', (int) $d['siape']);
            $this->db->bind(':email', $d['email']);
            $this->db->bind(':cpf', $d['cpf']);
            $this->db->bind(':nascimento', $d['data_nascimento']);
            $this->db->bind(':func_id', (int) $d['func_id']);
            $this->db->bind(':senha', $d['senha_hash']);

            if (!$this->db->executa()) {
                $this->db->rollBack();
                return false;
            }

            $servId = $this->db->ultimoIdInserido();

            // Telefone é opcional.
            if ($d['telefone'] !== '') {
                $this->db->query("
                    INSERT INTO telefone (tele_numero, serv_id, alun_id, resp_id)
                    VALUES (:numero, :serv_id, NULL, NULL)
                ");
                $this->db->bind(':numero', $d['telefone']);
                $this->db->bind(':serv_id', (int) $servId);
                $this->db->executa();
            }

            $this->db->commit();

            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();

            return false;
        }
    }
}