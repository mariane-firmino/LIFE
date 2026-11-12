<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

// Quando o botão Imprimir abre a página do documento (?imprimir=1), não há menu nem rodapé do site.
$modoPagina = !empty($dados['imprimir']);

if (!$modoPagina) {
    include '../App/Views/menu.php';
}

/** @var array $dados Enviado pelo controller (Paginas::relatorio) */

/*
 * Dados vindos do controller (Paginas::relatorio):
 *   periodos, periodo, tipos, tipo, turmas, turma,
 *   total_consultas, consultas_marcadas, total_estudantes, total_triagens,
 *   detalhes (lista do tipo escolhido), listas (todas as listas carregadas),
 *   limite_detalhes, gerado_em
 */

// Escapa texto antes de mostrar na tela (evita XSS).
$e = static fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

// Mostra o número com pelo menos 2 dígitos (ex.: 03, 12).
$num = static fn($valor) => sprintf('%02d', (int) $valor);

// Formata datas vindas do banco (Y-m-d ou Y-m-d H:i:s) para o padrão BR.
$dataBr = static function ($valor, bool $comHora = false): string {
    if (empty($valor)) {
        return '-';
    }
    $tempo = strtotime((string) $valor);
    return $tempo ? date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $tempo) : '-';
};

// Texto da turma: "Curso - 26 - Matutino".
$rotuloTurma = static fn($curso, $ano, $turno) => trim($curso . ' - ' . $ano . ' - ' . $turno);

$tipoAtual = $dados['tipo'];

// Textos dos filtros aplicados (usados no cabeçalho da impressão).
$rotuloPeriodoAtual = $dados['periodos'][$dados['periodo']] ?? '-';
$rotuloTipoAtual = $dados['tipos'][$tipoAtual] ?? '-';
$rotuloTurmaAtual = 'Todas';
foreach ($dados['turmas'] as $t) {
    if ((int) $t->turm_id === (int) $dados['turma']) {
        $rotuloTurmaAtual = $rotuloTurma($t->turm_curso, $t->turm_ano, $t->turm_turno);
        break;
    }
}

$emitidoPor = trim((string) ($_SESSION['usuario_nome'] ?? '')) ?: '—';
$consultasRealizadas = max(0, (int) $dados['total_consultas'] - (int) $dados['consultas_marcadas']);

// Linhas da tabela de relatórios (respeita o filtro "Tipo de relatório").
$relatorios = [
    'consultas' => [
        'nome' => 'Consultas',
        'total' => (int) $dados['total_consultas'],
    ],
    'estudantes' => [
        'nome' => 'Estudantes atendidos',
        'total' => (int) $dados['total_estudantes'],
    ],
    'triagens' => [
        'nome' => 'Triagens realizadas',
        'total' => (int) $dados['total_triagens'],
    ],
];
if ($tipoAtual !== 'todos') {
    $relatorios = array_intersect_key($relatorios, [$tipoAtual => true]);
}

// Link "Ver detalhes" mantendo período e turma.
$linkDetalhes = static function (string $tipo) use ($dados): string {
    return URL . '/paginas/relatorio?' . http_build_query([
        'periodo' => $dados['periodo'],
        'tipo' => $tipo,
        'turma' => $dados['turma'],
    ]);
};

// Link do botão "Imprimir relatório" (mesmos filtros, só o documento).
$linkImpressao = URL . '/paginas/relatorio?' . http_build_query([
    'periodo' => $dados['periodo'],
    'tipo' => $dados['tipo'],
    'turma' => $dados['turma'],
    'imprimir' => 1,
]);

/*
 * Desenha a tabela de detalhes de um tipo de relatório.
 * É usada na tela e na impressão, para os dois mostrarem os mesmos dados.
 *   $impressao = true  -> tabela com o visual do papel (classe tabela-impressao)
 *   $impressao = false -> tabela com o visual do Bootstrap (tela)
 */
