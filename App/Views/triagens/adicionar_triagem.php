<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$tipo = $dados['tipo'];
$perguntas = $dados['perguntas'];
$perguntaSexualInicial = 7;
$totalPerguntas = count($perguntas);
$rotaSalvar = $tipo === 'masculina' ? 'salvarTriagemMasculina' : 'salvarTriagemFeminina';
?>
<div class="layout">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <header class="home-topo">

            <div class="home-titulo">
                <h1>Triagem do Estudante</h1>
                <p>Preencha a triagem de <?= htmlspecialchars($dados['aluno_nome'], ENT_QUOTES, 'UTF-8') ?>.</p>
            </div>

            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

        </header>

    </div>

    <form action="<?= URL ?>/triagens/<?= $rotaSalvar ?>/<?= (int) $dados['aluno_id'] ?>" method="POST">
        <div class="shadow p-3 mb-5 rounded">
            <div class="container text-justify">
                <div class="row g-4">
                    <div class="col-12">
                        <h3>Situação Psicoemocional</h3>
                    </div>
                </div>

                <?php foreach ($perguntas as $numero => $pergunta): ?>
                    <?php if ($numero === $perguntaSexualInicial): ?>
                        <div class="row g-4 mt-4">
                            <div class="col-12">
                                <h3>Saúde Sexual e Reprodutiva de Adolescentes</h3>
                                <h5>Para <?= $tipo === 'masculina' ? 'meninos' : 'meninas' ?></h5>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($numero === 1 || $numero === 3 || $numero === 5 || $numero === 7 || $numero === 9 || $numero === 11 || $numero === 13 || $numero === 15): ?>
                        <div class="row g-4 mt-3">
                    <?php endif; ?>

                    <div class="col-12 col-md-6">
                        <label for="pergunta<?= (int) $numero ?>" class="form-label">
                            <?= htmlspecialchars($pergunta['pergunta'], ENT_QUOTES, 'UTF-8') ?>
                            <?= $pergunta['obrigatoria'] ? '<sub>*</sub>' : '' ?>
                        </label>

                        <?php if ($pergunta['tipo'] === 'select'): ?>
                            <select class="form-select" id="pergunta<?= (int) $numero ?>"
                                name="pergunta<?= (int) $numero ?>" <?= $pergunta['obrigatoria'] ? 'required' : '' ?>>
                                <option value="" selected>Selecione uma opção</option>
                                <?php foreach ($pergunta['opcoes'] as $opcao): ?>
                                    <option value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control" id="pergunta<?= (int) $numero ?>"
                                name="pergunta<?= (int) $numero ?>" maxlength="255"
                                <?= $pergunta['obrigatoria'] ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>

                    <?php if ($numero === $totalPerguntas || $numero % 2 === 0): ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="row g-4 mt-2">
                    <div class="col-12 col-md-6">
                        <button type="reset" class="btn btn-danger">Limpar informações</button>
                    </div>
                    <div class="col-12 col-md-6">
                        <button type="submit" class="btn btn-success w-100">Salvar informações</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include '../App/Views/footer.php'; ?>
