<?php

if (!class_exists('Dispositivo')) {
    require_once dirname(__DIR__) . '/Models/Dispositivo.php';
}

// =========================================================
// CONTROLE DE SESSÃO
// Executado a cada requisição (Public/index.php).
// - Registra o dispositivo de quem está logado.
// - Desloga quem teve a sessão encerrada remotamente
//   pela página "Gerenciar dispositivos".
// =========================================================

class ControleSessao
{
    // Só grava "última atividade" a cada 60 segundos.
    const INTERVALO_ATUALIZACAO = 60;

    // Retorna o model Dispositivo (evita carregar o arquivo duas vezes).
    public static function modelo()
    {
        return new Dispositivo();
    }

    public static function verificar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            return;
        }

        $modelo = new Dispositivo();

        // Conta desativada (ou apagada) pelo administrador: perde o acesso na hora.
        // Antes a pessoa continuava usando o sistema até a sessão expirar.
        if (!$modelo->servidorAtivo($_SESSION['usuario_id'])) {
            if (!empty($_SESSION['sessao_token'])) {
                $modelo->encerrarPorToken($_SESSION['sessao_token']);
            }
            $_SESSION = [];
            session_destroy();
            session_start();
            Sessao::mensagem(
                'usuario',
                'Seu acesso foi desativado pelo administrador.',
                'alert alert-warning'
            );
            Url::redirecionar('usuarios/login');
            exit;
        }

        // Sessão iniciada antes deste recurso existir: registra agora.
        if (empty($_SESSION['sessao_token'])) {
            $_SESSION['sessao_token'] = $modelo->registrar($_SESSION['usuario_id']);
            return;
        }

        $sessao = $modelo->sessaoAtiva($_SESSION['usuario_id'], $_SESSION['sessao_token']);

        if (!$sessao) {
            // A sessão foi encerrada em outro dispositivo.
            $_SESSION = [];
            session_destroy();
            session_start();
            Sessao::mensagem(
                'usuario',
                'Sua sessão foi encerrada a partir de outro dispositivo. Faça login novamente.',
                'alert alert-warning'
            );
            Url::redirecionar('usuarios/login');
            exit;
        }

        if ((int) $sessao->segundos_inativo >= self::INTERVALO_ATUALIZACAO) {
            $modelo->atualizarAtividade($_SESSION['sessao_token']);
        }
    }
}
