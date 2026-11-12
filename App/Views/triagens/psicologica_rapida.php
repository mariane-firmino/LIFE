<?php
// =====================================================================
// VIEW: TRIAGEM PSICOLÓGICA RÁPIDA.
// =====================================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';
echo '<link rel="stylesheet" href="' . URL . '/css/triagens/psicologica_rapida.css">';

$form  = $dados['form'] ?? [];
$erros = $dados['erros'] ?? [];
$max   = (int) ($dados['max_texto'] ?? 2000);
$id    = (int) $dados['aluno_id'];
$form += [
    'motivo' => '',
    'gravidade' => '',
    'sintomas' => [],
    'observacoes' => '',
];
$motivos = $dados['motivos'] ?? [
    'Crise de ansiedade' => 'Crise de ansiedade',
    'Ataque de pânico' => 'Ataque de pânico',
    'Choro intenso' => 'Choro intenso',
    'Nervosismo' => 'Nervosismo',
    'Outro' => 'Outro',
];
$gravidades = $dados['gravidades'] ?? ['Leve', 'Média', 'Grave'];

// Lista de sintomas (valor => rótulo) configurada pelo administrador.
$sintomas = $dados['sintomas'] ?? [
    'Falta de ar' => 'Falta de ar',
    'Tontura' => 'Tontura',
    'Tremores' => 'Tremores',
    'Taquicardia' => 'Taquicardia',
    'Choro intenso' => 'Choro intenso',
    'Aperto no peito' => 'Aperto no peito',
];
$sintomasMarcados = (array) ($form['sintomas'] ?? []);

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
?>
<div class="layout tr-psico">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">
        <header class="home-topo">
            <div class="home-titulo">
                <h1>Triagem Psicológica</h1>
                <p>Registre o atendimento de <?= htmlspecialchars($dados['aluno_nome'], ENT_QUOTES, 'UTF-8') ?>.</p>
            </div>
            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
        </header>
    </div>
    
    <nav class="m-3 tr-psico__breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/consulta">Consultar Aluno</a></li>
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>">Visualizar Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Triagem Psicológica</li>
        </ol>
    </nav>

    <div class="container tr-psico__content">
        <?php Sessao::mensagem('triagens'); ?>

        <?php if ($erros): ?>
            <div class="alert alert-danger" role="alert">Confira os campos marcados em vermelho.</div>
        <?php endif; ?>

        <form action="<?= URL ?>/triagens/psicologico_rapido/<?= $id ?>" method="POST" class="tr-psico__form shadow p-3 p-lg-4">
            <div class="row g-4">

                <!-- Motivo -->
                <div class="col-12 col-md-6">
                    <label for="motivo" class="form-label fw-bold mb-1">Motivo do atendimento <span class="text-danger">*</span></label>
                    <select class="form-select<?= trInvalido($erros, 'motivo') ?>" id="motivo" name="motivo" required>
                        <option value="" <?= ($form['motivo'] ?? '') === '' ? 'selected' : '' ?> disabled>Selecione o motivo</option>
                        <?php foreach ($motivos as $valor => $rotulo): ?>
                            <option value="<?= trH($valor) ?>" <?= ($form['motivo'] ?? '') === $valor ? 'selected' : '' ?>><?= trH($rotulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php trErro($erros, 'motivo'); ?>
                    <?php if (!$motivos): ?>
                        <div class="form-text text-danger">Nenhum motivo ativo foi configurado.</div>
                    <?php endif; ?>
                </div>

                <!-- Gravidade -->
                <div class="col-12 col-md-6">
                    <label for="gravidade" class="form-label fw-bold mb-1">Gravidade <span class="text-danger">*</span></label>
                    <select class="form-select tr-gravidade<?= trInvalido($erros, 'gravidade') ?>" id="gravidade" name="gravidade"
                        data-nivel="<?= trH($form['gravidade'] ?? '') ?>" required>
                        <option value="" <?= ($form['gravidade'] ?? '') === '' ? 'selected' : '' ?> disabled>Selecione a gravidade</option>
                        <?php foreach ($gravidades as $g): ?>
                            <option value="<?= trH($g) ?>" <?= ($form['gravidade'] ?? '') === $g ? 'selected' : '' ?>><?= trH($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php trErro($erros, 'gravidade'); ?>
                </div>

                <!-- Sintomas -->
                <div class="col-12">
                    <fieldset>
                        <legend class="form-label fw-bold mb-1 fs-6">
                            Sintomas apresentados <span class="text-muted fw-normal small">(opcional)</span>
                        </legend>
                        <div class="form-text mt-0 mb-2">Marque tudo o que o aluno apresentou.</div>
                        <?php if (!$sintomas): ?>
                            <p class="text-muted small">Nenhum sintoma ativo foi configurado.</p>
                        <?php endif; ?>
                        <div class="row g-2 tr-sintomas">
                            <?php foreach ($sintomas as $valor => $rotulo): ?>
                                <?php $idCampo = 'sintoma-' . trH($valor); ?>
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="<?= $idCampo ?>"
                                            name="sintomas[]" value="<?= trH($valor) ?>"
                                            <?= in_array($valor, $sintomasMarcados, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?= $idCampo ?>"><?= trH($rotulo) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($erros['sintomas'])): ?>
                            <div class="text-danger small mt-2"><?= trH($erros['sintomas']) ?></div>
                        <?php endif; ?>
                    </fieldset>
                </div>

                <!-- Observações -->
                <div class="col-12">
                    <label for="observacoes" class="form-label fw-bold mb-1">
                        Observações da enfermeira <span class="text-muted fw-normal small">(opcional)</span>
                    </label>
                    <div class="form-text mt-0 mb-1">Como o aluno chegou, o que ele relatou e o que foi feito.</div>
                    <textarea class="form-control<?= trInvalido($erros, 'observacoes') ?>" id="observacoes" name="observacoes"
                        rows="4" maxlength="<?= $max ?>" placeholder="Descreva o estado do aluno..."
                        data-contador="contador-observacoes"><?= trH($form['observacoes'] ?? '') ?></textarea>
                    <?php trErro($erros, 'observacoes'); ?>
                    <div class="form-text text-end" id="contador-observacoes"></div>
                </div>

                <!-- Botões -->
                <div class="col-12 d-flex flex-column flex-sm-row justify-content-between gap-2">
                    <a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>"
                        class="btn btn-light border-0 text-success fw-bold px-4 tr-psico__cancel">
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