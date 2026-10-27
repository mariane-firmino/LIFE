<!-- Redefinir senha: mesmo visual do login (Public/css/usuarios/login.css) -->
<div class="container-login">

    <div class="card-login">

        <!-- LADO DA LOGO -->
        <div class="lado-logo">
            <img
                src="<?= URL ?>/img/logo-life.png"
                alt="Logo LIFE"
            >
        </div>

        <!-- LADO DO FORMULÁRIO -->
        <div class="lado-form">

            <h2>REDEFINIR SENHA</h2>

            <?php if (!empty($dados['erro'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($dados['erro']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($dados['token'])): ?>

                <p>Digite sua nova senha abaixo.</p>

                <form method="POST" action="<?= URL ?>/usuarios/redefinirSenha">

                    <input type="hidden" name="token" value="<?= htmlspecialchars($dados['token']) ?>">

                    <div class="campo-login">
                        <label class="login-label" for="senha">Nova senha:</label>
                        <input
                            type="password"
                            id="senha"
                            name="senha"
                            class="login-form-control"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="campo-login">
                        <label class="login-label" for="confirmarSenha">Confirmar nova senha:</label>
                        <input
                            type="password"
                            id="confirmarSenha"
                            name="confirmarSenha"
                            class="login-form-control"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-login">
                        Alterar senha
                    </button>

                </form>

            <?php endif; ?>

            <!-- LINKS -->
            <div class="links">
                <p>
                    <a href="<?= URL ?>/usuarios/login">Voltar para o login</a>
                </p>
            </div>

        </div>

    </div>

</div>
