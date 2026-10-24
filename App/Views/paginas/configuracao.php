<?php

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/login');
    exit;
}

include '../App/Views/menu.php';

?>

<style>

/* =========================================================
   DESTACAR LINKS
========================================================= */

body.acessibilidade-links a {
    text-decoration: underline !important;
    font-weight: bold !important;
}


/* =========================================================
   ESPAÇAMENTO
========================================================= */

body.acessibilidade-espacamento {
    line-height: 2 !important;
    letter-spacing: 0.5px;
}

body.acessibilidade-espacamento .row {
    margin-bottom: 5px;
}


/* =========================================================
   MODO ESCURO
========================================================= */

body.tema-escuro {
    background-color: #121212 !important;
    color: #e8e8e8 !important;
}

body.tema-escuro .layout {
    background-color: #121212 !important;
    color: #e8e8e8 !important;
}


/* =========================================================
   CARDS
========================================================= */

body.tema-escuro .shadow {
    background-color: #1e1e1e !important;
    color: #e8e8e8 !important;
    border: 1px solid #303030 !important;

    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.35) !important;
}


/* =========================================================
   TÍTULOS
========================================================= */

body.tema-escuro h1,
body.tema-escuro h2,
body.tema-escuro h3,
body.tema-escuro h4,
body.tema-escuro h5,
body.tema-escuro h6 {
    color: #f5f5f5 !important;
}


/* =========================================================
   TEXTOS
========================================================= */

body.tema-escuro p {
    color: #cfcfcf !important;
}

body.tema-escuro b,
body.tema-escuro strong {
    color: #f1f1f1 !important;
}

body.tema-escuro .text-black {
    color: #f1f1f1 !important;
}

body.tema-escuro .text-muted {
    color: #999 !important;
}


/* =========================================================
   VERDE DO SISTEMA
========================================================= */

body.tema-escuro .text-success {
    color: #66d19e !important;
}

body.tema-escuro .linha-verde {
    border-color: #66d19e !important;
}


/* =========================================================
   LINKS
========================================================= */

body.tema-escuro a {
    color: #66d19e !important;
}

body.tema-escuro a:hover {
    color: #8ae5b8 !important;
}


/* =========================================================
   BORDAS
========================================================= */

body.tema-escuro .border-top {
    border-color: #353535 !important;
}

body.tema-escuro .border-bottom {
    border-color: #353535 !important;
}

body.tema-escuro .border {
    border-color: #353535 !important;
}


/* =========================================================
   BOTÕES VERDES
========================================================= */

body.tema-escuro .btn-outline-success {
    color: #66d19e !important;
    border-color: #66d19e !important;
}

body.tema-escuro .btn-outline-success:hover {
    background-color: #66d19e !important;
    color: #121212 !important;
}


/* =========================================================
   BOTÕES SECUNDÁRIOS
========================================================= */

body.tema-escuro .btn-outline-secondary {
    color: #cfcfcf !important;
    border-color: #555 !important;
}

body.tema-escuro .btn-outline-secondary:hover {
    background-color: #333 !important;
    color: #fff !important;
}


/* =========================================================
   INPUTS
========================================================= */

body.tema-escuro input,
body.tema-escuro select,
body.tema-escuro textarea {
    background-color: #1b1b1b !important;
    color: #eeeeee !important;
    border-color: #444 !important;
}

body.tema-escuro input::placeholder,
body.tema-escuro textarea::placeholder {
    color: #888 !important;
}

body.tema-escuro input:focus,
body.tema-escuro select:focus,
body.tema-escuro textarea:focus {
    background-color: #202020 !important;
    color: #fff !important;
    border-color: #66d19e !important;

    box-shadow:
        0 0 0 0.2rem rgba(102, 209, 158, 0.15) !important;
}


/* =========================================================
   SWITCHES
========================================================= */

body.tema-escuro .form-check-input {
    background-color: #333 !important;
    border-color: #555 !important;
}

