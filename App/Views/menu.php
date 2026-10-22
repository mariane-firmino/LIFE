<?php
// =========================================================
// DADOS DO MENU
// Apenas prepara os valores que serão mostrados.
// O visual está em Public/css/menu.css e o comportamento
// (destacar a página atual) em Public/js/menu.js.
// =========================================================

// Total de notificações (vem do controller em $dados).
// Se não vier, não mostra a bolinha vermelha.
$totalNotificacoes = (int) ($dados['totalNotificacoes']->total ?? 0);

// Nome e função do usuário logado (escapados por segurança).
$nomeUsuario = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$funcaoUsuario = htmlspecialchars($_SESSION['usuario_funcao'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!-- ===================================== -->
<!-- BARRA SUPERIOR (botão que abre o menu) -->
<!-- ===================================== -->

<nav class="navbar fixed-top">
    <div class="container-fluid">

        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
            aria-controls="offcanvasNavbar" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- ===================================== -->
        <!-- MENU LATERAL -->
        <!-- ===================================== -->

        <div class="offcanvas offcanvas-start menu-lateral" tabindex="-1" id="offcanvasNavbar"
            aria-labelledby="offcanvasNavbarLabel">

            <!-- Cabeçalho: marca, notificações e fechar -->
            <div class="offcanvas-header menu-cabecalho">

                <div class="d-flex align-items-center gap-2">
                    <div class="menu-logo">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>

                    <div>
                        <h5 class="menu-titulo" id="offcanvasNavbarLabel">LIFE</h5>
                        <small class="menu-subtitulo">Sistema de atendimento</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <!-- Notificações (só mostra o número se houver) -->
                    <a href="<?= URL ?>/notificacoes/notificacao" class="btn menu-sino position-relative"
                        aria-label="Notificações">
                        <i class="bi bi-bell text-white fs-5"></i>

                        <?php if ($totalNotificacoes > 0): ?>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $totalNotificacoes > 99 ? '99+' : $totalNotificacoes ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                        aria-label="Fechar"></button>
                </div>

            </div>

            <div class="offcanvas-body">

                <!-- Usuário logado -->
                <div class="d-flex align-items-center usuario-img gap-2 mb-3">
                    <div class="position-relative">
                        <img src="<?= URL ?>/img/user.avif" alt="Foto do usuário" class="rounded-circle" width="50"
                            height="50">
                        <span class="usuario-online"></span>
                    </div>

                    <div class="overflow-hidden">
                        <h6 class="mb-0 text-white text-truncate"><?= $nomeUsuario ?></h6>
                        <small class="text-white"><?= $funcaoUsuario ?></small>
                    </div>
                </div>

                <!-- Links -->
                <ul class="navbar-nav flex-grow-1 pe-3">

                    <li class="menu-secao-titulo">PRINCIPAL</li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/paginas/home">
                            <i class="bi bi-house-door-fill espaco-esquerdo"></i> HOME
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/paginas/perfil">
                            <i class="bi bi-person-fill espaco-esquerdo"></i> PERFIL
                        </a>
                    </li>

                    <li class="menu-secao-titulo espaco-esquerdo">ALUNOS</li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/alunos/aluno">
                            <i class="bi bi-mortarboard-fill espaco-esquerdo"></i> CADASTRO DE ALUNO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/alunos/consulta">
                            <i class="bi bi-file-earmark-person-fill espaco-esquerdo"></i> CONSULTAR ALUNO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/calendarios/calendario">
                            <i class="bi bi-calendar3 espaco-esquerdo"></i> CALENDÁRIO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/paginas/relatorio">
                            <i class="bi bi-file-earmark-bar-graph-fill espaco-esquerdo"></i> RELATÓRIO
                        </a>
                    </li>

                    <li class="menu-secao-titulo">SISTEMA</li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/paginas/configuracao">
                            <i class="bi bi-gear-wide-connected espaco-esquerdo"></i> CONFIGURAÇÕES
                        </a>
                    </li>

                    <!-- Sair: abre o modal de confirmação -->
                    <li class="nav-item menu-sair-area">
                        <button type="button" class="nav-link menu-sair w-100 text-start" data-bs-toggle="modal"
                            data-bs-target="#staticBackdrop">
                            <i class="bi bi-box-arrow-right espaco-esquerdo"></i> SAIR
                        </button>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</nav>


<!-- ===================================== -->
<!-- MODAL DE CONFIRMAÇÃO DE SAÍDA -->
<!-- ===================================== -->

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-saida">

            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="bi bi-box-arrow-right"></i> Sair do sistema
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body d-flex align-items-center gap-3">
                <div class="modal-saida-icone">
                    <i class="bi bi-question-circle"></i>
                </div>

                <div>
                    <strong>Tem certeza que deseja sair?</strong>
                    <p class="mb-0 text-muted">Você precisará fazer login novamente para acessar o sistema.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="<?= URL ?>/usuarios/logout" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>

        </div>
    </div>
</div>