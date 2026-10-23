<?php

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

include '../App/Views/menu.php';

$usuario = $dados['usuario'];

$fotoPerfil = !empty($usuario->serv_foto)
    ? URL . '/uploads/perfis/' . $usuario->serv_foto
    : URL . '/img/user.avif';

?>

<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <div class="row align-items-center">

            <!-- CABEÇALHO -->
            <header class="home-topo">
                <div class="home-titulo">
                    <h1>Perfil</h1>
                    <p>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>!</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">
            </header>

        </div>

    </div>


    <!-- BREADCRUMB -->
    <nav style="--bs-breadcrumb-divider: '>';" class="px-4 pb-2" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/paginas/perfil">Perfil</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Editar Perfil</li>
        </ol>
    </nav>


    <!-- FORMULÁRIO -->
    <div class="container mt-4 mb-5">

        <form
            class="perfil-card"
            action="<?= URL ?>/usuarios/editarPerfil"
            method="POST"
            enctype="multipart/form-data">

            <!-- PAINEL LATERAL: FOTO E BOTÕES -->
            <aside class="perfil-lateral">

                <div class="perfil-capa"></div>

                <div class="perfil-foto-wrap">

                    <img
                        src="<?= htmlspecialchars($fotoPerfil) ?>"
                        id="fotoPreview"
                        class="perfil-foto"
                        alt="Foto do usuário"
                        onerror="this.onerror=null;this.src='<?= URL ?>/img/user.avif';">

                    <label for="foto" class="perfil-foto-editar" title="Alterar foto">
                        <i class="bi bi-pencil text-success"></i>
                    </label>

                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept="image/jpeg,image/png,image/webp"
                        hidden>

                </div>

                <p class="perfil-foto-dica">JPG, PNG ou WEBP</p>

                <div class="perfil-botoes">

                    <button type="submit" class="btn btn-success button-user">
                        Salvar alterações
                    </button>

                    <a href="<?= URL ?>/usuarios/alterarSenha"
                       class="btn btn-outline-success button-user">
                        Alterar senha
                    </a>

                </div>

            </aside>


            <!-- CAMPOS -->
            <section class="perfil-conteudo">

                <h3 class="perfil-titulo">Editar informações</h3>

                <div class="perfil-grid">

                    <div class="perfil-campo-form perfil-campo--full">
                        <label for="nome" class="perfil-label">Nome</label>
                        <input
                            type="text"
                            name="nome"
                            id="nome"
                            class="form-control"
                            placeholder="Nome completo"
                            autocomplete="name"
                            required
                            value="<?= htmlspecialchars($usuario->serv_nome ?? '') ?>">
                    </div>

                    <div class="perfil-campo-form">
                        <label for="email" class="perfil-label">E-mail</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="Email completo"
                            autocomplete="email"
                            required
                            value="<?= htmlspecialchars($usuario->serv_email ?? '') ?>">
                    </div>

                    <div class="perfil-campo-form">
                        <label for="data_nascimento" class="perfil-label">Data de nascimento</label>
                        <input
                            type="date"
                            name="data_nascimento"
                            id="data_nascimento"
                            class="form-control"
                            value="<?= htmlspecialchars($usuario->serv_dt_nascimento ?? '') ?>">
                    </div>

                    <div class="perfil-campo-form perfil-campo--full">
                        <label for="telefone" class="perfil-label">Telefone / Celular</label>
                        <input
                            type="text"
                            name="telefone"
                            id="telefone"
                            class="form-control"
                            placeholder="(00) 00000-0000"
                            maxlength="15"
                            inputmode="tel"
                            autocomplete="tel"
                            value="<?= htmlspecialchars($usuario->tele_numero ?? '') ?>">
                    </div>

                </div>

                <p class="perfil-aviso">
                    CPF, SIAPE e função não podem ser alterados por aqui.
                </p>

            </section>

        </form>

    </div>

</div>


<!-- PRÉVIA DA FOTO -->
<script>
    const campoFoto = document.getElementById('foto');

    if (campoFoto) {
        campoFoto.addEventListener('change', function (event) {
            const arquivo = event.target.files[0];

            if (arquivo) {
                const imagem = document.getElementById('fotoPreview');

                if (imagem.dataset.blob) {
                    URL.revokeObjectURL(imagem.dataset.blob);
                }

                const novaUrl = URL.createObjectURL(arquivo);
                imagem.dataset.blob = novaUrl;
                imagem.src = novaUrl;
            }
        });
    }
</script>


<!-- MÁSCARA DO TELEFONE -->
<script>
    const telefone = document.getElementById('telefone');

    function aplicarMascaraTelefone(input) {
        let valor = input.value.replace(/\D/g, '');

        // Máximo de 11 números
        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }

        if (valor.length > 10) {
            // Celular
            valor = valor.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
        } else {
            // Telefone fixo
            valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        }

        input.value = valor;
    }

    if (telefone) {
        telefone.addEventListener('input', function () {
            aplicarMascaraTelefone(this);
        });

        // Aplica a máscara ao abrir a página
        aplicarMascaraTelefone(telefone);
    }
</script>


<?php include '../App/Views/footer.php'; ?>