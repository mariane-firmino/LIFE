<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';

// ---------- helpers de formulário (valores sempre escapados na saída) ----------
if (!function_exists('ep_val')) {
    function ep_val($dados, $campo)
    {
        $v = (string) ($dados[$campo] ?? '');
        return htmlspecialchars(html_entity_decode($v, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ep_erro')) {
    function ep_erro($dados, $campo)
    {
        if (!empty($dados[$campo . '_erro'])) {
            echo '<div class="text-danger small mt-1">' . htmlspecialchars($dados[$campo . '_erro']) . '</div>';
        }
    }
}

if (!function_exists('ep_input')) {
    function ep_input($dados, $campo, $rotulo, $col = 'col-md-6', $extra = '', $tipo = 'text')
    {
        echo '<div class="' . $col . '">';
        echo '<label for="f_' . $campo . '" class="form-label">' . $rotulo . '</label>';
        echo '<input type="' . $tipo . '" class="form-control' . (!empty($dados[$campo . '_erro']) ? ' is-invalid' : '') . '" id="f_' . $campo . '" name="' . $campo . '" value="' . ep_val($dados, $campo) . '" ' . $extra . '>';
        ep_erro($dados, $campo);
        echo '</div>';
    }
}

if (!function_exists('ep_select')) {
    function ep_select($dados, $campo, $rotulo, array $lista, $col = 'col-md-6')
    {
        $atual = html_entity_decode((string) ($dados[$campo] ?? ''), ENT_QUOTES, 'UTF-8');

        // mantém um valor antigo que não está mais na lista, para não perdê-lo ao salvar
        if ($atual !== '' && !in_array($atual, $lista, true)) {
            $lista[] = $atual;
        }

        echo '<div class="' . $col . '">';
        echo '<label for="f_' . $campo . '" class="form-label">' . $rotulo . '</label>';
        echo '<select id="f_' . $campo . '" name="' . $campo . '" class="form-select' . (!empty($dados[$campo . '_erro']) ? ' is-invalid' : '') . '">';
        echo '<option value=""' . ($atual === '' ? ' selected' : '') . '>Não informado</option>';
        foreach ($lista as $op) {
            $o = htmlspecialchars($op, ENT_QUOTES, 'UTF-8');
            echo '<option value="' . $o . '"' . ($atual === $op ? ' selected' : '') . '>' . $o . '</option>';
        }
        echo '</select>';
        ep_erro($dados, $campo);
        echo '</div>';
    }
}

if (!function_exists('ep_nota')) {
    function ep_nota($dados, $campo, $rotulo)
    {
        $atual = (string) ($dados[$campo] ?? '');
        echo '<div class="col-12 col-lg-6"><label class="form-label">' . $rotulo . '</label><div class="row">';
        for ($i = 0; $i <= 10; $i++) {
            $checked = ($atual !== '' && (int) $atual === $i) ? ' checked' : '';
            echo '<div class="col-auto form-check"><input class="form-check-input" type="radio" name="' . $campo . '" id="' . $campo . $i . '" value="' . $i . '"' . $checked . '>';
            echo '<label class="form-check-label" for="' . $campo . $i . '">' . $i . '</label></div>';
        }
        echo '</div>';
        ep_erro($dados, $campo);
        echo '</div>';
    }
}

$simNao = ['Sim', 'Não'];
$simNaoNs = ['Sim', 'Não', 'Não sei'];
$id = (int) $dados['aluno_id'];
?>
<div class="layout">
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <div class="col-8 linha-verde">
                <h1>Editar Perfil</h1>
                <p>Faça a edição do perfil do estudante aqui.</p>
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
            <li class="breadcrumb-item active" aria-current="page">Editar Perfil</li>
        </ol>
    </nav>

    <div class="container mb-5">

        <?php if (!empty($dados['erro_geral'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($dados['erro_geral']) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= URL ?>/alunos/editarPerfil/<?= $id ?>" novalidate>

            <!-- ============ ABAS ============
                 Cada parte do perfil fica em uma aba. O botão "Editar" de cada
                 aba do perfil chega aqui com #aba-saude, #aba-educacao...
                 e o script no fim da página abre a aba certa.
                 Todos os campos continuam no mesmo formulário (são enviados juntos). -->
            <?php
            $abasEdicao = [
                ['dados',    'Dados pessoais',     'bi-person-fill'],
                ['educacao', 'Educação formal',    'bi-mortarboard-fill'],
                ['saude',    'Histórico de saúde', 'bi-heart-fill'],
                ['habitos',  'Hábitos de vida',    'bi-activity'],
            ];
            ?>
            <ul class="nav nav-tabs" id="abasEdicao" role="tablist">
                <?php foreach ($abasEdicao as $i => [$aid, $titulo, $icone]): ?>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                            id="edicao-<?= $aid ?>-tab"
                            data-bs-toggle="tab" data-bs-target="#aba-<?= $aid ?>"
                            role="tab" aria-controls="aba-<?= $aid ?>"
                            aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                            <i class="bi <?= $icone ?> me-1" aria-hidden="true"></i><?= $titulo ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">

            <!-- ============ DADOS PESSOAIS + CONTATO ============ -->
            <div class="tab-pane show active" id="aba-dados" role="tabpanel" aria-labelledby="edicao-dados-tab" tabindex="0">
            <div class="row g-4 mb-4">

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="text-success fw-bold mb-4">
                                <i class="bi bi-person-fill text-success"></i> Dados Pessoais
                            </h5>
                            <div class="row g-3">
                                <?php
                                ep_input($dados, 'nome', 'Nome completo', 'col-md-6', 'maxlength="225"');
                                ep_input($dados, 'data_nascimento', 'Data de nascimento', 'col-md-6', '', 'date');
                                ep_select($dados, 'sexo', 'Sexo biológico', ['Masculino', 'Feminino', 'Outro']);
                                ep_input($dados, 'matricula', 'Matrícula', 'col-md-6', 'maxlength="13"');
                                ep_input($dados, 'rg', 'RG', 'col-md-6', 'maxlength="14" data-mascara="rg"');
                                ep_select($dados, 'curso', 'Curso', $dados['listas']['curso'] ?? []);
                                ep_select($dados, 'turno', 'Turno', $dados['listas']['turno'] ?? [], 'col-md-6');
                                ep_select($dados, 'ano_turma', 'Ano da turma', $dados['listas']['ano_turma'] ?? [], 'col-md-6');
                                ep_select($dados, 'tipo_sanguineo', 'Tipo sanguíneo', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'col-md-4');
                                ep_input($dados, 'altura', 'Altura (m)', 'col-md-4', 'maxlength="4" inputmode="numeric" placeholder="Ex: 1,70"');
                                ep_input($dados, 'peso', 'Peso (kg)', 'col-md-4', 'maxlength="6" inputmode="decimal" placeholder="Ex: 65,5"');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="text-success fw-bold mb-4">
                                <i class="bi bi-telephone-fill text-success"></i> Contato
                            </h5>
                            <div class="row g-3">
                                <?php
                                ep_input($dados, 'telefone_aluno', 'Telefone do aluno (WhatsApp)', 'col-12', 'maxlength="15" inputmode="tel" data-mascara="telefone" placeholder="(69) 99999-9999"');
                                ep_input($dados, 'nome_responsavel', 'Nome do responsável', 'col-12', 'maxlength="100"');
                                ep_input($dados, 'telefone_responsavel', 'Telefone do responsável (WhatsApp)', 'col-12', 'maxlength="15" inputmode="tel" data-mascara="telefone" placeholder="(69) 99999-9999"');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            </div><!-- /aba-dados -->

            <!-- ============ EDUCAÇÃO FORMAL ============ -->
            <div class="tab-pane" id="aba-educacao" role="tabpanel" aria-labelledby="edicao-educacao-tab" tabindex="0">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="text-success fw-bold mb-4">
                        <i class="bi bi-mortarboard-fill text-success"></i> Educação Formal
                    </h5>
                    <div class="row g-3">
                        <?php
                        ep_select($dados, 'tipo_escola', 'Tipo de escola que estudou antes do IFRO', $dados['listas']['tipo_escola'] ?? []);
                        ep_select($dados, 'motivo_ifro', 'Por que escolheu o IFRO para estudar?', $dados['listas']['motivo_ifro'] ?? []);
                        ep_select($dados, 'ja_reprovou', 'Já reprovou antes?', $simNao);
                        ep_input($dados, 'quantas_vezes', 'Quantas vezes reprovou?', 'col-md-6', 'maxlength="10"');
                        ep_select($dados, 'estuda_fora', 'Estuda fora do horário de aula? (em casa)', $simNao);
                        ep_input($dados, 'quanto_tempo', 'Quanto tempo reserva para estudar fora da escola?', 'col-md-6', 'maxlength="50"');
                        ep_nota($dados, 'nota_curso', 'Em uma escala (0-10), o quanto gosta do curso?');
                        ep_nota($dados, 'nota_motivacao', 'Em uma escala (0-10), o quanto se sente motivado(a) para estudar?');
                        ep_select($dados, 'atendimento', 'Comparece ao atendimento individual com o professor?', $simNao);
                        ep_input($dados, 'atendimento_porque', 'Por quê? (caso a resposta anterior seja "Não")', 'col-md-6', 'maxlength="255"');
                        ?>
                    </div>
                </div>
            </div>
            </div><!-- /aba-educacao -->

            <!-- ============ HISTÓRICO DE SAÚDE ============ -->
            <div class="tab-pane" id="aba-saude" role="tabpanel" aria-labelledby="edicao-saude-tab" tabindex="0">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="text-success fw-bold mb-4">
                        <i class="bi bi-heart-fill text-success"></i> Histórico de Saúde
                    </h5>
                    <div class="row g-3">
                        <?php
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
                        <fieldset class="col-12 mb-3">
                            <legend class="fs-6 fw-bold">O aluno possui alguma condição especial? (opcional)</legend>
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-1">
                                <?php foreach ($opcoesCondicoes as $i => $opcao): ?>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="condicoes[]"
                                                id="condicao-edicao-<?= $i ?>"
                                                value="<?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= in_array($opcao, $condicoesMarcadas, true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="condicao-edicao-<?= $i ?>">
                                                <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2">
                                <label for="condicao_outra_edicao" class="form-label">Qual? (somente se marcou "Outra")</label>
                                <input type="text" name="condicao_outra" id="condicao_outra_edicao" maxlength="100"
                                    class="form-control<?= !empty($dados['condicao_outra_erro']) ? ' is-invalid' : '' ?>"
                                    value="<?= ep_val($dados, 'condicao_outra') ?>">
                                <div class="invalid-feedback"><?= htmlspecialchars($dados['condicao_outra_erro'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </fieldset>
                        <?php
                        ep_select($dados, 'acesso_saude', 'Como é realizado o acesso ao serviço de saúde?', $dados['listas']['acesso_saude'] ?? []);
                        ep_select($dados, 'alergia_alimentar', 'Possui alergia ou restrição alimentar?', $simNaoNs);
                        ep_input($dados, 'alergia_qual', 'Se sim, quais alimentos?', 'col-12', 'maxlength="255"');
                        ep_select($dados, 'doenca_cronica', 'Possui doença crônica ou pré-existente?', $simNaoNs);
                        ep_input($dados, 'doenca_qual', 'Se sim, qual doença?', 'col-md-6', 'maxlength="255"');
                        ep_select($dados, 'medicacao_continua', 'Faz uso contínuo de alguma medicação?', $simNao);
                        ep_input($dados, 'medicacao_qual', 'Qual medicação?', 'col-md-6', 'maxlength="255"');
                        ep_select($dados, 'acompanhamento_psi', 'Já realizou acompanhamento psicológico/psiquiátrico?', $simNao);
                        ep_select($dados, 'cartao_vacina', 'Tem cartão de vacina?', $simNao);
                        ep_select($dados, 'ja_internado', 'Já ficou internado(a)?', $simNao);
                        ep_input($dados, 'internado_motivo', 'Se sim, por qual motivo?', 'col-md-6', 'maxlength="255"');
                        ep_select($dados, 'ja_cirurgia', 'Já passou por alguma cirurgia?', $simNao);
                        ep_input($dados, 'cirurgia_qual', 'Se sim, qual cirurgia?', 'col-md-6', 'maxlength="255"');
                        ?>
                    </div>
                </div>
            </div>
            </div><!-- /aba-saude -->

            <!-- ============ HÁBITOS DE VIDA ============ -->
            <div class="tab-pane" id="aba-habitos" role="tabpanel" aria-labelledby="edicao-habitos-tab" tabindex="0">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="text-success fw-bold mb-4">
                        <i class="bi bi-activity text-success"></i> Hábitos de Vida
                    </h5>
                    <div class="row g-3">
                        <?php
                        ep_select($dados, 'padrao_sono', 'Padrão de sono', [
                            'Restaurador',
                            'Não restaurador',
                            'Insônia (perde o sono e não consegue voltar a dormir)',
                            'Sonolência diurna',
                        ]);
                        ep_select($dados, 'refeicoes_dia', 'Refeições por dia', ['3', 'Mais que 3', 'Menos que 3']);
                        ep_input($dados, 'alimentacao_habitual', 'O que costuma comer nas principais refeições?', 'col-12', 'maxlength="1000"');
                        ep_select($dados, 'fuma', 'Fuma algum tipo de cigarro?', $simNao);
                        ep_select($dados, 'bebida_alcoolica', 'Consome bebida alcoólica?', $simNao);
                        ep_select($dados, 'pais_sabem', 'Os pais sabem (caso tenha respondido "Sim" acima)?', $simNao);
                        ep_select($dados, 'drogas_ilicitas', 'Já fez uso ou teve contato com droga ilícita?', $simNao);
                        ep_input($dados, 'drogas_qual', 'Se sim, qual/quais?', 'col-md-6', 'maxlength="255"');
                        ep_select($dados, 'atividade_fisica', 'Pratica atividade física?', $simNao);
                        ep_select($dados, 'frequencia_atividade', 'Frequência semanal de atividade física', ['3x por semana', '(+) de 3x por semana', '(-) de 3x por semana']);
                        ep_select($dados, 'banhos_dia', 'Banhos por dia', ['1 por dia', '2 por dia', '3 por dia', 'Mais de 3 por dia'], 'col-md-12');
                        ?>
                    </div>
                </div>
            </div>
            </div><!-- /aba-habitos -->

            </div><!-- /tab-content -->

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>" class="btn btn-link text-danger text-decoration-none">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i> Salvar alterações
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    // Abre a aba certa da edição:
    // 1) se algum campo voltou com erro, abre a aba desse campo;
    // 2) senão, abre a aba pedida no endereço (#aba-saude...);
    // 3) senão, fica em "Dados pessoais".
    document.addEventListener('DOMContentLoaded', function () {
        var nav = document.getElementById('abasEdicao');
        if (!nav || !window.bootstrap) { return; }

        var abaComErro = document.querySelector('#abasEdicao ~ .tab-content .is-invalid, #abasEdicao ~ .tab-content .text-danger.small');
        var pane = abaComErro ? abaComErro.closest('.tab-pane') : null;
        var alvoId = pane ? '#' + pane.id : location.hash;

        if (alvoId) {
            try {
                var botao = nav.querySelector('[data-bs-target="' + alvoId + '"]');
                if (botao) {
                    // Troca sem animação ao abrir a página (evita a aba
                    // "Dados pessoais" piscar em verde antes da aba certa).
                    nav.classList.add('sem-transicao');
                    bootstrap.Tab.getOrCreateInstance(botao).show();
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () { nav.classList.remove('sem-transicao'); });
                    });
                }
            } catch (e) { /* hash inválido: ignora */ }
        }

        // Mantém a aba no endereço (F5 não perde o lugar).
        nav.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (b) {
            b.addEventListener('shown.bs.tab', function (e) {
                history.replaceState(null, '', e.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>

<?php include '../App/Views/footer.php'; ?>
