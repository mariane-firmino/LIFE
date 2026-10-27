<?php
// =====================================================================
// VIEW: TRIAGEM RÁPIDA  ->  App/Views/triagens/triagem_rapida.php
// Formulário curto para atendimentos do dia a dia.
// =====================================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$form  = $dados['form'];
$erros = $dados['erros'];
$max   = (int) $dados['max_texto'];
$id    = (int) $dados['aluno_id'];

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

// Caixa de texto com contador de caracteres.
if (!function_exists('trTextarea')) {
    function trTextarea($form, $erros, $campo, $rotulo, $ajuda, $placeholder, $max, $obrigatorio = false)
    { ?>
        <div class="col-12">
            <label for="<?= $campo ?>" class="form-label fw-bold mb-1">
                <?= $rotulo ?> <?= $obrigatorio ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(opcional)</span>' ?>
            </label>
            <div class="form-text mt-0 mb-1"><?= $ajuda ?></div>
            <textarea class="form-control<?= trInvalido($erros, $campo) ?>" id="<?= $campo ?>" name="<?= $campo ?>"
                rows="4" maxlength="<?= $max ?>" placeholder="<?= trH($placeholder) ?>"
                data-contador="contador-<?= $campo ?>" <?= $obrigatorio ? 'required' : '' ?>><?= trH($form[$campo]) ?></textarea>
            <?php trErro($erros, $campo); ?>
            <div class="form-text text-end" id="contador-<?= $campo ?>"></div>
        </div>
    <?php }
}
?>
<style>
    /* Cores da gravidade no menu escolhido */
    .tr-gravidade[data-nivel="Leve"]  { border-left: 6px solid var(--bs-success); }
    .tr-gravidade[data-nivel="Média"] { border-left: 6px solid var(--bs-warning); }
    .tr-gravidade[data-nivel="Grave"] { border-left: 6px solid var(--bs-danger); }
</style>

<div class="layout">

    <!-- ===================================== Título ======================================= -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <div class="col-8 linha-verde">
                <h1>Triagem Rápida</h1>
                <p>Registre o atendimento de <b><?= trH($dados['aluno_nome']) ?></b>.</p>
            </div>
            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
        </div>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/consulta">Consultar Aluno</a></li>
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>">Visualizar Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Triagem Rápida</li>
        </ol>
    </nav>

    <div class="container mb-5">
        <?php Sessao::mensagem('triagens'); ?>

        <?php if ($erros): ?>
            <div class="alert alert-danger">Confira os campos marcados em vermelho.</div>
        <?php endif; ?>

        <form action="<?= URL ?>/triagens/rapida/<?= $id ?>" method="POST" class="shadow p-3 p-lg-4 rounded">
            <div class="row g-4">

                <!-- Queixa principal -->
                <div class="col-12 col-md-6">
                    <label for="queixa" class="form-label fw-bold mb-1">Queixa principal <span class="text-danger">*</span></label>
                    <select class="form-select<?= trInvalido($erros, 'queixa') ?>" id="queixa" name="queixa" required>
                        <option value="" <?= $form['queixa'] === '' ? 'selected' : '' ?> disabled>Selecione a queixa</option>
                        <?php foreach ($dados['queixas'] as $q): ?>
                            <option value="<?= trH($q) ?>" <?= $form['queixa'] === $q ? 'selected' : '' ?>><?= trH($q) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php trErro($erros, 'queixa'); ?>
                </div>

                <!-- Gravidade -->
                <div class="col-12 col-md-6">
                    <label for="gravidade" class="form-label fw-bold mb-1">Gravidade <span class="text-danger">*</span></label>
                    <select class="form-select tr-gravidade<?= trInvalido($erros, 'gravidade') ?>" id="gravidade" name="gravidade"
                        data-nivel="<?= trH($form['gravidade']) ?>" required>
                        <option value="" <?= $form['gravidade'] === '' ? 'selected' : '' ?> disabled>Selecione a gravidade</option>
                        <?php foreach ($dados['gravidades'] as $g): ?>
                            <option value="<?= trH($g) ?>" <?= $form['gravidade'] === $g ? 'selected' : '' ?>><?= trH($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php trErro($erros, 'gravidade'); ?>
                </div>

                <?php
                trTextarea($form, $erros, 'relato_aluno', 'Informações do aluno',
                    'O que o aluno relatou: sintomas, desde quando, se tomou algum remédio...',
                    'Ex.: Está com dor de cabeça desde o início da manhã, não tomou remédio.', $max);

                trTextarea($form, $erros, 'observacao', 'Informações da enfermeira',
                    'O que você observou ou mediu: sinais, temperatura, pressão...',
                    'Ex.: Temperatura 37,8 °C, pressão 11x7, aparentemente cansado.', $max);

                trTextarea($form, $erros, 'conduta', 'Como lidou',
                    'O que foi feito: orientação, medicação, repouso, contato com o responsável, encaminhamento...',
                    'Ex.: Repouso por 30 minutos e compressa. Responsável avisado; aluno voltou para a sala.', $max, true);
                ?>

                <!-- Botões -->
                <div class="col-12 d-flex flex-column flex-sm-row justify-content-between gap-2">
                    <a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>"
                        class="btn btn-light border-0 text-success fw-bold px-4" style="background-color: #f5f0f0;">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Salvar triagem
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cor da borda conforme a gravidade escolhida.
        var gravidade = document.getElementById('gravidade');
        if (gravidade) {
            gravidade.addEventListener('change', function () {
                gravidade.dataset.nivel = gravidade.value;
            });
        }

        // Contador "x / 2000" embaixo de cada caixa de texto.
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
