<?php
class Rota
{
    private $controlador = "Paginas";
    private $metodo = 'index';
    private $parametros = [];

    public function __construct()
    {
        $url = $this->url() ? $this->url() : [0];

        // Caminho absoluto: "../app/" (minúsculo) só funcionava no Windows.
        $pasta = dirname(__DIR__) . '/Controllers/';
        $arquivo = $pasta . ucwords((string) $url[0]) . '.php';

        if (preg_match('/^[A-Za-z]+$/', (string) $url[0]) && is_file($arquivo)) {
            $this->controlador = ucwords($url[0]);
            unset($url[0]);
        }

        require_once $pasta . $this->controlador . '.php';

        // Arquivo sem a classe esperada: responde 404 em vez de erro fatal.
        if (!class_exists($this->controlador)) {
            $this->naoEncontrado();
        }

        $this->controlador = new $this->controlador;

        if (isset($url[1])) {
            if (method_exists($this->controlador, $url[1]) && is_callable([$this->controlador, $url[1]])) {
                $this->metodo = $url[1];
                unset($url[1]);
            } //fim do if que verifica se o método existe
        } //fim do if que verifica se a url existe

        // Controller sem o método pedido nem "index" (ex.: /alunos): 404.
        if (!is_callable([$this->controlador, $this->metodo])) {
            $this->naoEncontrado();
        }

        $this->parametros = $url ? array_values($url) : [];
        call_user_func_array([$this->controlador, $this->metodo], $this->parametros);
    }

    private function naoEncontrado()
    {
        http_response_code(404);
        echo '<div class="container py-5 text-center">'
            . '<h1 class="h3">Página não encontrada</h1>'
            . '<p><a href="' . URL . '">Voltar para o início</a></p>'
            . '</div>';
        exit;
    }

    private function url()
    {
        //o filtro FILTER_SANITIZE_URL remove todos os caracteres ilegais de uma URL
        $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
        //verifica se a url existe
        if (isset($url)) {
            $url = trim(rtrim($url, '/'));
            $url = explode('/', $url);
            return $url; //retorna a url
        } //fim do if
    } //fim do método url

} //fim da classe