$tabelaDetalhes = static function (string $tipo, array $linhas, bool $impressao) use ($e, $dataBr, $rotuloTurma): void {
    $colunas = [
        'consultas' => ['Data', 'Horário', 'Aluno', 'Tipo', 'Responsável', 'Descrição', 'Situação'],
        'estudantes' => ['Estudante', 'Matrícula', 'Turma', 'Atendimentos', 'Último atendimento'],
        'triagens' => ['Data', 'Estudante', 'Turma', 'Tipo', 'Atendido por'],
    ];
    // Colunas numéricas ficam centralizadas.
    $centro = [
        'consultas' => ['Situação'],
        'estudantes' => ['Atendimentos'],
        'triagens' => [],
    ];

    $classeTabela = $impressao ? 'tabela-impressao' : 'table table-hover align-middle';
    $classeThead = $impressao ? '' : 'table-light';
    $classeTh = $impressao ? '' : 'text-success';

    echo '<table class="' . $classeTabela . '"><thead class="' . $classeThead . '"><tr>';
    foreach ($colunas[$tipo] as $titulo) {
        $alinha = in_array($titulo, $centro[$tipo], true) ? ' text-center' : '';
        echo '<th scope="col" class="' . trim($classeTh . $alinha) . '">' . $e($titulo) . '</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($linhas as $item) {
        echo '<tr>';
        if ($tipo === 'consultas') {
            $situacao = (int) $item->marcada === 1 ? 'Marcada' : 'Realizada';
            echo '<td>' . $e($dataBr($item->cons_data)) . '</td>';
            echo '<td>' . $e(substr((string) $item->cons_horario, 0, 5)) . '</td>';
            echo '<td>' . $e($item->alun_nome ?: '-') . '</td>';
            echo '<td>' . $e($item->cons_tipo) . '</td>';
            echo '<td>' . $e($item->cons_responsavel ?: '-') . '</td>';
            echo '<td>' . $e($item->cons_descricao ?: '-') . '</td>';
            echo '<td class="text-center">' . $e($situacao) . '</td>';
        } elseif ($tipo === 'estudantes') {
            echo '<td>' . $e($item->alun_nome) . '</td>';
            echo '<td>' . $e($item->alun_matricula) . '</td>';
            echo '<td>' . $e($rotuloTurma($item->turm_curso, $item->turm_ano, $item->turm_turno)) . '</td>';
            echo '<td class="text-center">' . (int) $item->atendimentos . '</td>';
            echo '<td>' . $e($dataBr($item->ultimo_atendimento, true)) . '</td>';
        } else {
            echo '<td>' . $e($dataBr($item->tria_data, true)) . '</td>';
            echo '<td>' . $e($item->alun_nome) . '</td>';
            echo '<td>' . $e($rotuloTurma($item->turm_curso, $item->turm_ano, $item->turm_turno)) . '</td>';
            // Triagem completa mostra quantas perguntas foram respondidas.
            $tipoTriagem = $item->tipo . ($item->respostas !== null ? ' (' . (int) $item->respostas . ' perguntas)' : '');
            echo '<td>' . $e($tipoTriagem) . '</td>';
            echo '<td>' . $e($item->serv_nome ?: 'Autor removido') . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
};

// Títulos das seções do relatório impresso.
$tituloSecao = [
    'consultas' => 'Relação de consultas',
    'estudantes' => 'Relação de estudantes atendidos',
    'triagens' => 'Relação de triagens',
];

// Texto do rodapé de cada página impressa (CSS não aceita aspas nem barras dentro do texto).
$rodapeImpressao = preg_replace('/["\\\\\r\n<>]+/', ' ', 'LIFE · Emitido em ' . $dados['gerado_em'] . ' por ' . $emitidoPor);
?>
<?php
// O número no final do link muda quando o CSS é editado, assim o navegador
// não usa uma cópia antiga guardada em cache.
$versaoCss = @filemtime(__DIR__ . '/../../../Public/css/paginas/relatorio.css') ?: time();
?>
<link rel="stylesheet" href="<?= URL ?>/css/paginas/relatorio.css?v=<?= $versaoCss ?>">

<!-- Garantia: mesmo que o CSS acima não carregue, o documento de impressão
     nunca aparece na tela e sempre aparece no papel. -->
<style>
    .relatorio-impressao:not(.modo-pagina) { display: none; }
    @media print {
        .relatorio-impressao:not(.modo-pagina) { display: block; }
        .relatorio-tela { display: none !important; }
    }
</style>

<!-- Rodapé e numeração de página da impressão (o texto depende de quem imprime) -->
<style>
    @media print {
        @page {
            @bottom-left {
                content: "<?= $rodapeImpressao ?>";
                font-size: 9pt;
                color: #666;
            }
            @bottom-right {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 9pt;
                color: #666;
            }
        }
    }
</style>

<div class="<?= $modoPagina ? 'relatorio-pagina' : 'layout' ?>">

    <?php if ($modoPagina): ?>
        <!-- Barra de ações da página do documento (não sai no papel) -->
        <div class="relatorio-barra">
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.close()">Fechar</button>
        </div>
        <!-- Esconde o botão do VLibras nesta página -->
        <style>
            [vw], [vw-access-button], [vw-plugin-wrapper] { display: none !important; }
        </style>
    <?php endif; ?>

    <!-- ===================================================================================================== -->
    <!-- VERSÃO DA TELA (não sai na impressão)                                                                 -->
    <!-- ===================================================================================================== -->
    <?php if (!$modoPagina): ?>
    <div class="relatorio-tela">

        <!-- CABEÇALHO -->
        <div class="container-fluid px-4 pt-5 pb-3">
            <div class="row align-items-center">
                <!-- CABEÇALHO -->
                <header class="home-topo">
                    <div class="home-titulo">
                        <h1>Relatório</h1>
                        <p>Relatório das ações realizadas durante o mês.</p>
                    </div>

                    <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
                </header>
            </div>
        </div>

        <!-- ==================================== Primeira Linha =================================================== -->
        <div class="container my-4">
            <form method="get" action="<?= URL ?>/paginas/relatorio" id="formRelatorio">
                <div class="row g-3 align-items-end">
                    <!-- Campo Período -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="periodo" class="form-label fw-bold mb-1">Período</label>
                        <select id="periodo" name="periodo" class="form-select border-success-subtle py-2"
                            onchange="this.form.submit()">
                            <?php foreach ($dados['periodos'] as $valor => $rotulo): ?>
                                <option value="<?= $e($valor) ?>" <?= $valor === $dados['periodo'] ? 'selected' : '' ?>>
                                    <?= $e($rotulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo Tipo de relatório -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="tipoRelatorio" class="form-label fw-bold mb-1">Tipo de relatório</label>
                        <select id="tipoRelatorio" name="tipo" class="form-select border-success-subtle py-2"
                            onchange="this.form.submit()">
                            <?php foreach ($dados['tipos'] as $valor => $rotulo): ?>
                                <option value="<?= $e($valor) ?>" <?= $valor === $tipoAtual ? 'selected' : '' ?>>
                                    <?= $e($rotulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo Turma -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="turma" class="form-label fw-bold mb-1">Turma</label>
                        <select id="turma" name="turma" class="form-select border-success-subtle py-2"
                            onchange="this.form.submit()">
                            <option value="0" <?= (int) $dados['turma'] === 0 ? 'selected' : '' ?>>Todas</option>
                            <?php foreach ($dados['turmas'] as $turma): ?>
                                <option value="<?= (int) $turma->turm_id ?>"
                                    <?= (int) $turma->turm_id === (int) $dados['turma'] ? 'selected' : '' ?>>
                                    <?= $e($rotuloTurma($turma->turm_curso, $turma->turm_ano, $turma->turm_turno)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Botão Imprimir relatório: abre o documento padronizado em outra aba -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <a href="<?= $e($linkImpressao) ?>" target="_blank" rel="noopener"
                            class="btn btn-light border border-secondary-subtle w-100 py-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-printer me-2 fs-5 text-success"></i>
                            Imprimir relatório
                        </a>
                    </div>
                </div>

                <!-- Se o JavaScript estiver desligado, o botão abaixo aplica os filtros -->
                <noscript>
                    <button type="submit" class="btn btn-success mt-3">Aplicar filtros</button>
                </noscript>
            </form>

            <?php if ((int) $dados['turma'] > 0): ?>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle"></i>
                    A turma filtra os estudantes atendidos e as triagens. As consultas não são
                    vinculadas a alunos no banco de dados, por isso os números de consultas
                    mostram todas as turmas.
                </p>
            <?php endif; ?>
        </div>
        <!-- ================================ FIM Primeira Linha =================================================== -->

        <!-- ==================================== Segunda Linha =================================================== -->
        <div class="container text-center">
            <div class="row align-items-start">
                <div class="col-12 mt-3 col-sm-12 col-md-4">
                    <div class="p-3 mb-3 bg-body-tertiary rounded" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);">
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill text-success fs-3"></i>
                                <h5><b>Total de Consultas</b></h5>
                            </div>
                        </div>
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            <h1><b><?= $num($dados['total_consultas']) ?></b></h1>
                        </div>
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            durante o período.
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3 col-sm-12 col-md-4">
                    <div class="p-3 mb-3 bg-body-tertiary rounded" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);">
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-walking text-success fs-3"></i>
                                <h5><b>Estudantes Atendidos</b></h5>
                            </div>
                        </div>
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            <h1><b><?= $num($dados['total_estudantes']) ?></b></h1>
                        </div>
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            durante o período.
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3 col-sm-12 col-md-4">
                    <div class="p-3 mb-3 bg-body-tertiary rounded" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);">
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-card-list text-success fs-3"></i>
                                <h5><b>Consultas Marcadas</b></h5>
                            </div>
                        </div>
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            <h1><b><?= $num($dados['consultas_marcadas']) ?></b></h1>
                        </div>
                        <div class="col-12 mt-3 col-sm-12 col-md-12">
                            ainda por acontecer no período.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ================================ FIM Segunda Linha =================================================== -->

        <!-- ==================================== Terceira Linha =================================================== -->
        <div class="container text-start">
            <div class="row align-items-start">
                <div class="shadow-sm p-3 mb-5 mt-5 bg-body-tertiary rounded">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr class="fw-bold">
                                    <th scope="col" class="text-success">Nome do Relatório</th>
                                    <th scope="col" class="text-success">Descrição</th>
                                    <th scope="col" class="text-success text-center">Registros</th>
                                    <th scope="col" class="text-success">Gerado em</th>
                                    <th scope="col" class="text-success"><span class="visually-hidden">Ações</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relatorios as $chave => $linha): ?>
                                    <tr>
                                        <td><?= $e($linha['nome']) ?></td>
                                        <td><?= $e(Relatorio::DESCRICOES[$chave]) ?></td>
                                        <td class="text-center"><?= $num($linha['total']) ?></td>
                                        <td class="text-muted"><?= $e($dados['gerado_em']) ?></td>
                                        <td class="text-end">
                                            <?php if ($tipoAtual !== $chave): ?>
                                                <a href="<?= $e($linkDetalhes($chave)) ?>"
                                                    class="btn btn-sm btn-outline-success">Ver detalhes</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- ================================ FIM Terceira Linha =================================================== -->

        <!-- ==================================== Detalhes do relatório escolhido ================================== -->
        <?php if ($tipoAtual !== 'todos'): ?>
            <div class="container text-start">
                <div class="row align-items-start">
                    <div class="shadow-sm p-3 mb-5 bg-body-tertiary rounded">
                        <h5 class="text-success fw-bold mb-3"><?= $e($rotuloTipoAtual) ?></h5>

                        <?php if (empty($dados['detalhes'])): ?>
                            <p class="text-muted mb-0">Nenhum registro encontrado para os filtros escolhidos.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <?php $tabelaDetalhes($tipoAtual, $dados['detalhes'], false); ?>
                            </div>

                            <?php if (count($dados['detalhes']) >= (int) $dados['limite_detalhes']): ?>
                                <p class="text-muted small mb-0">
                                    Mostrando os <?= (int) $dados['limite_detalhes'] ?> registros mais recentes.
                                    Escolha um período menor para ver os demais.
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- ================================ FIM Detalhes ========================================================= -->

    </div>
    <!-- ================================ FIM versão da tela =================================================== -->
    <?php endif; ?>


    <!-- ===================================================================================================== -->
    <!-- VERSÃO IMPRESSA (só aparece ao imprimir)                                                              -->
    <!-- ===================================================================================================== -->
    <div class="relatorio-impressao<?= $modoPagina ? ' modo-pagina' : '' ?>">

        <!-- Cabeçalho -->
        <div class="imp-cabecalho">
            <img src="<?= URL ?>/img/life-menor.png" alt="Logo LIFE" class="imp-logo">
            <div class="imp-instituicao">
                <strong>Instituto Federal de Rondônia</strong>
                <span>LIFE</span>
            </div>
            <div class="imp-emissao">
                <span>Relatório de atendimentos</span>
                <strong><?= $e($dados['gerado_em']) ?></strong>
            </div>
        </div>

        <!-- Título -->
        <h1 class="imp-titulo">Relatório de atendimentos</h1>
        <p class="imp-subtitulo"><?= $tipoAtual === 'todos' ? 'Todos os relatórios' : $e($rotuloTipoAtual) ?></p>

        <!-- Filtros aplicados -->
        <table class="imp-info">
            <tr>
                <th>Período</th>
                <td><?= $e($rotuloPeriodoAtual) ?></td>
                <th>Emitido em</th>
                <td><?= $e($dados['gerado_em']) ?></td>
            </tr>
            <tr>
                <th>Tipo</th>
                <td><?= $e($rotuloTipoAtual) ?></td>
                <th>Turma</th>
                <td><?= $e($rotuloTurmaAtual) ?></td>
            </tr>
            <tr>
                <th>Emitido por</th>
                <td colspan="3"><?= $e($emitidoPor) ?></td>
            </tr>
        </table>

        <!-- 1. Resumo -->
        <h2 class="imp-secao">1. Resumo</h2>
        <table class="imp-resumo">
            <tr>
                <td>Consultas no período</td>
                <td class="valor"><?= (int) $dados['total_consultas'] ?></td>
            </tr>
            <tr class="sub">
                <td>Realizadas</td>
                <td class="valor"><?= $consultasRealizadas ?></td>
            </tr>
            <tr class="sub">
                <td>Marcadas (ainda por acontecer)</td>
                <td class="valor"><?= (int) $dados['consultas_marcadas'] ?></td>
            </tr>
            <tr>
                <td>Estudantes atendidos</td>
                <td class="valor"><?= (int) $dados['total_estudantes'] ?></td>
            </tr>
            <tr>
                <td>Triagens realizadas</td>
                <td class="valor"><?= (int) $dados['total_triagens'] ?></td>
            </tr>
        </table>
        <?php if ((int) $dados['turma'] > 0): ?>
            <p class="imp-nota">
                A turma filtra os estudantes atendidos e as triagens. As consultas não são
                vinculadas a alunos no banco de dados, por isso os números de consultas
                incluem todas as turmas.
            </p>
        <?php endif; ?>

        <!-- 2, 3, 4... Relações -->
        <?php $numeroSecao = 2; ?>
        <?php foreach ($dados['listas'] as $tipoLista => $linhasLista): ?>
            <h2 class="imp-secao"><?= $numeroSecao++ ?>. <?= $e($tituloSecao[$tipoLista]) ?></h2>
            <p class="imp-legenda">
                Ordenados do mais recente para o mais antigo. Os dados refletem os registros do
                sistema no momento da emissão do relatório.
            </p>

            <?php if (empty($linhasLista)): ?>
                <p class="imp-vazio">Nenhum registro encontrado para os filtros escolhidos.</p>
            <?php else: ?>
                <?php $tabelaDetalhes($tipoLista, $linhasLista, true); ?>
                <?php if (count($linhasLista) >= (int) $dados['limite_detalhes']): ?>
                    <p class="imp-nota">
                        Mostrando os <?= (int) $dados['limite_detalhes'] ?> registros mais recentes.
                        Escolha um período menor para ver os demais.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Assinaturas -->
        <div class="imp-assinaturas">
            <div class="imp-assinatura">
                <div class="linha"></div>
                <div class="nome"><?= $e($emitidoPor) ?></div>
                <div class="legenda">Responsável pela emissão</div>
            </div>
            <div class="imp-assinatura">
                <div class="linha"></div>
                <div class="nome">Ciência da Coordenação</div>
                <div class="legenda">Data: ____ / ____ / ________</div>
            </div>
        </div>

        <p class="imp-rodape-doc">
            Documento gerado eletronicamente pelo LIFE em <?= $e($dados['gerado_em']) ?>.
            Reflete os registros do sistema na data e hora de emissão.
        </p>
    </div>
    <!-- ================================ FIM versão impressa ================================================== -->

</div>
<?php if ($modoPagina): ?>
    <!-- Abre a janela de impressão assim que a página (e o logo) carregarem -->
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
<?php else: ?>
    <?php include '../App/Views/footer.php' ?>
<?php endif; ?>