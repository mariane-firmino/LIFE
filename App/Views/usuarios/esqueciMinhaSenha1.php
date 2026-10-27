<div class="container-login">

    <div class="container_esqueci_minha_seha">

        <div class="left_esqueci_minha_seha">

            <img
                src="<?= URL ?>/img/logo-life.png"
                class="logo_esqueci_minha_seha"
                alt="Logo Enfermaria"
            >

        </div>


        <div class="right_esqueci_minha_seha">

            <h1>Esqueci minha senha</h1>

            <p>
                Informe seu e-mail cadastrado e enviaremos
                <br>
                um link de recuperação.
            </p>


            <?php if (!empty($dados['erro'])): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($dados['erro']) ?>
                </div>

            <?php endif; ?>


            <?php if (!empty($dados['sucesso'])): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($dados['sucesso']) ?>
                </div>

            <?php endif; ?>


            <form
                action="<?= URL ?>/usuarios/esqueciMinhaSenha1"
                method="POST"
            >

                <label
                    class="ems-label"
                    for="email"
                >
                    E-mail:
                </label>

                <input
                    type="email"
                    id="email"
                    name="email" maxlength="225"
                    placeholder="Digite seu e-mail"
                    value="<?= htmlspecialchars($dados['email'] ?? '') ?>"
                    required
                    class="ems-input"
                >


                <button
                    type="submit"
                    class="ems-button"
                >
                    Enviar link de recuperação
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

            </form>


            <div class="divisor_esqueci_minha_seha">

                <hr>

                <span>ou</span>

                <hr>

            </div>


            <a
                class="ems-a"
                href="<?= URL ?>/usuarios/login"
            >
                voltar para login
            </a>

        </div>

    </div>

</div>