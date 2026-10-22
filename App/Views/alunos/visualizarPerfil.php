<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

$aluno = $dados['aluno'];
$saude = $dados['saude'];
$historico = $dados['historico'] ?? [];

// Escapa para HTML (decodifica entidades gravadas por cadastros antigos) e mostra "Não informado" quando vazio
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

// "Sim" + detalhe (ex.: "Sim — poeira")
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

$nascimento = !empty($aluno->data_nascimento) ? date('d/m/Y', strtotime($aluno->data_nascimento)) : '';
$cadastro = !empty($aluno->data_cadastro) ? date('d/m/Y', strtotime($aluno->data_cadastro)) : '';
$idadeTexto = $dados['idade'] !== null ? $dados['idade'] . ' anos' : 'Idade não informada';
$id = (int) $aluno->id;
$atualizado = !empty($aluno->atualizado_em) ? date('d/m/Y H:i', strtotime($aluno->atualizado_em)) : '';

// Altura (m) e peso (kg) formatados
$alturaTexto = ($aluno->altura !== null && $aluno->altura !== '')
    ? number_format((float) $aluno->altura, 2, ',', '') . ' m'
    : '';
$pesoTexto = ($aluno->peso !== null && $aluno->peso !== '')
    ? rtrim(rtrim(number_format((float) $aluno->peso, 2, ',', ''), '0'), ',') . ' kg'
    : '';

// Foto do aluno (arquivo em Public/uploads/alunos/) ou imagem padrão
$fotoAluno = (!empty($aluno->foto) && is_file(dirname(__DIR__, 3) . '/Public/uploads/alunos/' . basename((string) $aluno->foto)))
    ? URL . '/uploads/alunos/' . basename((string) $aluno->foto)
    : URL . '/img/user.avif';

// Triagem pelo sexo do aluno
$rotaTriagem = ($aluno->sexo === 'Masculino') ? 'triagemMasculina' : 'triagemFeminina';

