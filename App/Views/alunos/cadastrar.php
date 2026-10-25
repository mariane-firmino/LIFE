<?php
// =====================================================================
// VIEW: CADASTRO DE ALUNO EM ETAPAS (ABAS)
// Arquivo: App/Views/alunos/cadastrar.php
//
// O formulário continua sendo UM só (mesmos campos e mesmo controller);
// apenas foi dividido em 5 abas:
//   1. Dados do aluno   2. Responsável   3. Educação formal
//   4. Histórico de saúde   5. Hábitos de vida
// =====================================================================

// Só usuários logados acessam esta página.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

// CSS adicional das abas (arquivo: Public/css/cadastro-aluno.css).
echo '<link rel="stylesheet" href="' . URL . '/css/cadastro-aluno.css">';


// ---------------------------------------------------------------------
// FUNÇÕES AUXILIARES
// ---------------------------------------------------------------------

// Valor já escapado para usar dentro de value="...".
if (!function_exists('valor')) {
    function valor($dados, $campo)
    {
        return htmlspecialchars($dados[$campo] ?? '');
    }
}

// Mensagem de erro (vinda do Controller) logo abaixo do campo.
if (!function_exists('erro')) {
    function erro($dados, $campo)
    {
        if (!empty($dados[$campo . '_erro'])) {
            echo '<div class="text-danger small mt-1">' . htmlspecialchars($dados[$campo . '_erro']) . '</div>';
        }
    }
}

// Gera as <option> de um <select>, marcando a opção atual.
if (!function_exists('opcoes')) {
    function opcoes($atual, array $lista)
    {
        foreach ($lista as $valor) {
            $selecionado = ($atual === $valor) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($valor) . '"' . $selecionado . '>' . htmlspecialchars($valor) . '</option>';
        }
    }
}


// ---------------------------------------------------------------------
// LISTAS DO ADMINISTRADOR
// Opções dos <select> (curso, turno, ano da turma, tipo de escola...).
// Vêm do Controller (Alunos::listasOpcoes) e são definidas na página
// "Informações do sistema" do administrador. O servidor só escolhe.
// ---------------------------------------------------------------------
$listas = $dados['listas'] ?? [];


// ---------------------------------------------------------------------
// DEFINIÇÃO DAS ABAS
// "campos" = campos da aba que podem receber mensagem de erro do
// Controller. Serve para (1) abrir a aba com erro e (2) marcar a aba
// com um aviso vermelho quando o servidor rejeitar o cadastro.
// ---------------------------------------------------------------------
$abas = [
    [
        'id' => 'aluno',
        'titulo' => 'Dados do aluno',
        'icone' => 'bi-person-fill',
        'campos' => ['nome', 'data_nascimento', 'curso', 'turno', 'ano_turma', 'sexo', 'matricula',
                     'telefone_aluno', 'tipo_sanguineo', 'altura', 'peso'],
    ],
    [
        'id' => 'responsavel',
        'titulo' => 'Responsável',
        'icone' => 'bi-people-fill',
        'campos' => ['nome_responsavel', 'telefone_responsavel'],
    ],
    [
        'id' => 'educacao',
        'titulo' => 'Educação formal',
        'icone' => 'bi-mortarboard-fill',
        'campos' => [],
    ],
    [
        'id' => 'saude',
        'titulo' => 'Histórico de saúde',
        'icone' => 'bi-heart-pulse-fill',
        'campos' => ['condicao_outra'],
    ],
    [
        'id' => 'habitos',
        'titulo' => 'Hábitos de vida',
        'icone' => 'bi-activity',
        'campos' => [],
    ],
];

// Descobre quais abas têm erro e qual deve abrir primeiro.
$abaAtiva = 0;
$achouErro = false;
foreach ($abas as $i => &$aba) {
    $aba['erro'] = false;
    foreach ($aba['campos'] as $campo) {
        if (!empty($dados[$campo . '_erro'])) {
            $aba['erro'] = true;
            break;
        }
    }
    if ($aba['erro'] && !$achouErro) {
        $abaAtiva = $i;       // abre a primeira aba com erro
        $achouErro = true;
    }
}
unset($aba);

