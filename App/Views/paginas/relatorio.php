<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';

$filtros      = $dados['filtros'];
$atendimentos = $dados['atendimentos'] ?? [];

$h = function ($texto) {
    return htmlspecialchars(html_entity_decode((string) $texto, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
};
$dataBr = function ($iso) {
    $ts = strtotime((string) $iso);
    return $ts ? date('d/m/Y', $ts) : '';
};
$periodoTexto = $dataBr($filtros['inicio']) . ' a ' . $dataBr($filtros['fim']);

$cards = [
    ['bi-people-fill', 'Total de Consultas', $dados['total_consultas'], 'realizadas no período.'],
    ['bi-person-walking', 'Estudantes Atendidos', $dados['estudantes_atendidos'], 'durante o período.'],
    ['bi-card-list', 'Consultas Marcadas', $dados['consultas_marcadas'], 'agendadas no período.'],
];
?>
<style>
    .rel-impressao-topo { display: none; }
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        .navbar, .pular-conteudo, .offcanvas, .rel-filtros, .home-topo img, [vw], .vw-plugin-wrapper, footer { display: none !important; }
        .layout { margin: 0 !important; padding: 0 !important; }
        .rel-impressao-topo { display: block; margin-bottom: 8px; }
        .rel-card { box-shadow: none !important; border: 1px solid #ccc; }
        .table-responsive { overflow: visible !important; }
        .rel-tabela { font-size: 11px; }
    }
</style>

<div class="layout">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">
        <div class="row align-items-center">
            <header class="home-topo">
                <div class="home-titulo">
                    <h1>Relatório</h1>
                    <p>Relatório das ações realizadas no período.</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </header>
        </div>
    </div>

    <div class="container rel-impressao-topo">
        <b>Período:</b> <?= $h($periodoTexto) ?> &bull;
        <b>Tipo:</b> <?= $h($dados['tipos'][$filtros['tipo']]) ?> &bull;
        <b>Emitido em:</b> <?= date('d/m/Y H:i') ?>
    </div>

    <!-- ==================================== Filtros =================================================== -->
    <div class="container my-4 rel-filtros">
        <form method="get" action="<?= URL ?>/paginas/relatorio" class="row g-3 align-items-end">
            <div class="col-6 col-md-2">
                <label for="inicio" class="form-label fw-bold mb-1">De</label>
                <input type="date" id="inicio" name="inicio" class="form-control border-success-subtle py-2"
                    value="<?= $h($filtros['inicio']) ?>" required>
            </div>
            <div class="col-6 col-md-2">
                <label for="fim" class="form-label fw-bold mb-1">Até</label>
                <input type="date" id="fim" name="fim" class="form-control border-success-subtle py-2"
                    value="<?= $h($filtros['fim']) ?>" required>
            </div>

            <div class="col-12 col-sm-6 col-md-2">
                <label for="tipoRelatorio" class="form-label fw-bold mb-1">Tipo de relatório</label>
                <select id="tipoRelatorio" name="tipo" class="form-select border-success-subtle py-2">
                    <?php foreach ($dados['tipos'] as $valor => $rotulo): ?>
                        <option value="<?= $h($valor) ?>" <?= $filtros['tipo'] === $valor ? 'selected' : '' ?>><?= $h($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-2">
                <label for="turma" class="form-label fw-bold mb-1">Turma</label>
                <select id="turma" name="turma" class="form-select border-success-subtle py-2">
                    <option value="">Todas</option>
                    <?php foreach ($dados['turmas'] as $t): ?>
                        <option value="<?= (int) $t->turm_id ?>" <?= (int) $filtros['turma'] === (int) $t->turm_id ? 'selected' : '' ?>>
                            <?= $h($t->turm_curso . ' ' . $t->turm_ano . ' — ' . $t->turm_turno) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                    <i class="bi bi-funnel-fill me-1" aria-hidden="true"></i> Filtrar
                </button>
            </div>

            <div class="col-6 col-md-2">
                <button type="button" onclick="window.print()"
                    class="btn btn-light border border-secondary-subtle w-100 py-2 d-flex align-items-center justify-content-center">
                    <i class="bi bi-printer me-2 fs-5 text-success" aria-hidden="true"></i>
                    Imprimir
                </button>
            </div>
        </form>
    </div>

    <!-- ==================================== Cards =================================================== -->
    <div class="container text-center">
        <div class="row align-items-start">
            <?php foreach ($cards as [$icone, $titulo, $valor, $sub]): ?>
                <div class="col-12 mt-3 col-md-4">
                    <div class="p-3 mb-3 bg-body-tertiary rounded rel-card" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi <?= $icone ?> text-success fs-3" aria-hidden="true"></i>
                            <h5 class="mb-0"><b><?= $titulo ?></b></h5>
                        </div>
                        <h1 class="mt-3"><b><?= str_pad((string) (int) $valor, 2, '0', STR_PAD_LEFT) ?></b></h1>
                        <div><?= $sub ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ==================================== Tabela =================================================== -->
    <div class="container text-start">
        <div class="shadow-sm p-3 mb-5 mt-4 bg-body-tertiary rounded">
            <h2 class="h5 fw-bold mb-3">
                Atendimentos de <?= $h($periodoTexto) ?>
                <span class="badge text-bg-success ms-1"><?= count($atendimentos) ?></span>
            </h2>

            <div class="table-responsive">
                <table class="table table-hover align-middle rel-tabela">
                    <thead class="table-light">
                        <tr class="fw-bold">
                            <th scope="col" class="text-success">Data</th>
                            <th scope="col" class="text-success">Aluno</th>
                            <th scope="col" class="text-success">Matrícula</th>
                            <th scope="col" class="text-success">Turma</th>
                            <th scope="col" class="text-success">Tipo</th>
                            <th scope="col" class="text-success">Profissional</th>
                            <th scope="col" class="text-success">Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$atendimentos): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Nenhum atendimento encontrado neste período.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($atendimentos as $a): ?>
                            <tr>
                                <td class="text-nowrap">
                                    <?= $h($dataBr($a->data)) ?>
                                    <small class="text-muted"><?= !empty($a->hora) ? $h(substr($a->hora, 0, 5)) : '' ?></small>
                                </td>
                                <td class="fw-semibold">
                                    <?php if (!empty($a->alun_id)): ?>
                                        <a href="<?= URL ?>/alunos/visualizarPerfil/<?= (int) $a->alun_id ?>" class="text-success text-decoration-none">
                                            <?= $h($a->alun_nome) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fw-normal">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $h($a->alun_matricula ?? '') ?: '--' ?></td>
                                <td class="text-nowrap">
                                    <?= !empty($a->turm_curso) ? $h($a->turm_curso . ' ' . $a->turm_ano) : '--' ?>
                                </td>
                                <td><?= $h($a->tipo) ?></td>
                                <td><?= $h($a->profissional ?? '') ?: '--' ?></td>
                                <td><?= $h($a->descricao ?? '') ?: '--' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../App/Views/footer.php' ?>
