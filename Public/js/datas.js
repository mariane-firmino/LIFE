/* =========================================================
   DATAS NO FORMATO BRASILEIRO (dd/mm/aaaa)

   O <input type="date"> nativo segue o idioma do navegador:
   num Windows/Chrome em inglês ele aparece como mm/dd/yyyy.
   Este script troca cada campo de data por um campo de texto
   com máscara dd/mm/aaaa. O campo original vira "hidden" e
   continua com o mesmo name/id e o valor em AAAA-MM-DD, então
   o PHP e os outros scripts (ex.: calendario.js) não mudam.
========================================================= */
(function () {
    "use strict";

    const descritorValor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value");

    const isoParaBr = (iso) => {
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || "");
        return m ? `${m[3]}/${m[2]}/${m[1]}` : "";
    };

    const brParaIso = (br) => {
        const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(br || "");
        if (!m) return "";
        const [, d, mes, a] = m;
        const data = new Date(Number(a), Number(mes) - 1, Number(d));
        const valida = data.getFullYear() === Number(a)
            && data.getMonth() === Number(mes) - 1
            && data.getDate() === Number(d);
        return valida ? `${a}-${mes}-${d}` : "";
    };

    const mascarar = (valor) => {
        const n = valor.replace(/\D/g, "").substring(0, 8);
        if (n.length > 4) return `${n.substring(0, 2)}/${n.substring(2, 4)}/${n.substring(4)}`;
        if (n.length > 2) return `${n.substring(0, 2)}/${n.substring(2)}`;
        return n;
    };

    function converter(original) {
        if (original.dataset.dataBr === "1") return;
        original.dataset.dataBr = "1";

        const visivel = document.createElement("input");
        visivel.type = "text";
        visivel.inputMode = "numeric";
        visivel.autocomplete = "off";
        visivel.placeholder = "dd/mm/aaaa";
        visivel.maxLength = 10;
        visivel.className = original.className;
        visivel.required = original.required;
        visivel.disabled = original.disabled;
        visivel.readOnly = original.readOnly;
        visivel.title = "Data no formato dd/mm/aaaa";
        if (original.getAttribute("aria-describedby")) {
            visivel.setAttribute("aria-describedby", original.getAttribute("aria-describedby"));
        }

        // O rótulo (<label for>) passa a apontar para o campo visível.
        if (original.id) {
            visivel.id = original.id + "_br";
            document.querySelectorAll(`label[for="${CSS.escape(original.id)}"]`)
                .forEach((label) => label.setAttribute("for", visivel.id));
        }

        const minimo = original.min;
        const maximo = original.max;

        original.type = "hidden";
        original.required = false;
        original.insertAdjacentElement("beforebegin", visivel);
        visivel.value = isoParaBr(descritorValor.get.call(original));

        // Quando outro script define campo.value = "2026-11-09",
        // o campo visível também é atualizado.
        let versao = 0;
        Object.defineProperty(original, "value", {
            configurable: true,
            get() { return descritorValor.get.call(this); },
            set(v) {
                versao++;
                descritorValor.set.call(this, v);
                visivel.value = isoParaBr(v);
                visivel.setCustomValidity("");
            }
        });

        const validar = () => {
            const texto = visivel.value.trim();
            const iso = brParaIso(texto);
            let mensagem = "";

            if (texto !== "" && iso === "") {
                mensagem = "Digite uma data válida no formato dd/mm/aaaa.";
            } else if (iso && minimo && iso < minimo) {
                mensagem = `A data deve ser a partir de ${isoParaBr(minimo)}.`;
            } else if (iso && maximo && iso > maximo) {
                mensagem = `A data deve ser até ${isoParaBr(maximo)}.`;
            }

            visivel.setCustomValidity(mensagem);
            const anterior = descritorValor.get.call(original);
            descritorValor.set.call(original, mensagem ? "" : iso);
            if (anterior !== descritorValor.get.call(original)) {
                original.dispatchEvent(new Event("input", { bubbles: true }));
                original.dispatchEvent(new Event("change", { bubbles: true }));
            }
        };

        visivel.addEventListener("input", function () {
            this.value = mascarar(this.value);
            validar();
        });
        visivel.addEventListener("blur", validar);

        // Botão "Limpar"/form.reset(): volta o campo oculto ao valor inicial.
        // Se um script definir a data logo depois do reset, o valor dele vale.
        const valorInicial = descritorValor.get.call(original);
        if (original.form) {
            original.form.addEventListener("reset", () => {
                const versaoNoReset = versao;
                setTimeout(() => {
                    if (versao === versaoNoReset) {
                        original.value = valorInicial;
                    }
                }, 0);
            });
        }
    }

    function converterTodos(raiz) {
        (raiz || document).querySelectorAll('input[type="date"]').forEach(converter);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => converterTodos());
    } else {
        converterTodos();
    }

    window.LifeDatas = { converterTodos, isoParaBr, brParaIso };
})();
