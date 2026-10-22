<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

/*
 * Números dos cards.
 * O controller deve enviar em $dados:
 *   total, meninos, meninas, com_doenca
 * Enquanto não enviar, aparece 0.
 */
$total     = (int) ($dados['total']      ?? 0);
$meninos   = (int) ($dados['meninos']    ?? 0);
$meninas   = (int) ($dados['meninas']    ?? 0);
$comDoenca = (int) ($dados['com_doenca'] ?? 0);

// Porcentagem em relação ao total de alunos
function enfPorcentagem($parte, $total)
{
    return $total > 0 ? round(($parte / $total) * 100) : 0;
}

$cards = [
    [
        'classe' => 'enf-perfil--total',
        'icone'  => 'bi-people-fill',
        'titulo' => 'Alunos cadastrados',
        'valor'  => $total,
        'sub'    => $total === 1 ? '1 aluno no sistema' : $total . ' alunos no sistema',
    ],
    [
        'classe' => 'enf-perfil--meninos',
        'icone'  => 'bi-gender-male',
        'titulo' => 'Meninos',
        'valor'  => $meninos,
        'sub'    => enfPorcentagem($meninos, $total) . '% do total',
    ],
    [
        'classe' => 'enf-perfil--meninas',
        'icone'  => 'bi-gender-female',
        'titulo' => 'Meninas',
        'valor'  => $meninas,
        'sub'    => enfPorcentagem($meninas, $total) . '% do total',
    ],
    [
        'classe' => 'enf-perfil--saude',
        'icone'  => 'bi-heart-pulse-fill',
        'titulo' => 'Alunos com doenças',
        'valor'  => $comDoenca,
        'sub'    => enfPorcentagem($comDoenca, $total) . '% do total',
    ],
];
?>

<!-- Bootstrap Icons (remova esta linha se já estiver carregado no menu.php ou no <head>) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <div class="row align-items-center">

            <!-- CABEÇALHO -->
            <header class="home-topo">

                <div class="home-titulo">
                    <h1>Cadastro de Aluno</h1>
                    <p>Faça o cadastro dos alunos nessa página.</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

            </header>
        </div>

    </div>


    <main class="container-fluid enf-conteudo">

        <!-- RESUMO -->
        <h2 class="enf-secao-titulo">Resumo</h2>

        <div class="row enf-cards">

            <?php foreach ($cards as $card): ?>

                <div class="col-12 col-sm-6 col-lg-3">

                    <article class="card enf-perfil <?= $card['classe'] ?>">

                        <div class="card-body enf-corpo">

                            <div class="enf-card-topo">
                                <h3 class="enf-card-titulo">
                                    <?= htmlspecialchars($card['titulo']) ?>
                                </h3>

                                <span class="enf-card-icone" aria-hidden="true">
                                    <i class="bi <?= $card['icone'] ?>"></i>
                                </span>
                            </div>

                            <div class="enf-card-numero">
                                <?= number_format($card['valor'], 0, ',', '.') ?>
                            </div>

                            <div class="enf-card-sub">
                                <?= htmlspecialchars($card['sub']) ?>
                            </div>

                        </div>

                    </article>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- BOTÃO -->
        <div class="enf-botao-area">

            <a class="btn enf-cadastrar" href="<?= URL ?>/alunos/cadastrar">
                <i class="bi bi-person-plus-fill"></i>
                Cadastrar Aluno
            </a>

        </div>

    </main>

</div>

<?php include '../App/Views/footer.php'; ?>