<?php
// =====================================================================
// VIEW: CONFIGURAÇÕES  ->  App/Views/paginas/configuracao.php
//
// Seções: Perfil e conta, Acessibilidade, Notificações, Segurança e
// Sobre o sistema. O menu da esquerda leva a cada seção.
//
// A seção de Acessibilidade é desenhada por Public/js/acessibilidade.js
// (o mesmo painel do botão flutuante que aparece nas outras páginas).
// =====================================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

echo '<link rel="stylesheet" href="' . URL . '/css/paginas/configuracao.css">';

$h = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};

$nome   = $_SESSION['usuario_nome'] ?? '';
$email  = $_SESSION['usuario_email'] ?? '';
$funcao = $_SESSION['usuario_funcao'] ?? '';
$prefs  = $dados['preferencias_email'] ?? null;

// Iniciais para o avatar (ex.: "Mariane Cardoso" -> "MC").
$partesNome = preg_split('/\s+/', trim($nome)) ?: [];
$iniciais = mb_strtoupper(
    mb_substr($partesNome[0] ?? '', 0, 1) . (count($partesNome) > 1 ? mb_substr(end($partesNome), 0, 1) : '')
);

// Seções do menu lateral: [id, título, ícone].
$secoes = [
    ['conta', 'Perfil e conta', 'bi-person-circle'],
    ['acessibilidade', 'Acessibilidade', 'bi-universal-access'],
    ['notificacoes', 'Notificações', 'bi-bell-fill'],
    ['seguranca', 'Segurança', 'bi-shield-lock-fill'],
    ['sobre', 'Sobre o sistema', 'bi-info-circle-fill'],
];

// Mensagem depois de salvar as notificações.
$mensagem = $_SESSION['configuracao_mensagem'] ?? '';
$tipoMensagem = $_SESSION['configuracao_tipo_mensagem'] ?? 'info';
unset($_SESSION['configuracao_mensagem'], $_SESSION['configuracao_tipo_mensagem']);
?>

