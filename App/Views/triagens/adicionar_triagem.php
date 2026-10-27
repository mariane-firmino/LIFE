<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$tipo = $dados['tipo'];
$parte = $dados['parte'];                 // 'psicologica' ou 'fisica'
$perguntas = $dados['perguntas'];
$numeros = array_keys($perguntas);
$ultimaPergunta = end($numeros);
$rotaSalvar = $parte === 'fisica' ? 'salvarFisica' : 'salvarPsicologica';
$alunoId = (int) $dados['aluno_id'];

// A outra parte da triagem (para o link "Ir para a triagem ...").
$outraParte = $parte === 'fisica' ? 'psicologica' : 'fisica';
?>
<div class="layout">
    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <header class="home-topo">

            <div class="home-titulo">
                <h1><?= htmlspecialchars($dados['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Preencha a triagem de <?= htmlspecialchars($dados['aluno_nome'], ENT_QUOTES, 'UTF-8') ?>.</p>
            </div>

            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

        </header>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/consulta">Consultar Aluno</a></li>
            <li class="breadcrumb-item"><a href="<?= URL ?>/alunos/visualizarPerfil/<?= $alunoId ?>">Visualizar Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($dados['titulo'], ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </nav>

    <div class="container">
        <?php Sessao::mensagem('triagens'); ?>
    </div>

    <form action="<?= URL ?>/triagens/<?= $rotaSalvar ?>/<?= $alunoId ?>" method="POST">
        <div class="shadow p-3 mb-5 rounded">
            <div class="container text-justify">
                <div class="row g-4">
                    <div class="col-12">
                        <?php if ($parte === 'fisica'): ?>
                            <h3>Saúde Sexual e Reprodutiva de Adolescentes</h3>
                            <h5>Para <?= $tipo === 'masculina' ? 'meninos' : 'meninas' ?></h5>
                        <?php else: ?>
                            <h3>Situação Psicoemocional</h3>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                // Duas perguntas por linha.
                $posicao = 0;
                foreach ($perguntas as $numero => $pergunta):
                    $posicao++;
                    ?>
                    <?php if ($posicao % 2 === 1): ?>
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

                    <?php if ($numero === $ultimaPergunta || $posicao % 2 === 0): ?>
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

                <p class="text-end small mt-3 mb-0">
                    <a href="<?= URL ?>/triagens/<?= $outraParte ?>/<?= $alunoId ?>" class="text-success fw-bold">
                        Ir para a <?= mb_strtolower(TriagemPerguntas::PARTES[$outraParte]) ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </p>
            </div>
        </div>
    </form>
</div>

<?php include '../App/Views/footer.php'; ?>
