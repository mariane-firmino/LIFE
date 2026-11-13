<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';
?>
<div class="layout">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">
        <div class="row align-items-center">
            <!-- CABEÇALHO -->
            <header class="home-topo">
                <div class="home-titulo">
                    <h1>Notificação</h1>
                    <p>Veja todas as notificações aqui.</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </header>
        </div>
    </div>

<?php
// Texto seguro para o HTML (título/descrição podem vir NULL do banco).
$notiH = static fn($texto) => htmlspecialchars((string) ($texto ?? ''), ENT_QUOTES, 'UTF-8');
$statusAtual = (string) ($dados['status'] ?? '');
?>
<form action="<?= URL ?>/notificacoes/notificacao" method="GET">

    <!-- Botão padrão do formulário: Enter na pesquisa mantém o status
         escolhido (antes o Enter "clicava" em "Todas" e limpava o filtro). -->
    <button type="submit" name="status" value="<?= $notiH($statusAtual) ?>" hidden tabindex="-1" aria-hidden="true"></button>

    <div class="container text-center">
        <div class="row align-items-center g-3 noti-responsive">

            <!-- FILTROS -->
            <div class="col-12 col-lg-5">

                <div class="d-flex justify-content-center flex-wrap gap-2">

                    <button
                        type="submit"
                        name="status"
                        value=""
                        class="btn <?= empty($dados['status']) ? 'btn-success' : 'btn-outline-success' ?>">
                        Todas
                    </button>

                    <button
                        type="submit"
                        name="status"
                        value="nao_lidas"
                        class="btn <?= $dados['status'] === 'nao_lidas' ? 'btn-success' : 'btn-outline-success' ?>">
                        Não Lidas
                    </button>

                    <button
                        type="submit"
                        name="status"
                        value="lidas"
                        class="btn <?= $dados['status'] === 'lidas' ? 'btn-success' : 'btn-outline-success' ?>">
                        Lidas
                    </button>

                </div>

            </div>


            <!-- PESQUISA -->
            <div class="col-12 col-md-7 col-lg-4">

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <input
                        class="form-control"
                        type="text"
                        name="pesquisa" maxlength="100"
                        value="<?= htmlspecialchars($dados['pesquisa'] ?? '') ?>"
                        placeholder="Pesquise alguma coisa"
                        aria-label="Pesquise alguma coisa">

                </div>

            </div>


            <!-- DATA -->
            <div class="col-12 col-md-5 col-lg-3">

                <input 
        type="date" 
        class="form-control" 
        id="data" 
        name="data"
        value="<?= $notiH($dados['data'] ?? '') ?>"
        aria-label="Filtrar por data"
        onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
            </div>

        </div>
    </div>

</form>

    <?php if (!empty($dados['notificacoes'])): ?>

        <?php foreach ($dados['notificacoes'] as $notificacao): ?>

            <div class="alert notificacao-item mt-3 mx-3 border border-2 
    <?= $notificacao->noti_status === 'Lida'
                ? 'border-success'
                : 'border-danger' ?>" role="alert">
                <div class="row align-items-center">

                    <!-- Título e descrição -->
                    <div class="col">

                        <h6 class="mb-1">
                            <strong>
                                <?= $notiH($notificacao->noti_titulo); ?>
                            </strong>
                        </h6>

                        <small>
                            <?= $notiH($notificacao->noti_descricao); ?>
                        </small>

                    </div>

                    <!-- Data -->
                    <div class="col-auto d-flex align-items-center">
                        <small class="text-nowrap">

                            <?= date('d/m/Y H:i', strtotime($notificacao->noti_data)); ?>

                        </small>
                    </div>

                    <!-- Três pontinhos -->
                    <div class="col-auto">

                        <div class="dropdown">

                            <button class="btn p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opções da notificação">

                                <b class="tres-pontos">...</b>

                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <a class="dropdown-item"
                                        href="<?= URL ?>/notificacoes/detalhe/<?= $notificacao->noti_id ?>">
                                        Visualizar
                                    </a>

                                </li>

                                <?php if ($notificacao->noti_status !== 'Lida'): ?>
                                <li>
                                    <form method="post" class="m-0" action="<?= URL ?>/notificacoes/marcarComoLida/<?= (int) $notificacao->noti_id ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($dados['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="dropdown-item">Marcar como lida</button>
                                    </form>
                                </li>
                                <?php endif; ?>

                                <li>
                                    <a class="dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal" data-bs-target="#modalExcluir<?= $notificacao->noti_id; ?>">
                                        Excluir
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>
            </div>


            <!-- Modal de confirmação -->
            <div class="modal fade" id="modalExcluir<?= $notificacao->noti_id; ?>" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" aria-hidden="true">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h1 class="modal-title fs-5">
                                Confirmação de Exclusão
                            </h1>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            </button>

                        </div>

                        <div class="modal-body">
                            Tem certeza de que deseja excluir esta notificação?
                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <form method="post" class="d-inline m-0" action="<?= URL ?>/notificacoes/excluir/<?= (int) $notificacao->noti_id; ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($dados['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-danger">Confirmar</button>
                            </form>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="alert alert-info mt-3 mx-3">
            Nenhuma notificação encontrada.
        </div>

    <?php endif; ?>

</div>
<?php include '../App/Views/footer.php' ?>