// Condições especiais (checkboxes) já marcadas.
$opcoesCondicoes = [
    'Autismo (TEA)',
    'TDAH',
    'Paralisia cerebral',
    'Deficiência física / motora',
    'Deficiência visual',
    'Deficiência auditiva',
    'Deficiência intelectual',
    'Síndrome de Down',
    'Epilepsia',
    'Outra',
];
$condicoesMarcadas = ($dados['condicoes'] ?? '') === ''
    ? []
    : explode(';', $dados['condicoes']);
?>
<div class="layout">
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
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

    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/aluno">Alunos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar Aluno</li>
        </ol>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="card p-4 shadow mx-auto" style="max-width: 1200px;">

            <h2 class="mt-2 fs-3">Cadastro de aluno</h2>
            <p class="text-muted small mb-3">Campos com <span class="text-danger">*</span> são obrigatórios.</p>

            <!-- novalidate: a validação é feita pelo JavaScript no final desta página
                 (assim ele consegue abrir a aba certa quando falta algum campo) -->
            <form id="formCadastroAluno" method="post" action="<?= URL ?>/alunos/cadastrar" novalidate>

                <!-- ============================= MENU DAS ABAS ============================= -->
                <ul class="nav nav-tabs flex-nowrap overflow-x-auto" id="abasCadastro" role="tablist">
                    <?php foreach ($abas as $i => $aba): ?>
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                class="nav-link <?= $i === $abaAtiva ? 'active' : '' ?> <?= $aba['erro'] ? 'aba-erro' : '' ?>"
                                id="aba-<?= $aba['id'] ?>-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#aba-<?= $aba['id'] ?>"
                                role="tab"
                                aria-controls="aba-<?= $aba['id'] ?>"
                                aria-selected="<?= $i === $abaAtiva ? 'true' : 'false' ?>">
                                <i class="bi <?= $aba['icone'] ?> me-1" aria-hidden="true"></i>
                                <?= $aba['titulo'] ?>
                                <?php if ($aba['erro']): ?>
                                    <!-- Aviso: esta aba tem campo com erro -->
                                    <span class="badge text-bg-danger ms-1">!</span>
                                <?php endif; ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content pt-4">

                    <!-- ===================== ABA 1: DADOS DO ALUNO ===================== -->
                    <div class="tab-pane <?= $abaAtiva === 0 ? 'show active' : '' ?>" id="aba-aluno" role="tabpanel" aria-labelledby="aba-aluno-tab" tabindex="0">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="inputnomealuno" class="form-label">Nome aluno <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputnomealuno" name="nome" required value="<?= valor($dados, 'nome') ?>">
                                <?php erro($dados, 'nome'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="inputdatadenascimento" class="form-label">Data de nascimento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="inputdatadenascimento" name="data_nascimento" required value="<?= valor($dados, 'data_nascimento') ?>">
                                <?php erro($dados, 'data_nascimento'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="inputcurso" class="form-label">Curso <span class="text-danger">*</span></label>
                                <select id="inputcurso" class="form-select" name="curso" required>
                                    <option value="" disabled <?= empty($dados['curso']) ? 'selected' : '' ?>>Escolha seu curso</option>
                                    <?php opcoes($dados['curso'] ?? '', $listas['curso'] ?? []); ?>
                                </select>
                                <?php erro($dados, 'curso'); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="inputturno" class="form-label">Turno <span class="text-danger">*</span></label>
                                <select id="inputturno" class="form-select" name="turno" required>
                                    <option value="" disabled <?= empty($dados['turno']) ? 'selected' : '' ?>>Escolha seu turno</option>
                                    <?php opcoes($dados['turno'] ?? '', $listas['turno'] ?? []); ?>
                                </select>
                                <?php erro($dados, 'turno'); ?>
                            </div>
                            <div class="col-md-2">
                                <label for="inputanoturma" class="form-label">Ano da turma <span class="text-danger">*</span></label>
                                <select id="inputanoturma" class="form-select" name="ano_turma" required>
                                    <option value="" disabled <?= empty($dados['ano_turma']) ? 'selected' : '' ?>>Ano</option>
                                    <?php opcoes($dados['ano_turma'] ?? '', $listas['ano_turma'] ?? []); ?>
                                </select>
                                <?php erro($dados, 'ano_turma'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="inputsexo" class="form-label">Qual seu sexo biológico? <span class="text-danger">*</span></label>
                                <select id="inputsexo" class="form-select" name="sexo" required>
                                    <option value="" disabled <?= empty($dados['sexo']) ? 'selected' : '' ?>>Escolha seu sexo</option>
                                    <?php opcoes($dados['sexo'] ?? '', ['Masculino', 'Feminino', 'Outro']); ?>
                                </select>
                                <?php erro($dados, 'sexo'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="inputnumerodematricula" class="form-label">Número de matrícula <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" maxlength="13" id="inputnumerodematricula" name="matricula" required value="<?= valor($dados, 'matricula') ?>">
                                <?php erro($dados, 'matricula'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="inputtelefonealuno" class="form-label">Telefone do aluno (WhatsApp)</label>
                                <input type="text" class="form-control" maxlength="15" id="inputtelefonealuno" name="telefone_aluno" value="<?= valor($dados, 'telefone_aluno') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="inputtiposanguineo" class="form-label">Tipo sanguíneo</label>
                                <select id="inputtiposanguineo" class="form-select" name="tipo_sanguineo">
                                    <option value="" <?= empty($dados['tipo_sanguineo']) ? 'selected' : '' ?>>Não informado</option>
                                    <?php opcoes($dados['tipo_sanguineo'] ?? '', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']); ?>
                                </select>
                                <?php erro($dados, 'tipo_sanguineo'); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="inputaltura" class="form-label">Altura (m)</label>
                                <input type="text" class="form-control" maxlength="4" inputmode="numeric" placeholder="Ex: 1,70" id="inputaltura" name="altura" value="<?= valor($dados, 'altura') ?>">
                                <?php erro($dados, 'altura'); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="inputpeso" class="form-label">Peso (kg)</label>
                                <input type="text" class="form-control" maxlength="6" inputmode="decimal" placeholder="Ex: 65,5" id="inputpeso" name="peso" value="<?= valor($dados, 'peso') ?>">
                                <?php erro($dados, 'peso'); ?>
                            </div>

                        </div>
                    </div>

                    <!-- ===================== ABA 2: RESPONSÁVEL ===================== -->
                    <div class="tab-pane <?= $abaAtiva === 1 ? 'show active' : '' ?>" id="aba-responsavel" role="tabpanel" aria-labelledby="aba-responsavel-tab" tabindex="0">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="inputnomeresponsavel" class="form-label">Nome do responsável <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputnomeresponsavel" name="nome_responsavel" required value="<?= valor($dados, 'nome_responsavel') ?>">
                                <?php erro($dados, 'nome_responsavel'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="inputtelefonedosresponsaveis" class="form-label">Telefone dos responsáveis (WhatsApp)</label>
                                <input type="text" class="form-control" maxlength="15" id="inputtelefonedosresponsaveis" name="telefone_responsavel" value="<?= valor($dados, 'telefone_responsavel') ?>">
                                <?php erro($dados, 'telefone_responsavel'); ?>
                            </div>

                        </div>
                    </div>

                    <!-- ===================== ABA 3: EDUCAÇÃO FORMAL ===================== -->
                    <div class="tab-pane <?= $abaAtiva === 2 ? 'show active' : '' ?>" id="aba-educacao" role="tabpanel" aria-labelledby="aba-educacao-tab" tabindex="0">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="inputtipoescola" class="form-label">Tipo de escola que estudou antes do IFRO:</label>
                                <select id="inputtipoescola" class="form-select" name="tipo_escola">
                                    <option value="" disabled <?= empty($dados['tipo_escola']) ? 'selected' : '' ?>>Escolha o tipo de escola</option>
                                    <?php opcoes($dados['tipo_escola'] ?? '', $listas['tipo_escola'] ?? []); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputporquenao" class="form-label">Porque escolheu o IFRO para estudar?</label>
                                <select id="inputporquenao" class="form-select" name="motivo_ifro">
                                    <option value="" disabled <?= empty($dados['motivo_ifro']) ? 'selected' : '' ?>>Escolha o motivo</option>
                                    <?php opcoes($dados['motivo_ifro'] ?? '', $listas['motivo_ifro'] ?? []); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputjareprovou" class="form-label">Você já reprovou antes?</label>
                                <select id="inputjareprovou" class="form-select" name="ja_reprovou">
                                    <option value="" disabled <?= empty($dados['ja_reprovou']) ? 'selected' : '' ?>>Escolha a opção</option>
                                    <?php opcoes($dados['ja_reprovou'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputquantasvezes" class="form-label">Quantas vezes reprovou?</label>
                                <input type="text" class="form-control" id="inputquantasvezes" name="quantas_vezes" value="<?= valor($dados, 'quantas_vezes') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputestudafora" class="form-label">Você estuda fora do horário de aula? (Em casa)</label>
                                <select id="inputestudafora" class="form-select" name="estuda_fora">
                                    <option value="" disabled <?= empty($dados['estuda_fora']) ? 'selected' : '' ?>>Escolha a opção</option>
                                    <?php opcoes($dados['estuda_fora'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputquantotempo" class="form-label">Quanto tempo você reserva para estudar fora da escola?</label>
                                <input type="text" class="form-control" id="inputquantotempo" name="quanto_tempo" value="<?= valor($dados, 'quanto_tempo') ?>">
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label">Em uma escala (0-10)<br>O quanto você gosta do curso que está matriculado?</label>
                                <div class="row">
                                    <?php for ($i = 0; $i <= 10; $i++): ?>
                                        <div class="col-auto form-check">
                                            <input class="form-check-input" type="radio" name="nota_curso" id="curso<?= $i ?>" value="<?= $i ?>" <?= (isset($dados['nota_curso']) && $dados['nota_curso'] !== '' && (int) $dados['nota_curso'] === $i) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="curso<?= $i ?>"><?= $i ?></label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Em uma escala (0-10)<br>O quanto você sente-se motivado para estudar?</label>
                                <div class="row">
                                    <?php for ($i = 0; $i <= 10; $i++): ?>
                                        <div class="col-auto form-check">
                                            <input class="form-check-input" type="radio" name="nota_motivacao" id="motivacao<?= $i ?>" value="<?= $i ?>" <?= (isset($dados['nota_motivacao']) && $dados['nota_motivacao'] !== '' && (int) $dados['nota_motivacao'] === $i) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="motivacao<?= $i ?>"><?= $i ?></label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="inputatendimento" class="form-label">Comparece ao atendimento indivídual com o professor?</label>
                                <select id="inputatendimento" class="form-select" name="atendimento">
                                    <option value="" disabled <?= empty($dados['atendimento']) ? 'selected' : '' ?>>Escolha a opção</option>
                                    <?php opcoes($dados['atendimento'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputatendimentoporque" class="form-label">Por quê? (Caso a resposta anterior seja "Não")</label>
                                <input type="text" class="form-control" id="inputatendimentoporque" name="atendimento_porque" value="<?= valor($dados, 'atendimento_porque') ?>">
                            </div>

                        </div>
                    </div>

                    <!-- ===================== ABA 4: HISTÓRICO DE SAÚDE ===================== -->
                    <div class="tab-pane <?= $abaAtiva === 3 ? 'show active' : '' ?>" id="aba-saude" role="tabpanel" aria-labelledby="aba-saude-tab" tabindex="0">
                        <div class="row g-3">

                            <!-- Condições especiais (autismo, paralisia, etc.) -->
                            <fieldset class="col-12 mb-3">
                                <legend class="fs-6 fw-bold">O aluno possui alguma condição especial? (opcional)</legend>
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-1">
                                    <?php foreach ($opcoesCondicoes as $i => $opcao): ?>
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="condicoes[]"
                                                    id="condicao-cadastro-<?= $i ?>"
                                                    value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= in_array($opcao, $condicoesMarcadas, true) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="condicao-cadastro-<?= $i ?>">
                                                    <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-2">
                                    <label for="condicao_outra_cadastro" class="form-label">Qual? (somente se marcou "Outra")</label>
                                    <input type="text" name="condicao_outra" id="condicao_outra_cadastro" maxlength="100"
                                        class="form-control <?= !empty($dados['condicao_outra_erro']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars(html_entity_decode($dados['condicao_outra'] ?? '', ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="invalid-feedback"><?= htmlspecialchars($dados['condicao_outra_erro'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </fieldset>

                            <div class="col-md-6">
                                <label for="inputacessosaude" class="form-label">Como é realizado seu acesso ao serviço de saúde?</label>
                                <select id="inputacessosaude" class="form-select" name="acesso_saude">
                                    <option value="" disabled <?= empty($dados['acesso_saude']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['acesso_saude'] ?? '', $listas['acesso_saude'] ?? []); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputalergiaalimentar" class="form-label">Possui alguma alergia alimentar ou algum tipo restrição alimentar?</label>
                                <select id="inputalergiaalimentar" class="form-select" name="alergia_alimentar">
                                    <option value="" disabled <?= empty($dados['alergia_alimentar']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['alergia_alimentar'] ?? '', ['Sim', 'Não', 'Não sei']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputalergiaqual" class="form-label">Se respondeu SIM, quais alimentos causam alergia ou restrição?</label>
                                <input type="text" class="form-control" id="inputalergiaqual" name="alergia_qual" value="<?= valor($dados, 'alergia_qual') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputdoencacronica" class="form-label">Você possui alguma doença crônica ou pré-existente?</label>
                                <select id="inputdoencacronica" class="form-select" name="doenca_cronica">
                                    <option value="" disabled <?= empty($dados['doenca_cronica']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['doenca_cronica'] ?? '', ['Sim', 'Não', 'Não sei']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputdoencaqual" class="form-label">Diga qual doença? Caso sua resposta anterior tenha sido SIM.</label>
                                <input type="text" class="form-control" id="inputdoencaqual" name="doenca_qual" value="<?= valor($dados, 'doenca_qual') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputmedicacaocontinua" class="form-label">Você faz uso contínuo ou regular de algum tipo de medicação?</label>
                                <select id="inputmedicacaocontinua" class="form-select" name="medicacao_continua">
                                    <option value="" disabled <?= empty($dados['medicacao_continua']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['medicacao_continua'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputmedicacaoqual" class="form-label">Qual medicação?</label>
                                <input type="text" class="form-control" id="inputmedicacaoqual" name="medicacao_qual" value="<?= valor($dados, 'medicacao_qual') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputacompanhamentopsi" class="form-label">Já realizou acompanhamento psicológico/psiquiátrico?</label>
                                <select id="inputacompanhamentopsi" class="form-select" name="acompanhamento_psi">
                                    <option value="" disabled <?= empty($dados['acompanhamento_psi']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['acompanhamento_psi'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputcartaovacina" class="form-label">Você tem cartão de vacina?</label>
                                <select id="inputcartaovacina" class="form-select" name="cartao_vacina">
                                    <option value="" disabled <?= empty($dados['cartao_vacina']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['cartao_vacina'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputjainternado" class="form-label">Alguma vez ja ficou internado?</label>
                                <select id="inputjainternado" class="form-select" name="ja_internado">
                                    <option value="" disabled <?= empty($dados['ja_internado']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['ja_internado'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputinternadomotivo" class="form-label">Por qual motivo? Caso sua resposta tenha sido SIM, na questão anterior</label>
                                <input type="text" class="form-control" id="inputinternadomotivo" name="internado_motivo" value="<?= valor($dados, 'internado_motivo') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputjacirurgia" class="form-label">Já passou por alguma cirurgia antes?</label>
                                <select id="inputjacirurgia" class="form-select" name="ja_cirurgia">
                                    <option value="" disabled <?= empty($dados['ja_cirurgia']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['ja_cirurgia'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputcirurgiaqual" class="form-label">Qual tipo de cirurgia você foi submetido? Caso sua resposta tenha sido SIM, na questão anterior.</label>
                                <input type="text" class="form-control" id="inputcirurgiaqual" name="cirurgia_qual" value="<?= valor($dados, 'cirurgia_qual') ?>">
                            </div>

                        </div>
                    </div>

                    <!-- ===================== ABA 5: HÁBITOS DE VIDA ===================== -->
                    <div class="tab-pane <?= $abaAtiva === 4 ? 'show active' : '' ?>" id="aba-habitos" role="tabpanel" aria-labelledby="aba-habitos-tab" tabindex="0">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="inputpadraosono" class="form-label">Como é o seu padrão de sono?</label>
                                <select id="inputpadraosono" class="form-select" name="padrao_sono">
                                    <option value="" disabled <?= empty($dados['padrao_sono']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['padrao_sono'] ?? '', [
                                        'Restaurador',
                                        'Não restaurador',
                                        'Insônia (perde o sono e não consegue voltar a dormir)',
                                        'Sonolência diurna',
                                    ]); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputrefeicoesdia" class="form-label">Quantas refeições você faz por dia?</label>
                                <select id="inputrefeicoesdia" class="form-select" name="refeicoes_dia">
                                    <option value="" disabled <?= empty($dados['refeicoes_dia']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['refeicoes_dia'] ?? '', ['3', 'Mais que 3', 'Menos que 3']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputalimentacaohabitual" class="form-label">O que você, geralmente, come durante as principais refeições do seu dia?</label>
                                <input type="text" class="form-control" id="inputalimentacaohabitual" name="alimentacao_habitual" value="<?= valor($dados, 'alimentacao_habitual') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputfuma" class="form-label">Você fuma algum tipo de cigarro?</label>
                                <select id="inputfuma" class="form-select" name="fuma">
                                    <option value="" disabled <?= empty($dados['fuma']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['fuma'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputbebidaalcoolica" class="form-label">Você consome algum tipo de bebida alcoólica?</label>
                                <select id="inputbebidaalcoolica" class="form-select" name="bebida_alcoolica">
                                    <option value="" disabled <?= empty($dados['bebida_alcoolica']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['bebida_alcoolica'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputpaissabem" class="form-label">Seus pais sabem disso? Caso sua resposta tenha sido SIM, nas questões anteriores.</label>
                                <select id="inputpaissabem" class="form-select" name="pais_sabem">
                                    <option value="" disabled <?= empty($dados['pais_sabem']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['pais_sabem'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputdrogasilicitas" class="form-label">Já fez uso ou teve contato com algum tipo de droga ilícita?</label>
                                <select id="inputdrogasilicitas" class="form-select" name="drogas_ilicitas">
                                    <option value="" disabled <?= empty($dados['drogas_ilicitas']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['drogas_ilicitas'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputdrogasqual" class="form-label">Qual/quais? Caso sua resposta tenha sido SIM, na questão anterior.</label>
                                <input type="text" class="form-control" id="inputdrogasqual" name="drogas_qual" value="<?= valor($dados, 'drogas_qual') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="inputatividadefisica" class="form-label">Pratica algum tipo de atividade física?</label>
                                <select id="inputatividadefisica" class="form-select" name="atividade_fisica">
                                    <option value="" disabled <?= empty($dados['atividade_fisica']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['atividade_fisica'] ?? '', ['Sim', 'Não']); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="inputfrequenciaatividade" class="form-label">Com qual frequência você pratica atividade física na semana?</label>
                                <select id="inputfrequenciaatividade" class="form-select" name="frequencia_atividade">
                                    <option value="" disabled <?= empty($dados['frequencia_atividade']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['frequencia_atividade'] ?? '', ['3x por semana', '(+) de 3x por semana', '(-) de 3x por semana']); ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label for="inputbanhosdia" class="form-label">Quantos banhos você toma por dia?</label>
                                <select id="inputbanhosdia" class="form-select" name="banhos_dia">
                                    <option value="" disabled <?= empty($dados['banhos_dia']) ? 'selected' : '' ?>>Escolha</option>
                                    <?php opcoes($dados['banhos_dia'] ?? '', ['1 por dia', '2 por dia', '3 por dia', 'Mais de 3 por dia']); ?>
                                </select>
                            </div>

                        </div>
                    </div>

                </div><!-- /tab-content -->

                <!-- ============================= BOTÕES DE NAVEGAÇÃO ============================= -->
                <div class="d-flex justify-content-between align-items-center mt-5">

                    <button type="reset" class="btn btn-link text-danger text-decoration-none">
                        Limpar formulário
                    </button>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnAnterior" class="btn btn-light border">
                            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Anterior
                        </button>

                        <button type="button" id="btnProximo" class="btn btn-success">
                            Próximo <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                        </button>

                        <!-- Só aparece na última aba -->
                        <button type="submit" id="btnEnviar" class="btn btn-success d-none">
                            Cadastrar Estudante
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- =====================================================================
     COMPORTAMENTO DAS ETAPAS
     - "Próximo" confere os campos obrigatórios da aba atual.
     - Ao enviar, confere TODAS as abas e abre a primeira com problema.
     - A validação do servidor (Controller) continua valendo.
     ===================================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.bootstrap) { return; }   // sem Bootstrap JS as abas não funcionam

        var form        = document.getElementById('formCadastroAluno');
        var abas        = Array.prototype.slice.call(document.querySelectorAll('#abasCadastro [data-bs-toggle="tab"]'));
        var btnAnterior = document.getElementById('btnAnterior');
        var btnProximo  = document.getElementById('btnProximo');
        var btnEnviar   = document.getElementById('btnEnviar');

        // Painel (conteúdo) de uma aba, pelo índice.
        function painel(i) { return document.querySelector(abas[i].getAttribute('data-bs-target')); }

        // Índice da aba aberta no momento.
        function atual() {
            return abas.findIndex(function (a) { return a.classList.contains('active'); });
        }

        // Abre a aba de índice i.
        function ir(i) { bootstrap.Tab.getOrCreateInstance(abas[i]).show(); }

        // Primeiro campo inválido (obrigatório vazio, formato errado...) de um painel.
        function primeiroInvalido(p) {
            return Array.prototype.slice.call(p.querySelectorAll('input, select, textarea'))
                .find(function (el) { return !el.checkValidity(); });
        }

        // Mostra "Anterior / Próximo / Cadastrar" conforme a aba aberta.
        function atualizarBotoes() {
            var i = atual();
            btnAnterior.disabled = (i === 0);
            btnProximo.classList.toggle('d-none', i === abas.length - 1);
            btnEnviar.classList.toggle('d-none', i !== abas.length - 1);
        }

        abas.forEach(function (a) { a.addEventListener('shown.bs.tab', atualizarBotoes); });
        atualizarBotoes();

        btnAnterior.addEventListener('click', function () {
            if (atual() > 0) { ir(atual() - 1); }
        });

        // "Próximo": só avança se a aba atual estiver correta.
        btnProximo.addEventListener('click', function () {
            var invalido = primeiroInvalido(painel(atual()));
            if (invalido) { invalido.reportValidity(); return; }
            ir(atual() + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Enter dentro de um campo avança a etapa (em vez de enviar o formulário cedo demais).
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && atual() !== abas.length - 1) {
                e.preventDefault();
                btnProximo.click();
            }
        });

        // Ao enviar: confere todas as abas, na ordem.
        form.addEventListener('submit', function (e) {
            for (var i = 0; i < abas.length; i++) {
                var invalido = primeiroInvalido(painel(i));
                if (invalido) {
                    e.preventDefault();
                    ir(i);
                    // Pequena espera para o painel ficar visível antes de mostrar o aviso.
                    setTimeout(function () { invalido.reportValidity(); }, 50);
                    return;
                }
            }
        });

        // "Limpar formulário" volta para a primeira aba.
        form.addEventListener('reset', function () {
            setTimeout(function () { ir(0); }, 0);
        });
    });
</script>

<?php include '../App/Views/footer.php' ?>