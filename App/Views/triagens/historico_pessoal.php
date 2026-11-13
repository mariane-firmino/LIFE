<?php
// =====================================================================
// VIEW: TRIAGEM PSICOLÓGICA - ETAPA 2: HISTÓRICO PESSOAL.
// =====================================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';
echo '<link rel="stylesheet" href="' . URL . '/css/triagens/psicologica_rapida.css">';

$form     = $dados['form'] ?? [];
$erros    = $dados['erros'] ?? [];
$opcoes   = $dados['opcoes'] ?? [];
$gatilhos = $dados['gatilhos'] ?? [];
$max      = (int) ($dados['max_texto'] ?? 2000);
$id       = (int) $dados['aluno_id'];
$triagem  = $dados['triagem'];
$acao     = URL . '/triagens/historico_pessoal/' . $id . '/' . (int) $triagem->id;
$gatilhosMarcados = (array) ($form['gatilhos'] ?? []);

// Escapa texto para o HTML.
if (!function_exists('trH')) {
    function trH($texto)
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }
}

// Classe "is-invalid" + mensagem de erro de um campo.
if (!function_exists('trInvalido')) {
    function trInvalido($erros, $campo)
    {
        return !empty($erros[$campo]) ? ' is-invalid' : '';
    }
}
if (!function_exists('trErro')) {
    function trErro($erros, $campo)
    {
        if (!empty($erros[$campo])) {
            echo '<div class="invalid-feedback">' . trH($erros[$campo]) . '</div>';
        }
    }
}

