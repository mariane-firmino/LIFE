<?php
// =========================================================
// DADOS DO MENU
// =========================================================

// Total de notificações (vem do controller em $dados).
// Se não vier, não mostra a bolinha vermelha.
$totalNotificacoes = (int) ($dados['totalNotificacoes']->total ?? 0);

// Caminho da página atual, usado para destacar o item ativo.
$caminhoAtual = strtolower(trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'));

// Devolve os atributos do link; se for a página atual,
// adiciona a classe "active" do Bootstrap.
$atributosLink = function ($rota) use ($caminhoAtual) {
    $rota = strtolower(trim($rota, '/'));

    if (substr($caminhoAtual, -strlen($rota)) === $rota) {
        return 'class="nav-link active" aria-current="page"';
    }

    return 'class="nav-link"';
};
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

        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-label="Menu principal">

            <div class="offcanvas-header">

                <!-- Notificações (só mostra o número se houver) -->
                <a href="<?= URL ?>/notificacoes/notificacao" class="btn btn-success position-relative"
                    aria-label="Notificações">
                    <i class="bi bi-bell text-white fs-4"></i>

                    <?php if ($totalNotificacoes > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $totalNotificacoes > 99 ? '99+' : $totalNotificacoes ?>
                        </span>
                    <?php endif; ?>
                </a>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="Fechar"></button>

            </div>

            <div class="offcanvas-body">

                <!-- Usuário logado -->
                <div class="d-flex align-items-center usuario-img gap-2 mb-4">
                    <img src="<?= URL ?>/img/user.avif" alt="Foto do usuário" class="rounded-circle" width="50"
                        height="50">

                    <div>
                        <h6 class="mb-0 text-white">
                            <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>
                        </h6>
                        <small class="text-white">
                            <?= htmlspecialchars($_SESSION['usuario_funcao'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </small>
                    </div>
                </div>

                <!-- Links -->
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <li class="nav-item">
                        <a <?= $atributosLink('paginas/home') ?> href="<?= URL ?>/paginas/home">
                            <i class="bi bi-house-door-fill"></i> HOME
                        </a>
                    </li>
                    <li class="nav-item">
                        <a <?= $atributosLink('paginas/perfil') ?> href="<?= URL ?>/paginas/perfil">
                            <i class="bi bi-person-fill"></i> PERFIL
                        </a>
                    </li>
                    <li class="nav-item">
                        <a <?= $atributosLink('alunos/aluno') ?> href="<?= URL ?>/alunos/aluno">
                            <i class="bi bi-mortarboard-fill"></i> CADASTRO DE ALUNO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a <?= $atributosLink('alunos/consulta') ?> href="<?= URL ?>/alunos/consulta">
                            <i class="bi bi-file-earmark-person-fill"></i> CONSULTAR ALUNO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a <?= $atributosLink('calendarios/calendario') ?> href="<?= URL ?>/calendarios/calendario">
                            <i class="bi bi-calendar3"></i> CALENDÁRIO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a <?= $atributosLink('paginas/relatorio') ?> href="<?= URL ?>/paginas/relatorio">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i> RELATÓRIO
                        </a>
                    </li>
                    <li class="nav-item">
                        <a <?= $atributosLink('paginas/configuracao') ?> href="<?= URL ?>/paginas/configuracao">
                            <i class="bi bi-gear-wide-connected"></i> CONFIGURAÇÕES
                        </a>
                    </li>

                    <!-- Sair: abre o modal de confirmação -->
                    <li class="nav-item vermelho">
                        <button type="button" class="nav-link w-100 text-start" data-bs-toggle="modal"
                            data-bs-target="#staticBackdrop">
                            <i class="bi bi-person-walking"></i> SAIR
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
        <div class="modal-content">

            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Sair</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <strong>Tem certeza que deseja sair?</strong>
                <p class="mb-0 text-muted">Você precisará fazer login novamente para acessar o sistema.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="<?= URL ?>/usuarios/logout" class="btn btn-danger">Sair</a>
            </div>

        </div>
    </div>
</div>