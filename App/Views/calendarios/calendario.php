<?php
/**
 * View: Calendário
 * Variáveis recebidas do Controller: $dados['tipos'], $dados['responsaveis']
 */
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$tipos        = $dados['tipos'];
$responsaveis = $dados['responsaveis'];
?>

<!-- FullCalendar (sem Composer: carrega direto do CDN) -->
<link rel="stylesheet" href="<?= URL ?>/css/calendario.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/pt-br.global.min.js"></script>

<div class="layout">

    <div class="container-fluid px-4 pt-5 pb-3">
        <div class="row align-items-center">
            <header class="home-topo">
                <div class="home-titulo">
                    <h1>Calendário</h1>
                    <p>Gerencie seus compromissos no calendário.</p>
                </div>
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </header>
        </div>
    </div>

    <?php if (!empty($_SESSION['mensagem'])): ?>
        <div class="container-fluid px-4">
            <div class="alert alert-<?= htmlspecialchars($_SESSION['tipo_msg'] ?? 'success') ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['mensagem']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_msg']); ?>
    <?php endif; ?>

    <main class="cal-page">
        <div class="cal-layout">

            <!-- CALENDÁRIO -->
            <section class="cal-card">
                <div class="cal-toolbar-top">
                    <button type="button" id="cal-btn-novo" class="btn btn-success cal-btn-novo">
                        + Adicionar Consulta
                    </button>

                    <!-- Legenda de cores por tipo -->
                    <ul class="cal-legend" aria-label="Legenda">
                        <?php foreach ($tipos as $nome => $cor): ?>
                            <li>
                                <span class="cal-dot" style="background: <?= htmlspecialchars($cor) ?>"></span>
                                <?= htmlspecialchars($nome) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="calendario"></div>
            </section>

            <!-- PAINEL LATERAL -->
            <aside class="cal-side">
                <h2 class="cal-side-title">Compromissos do mês</h2>

                <div class="cal-side-block">
                    <h3>Próximas consultas <span class="cal-count" id="cal-count-upcoming">0</span></h3>
                    <div id="cal-upcoming" class="cal-list"></div>
                </div>

                <div class="cal-side-block">
                    <h3>Consultas realizadas <span class="cal-count" id="cal-count-done">0</span></h3>
                    <div id="cal-done" class="cal-list"></div>
                </div>
            </aside>

        </div>
    </main>

    <?php include '../App/Views/calendarios/adicionarConsulta.php'; ?>
    <?php include '../App/Views/calendarios/visualizarConsulta.php'; ?>

</div>

<script>
    window.CALENDARIO_CONFIG = {
        url: "<?= URL ?>/calendarios",
        // Cores por tipo, vindas do Model (única fonte).
        cores: <?= json_encode($tipos, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="<?= URL ?>/js/calendario.js"></script>

<?php include '../App/Views/footer.php'; ?>
