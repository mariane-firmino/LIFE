<?php
// =====================================================================
// VIEW: GERENCIAR PERFIS  ->  App/Views/admin/perfis.php
//
// Três abas:
//   1. Aguardando ativação : servidores inativos (ativar ou recusar)
//   2. Todos os servidores : lista completa (ativar/desativar)
//   3. Adicionar servidor  : cadastro feito pelo admin (já nasce ativo)
//
// Os dados vêm do Controller Admin::perfis() / adicionarServidor().
// =====================================================================

// Só usuários logados (o Controller já confere se é admin).
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/admin/menuAdmin.php';

// CSS adicional das páginas do administrador.
echo '<link rel="stylesheet" href="' . URL . '/css/admin.css">';


// ---------------------------------------------------------------------
// FUNÇÕES AUXILIARES
// ---------------------------------------------------------------------

// Escapa texto para HTML.
if (!function_exists('h')) {
    function h($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

// Botão de ação (ativar / desativar / recusar). Cada um é um pequeno
// formulário POST, com confirmação antes de enviar.
//   $url      : endereço da ação
//   $voltar   : aba que deve reabrir depois (aba-pendentes | aba-todos)
// O texto do confirm() passa por json_encode: nomes com apóstrofo ou
// aspas (digitados pelo próprio servidor no cadastro) não quebram o
// JavaScript nem permitem injetar código.
if (!function_exists('admBotao')) {
    function admBotao($url, $voltar, $texto, $classe, $icone, $confirma)
    { ?>
        <form method="post" action="<?= h($url) ?>" class="d-inline"
            onsubmit="return confirm(<?= h(json_encode($confirma, JSON_UNESCAPED_UNICODE)) ?>);">
            <input type="hidden" name="voltar" value="<?= h($voltar) ?>">
            <button type="submit" class="btn btn-sm <?= $classe ?>">
                <i class="bi <?= $icone ?>" aria-hidden="true"></i> <?= $texto ?>
            </button>
        </form>
    <?php }
}

// Valor de um campo do formulário "Adicionar servidor" (vazio se não houver).
if (!function_exists('admValor')) {
    function admValor($form, $campo)
    {
        return h($form[$campo] ?? '');
    }
}

// Classe "is-invalid" + mensagem de erro de um campo.
if (!function_exists('admInvalido')) {
    function admInvalido($form, $campo)
    {
        return !empty($form[$campo . '_erro']) ? ' is-invalid' : '';
    }
}
if (!function_exists('admErro')) {
    function admErro($form, $campo)
    {
        if (!empty($form[$campo . '_erro'])) {
            echo '<div class="invalid-feedback d-block">' . h($form[$campo . '_erro']) . '</div>';
        }
    }
}


// ---------------------------------------------------------------------
// DADOS DO CONTROLLER
// ---------------------------------------------------------------------
$servidores = $dados['servidores'];
$pendentes  = $dados['pendentes'];
$funcoes    = $dados['funcoes'];
$form       = $dados['form'] ?? [];                         // formulário com erros (ou vazio)
$abaAtiva   = $dados['abaAtiva'] ?? 'aba-pendentes';        // aba que abre primeiro

// Lista das abas: id, título, ícone e quantidade (selo).
$abas = [
    ['aba-pendentes', 'Aguardando ativação', 'bi-hourglass-split', count($pendentes)],
    ['aba-todos', 'Todos os servidores', 'bi-people-fill', count($servidores)],
    ['aba-adicionar', 'Adicionar servidor', 'bi-person-plus-fill', null],
];

// A aba "Adicionar" fica vermelha quando o formulário voltou com erro.
$formComErro = $abaAtiva === 'aba-adicionar' && !empty($form);
?>
<div class="layout">

    <!-- ===================================== Título ======================================= -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <div class="col-8 linha-verde">
                <h1>Gerenciar Perfis</h1>
                <p>Ative os cadastros de servidores ou cadastre um novo perfil.</p>
            </div>
            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
        </div>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/admin/home">Painel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar Perfis</li>
        </ol>
    </nav>

    <div class="container text-start mb-5" id="areaAbasAdmin">

        <!-- Mensagens das ações (ativado, recusado, cadastrado...) -->
        <?php Sessao::mensagem('admin'); ?>

        <!-- ============================= MENU DAS ABAS ============================= -->
        <ul class="nav nav-tabs flex-nowrap overflow-x-auto" id="abasAdmin" role="tablist">
            <?php foreach ($abas as [$aid, $titulo, $icone, $qtd]): ?>
                <li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link <?= $aid === $abaAtiva ? 'active' : '' ?> <?= ($aid === 'aba-adicionar' && $formComErro) ? 'aba-erro' : '' ?>"
                        id="<?= $aid ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $aid ?>"
                        role="tab" aria-controls="<?= $aid ?>"
                        aria-selected="<?= $aid === $abaAtiva ? 'true' : 'false' ?>">
                        <i class="bi <?= $icone ?> me-1" aria-hidden="true"></i><?= $titulo ?>
                        <?php if ($qtd !== null): ?>
                            <!-- Quantidade de itens da aba (amarelo se há pendências) -->
                            <span class="badge <?= ($aid === 'aba-pendentes' && $qtd > 0) ? 'text-bg-warning' : 'text-bg-secondary' ?> ms-1">
                                <?= (int) $qtd ?>
                            </span>
                        <?php endif; ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom bg-body-tertiary p-3 p-lg-4">

            <!-- ============ ABA 1: AGUARDANDO ATIVAÇÃO ============ -->
            <div class="tab-pane <?= $abaAtiva === 'aba-pendentes' ? 'show active' : '' ?>" id="aba-pendentes"
                role="tabpanel" aria-labelledby="aba-pendentes-tab" tabindex="0">

                <p class="text-muted small">
                    Todo servidor que se cadastra começa <b>inativo</b> e não consegue entrar no sistema.
                    Ative para liberar o acesso ou recuse para apagar o cadastro.
                </p>

                <?php if (!$pendentes): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>
                        Nenhum cadastro aguardando ativação.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-success">Nome</th>
                                    <th scope="col" class="text-success">Função</th>
                                    <th scope="col" class="text-success">SIAPE</th>
                                    <th scope="col" class="text-success">E-mail</th>
                                    <th scope="col" class="text-success">Cadastro</th>
                                    <th scope="col" class="text-success text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendentes as $s): ?>
                                    <tr>
                                        <td class="text-break"><b><?= h($s->nome) ?></b></td>
                                        <td><?= h($s->funcao) ?></td>
                                        <td><?= h($s->siape) ?></td>
                                        <td class="text-break"><?= h($s->email) ?></td>
                                        <td class="text-muted text-nowrap"><?= date('d/m/Y', strtotime($s->criado_em)) ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php
                                            admBotao(URL . '/admin/alterarStatus/' . (int) $s->id . '/ativo', 'aba-pendentes',
                                                'Ativar', 'btn-success', 'bi-check-lg',
                                                'Ativar o acesso de ' . $s->nome . '?');
                                            admBotao(URL . '/admin/recusarCadastro/' . (int) $s->id, 'aba-pendentes',
                                                'Recusar', 'btn-outline-danger', 'bi-x-lg',
                                                'Recusar e APAGAR o cadastro de ' . $s->nome . '? Esta ação não pode ser desfeita.');
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============ ABA 2: TODOS OS SERVIDORES ============ -->
            <div class="tab-pane <?= $abaAtiva === 'aba-todos' ? 'show active' : '' ?>" id="aba-todos"
                role="tabpanel" aria-labelledby="aba-todos-tab" tabindex="0">

                <!-- Filtro: esconde as linhas que não contêm o texto digitado -->
                <div class="mb-3" style="max-width: 360px;">
                    <label for="filtroServidores" class="form-label small mb-1">Filtrar servidores</label>
                    <input type="search" id="filtroServidores" class="form-control form-control-sm"
                        placeholder="Nome, e-mail, SIAPE ou função">
                </div>

                <?php if (!$servidores): ?>
                    <p class="text-muted mb-0">Nenhum servidor cadastrado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabelaServidores">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-success">Nome</th>
                                    <th scope="col" class="text-success">Função</th>
                                    <th scope="col" class="text-success">SIAPE</th>
                                    <th scope="col" class="text-success">E-mail</th>
                                    <th scope="col" class="text-success">Telefone</th>
                                    <th scope="col" class="text-success">Status</th>
                                    <th scope="col" class="text-success text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servidores as $s): ?>
                                    <tr>
                                        <td class="text-break"><b><?= h($s->nome) ?></b></td>
                                        <td><?= h($s->funcao) ?></td>
                                        <td><?= h($s->siape) ?></td>
                                        <td class="text-break"><?= h($s->email) ?></td>
                                        <td class="text-nowrap"><?= $s->telefone ? h($s->telefone) : '<span class="text-muted">--</span>' ?></td>
                                        <td>
                                            <?php if ($s->status === 'ativo'): ?>
                                                <span class="badge text-bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <?php
                                            // Mostra só a ação que faz sentido para o status atual.
                                            if ($s->status === 'ativo') {
                                                admBotao(URL . '/admin/alterarStatus/' . (int) $s->id . '/inativo', 'aba-todos',
                                                    'Desativar', 'btn-outline-secondary', 'bi-slash-circle',
                                                    'Desativar o acesso de ' . $s->nome . '?');
                                            } else {
                                                admBotao(URL . '/admin/alterarStatus/' . (int) $s->id . '/ativo', 'aba-todos',
                                                    'Ativar', 'btn-success', 'bi-check-lg',
                                                    'Ativar o acesso de ' . $s->nome . '?');
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============ ABA 3: ADICIONAR SERVIDOR ============ -->
            <div class="tab-pane <?= $abaAtiva === 'aba-adicionar' ? 'show active' : '' ?>" id="aba-adicionar"
                role="tabpanel" aria-labelledby="aba-adicionar-tab" tabindex="0">

                <p class="text-muted small">
                    O perfil criado aqui já fica <b>ativo</b>. Informe uma senha inicial e peça ao servidor para trocá-la.
                </p>

                <form class="row g-3" method="post" action="<?= URL ?>/admin/adicionarServidor" novalidate>

                    <div class="col-md-6">
                        <label for="admNome" class="form-label">Nome completo <span class="text-danger">*</span></label>
                        <input type="text" id="admNome" name="nome" maxlength="225" required
                            class="form-control<?= admInvalido($form, 'nome') ?>" value="<?= admValor($form, 'nome') ?>">
                        <?php admErro($form, 'nome'); ?>
                    </div>

                    <div class="col-md-6">
                        <label for="admEmail" class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" id="admEmail" name="email" maxlength="225" required
                            class="form-control<?= admInvalido($form, 'email') ?>" value="<?= admValor($form, 'email') ?>">
                        <?php admErro($form, 'email'); ?>
                    </div>

                    <div class="col-md-4">
                        <label for="admSiape" class="form-label">SIAPE <span class="text-danger">*</span></label>
                        <input type="text" id="admSiape" name="siape" inputmode="numeric" maxlength="8" required
                            class="form-control<?= admInvalido($form, 'siape') ?>" value="<?= admValor($form, 'siape') ?>">
                        <?php admErro($form, 'siape'); ?>
                    </div>

                    <div class="col-md-4">
                        <label for="admCpf" class="form-label">CPF <span class="text-danger">*</span></label>
                        <input type="text" id="admCpf" name="cpf" inputmode="numeric" maxlength="14"
                            placeholder="000.000.000-00" required
                            class="form-control<?= admInvalido($form, 'cpf') ?>" value="<?= admValor($form, 'cpf') ?>">
                        <?php admErro($form, 'cpf'); ?>
                    </div>

                    <div class="col-md-4">
                        <label for="admNasc" class="form-label">Data de nascimento <span class="text-danger">*</span></label>
                        <input type="date" id="admNasc" name="data_nascimento" max="<?= date('Y-m-d') ?>" required
                            class="form-control<?= admInvalido($form, 'data_nascimento') ?>"
                            value="<?= admValor($form, 'data_nascimento') ?>">
                        <?php admErro($form, 'data_nascimento'); ?>
                    </div>

                    <div class="col-md-6">
                        <label for="admFuncao" class="form-label">Função <span class="text-danger">*</span></label>
                        <select id="admFuncao" name="func_id" required class="form-select<?= admInvalido($form, 'func_id') ?>">
                            <option value="" disabled <?= empty($form['func_id']) ? 'selected' : '' ?>>Escolha a função</option>
                            <?php foreach ($funcoes as $f): ?>
                                <option value="<?= (int) $f->id ?>" <?= (($form['func_id'] ?? '') === (string) $f->id) ? 'selected' : '' ?>>
                                    <?= h($f->nome) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php admErro($form, 'func_id'); ?>
                    </div>

                    <div class="col-md-6">
                        <label for="admTel" class="form-label">Telefone (opcional)</label>
                        <input type="text" id="admTel" name="telefone" maxlength="15" inputmode="tel"
                            class="form-control<?= admInvalido($form, 'telefone') ?>" value="<?= admValor($form, 'telefone') ?>">
                        <?php admErro($form, 'telefone'); ?>
                    </div>

                    <div class="col-md-6">
                        <label for="admSenha" class="form-label">Senha inicial <span class="text-danger">*</span></label>
                        <input type="password" id="admSenha" name="senha" autocomplete="new-password" required
                            class="form-control<?= admInvalido($form, 'senha') ?>">
                        <div class="form-text">Mínimo de 8 caracteres.</div>
                        <?php admErro($form, 'senha'); ?>
                    </div>

                    <div class="col-md-6">
                        <label for="admSenha2" class="form-label">Confirmar senha <span class="text-danger">*</span></label>
                        <input type="password" id="admSenha2" name="confirmar_senha" autocomplete="new-password" required
                            class="form-control<?= admInvalido($form, 'confirmar_senha') ?>">
                        <?php admErro($form, 'confirmar_senha'); ?>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i> Cadastrar servidor
                        </button>
                    </div>
                </form>
            </div>

        </div><!-- /tab-content -->
    </div>

</div>

<!-- =====================================================================
     COMPORTAMENTO DA PÁGINA
     - Abre a aba indicada no endereço (#aba-todos) ao voltar de uma ação.
     - Mantém a aba no endereço para o F5 não perder o lugar.
     - Filtro da tabela "Todos os servidores".
     ===================================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ----- Abas -----
        var nav = document.getElementById('abasAdmin');
        if (nav && window.bootstrap) {
            if (location.hash) {
                try {
                    var alvo = nav.querySelector('[data-bs-target="' + location.hash + '"]');
                    if (alvo) {
                        bootstrap.Tab.getOrCreateInstance(alvo).show();
                        document.getElementById('areaAbasAdmin').scrollIntoView();
                    }
                } catch (e) { /* hash inválido: ignora */ }
            }
            nav.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (botao) {
                botao.addEventListener('shown.bs.tab', function (e) {
                    history.replaceState(null, '', e.target.getAttribute('data-bs-target'));
                });
            });
        }

        // ----- Filtro da tabela -----
        var filtro = document.getElementById('filtroServidores');
        var linhas = document.querySelectorAll('#tabelaServidores tbody tr');
        if (filtro) {
            filtro.addEventListener('input', function () {
                var termo = filtro.value.toLowerCase().trim();
                linhas.forEach(function (linha) {
                    linha.hidden = termo !== '' && linha.textContent.toLowerCase().indexOf(termo) === -1;
                });
            });
        }
    });
</script>

<?php include '../App/Views/footer.php' ?>