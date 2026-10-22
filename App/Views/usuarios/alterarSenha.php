<?php

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

// Inclui o menu
include '../App/Views/menu.php';

// Mensagem de erro (mostrada dentro da página, sem alert())
$mensagemErro = $_SESSION['erro'] ?? '';
unset($_SESSION['erro']);

?>

<div class="layout">

    <!-- CABEÇALHO -->
    <div class="container-fluid px-4 pt-5 pb-3">

        <div class="row align-items-center">

            <div class="col-12 col-md-8 linha-verde">
                <h1>Perfil</h1>
                <p>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>!</p>
            </div>

            <div class="col-12 col-md-4 text-center text-md-end mt-3 mt-md-0">
                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo Enfermaria">
            </div>

        </div>

    </div>


    <!-- BREADCRUMB -->
    <nav style="--bs-breadcrumb-divider: '>';" class="px-4 pb-2" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/paginas/perfil">Perfil</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Alterar Senha</li>
        </ol>
    </nav>


    <!-- FORMULÁRIO -->
    <div class="container mt-4 mb-5">

        <div class="senha-card">

            <div class="perfil-capa senha-capa"></div>

            <div class="senha-corpo">

                <h2 class="senha-titulo">Alterar senha</h2>
                <p class="senha-sub">Preencha os campos abaixo para definir uma nova senha.</p>

                <?php if ($mensagemErro !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($mensagemErro) ?>
                    </div>
                <?php endif; ?>

                <form
                    id="formSenha"
                    action="<?= URL ?>/usuarios/salvarSenha"
                    method="POST"
                    novalidate>

                    <!-- SENHA ATUAL -->
                    <div class="senha-campo">
                        <label for="senha" class="perfil-label">
                            Senha atual <sup>*</sup>
                        </label>

                        <div class="senha-input">
                            <input
                                type="password"
                                class="form-control"
                                id="senha"
                                name="senha_atual"
                                autocomplete="current-password"
                                required>

                            <button type="button" class="senha-mostrar" data-alvo="senha">
                                Mostrar
                            </button>
                        </div>
                    </div>


                    <!-- NOVA SENHA -->
                    <div class="senha-campo">
                        <label for="novaSenha" class="perfil-label">
                            Nova senha <sup>*</sup>
                        </label>

                        <div class="senha-input">
                            <input
                                type="password"
                                class="form-control"
                                id="novaSenha"
                                name="nova_senha"
                                autocomplete="new-password"
                                required>

                            <button type="button" class="senha-mostrar" data-alvo="novaSenha">
                                Mostrar
                            </button>
                        </div>

                        <!-- Medidor de força -->
                        <div class="senha-forca" aria-hidden="true">
                            <span id="forcaBarra"></span>
                        </div>
                        <small id="forcaTexto" class="senha-dica">
                            Use letras maiúsculas, minúsculas, números e símbolos.
                        </small>
                    </div>


                    <!-- CONFIRMAR NOVA SENHA -->
                    <div class="senha-campo">
                        <label for="confirmarSenha" class="perfil-label">
                            Confirmar nova senha <sup>*</sup>
                        </label>

                        <div class="senha-input">
                            <input
                                type="password"
                                class="form-control"
                                id="confirmarSenha"
                                name="confirmar_senha"
                                autocomplete="new-password"
                                required>

                            <button type="button" class="senha-mostrar" data-alvo="confirmarSenha">
                                Mostrar
                            </button>
                        </div>

                        <small id="confirmarTexto" class="senha-dica"></small>
                    </div>


                    <!-- BOTÕES -->
                    <div class="senha-botoes">

                        <a href="<?= URL ?>/paginas/perfil" class="btn btn-outline-success button-user">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success button-user">
                            Salvar alterações
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>


<script>
    (function () {
        const form = document.getElementById('formSenha');
        const nova = document.getElementById('novaSenha');
        const confirmar = document.getElementById('confirmarSenha');
        const barra = document.getElementById('forcaBarra');
        const forcaTexto = document.getElementById('forcaTexto');
        const confirmarTexto = document.getElementById('confirmarTexto');

        // Mostrar / ocultar senha
        document.querySelectorAll('.senha-mostrar').forEach(function (botao) {
            botao.addEventListener('click', function () {
                const campo = document.getElementById(botao.dataset.alvo);
                const mostrar = campo.type === 'password';

                campo.type = mostrar ? 'text' : 'password';
                botao.textContent = mostrar ? 'Ocultar' : 'Mostrar';
            });
        });

        // Força da senha (apenas visual)
        function calcularForca(valor) {
            let pontos = 0;

            if (valor.length >= 8) pontos++;
            if (/[a-z]/.test(valor) && /[A-Z]/.test(valor)) pontos++;
            if (/\d/.test(valor)) pontos++;
            if (/[^A-Za-z0-9]/.test(valor)) pontos++;

            return pontos;
        }

        const niveis = [
            { texto: 'Use letras maiúsculas, minúsculas, números e símbolos.', cor: '#d9534f', largura: '0%' },
            { texto: 'Senha fraca', cor: '#d9534f', largura: '25%' },
            { texto: 'Senha razoável', cor: '#f0ad4e', largura: '50%' },
            { texto: 'Senha boa', cor: '#5cb85c', largura: '75%' },
            { texto: 'Senha forte', cor: '#168653', largura: '100%' }
        ];

        nova.addEventListener('input', function () {
            const nivel = niveis[nova.value ? calcularForca(nova.value) || 1 : 0];

            barra.style.width = nivel.largura;
            barra.style.backgroundColor = nivel.cor;
            forcaTexto.textContent = nivel.texto;

            verificarConfirmacao();
        });

        // Confirmação
        function verificarConfirmacao() {
            if (!confirmar.value) {
                confirmarTexto.textContent = '';
                confirmarTexto.className = 'senha-dica';
                return true;
            }

            const iguais = nova.value === confirmar.value;

            confirmarTexto.textContent = iguais
                ? 'As senhas coincidem.'
                : 'As senhas não coincidem.';
            confirmarTexto.className = 'senha-dica ' + (iguais ? 'senha-ok' : 'senha-erro');

            return iguais;
        }

        confirmar.addEventListener('input', verificarConfirmacao);

        // Impede o envio se as senhas forem diferentes
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity() || nova.value !== confirmar.value) {
                event.preventDefault();
                form.classList.add('foi-validado');
                verificarConfirmacao();
                if (!confirmar.value) {
                    confirmarTexto.textContent = 'Confirme a nova senha.';
                    confirmarTexto.className = 'senha-dica senha-erro';
                }
            }
        });
    })();
</script>


<?php include '../App/Views/footer.php'; ?>