<div class="layout">

    <!-- ===================================== Título ======================================= -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">
        <div class="row align-items-start">
            <div class="col-8 linha-verde">
                <h1>Configurações</h1>
                <p>Sua conta, acessibilidade, notificações e segurança em um só lugar.</p>
            </div>
            <div class="col-4 text-end">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="cfg">

            <!-- ===================================== Menu das seções ======================================= -->
            <nav class="cfg-menu" aria-label="Seções das configurações">
                <ul>
                    <?php foreach ($secoes as $i => [$sid, $stitulo, $sicone]): ?>
                        <li>
                            <a href="#<?= $sid ?>" class="cfg-menu-link<?= $i === 0 ? ' ativo' : '' ?>" data-cfg-secao="<?= $sid ?>">
                                <i class="bi <?= $sicone ?>" aria-hidden="true"></i> <?= $stitulo ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="cfg-conteudo">

                <!-- ===================================== PERFIL E CONTA ======================================= -->
                <section class="cfg-cartao" id="conta" aria-labelledby="titulo-conta">
                    <header class="cfg-cartao-topo">
                        <h2 id="titulo-conta"><i class="bi bi-person-circle" aria-hidden="true"></i> Perfil e conta</h2>
                        <p>Seus dados de acesso ao sistema.</p>
                    </header>

                    <div class="cfg-perfil">
                        <div class="cfg-avatar" aria-hidden="true"><?= $h($iniciais ?: '?') ?></div>
                        <div class="cfg-perfil-texto">
                            <b><?= $h($nome) ?></b>
                            <span><?= $h($funcao ?: 'Servidor(a)') ?></span>
                        </div>
                    </div>

                    <dl class="cfg-dados">
                        <div class="cfg-linha">
                            <dt>Nome</dt>
                            <dd><?= $h($nome) ?></dd>
                        </div>
                        <div class="cfg-linha">
                            <dt>E-mail</dt>
                            <dd class="text-break"><?= $h($email) ?></dd>
                        </div>
                        <div class="cfg-linha">
                            <dt>Função</dt>
                            <dd><?= $h($funcao ?: '--') ?></dd>
                        </div>
                    </dl>

                    <div class="cfg-acoes">
                        <a href="<?= URL ?>/usuarios/editarPerfil" class="btn btn-success fw-bold">
                            <i class="bi bi-pencil-fill me-1" aria-hidden="true"></i> Editar perfil
                        </a>
                        <a href="<?= URL ?>/usuarios/alterarSenha" class="btn btn-outline-success fw-bold">
                            <i class="bi bi-key-fill me-1" aria-hidden="true"></i> Alterar senha
                        </a>
                    </div>
                </section>

                <!-- ===================================== ACESSIBILIDADE ======================================= -->
                <section class="cfg-cartao" id="acessibilidade" aria-labelledby="titulo-acessibilidade">
                    <header class="cfg-cartao-topo">
                        <h2 id="titulo-acessibilidade"><i class="bi bi-universal-access" aria-hidden="true"></i> Acessibilidade e aparência</h2>
                        <p>As mudanças valem na hora, em todas as páginas, e ficam salvas neste navegador.</p>
                    </header>

                    <!-- Desenhado por Public/js/acessibilidade.js -->
                    <div data-a11y-painel="completo">
                        <p class="text-muted mb-0">Carregando as opções de acessibilidade...</p>
                    </div>

                    <div class="cfg-dica">
                        <i class="bi bi-lightbulb-fill" aria-hidden="true"></i>
                        <div>
                            Nas outras páginas, use o botão <b>redondo verde</b> no canto inferior esquerdo
                            ou o atalho <kbd>Alt</kbd> + <kbd>Shift</kbd> + <kbd>A</kbd>.
                            Para tradução em Libras, use o botão azul do <b>VLibras</b> à direita da tela.
                        </div>
                    </div>
                </section>

                <!-- ===================================== NOTIFICAÇÕES ======================================= -->
                <section class="cfg-cartao" id="notificacoes" aria-labelledby="titulo-notificacoes">
                    <header class="cfg-cartao-topo">
                        <h2 id="titulo-notificacoes"><i class="bi bi-bell-fill" aria-hidden="true"></i> Notificações</h2>
                        <p>Os avisos dentro do sistema (sininho) ficam sempre ligados. Aqui você escolhe o que também chega por e-mail.</p>
                    </header>

                    <?php if ($mensagem !== ''): ?>
                        <div class="alert alert-<?= $h($tipoMensagem) ?>" role="status"><?= $h($mensagem) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= URL ?>/paginas/salvarPreferenciasNotificacao">
                        <input type="hidden" name="csrf_token" value="<?= $h($dados['csrf_token']) ?>">

                        <div class="cfg-linha">
                            <div class="cfg-texto">
                                <label for="notificacaoConsulta">Consultas na agenda</label>
                                <p id="notificacaoConsulta-d">E-mail quando uma consulta for marcada.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input cfg-switch" type="checkbox" role="switch" id="notificacaoConsulta"
                                    name="email_consultas" value="1" aria-describedby="notificacaoConsulta-d"
                                    <?= !empty($prefs->email_consultas) ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="cfg-linha">
                            <div class="cfg-texto">
                                <label for="notificacaoTriagem">Novas triagens</label>
                                <p id="notificacaoTriagem-d">E-mail quando uma triagem for registrada.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input cfg-switch" type="checkbox" role="switch" id="notificacaoTriagem"
                                    name="email_triagens" value="1" aria-describedby="notificacaoTriagem-d"
                                    <?= !empty($prefs->email_triagens) ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <?php if (!empty($dados['is_admin'])): ?>
                            <div class="cfg-linha">
                                <div class="cfg-texto">
                                    <label for="notificacaoCadastros">Novos cadastros de servidores</label>
                                    <p id="notificacaoCadastros-d">Só para administradores: e-mail quando alguém pedir acesso.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input cfg-switch" type="checkbox" role="switch" id="notificacaoCadastros"
                                        name="email_cadastros" value="1" aria-describedby="notificacaoCadastros-d"
                                        <?= !empty($prefs->email_cadastros) ? 'checked' : '' ?>>
                                </div>
                            </div>
                        <?php endif; ?>

                        <p class="cfg-observacao">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            Os e-mails não trazem dados de saúde dos alunos. Avisos importantes da sua conta
                            (como troca de senha) são enviados mesmo com estas opções desligadas.
                        </p>

                        <div class="cfg-acoes">
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Salvar notificações
                            </button>
                        </div>
                    </form>
                </section>

                <!-- ===================================== SEGURANÇA ======================================= -->
                <section class="cfg-cartao" id="seguranca" aria-labelledby="titulo-seguranca">
                    <header class="cfg-cartao-topo">
                        <h2 id="titulo-seguranca"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Segurança</h2>
                        <p>Proteja o acesso à sua conta.</p>
                    </header>

                    <div class="cfg-lista">
                        <a class="cfg-item" href="<?= URL ?>/usuarios/alterarSenha">
                            <i class="bi bi-key-fill" aria-hidden="true"></i>
                            <span><b>Alterar senha</b><small>Troque a senha de acesso.</small></span>
                            <i class="bi bi-chevron-right cfg-item-seta" aria-hidden="true"></i>
                        </a>
                        <a class="cfg-item" href="<?= URL ?>/paginas/gerenciarDispositivos">
                            <i class="bi bi-laptop" aria-hidden="true"></i>
                            <span><b>Gerenciar dispositivos</b><small>Veja onde sua conta está conectada.</small></span>
                            <i class="bi bi-chevron-right cfg-item-seta" aria-hidden="true"></i>
                        </a>
                        <a class="cfg-item" href="<?= URL ?>/paginas/relatorio">
                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                            <span><b>Atividades recentes</b><small>O que foi feito no sistema.</small></span>
                            <i class="bi bi-chevron-right cfg-item-seta" aria-hidden="true"></i>
                        </a>
                        <button type="button" class="cfg-item cfg-item--perigo" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            <span><b>Encerrar sessão</b><small>Sair do sistema neste dispositivo.</small></span>
                            <i class="bi bi-chevron-right cfg-item-seta" aria-hidden="true"></i>
                        </button>
                    </div>
                </section>

                <!-- ===================================== SOBRE ======================================= -->
                <section class="cfg-cartao" id="sobre" aria-labelledby="titulo-sobre">
                    <header class="cfg-cartao-topo">
                        <h2 id="titulo-sobre"><i class="bi bi-info-circle-fill" aria-hidden="true"></i> Sobre o sistema</h2>
                        <p>Informações do LIFE e contato do suporte.</p>
                    </header>

                    <dl class="cfg-dados">
                        <div class="cfg-linha">
                            <dt>Sistema</dt>
                            <dd><?= $h(APP_NOME) ?> &bull; Enfermaria e triagem</dd>
                        </div>
                        <div class="cfg-linha">
                            <dt>Versão</dt>
                            <dd><?= $h(APP_VERSAO) ?></dd>
                        </div>
                        <div class="cfg-linha">
                            <dt>Instituição</dt>
                            <dd>Instituto Federal de Rondônia (IFRO)</dd>
                        </div>
                        <div class="cfg-linha">
                            <dt>Licença</dt>
                            <dd>Uso interno</dd>
                        </div>
                        <div class="cfg-linha">
                            <dt>Suporte</dt>
                            <dd>
                                <span class="user-select-all">enfermariaifro@gmail.com</span><br>
                                <span class="user-select-all">(69) 99926-2045</span>
                            </dd>
                        </div>
                    </dl>

                    <div class="cfg-acoes">
                        <a href="<?= URL ?>/paginas/sobre" class="btn btn-outline-success fw-bold">
                            <i class="bi bi-people-fill me-1" aria-hidden="true"></i> Conheça a equipe
                        </a>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>

<script>
    // Menu das seções: destaca a seção que está na tela.
    document.addEventListener('DOMContentLoaded', function () {
        var links = document.querySelectorAll('[data-cfg-secao]');
        if (!('IntersectionObserver' in window) || !links.length) return;

        var marcar = function (id) {
            links.forEach(function (l) {
                var ativo = l.getAttribute('data-cfg-secao') === id;
                l.classList.toggle('ativo', ativo);
                if (ativo) l.setAttribute('aria-current', 'true'); else l.removeAttribute('aria-current');
            });
        };

        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) { if (e.isIntersecting) marcar(e.target.id); });
        }, { rootMargin: '-30% 0px -60% 0px' });

        links.forEach(function (l) {
            var secao = document.getElementById(l.getAttribute('data-cfg-secao'));
            if (secao) observador.observe(secao);
        });

        // Abriu já numa seção (ex.: .../configuracao#acessibilidade).
        if (location.hash && document.getElementById(location.hash.slice(1))) marcar(location.hash.slice(1));
    });
</script>

<?php include '../App/Views/footer.php'; ?>
