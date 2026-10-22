<?php

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

// Inclui o menu
include '../App/Views/menu.php';

// Foto do usuário (ou imagem padrão)
if (!empty($_SESSION['usuario_foto'])) {
    $fotoPerfil = URL . '/uploads/perfis/' . $_SESSION['usuario_foto'];
} else {
    $fotoPerfil = URL . '/img/user.avif';
}

// Dados da sessão já tratados para exibição
$nome     = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário');
$email    = htmlspecialchars($_SESSION['usuario_email'] ?? '');
$telefone = htmlspecialchars($_SESSION['usuario_telefone'] ?? '');
$cpf      = htmlspecialchars($_SESSION['usuario_cpf'] ?? '');
$siape    = htmlspecialchars($_SESSION['usuario_siape'] ?? '');
$funcao   = htmlspecialchars($_SESSION['usuario_funcao'] ?? '');

$nascimento = !empty($_SESSION['usuario_dt_nascimento'])
    ? date('d/m/Y', strtotime($_SESSION['usuario_dt_nascimento']))
    : '';

// Mostra "Não informado" quando o campo estiver vazio
function perfilValor($valor)
{
    return $valor !== '' ? $valor : '<span class="perfil-vazio">Não informado</span>';
}
?>

<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <div class="row align-items-center">

            <div class="col-12 col-md-8 linha-verde">
                <h1>Perfil</h1>
                <p>Bem-vindo, <?= $nome ?>!</p>
            </div>

            <div class="col-12 col-md-4 text-center text-md-end mt-3 mt-md-0">
                <img
                    src="<?= URL ?>/img/life-menor.png"
                    class="logo-home"
                    alt="Logo Enfermaria">
            </div>

        </div>

    </div>


    <!-- CARD DO PERFIL -->
    <div class="container pb-5">

        <div class="perfil-card">

            <!-- PAINEL LATERAL -->
            <aside class="perfil-lateral">

                <div class="perfil-capa"></div>

                <img
                    src="<?= $fotoPerfil ?>"
                    class="perfil-foto"
                    alt="Foto de perfil"
                    onerror="this.onerror=null;this.src='<?= URL ?>/img/user.avif';">

                <h2 class="perfil-nome"><?= $nome ?></h2>

                <?php if ($funcao !== ''): ?>
                    <span class="perfil-funcao"><?= $funcao ?></span>
                <?php endif; ?>

                <div class="perfil-botoes">

                    <a class="btn btn-success button-user"
                       href="<?= URL ?>/usuarios/editarPerfil">
                        Editar Perfil
                    </a>

                    <a class="btn btn-outline-success button-user"
                       href="<?= URL ?>/usuarios/alterarSenha">
                        Alterar Senha
                    </a>

                </div>

            </aside>


            <!-- INFORMAÇÕES -->
            <section class="perfil-conteudo">

                <h3 class="perfil-titulo">Informações pessoais</h3>

                <div class="perfil-grid">

                    <div class="perfil-campo perfil-campo--full">
                        <span class="perfil-label">Nome completo</span>
                        <p><?= perfilValor($nome) ?></p>
                    </div>

                    <div class="perfil-campo perfil-campo--full">
                        <span class="perfil-label">E-mail</span>
                        <p><?= perfilValor($email) ?></p>
                    </div>

                    <div class="perfil-campo">
                        <span class="perfil-label">Data de nascimento</span>
                        <p><?= perfilValor($nascimento) ?></p>
                    </div>

                    <div class="perfil-campo">
                        <span class="perfil-label">Telefone / Celular</span>
                        <p><?= perfilValor($telefone) ?></p>
                    </div>

                    <div class="perfil-campo">
                        <span class="perfil-label">CPF</span>
                        <p><?= perfilValor($cpf) ?></p>
                    </div>

                    <div class="perfil-campo">
                        <span class="perfil-label">SIAPE</span>
                        <p><?= perfilValor($siape) ?></p>
                    </div>

                    <div class="perfil-campo perfil-campo--full">
                        <span class="perfil-label">Função</span>
                        <p><?= perfilValor($funcao) ?></p>
                    </div>

                </div>

            </section>

        </div>

    </div>

</div>

<?php include '../App/Views/footer.php'; ?>