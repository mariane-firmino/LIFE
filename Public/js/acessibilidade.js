// =====================================================================
// ACESSIBILIDADE   ->   Public/js/acessibilidade.js
//
// Um só arquivo cuida de TODAS as opções de acessibilidade do sistema:
//  - guarda as escolhas no navegador (localStorage, chaves "enfermaria_*");
//  - aplica as escolhas em todas as páginas (inclusive login);
//  - cria o botão flutuante de acessibilidade (canto inferior esquerdo);
//  - desenha o painel completo na página de Configurações, em
//    qualquer elemento com  data-a11y-painel="completo".
//
// É carregado no <head> (Public/index.php) para aplicar o tema e o
// tamanho do texto ANTES da página aparecer (sem "piscar" o tema claro).
// Logo depois de <body>, o index.php chama  LifeAcessibilidade.aplicar()
// para colocar as classes no <body> também.
//
// Atalho de teclado: Alt + Shift + A abre/fecha o painel rápido.
// =====================================================================

(function () {
    'use strict';

    var PREFIXO = 'enfermaria_';

    // Tamanhos do texto (o valor salvo vai de -2 a 3).
    var FONTES = [
        { classe: 'fonte-muito-pequena', nome: 'Muito pequena' },
        { classe: 'fonte-pequena', nome: 'Pequena' },
        { classe: 'fonte-normal', nome: 'Normal' },
        { classe: 'fonte-grande', nome: 'Grande' },
        { classe: 'fonte-muito-grande', nome: 'Muito grande' },
        { classe: 'fonte-extra-grande', nome: 'Extra grande' }
    ];
    var FONTE_MIN = -2;
    var FONTE_MAX = 3;

    var TEMAS = [
        { valor: 'claro', nome: 'Claro', icone: 'bi-sun-fill' },
        { valor: 'escuro', nome: 'Escuro', icone: 'bi-moon-stars-fill' },
        { valor: 'sistema', nome: 'Automático', icone: 'bi-circle-half' }
    ];

    // Grupos do painel (na ordem em que aparecem).
    var GRUPOS = [
        { id: 'visao', titulo: 'Visão', icone: 'bi-eye-fill' },
        { id: 'leitura', titulo: 'Leitura', icone: 'bi-book-half' },
        { id: 'navegacao', titulo: 'Navegação e movimento', icone: 'bi-mouse2-fill' }
    ];

    // Opções liga/desliga. "classe" vai no <body> quando a opção está ligada.
    var OPCOES = [
        { chave: 'alto_contraste', grupo: 'visao', classe: 'a11y-alto-contraste', icone: 'bi-highlights',
          titulo: 'Alto contraste', desc: 'Fundo preto, texto branco e links em amarelo.' },
        { chave: 'fonte_legivel', grupo: 'visao', classe: 'a11y-fonte-legivel', icone: 'bi-fonts',
          titulo: 'Fonte mais legível', desc: 'Letras mais fáceis de diferenciar, boas para baixa visão e dislexia.' },
        { chave: 'cursor_grande', grupo: 'visao', classe: 'a11y-cursor-grande', icone: 'bi-cursor-fill',
          titulo: 'Cursor grande', desc: 'Aumenta a seta do mouse.' },

        { chave: 'destacar_links', grupo: 'leitura', classe: 'acessibilidade-links', icone: 'bi-link-45deg',
          titulo: 'Destacar links', desc: 'Sublinha e deixa em negrito tudo o que é clicável.' },
        { chave: 'espacamento', grupo: 'leitura', classe: 'acessibilidade-espacamento', icone: 'bi-text-paragraph',
          titulo: 'Maior espaçamento', desc: 'Mais espaço entre linhas, letras e palavras.' },
        { chave: 'guia_leitura', grupo: 'leitura', classe: 'a11y-guia-ativa', icone: 'bi-distribute-vertical',
          titulo: 'Guia de leitura', desc: 'Uma faixa acompanha o mouse e escurece o resto da tela.' },
        { chave: 'leitura_voz', grupo: 'leitura', classe: 'a11y-leitura-voz', icone: 'bi-volume-up-fill',
          titulo: 'Ler texto em voz alta', desc: 'Selecione um texto e clique em "Ouvir".', precisaVoz: true },

        { chave: 'foco_visivel', grupo: 'navegacao', classe: 'a11y-foco', icone: 'bi-bullseye',
          titulo: 'Destacar foco do teclado', desc: 'Borda amarela grossa no item escolhido com a tecla Tab.' },
        { chave: 'reduzir_animacoes', grupo: 'navegacao', classe: 'a11y-sem-animacao', icone: 'bi-pause-circle-fill',
          titulo: 'Reduzir animações', desc: 'Desliga transições e movimentos da tela.' }
    ];

    var temVoz = 'speechSynthesis' in window && typeof window.SpeechSynthesisUtterance !== 'undefined';
    var midiaEscura = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var estado = null;


    // =================================================================
    // GUARDAR E LER (localStorage pode falhar em janela anônima)
    // =================================================================

    function ler(chave) {
        try { return window.localStorage.getItem(PREFIXO + chave); } catch (e) { return null; }
    }
    function gravar(chave, valor) {
        try { window.localStorage.setItem(PREFIXO + chave, String(valor)); } catch (e) { /* sem armazenamento */ }
    }
    function apagar(chave) {
        try { window.localStorage.removeItem(PREFIXO + chave); } catch (e) { /* sem armazenamento */ }
    }

    function carregarEstado() {
        var fonte = parseInt(ler('tamanho_fonte'), 10);
        if (isNaN(fonte) || fonte < FONTE_MIN || fonte > FONTE_MAX) fonte = 0;

        // "tema" é novo; quem já usava o modo escuro antigo continua no escuro.
        var tema = ler('tema');
        if (tema !== 'claro' && tema !== 'escuro' && tema !== 'sistema') {
            tema = ler('tema_escuro') === 'true' ? 'escuro' : 'claro';
        }

        var opcoes = {};
        OPCOES.forEach(function (o) { opcoes[o.chave] = ler(o.chave) === 'true'; });
        if (!temVoz) opcoes.leitura_voz = false;

        return { fonte: fonte, tema: tema, opcoes: opcoes };
    }

    function temaEscuroAtivo() {
        if (estado.tema === 'escuro') return true;
        if (estado.tema === 'sistema') return !!(midiaEscura && midiaEscura.matches);
        return false;
    }


    // =================================================================
    // APLICAR NA PÁGINA
    // =================================================================

    function aplicar() {
        if (!estado) estado = carregarEstado();

        var raiz = document.documentElement;
        var corpo = document.body;
        var escuro = temaEscuroAtivo();

        // Tamanho do texto (no <html>: tudo em rem acompanha).
        FONTES.forEach(function (f) { raiz.classList.remove(f.classe); });
        raiz.classList.add(FONTES[estado.fonte - FONTE_MIN].classe);

        // Tema do Bootstrap (alto contraste usa a base escura).
        raiz.setAttribute('data-bs-theme', (escuro || estado.opcoes.alto_contraste) ? 'dark' : 'light');

        if (corpo) {
            corpo.classList.toggle('tema-escuro', escuro && !estado.opcoes.alto_contraste);
            OPCOES.forEach(function (o) { corpo.classList.toggle(o.classe, !!estado.opcoes[o.chave]); });
        }

        if (estado.opcoes.fonte_legivel) carregarFonteLegivel();
        if (!estado.opcoes.leitura_voz) esconderBotaoOuvir(true);

        sincronizarControles();
    }

    // Carrega a fonte Atkinson Hyperlegible só quando alguém ativa a opção.
    function carregarFonteLegivel() {
        if (document.getElementById('a11yFonteLegivel')) return;
        var link = document.createElement('link');
        link.id = 'a11yFonteLegivel';
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap';
        document.head.appendChild(link);
    }


    // =================================================================
    // MUDAR UMA OPÇÃO (usado pelos botões dos painéis)
    // =================================================================

    function mudarFonte(passo) {
        var nova = Math.max(FONTE_MIN, Math.min(FONTE_MAX, estado.fonte + passo));
        if (nova === estado.fonte) {
            anunciar(passo > 0 ? 'O texto já está no tamanho máximo.' : 'O texto já está no tamanho mínimo.');
            return;
        }
        estado.fonte = nova;
        gravar('tamanho_fonte', nova);
        aplicar();
        anunciar('Tamanho do texto: ' + FONTES[nova - FONTE_MIN].nome + '.');
    }

    function mudarTema(tema) {
        estado.tema = tema;
        gravar('tema', tema);
        gravar('tema_escuro', tema === 'escuro'); // compatibilidade com a versão antiga
        aplicar();
        var nome = TEMAS.filter(function (t) { return t.valor === tema; })[0].nome;
        anunciar('Tema ' + nome.toLowerCase() + ' ativado.');
    }

    function mudarOpcao(chave, ligada) {
        estado.opcoes[chave] = ligada;
        gravar(chave, ligada);
        aplicar();
        var opcao = OPCOES.filter(function (o) { return o.chave === chave; })[0];
        anunciar(opcao.titulo + (ligada ? ' ativado.' : ' desativado.'));
    }

    function restaurar() {
        apagar('tamanho_fonte');
        apagar('tema');
        apagar('tema_escuro');
        OPCOES.forEach(function (o) { apagar(o.chave); });
        estado = carregarEstado();
        aplicar();
        anunciar('Configurações de acessibilidade restauradas.');
    }


    // =================================================================
    // PAINÉIS (o mesmo HTML no botão flutuante e em Configurações)
    // =================================================================

    var contadorPainel = 0;

    function htmlPainel(completo) {
        var id = 'a11y' + (++contadorPainel);
        var h = '';

        GRUPOS.forEach(function (g) {
            h += '<section class="a11y-grupo" aria-labelledby="' + id + '-' + g.id + '">';
            h += '<h3 class="a11y-grupo-titulo" id="' + id + '-' + g.id + '"><i class="bi ' + g.icone + '" aria-hidden="true"></i> ' + g.titulo + '</h3>';

            if (g.id === 'visao') {
                // Tamanho do texto
                h += '<div class="a11y-linha a11y-linha--fonte">'
                    + '<div class="a11y-texto"><span class="a11y-rotulo" id="' + id + '-fonte"><i class="bi bi-type" aria-hidden="true"></i> Tamanho do texto</span>'
                    + (completo ? '<p class="a11y-desc">Aumenta ou diminui as letras de todas as páginas.</p>' : '') + '</div>'
                    + '<div class="a11y-fonte" role="group" aria-labelledby="' + id + '-fonte">'
                    + '<button type="button" class="btn btn-outline-success" data-a11y-fonte="-1" aria-label="Diminuir o texto">A−</button>'
                    + '<output class="a11y-fonte-nome" data-a11y-fonte-nome>Normal</output>'
                    + '<button type="button" class="btn btn-outline-success" data-a11y-fonte="1" aria-label="Aumentar o texto">A+</button>'
                    + '</div></div>';

                // Tema
                h += '<div class="a11y-linha a11y-linha--tema">'
                    + '<div class="a11y-texto"><span class="a11y-rotulo" id="' + id + '-tema"><i class="bi bi-palette-fill" aria-hidden="true"></i> Tema</span>'
                    + (completo ? '<p class="a11y-desc">Automático segue o tema do seu computador ou celular.</p>' : '') + '</div>'
                    + '<div class="btn-group a11y-tema" role="radiogroup" aria-labelledby="' + id + '-tema">';
                TEMAS.forEach(function (t) {
                    var rid = id + '-tema-' + t.valor;
                    h += '<input type="radio" class="btn-check" name="' + id + '-tema" id="' + rid + '" value="' + t.valor + '" data-a11y-tema autocomplete="off">'
                        + '<label class="btn btn-outline-success btn-sm" for="' + rid + '"><i class="bi ' + t.icone + '" aria-hidden="true"></i> ' + t.nome + '</label>';
                });
                h += '</div></div>';
            }

            OPCOES.filter(function (o) { return o.grupo === g.id; }).forEach(function (o) {
                var oid = id + '-' + o.chave;
                var semVoz = o.precisaVoz && !temVoz;
                h += '<div class="a11y-linha">'
                    + '<div class="a11y-texto"><label class="a11y-rotulo" for="' + oid + '"><i class="bi ' + o.icone + '" aria-hidden="true"></i> ' + o.titulo + '</label>'
                    + (completo || semVoz ? '<p class="a11y-desc" id="' + oid + '-d">' + (semVoz ? 'Este navegador não tem leitura em voz alta.' : o.desc) + '</p>' : '')
                    + '</div>'
                    + '<div class="form-check form-switch m-0"><input class="form-check-input a11y-switch" type="checkbox" role="switch" id="' + oid + '"'
                    + ' data-a11y-opcao="' + o.chave + '"' + (completo || semVoz ? ' aria-describedby="' + oid + '-d"' : '') + (semVoz ? ' disabled' : '') + '></div>'
                    + '</div>';
            });

            h += '</section>';
        });

        h += '<div class="a11y-rodape">'
            + '<button type="button" class="btn btn-outline-secondary btn-sm" data-a11y-restaurar><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Restaurar padrão</button>'
            + '</div>';

        return h;
    }

    function sincronizarControles() {
        if (!estado) return;
        var nomeFonte = FONTES[estado.fonte - FONTE_MIN].nome;

        each('[data-a11y-fonte-nome]', function (el) { el.textContent = nomeFonte; });
        each('[data-a11y-fonte="-1"]', function (el) { el.disabled = estado.fonte <= FONTE_MIN; });
        each('[data-a11y-fonte="1"]', function (el) { el.disabled = estado.fonte >= FONTE_MAX; });
        each('[data-a11y-tema]', function (el) { el.checked = el.value === estado.tema; });
        each('[data-a11y-opcao]', function (el) { el.checked = !!estado.opcoes[el.getAttribute('data-a11y-opcao')]; });

        var botao = document.getElementById('a11yBotao');
        if (botao) {
            var algumaAtiva = estado.fonte !== 0 || estado.tema !== 'claro'
                || OPCOES.some(function (o) { return estado.opcoes[o.chave]; });
            botao.classList.toggle('a11y-botao--ativo', algumaAtiva);
        }
    }

    function each(seletor, fn) {
        Array.prototype.forEach.call(document.querySelectorAll(seletor), fn);
    }


    // =================================================================
    // BOTÃO FLUTUANTE + PAINEL RÁPIDO
    // =================================================================

    function criarBotaoFlutuante() {
        var botao = document.createElement('button');
        botao.type = 'button';
        botao.id = 'a11yBotao';
        botao.className = 'a11y-botao';
        botao.setAttribute('aria-expanded', 'false');
        botao.setAttribute('aria-controls', 'a11yPainel');
        botao.title = 'Acessibilidade (Alt + Shift + A)';
        botao.innerHTML = '<i class="bi bi-universal-access" aria-hidden="true"></i><span class="visually-hidden">Abrir opções de acessibilidade</span>';

        var painel = document.createElement('div');
        painel.id = 'a11yPainel';
        painel.className = 'a11y-painel a11y-painel--rapido';
        painel.setAttribute('role', 'dialog');
        painel.setAttribute('aria-labelledby', 'a11yPainelTitulo');
        painel.hidden = true;

        var linkConfig = window.LIFE_URL ? '<a class="a11y-mais" href="' + window.LIFE_URL + '/paginas/configuracao#acessibilidade">Todas as configurações</a>' : '';
        painel.innerHTML = '<div class="a11y-painel-topo">'
            + '<h2 class="a11y-painel-titulo" id="a11yPainelTitulo"><i class="bi bi-universal-access" aria-hidden="true"></i> Acessibilidade</h2>'
            + '<button type="button" class="btn-close" data-a11y-fechar aria-label="Fechar"></button>'
            + '</div>'
            + '<div class="a11y-painel-corpo">' + htmlPainel(false) + linkConfig + '</div>';

        document.body.appendChild(painel);
        document.body.appendChild(botao);

        botao.addEventListener('click', function () { alternarPainel(); });
    }

    function alternarPainel(abrir) {
        var painel = document.getElementById('a11yPainel');
        var botao = document.getElementById('a11yBotao');
        if (!painel || !botao) return;

        var vaiAbrir = typeof abrir === 'boolean' ? abrir : painel.hidden;
        painel.hidden = !vaiAbrir;
        botao.setAttribute('aria-expanded', String(vaiAbrir));

        if (vaiAbrir) {
            var primeiro = painel.querySelector('button:not([disabled]), input:not([disabled])');
            if (primeiro) primeiro.focus();
        } else {
            botao.focus();
        }
    }


    // =================================================================
    // GUIA DE LEITURA (faixa que segue o mouse)
    // =================================================================

    function prepararGuia() {
        var guia = document.createElement('div');
        guia.className = 'a11y-guia';
        guia.setAttribute('aria-hidden', 'true');
        document.body.appendChild(guia);

        document.addEventListener('mousemove', function (e) {
            if (!estado.opcoes.guia_leitura) return;
            guia.style.top = (e.clientY - guia.offsetHeight / 2) + 'px';
        }, { passive: true });
    }


    // =================================================================
    // LER TEXTO SELECIONADO EM VOZ ALTA
    // =================================================================

    var botaoOuvir = null;
    var textoParaOuvir = '';
    var falando = false;

    function vozPortugues() {
        var vozes = window.speechSynthesis.getVoices();
        for (var i = 0; i < vozes.length; i++) if (/^pt[-_]BR/i.test(vozes[i].lang)) return vozes[i];
        for (var j = 0; j < vozes.length; j++) if (/^pt/i.test(vozes[j].lang)) return vozes[j];
        return null;
    }

    function prepararVoz() {
        if (!temVoz) return;

        botaoOuvir = document.createElement('button');
        botaoOuvir.type = 'button';
        botaoOuvir.className = 'a11y-ouvir btn btn-success btn-sm';
        botaoOuvir.hidden = true;
        document.body.appendChild(botaoOuvir);

        // Não deixa o clique no botão apagar a seleção.
        botaoOuvir.addEventListener('mousedown', function (e) { e.preventDefault(); });
        botaoOuvir.addEventListener('click', function () {
            if (falando) { window.speechSynthesis.cancel(); return; }
            var fala = new window.SpeechSynthesisUtterance(textoParaOuvir);
            fala.lang = 'pt-BR';
            var voz = vozPortugues();
            if (voz) fala.voice = voz;
            fala.onstart = function () { falando = true; rotuloOuvir(); };
            fala.onend = fala.onerror = function () { falando = false; rotuloOuvir(); esconderBotaoOuvir(); };
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(fala);
        });

        var verSelecao = function () { setTimeout(mostrarBotaoOuvir, 10); };
        document.addEventListener('mouseup', verSelecao);
        document.addEventListener('keyup', function (e) { if (e.shiftKey || e.key === 'Shift') verSelecao(); });
    }

    function rotuloOuvir() {
        if (!botaoOuvir) return;
        botaoOuvir.innerHTML = falando
            ? '<i class="bi bi-stop-fill" aria-hidden="true"></i> Parar'
            : '<i class="bi bi-volume-up-fill" aria-hidden="true"></i> Ouvir';
    }

    function mostrarBotaoOuvir() {
        if (!botaoOuvir || !estado.opcoes.leitura_voz) return;
        var selecao = window.getSelection ? window.getSelection() : null;
        var texto = selecao ? String(selecao).trim() : '';

        if (texto.length < 2 || !selecao.rangeCount) {
            if (!falando) esconderBotaoOuvir();
            return;
        }

        var caixa = selecao.getRangeAt(0).getBoundingClientRect();
        textoParaOuvir = texto.slice(0, 4000);
        rotuloOuvir();
        botaoOuvir.hidden = false;
        var largura = botaoOuvir.offsetWidth || 90;
        var esquerda = Math.min(Math.max(8, caixa.left), window.innerWidth - largura - 8);
        var topo = caixa.bottom + 8;
        if (topo + 40 > window.innerHeight) topo = Math.max(8, caixa.top - 44);
        botaoOuvir.style.left = esquerda + 'px';
        botaoOuvir.style.top = topo + 'px';
    }

    function esconderBotaoOuvir(pararFala) {
        if (!botaoOuvir) return;
        if (pararFala && falando) window.speechSynthesis.cancel();
        botaoOuvir.hidden = true;
    }


    // =================================================================
    // EXTRAS: link "pular para o conteúdo" e avisos para leitor de tela
    // =================================================================

    function criarPularConteudo() {
        var alvo = document.getElementById('conteudo-principal')
            || document.querySelector('main, .layout, .container-login');
        if (!alvo) return;
        if (!alvo.id) alvo.id = 'conteudo-principal';
        if (!alvo.hasAttribute('tabindex')) alvo.setAttribute('tabindex', '-1');

        // As telas com menu já têm o link no menu.php (.pular-conteudo):
        // só garante que ele aponte para o conteúdo desta tela.
        var doMenu = document.querySelector('.pular-conteudo');
        if (doMenu) {
            doMenu.setAttribute('href', '#' + alvo.id);
            return;
        }

        var pular = document.createElement('a');
        pular.className = 'a11y-pular';
        pular.href = '#' + alvo.id;
        pular.textContent = 'Pular para o conteúdo';
        document.body.insertBefore(pular, document.body.firstChild);
    }

    function criarAnunciador() {
        var anuncio = document.createElement('div');
        anuncio.id = 'a11yAnuncio';
        anuncio.className = 'visually-hidden';
        anuncio.setAttribute('aria-live', 'polite');
        document.body.appendChild(anuncio);
    }

    function anunciar(texto) {
        var anuncio = document.getElementById('a11yAnuncio');
        if (!anuncio) return;
        anuncio.textContent = '';
        setTimeout(function () { anuncio.textContent = texto; }, 50);
    }


    // =================================================================
    // EVENTOS (delegados: valem para qualquer painel da página)
    // =================================================================

    document.addEventListener('click', function (e) {
        var alvo = e.target.closest ? e.target.closest('[data-a11y-fonte], [data-a11y-restaurar], [data-a11y-fechar]') : null;
        if (!alvo) return;
        if (alvo.hasAttribute('data-a11y-fonte')) mudarFonte(parseInt(alvo.getAttribute('data-a11y-fonte'), 10));
        else if (alvo.hasAttribute('data-a11y-restaurar')) restaurar();
        else if (alvo.hasAttribute('data-a11y-fechar')) alternarPainel(false);
    });

    document.addEventListener('change', function (e) {
        var el = e.target;
        if (el.hasAttribute('data-a11y-opcao')) mudarOpcao(el.getAttribute('data-a11y-opcao'), el.checked);
        else if (el.hasAttribute('data-a11y-tema') && el.checked) mudarTema(el.value);
    });

    document.addEventListener('keydown', function (e) {
        if (e.altKey && e.shiftKey && (e.key === 'A' || e.key === 'a' || e.code === 'KeyA')) {
            e.preventDefault();
            alternarPainel();
        } else if (e.key === 'Escape') {
            var painel = document.getElementById('a11yPainel');
            if (painel && !painel.hidden) alternarPainel(false);
            esconderBotaoOuvir(true);
        }
    });

    // Fecha o painel rápido ao clicar fora dele.
    document.addEventListener('mousedown', function (e) {
        var painel = document.getElementById('a11yPainel');
        if (!painel || painel.hidden) return;
        if (!painel.contains(e.target) && !e.target.closest('#a11yBotao')) alternarPainel(false);
    });

    // Tema "Automático": acompanha a mudança do sistema na hora.
    if (midiaEscura) {
        var aoMudarSistema = function () { if (estado && estado.tema === 'sistema') aplicar(); };
        if (midiaEscura.addEventListener) midiaEscura.addEventListener('change', aoMudarSistema);
        else if (midiaEscura.addListener) midiaEscura.addListener(aoMudarSistema);
    }

    // Outra aba mudou as configurações: aplica aqui também.
    window.addEventListener('storage', function (e) {
        if (e.key && e.key.indexOf(PREFIXO) === 0) { estado = carregarEstado(); aplicar(); }
    });


    // =================================================================
    // INÍCIO
    // =================================================================

    estado = carregarEstado();
    aplicar(); // no <head>: já aplica tamanho do texto e tema do Bootstrap

    document.addEventListener('DOMContentLoaded', function () {
        criarAnunciador();
        criarPularConteudo();

        // Painel completo (página de Configurações).
        var completos = document.querySelectorAll('[data-a11y-painel="completo"]');
        Array.prototype.forEach.call(completos, function (el) {
            el.classList.add('a11y-painel', 'a11y-painel--completo');
            el.innerHTML = htmlPainel(true);
        });

        // Botão flutuante em todas as páginas, menos onde já há o painel completo.
        if (!completos.length) criarBotaoFlutuante();

        prepararGuia();
        prepararVoz();
        aplicar();
    });

    // Usado pelo index.php logo depois de <body>.
    window.LifeAcessibilidade = { aplicar: aplicar, restaurar: restaurar, abrirPainel: function () { alternarPainel(true); } };
})();
