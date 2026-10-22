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
        $this->notificacaoModel = $this->model('Notificacao');
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

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . URL . '/login');
            exit;
        }


        // =====================================================
        // PEGA O ID DO USUÁRIO LOGADO
        // =====================================================

        $usuario_id = $_SESSION['usuario_id'];


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
        // Busca a notificação pelo ID.
        $notificacao =
            $this->model('Notificacao')->buscarPorId($id);


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
            'notificacao' => $notificacao
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
        // Carrega o Model de notificações.
        $notificacaoModel =
            $this->model('Notificacao');


        // Busca a notificação antes de tentar excluir.
        $notificacao =
            $notificacaoModel->buscarPorId($id);


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
            $notificacaoModel->excluirNotificacao($id)
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
        // =====================================================
        // VERIFICA SE O USUÁRIO ESTÁ LOGADO
        // =====================================================

        if (!isset($_SESSION['usuario_id'])) {

            header(
                'Location: ' .
                URL .
                '/usuarios/login'
            );

            exit;
        }


        // =====================================================
        // MARCA A NOTIFICAÇÃO COMO LIDA
        // =====================================================

        $this->notificacaoModel->marcarComoLida($id);


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
}