$campoSaude = function ($campo) use ($saude) {
    return $saude ? ($saude->$campo ?? null) : null;
};
?>
<div class="layout">
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            
            <div class="col-8 linha-verde">
                <h1>Perfil do Aluno</h1>
                <p>Visualize as informações do estudante aqui.</p>
            </div>
            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
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

            <a href="<?= URL ?>/triagens/<?= $rotaTriagem ?>/<?= $id ?>" class="btn btn-success fw-bold px-3 py-2">
                Adicionar Triagem
            </a>

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

                        <div class="position-relative flex-shrink-0">
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

                        <div style="min-width: 0;">
                            <h4 class="fw-bold mb-1 text-break"><?= vp($aluno->nome) ?></h4>
                            <p class="text-muted mb-3">
                                <?= $idadeTexto ?> &bull; Nascimento: <?= vp($nascimento) ?>
                            </p>
                            <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-2">
                                <span class="badge text-bg-light border fw-normal fs-6">
                                    <i class="bi bi-mortarboard-fill text-success me-1"></i>
                                    Turma: <?= vp(!empty($aluno->ano_turma) ? $aluno->ano_turma . 'º ano' : '', '--') ?>
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
                        </div>

                    </div>
                </div>

                <!-- Indicadores -->
                <div class="col-12 col-lg-7">
                    <div class="row row-cols-2 row-cols-md-3 g-2 text-center">
                        <?php
                        $indicadores = [
                            ['bi-rulers', $alturaTexto, 'Altura'],
                            ['bi-speedometer2', $pesoTexto, 'Peso'],
                            ['bi-droplet-fill', $aluno->tipo_sanguineo, 'Tipo sanguíneo'],
                            ['bi-gender-ambiguous', $aluno->sexo, 'Sexo biológico'],
                            ['bi-calendar-fill', $cadastro, 'Data de cadastro'],
                            ['bi-clock-history', $atualizado, 'Última atualização'],
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
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===================================== Dados pessoais + Informações rápidas ======================================= -->
    <div class="container text-start">
        <div class="row g-3 mb-3">

            <div class="col-12 col-lg-6">
                <div class="shadow-sm p-3 bg-body-tertiary rounded h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-person-fill text-success fs-3"></i>
                        <h5 class="mb-0"><b>Dados Pessoais</b></h5>
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
                        <div class="col-12 col-sm-6">
                            <small>Responsável</small>
                            <p class="text-break"><b><?= vp($aluno->responsavel) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Telefone do responsável</small>
                            <p><b><?= vpTelefone($dados['telefone_responsavel']) ?></b></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="shadow-sm p-3 bg-success-subtle rounded h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-heart-fill text-success fs-3"></i>
                        <h5 class="mb-0"><b>Informações Rápidas</b></h5>
                    </div>

                    <?php if (!$saude): ?>
                        <p class="text-muted">Este aluno ainda não possui histórico de saúde registrado.</p>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Alergias / restrições alimentares</b></small>
                                <p class="text-break"><b><?= vpSimQual($saude->alergia_alimentar, $saude->alergia_qual) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Doença crônica</b></small>
                                <p class="text-break"><b><?= vpSimQual($saude->doenca_cronica, $saude->doenca_qual) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Uso contínuo de medicação</b></small>
                                <p class="text-break"><b><?= vpSimQual($saude->medicacao_continua, $saude->medicacao_qual) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Acompanhamento psicológico</b></small>
                                <p><b><?= vp($saude->acompanhamento_psi) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Cartão de vacina</b></small>
                                <p><b><?= vp($saude->cartao_vacina) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Acesso ao serviço de saúde</b></small>
                                <p><b><?= vp($saude->acesso_saude) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Já foi internado(a)</b></small>
                                <p class="text-break"><b><?= vpSimQual($saude->ja_internado, $saude->internado_motivo) ?></b></p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <small class="text-success"><b>Já fez cirurgia</b></small>
                                <p class="text-break"><b><?= vpSimQual($saude->ja_cirurgia, $saude->cirurgia_qual) ?></b></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ===================================== Hábitos e educação ======================================= -->
    <?php if ($saude): ?>
    <div class="container text-start">
        <div class="row g-3 mb-3">

            <div class="col-12 col-lg-6">
                <div class="shadow-sm p-3 bg-body-tertiary rounded h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-activity text-success fs-3"></i>
                        <h5 class="mb-0"><b>Hábitos de Vida</b></h5>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <small>Padrão de sono</small>
                            <p class="text-break"><b><?= vp($saude->padrao_sono) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Refeições por dia</small>
                            <p><b><?= vp($saude->refeicoes_dia) ?></b></p>
                        </div>
                        <div class="col-12">
                            <small>Alimentação habitual</small>
                            <p class="text-break"><b><?= vp($saude->alimentacao_habitual) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Fuma</small>
                            <p><b><?= vp($saude->fuma) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Bebida alcoólica</small>
                            <p><b><?= vp($saude->bebida_alcoolica) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Pais sabem</small>
                            <p><b><?= vp($saude->pais_sabem) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Contato com drogas ilícitas</small>
                            <p class="text-break"><b><?= vpSimQual($saude->drogas_ilicitas, $saude->drogas_qual) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Atividade física</small>
                            <p><b><?= vpSimQual($saude->atividade_fisica, $saude->frequencia_atividade) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Banhos por dia</small>
                            <p><b><?= vp($saude->banhos_dia) ?></b></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="shadow-sm p-3 bg-body-tertiary rounded h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-mortarboard-fill text-success fs-3"></i>
                        <h5 class="mb-0"><b>Educação Formal</b></h5>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <small>Tipo de escola anterior</small>
                            <p><b><?= vp($saude->tipo_escola) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Motivo da escolha do IFRO</small>
                            <p class="text-break"><b><?= vp($saude->motivo_ifro) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Já reprovou</small>
                            <p><b><?= vpSimQual($saude->ja_reprovou, $saude->quantas_vezes !== null ? $saude->quantas_vezes . ' vez(es)' : '') ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Estuda fora do horário de aula</small>
                            <p><b><?= vpSimQual($saude->estuda_fora, $saude->quanto_tempo) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Gosta do curso (0–10)</small>
                            <p><b><?= vp($saude->nota_curso) ?></b></p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <small>Motivação para estudar (0–10)</small>
                            <p><b><?= vp($saude->nota_motivacao) ?></b></p>
                        </div>
                        <div class="col-12">
                            <small>Comparece ao atendimento individual com o professor</small>
                            <p class="text-break"><b>
                                <?php
                                $att = trim((string) $saude->atendimento);
                                echo ($att === 'Não' && trim((string) $saude->atendimento_porque) !== '')
                                    ? vp('Não — ' . $saude->atendimento_porque)
                                    : vp($att);
                                ?>
                            </b></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <!-- ===================================== Histórico de visitas ======================================= -->
    <div class="container text-start">
        <div class="row">
            <div class="col-12">
                <div class="shadow-sm p-3 mb-5 bg-body-tertiary rounded">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-clipboard-pulse text-success fs-3"></i>
                        <h3 class="text-success mb-0 fs-4"><b>Histórico de Visitas Recentes</b></h3>
                    </div>

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
            </div>
        </div>
    </div>

</div>
<?php include '../App/Views/footer.php' ?>
