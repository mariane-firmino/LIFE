<?php
session_start();
include "./../App/configuracao.php";
include "./../App/autoload.php";

$db = new Database;
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
</head>
<body>
    <?php
    $rotas = new Rota();
    ?>
    <?php include __DIR__ . '/../App/Views/vlibras.php'; ?>
</body>
<!-- linkando js -->
<script src="<?= URL ?>/js/bootstrap.bundle.min.js"></script>
<script src="<?= URL ?>/js/formatacao.js"></script>
<script src="<?= URL ?>/js/consulta.js"></script>
<script src="<?= URL ?>/js/api.js"></script>
<script src="<?= URL ?>/js/calendario.js"></script>
<script src="<?= URL ?>/js/menu.js"></script>
<script src="<?= URL ?>/js/acessibilidade.js"></script>
<script src="<?= URL ?>/js/sobre.js"></script>
</html>