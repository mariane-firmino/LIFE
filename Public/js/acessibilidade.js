(function () {
    var fontes = [
        'fonte-muito-pequena',
        'fonte-pequena',
        'fonte-normal',
        'fonte-grande',
        'fonte-muito-grande',
        'fonte-extra-grande'
    ];

    function aplicarPreferencias() {
        var raiz = document.documentElement;
        var corpo = document.body;
        var fonte = parseInt(localStorage.getItem('enfermaria_tamanho_fonte'), 10);
        if (isNaN(fonte) || fonte < -2 || fonte > 3) {
            fonte = 0;
        }

        fontes.forEach(function (classe) {
            raiz.classList.remove(classe);
        });
        raiz.classList.add(fontes[fonte + 2]);

        var temaEscuro = localStorage.getItem('enfermaria_tema_escuro') === 'true';
        var destacarLinks = localStorage.getItem('enfermaria_destacar_links') === 'true';
        var espacamento = localStorage.getItem('enfermaria_espacamento') === 'true';

        corpo.classList.toggle('tema-escuro', temaEscuro);
        corpo.classList.toggle('acessibilidade-links', destacarLinks);
        corpo.classList.toggle('acessibilidade-espacamento', espacamento);
        raiz.setAttribute('data-bs-theme', temaEscuro ? 'dark' : 'light');

        var modoEscuro = document.getElementById('modoEscuro');
        var links = document.getElementById('destacarLinks');
        var maiorEspacamento = document.getElementById('maiorEspacamento');

        if (modoEscuro) modoEscuro.checked = temaEscuro;
        if (links) links.checked = destacarLinks;
        if (maiorEspacamento) maiorEspacamento.checked = espacamento;

        var indicadorFonte = document.getElementById('indicadorFonte');
        if (indicadorFonte) {
            indicadorFonte.textContent = [
                'Muito pequena',
                'Pequena',
                'Normal',
                'Grande',
                'Muito grande',
                'Extra grande'
            ][fonte + 2];
        }
    }

    document.addEventListener('DOMContentLoaded', aplicarPreferencias);

    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'modoEscuro') {
            document.documentElement.setAttribute(
                'data-bs-theme',
                event.target.checked ? 'dark' : 'light'
            );
        }
    });
})();
