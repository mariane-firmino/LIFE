/* =========================================================
   PERGUNTAS CONDICIONAIS
   Esconde perguntas de acompanhamento ("Qual?", "Quantas
   vezes?", "Por quê?") até a resposta anterior pedir por elas.
   Ex.: "Possui alergia?" = Não  ->  "Quais alimentos?" some.

   Enquanto escondido, o campo fica desativado: não é enviado
   e não é obrigatório. O servidor aplica a mesma regra.

   Duas formas de ligar um campo a outro:
   1) Pelo mapa REGRAS abaixo (formulários de aluno).
   2) Pelos atributos no próprio campo (triagens):
        data-depende-de="pergunta8"
        data-depende-valores='["Sim"]'
========================================================= */
(function () {
    "use strict";

    // campo dependente: [[campo que controla, [valores que mostram]], ...]
    // Mais de uma condição = basta UMA ser verdadeira.
    // Manter igual a Alunos::DEPENDENCIAS (PHP).
    const REGRAS = {
        quantas_vezes:        [["ja_reprovou", ["Sim"]]],
        quanto_tempo:         [["estuda_fora", ["Sim"]]],
        atendimento_porque:   [["atendimento", ["Não"]]],
        alergia_qual:         [["alergia_alimentar", ["Sim"]]],
        doenca_qual:          [["doenca_cronica", ["Sim"]]],
        medicacao_qual:       [["medicacao_continua", ["Sim"]]],
        internado_motivo:     [["ja_internado", ["Sim"]]],
        cirurgia_qual:        [["ja_cirurgia", ["Sim"]]],
        pais_sabem:           [["fuma", ["Sim"]], ["bebida_alcoolica", ["Sim"]]],
        drogas_qual:          [["drogas_ilicitas", ["Sim"]]],
        frequencia_atividade: [["atividade_fisica", ["Sim"]]],
        condicao_outra:       [["condicoes[]", ["Outra"]]],
    };

    // Valores marcados/escolhidos de um campo (ignora campos desativados,
    // assim uma pergunta escondida também esconde as que dependem dela).
    function valoresDe(form, nome) {
        const campos = Array.from(form.elements).filter((el) => el.name === nome && !el.disabled);
        const valores = [];
        campos.forEach((el) => {
            if (el.type === "checkbox" || el.type === "radio") {
                if (el.checked) valores.push(el.value);
            } else if (el.value !== "") {
                valores.push(el.value);
            }
        });
        return valores;
    }

    // Bloco visual do campo (a coluna com rótulo + campo).
    function blocoDe(campo) {
        return campo.closest('[class*="col-"], .mt-2') || campo.parentElement;
    }

    function iniciar(form) {
        const dependentes = [];

        Array.from(form.elements).forEach((campo) => {
            let condicoes = null;

            if (campo.dataset.dependeDe) {
                try {
                    condicoes = [[campo.dataset.dependeDe, JSON.parse(campo.dataset.dependeValores || '["Sim"]')]];
                } catch (e) {
                    condicoes = [[campo.dataset.dependeDe, ["Sim"]]];
                }
            } else if (REGRAS[campo.name]) {
                condicoes = REGRAS[campo.name];
            }

            if (!condicoes) return;
            // Só liga se o campo que controla existe neste formulário.
            const existe = condicoes.some(([nome]) => Array.from(form.elements).some((el) => el.name === nome));
            if (existe) dependentes.push({ campo, bloco: blocoDe(campo), condicoes });
        });

        if (!dependentes.length) return;

        function atualizar() {
            // Repete algumas vezes para resolver cadeias (A -> B -> C).
            for (let volta = 0; volta < 4; volta++) {
                let mudou = false;
                dependentes.forEach(({ campo, bloco, condicoes }) => {
                    const mostrar = condicoes.some(([nome, aceitos]) =>
                        valoresDe(form, nome).some((v) => aceitos.includes(v)));

                    if (campo.disabled === !mostrar) return;
                    mudou = true;
                    campo.disabled = !mostrar;
                    bloco.hidden = !mostrar;
                    if (!mostrar) {
                        campo.classList.remove("is-invalid");
                        campo.setCustomValidity && campo.setCustomValidity("");
                    }
                });
                if (!mudou) break;
            }
        }

        form.addEventListener("change", atualizar);
        form.addEventListener("input", atualizar);
        form.addEventListener("reset", () => setTimeout(atualizar, 0));
        atualizar();
    }

    function iniciarTodos() {
        document.querySelectorAll("form").forEach(iniciar);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", iniciarTodos);
    } else {
        iniciarTodos();
    }
})();
