<?php
// =====================================================================
// VIEW: INFORMAÇÕES DO SISTEMA  ->  App/Views/admin/sistema.php
//
// Uma aba para cada lista (anos das turmas, cursos, turnos, tipos de
// escola...). Em cada lista o administrador pode:
//   - adicionar uma opção;
//   - editar o nome (os registros antigos são atualizados junto);
//   - ativar/desativar (desativada some dos formulários);
//   - excluir (só se nenhum aluno estiver usando).
//
// Os servidores só ESCOLHEM entre as opções ativas.
// Os dados vêm do Controller Admin::sistema().
// =====================================================================

// Só usuários logados (o Controller já confere se é admin).
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/admin/menuAdmin.php';

// CSS adicional das páginas do administrador.
echo '<link rel="stylesheet" href="' . URL . '/css/admin.css">';

// Escapa texto para HTML.
if (!function_exists('h')) {
    function h($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

// Listas (uma por aba). A primeira aba abre por padrão.
$grupos = $dados['grupos'];
$primeiro = array_key_first($grupos);
?>
<div class="layout">

    <!-- ===================================== Título ======================================= -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <div class="col-8 linha-verde">
                <h1>Informações do Sistema</h1>
                <p>Defina as opções que os servidores podem escolher nos formulários.</p>
            </div>
            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
        </div>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" class="m-3" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL ?>/admin/home">Painel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Informações do Sistema</li>
        </ol>
    </nav>

    <div class="container text-start mb-5" id="areaAbasAdmin">

        <!-- Mensagens das ações (adicionado, editado, excluído...) -->
        <?php Sessao::mensagem('admin'); ?>

        <!-- ============================= MENU DAS ABAS ============================= -->
        <ul class="nav nav-tabs flex-nowrap overflow-x-auto" id="abasAdmin" role="tablist">
            <?php foreach ($grupos as $chave => $grupo): ?>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link <?= $chave === $primeiro ? 'active' : '' ?>"
                        id="aba-<?= h($chave) ?>-tab" data-bs-toggle="tab" data-bs-target="#aba-<?= h($chave) ?>"
                        role="tab" aria-controls="aba-<?= h($chave) ?>"
                        aria-selected="<?= $chave === $primeiro ? 'true' : 'false' ?>">
                        <i class="bi <?= h($grupo['cfg']['icone']) ?> me-1" aria-hidden="true"></i><?= h($grupo['cfg']['rotulo']) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom bg-body-tertiary p-3 p-lg-4">

            <?php foreach ($grupos as $chave => $grupo):
                $cfg = $grupo['cfg'];
                $opcoes = $grupo['opcoes'];
                $uso = $grupo['uso'];       // ['valor' => quantos alunos usam]
                ?>
                <!-- ============ LISTA: <?= h($cfg['rotulo']) ?> ============ -->
                <div class="tab-pane <?= $chave === $primeiro ? 'show active' : '' ?>" id="aba-<?= h($chave) ?>"
                    role="tabpanel" aria-labelledby="aba-<?= h($chave) ?>-tab" tabindex="0">

                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi <?= h($cfg['icone']) ?> text-success fs-3" aria-hidden="true"></i>
                        <h2 class="h5 mb-0"><b><?= h($cfg['rotulo']) ?></b></h2>
                    </div>
                    <p class="text-muted small"><?= h($cfg['ajuda']) ?> Só as opções <b>ativas</b> aparecem para os servidores.</p>

                    <!-- Adicionar nova opção -->
                    <form method="post" action="<?= URL ?>/admin/adicionarOpcao/<?= h($chave) ?>"
                        class="row g-2 align-items-end bg-body rounded border p-3 mb-3">
                        <div class="col-sm-8 col-md-6">
                            <label for="novo-<?= h($chave) ?>" class="form-label small mb-1">
                                Nova opção: <?= h(mb_strtolower($cfg['singular'])) ?>
                            </label>
                            <input type="text" id="novo-<?= h($chave) ?>" name="valor" required
                                maxlength="<?= (int) $cfg['max'] ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <button type="submit" class="btn btn-success btn-sm fw-bold w-100">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Adicionar
                            </button>
                        </div>
                    </form>

                    <!-- Tabela das opções -->
                    <?php if (!$opcoes): ?>
                        <p class="text-muted mb-0">Nenhuma opção cadastrada nesta lista.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-success"><?= h($cfg['singular']) ?></th>
                                        <th scope="col" class="text-success">Em uso</th>
                                        <th scope="col" class="text-success">Status</th>
                                        <th scope="col" class="text-success text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($opcoes as $o):
                                        $emUso = (int) ($uso[$o->valor] ?? 0);
                                        $ativa = (int) $o->ativo === 1;
                                        ?>
                                        <tr class="<?= $ativa ? '' : 'adm-linha-inativa' ?>">
                                            <td class="text-break"><b><?= h($o->valor) ?></b></td>
                                            <td class="text-nowrap">
                                                <?= $emUso ?> <?= $emUso === 1 ? 'aluno' : 'alunos' ?>
                                            </td>
                                            <td>
                                                <?php if ($ativa): ?>
                                                    <span class="badge text-bg-success">Ativa</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">Desativada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-nowrap">

                                                <!-- Editar: abre o modal já preenchido (veja o script no fim) -->
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditarOpcao"
                                                    data-acao="<?= URL ?>/admin/editarOpcao/<?= (int) $o->id ?>"
                                                    data-valor="<?= h($o->valor) ?>"
                                                    data-max="<?= (int) $cfg['max'] ?>"
                                                    data-uso="<?= $emUso ?>">
                                                    <i class="bi bi-pencil-fill" aria-hidden="true"></i> Editar
                                                </button>

                                                <!-- Ativar / desativar -->
                                                <form method="post" action="<?= URL ?>/admin/alternarOpcao/<?= (int) $o->id ?>" class="d-inline">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <?php if ($ativa): ?>
                                                            <i class="bi bi-eye-slash" aria-hidden="true"></i> Desativar
                                                        <?php else: ?>
                                                            <i class="bi bi-eye" aria-hidden="true"></i> Ativar
                                                        <?php endif; ?>
                                                    </button>
                                                </form>

                                                <!-- Excluir: só quando ninguém usa -->
                                                <?php if ($emUso === 0): ?>
                                                    <form method="post" action="<?= URL ?>/admin/excluirOpcao/<?= (int) $o->id ?>" class="d-inline"
                                                        onsubmit="return confirm(<?= h(json_encode('Excluir "' . $o->valor . '"?', JSON_UNESCAPED_UNICODE)) ?>);">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash-fill" aria-hidden="true"></i> Excluir
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <!-- Em uso: não pode excluir (só desativar) -->
                                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                                        title="Em uso por alunos: desative em vez de excluir">
                                                        <i class="bi bi-trash-fill" aria-hidden="true"></i> Excluir
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div><!-- /tab-content -->
    </div>

</div>

<!-- =====================================================================
     MODAL "EDITAR OPÇÃO"
     Um único modal serve para todas as opções: o script abaixo
     preenche o endereço da ação e o valor atual ao abrir.
     ===================================================================== -->
<div class="modal fade" id="modalEditarOpcao" tabindex="-1" aria-labelledby="modalEditarOpcaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="" id="formEditarOpcao" class="modal-content">

            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalEditarOpcaoLabel">
                    <i class="bi bi-pencil-fill text-success me-1" aria-hidden="true"></i> Editar opção
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <label for="editarValor" class="form-label">Novo nome</label>
                <input type="text" id="editarValor" name="valor" class="form-control" required>

                <!-- Aviso que muda conforme a opção esteja em uso ou não -->
                <div class="form-text" id="editarAviso"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success fw-bold">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- =====================================================================
     COMPORTAMENTO DA PÁGINA
     - Preenche o modal de edição.
     - Abre a aba indicada no endereço (#aba-curso) ao voltar de uma ação.
     - Mantém a aba no endereço para o F5 não perder o lugar.
     ===================================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ----- Modal de edição -----
        var modal = document.getElementById('modalEditarOpcao');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (evento) {
                var botao = evento.relatedTarget;        // botão "Editar" que abriu o modal
                var campo = document.getElementById('editarValor');
                var uso = parseInt(botao.getAttribute('data-uso'), 10) || 0;

                document.getElementById('formEditarOpcao').action = botao.getAttribute('data-acao');
                campo.value = botao.getAttribute('data-valor');
                campo.maxLength = botao.getAttribute('data-max');

                document.getElementById('editarAviso').textContent = uso > 0
                    ? 'Esta opção é usada por ' + uso + ' aluno(s). O nome deles será atualizado junto.'
                    : 'Nenhum aluno usa esta opção ainda.';
            });

            // Já deixa o cursor no campo quando o modal terminar de abrir.
            modal.addEventListener('shown.bs.modal', function () {
                document.getElementById('editarValor').focus();
            });
        }

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
    });
</script>

<?php include '../App/Views/footer.php' ?>