<?php
// =====================================================================
// VIEW: PERFIL DO ALUNO  ->  App/Views/alunos/visualizarPerfil.php
// =====================================================================

// Só usuários logados acessam esta página.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

// CSS adicional deste perfil.
echo '<link rel="stylesheet" href="' . URL . '/css/alunos/perfilAluno.css">';

// ---------------------------------------------------------------------
// DADOS RECEBIDOS DO CONTROLLER
// ---------------------------------------------------------------------
$aluno      = $dados['aluno'];
$saude      = $dados['saude'];
$historico  = $dados['historico'] ?? [];
$imc        = $dados['imc'] ?? null;        // array ou null (sem altura/peso)
$atencao    = $dados['atencao'];            // nível, rótulo e motivos
$condicoes  = $dados['condicoes'] ?? [];    // lista de condições especiais


// ---------------------------------------------------------------------
// FUNÇÕES AUXILIARES (só são criadas se ainda não existirem)
// ---------------------------------------------------------------------

// Escapa para HTML e mostra "Não informado" quando o valor está vazio.
if (!function_exists('vp')) {
    function vp($valor, $padrao = 'Não informado')
    {
        $valor = trim((string) ($valor ?? ''));
        if ($valor === '') {
            return '<span class="text-muted fw-normal">' . $padrao . '</span>';
        }
        return htmlspecialchars(html_entity_decode($valor, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }
}

// "Sim" + detalhe (ex.: "Sim — poeira").
if (!function_exists('vpSimQual')) {
    function vpSimQual($resposta, $qual)
    {
        $resposta = trim((string) ($resposta ?? ''));
        $qual = trim((string) ($qual ?? ''));
        if ($resposta === '') {
            return vp('');
        }
        if ($resposta === 'Sim' && $qual !== '') {
            return vp($resposta . ' — ' . $qual);
        }
        return vp($resposta);
    }
}

// Telefone formatado: (69) 99999-9999.
if (!function_exists('vpTelefone')) {
    function vpTelefone($numero)
    {
        $limpo = preg_replace('/\D/', '', (string) $numero);
        if (strlen($limpo) == 11) {
            return vp('(' . substr($limpo, 0, 2) . ') ' . substr($limpo, 2, 5) . '-' . substr($limpo, 7, 4));
        }
        if (strlen($limpo) == 10) {
            return vp('(' . substr($limpo, 0, 2) . ') ' . substr($limpo, 2, 4) . '-' . substr($limpo, 6, 4));
        }
        return vp($numero);
    }
}

// Cartão da seção "Informações rápidas".
// $estado: alerta (vermelho) | aviso (amarelo) | info (azul) | ok (verde).
// $valorHtml já deve vir escapado (use vp / vpSimQual).
if (!function_exists('paCartaoRapido')) {
    function paCartaoRapido($icone, $rotulo, $valorHtml, $estado = 'ok')
    {
        // Ícone + texto escondido: o estado não depende só da cor.
        $selos = [
            'alerta' => ['bi-exclamation-triangle-fill text-danger', 'Alerta'],
            'aviso'  => ['bi-exclamation-circle-fill text-warning', 'Aviso'],
            'info'   => ['bi-info-circle-fill text-info', 'Informação'],
            'ok'     => ['bi-check-circle-fill text-success', 'Sem alerta'],
        ];
        [$classeSelo, $textoSelo] = $selos[$estado] ?? $selos['ok'];

        return '<div class="col">'
            . '<div class="pa-rapida__item pa-rapida__item--' . $estado . '">'
            .   '<i class="bi ' . $icone . ' pa-rapida__icone" aria-hidden="true"></i>'
            .   '<div class="flex-grow-1">'
            .     '<div class="pa-rapida__rotulo">' . $rotulo . '</div>'
            .     '<div class="pa-rapida__valor">' . $valorHtml . '</div>'
            .   '</div>'
            .   '<i class="bi ' . $classeSelo . '" aria-hidden="true"></i>'
            .   '<span class="visually-hidden">' . $textoSelo . '</span>'
            . '</div></div>';
    }
}


// ---------------------------------------------------------------------
// PREPARAÇÃO DOS VALORES PARA EXIBIR
// ---------------------------------------------------------------------
$nascimento = !empty($aluno->data_nascimento) ? date('d/m/Y', strtotime($aluno->data_nascimento)) : '';
$cadastro   = !empty($aluno->data_cadastro) ? date('d/m/Y', strtotime($aluno->data_cadastro)) : '';
$atualizado = !empty($aluno->atualizado_em) ? date('d/m/Y H:i', strtotime($aluno->atualizado_em)) : '';
$idadeTexto = $dados['idade'] !== null ? $dados['idade'] . ' anos' : 'Idade não informada';
$id         = (int) $aluno->id;

// Altura (m) e peso (kg) formatados.
$alturaTexto = ($aluno->altura !== null && $aluno->altura !== '')
    ? number_format((float) $aluno->altura, 2, ',', '') . ' m'
    : '';
$pesoTexto = ($aluno->peso !== null && $aluno->peso !== '')
    ? rtrim(rtrim(number_format((float) $aluno->peso, 2, ',', ''), '0'), ',') . ' kg'
    : '';

// Foto do aluno (Public/uploads/alunos/) ou imagem padrão.
$fotoAluno = (!empty($aluno->foto) && is_file(dirname(__DIR__, 3) . '/Public/uploads/alunos/' . basename((string) $aluno->foto)))
    ? URL . '/uploads/alunos/' . basename((string) $aluno->foto)
    : URL . '/img/user.avif';

// Opções do botão "Adicionar Triagem": [rota, título, descrição, ícone].
$opcoesTriagem = [
    ['psicologica', 'Triagem psicológica', 'Situação psicoemocional', 'bi-chat-heart-fill'],
    ['fisica',      'Triagem física',      'Saúde sexual e reprodutiva', 'bi-heart-pulse-fill'],
    ['rapida',      'Triagem rápida',      'Atendimento do dia a dia', 'bi-lightning-charge-fill'],
];

// --- Indicador de atenção: ícone e texto do tooltip ---
$iconesAtencao = [
    'alta'      => 'bi-exclamation-octagon-fill',
    'media'     => 'bi-exclamation-triangle-fill',
    'baixa'     => 'bi-check-circle-fill',
    'sem-dados' => 'bi-question-circle-fill',
];
$iconeAtencao   = $iconesAtencao[$atencao['nivel']] ?? $iconesAtencao['sem-dados'];
$tooltipAtencao = $atencao['motivos'] ? implode(' • ', $atencao['motivos']) : 'Nenhum alerta encontrado na ficha.';

// --- Responsável: telefone, link de ligação e WhatsApp ---
$telResp       = (string) $dados['telefone_responsavel'];
$digitosResp   = preg_replace('/\D/', '', $telResp);
$temTelResp    = strlen($digitosResp) >= 10;
// WhatsApp precisa do DDI do Brasil (55) na frente.
$linkWhatsApp  = 'https://wa.me/' . (strlen($digitosResp) <= 11 ? '55' : '') . $digitosResp;

// Atalho: a resposta é "Sim"?
$ehSim = function ($valor) {
    return trim((string) $valor) === 'Sim';
};
?>
<div class="layout">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <div class="row align-items-center">

            <!-- CABEÇALHO -->
            <header class="home-topo">

                <div class="home-titulo">
                    <h1>Perfil do Aluno</h1>
                    <p>Visualize as informações do estudante aqui.</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

            </header>
        </div>

    </div>


    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/consulta">Consultar Aluno</a></li>
            <li class="breadcrumb-item active" aria-current="page">Visualizar Perfil</li>
        </ol>
    </nav>

    <div class="container">
        <?php Sessao::mensagem('alunos'); ?>
    </div>

    <!-- ===================================== Ações ======================================= -->
    <div class="container">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">

            <!-- Adicionar Triagem: abre um menu para escolher qual triagem fazer -->
            <div class="dropdown">
                <button type="button" class="btn btn-success fw-bold px-3 py-2 dropdown-toggle w-100"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Adicionar Triagem
                </button>
                <ul class="dropdown-menu shadow">
                    <?php foreach ($opcoesTriagem as [$rotaOpcao, $tituloOpcao, $descOpcao, $iconeOpcao]): ?>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= URL ?>/triagens/<?= $rotaOpcao ?>/<?= $id ?>">
                                <i class="bi <?= $iconeOpcao ?> text-success fs-5" aria-hidden="true"></i>
                                <span>
                                    <b><?= $tituloOpcao ?></b><br>
                                    <small class="text-muted"><?= $descOpcao ?></small>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= URL ?>/alunos/consulta"
                    class="btn btn-light border-0 text-success fw-bold px-3 py-2"
                    style="background-color: #f5f0f0;">
                    <i class="bi bi-arrow-left me-1"></i>
                    Voltar
                </a>

                <a href="<?= URL ?>/alunos/editarPerfil/<?= $id ?>" class="btn btn-success fw-bold px-3 py-2">
                    <i class="bi bi-pencil-fill me-2"></i>
                    Editar Aluno
                </a>
            </div>

        </div>
    </div>

    <!-- ===================================== Cabeçalho do perfil ======================================= -->
    <div class="container mt-4">
        <div class="shadow rounded p-3 p-lg-4 mb-4">
            <div class="row align-items-center g-4">

                <!-- Foto + identificação -->
                <div class="col-12 col-lg-5">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3 text-center text-sm-start">

                        <!-- Coluna da foto: INDICADOR DE ATENÇÃO fica ACIMA da foto -->
                        <div class="d-flex flex-column align-items-center gap-2 flex-shrink-0">

                            <!-- Selo de atenção (passe o mouse / toque para ver os motivos) -->
                            <span class="pa-atencao pa-atencao--<?= $atencao['nivel'] ?>"
                                tabindex="0"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="<?= htmlspecialchars($tooltipAtencao) ?>">
                                <i class="bi <?= $iconeAtencao ?>" aria-hidden="true"></i>
                                <?= htmlspecialchars($atencao['rotulo']) ?>
                            </span>

                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($fotoAluno) ?>"
                                    class="rounded-circle border border-3 border-success-subtle"
                                    style="width: 130px; height: 130px; object-fit: cover;"
                                    alt="Foto do aluno">

                                <form action="<?= URL ?>/alunos/atualizarFoto/<?= $id ?>" method="POST" enctype="multipart/form-data">
                                    <label for="fotoAluno"
                                        class="btn btn-success btn-sm rounded-circle position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center"
                                        style="width: 34px; height: 34px; padding: 0;"
                                        title="Alterar foto">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" name="foto" id="fotoAluno" class="d-none"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="if (this.files[0] && this.files[0].size > 5 * 1024 * 1024) { alert('A imagem deve ter no máximo 5 MB.'); this.value = ''; } else if (this.files[0]) { this.form.submit(); }">
                                </form>
                            </div>
                        </div>

                        <div style="min-width: 0;">
                            <h4 class="fw-bold mb-1 text-break"><?= vp($aluno->nome) ?></h4>
                            <p class="text-muted mb-3">
                                <?= $idadeTexto ?> &bull; Nascimento: <?= vp($nascimento) ?>
                            </p>
                            <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-2">
                                <span class="badge text-bg-light border fw-normal fs-6">
                                    <i class="bi bi-mortarboard-fill text-success me-1"></i>
                                    Turma: <?= vp(!empty($aluno->ano_turma) ? $aluno->ano_turma . '' : '', '--') ?>
                                </span>
                                <span class="badge text-bg-light border fw-normal fs-6">
                                    <i class="bi bi-sun-fill text-success me-1"></i>
                                    <?= vp($aluno->turno, '--') ?>
                                </span>
                                <span class="badge text-bg-light border fw-normal fs-6">
                                    <i class="bi bi-person-badge-fill text-success me-1"></i>
                                    Mat.: <?= vp($aluno->matricula, '--') ?>
                                </span>
                            </div>

                            <!-- CONDIÇÕES ESPECIAIS (autismo, paralisia, etc.) -->
                            <?php if (!empty($condicoes)): ?>
                                <div class="pa-cond d-flex flex-wrap justify-content-center justify-content-sm-start gap-2 mt-3">
                                    <?php foreach ($condicoes as $condicao): ?>
                                        <span class="pa-cond__item">
                                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                            <?= vp($condicao) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- Indicadores (altura, peso, IMC...) -->
                <div class="col-12 col-lg-7">
                    <div class="row row-cols-2 row-cols-md-3 g-2 text-center">

                        <?php
                        // Quadros simples (ícone, valor, rótulo).
                        $indicadores = [
                            ['bi-rulers', $alturaTexto, 'Altura'],
                            ['bi-speedometer2', $pesoTexto, 'Peso'],
                        ];
                        foreach ($indicadores as [$icone, $valor, $rotulo]): ?>
                            <div class="col">
                                <div class="h-100 p-3 rounded border bg-body-tertiary d-flex flex-column align-items-center justify-content-center">
                                    <i class="bi <?= $icone ?> text-success fs-4 mb-1"></i>
                                    <div class="fw-bold fs-6 text-nowrap"><?= vp($valor, '--') ?></div>
                                    <small class="text-muted"><?= $rotulo ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- IMC calculado automaticamente (peso ÷ altura²) -->
                        <div class="col">
                            <div class="pa-imc <?= $imc ? 'pa-imc--' . $imc['nivel'] : '' ?> h-100 p-3 rounded border bg-body-tertiary d-flex flex-column align-items-center justify-content-center"
                                <?php if ($imc && $imc['nota'] !== ''): ?>
                                data-bs-toggle="tooltip" title="<?= htmlspecialchars($imc['nota']) ?>"
                                <?php endif; ?>>
                                <i class="bi bi-calculator-fill text-success fs-4 mb-1"></i>
                                <?php if ($imc): ?>
                                    <div class="fw-bold fs-6 text-nowrap"><?= $imc['texto'] ?> <small class="fw-normal">kg/m²</small></div>
                                    <small class="text-muted">
                                        IMC<?= $imc['classe'] ? ' · ' . htmlspecialchars($imc['classe']) : '' ?>
                                    </small>
                                    <?php if ($imc['nota'] !== ''): ?>
                                        <small class="text-muted" style="font-size: .7rem;">Ver curva da OMS (idade/sexo)</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="fw-bold fs-6"><span class="text-muted fw-normal">--</span></div>
                                    <small class="text-muted">IMC (falta altura/peso)</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        $indicadores2 = [
                            ['bi-droplet-fill', $aluno->tipo_sanguineo, 'Tipo sanguíneo'],
                            ['bi-gender-ambiguous', $aluno->sexo, 'Sexo biológico'],
                            ['bi-calendar-fill', $cadastro, 'Data de cadastro'],
                        ];
                        foreach ($indicadores2 as [$icone, $valor, $rotulo]): ?>
                            <div class="col">
                                <div class="h-100 p-3 rounded border bg-body-tertiary d-flex flex-column align-items-center justify-content-center">
                                    <i class="bi <?= $icone ?> text-success fs-4 mb-1"></i>
                                    <div class="fw-bold fs-6 text-nowrap"><?= vp($valor, '--') ?></div>
                                    <small class="text-muted"><?= $rotulo ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($atualizado !== ''): ?>
                        <p class="text-muted small text-end mt-2 mb-0">
                            <i class="bi bi-clock-history me-1"></i>Última atualização: <?= $atualizado ?>
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- ===================================== INFORMAÇÕES RÁPIDAS (destaque) ======================================= -->
    <div class="container text-start">
        <section class="pa-rapida p-3 mb-3" aria-labelledby="titulo-rapidas">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-heart-pulse-fill text-success fs-3"></i>
                <h2 id="titulo-rapidas" class="h5 mb-0"><b>Informações Rápidas</b></h2>

                <!-- Selo no canto superior direito (ms-auto empurra para a direita) -->
                <span class="pa-rapida__selo ms-auto">
                    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                    IMPORTANTE
                </span>
            </div>

            <?php if (!$saude): ?>
                <p class="text-muted mb-0">
                    Este aluno ainda não possui ficha de saúde registrada.
                    <a href="<?= URL ?>/alunos/editarPerfil/<?= $id ?>">Preencher agora</a>
                </p>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-2">
                    <?php
                    // Cada cartão: ícone, rótulo, valor e estado (cor).
                    // Os estados seguem a gravidade: "Sim" em risco = alerta.

                    // Condição especial (autismo, paralisia...).
                    echo paCartaoRapido(
                        'bi-person-wheelchair',
                        'Condição especial',
                        $condicoes ? vp(implode(', ', $condicoes)) : vp('Nenhuma informada'),
                        $condicoes ? 'alerta' : 'ok'
                    );

                    echo paCartaoRapido(
                        'bi-flower1',
                        'Alergias / restrições alimentares',
                        vpSimQual($saude->alergia_alimentar, $saude->alergia_qual),
                        $ehSim($saude->alergia_alimentar) ? 'alerta' : 'ok'
                    );

                    echo paCartaoRapido(
                        'bi-clipboard2-pulse-fill',
                        'Doença crônica',
                        vpSimQual($saude->doenca_cronica, $saude->doenca_qual),
                        $ehSim($saude->doenca_cronica) ? 'alerta' : 'ok'
                    );

                    echo paCartaoRapido(
                        'bi-capsule',
                        'Uso contínuo de medicação',
                        vpSimQual($saude->medicacao_continua, $saude->medicacao_qual),
                        $ehSim($saude->medicacao_continua) ? 'aviso' : 'ok'
                    );

                    echo paCartaoRapido(
                        'bi-chat-heart-fill',
                        'Acompanhamento psicológico',
                        vp($saude->acompanhamento_psi),
                        $ehSim($saude->acompanhamento_psi) ? 'info' : 'ok'
                    );

                    // Vacina: "Não" merece aviso.
                    echo paCartaoRapido(
                        'bi-shield-plus',
                        'Cartão de vacina',
                        vp($saude->cartao_vacina),
                        trim((string) $saude->cartao_vacina) === 'Não' ? 'aviso' : 'ok'
                    );

                    echo paCartaoRapido(
                        'bi-hospital-fill',
                        'Acesso ao serviço de saúde',
                        vp($saude->acesso_saude),
                        'ok'
                    );

                    echo paCartaoRapido(
                        'bi-hospital',
                        'Já foi internado(a)',
                        vpSimQual($saude->ja_internado, $saude->internado_motivo),
                        $ehSim($saude->ja_internado) ? 'info' : 'ok'
                    );

                    echo paCartaoRapido(
                        'bi-bandaid-fill',
                        'Já fez cirurgia',
                        vpSimQual($saude->ja_cirurgia, $saude->cirurgia_qual),
                        $ehSim($saude->ja_cirurgia) ? 'info' : 'ok'
                    );
                    ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- ===================================== RESPONSÁVEL (destaque) + Dados pessoais ======================================= -->
    <div class="container text-start">
        <div class="row g-3 mb-3 mt-4">

            <!-- Responsável: nome grande e botões para ligar / WhatsApp -->
            <div class="col-12 col-lg-5">
                <section class="pa-resp <?= $temTelResp ? '' : 'pa-resp--sem-tel' ?> shadow-sm h-100" aria-labelledby="titulo-resp">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-people-fill text-success fs-3"></i>
                        <h2 id="titulo-resp" class="h5 mb-0"><b>Responsável</b></h2>
                    </div>

                    <div class="pa-resp__nome mb-3"><?= vp($aluno->responsavel) ?></div>

                    <?php if ($temTelResp): ?>
                        <!-- Link tel: abre o discador no celular -->
                        <a class="pa-resp__tel d-inline-flex align-items-center gap-2 mb-3"
                            href="tel:<?= $digitosResp ?>">
                            <i class="bi bi-telephone-fill text-success"></i>
                            <?= vpTelefone($telResp) ?>
                        </a>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="tel:<?= $digitosResp ?>" class="btn btn-success fw-bold">
                                <i class="bi bi-telephone-outbound-fill me-1"></i> Ligar
                            </a>
                            <a href="<?= htmlspecialchars($linkWhatsApp) ?>" target="_blank" rel="noopener noreferrer"
                                class="btn btn-outline-success fw-bold">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Sem telefone válido: alerta, pois é contato de emergência -->
                        <p class="fw-bold text-danger-emphasis mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Telefone do responsável não informado
                        </p>
                        <a href="<?= URL ?>/alunos/editarPerfil/<?= $id ?>" class="btn btn-danger btn-sm fw-bold">
                            <i class="bi bi-pencil-fill me-1"></i> Cadastrar telefone
                        </a>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Dados pessoais (sem o responsável, que agora tem bloco próprio) -->
            <div class="col-12 col-lg-7">
                <div class="shadow-sm p-3 bg-body-tertiary rounded h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-person-fill text-success fs-3"></i>
                        <h2 class="h5 mb-0"><b>Dados Pessoais</b></h2>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <small>Nome completo</small>
                            <p class="text-break"><b><?= vp($aluno->nome) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Data de nascimento</small>
                            <p><b><?= vp($nascimento) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>RG</small>
                            <p><b><?= vp($aluno->rg) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Matrícula</small>
                            <p><b><?= vp($aluno->matricula) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Curso</small>
                            <p class="text-break"><b><?= vp($aluno->curso) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Telefone do aluno</small>
                            <p><b><?= vpTelefone($dados['telefone_aluno']) ?></b></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ===================================== ABAS (saúde, vacina, SUS, laudos, anotações...) ======================================= -->
    <!-- Arquivo: App/Views/alunos/_abasPerfil.php (usa as variáveis e funções desta página) -->
    <?php include __DIR__ . '/_abasPerfil.php'; ?>

</div>

<!-- Ativa os tooltips do Bootstrap (motivos da atenção e nota do IMC) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        }
    });
</script>

<?php include '../App/Views/footer.php' ?>