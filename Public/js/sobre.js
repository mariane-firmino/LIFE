/* =========================================================
   SOBRE NÓS — contadores animados, filtro e cartões que viram
========================================================= */

(function () {
    'use strict';

    var reduzMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var temHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    document.addEventListener('DOMContentLoaded', function () {
        iniciarContadores();
        iniciarCartoes();
        iniciarFiltro();
    });

    /* ---------------------------------------------------
       Contadores: contam de 0 até o valor ao aparecer na tela.
       Sem JS (ou com "reduzir movimento") o valor final já está no HTML.
    --------------------------------------------------- */
    function iniciarContadores() {
        var numeros = document.querySelectorAll('.stat-num');
        if (!numeros.length || reduzMovimento || !('IntersectionObserver' in window)) { return; }

        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (!entrada.isIntersecting) { return; }
                observador.unobserve(entrada.target);
                contar(entrada.target);
            });
        }, { threshold: 0.4 });

        numeros.forEach(function (el) {
            el.textContent = '0' + (el.getAttribute('data-sufixo') || '');
            observador.observe(el);
        });
    }

    function contar(el) {
        var alvo = parseInt(el.getAttribute('data-valor'), 10) || 0;
        var sufixo = el.getAttribute('data-sufixo') || '';
        var duracao = 1300;
        var inicio = null;

        function passo(agora) {
            if (inicio === null) { inicio = agora; }
            var t = Math.min((agora - inicio) / duracao, 1);
            var suave = 1 - Math.pow(1 - t, 3); // ease-out
            el.textContent = Math.round(alvo * suave).toLocaleString('pt-BR') + sufixo;
            if (t < 1) { window.requestAnimationFrame(passo); }
        }

        window.requestAnimationFrame(passo);
    }

    /* ---------------------------------------------------
       Cartões: viram no mouse (desktop), no toque e no teclado.
       A face escondida fica "inert" (sem foco e fora do leitor de tela).
    --------------------------------------------------- */
    function virar(cartao, virado, moverFoco) {
        var frente = cartao.querySelector('.membro-frente');
        var verso = cartao.querySelector('.membro-verso');

        cartao.classList.toggle('virado', virado);
        frente.inert = virado;
        verso.inert = !virado;

        if (moverFoco) {
            var botao = (virado ? verso : frente).querySelector('button');
            if (botao) { botao.focus(); }
        }
    }

    function iniciarCartoes() {
        document.querySelectorAll('.membro').forEach(function (cartao) {
            var frente = cartao.querySelector('.membro-frente');
            var verso = cartao.querySelector('.membro-verso');

            cartao.querySelector('[data-virar]').addEventListener('click', function () {
                virar(cartao, true, true);
            });

            cartao.querySelector('[data-voltar]').addEventListener('click', function () {
                virar(cartao, false, true);
            });

            // Esc devolve o cartão para a frente
            cartao.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && cartao.classList.contains('virado')) {
                    virar(cartao, false, true);
                }
            });

            if (temHover) {
                // Desktop: vira ao passar o mouse
                cartao.addEventListener('mouseenter', function () { virar(cartao, true, false); });
                cartao.addEventListener('mouseleave', function () {
                    // Se o foco do teclado está no verso, mantém aberto
                    if (!verso.contains(document.activeElement)) { virar(cartao, false, false); }
                });
            } else {
                // Celular/tablet: tocar em qualquer ponto da face vira
                frente.addEventListener('click', function () { virar(cartao, true, false); });
                verso.addEventListener('click', function (e) {
                    if (!e.target.closest('button')) { virar(cartao, false, false); }
                });
            }
        });
    }

    /* ---------------------------------------------------
       Filtro por área (Front-end, Back-end, Full Stack)
    --------------------------------------------------- */
    function iniciarFiltro() {
        var botoes = document.querySelectorAll('.filtro');
        var cartoes = Array.prototype.slice.call(document.querySelectorAll('.membro'));
        var status = document.getElementById('filtro-status');
        if (!botoes.length || !cartoes.length) { return; }

        botoes.forEach(function (botao) {
            botao.addEventListener('click', function () {
                botoes.forEach(function (b) {
                    var ativo = b === botao;
                    b.classList.toggle('ativo', ativo);
                    b.setAttribute('aria-pressed', ativo ? 'true' : 'false');
                });
                filtrar(botao.getAttribute('data-filtro'));
            });
        });

        function filtrar(filtro) {
            var visiveis = 0;

            cartoes.forEach(function (cartao) {
                var categorias = (cartao.getAttribute('data-categorias') || '').split(' ');
                var mostrar = filtro === 'todos' || categorias.indexOf(filtro) !== -1;

                if (mostrar) {
                    visiveis++;
                    virar(cartao, false, false);
                    cartao.hidden = false;
                    void cartao.offsetWidth; // reinicia a transição
                    cartao.classList.remove('oculto');
                } else {
                    cartao.classList.add('oculto');
                    if (reduzMovimento) {
                        cartao.hidden = true;
                    } else {
                        window.setTimeout(function () {
                            if (cartao.classList.contains('oculto')) { cartao.hidden = true; }
                        }, 260);
                    }
                }
            });

            if (status) {
                status.textContent = 'Mostrando ' + visiveis + ' de ' + cartoes.length + ' desenvolvedores.';
            }
        }
    }
})();