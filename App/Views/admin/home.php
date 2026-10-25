<?php
// =====================================================================
// VIEW: PAINEL DO ADMINISTRADOR  ->  App/Views/admin/home.php
// Mostra os totais de servidores e alunos do sistema.
// Os dados vêm do Controller Admin::home().
// =====================================================================

// Só usuários logados (o Controller já confere se é admin).
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/admin/menuAdmin.php';

// CSS adicional das páginas do administrador.
echo '<link rel="stylesheet" href="' . URL . '/css/admin.css">';

// Escapa texto para HTML.
if (!function_exists('h')) {
    function h($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

// Dados do Controller.
$totais = $dados['totais'];
$porCurso = $dados['porCurso'] ?? [];
$porFuncao = $dados['porFuncao'] ?? [];

// Maior valor de cada lista: serve para calcular o tamanho das barras.
$maxCurso = 1;
foreach ($porCurso as $linha) {
    $maxCurso = max($maxCurso, (int) $linha->total);
}
$maxFuncao = 1;
foreach ($porFuncao as $linha) {
    $maxFuncao = max($maxFuncao, (int) $linha->total);
}

$pendentes = (int) $totais->servidores_inativos;
?>
<div class="layout">

    <!-- ===================================== Título ======================================= -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <div class="col-8 linha-verde">
                <h1>Painel do Administrador</h1>
                <p>Olá, <?= h($_SESSION['usuario_nome'] ?? 'administrador') ?>. Veja um resumo do sistema.</p>
            </div>
            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
        </div>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Painel</li>
        </ol>
    </nav>

    <!-- ===================================== Totais ======================================= -->
    <div class="container text-start">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">

            <div class="col">
                <div class="adm-card">
                    <i class="bi bi-person-badge-fill adm-card__icone" aria-hidden="true"></i>
                    <div>
                        <div class="adm-card__numero"><?= (int) $totais->servidores ?></div>
                        <div class="adm-card__rotulo">Servidores cadastrados</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="adm-card">
                    <i class="bi bi-mortarboard-fill adm-card__icone" aria-hidden="true"></i>
                    <div>
                        <div class="adm-card__numero"><?= (int) $totais->alunos ?></div>
                        <div class="adm-card__rotulo">Alunos cadastrados</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="adm-card">
                    <i class="bi bi-person-check-fill adm-card__icone" aria-hidden="true"></i>
                    <div>
                        <div class="adm-card__numero"><?= (int) $totais->servidores_ativos ?></div>
                        <div class="adm-card__rotulo">Servidores ativos</div>
                    </div>
                </div>
            </div>

            <!-- Aguardando ativação: vira link e muda de cor quando há pendências -->
            <div class="col">
                <a href="<?= URL ?>/admin/perfis" class="adm-card <?= $pendentes > 0 ? 'adm-card--alerta' : '' ?>">
                    <i class="bi bi-hourglass-split adm-card__icone" aria-hidden="true"></i>
                    <div>
                        <div class="adm-card__numero"><?= $pendentes ?></div>
                        <div class="adm-card__rotulo">
                            <?= $pendentes > 0 ? 'Aguardando ativação (clique para revisar)' : 'Aguardando ativação' ?>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- ===================================== Detalhes ======================================= -->
        <div class="row g-3 mb-4">

            <!-- Alunos por curso -->
            <div class="col-12 col-lg-7">
                <div class="shadow-sm p-3 bg-body-tertiary rounded h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-bar-chart-fill text-success fs-3" aria-hidden="true"></i>
                        <h2 class="h5 mb-0"><b>Alunos por curso</b></h2>
                    </div>

                    <?php if (!$porCurso): ?>
                        <p class="text-muted mb-0">Nenhum aluno cadastrado ainda.</p>
                    <?php else: ?>
                        <?php foreach ($porCurso as $linha): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-break"><?= h($linha->nome) ?></span>
                                    <b><?= (int) $linha->total ?></b>
                                </div>
                                <!-- Barra proporcional ao maior curso -->
                                <div class="adm-barra" role="img"
                                    aria-label="<?= h($linha->nome) ?>: <?= (int) $linha->total ?> alunos">
                                    <div class="adm-barra__preenchida"
                                        style="width: <?= round(((int) $linha->total / $maxCurso) * 100) ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Servidores por função + atalhos -->
            <div class="col-12 col-lg-5">
                <div class="shadow-sm p-3 bg-body-tertiary rounded mb-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-people-fill text-success fs-3" aria-hidden="true"></i>
                        <h2 class="h5 mb-0"><b>Servidores ativos por função</b></h2>
                    </div>

                    <?php if (!$porFuncao): ?>
                        <p class="text-muted mb-0">Nenhum servidor ativo.</p>
                    <?php else: ?>
                        <?php foreach ($porFuncao as $linha): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?= h($linha->nome) ?></span>
                                    <b><?= (int) $linha->total ?></b>
                                </div>
                                <div class="adm-barra" role="img"
                                    aria-label="<?= h($linha->nome) ?>: <?= (int) $linha->total ?>">
                                    <div class="adm-barra__preenchida"
                                        style="width: <?= round(((int) $linha->total / $maxFuncao) * 100) ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Atalhos -->
                <div class="d-grid gap-2">
                    <a href="<?= URL ?>/admin/perfis" class="btn btn-success fw-bold py-2">
                        <i class="bi bi-people-fill me-1" aria-hidden="true"></i> Gerenciar perfis
                    </a>
                    <a href="<?= URL ?>/admin/sistema" class="btn btn-outline-success fw-bold py-2">
                        <i class="bi bi-sliders me-1" aria-hidden="true"></i> Informações do sistema
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
<?php include '../App/Views/footer.php' ?>