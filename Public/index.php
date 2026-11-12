<?php
// Guarda a saída em buffer: assim redirecionamentos, respostas JSON e downloads
// funcionam mesmo depois do HTML do cabeçalho, em qualquer servidor
// (antes dependia do output_buffering do php.ini do XAMPP).
ob_start();

// Cookie de sessão: inacessível ao JavaScript (HttpOnly) e não enviado em
// formulários vindos de outros sites (SameSite=Lax), o que protege contra CSRF
// nos formulários que não têm token próprio.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
include "./../App/configuracao.php";
include "./../App/autoload.php";

$db = new Database;

// Registra o dispositivo e desloga sessões encerradas remotamente.
ControleSessao::verificar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=APP_NOME?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- linkando com o css -->
    <link rel="stylesheet" href="<?= URL ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URL ?>/css/geral.css">
    <link rel="stylesheet" href="<?= URL ?>/css/acessibilidade.css">

    <!-- Acessibilidade: carrega no <head> para aplicar tema e tamanho do texto antes da página aparecer -->
    <script>window.LIFE_URL = <?= json_encode(URL) ?>;</script>
    <script src="<?= URL ?>/js/acessibilidade.js"></script>
</head>
<body>
    <script>LifeAcessibilidade.aplicar();</script>
    <?php
    $rotas = new Rota();
    ?>
    <?php include __DIR__ . '/../App/Views/vlibras.php'; ?>
<!-- linkando js -->
<script src="<?= URL ?>/js/bootstrap.bundle.min.js"></script>
<script src="<?= URL ?>/js/datas.js"></script>
<script src="<?= URL ?>/js/condicionais.js"></script>
<script src="<?= URL ?>/js/formatacao.js"></script>
<script src="<?= URL ?>/js/consulta.js"></script>
<!-- calendario.js NÃO entra aqui: é carregado só pela view do calendário.
     Carregar nos dois lugares fazia cada consulta ser salva duas vezes. -->
<script src="<?= URL ?>/js/menu.js"></script>
<script src="<?= URL ?>/js/sobre.js"></script>
</body>
</html>