<?php
// =====================================================================
// VIEW: GERENCIAR DISPOSITIVOS  ->  App/Views/paginas/gerenciarDispositivos.php
//
// Lista as sessões ativas do servidor (tabela sessao_dispositivo) e
// permite encerrar uma ou todas as outras. Estilo em
// Public/css/paginas/dispositivos.css.
// =====================================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';

echo '<link rel="stylesheet" href="' . URL . '/css/paginas/dispositivos.css">';

$h = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};

$dispositivos = $dados['dispositivos'] ?? [];
$csrf = $dados['csrf'] ?? '';

// "Agora mesmo", "Há 5 min", "Há 2 h", "03/11/2026 09:15"
// (os segundos vêm calculados pelo banco, evitando diferença de fuso PHP x MySQL)
$tempoRelativo = function ($d) {
    $diff = (int) $d->segundos_inativo;

    if ($diff < 60) return 'Agora mesmo';
    if ($diff < 3600) return 'Há ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Há ' . floor($diff / 3600) . ' h';
    return $d->atividade_formatada;
};

// Não há serviço de geolocalização: mostra o IP de forma amigável.
$descreverLocal = function ($ip) {
    if (in_array($ip, ['127.0.0.1', '::1'], true)) {
        return 'Este computador (localhost)';
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return 'Rede local · ' . $ip;
    }
    return 'IP ' . $ip;
};

$iconeDispositivo = function ($nome) {
    if (in_array($nome, ['iPhone', 'Celular Android'], true)) return 'bi-phone';
    if (in_array($nome, ['iPad', 'Tablet Android'], true)) return 'bi-tablet';
    if (in_array($nome, ['Mac', 'Chromebook'], true)) return 'bi-laptop';
    return 'bi-pc-display';
};

