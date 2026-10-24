<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$alunos = $dados['alunos'] ?? [];
$total  = count($alunos);

// Opções dos filtros, montadas a partir dos próprios alunos
$cursos = [];
$turmas = [];

foreach ($alunos as $aluno) {
    if (!empty($aluno->curso)) {
        $cursos[(string) $aluno->curso] = true;
    }
    if (!empty($aluno->ano_turma)) {
        $turmas[(string) $aluno->ano_turma] = true;
    }
}

$cursos = array_keys($cursos);
$turmas = array_keys($turmas);
sort($cursos);
sort($turmas);

// Classe de cor do turno
function consultaAlunoClasseTurno($turno)
{
    $t = mb_strtolower((string) $turno);

    if (strpos($t, 'manh') !== false) return 'turno-manha';
    if (strpos($t, 'tarde') !== false) return 'turno-tarde';
    if (strpos($t, 'noite') !== false || strpos($t, 'notur') !== false) return 'turno-noite';

    return 'turno-outro';
}
?>

<!-- Bootstrap Icons (remova esta linha se já estiver carregado no menu.php ou no <head>) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <header class="home-topo">

            <div class="home-titulo">
                <h1>Consultar Aluno</h1>
                <p>Faça a consulta do estudante aqui.</p>
            </div>

            <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

        </header>

    </div>


    <main class="consultaAlunoPagina">

        <!-- FILTROS -->
        <section class="consultaAlunoFiltros" aria-label="Filtros">

            <div class="consultaAlunoCampoPesquisa">
                <i class="bi bi-search consultaAlunoIconePesquisa"></i>

                <input
                    type="search"
                    id="consultaAlunoBusca"
                    class="consultaAlunoInputPesquisa"
                    placeholder="Buscar por nome do aluno"
                    autocomplete="off"
                    aria-label="Buscar por nome do aluno">
            </div>

            <select id="consultaAlunoFiltroCurso" class="consultaAlunoSelect" aria-label="Filtrar por curso">
                <option value="">Todos os cursos</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= htmlspecialchars(mb_strtolower($curso)) ?>">
                        <?= htmlspecialchars($curso) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="consultaAlunoFiltroTurma" class="consultaAlunoSelect" aria-label="Filtrar por turma">
                <option value="">Todas as turmas</option>
                <?php foreach ($turmas as $turma): ?>
                    <option value="<?= htmlspecialchars($turma) ?>">
                        <?= htmlspecialchars($turma) ?>º ano
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" id="consultaAlunoLimpar" class="consultaAlunoLimpar" hidden>
                <i class="bi bi-x-circle"></i> Limpar
            </button>

        </section>


        <!-- RESULTADO -->
        <section class="consultaAlunoResultado" aria-live="polite">

            <div class="consultaAlunoResultadoIcone">
                <i class="bi bi-people-fill"></i>
            </div>

            <div class="consultaAlunoResultadoTexto">
                <strong class="consultaAlunoResultadoTitulo">Resultados da pesquisa</strong>

                <span class="consultaAlunoResultadoQuantidade" id="consultaAlunoContador">
                    <?= $total ?> <?= $total === 1 ? 'aluno encontrado' : 'alunos encontrados' ?>
                </span>
            </div>

        </section>


        <!-- TABELA -->
        <section class="consultaAlunoTabelaArea">

            <div class="consultaAlunoTabelaContainer">

                <table class="consultaAlunoTabela">

                    <thead class="consultaAlunoTabelaCabecalho">
                        <tr>
                            <th>Nome do Aluno</th>
                            <th>Turma</th>
                            <th>Curso</th>
                            <th>Turno</th>
                            <th>Data de Cadastro</th>
                            <th class="consultaAlunoColunaAcoes">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="consultaAlunoTabelaCorpo" id="consultaAlunoCorpo">

                        <?php foreach ($alunos as $aluno): ?>

                            <?php
                            $nome   = (string) ($aluno->nome ?? '');
                            $curso  = (string) ($aluno->curso ?? '');
                            $turma  = (string) ($aluno->ano_turma ?? '');
                            $turno  = (string) ($aluno->turno ?? '');
                            $inicial = $nome !== '' ? mb_strtoupper(mb_substr($nome, 0, 1)) : '?';
                            $fotoAluno = !empty($aluno->foto)
                                && is_file(dirname(__DIR__, 3) . '/Public/uploads/alunos/' . basename((string) $aluno->foto))
                                ? URL . '/uploads/alunos/' . basename((string) $aluno->foto)
                                : null;
                            ?>

                            <tr
                                class="consultaAlunoLinha"
                                data-nome="<?= htmlspecialchars(mb_strtolower($nome)) ?>"
                                data-curso="<?= htmlspecialchars(mb_strtolower($curso)) ?>"
                                data-turma="<?= htmlspecialchars($turma) ?>">

                                <!-- NOME -->
                                <td class="consultaAlunoCelula consultaAlunoCelulaNome" data-label="Aluno">
                                    <?php if ($fotoAluno !== null): ?>
                                        <img class="consultaAlunoAvatar"
                                            src="<?= htmlspecialchars($fotoAluno, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="Foto de <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="consultaAlunoAvatar" aria-hidden="true">
                                            <?= htmlspecialchars($inicial) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="consultaAlunoNomeTexto">
                                        <?= htmlspecialchars($nome) ?>
                                    </span>
                                </td>

                                <!-- TURMA -->
                                <td class="consultaAlunoCelula" data-label="Turma">
                                    <?= $turma !== '' ? htmlspecialchars($turma) . 'º ano' : '--' ?>
                                </td>

                                <!-- CURSO -->
                                <td class="consultaAlunoCelula" data-label="Curso">
                                    <?= $curso !== '' ? htmlspecialchars($curso) : '--' ?>
                                </td>

                                <!-- TURNO -->
                                <td class="consultaAlunoCelula" data-label="Turno">
                                    <?php if ($turno !== ''): ?>
                                        <span class="consultaAlunoTurno <?= consultaAlunoClasseTurno($turno) ?>">
                                            <?= htmlspecialchars($turno) ?>
                                        </span>
                                    <?php else: ?>
                                        --
                                    <?php endif; ?>
                                </td>

                                <!-- DATA DE CADASTRO -->
                                <td class="consultaAlunoCelula" data-label="Cadastro">
                                    <?= !empty($aluno->data_cadastro)
                                        ? date('d/m/Y', strtotime($aluno->data_cadastro))
                                        : '--/--/----' ?>
                                </td>

                                <!-- AÇÕES -->
                                <td class="consultaAlunoCelula consultaAlunoCelulaAcoes" data-label="">
                                    <a
                                        class="consultaAlunoBotaoPerfil"
                                        href="<?= URL ?>/alunos/visualizarPerfil/<?= (int) $aluno->id ?>"
                                        title="Visualizar perfil">
                                        <i class="bi bi-eye-fill"></i>
                                        <span>Ver perfil</span>
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        <!-- Linha de "nada encontrado" (controlada pelo JS) -->
                        <tr id="consultaAlunoVazio" class="consultaAlunoLinhaVazia"<?= $total > 0 ? ' hidden' : '' ?>>
                            <td colspan="6">
                                <i class="bi bi-search"></i>
                                <span id="consultaAlunoVazioTexto">
                                    <?= $total > 0 ? 'Nenhum aluno encontrado com esses filtros.' : 'Nenhum aluno cadastrado.' ?>
                                </span>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<script>
    (function () {
        const busca = document.getElementById('consultaAlunoBusca');
        const filtroCurso = document.getElementById('consultaAlunoFiltroCurso');
        const filtroTurma = document.getElementById('consultaAlunoFiltroTurma');
        const botaoLimpar = document.getElementById('consultaAlunoLimpar');
        const contador = document.getElementById('consultaAlunoContador');
        const vazio = document.getElementById('consultaAlunoVazio');
        const vazioTexto = document.getElementById('consultaAlunoVazioTexto');
        const linhas = document.querySelectorAll('#consultaAlunoCorpo .consultaAlunoLinha');

        // Remove acentos e deixa minúsculo ("João" encontra "joao")
        function normalizar(texto) {
            return (texto || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }

        function filtrar() {
            const termo = normalizar(busca.value);
            const curso = filtroCurso.value;
            const turma = filtroTurma.value;
            let visiveis = 0;

            linhas.forEach(function (linha) {
                const combina =
                    normalizar(linha.dataset.nome).indexOf(termo) !== -1 &&
                    (curso === '' || linha.dataset.curso === curso) &&
                    (turma === '' || linha.dataset.turma === turma);

                linha.hidden = !combina;

                if (combina) visiveis++;
            });

            contador.textContent =
                visiveis + (visiveis === 1 ? ' aluno encontrado' : ' alunos encontrados');

            // Mensagem de "nada encontrado" só se existirem alunos
            if (linhas.length > 0) {
                vazio.hidden = visiveis > 0;
                vazioTexto.textContent = 'Nenhum aluno encontrado com esses filtros.';
            }

            botaoLimpar.hidden = !(termo || curso || turma);
        }

        busca.addEventListener('input', filtrar);
        filtroCurso.addEventListener('change', filtrar);
        filtroTurma.addEventListener('change', filtrar);

        botaoLimpar.addEventListener('click', function () {
            busca.value = '';
            filtroCurso.value = '';
            filtroTurma.value = '';
            filtrar();
            busca.focus();
        });
    })();
</script>


<?php include '../App/Views/footer.php'; ?>