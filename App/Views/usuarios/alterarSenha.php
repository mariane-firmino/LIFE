<?php

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

// Inclui o menu
include '../App/Views/menu.php';

// Mensagem de erro
if (isset($_SESSION['erro'])):
?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("<?= addslashes($_SESSION['erro']) ?>");
        });
    </script>

<?php
    unset($_SESSION['erro']);
endif;

?>

<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">

        <div class="row align-items-start">

            <div class="col-8 linha-verde">
                <h1>Perfil</h1>

                <p>
                    Bem-Vindo,
                    <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>!
                </p>
            </div>

            <div class="col-4 text-end">
                <img
                    src="<?= URL ?>/img/logo_enfermaria.jpeg"
                    class="logo-home"
                    alt="Logo">
            </div>

        </div>

    </div>


    <!-- FORMULÁRIO -->
    <div class="shadow p-3 mb-5 rounded position-absolute top-50 start-50 translate-middle formulario-senha">

        <div class="container text-justify">

            <div class="row row-cols-1">

                <div class="col">
                    <h4>Meus Dados - Alterar Senha</h4>
                </div>

            </div>


            <div class="row row-cols-1 border-top p-2">

                <form
                    action="<?= URL ?>/usuarios/salvarSenha"
                    method="POST">

                    <!-- SENHA ATUAL -->
                    <div class="mb-3">

                        <label for="senha" class="form-label">
                            Digite a senha atual <sup>*</sup>
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="senha"
                            name="senha_atual"
                            required>

                    </div>


                    <!-- NOVA SENHA -->
                    <div class="mb-3">

                        <label for="novaSenha" class="form-label">
                            Nova Senha <sup>*</sup>
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="novaSenha"
                            name="nova_senha"
                            required>

                    </div>


                    <!-- CONFIRMAR NOVA SENHA -->
                    <div class="mb-3">

                        <label for="confirmarSenha" class="form-label">
                            Confirmar Nova Senha <sup>*</sup>
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="confirmarSenha"
                            name="confirmar_senha"
                            required>

                    </div>


                    <!-- BOTÃO -->
                    <div class="mb-3 text-end">

                        <button
                            type="submit"
                            class="btn btn-success">
                            Salvar Alterações
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>


<?php include '../App/Views/footer.php'; ?>