body.tema-escuro .form-check-input:checked {
    background-color: #66d19e !important;
    border-color: #66d19e !important;
}


/* =========================================================
   TABELAS
========================================================= */

body.tema-escuro table {
    color: #e8e8e8 !important;
}

body.tema-escuro th {
    background-color: #252525 !important;
    color: #fff !important;
}

body.tema-escuro td {
    background-color: #1e1e1e !important;
    color: #ddd !important;
    border-color: #353535 !important;
}


/* =========================================================
   DROPDOWN
========================================================= */

body.tema-escuro .dropdown-menu {
    background-color: #1e1e1e !important;
    border-color: #353535 !important;
}

body.tema-escuro .dropdown-item {
    color: #e8e8e8 !important;
}

body.tema-escuro .dropdown-item:hover {
    background-color: #2c2c2c !important;
    color: #66d19e !important;
}


/* =========================================================
   MODAIS
========================================================= */

body.tema-escuro .modal-content {
    background-color: #1e1e1e !important;
    color: #e8e8e8 !important;
    border-color: #353535 !important;
}

body.tema-escuro .modal-header,
body.tema-escuro .modal-footer {
    border-color: #353535 !important;
}

body.tema-escuro .btn-close {
    filter: invert(1) grayscale(100%);
}


/* =========================================================
   ALERTAS
========================================================= */

body.tema-escuro .alert {
    background-color: #222 !important;
    color: #e8e8e8 !important;
    border-color: #3a3a3a !important;
}


/* =========================================================
   ÍCONES
========================================================= */

body.tema-escuro .bi {
    color: inherit;
}


/* =========================================================
   IMAGENS
========================================================= */

body.tema-escuro img {
    filter: brightness(0.9);
}


/* =========================================================
   FOCO DO TECLADO
========================================================= */

body.tema-escuro a:focus,
body.tema-escuro button:focus,
body.tema-escuro input:focus,
body.tema-escuro select:focus,
body.tema-escuro textarea:focus {
    outline: 2px solid #66d19e !important;
    outline-offset: 2px;
}


/* =========================================================
   TAMANHO DA FONTE
========================================================= */

/* 80% */

html.fonte-muito-pequena {
    font-size: 80%;
}


/* 90% */

html.fonte-pequena {
    font-size: 90%;
}


/* 100% */

html.fonte-normal {
    font-size: 100%;
}


/* 115% */

html.fonte-grande {
    font-size: 115%;
}


/* 130% */

html.fonte-muito-grande {
    font-size: 130%;
}


/* 150% */

html.fonte-extra-grande {
    font-size: 150%;
}


/* =========================================================
   BOTÕES DE ACESSIBILIDADE
========================================================= */

.btn-acessibilidade {
    min-width: 45px;
}


/* =========================================================
   OPÇÕES DE CONFIGURAÇÃO
========================================================= */

