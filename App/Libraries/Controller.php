<?php
class Controller { // aqui é possível controlar para qual página você quer ir de forma mais simples
    public function model($model)
    {
        require_once '../app/Models/' . $model . '.php';
        return new $model;
    } // fim da função model

    public function library($library)
    {
        require_once '../app/Libraries/' . $library . '.php';
        return new $library;
    } // fim da função library

    public function view($view, $dados = [])
    {
        if (isset($_SESSION['usuario_id'])) {

            $notificacaoModel = $this->model('Notificacao');

            $dados['totalNotificacoes'] =
                $notificacaoModel->contarNotificacao(
                    $_SESSION['usuario_id']
                );

        } else {

            // Usuário não logado
            $dados['totalNotificacoes'] = (object) [
                'total' => 0
            ];
        }
        
        $arquivo = '../app/Views/' . $view . '.php';

        if (file_exists($arquivo)) {
            require_once $arquivo;
        } else {
            die("O arquivo não existe");
        }
    } // fim da função view
}