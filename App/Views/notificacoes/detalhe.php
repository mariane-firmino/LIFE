<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';
?>
<div class="layout">
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">

            <div class="col-8 linha-verde">
                <h1>Notificações</h1>
                <p>Veja todas as notificações aqui.</p>
            </div>

            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
        </div>
    </div>

    <!-- =========== Como acessar as notificações =========== -->
    <div class="container-fluid px-3 px-md-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb flex-wrap">
                <li class="breadcrumb-item">
                    <a href="<?= URL ?>/notificacoes/notificacao">
                        Notificação
                    </a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                    Visualizar detalhes
                </li>
            </ol>
        </nav>
    </div>
    <!-- =========== FIM Como acessar as notificações =========== -->

    <!-- =========== Detalhes da notificação =========== -->
    <div class="container-fluid px-3 px-md-4">
        <div class="mt-4">
            <h3>
            <?= htmlspecialchars((string) ($dados['notificacao']->noti_titulo ?? ''), ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        <small class="text-muted">
            Data:
            <?= date(
                'd/m/Y \à\s H:i',
                strtotime($dados['notificacao']->noti_data)
            ); ?>
        </small>

        <div class="shadow p-3 p-md-4 mt-3 mb-3 rounded">

            <p class="mb-0">
                <?= nl2br(
                    htmlspecialchars((string) ($dados['notificacao']->noti_descricao ?? ''), ENT_QUOTES, 'UTF-8')
                ); ?>
            </p>

        </div>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="<?= URL ?>/notificacoes/notificacao" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Voltar
                </a>

                <?php if (($dados['notificacao']->noti_status ?? '') !== 'Lida'): ?>
                    <form method="post" class="m-0" action="<?= URL ?>/notificacoes/marcarComoLida/<?= (int) $dados['notificacao']->noti_id ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($dados['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-success">Marcar como lida</button>
                    </form>
                <?php else: ?>
                    <span class="btn btn-light disabled border-0 text-success">
                        <i class="bi bi-check2-all me-1" aria-hidden="true"></i> Lida
                    </span>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
<?php include '../App/Views/footer.php' ?>