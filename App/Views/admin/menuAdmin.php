<?php
// =========================================================
// MENU DO ADMINISTRADOR  ->  App/Views/admin/menuAdmin.php
// Mesmo visual do menu comum (Public/css/menu.css e
// Public/js/menu.js), mas com os links do administrador.
//
// O sino mostra quantos servidores aguardam ativação
// (vem do Controller em $dados['totalPendentes']).
// =========================================================

// Servidores aguardando ativação. Se não vier, não mostra a bolinha.
$totalPendentes = (int) ($dados['totalPendentes'] ?? 0);

// Nome do usuário logado (escapado por segurança).
$nomeUsuario = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');

// Foto do usuário (mesma lógica do menu comum).
$nomeFotoUsuario = basename((string) ($_SESSION['usuario_foto'] ?? ''));
$caminhoFotoUsuario = dirname(__DIR__, 3) . '/Public/uploads/perfis/' . $nomeFotoUsuario;
$fotoUsuario = $nomeFotoUsuario !== '' && is_file($caminhoFotoUsuario)
    ? URL . '/uploads/perfis/' . rawurlencode($nomeFotoUsuario)
    : URL . '/img/user.avif';
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

            <!-- Cabeçalho: marca, pendências e fechar -->
            <div class="offcanvas-header menu-cabecalho">

                <div class="d-flex align-items-center gap-2">
                    <div class="menu-logo">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>

                    <div>
                        <h5 class="menu-titulo" id="offcanvasNavbarLabel">LIFE</h5>
                        <small class="menu-subtitulo">Painel do administrador</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <!-- Sino: leva aos cadastros aguardando ativação -->
                    <a href="<?= URL ?>/admin/perfis" class="btn menu-sino position-relative"
                        aria-label="Cadastros aguardando ativação">
                        <i class="bi bi-person-check text-white fs-5"></i>

                        <?php if ($totalPendentes > 0): ?>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $totalPendentes > 99 ? '99+' : $totalPendentes ?>
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
                        <img src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto do usuário"
                            class="rounded-circle" width="50" height="50">
                        <span class="usuario-online"></span>
                    </div>

                    <div class="overflow-hidden">
                        <h6 class="mb-0 text-white text-truncate"><?= $nomeUsuario ?></h6>
                        <small class="text-white">Administrador</small>
                    </div>
                </div>

                <!-- Links -->
                <ul class="navbar-nav flex-grow-1 pe-3">

                    <li class="menu-secao-titulo">PRINCIPAL</li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/admin/home">
                            <i class="bi bi-speedometer2 espaco-esquerdo"></i> PAINEL
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/paginas/perfil">
                            <i class="bi bi-person-fill espaco-esquerdo"></i> PERFIL
                        </a>
                    </li>

                    <li class="menu-secao-titulo espaco-esquerdo">ADMINISTRAÇÃO</li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/admin/perfis">
                            <i class="bi bi-people-fill espaco-esquerdo"></i> GERENCIAR PERFIS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL ?>/admin/sistema">
                            <i class="bi bi-sliders espaco-esquerdo"></i> INFORMAÇÕES DO SISTEMA
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