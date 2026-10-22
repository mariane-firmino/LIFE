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
        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Página de home',
            'descricao' => 'pagina home'
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
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de configuração',
            'descricao' => 'pagina configuração'
        ];

        // Abre: App/Views/paginas/configuracao.php
        $this->view('paginas/configuracao', $dados);
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