$total = count($dispositivos);
$outros = array_values(array_filter($dispositivos, fn($d) => !$d->atual));
$ultimaOutra = $outros[0] ?? null;
$ips = array_unique(array_map(fn($d) => $d->sess_ip, $dispositivos));
?>
<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">
        <div class="row align-items-center">
            <header class="home-topo">
                <div class="home-titulo">
                    <h1>Gerenciar Dispositivos</h1>
                    <p>Veja onde sua conta está conectada e encerre os acessos que não reconhecer.</p>
                </div>
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </header>
        </div>
    </div>

    <!-- BREADCRUMB -->
    <nav style="--bs-breadcrumb-divider: '>';" class="px-4 pb-2" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/paginas/configuracao">Configurações</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar Dispositivos</li>
        </ol>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="dsp">

            <?php Sessao::mensagem('dispositivos'); ?>

            <!-- ===================== Resumo ===================== -->
            <section class="dsp-resumo" aria-label="Resumo dos acessos">

                <div class="dsp-indicador">
                    <span class="dsp-indicador-icone" aria-hidden="true"><i class="bi bi-display"></i></span>
                    <div class="dsp-indicador-texto">
                        <span class="dsp-indicador-rotulo">Dispositivos ativos</span>
                        <span class="dsp-indicador-valor"><?= $total ?></span>
                        <span class="dsp-indicador-detalhe">
                            <?= $total === 1 ? 'Somente este dispositivo' : ($total - 1) . ' além deste' ?>
                        </span>
                    </div>
                </div>

                <div class="dsp-indicador">
                    <span class="dsp-indicador-icone" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
                    <div class="dsp-indicador-texto">
                        <span class="dsp-indicador-rotulo">Outro acesso recente</span>
                        <?php if ($ultimaOutra): ?>
                            <span class="dsp-indicador-valor"><?= $h($tempoRelativo($ultimaOutra)) ?></span>
                            <span class="dsp-indicador-detalhe">
                                <?= $h($ultimaOutra->sess_dispositivo . ' · ' . $ultimaOutra->sess_navegador) ?>
                            </span>
                        <?php else: ?>
                            <span class="dsp-indicador-valor">—</span>
                            <span class="dsp-indicador-detalhe">Nenhum outro acesso</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dsp-indicador">
                    <span class="dsp-indicador-icone" aria-hidden="true"><i class="bi bi-geo-alt"></i></span>
                    <div class="dsp-indicador-texto">
                        <span class="dsp-indicador-rotulo">Locais de acesso</span>
                        <span class="dsp-indicador-valor">
                            <?= count($ips) ?> <?= count($ips) === 1 ? 'endereço' : 'endereços' ?>
                        </span>
                        <span class="dsp-indicador-detalhe">
                            Atual: <?= $h($descreverLocal($_SERVER['REMOTE_ADDR'] ?? '')) ?>
                        </span>
                    </div>
                </div>

            </section>

            <!-- ===================== Sessões ativas ===================== -->
            <section class="dsp-cartao" aria-labelledby="titulo-sessoes">

                <header class="dsp-cartao-topo">
                    <div>
                        <h2 id="titulo-sessoes"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i> Sessões ativas</h2>
                        <p>Dispositivos em que sua conta está conectada agora.</p>
                    </div>

                    <button type="button" class="btn btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#modalEncerrar"
                            data-dsp-acao="<?= URL ?>/paginas/encerrarOutrosDispositivos"
                            data-dsp-titulo="Encerrar outras sessões?"
                            data-dsp-texto="Todos os outros dispositivos serão desconectados. Você continuará conectado neste."
                            data-dsp-botao="Encerrar todas"
                            <?= count($outros) ? '' : 'disabled' ?>>
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                        Encerrar outras sessões
                    </button>
                </header>

                <?php if (!$dispositivos): ?>
                    <div class="dsp-vazio">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        Nenhuma sessão ativa encontrada.
                    </div>
                <?php else: ?>
                    <ul class="dsp-lista">
                        <?php foreach ($dispositivos as $d): ?>
                            <?php $nome = $d->sess_dispositivo . ' · ' . $d->sess_navegador; ?>
                            <li class="dsp-item<?= $d->atual ? ' dsp-item--atual' : '' ?>">

                                <span class="dsp-item-icone" aria-hidden="true">
                                    <i class="bi <?= $iconeDispositivo($d->sess_dispositivo) ?>"></i>
                                </span>

                                <div class="dsp-item-info">
                                    <h3 class="dsp-item-nome">
                                        <?= $h($nome) ?>
                                        <?php if ($d->atual): ?>
                                            <span class="dsp-selo"><i class="bi bi-check2" aria-hidden="true"></i> Este dispositivo</span>
                                        <?php endif; ?>
                                    </h3>
                                    <ul class="dsp-item-meta">
                                        <li>
                                            <?php if ($d->atual || (int) $d->segundos_inativo < 300): ?>
                                                <span class="dsp-online" aria-hidden="true"></span>
                                            <?php else: ?>
                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                            <?php endif; ?>
                                            <?= $d->atual ? 'Ativo agora' : $h($tempoRelativo($d)) ?>
                                        </li>
                                        <li><i class="bi bi-geo-alt" aria-hidden="true"></i><?= $h($descreverLocal($d->sess_ip)) ?></li>
                                        <li><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>Entrou em <?= $h($d->criado_formatado) ?></li>
                                    </ul>
                                </div>

                                <div class="dsp-item-acao">
                                    <button type="button" class="btn btn-sm <?= $d->atual ? 'btn-outline-secondary' : 'btn-outline-danger' ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalEncerrar"
                                            data-dsp-acao="<?= URL ?>/paginas/encerrarDispositivo/<?= (int) $d->sess_id ?>"
                                            <?php if ($d->atual): ?>
                                                data-dsp-titulo="Sair deste dispositivo?"
                                                data-dsp-texto="Você será desconectado e voltará para a tela de login."
                                                data-dsp-botao="Sair"
                                            <?php else: ?>
                                                data-dsp-titulo="Encerrar esta sessão?"
                                                data-dsp-texto="<?= $h($nome) ?> será desconectado e precisará fazer login novamente."
                                                data-dsp-botao="Encerrar sessão"
                                            <?php endif; ?>
                                            aria-label="<?= $d->atual ? 'Sair deste dispositivo' : 'Encerrar sessão em ' . $h($nome) ?>">
                                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                                        <?= $d->atual ? 'Sair' : 'Encerrar' ?>
                                    </button>
                                </div>

                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="dsp-dica">
                    <i class="bi bi-lightbulb" aria-hidden="true"></i>
                    <span>
                        Não reconhece algum acesso? Encerre a sessão e
                        <a href="<?= URL ?>/usuarios/alterarSenha">altere sua senha</a>.
                        Os dispositivos aparecem aqui a partir do login.
                    </span>
                </div>

            </section>

        </div>
    </div>

</div>

<!-- ===================== Modal de confirmação ===================== -->
<div class="modal fade" id="modalEncerrar" tabindex="-1" aria-labelledby="modalEncerrarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" id="modalEncerrarForm">
            <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEncerrarTitulo">Encerrar sessão?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="modalEncerrarTexto"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="modalEncerrarBotao">Encerrar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Preenche o modal com os dados do botão que o abriu.
    document.getElementById('modalEncerrar').addEventListener('show.bs.modal', function (evento) {
        var botao = evento.relatedTarget;
        if (!botao) return;
        document.getElementById('modalEncerrarForm').action = botao.dataset.dspAcao;
        document.getElementById('modalEncerrarTitulo').textContent = botao.dataset.dspTitulo;
        document.getElementById('modalEncerrarTexto').textContent = botao.dataset.dspTexto;
        document.getElementById('modalEncerrarBotao').textContent = botao.dataset.dspBotao;
    });
</script>

<?php include '../App/Views/footer.php'; ?>
