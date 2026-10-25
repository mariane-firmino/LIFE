<?php

class Notificacoes extends Controller
{
    // =========================================================
    // MODEL DE NOTIFICAÇÕES
    //
    // Guarda o Model responsável por buscar, contar, excluir
    // e marcar notificações como lidas.
    // =========================================================

    private $notificacaoModel;


    // =========================================================
    // CONSTRUTOR
    //
    // Carrega o Model "Notificacao".
    // =========================================================

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $this->notificacaoModel = $this->model('Notificacao');
        if (empty($_SESSION['csrf_token_notificacoes'])) {
            $_SESSION['csrf_token_notificacoes'] = bin2hex(random_bytes(32));
        }
    }


    // =========================================================
    // PÁGINA DE NOTIFICAÇÕES
    //
    // Exibe as notificações do usuário logado.
    // Também permite filtrar por:
    // - Pesquisa
    // - Data
    // - Status
    // =========================================================

    public function notificacao()
    {
        // =====================================================
        // VERIFICA SE EXISTE UM USUÁRIO LOGADO
        // =====================================================

        // =====================================================
        // PEGA O ID DO USUÁRIO LOGADO
        // =====================================================

        $usuario_id = (int) $_SESSION['usuario_id'];


        // =====================================================
        // RECEBE OS FILTROS ENVIADOS PELA URL
        //
        // Exemplo:
        // /notificacoes/notificacao?pesquisa=consulta
        // =====================================================

        $pesquisa = isset($_GET['pesquisa'])
            ? trim($_GET['pesquisa'])
            : '';

        $data = isset($_GET['data'])
            ? $_GET['data']
            : '';

        $status = isset($_GET['status'])
            ? $_GET['status']
            : '';


        // =====================================================
        // BUSCA AS NOTIFICAÇÕES NO BANCO
        //
        // Envia para o Model:
        // 1. ID do usuário
        // 2. Pesquisa
        // 3. Data
        // 4. Status
        // =====================================================

        $dados['notificacoes'] =
            $this->notificacaoModel->mostarNotificacao(
                $usuario_id,
                $pesquisa,
                $data,
                $status
            );


        // =====================================================
        // MANTÉM OS FILTROS PREENCHIDOS NA PÁGINA
        //
        // Assim, quando a página recarregar, os valores que
        // o usuário digitou continuam aparecendo.
        // =====================================================

        $dados['pesquisa'] = $pesquisa;
        $dados['data'] = $data;
        $dados['status'] = $status;
        $dados['csrf_token'] = $_SESSION['csrf_token_notificacoes'];


        // =====================================================
        // ABRE A VIEW DE NOTIFICAÇÕES
        // =====================================================

        $this->view(
            'notificacoes/notificacao',
            $dados
        );
    }


    // =========================================================
    // DETALHES DE UMA NOTIFICAÇÃO
    //
    // Recebe o ID da notificação pela URL e busca seus dados.
    // =========================================================

    public function detalhe($id)
    {
        $notificacao = $this->notificacaoModel->buscarPorId(
            (int) $id,
            (int) $_SESSION['usuario_id']
        );


        // =====================================================
        // VERIFICA SE A NOTIFICAÇÃO EXISTE
        // =====================================================

        if (!$notificacao) {

            // Se não existir, volta para a página de
            // notificações.
            header(
                'Location: ' .
                URL .
                '/notificacoes/notificacao'
            );

            exit;
        }


        // =====================================================
        // ENVIA A NOTIFICAÇÃO PARA A VIEW
        // =====================================================

        $dados = [
            'notificacao' => $notificacao,
            'csrf_token' => $_SESSION['csrf_token_notificacoes'],
        ];


        // Abre a página de detalhes.
        $this->view(
            'notificacoes/detalhe',
            $dados
        );
    }


    // =========================================================
    // EXCLUIR NOTIFICAÇÃO
    //
    // Recebe o ID da notificação e remove ela do banco.
    // =========================================================

    public function excluir($id)
    {
        $this->validarPostComToken();

        // Carrega o Model de notificações.
        $notificacaoModel =
            $this->model('Notificacao');


        // Busca a notificação antes de tentar excluir.
        $notificacao = $notificacaoModel->buscarPorId(
            (int) $id,
            (int) $_SESSION['usuario_id']
        );


        // =====================================================
        // VERIFICA SE A NOTIFICAÇÃO EXISTE
        // =====================================================

        if (!$notificacao) {

            // Se não existir, volta para a página principal.
            header(
                'Location: ' .
                URL .
                '/notificacoes/notificacao'
            );

            exit;
        }


        // =====================================================
        // TENTA EXCLUIR A NOTIFICAÇÃO
        // =====================================================

        if (
            $notificacaoModel->excluirNotificacao(
                (int) $id,
                (int) $_SESSION['usuario_id']
            )
        ) {

            // Se deu certo, volta para a página.
            header(
                'Location: ' .
                URL .
                '/notificacoes/notificacao'
            );

            exit;

        } else {

            // Se ocorreu algum erro, mostra a mensagem.
            die("Erro ao excluir a notificação.");
        }
    }


    // =========================================================
    // MARCAR COMO LIDA
    //
    // Altera o status da notificação para indicar que ela
    // já foi visualizada pelo usuário.
    // =========================================================

    public function marcarComoLida($id)
    {
        $this->validarPostComToken();

        $this->notificacaoModel->marcarComoLida(
            (int) $id,
            (int) $_SESSION['usuario_id']
        );


        // =====================================================
        // VOLTA PARA A PÁGINA DE NOTIFICAÇÕES
        // =====================================================

        header(
            'Location: ' .
            URL .
            '/notificacoes/notificacao'
        );

        exit;
    }

    private function validarPostComToken(): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals($_SESSION['csrf_token_notificacoes'], $token)
        ) {
            http_response_code(403);
            exit('Requisição inválida.');
        }
    }
}