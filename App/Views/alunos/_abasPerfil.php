<?php
// =====================================================================
// PARTIAL: ABAS DO PERFIL DO ALUNO
// Arquivo: App/Views/alunos/_abasPerfil.php
//
// É incluído pela view visualizarPerfil.php (usa as mesmas variáveis
// e as funções vp(), vpSimQual() definidas lá).
//
// Abas: Histórico de saúde, Triagens, Triagens rápidas, Cartão de vacina, Cartão do SUS,
// Laudos médicos, Informações psicológicas, Anotações rápidas,
// Informações extras, Educação formal, Hábitos de vida e
// Histórico de visitas.
// =====================================================================

// ---------------------------------------------------------------------
// DADOS RECEBIDOS DO CONTROLLER
// ---------------------------------------------------------------------
$complemento  = $dados['complemento'] ?? null;
$docs         = $dados['docs'] ?? ['vacina' => [], 'sus' => [], 'laudo' => []];
$notasRapidas = $dados['notas_rapidas'] ?? [];
$notasPsico   = $dados['notas_psico'] ?? [];
$rapidas      = $dados['triagens_rapidas'] ?? [];
$triagens     = $dados['triagens'] ?? [];
$podePsico    = !empty($dados['pode_psico']);
$usuarioId    = (int) ($_SESSION['usuario_id'] ?? 0);
$alunoId      = (int) $aluno->id;

// Link da edição geral do perfil (o "#aba-..." serve para abrir a aba certa
// quando a tela de edição também usar abas).
$urlEditar = URL . '/alunos/editarPerfil/' . $alunoId;


// ---------------------------------------------------------------------
// FUNÇÕES AUXILIARES DESTE BLOCO
// ---------------------------------------------------------------------

// Título do painel + botão de ação à direita ($acaoHtml já vem pronto).
if (!function_exists('paTituloPainel')) {
    function paTituloPainel($titulo, $icone, $acaoHtml = '')
    { ?>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi <?= $icone ?> text-success fs-3" aria-hidden="true"></i>
                <h2 class="h5 mb-0"><b><?= $titulo ?></b></h2>
            </div>
            <?= $acaoHtml ?>
        </div>
    <?php }
}

// Botão "Editar" que leva à tela de edição do perfil.
if (!function_exists('paBotaoEditar')) {
    function paBotaoEditar($url, $aba)
    {
        return '<a href="' . htmlspecialchars($url . '#' . $aba) . '" class="btn btn-outline-success btn-sm fw-bold">'
            . '<i class="bi bi-pencil-fill me-1" aria-hidden="true"></i> Editar</a>';
    }
}

// Lista de pares [rótulo, valorHtml, (largura total?)] em duas colunas.
if (!function_exists('paLinhas')) {
    function paLinhas(array $pares)
    {
        echo '<div class="row">';
        foreach ($pares as $par) {
            $largo = $par[2] ?? false;
            echo '<div class="col-12' . ($largo ? '' : ' col-sm-6') . '">'
                . '<small>' . $par[0] . '</small>'
                . '<p class="text-break"><b>' . $par[1] . '</b></p></div>';
        }
        echo '</div>';
    }
}

// Formulário de envio de arquivo.
// $opc: titulo (mostra campo), titulo_obrigatorio, data (mostra campo),
//       accept (tipos aceitos), botao (texto), ajuda (texto pequeno).
if (!function_exists('paFormEnvio')) {
    function paFormEnvio($alunoId, $tipo, array $opc)
    { ?>
        <form class="row g-2 bg-body rounded border p-3 mb-3" method="post" enctype="multipart/form-data"
            action="<?= URL ?>/anexos/enviarDocumento/<?= (int) $alunoId ?>/<?= $tipo ?>">

            <?php if (!empty($opc['titulo'])): ?>
                <div class="col-md-6">
                    <label class="form-label small mb-1">
                        Título <?= !empty($opc['titulo_obrigatorio']) ? '<span class="text-danger">*</span>' : '' ?>
                    </label>
                    <input type="text" name="titulo" maxlength="150" class="form-control form-control-sm"
                        <?= !empty($opc['titulo_obrigatorio']) ? 'required' : '' ?>>
                </div>
            <?php endif; ?>

            <?php if (!empty($opc['data'])): ?>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Data do documento</label>
                    <input type="date" name="data_documento" max="<?= date('Y-m-d') ?>" class="form-control form-control-sm">
                </div>
            <?php endif; ?>

            <div class="col-12">
                <label class="form-label small mb-1">Descrição (opcional)</label>
                <input type="text" name="descricao" maxlength="500" class="form-control form-control-sm"
                    placeholder="<?= htmlspecialchars($opc['placeholder'] ?? '') ?>">
            </div>

            <div class="col-md-8">
                <label class="form-label small mb-1">Arquivo <span class="text-danger">*</span></label>
                <input type="file" name="arquivo" required accept="<?= $opc['accept'] ?>" class="form-control form-control-sm">
                <div class="form-text"><?= $opc['ajuda'] ?></div>
            </div>

            <div class="col-md-4 d-flex align-items-start">
                <button type="submit" class="btn btn-success btn-sm fw-bold mt-md-4 w-100">
                    <i class="bi bi-upload me-1" aria-hidden="true"></i> <?= $opc['botao'] ?>
                </button>
            </div>
        </form>
    <?php }
}

