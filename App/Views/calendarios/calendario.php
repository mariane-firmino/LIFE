<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';
?>

<div class="layout">

    <!-- =====================================================
         CABEÇALHO DA PÁGINA
    ====================================================== -->
    <div class="container-fluid calendario-page-header">
        <div class="calendario-page-header-content">

            <div class="calendario-page-title">
                <h1>Calendário</h1>
                <p>Gerencie seus compromissos no calendário.</p>
            </div>

            <div class="calendario-page-logo">
                <img
                    src="<?= URL ?>/img/logo_enfermaria.jpeg"
                    class="logo-home"
                    alt="Logo"
                >
            </div>

        </div>
    </div>


    <!-- =====================================================
         ÁREA PRINCIPAL
    ====================================================== -->
    <main class="calendario-agenda-page">

        <div class="calendario-agenda-layout">

            <!-- CALENDÁRIO -->
            <div class="calendario-agenda-calendar-column">

                <!-- Botão "Adicionar compromisso" (abre o modal abaixo) -->
                <?php include '../App/Views/calendarios/adicionarCompromisso.php'; ?>

                <section class="calendario-agenda-box">

                    <header class="calendario-agenda-header">

                        <button
                            id="calendario-agenda-prev"
                            class="calendario-agenda-arrow"
                            type="button"
                            aria-label="Mês anterior"
                        >←</button>

                        <div class="calendario-agenda-title-area">
                            <button
                                id="calendario-agenda-month-button"
                                class="calendario-agenda-title-button"
                                type="button"
                            >Janeiro</button>

                            <span class="calendario-agenda-title-of">de</span>

                            <button
                                id="calendario-agenda-year-button"
                                class="calendario-agenda-title-button"
                                type="button"
                            >2026</button>
                        </div>

                        <button
                            id="calendario-agenda-next"
                            class="calendario-agenda-arrow"
                            type="button"
                            aria-label="Próximo mês"
                        >→</button>

                    </header>

                    <!-- Menus de mês e ano (preenchidos pelo JS) -->
                    <div
                        id="calendario-agenda-month-menu"
                        class="calendario-agenda-dropdown calendario-agenda-month-dropdown"
                    ></div>

                    <div
                        id="calendario-agenda-year-menu"
                        class="calendario-agenda-dropdown calendario-agenda-year-dropdown"
                    ></div>

                    <!-- Dias da semana e grade (preenchidos pelo JS) -->
                    <div id="calendario-agenda-week" class="calendario-agenda-week"></div>
                    <div id="calendario-agenda-grid" class="calendario-agenda-grid"></div>

                </section>

            </div>


            <!-- PAINEL DIREITO -->
            <aside class="calendario-agenda-side-panel">

                <h2 class="calendario-agenda-side-title">Compromissos do mês</h2>

                <div class="calendario-agenda-legend">
                    <span class="calendario-agenda-legend-dot calendario-agenda-black"></span>
                    <span>Consultas realizadas</span>
                </div>

                <section class="calendario-agenda-side-section">
                    <h3>Próximas consultas</h3>
                    <div id="calendario-agenda-upcoming" class="calendario-agenda-side-list"></div>
                </section>

                <section class="calendario-agenda-side-section calendario-agenda-completed-section">
                    <h3>Consultas realizadas</h3>
                    <div id="calendario-agenda-completed" class="calendario-agenda-side-list"></div>
                </section>

            </aside>

        </div>

    </main>


    <!-- =====================================================
         MODAL: ADICIONAR COMPROMISSO
         Envia para o controller PHP (POST normal).
    ====================================================== -->
    <div id="calendario-agenda-modal" class="calendario-agenda-modal">

        <div class="calendario-agenda-modal-box">

            <div class="calendario-agenda-modal-header">
                <h2>Adicionar Compromisso</h2>

                <button
                    id="calendario-agenda-modal-close"
                    type="button"
                    aria-label="Fechar"
                >×</button>
            </div>

            <form
                id="calendario-agenda-form"
                action="<?= URL ?>/calendarios/salvarConsulta"
                method="POST"
            >

                <div class="calendario-agenda-field">
                    <label for="calendario-agenda-date">Data</label>
                    <input
                        id="calendario-agenda-date"
                        name="data"
                        type="date"
                        required
                    >
                </div>

                <div class="calendario-agenda-field">
                    <label for="calendario-agenda-time">Horário</label>
                    <input
                        id="calendario-agenda-time"
                        name="horario"
                        type="time"
                        required
                    >
                </div>

                <div class="calendario-agenda-field">
                    <label for="calendario-agenda-type">Tipo</label>
                    <input
                        id="calendario-agenda-type"
                        name="tipo"
                        type="text"
                        placeholder="Consulta"
                        maxlength="60"
                        required
                    >
                </div>

                <div class="calendario-agenda-field">
                    <label for="calendario-agenda-color">Cor</label>
                    <input
                        id="calendario-agenda-color"
                        name="cor"
                        type="color"
                        value="#ff0000"
                    >
                </div>

                <button class="btn btn-success w-100" type="submit">
                    Salvar compromisso
                </button>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     SCRIPTS
     (remova as duas primeiras linhas se o footer já carrega)
========================================================== -->
<script src="<?= URL ?>/js/agendaAPI.js"></script>
<script src="<?= URL ?>/js/calendario.js"></script>

<script>
/* Abrir e fechar o modal */
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("calendario-agenda-modal");
    const fechar = document.getElementById("calendario-agenda-modal-close");
    const abrir = document.querySelector(".calendario-agenda-add-button");

    if (!modal) return;

    if (abrir) {
        abrir.addEventListener("click", function () {
            modal.classList.add("is-open");
        });
    }

    if (fechar) {
        fechar.addEventListener("click", function () {
            modal.classList.remove("is-open");
        });
    }

    // Clicar fora da caixa também fecha
    modal.addEventListener("click", function (e) {
        if (e.target === modal) modal.classList.remove("is-open");
    });
});
</script>

<?php include '../App/Views/footer.php'; ?>