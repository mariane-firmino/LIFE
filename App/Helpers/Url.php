<?php
class Url{
    public static function redirecionar($url){
        // URL sempre usa "/" (DIRECTORY_SEPARATOR é "\" no Windows e gerava "life\usuarios/login").
        header("Location: " . URL . '/' . ltrim($url, '/'));
        // Para a execução aqui: sem isso o resto do método continuava rodando
        // (ex.: o cadastro voltava a montar o formulário e "gastava" a mensagem de sucesso).
        exit;
    }//fim da função URL
}