.configuracao-opcao {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.configuracao-descricao {
    margin: 0;
}


/* =========================================================
   CONTROLE DE FONTE
========================================================= */

.controle-fonte {
    display: flex;
    align-items: center;
    gap: 6px;
}

.indicador-fonte {
    min-width: 65px;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
}


/* =========================================================
   RESPONSIVIDADE
========================================================= */

@media (max-width: 576px) {

    .configuracao-opcao {
        align-items: flex-start;
        flex-direction: column;
    }

    .controle-fonte {
        width: 100%;
        justify-content: flex-start;
    }

    .btn-acessibilidade {
        min-width: 48px;
        min-height: 40px;
    }

}

</style>


<div class="layout">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <div class="container-fluid ps-4 pe-4 pt-5 pb-3">

        <div class="row align-items-start">

        <!-- CABEÇALHO -->
            <header class="home-topo">

                <div class="home-titulo">
                    <h1>Configuração</h1>
                    <p>Gerencie as configurações do sistema,
                    aparência e acessibilidade.</p>
                </div>

                <img src="<?= URL ?>/img/life-menor.png" class="logo-home" alt="Logo">

            </header>
        </div>

    </div>


    <!-- =====================================================
         CONTEÚDO
    ====================================================== -->

    <div class="container text-justify mt-4 mb-5">


        <!-- =================================================
             PRIMEIRA LINHA
        ================================================== -->

        <div class="row align-items-start">


            <!-- =============================================
                 PERFIL
            ============================================== -->

            <div class="col-12 col-lg-6">

                <div class="shadow p-3 mb-5 rounded">

                    <div class="container text-justify">

                        <div class="row row-cols-1">

                            <div class="col">

                                <h4 class="text-success">

                                    <i class="bi bi-person"></i>

                                    Perfil e Conta

                                </h4>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Nome do Usuário</b>
                            </div>

                            <div class="col">

                                <?= $_SESSION['usuario_nome']; ?>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Email</b>
                            </div>

                            <div class="col">

                                <?= $_SESSION['usuario_email']; ?>

                            </div>

                        </div>


                        <div class="row row-cols-1 border-top p-2">

                            <div class="col">

                                <b>Senha</b>

                            </div>

                        </div>


                        <div class="row row-cols-1 border-top p-2">

                            <div class="col">

                                <a
                                    class="text-decoration-none text-black"
                                    href="<?= URL ?>/usuarios/alterarSenha"
                                >

                                    <b>Alterar Senha</b>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =============================================
                 ACESSIBILIDADE
            ============================================== -->

            <div class="col-12 col-lg-6">

                <div class="shadow p-3 mb-5 rounded">

                    <div class="container text-justify">

                        <div class="row row-cols-1">

                            <div class="col">

                                <h4 class="text-success">

                                    <i class="bi bi-universal-access"></i>

                                    Acessibilidade e Aparência

                                </h4>

                            </div>

                        </div>


                        <!-- =================================================
                             TAMANHO DO TEXTO
                        ================================================== -->

                        <div class="row border-top p-2">

                            <div class="col">

                                <div class="configuracao-opcao">

                                    <div>

                                        <b>Tamanho do texto</b>

                                        <p class="configuracao-descricao">
                                            Escolha o tamanho das letras do sistema.
                                        </p>

                                    </div>


                                    <div class="controle-fonte">

                                        <button
                                            type="button"
                                            class="btn btn-outline-success btn-acessibilidade"
                                            onclick="alterarFonte(-1)"
                                            aria-label="Diminuir tamanho do texto"
                                        >
                                            A-
                                        </button>


                                        <span
                                            id="indicadorFonte"
                                            class="indicador-fonte"
                                        >
                                            Normal
                                        </span>


                                        <button
                                            type="button"
                                            class="btn btn-outline-success btn-acessibilidade"
                                            onclick="alterarFonte(1)"
                                            aria-label="Aumentar tamanho do texto"
                                        >
                                            A+
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             MODO ESCURO
                        ================================================== -->

                        <div class="row border-top p-2">

                            <div class="col">

                                <div class="configuracao-opcao">

                                    <div>

                                        <b>Modo escuro</b>

                                        <p class="configuracao-descricao">
                                            Altera o sistema para um tema escuro mais confortável.
                                        </p>

                                    </div>

                                    <div class="form-check form-switch">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="modoEscuro"
                                            onchange="alternarTema()"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             DESTACAR LINKS
                        ================================================== -->

                        <div class="row border-top p-2">

                            <div class="col">

                                <div class="configuracao-opcao">

                                    <div>

                                        <b>Destacar links</b>

                                        <p class="configuracao-descricao">
                                            Deixa links mais fáceis de identificar.
                                        </p>

                                    </div>

                                    <div class="form-check form-switch">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="destacarLinks"
                                            onchange="alternarLinks()"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             ESPAÇAMENTO
                        ================================================== -->

                        <div class="row border-top p-2">

                            <div class="col">

                                <div class="configuracao-opcao">

                                    <div>

                                        <b>Maior espaçamento</b>

                                        <p class="configuracao-descricao">
                                            Aumenta o espaço entre as linhas e textos.
                                        </p>

                                    </div>

                                    <div class="form-check form-switch">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="maiorEspacamento"
                                            onchange="alternarEspacamento()"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             RESTAURAR
                        ================================================== -->

                        <div class="row border-top p-2 mt-2">

                            <div class="col text-end">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="restaurarConfiguracoes()"
                                >

                                    <i class="bi bi-arrow-counterclockwise"></i>

                                    Restaurar configurações

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             SEGUNDA LINHA
        ================================================== -->

        <div class="row align-items-start">


            <!-- =============================================
                 NOTIFICAÇÕES
            ============================================== -->

            <div class="col-12 col-lg-6">

                <div class="shadow p-3 mb-5 rounded">

                    <div class="container text-justify">

                        <div class="row row-cols-1">

                            <div class="col">

                                <h4 class="text-success">

                                    <i class="bi bi-bell"></i>

                                    Notificações

                                </h4>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Avisos de Consulta</b>
                            </div>

                            <div class="col">

                                <div class="form-check form-switch float-end">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="notificacaoConsulta"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Novas Triagens</b>
                            </div>

                            <div class="col">

                                <div class="form-check form-switch float-end">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="notificacaoTriagem"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Relatórios Prontos</b>
                            </div>

                            <div class="col">

                                <div class="form-check form-switch float-end">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="notificacaoRelatorio"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Comunicados Gerais</b>
                            </div>

                            <div class="col">

                                <div class="form-check form-switch float-end">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="notificacaoComunicados"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =============================================
                 SEGURANÇA
            ============================================== -->

            <div class="col-12 col-lg-6">

                <div class="shadow p-3 mb-5 rounded">

                    <div class="container text-justify">

                        <div class="row row-cols-1">

                            <div class="col">

                                <h4 class="text-success">

                                    <i class="bi bi-briefcase"></i>

                                    Segurança

                                </h4>

                            </div>

                        </div>


                        <div class="row row-cols-1 border-top p-2">

                            <div class="col">

                                <b>Sessão atual</b>

                            </div>

                        </div>


                        <div class="row row-cols-1 border-top p-2">

                            <div class="col">

                                <a
                                    class="text-decoration-none text-black"
                                    href="<?= URL ?>/paginas/gerenciarDispositivos"
                                >

                                    <b>Gerenciar dispositivos</b>

                                </a>

                            </div>

                        </div>


                        <div class="row row-cols-1 border-top p-2">

                            <div class="col">

                                <a
                                    class="text-decoration-none text-black"
                                    href="<?= URL ?>/paginas/relatorio"
                                >

                                    <b>Atividades recentes</b>

                                </a>

                            </div>

                        </div>


                        <div class="row row-cols-1 border-top p-2">

                            <div class="col">

                                <a
                                    class="text-decoration-none text-black"
                                    href="<?= URL ?>/usuario/logout"
                                >

                                    <b>Encerrar sessão</b>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             SOBRE
        ================================================== -->

        <div class="row align-items-start">

            <div class="col-12">

                <div class="shadow p-3 mb-5 rounded">

                    <div class="container text-justify">

                        <div class="row row-cols-1">

                            <div class="col">

                                <h4 class="text-success">

                                    <i class="bi bi-info-circle-fill"></i>

                                    Sobre o Sistema

                                </h4>

                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Instituição</b>
                            </div>

                            <div class="col">
                                Instituto Federal de Rondônia
                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Sistema</b>
                            </div>

                            <div class="col">
                                Enfermaria - Sistema de Triagem
                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Licença</b>
                            </div>

                            <div class="col">
                                Uso interno
                            </div>

                        </div>


                        <div class="row row-cols-2 border-top p-2">

                            <div class="col">
                                <b>Suporte</b>
                            </div>

                            <div class="col">
                                enfermariaifro@gmail.com | (69) 99926-2045
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

/* =========================================================
   VARIÁVEL DO TAMANHO DA FONTE
========================================================= */

let tamanhoFonte = 0;


/* =========================================================
   NÍVEIS DA FONTE
========================================================= */

const niveisFonte = [
    {
        valor: -2,
        classe: 'fonte-muito-pequena',
        nome: 'Muito pequena'
    },
    {
        valor: -1,
        classe: 'fonte-pequena',
        nome: 'Pequena'
    },
    {
        valor: 0,
        classe: 'fonte-normal',
        nome: 'Normal'
    },
    {
        valor: 1,
        classe: 'fonte-grande',
        nome: 'Grande'
    },
    {
        valor: 2,
        classe: 'fonte-muito-grande',
        nome: 'Muito grande'
    },
    {
        valor: 3,
        classe: 'fonte-extra-grande',
        nome: 'Extra grande'
    }
];


/* =========================================================
   ATUALIZAR INDICADOR DA FONTE
========================================================= */

function atualizarIndicadorFonte() {

    const indicador =
        document.getElementById('indicadorFonte');

    if (!indicador) {
        return;
    }

    const nivel =
        niveisFonte.find(
            item => item.valor === tamanhoFonte
        );

    if (nivel) {

        indicador.textContent =
            nivel.nome;

    }

}


/* =========================================================
   ALTERAR TAMANHO DA FONTE
========================================================= */

function alterarFonte(valor) {

    tamanhoFonte += valor;


    if (tamanhoFonte < -2) {
        tamanhoFonte = -2;
    }


    if (tamanhoFonte > 3) {
        tamanhoFonte = 3;
    }


    document.documentElement.classList.remove(
        'fonte-muito-pequena',
        'fonte-pequena',
        'fonte-normal',
        'fonte-grande',
        'fonte-muito-grande',
        'fonte-extra-grande'
    );


    const nivel =
        niveisFonte.find(
            item => item.valor === tamanhoFonte
        );

    if (nivel) {

        document.documentElement.classList.add(
            nivel.classe
        );

    }


    localStorage.setItem(
        'enfermaria_tamanho_fonte',
        tamanhoFonte
    );


    atualizarIndicadorFonte();

}


/* =========================================================
   MODO ESCURO
========================================================= */

function alternarTema() {

    const checkbox =
        document.getElementById('modoEscuro');

    if (!checkbox) {
        return;
    }

    const ativado =
        checkbox.checked;

    document.body.classList.toggle(
        'tema-escuro',
        ativado
    );

    document.documentElement.setAttribute(
        'data-bs-theme',
        ativado ? 'dark' : 'light'
    );

    localStorage.setItem(
        'enfermaria_tema_escuro',
        ativado
    );
}


/* =========================================================
   DESTACAR LINKS
========================================================= */

function alternarLinks() {

    const checkbox =
        document.getElementById('destacarLinks');

    if (!checkbox) {
        return;
    }

    const ativado =
        checkbox.checked;

    document.body.classList.toggle(
        'acessibilidade-links',
        ativado
    );

    localStorage.setItem(
        'enfermaria_destacar_links',
        ativado
    );
}


/* =========================================================
   ESPAÇAMENTO
========================================================= */

function alternarEspacamento() {

    const checkbox =
        document.getElementById('maiorEspacamento');

    if (!checkbox) {
        return;
    }

    const ativado =
        checkbox.checked;

    document.body.classList.toggle(
        'acessibilidade-espacamento',
        ativado
    );

    localStorage.setItem(
        'enfermaria_espacamento',
        ativado
    );
}


/* =========================================================
   CARREGAR CONFIGURAÇÕES
========================================================= */

function carregarConfiguracoes() {


    /* =====================================================
       FONTE
    ====================================================== */

    let fonte = parseInt(
        localStorage.getItem(
            'enfermaria_tamanho_fonte'
        )
    );


    if (
        isNaN(fonte) ||
        fonte < -2 ||
        fonte > 3
    ) {

        fonte = 0;

    }


    tamanhoFonte = fonte;


    document.body.classList.remove(
        'fonte-muito-pequena',
        'fonte-pequena',
        'fonte-normal',
        'fonte-grande',
        'fonte-muito-grande',
        'fonte-extra-grande'
    );


    const nivelFonte =
        niveisFonte.find(
            item => item.valor === fonte
        );


    if (nivelFonte) {

        document.body.classList.add(
            nivelFonte.classe
        );

    }


    atualizarIndicadorFonte();


    /* =====================================================
       MODO ESCURO
    ====================================================== */

    const temaEscuro =
        localStorage.getItem(
            'enfermaria_tema_escuro'
        ) === 'true';


    const modoEscuro =
        document.getElementById(
            'modoEscuro'
        );


    if (modoEscuro) {

        modoEscuro.checked =
            temaEscuro;

    }


    document.body.classList.toggle(
        'tema-escuro',
        temaEscuro
    );


    /* =====================================================
       LINKS
    ====================================================== */

    const links =
        localStorage.getItem(
            'enfermaria_destacar_links'
        ) === 'true';


    const destacarLinks =
        document.getElementById(
            'destacarLinks'
        );


    if (destacarLinks) {

        destacarLinks.checked =
            links;

    }


    document.body.classList.toggle(
        'acessibilidade-links',
        links
    );


    /* =====================================================
       ESPAÇAMENTO
    ====================================================== */

    const espacamento =
        localStorage.getItem(
            'enfermaria_espacamento'
        ) === 'true';


    const maiorEspacamento =
        document.getElementById(
            'maiorEspacamento'
        );


    if (maiorEspacamento) {

        maiorEspacamento.checked =
            espacamento;

    }


    document.body.classList.toggle(
        'acessibilidade-espacamento',
        espacamento
    );

}


/* =========================================================
   RESTAURAR CONFIGURAÇÕES
========================================================= */

function restaurarConfiguracoes() {


    /* -------------------------
       APAGAR CONFIGURAÇÕES
    ------------------------- */

    localStorage.removeItem(
        'enfermaria_tamanho_fonte'
    );

    localStorage.removeItem(
        'enfermaria_tema_escuro'
    );

    localStorage.removeItem(
        'enfermaria_alto_contraste'
    );

    localStorage.removeItem(
        'enfermaria_destacar_links'
    );

    localStorage.removeItem(
        'enfermaria_espacamento'
    );


    /* -------------------------
       FONTE NORMAL
    ------------------------- */

    tamanhoFonte = 0;


    document.body.classList.remove(
        'fonte-muito-pequena',
        'fonte-pequena',
        'fonte-normal',
        'fonte-grande',
        'fonte-muito-grande',
        'fonte-extra-grande',
        'tema-escuro',
        'acessibilidade-links',
        'acessibilidade-espacamento'
    );

    document.documentElement.setAttribute('data-bs-theme', 'light');


    document.documentElement.classList.remove(
        'fonte-muito-pequena',
        'fonte-pequena',
        'fonte-normal',
        'fonte-grande',
        'fonte-muito-grande',
        'fonte-extra-grande'
    );

    document.documentElement.classList.add(
        'fonte-normal'
    );


    atualizarIndicadorFonte();


    /* -------------------------
       DESATIVAR SWITCHES
    ------------------------- */

    const modoEscuro =
        document.getElementById('modoEscuro');

    if (modoEscuro) {
        modoEscuro.checked = false;
    }


    const destacarLinks =
        document.getElementById('destacarLinks');

    if (destacarLinks) {
        destacarLinks.checked = false;
    }


    const maiorEspacamento =
        document.getElementById('maiorEspacamento');

    if (maiorEspacamento) {
        maiorEspacamento.checked = false;
    }

}


document.addEventListener(
    'DOMContentLoaded',
    carregarConfiguracoes
);

</script>


<?php

include '../App/Views/footer.php';

?>