// Menu de escolha obrigatório (rótulo, campo, opções valor => texto).
if (!function_exists('trSelect')) {
    function trSelect($campo, $rotulo, $lista, $form, $erros, $controla = '')
    {
        $atual = (string) ($form[$campo] ?? '');
        ?>
        <label for="<?= $campo ?>" class="form-label fw-bold mb-1"><?= trH($rotulo) ?> <span class="text-danger">*</span></label>
        <select class="form-select<?= trInvalido($erros, $campo) ?>" id="<?= $campo ?>" name="<?= $campo ?>" required
            <?= $controla !== '' ? 'data-controla="' . trH($controla) . '"' : '' ?>>
            <option value="" <?= $atual === '' ? 'selected' : '' ?> disabled>Selecione</option>
            <?php foreach ($lista as $valor => $texto): ?>
                <option value="<?= trH($valor) ?>" <?= $atual === (string) $valor ? 'selected' : '' ?>><?= trH($texto) ?></option>
            <?php endforeach; ?>
        </select>
        <?php trErro($erros, $campo);
    }
}
?>
<div class="layout tr-psico">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">
        <header class="home-topo">
            <div class="home-titulo">
                <h1>Triagem Psicológica</h1>
                <p>Etapa 2 de 2 &mdash; Histórico pessoal de <?= trH($dados['aluno_nome']) ?>.</p>
            </div>
            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
        </header>
    </div>

    <nav class="m-3 tr-psico__breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/consulta">Consultar Aluno</a></li>
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>">Visualizar Perfil</a></li>
            <li class="breadcrumb-item">Triagem Psicológica</li>
            <li class="breadcrumb-item active" aria-current="page">Histórico Pessoal</li>
        </ol>
    </nav>

    <div class="container tr-psico__content">
        <?php Sessao::mensagem('triagens'); ?>

        <?php if ($erros): ?>
            <div class="alert alert-danger" role="alert">Confira os campos marcados em vermelho.</div>
        <?php endif; ?>

        <p class="text-muted small mb-3">
            Etapa 1 registrada: <b><?= trH($triagem->motivo) ?></b> (<?= trH($triagem->gravidade) ?>).
        </p>

        <form action="<?= $acao ?>" method="POST" class="tr-psico__form shadow p-3 p-lg-4">
            <div class="row g-4">

                <!-- Diagnóstico -->
                <div class="col-12 col-md-6">
                    <?php trSelect('diagnostico', 'Já recebeu diagnóstico de algum transtorno psicológico?', $opcoes['diagnostico'], $form, $erros, 'bloco-transtorno'); ?>
                </div>
                <div class="col-12 col-md-6" id="bloco-transtorno" data-mostrar-se="sim">
                    <label for="transtorno" class="form-label fw-bold mb-1">Qual transtorno foi diagnosticado? <span class="text-danger">*</span></label>
                    <textarea class="form-control<?= trInvalido($erros, 'transtorno') ?>" id="transtorno" name="transtorno"
                        rows="2" maxlength="<?= $max ?>" placeholder="Descreva o transtorno relatado..."><?= trH($form['transtorno'] ?? '') ?></textarea>
                    <?php trErro($erros, 'transtorno'); ?>
                </div>

                <!-- Medicamento -->
                <div class="col-12 col-md-6">
                    <?php trSelect('medicamento', 'Faz uso de algum medicamento?', $opcoes['medicamento'], $form, $erros, 'bloco-medicamento'); ?>
                </div>
                <div class="col-12 col-md-6" id="bloco-medicamento" data-mostrar-se="sim">
                    <label for="nome_medicamento" class="form-label fw-bold mb-1">Qual medicamento utiliza? <span class="text-danger">*</span></label>
                    <input type="text" class="form-control<?= trInvalido($erros, 'nome_medicamento') ?>" id="nome_medicamento"
                        name="nome_medicamento" maxlength="255" placeholder="Digite o nome do medicamento"
                        value="<?= trH($form['nome_medicamento'] ?? '') ?>">
                    <?php trErro($erros, 'nome_medicamento'); ?>
                </div>

                <!-- Acompanhamento -->
                <div class="col-12 col-md-6">
                    <?php trSelect('acompanhamento', 'Faz acompanhamento psicológico ou psiquiátrico?', $opcoes['acompanhamento'], $form, $erros); ?>
                </div>

                <!-- Crises -->
                <div class="col-12 col-md-6">
                    <?php trSelect('crises', 'Já teve crises de ansiedade ou pânico anteriormente?', $opcoes['crises'], $form, $erros, 'bloco-gatilhos'); ?>
                </div>

                <!-- Gatilhos (só se já teve crises) -->
                <div class="col-12" id="bloco-gatilhos" data-mostrar-se="sim">
                    <fieldset>
                        <legend class="form-label fw-bold mb-1 fs-6">
                            O que costuma desencadear as crises? <span class="text-muted fw-normal small">(opcional)</span>
                        </legend>
                        <div class="form-text mt-0 mb-2">Marque tudo o que se aplica.</div>
                        <div class="row g-2 tr-sintomas">
                            <?php foreach ($gatilhos as $valor => $rotulo): ?>
                                <?php $idCampo = 'gatilho-' . trH($valor); ?>
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="<?= $idCampo ?>"
                                            name="gatilhos[]" value="<?= trH($valor) ?>"
                                            <?= in_array($valor, $gatilhosMarcados, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?= $idCampo ?>"><?= trH($rotulo) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($erros['gatilhos'])): ?>
                            <div class="text-danger small mt-2"><?= trH($erros['gatilhos']) ?></div>
                        <?php endif; ?>
                    </fieldset>
                </div>

                <!-- Relação familiar -->
                <div class="col-12 col-md-4">
                    <?php trSelect('relacao_familiar', 'Como é a relação com os familiares?', $opcoes['relacao_familiar'], $form, $erros); ?>
                </div>

                <!-- Histórico familiar -->
                <div class="col-12 col-md-4">
                    <?php trSelect('historico_familiar', 'Há histórico de transtorno psicológico na família?', $opcoes['historico_familiar'], $form, $erros); ?>
                </div>

                <!-- Alimentação -->
                <div class="col-12 col-md-4">
                    <?php trSelect('alimentacao', 'Tem enfrentado dificuldades com a alimentação?', $opcoes['alimentacao'], $form, $erros); ?>
                </div>

                <!-- Observações finais -->
                <div class="col-12">
                    <label for="observacoes" class="form-label fw-bold mb-1">
                        Observações finais da profissional <span class="text-muted fw-normal small">(opcional)</span>
                    </label>
                    <textarea class="form-control<?= trInvalido($erros, 'observacoes') ?>" id="observacoes" name="observacoes"
                        rows="4" maxlength="<?= $max ?>" placeholder="Descreva informações importantes do atendimento..."
                        data-contador="contador-observacoes"><?= trH($form['observacoes'] ?? '') ?></textarea>
                    <?php trErro($erros, 'observacoes'); ?>
                    <div class="form-text text-end" id="contador-observacoes"></div>
                </div>

                <!-- Botões -->
                <div class="col-12 d-flex flex-column flex-sm-row justify-content-between gap-2">
                    <a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>#aba-triagens"
                        class="btn btn-light border-0 text-success fw-bold px-4 tr-psico__cancel">
                        <i class="bi bi-skip-forward me-1" aria-hidden="true"></i> Pular esta etapa
                    </a>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Salvar informações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mostra "Se sim, qual...?" (e os gatilhos) só quando a resposta é "Sim".
        document.querySelectorAll('select[data-controla]').forEach(function (menu) {
            var bloco = document.getElementById(menu.dataset.controla);
            if (!bloco) {
                return;
            }
            function atualizar() {
                var mostrar = menu.value === bloco.dataset.mostrarSe;
                bloco.hidden = !mostrar;
                bloco.querySelectorAll('input, textarea, select').forEach(function (campo) {
                    campo.disabled = !mostrar;
                });
            }
            menu.addEventListener('change', atualizar);
            atualizar();
        });

        // Contador "x / máximo" embaixo da caixa de texto.
        document.querySelectorAll('textarea[data-contador]').forEach(function (caixa) {
            var contador = document.getElementById(caixa.dataset.contador);
            function atualizar() {
                contador.textContent = caixa.value.length + ' / ' + caixa.maxLength;
            }
            caixa.addEventListener('input', atualizar);
            atualizar();
        });
    });
</script>

<?php include '../App/Views/footer.php'; ?>
