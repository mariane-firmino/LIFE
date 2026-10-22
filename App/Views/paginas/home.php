<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

/*
 * Números dos cards.
 * O controller deve enviar em $dados:
 *   notificacoes, alunos, atendimentos_hoje, atendimentos_periodicos
 * Enquanto não enviar, aparece 0.
 */
$cards = [
    [
        'classe' => 'home-card--notificacoes',
        'icone'  => 'bi-bell-fill',
        'titulo' => 'Novas notificações',
        'valor'  => (int) ($dados['notificacoes'] ?? 0),
    ],
    [
        'classe' => 'home-card--alunos',
        'icone'  => 'bi-people-fill',
        'titulo' => 'Alunos cadastrados',
        'valor'  => (int) ($dados['alunos'] ?? 0),
    ],
    [
        'classe' => 'home-card--hoje',
        'icone'  => 'bi-clipboard2-pulse-fill',
        'titulo' => 'Atendimentos realizados hoje',
        'valor'  => (int) ($dados['atendimentos_hoje'] ?? 0),
    ],
    [
        'classe' => 'home-card--periodicos',
        'icone'  => 'bi-calendar2-check-fill',
        'titulo' => 'Atendimentos periódicos',
        'valor'  => (int) ($dados['atendimentos_periodicos'] ?? 0),
    ],
];
?>

<!-- Bootstrap Icons (remova esta linha se já estiver carregado no menu.php ou no <head>) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="layout">

    <main class="conteudo">

        <!-- CABEÇALHO -->
        <header class="home-topo">

            <div class="home-titulo">
                <h1>Início</h1>
                <p>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>!</p>
            </div>

            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

        </header>


        <!-- CARDS -->
        <h2 class="home-secao-titulo">Resumo de hoje</h2>

        <section class="home-cards">

            <?php foreach ($cards as $card): ?>

                <article class="home-card <?= $card['classe'] ?>">

                    <span class="home-card-icone" aria-hidden="true">
                        <i class="bi <?= $card['icone'] ?>"></i>
                    </span>

                    <h3><?= htmlspecialchars($card['titulo']) ?></h3>

                    <strong class="home-card-numero">
                        <?= number_format($card['valor'], 0, ',', '.') ?>
                    </strong>

                </article>

            <?php endforeach; ?>

        </section>

    </main>

</div>

<?php include '../App/Views/footer.php'; ?>