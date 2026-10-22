/* =========================================================
   MENU LATERAL
   Arquivo: Public/js/menu.js

   Faz duas coisas:
   1. Destaca no menu o link da página em que o usuário está.
   2. Fecha o menu lateral quando o modal "Sair" é aberto,
      para os dois não ficarem um sobre o outro.
   ========================================================= */

(function () {
    'use strict';

    // Remove a barra final e deixa em minúsculas para comparar caminhos.
    function normalizar(caminho) {
        return caminho.replace(/\/+$/, '').toLowerCase();
    }


    // -----------------------------------------------------
    // 1. DESTACAR A PÁGINA ATUAL
    // Marca o link com a classe "active" (Bootstrap) quando o
    // endereço atual é o do link ou uma subpágina dele
    // (ex.: alunos/consulta/5 destaca "alunos/consulta").
    // -----------------------------------------------------

    function destacarPaginaAtual() {
        var atual = normalizar(window.location.pathname);
        var links = document.querySelectorAll('#offcanvasNavbar .nav-link[href]');

        links.forEach(function (link) {
            var caminhoLink;

            try {
                caminhoLink = normalizar(
                    new URL(link.getAttribute('href'), window.location.origin).pathname
                );
            } catch (erro) {
                return; // link inválido: ignora
            }

            var ativo = atual === caminhoLink || atual.indexOf(caminhoLink + '/') === 0;

            link.classList.toggle('active', ativo);

            if (ativo) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }


    // -----------------------------------------------------
    // 2. FECHAR O MENU AO ABRIR O MODAL DE SAÍDA
    // -----------------------------------------------------

    function fecharMenuAoAbrirModal() {
        var modal = document.getElementById('staticBackdrop');
        var menu = document.getElementById('offcanvasNavbar');

        if (!modal || !menu) {
            return;
        }

        modal.addEventListener('show.bs.modal', function () {
            // "bootstrap" só existe se o Bootstrap JS estiver carregado.
            if (window.bootstrap && window.bootstrap.Offcanvas) {
                var instancia = window.bootstrap.Offcanvas.getInstance(menu);

                if (instancia) {
                    instancia.hide();
                }
            }
        });
    }


    function iniciar() {
        destacarPaginaAtual();
        fecharMenuAoAbrirModal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();