<?php

class Paginas extends Controller
{
    // =========================================================
    // CÓDIGO ANTIGO
    // =========================================================
    //
    // Este trecho está comentado e atualmente não é utilizado.
    // Provavelmente era usado para carregar o Model de usuário.
    //
    // Se não for mais necessário, futuramente pode ser removido.
    // =========================================================

    /*
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = $this->model('Usuario');
    }
    */


    // =========================================================
    // PÁGINA INICIAL
    // =========================================================
    //
    // Quando alguém acessa o endereço principal do sistema,
    // é redirecionado para a página de login.
    //
    // Exemplo:
    // /ENFERMARIA
    //
    // será direcionado para:
    // /ENFERMARIA/usuarios/login
    // =========================================================

    public function index()
    {
        URL::redirecionar('usuarios/login');
    }


    // =========================================================
    // HOME
    // =========================================================
    //
    // Abre a página principal do sistema depois que o usuário
    // acessa a área correspondente.
    // =========================================================

    public function home()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $home = $this->model('Home');
        $fusoHorario = new DateTimeZone('America/Porto_Velho');
        $agora = new DateTimeImmutable('now', $fusoHorario);
        $inicioMes = $agora->modify('first day of this month')->setTime(0, 0);
        $inicioProximoMes = $inicioMes->modify('first day of next month');

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Página de home',
            'descricao' => 'pagina home',
            'notificacoes' => $home->contarNotificacoesNaoLidas((int) $_SESSION['usuario_id']),
            'alunos' => $home->contarAlunos(),
            'atendimentos_hoje' => $home->contarAtendimentosHoje($agora->format('Y-m-d')),
            'atendimentos_periodicos' => $home->contarAtendimentosPeriodicos(
                $inicioMes->format('Y-m-d'),
                $inicioProximoMes->format('Y-m-d')
            ),
        ];

        // Abre: App/Views/paginas/home.php
        $this->view('paginas/home', $dados);
    }


    // =========================================================
    // PERFIL
    // =========================================================
    //
    // Abre a página de perfil do usuário.
    // =========================================================

    public function perfil()
    {
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de perfil',
            'descricao' => 'pagina perfil'
        ];

        // Abre: App/Views/paginas/perfil.php
        $this->view('paginas/perfil', $dados);
    }


    // =========================================================
    // SOBRE NÓS
    // =========================================================
    //
    // Abre a página com informações sobre o sistema/projeto.
    // =========================================================

    public function sobre()
    {
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página sobre nós',
            'descricao' => 'pagina sobre nós'
        ];

        // Abre: App/Views/paginas/sobre.php
        $this->view('paginas/sobre', $dados);
    }


    // =========================================================
    // CONFIGURAÇÕES
    // =========================================================
    //
    // Abre a página de configurações.
    // =========================================================

    public function configuracao()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        if (empty($_SESSION['csrf_token_notificacoes'])) {
            $_SESSION['csrf_token_notificacoes'] = bin2hex(random_bytes(32));
        }

        $notificacoes = $this->model('Notificacao');
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de configuração',
            'descricao' => 'pagina configuração',
            'preferencias_email' => $notificacoes->buscarPreferenciasEmail((int) $_SESSION['usuario_id']),
            'csrf_token' => $_SESSION['csrf_token_notificacoes'],
            'is_admin' => ($_SESSION['usuario_perfil'] ?? '') === 'admin',
        ];

        // Abre: App/Views/paginas/configuracao.php
        $this->view('paginas/configuracao', $dados);
    }

    public function salvarPreferenciasNotificacao()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || empty($_SESSION['csrf_token_notificacoes'])
            || !hash_equals($_SESSION['csrf_token_notificacoes'], $token)
        ) {
            http_response_code(403);
            exit('Requisição inválida.');
        }

        $salvou = $this->model('Notificacao')->salvarPreferenciasEmail(
            (int) $_SESSION['usuario_id'],
            isset($_POST['email_consultas']),
            isset($_POST['email_triagens']),
            isset($_POST['email_cadastros'])
        );

        $_SESSION['configuracao_mensagem'] = $salvou
            ? 'Preferências de e-mail salvas.'
            : 'Não foi possível salvar as preferências.';
        $_SESSION['configuracao_tipo_mensagem'] = $salvou ? 'success' : 'danger';

        URL::redirecionar('paginas/configuracao');
        exit;
    }


    // =========================================================
    // RELATÓRIO
    // =========================================================
    //
    // Abre a página de relatórios.
    // =========================================================

    public function relatorio()
    {
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de relatório',
            'descricao' => 'pagina relatorio'
        ];

        // Abre: App/Views/paginas/relatorio.php
        $this->view('paginas/relatorio', $dados);
    }


    // =========================================================
    // GERENCIAR DISPOSITIVOS
    // =========================================================
    //
    // Abre a página responsável pelo gerenciamento dos
    // dispositivos.
    // =========================================================

    public function gerenciarDispositivos()
    {
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de gerenciamento de dispositivos',
            'descricao' => 'pagina gerenciamento de dispositivos'
        ];

        // Abre: App/Views/paginas/gerenciarDispositivos.php
        $this->view('paginas/gerenciarDispositivos', $dados);
    }
}