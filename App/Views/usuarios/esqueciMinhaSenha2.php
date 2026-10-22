<div class="container-login">

    <div class="container-gustavo">

        <div class="left">

            <img
                src="<?= URL ?>/img/life-menor.png"
                alt="Logo Enfermaria"
                class="logo"
            >

        </div>


        <div class="right">

            <h1>Redefinir senha</h1>


            <?php if (!empty($dados['erro'])): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($dados['erro']) ?>
                </div>

            <?php endif; ?>


            <?php if (empty($dados['erro']) || !empty($dados['token'])): ?>

                <?php if (!empty($dados['token'])): ?>

                    <p>
                        Digite sua nova senha abaixo.
                    </p>


                    <form
                        method="POST"
                        action="<?= URL ?>/usuarios/redefinirSenha"
                    >

                        <input
                            type="hidden"
                            name="token"
                            value="<?= htmlspecialchars($dados['token']) ?>"
                        >


                        <div class="mb-3">

                            <label for="senha">
                                Nova senha:
                            </label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label for="confirmarSenha">
                                Confirmar nova senha:
                            </label>

                            <input
                                type="password"
                                id="confirmarSenha"
                                name="confirmarSenha"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Alterar senha
                        </button>

                    </form>

                <?php endif; ?>

            <?php endif; ?>


            <br>

            <a
                href="<?= URL ?>/usuarios/login"
            >
                Voltar para o login
            </a>

        </div>

    </div>

</div>