// Galeria de miniaturas (vacina e SUS).
if (!function_exists('paGaleria')) {
    function paGaleria(array $lista, $rotulo)
    {
        if (!$lista) {
            echo '<p class="text-muted mb-0">Nenhuma foto enviada ainda.</p>';
            return;
        } ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php foreach ($lista as $d): $url = URL . '/anexos/arquivo/' . (int) $d->id; ?>
                <div class="col">
                    <div class="pa-miniatura">
                        <a href="<?= $url ?>" target="_blank" rel="noopener">
                            <img src="<?= $url ?>" loading="lazy" alt="<?= htmlspecialchars($rotulo) ?>">
                        </a>
                        <div class="pa-miniatura__legenda">
                            <div class="small text-break"><?= vp($d->descricao, 'Sem descrição') ?></div>
                            <div class="small text-muted">Enviada em <?= date('d/m/Y', strtotime($d->criado_em)) ?></div>
                            <form method="post" action="<?= URL ?>/anexos/excluirDocumento/<?= (int) $d->id ?>"
                                onsubmit="return confirm('Excluir esta foto?');">
                                <button type="submit" class="btn btn-sm btn-outline-danger mt-1">
                                    <i class="bi bi-trash-fill" aria-hidden="true"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php }
}

// Painel de anotações (usado em "Anotações rápidas" e "Informações psicológicas").
if (!function_exists('paNotas')) {
    function paNotas(array $notas, $alunoId, $tipo, $usuarioId, $placeholder, $classeExtra = '')
    { ?>
        <!-- Nova anotação -->
        <form method="post" action="<?= URL ?>/anexos/adicionarAnotacao/<?= (int) $alunoId ?>/<?= $tipo ?>" class="mb-4">
            <label class="form-label small mb-1">Nova anotação</label>
            <textarea name="texto" rows="3" maxlength="1000" required class="form-control mb-2"
                placeholder="<?= htmlspecialchars($placeholder) ?>"></textarea>
            <button type="submit" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Adicionar anotação
            </button>
        </form>

        <!-- Lista (mais recentes primeiro) -->
        <?php if (!$notas): ?>
            <p class="text-muted mb-0">Nenhuma anotação registrada.</p>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($notas as $n): ?>
                    <div class="pa-nota <?= $classeExtra ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <small class="text-muted">
                                <b><?= vp($n->serv_nome, 'Autor removido') ?></b>
                                <?= !empty($n->func_nome) ? ' (' . vp($n->func_nome) . ')' : '' ?>
                                &bull; <?= date('d/m/Y H:i', strtotime($n->criado_em)) ?>
                            </small>

                            <?php if ((int) $n->serv_id === (int) $usuarioId): ?>
                                <!-- Só o autor vê o botão de excluir -->
                                <form method="post" action="<?= URL ?>/anexos/excluirAnotacao/<?= (int) $n->id ?>"
                                    onsubmit="return confirm('Excluir esta anotação?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Excluir anotação">
                                        <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="text-break"><?= nl2br(htmlspecialchars($n->texto)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php }
}


// ---------------------------------------------------------------------
// LISTA DAS ABAS (id, título, ícone)
// ---------------------------------------------------------------------
$abasPerfil = [
    ['saude',    'Histórico de saúde',        'bi-heart-pulse-fill'],
    ['triagens', 'Triagens',                  'bi-clipboard2-pulse-fill'],
    ['rapidas',  'Triagens rápidas',          'bi-lightning-charge-fill'],
    ['vacina',   'Cartão de vacina',          'bi-shield-plus'],
    ['sus',      'Cartão do SUS',             'bi-credit-card-2-front-fill'],
    ['laudos',   'Laudos médicos',            'bi-file-earmark-medical-fill'],
    ['psico',    'Informações psicológicas',  $podePsico ? 'bi-chat-heart-fill' : 'bi-lock-fill'],
    ['notas',    'Anotações rápidas',         'bi-sticky-fill'],
    ['extras',   'Informações extras',        'bi-info-circle-fill'],
    ['educacao', 'Educação formal',           'bi-mortarboard-fill'],
    ['habitos',  'Hábitos de vida',           'bi-activity'],
    ['visitas',  'Histórico de visitas',      'bi-clipboard-pulse'],
];

// Número do SUS formatado (000 0000 0000 0000) quando tem 15 dígitos.
$susDigitos = preg_replace('/\D/', '', (string) ($complemento->cartao_sus ?? ''));
$susTexto = strlen($susDigitos) === 15
    ? substr($susDigitos, 0, 3) . ' ' . substr($susDigitos, 3, 4) . ' ' . substr($susDigitos, 7, 4) . ' ' . substr($susDigitos, 11, 4)
    : $susDigitos;

$infoExtras = trim((string) ($complemento->info_extras ?? ''));
?>

<!-- ===================================== ABAS DO PERFIL ======================================= -->
<div class="container text-start mb-5" id="areaAbasPerfil">

    <!-- Mensagens das ações das abas (envio, exclusão, anotações...) -->
    <?php Sessao::mensagem('anexos'); ?>

    <ul class="nav nav-tabs" id="abasPerfil" role="tablist">
        <?php foreach ($abasPerfil as $i => [$aid, $titulo, $icone]): ?>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                    id="perfil-<?= $aid ?>-tab"
                    data-bs-toggle="tab" data-bs-target="#aba-<?= $aid ?>"
                    role="tab" aria-controls="aba-<?= $aid ?>"
                    aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                    <i class="bi <?= $icone ?> me-1" aria-hidden="true"></i><?= $titulo ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content pa-abas-conteudo p-3 p-lg-4">

        <!-- ================== HISTÓRICO DE SAÚDE ================== -->
        <div class="tab-pane show active" id="aba-saude" role="tabpanel" aria-labelledby="perfil-saude-tab" tabindex="0">
            <?php paTituloPainel('Histórico de Saúde', 'bi-heart-pulse-fill', paBotaoEditar($urlEditar, 'aba-saude')); ?>

            <?php if (!$saude): ?>
                <p class="text-muted mb-0">Este aluno ainda não possui ficha de saúde registrada.</p>
            <?php else:
                paLinhas([
                    ['Condição especial', vp(implode(', ', $condicoes), 'Nenhuma informada')],
                    ['Acesso ao serviço de saúde', vp($saude->acesso_saude)],
                    ['Alergia / restrição alimentar', vpSimQual($saude->alergia_alimentar, $saude->alergia_qual)],
                    ['Doença crônica', vpSimQual($saude->doenca_cronica, $saude->doenca_qual)],
                    ['Uso contínuo de medicação', vpSimQual($saude->medicacao_continua, $saude->medicacao_qual)],
                    ['Acompanhamento psicológico/psiquiátrico', vp($saude->acompanhamento_psi)],
                    ['Cartão de vacina', vp($saude->cartao_vacina)],
                    ['Já foi internado(a)', vpSimQual($saude->ja_internado, $saude->internado_motivo)],
                    ['Já fez cirurgia', vpSimQual($saude->ja_cirurgia, $saude->cirurgia_qual)],
                ]);
            endif; ?>
        </div>

        <!-- ================== TRIAGENS (respostas separadas em psicológica e física) ================== -->
        <div class="tab-pane" id="aba-triagens" role="tabpanel" aria-labelledby="perfil-triagens-tab" tabindex="0">
            <?php paTituloPainel(
                'Triagens',
                'bi-clipboard2-pulse-fill',
                '<div class="d-flex flex-wrap gap-2">'
                . '<a href="' . URL . '/triagens/psicologica/' . $alunoId . '" class="btn btn-success btn-sm fw-bold">'
                . '<i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Psicológica</a>'
                . '<a href="' . URL . '/triagens/fisica/' . $alunoId . '" class="btn btn-success btn-sm fw-bold">'
                . '<i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Física</a>'
                . '</div>'
            ); ?>

            <?php if (!$triagens): ?>
                <p class="text-muted mb-0">Nenhuma triagem registrada.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-4">
                    <?php foreach ($triagens as $tri): ?>
                        <div class="border rounded bg-body p-3">
                            <small class="text-muted d-block mb-3">
                                <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>
                                <?= date('d/m/Y H:i', strtotime($tri['data'])) ?>
                                &bull; <b><?= vp($tri['serv_nome'], 'Autor removido') ?></b>
                                <?= !empty($tri['func_nome']) ? ' (' . vp($tri['func_nome']) . ')' : '' ?>
                            </small>

                            <div class="row g-3">
                                <?php
                                // Mostra só as partes respondidas nesta triagem (agora cada
                                // parte é salva em uma página própria; triagens antigas têm as duas).
                                $partes = array_filter([
                                    'psicologica' => ['Triagem psicológica', 'bi-chat-heart-fill', 'pa-nota--psico'],
                                    'fisica'      => ['Triagem física', 'bi-heart-pulse-fill', ''],
                                ], function ($chaveParte) use ($tri) {
                                    return !empty($tri[$chaveParte]);
                                }, ARRAY_FILTER_USE_KEY);
                                $colunaParte = count($partes) > 1 ? 'col-12 col-lg-6' : 'col-12';

                                foreach ($partes as $chaveParte => [$tituloParte, $iconeParte, $classeParte]): ?>
                                    <div class="<?= $colunaParte ?>">
                                        <div class="pa-nota <?= $classeParte ?> h-100">
                                            <h3 class="h6 fw-bold mb-3">
                                                <i class="bi <?= $iconeParte ?> me-1" aria-hidden="true"></i> <?= $tituloParte ?>
                                            </h3>
                                            <?php foreach ($tri[$chaveParte] as $r): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted"><?= vp($r->pergunta) ?></small>
                                                    <div class="text-break"><b><?= vp($r->resposta) ?></b></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================== TRIAGENS RÁPIDAS ================== -->
        <div class="tab-pane" id="aba-rapidas" role="tabpanel" aria-labelledby="perfil-rapidas-tab" tabindex="0">
            <?php paTituloPainel(
                'Triagens Rápidas',
                'bi-lightning-charge-fill',
                '<a href="' . URL . '/triagens/rapida/' . $alunoId . '" class="btn btn-success btn-sm fw-bold">'
                . '<i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nova triagem rápida</a>'
            ); ?>

            <?php if (!$rapidas): ?>
                <p class="text-muted mb-0">Nenhuma triagem rápida registrada.</p>
            <?php else:
                // Cor do selo de gravidade.
                $corGravidade = ['Leve' => 'text-bg-success', 'Média' => 'text-bg-warning', 'Grave' => 'text-bg-danger'];
                ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($rapidas as $t): ?>
                        <div class="pa-nota pa-rapida pa-rapida--<?= $t->gravidade === 'Grave' ? 'grave' : ($t->gravidade === 'Média' ? 'media' : 'leve') ?>">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <b class="fs-6"><?= vp($t->queixa) ?></b>
                                    <span class="badge <?= $corGravidade[$t->gravidade] ?? 'text-bg-secondary' ?>"><?= vp($t->gravidade) ?></span>
                                </div>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($t->criado_em)) ?>
                                    &bull; <b><?= vp($t->serv_nome, 'Autor removido') ?></b>
                                    <?= !empty($t->func_nome) ? ' (' . vp($t->func_nome) . ')' : '' ?>
                                </small>
                            </div>

                            <?php
                            $blocos = [
                                ['Informações do aluno', $t->relato_aluno],
                                ['Informações da enfermeira', $t->observacao],
                                ['Como lidou', $t->conduta],
                            ];
                            foreach ($blocos as [$rotulo, $texto]):
                                if (trim((string) $texto) === '') continue; ?>
                                <div class="mb-2">
                                    <small class="text-muted"><?= $rotulo ?></small>
                                    <div class="text-break"><?= nl2br(htmlspecialchars($texto)) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================== CARTÃO DE VACINA ================== -->
        <div class="tab-pane" id="aba-vacina" role="tabpanel" aria-labelledby="perfil-vacina-tab" tabindex="0">
            <?php paTituloPainel('Cartão de Vacina', 'bi-shield-plus'); ?>

            <p class="mb-3">
                <small class="text-muted">Informado na ficha:</small>
                <b><?= vp($saude->cartao_vacina ?? '') ?></b>
            </p>

            <?php paFormEnvio($alunoId, 'vacina', [
                'accept' => 'image/jpeg,image/png,image/webp',
                'ajuda' => 'JPG, PNG ou WEBP, até 5 MB.',
                'botao' => 'Adicionar foto',
                'placeholder' => 'Ex.: frente do cartão',
            ]); ?>

            <?php paGaleria($docs['vacina'], 'Foto do cartão de vacina'); ?>
        </div>

        <!-- ================== CARTÃO DO SUS ================== -->
        <div class="tab-pane" id="aba-sus" role="tabpanel" aria-labelledby="perfil-sus-tab" tabindex="0">
            <?php paTituloPainel('Cartão do SUS', 'bi-credit-card-2-front-fill'); ?>

            <!-- Número do cartão (CNS: 15 dígitos) -->
            <form method="post" action="<?= URL ?>/anexos/salvarSus/<?= $alunoId ?>" class="mb-4">
                <label for="inputSus" class="form-label small mb-1">Número do cartão do SUS</label>

                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <input type="text" id="inputSus" name="cartao_sus" inputmode="numeric" maxlength="18"
                            class="form-control" placeholder="000 0000 0000 0000"
                            value="<?= htmlspecialchars($susTexto) ?>">
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-success fw-bold w-100 px-4">
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Salvar número
                        </button>
                    </div>
                </div>

                <div class="form-text">15 dígitos. Deixe vazio para apagar o número.</div>
            </form>

            <h3 class="h6 fw-bold">Fotos do cartão</h3>
            <?php paFormEnvio($alunoId, 'sus', [
                'accept' => 'image/jpeg,image/png,image/webp',
                'ajuda' => 'JPG, PNG ou WEBP, até 5 MB.',
                'botao' => 'Adicionar foto',
                'placeholder' => 'Ex.: frente / verso',
            ]); ?>

            <?php paGaleria($docs['sus'], 'Foto do cartão do SUS'); ?>
        </div>

        <!-- ================== LAUDOS MÉDICOS ================== -->
        <div class="tab-pane" id="aba-laudos" role="tabpanel" aria-labelledby="perfil-laudos-tab" tabindex="0">
            <?php paTituloPainel('Laudos Médicos', 'bi-file-earmark-medical-fill'); ?>

            <?php paFormEnvio($alunoId, 'laudo', [
                'titulo' => true,
                'titulo_obrigatorio' => true,
                'data' => true,
                'accept' => 'image/jpeg,image/png,image/webp,application/pdf',
                'ajuda' => 'PDF, JPG, PNG ou WEBP, até 10 MB.',
                'botao' => 'Enviar laudo',
                'placeholder' => 'Ex.: laudo neuropsicológico, emitido por...',
            ]); ?>

            <?php if (!$docs['laudo']): ?>
                <p class="text-muted mb-0">Nenhum laudo enviado ainda.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-success">Laudo</th>
                                <th scope="col" class="text-success">Data</th>
                                <th scope="col" class="text-success">Enviado por</th>
                                <th scope="col" class="text-success text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docs['laudo'] as $l): ?>
                                <tr>
                                    <td class="text-break">
                                        <i class="bi <?= $l->mime === 'application/pdf' ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-success' ?> me-1" aria-hidden="true"></i>
                                        <b><?= vp($l->titulo) ?></b>
                                        <?php if (!empty($l->descricao)): ?>
                                            <br><small class="text-muted"><?= vp($l->descricao) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-nowrap">
                                        <?= !empty($l->data_documento) ? date('d/m/Y', strtotime($l->data_documento)) : '--' ?>
                                    </td>
                                    <td>
                                        <?= vp($l->serv_nome, '--') ?>
                                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($l->criado_em)) ?></small>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="<?= URL ?>/anexos/arquivo/<?= (int) $l->id ?>" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Abrir
                                        </a>
                                        <form method="post" action="<?= URL ?>/anexos/excluirDocumento/<?= (int) $l->id ?>"
                                            class="d-inline" onsubmit="return confirm('Excluir este laudo?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash-fill" aria-hidden="true"></i> Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================== INFORMAÇÕES PSICOLÓGICAS (restrito) ================== -->
        <div class="tab-pane" id="aba-psico" role="tabpanel" aria-labelledby="perfil-psico-tab" tabindex="0">
            <?php paTituloPainel('Informações Psicológicas', $podePsico ? 'bi-chat-heart-fill' : 'bi-lock-fill'); ?>

            <?php if (!$podePsico): ?>
                <!-- O conteúdo nem chega à página: o Controller só busca se houver permissão -->
                <div class="alert alert-secondary mb-0">
                    <i class="bi bi-lock-fill me-1" aria-hidden="true"></i>
                    Conteúdo sigiloso, disponível apenas para o(a) psicólogo(a).
                </div>
            <?php else: ?>
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-shield-lock-fill me-1" aria-hidden="true"></i>
                    Conteúdo sigiloso: só quem tem permissão psicológica consegue ver estas anotações.
                </div>
                <?php paNotas($notasPsico, $alunoId, 'psicologica', $usuarioId, 'Registre aqui o acompanhamento psicológico...', 'pa-nota--psico'); ?>
            <?php endif; ?>
        </div>

        <!-- ================== ANOTAÇÕES RÁPIDAS ================== -->
        <div class="tab-pane" id="aba-notas" role="tabpanel" aria-labelledby="perfil-notas-tab" tabindex="0">
            <?php paTituloPainel('Anotações Rápidas', 'bi-sticky-fill'); ?>
            <?php paNotas($notasRapidas, $alunoId, 'rapida', $usuarioId, 'Ex.: mãe avisou que o aluno chega mais tarde esta semana...'); ?>
        </div>

        <!-- ================== INFORMAÇÕES EXTRAS ================== -->
        <div class="tab-pane" id="aba-extras" role="tabpanel" aria-labelledby="perfil-extras-tab" tabindex="0">
            <?php paTituloPainel(
                'Informações Extras',
                'bi-info-circle-fill',
                '<button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="collapse" data-bs-target="#formExtras" aria-expanded="false" aria-controls="formExtras">'
                . '<i class="bi bi-pencil-fill me-1" aria-hidden="true"></i> Editar</button>'
            ); ?>

            <?php if ($infoExtras !== ''): ?>
                <div class="p-3 bg-body rounded border mb-3 text-break">
                    <?= nl2br(htmlspecialchars($infoExtras)) ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Nenhuma informação extra registrada.</p>
            <?php endif; ?>

            <!-- Formulário de edição (abre ao clicar em "Editar") -->
            <div class="collapse" id="formExtras">
                <form method="post" action="<?= URL ?>/anexos/salvarInfoExtras/<?= $alunoId ?>">
                    <label for="inputExtras" class="form-label small mb-1">Informações extras</label>
                    <textarea id="inputExtras" name="info_extras" rows="6" maxlength="5000"
                        class="form-control mb-2"><?= htmlspecialchars($infoExtras) ?></textarea>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Salvar informações
                    </button>
                </form>
            </div>
        </div>

        <!-- ================== EDUCAÇÃO FORMAL ================== -->
        <div class="tab-pane" id="aba-educacao" role="tabpanel" aria-labelledby="perfil-educacao-tab" tabindex="0">
            <?php paTituloPainel('Educação Formal', 'bi-mortarboard-fill', paBotaoEditar($urlEditar, 'aba-educacao')); ?>

            <?php if (!$saude): ?>
                <p class="text-muted mb-0">Sem informações registradas.</p>
            <?php else:
                // "Comparece ao atendimento": se "Não", mostra o motivo.
                $att = trim((string) $saude->atendimento);
                $attHtml = ($att === 'Não' && trim((string) $saude->atendimento_porque) !== '')
                    ? vp('Não — ' . $saude->atendimento_porque)
                    : vp($att);

                paLinhas([
                    ['Tipo de escola anterior', vp($saude->tipo_escola)],
                    ['Motivo da escolha do IFRO', vp($saude->motivo_ifro)],
                    ['Já reprovou', vpSimQual($saude->ja_reprovou, $saude->quantas_vezes !== null ? $saude->quantas_vezes . ' vez(es)' : '')],
                    ['Estuda fora do horário de aula', vpSimQual($saude->estuda_fora, $saude->quanto_tempo)],
                    ['Gosta do curso (0–10)', vp($saude->nota_curso)],
                    ['Motivação para estudar (0–10)', vp($saude->nota_motivacao)],
                    ['Comparece ao atendimento individual com o professor', $attHtml, true],
                ]);
            endif; ?>
        </div>

        <!-- ================== HÁBITOS DE VIDA ================== -->
        <div class="tab-pane" id="aba-habitos" role="tabpanel" aria-labelledby="perfil-habitos-tab" tabindex="0">
            <?php paTituloPainel('Hábitos de Vida', 'bi-activity', paBotaoEditar($urlEditar, 'aba-habitos')); ?>

            <?php if (!$saude): ?>
                <p class="text-muted mb-0">Sem informações registradas.</p>
            <?php else:
                paLinhas([
                    ['Padrão de sono', vp($saude->padrao_sono)],
                    ['Refeições por dia', vp($saude->refeicoes_dia)],
                    ['Alimentação habitual', vp($saude->alimentacao_habitual), true],
                    ['Fuma', vp($saude->fuma)],
                    ['Bebida alcoólica', vp($saude->bebida_alcoolica)],
                    ['Pais sabem', vp($saude->pais_sabem)],
                    ['Contato com drogas ilícitas', vpSimQual($saude->drogas_ilicitas, $saude->drogas_qual)],
                    ['Atividade física', vpSimQual($saude->atividade_fisica, $saude->frequencia_atividade)],
                    ['Banhos por dia', vp($saude->banhos_dia)],
                ]);
            endif; ?>
        </div>

        <!-- ================== HISTÓRICO DE VISITAS ================== -->
        <div class="tab-pane" id="aba-visitas" role="tabpanel" aria-labelledby="perfil-visitas-tab" tabindex="0">
            <?php paTituloPainel('Histórico de Visitas Recentes', 'bi-clipboard-pulse'); ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="fw-bold">
                            <th scope="col" class="text-success">Data</th>
                            <th scope="col" class="text-success">Tipo</th>
                            <th scope="col" class="text-success">Descrição</th>
                            <th scope="col" class="text-success">Observação</th>
                            <th scope="col" class="text-success">Profissional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($historico)): ?>
                            <?php foreach ($historico as $visita): ?>
                                <?php
                                $descricaoVisita = !empty($visita->cons_descricao)
                                    ? $visita->cons_descricao
                                    : (!empty($visita->total_respostas) ? 'Triagem com ' . (int) $visita->total_respostas . ' respostas' : '');
                                ?>
                                <tr>
                                    <td class="text-muted">
                                        <?php if (!empty($visita->cons_data)): ?>
                                            <?= date('d/m/Y', strtotime($visita->cons_data)) ?>
                                            <?= !empty($visita->cons_horario) ? substr($visita->cons_horario, 0, 5) : '' ?>
                                        <?php else: ?>
                                            --
                                        <?php endif; ?>
                                    </td>
                                    <td><?= vp($visita->cons_tipo, '--') ?></td>
                                    <td class="text-break"><?= vp($descricaoVisita, '--') ?></td>
                                    <td class="text-break"><?= vp($visita->cons_observacao, '--') ?></td>
                                    <td>
                                        <?= vp($visita->serv_nome, '--') ?>
                                        <?php if (!empty($visita->func_nome)): ?>
                                            <br><small class="text-muted"><?= vp($visita->func_nome) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted p-4">
                                    Nenhuma visita registrada para este aluno.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /tab-content -->
</div>

<!-- =====================================================================
     COMPORTAMENTO DAS ABAS
     - Abre a aba indicada no endereço (#aba-laudos), usado ao voltar
       de um envio/exclusão.
     - Mantém a aba no endereço, para o F5 não perder o lugar.
     ===================================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var nav = document.getElementById('abasPerfil');
        if (!nav || !window.bootstrap) { return; }

        // Abre a aba que veio no #hash.
        if (location.hash) {
            try {
                var alvo = nav.querySelector('[data-bs-target="' + location.hash + '"]');
                if (alvo) {
                    bootstrap.Tab.getOrCreateInstance(alvo).show();
                    // Leva a tela até as abas (e à mensagem de confirmação).
                    document.getElementById('areaAbasPerfil').scrollIntoView();
                }
            } catch (e) { /* hash inválido: ignora */ }
        }

        // Atualiza o endereço quando o usuário troca de aba.
        nav.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (botao) {
            botao.addEventListener('shown.bs.tab', function (e) {
                history.replaceState(null, '', e.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>