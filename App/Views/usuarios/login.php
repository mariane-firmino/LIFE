<div class="container-login">

    <div class="card-login">

        <!-- LADO DA LOGO -->
        <div class="lado-logo">
            <img
                src="<?= URL ?>/public/img/logo-life.png"
                alt="Logo Enfermaria"
            >
        </div>

        <!-- LADO DO FORMULÁRIO -->
        <div class="lado-form">

            <h2>REALIZAR LOGIN</h2>

            <!-- Mensagem da sessão -->
            <?= Sessao::mensagem('usuario') ?>

            <form action="<?= URL ?>/usuarios/login" method="post">

                <!-- E-MAIL OU SIAPE -->
                <div class="campo-login">
                    <label
                        class="login-label"
                        for="identificador"
                    >
                        E-mail ou SIAPE:
                    </label>

                    <input
                        type="text"
                        id="identificador"
                        name="identificador"
                        class="login-form-control <?= !empty($dados['identificador_erro']) ? 'is-invalid' : '' ?>"
                        placeholder="Digite seu e-mail ou SIAPE"
                        value="<?= htmlspecialchars($dados['identificador'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >

                    <?php if (!empty($dados['identificador_erro'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($dados['identificador_erro']) ?>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- SENHA -->
                <div class="campo-login">
                    <label
                        class="login-label"
                        for="senha"
                    >
                        Senha:
                    </label>

                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        class="login-form-control <?= !empty($dados['senha_erro']) ? 'is-invalid' : '' ?>"
                        placeholder="Digite sua senha"
                        required
                    >

                    <?php if (!empty($dados['senha_erro'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($dados['senha_erro']) ?>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- BOTÃO -->
                <button
                    type="submit"
                    class="btn-login"
                >
                    Realizar Login
                </button>

            </form>


            <!-- LINKS -->
            <div class="links">

                <p>
                    Esqueceu a senha?
                    <a href="<?= URL ?>/usuarios/esqueciMinhaSenha1">
                        Esqueci a senha
                    </a>
                </p>

                <p>
                    Ainda não está cadastrado?
                    <a href="<?= URL ?>/usuarios/cadastrar">
                        Cadastre-se
                    </a>
                </p>

            </div>

        </div>

    </div>

</div>
