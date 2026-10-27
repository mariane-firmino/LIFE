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
$alunosAtencao = $dados['alunos_atencao'] ?? [];

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
        'titulo' => 'Atendimentos periódicos no mês',
        'valor'  => (int) ($dados['atendimentos_periodicos'] ?? 0),
    ],
];
?>

<!-- Bootstrap Icons (remova esta linha se já estiver carregado no menu.php ou no <head>) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="layout">

    <main class="conteudo" id="conteudo-principal" tabindex="-1">

        <!-- CABEÇALHO -->
        <header class="home-topo">

            <div class="home-titulo">
                <h1>Início</h1>
                <p>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>!</p>
            </div>

            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logotipo do sistema LIFE">

        </header>


        <!-- CARDS -->
        <h2 class="home-secao-titulo" id="titulo-resumo">Resumo de hoje</h2>

        <section class="home-cards" aria-labelledby="titulo-resumo">

            <?php foreach ($cards as $card): ?>

                <article class="home-card <?= $card['classe'] ?>" aria-label="<?= htmlspecialchars($card['titulo']) ?>: <?= (int) $card['valor'] ?>">

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


        <!-- LISTA: PESSOAS QUE PRECISAM DE ATENÇÃO -->
        <section class="home-atencao" aria-labelledby="titulo-atencao">

            <div class="home-atencao-cabecalho">
                <h2 class="home-secao-titulo" id="titulo-atencao">Lista de pessoas que precisam de atenção</h2>
                <?php if ($alunosAtencao): ?>
                    <span class="home-atencao-total"><?= count($alunosAtencao) ?> <?= count($alunosAtencao) === 1 ? 'aluno' : 'alunos' ?></span>
                <?php endif; ?>
            </div>

            <p class="home-atencao-legenda">
                Alunos com sinais de alerta na triagem mais recente (últimos 30 dias).
            </p>

            <?php if (!$alunosAtencao): ?>

                <p class="home-atencao-vazio" role="status">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    Nenhum aluno precisa de atenção no momento.
                </p>

            <?php else: ?>

                <ul class="home-atencao-lista">
                    <?php foreach ($alunosAtencao as $aluno): ?>
                        <li class="home-atencao-item">
                            <a class="home-atencao-link"
                               href="<?= URL ?>/alunos/visualizarPerfil/<?= (int) $aluno['id'] ?>">

                                <span class="home-atencao-avatar" aria-hidden="true">
                                    <i class="bi bi-person-fill"></i>
                                </span>

                                <span class="home-atencao-info">
                                    <strong class="home-atencao-nome"><?= htmlspecialchars($aluno['nome']) ?></strong>
                                    <span class="home-atencao-meta">
                                        <?php if ($aluno['turma'] !== ''): ?>
                                            <?= htmlspecialchars($aluno['turma']) ?> ·
                                        <?php endif; ?>
                                        Triagem em <?= date('d/m/Y', strtotime($aluno['data'])) ?>
                                    </span>
                                    <span class="home-atencao-motivos">
                                        <span class="visually-hidden">Motivos:</span>
                                        <?php foreach ($aluno['motivos'] as $motivo): ?>
                                            <span class="home-atencao-motivo"><?= htmlspecialchars($motivo) ?></span>
                                        <?php endforeach; ?>
                                    </span>
                                </span>

                                <span class="home-atencao-ver">
                                    Ver perfil<span class="visually-hidden"> de <?= htmlspecialchars($aluno['nome']) ?></span>
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                </span>

                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>

        </section>

    </main>

</div>

<?php include '../App/Views/footer.php'; ?>