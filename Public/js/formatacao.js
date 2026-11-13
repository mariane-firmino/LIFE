
// =====================================================================
// MÁSCARAS (CPF, telefone e RG)
// Para usar num campo, basta colocar no <input>:
//   data-mascara="cpf"       ->  000.000.000-00
//   data-mascara="telefone"  ->  (00) 00000-0000  ou  (00) 0000-0000
//   data-mascara="rg"        ->  só números, o último pode ser X (até 14)
//   data-mascara="altura"    ->  0,00 (metros, até 3 números)
//   data-mascara="peso"      ->  000,00 (kg, até 3 números + 2 decimais)
//   data-mascara="nome"      ->  só letras (com acento) e espaços
// =====================================================================

const mascaras = {
    cpf: function (valor) {
        // Remove tudo que não for número e limita a 11 números
        let numeros = valor.replace(/\D/g, "").substring(0, 11);

        return numeros
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    },

    telefone: function (valor) {
        // Limita a 11 números (DDD + telefone)
        const numeros = valor.replace(/\D/g, "").substring(0, 11);

        if (numeros.length === 11) {
            // Celular: (00) 00000-0000
            return numeros.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
        }
        if (numeros.length >= 7) {
            // Fixo, ou celular ainda sendo digitado: (00) 0000-0000
            return numeros.replace(/^(\d{2})(\d{4})(\d+)$/, "($1) $2-$3");
        }
        if (numeros.length > 2) {
            return numeros.replace(/^(\d{2})(\d+)$/, "($1) $2");
        }

        return numeros;
    },

    rg: function (valor) {
        // Números, até 14 caracteres; o dígito final pode ser X.
        // (Antes cortava em 6 e RGs comuns, de 7 a 9 dígitos, perdiam números.)
        const limpo = valor.toUpperCase().replace(/[^0-9X]/g, "");
        const numeros = limpo.replace(/X/g, "");
        const terminaComX = /X$/.test(limpo) && numeros.length > 0;
        return numeros.substring(0, terminaComX ? 13 : 14) + (terminaComX ? "X" : "");
    },

    altura: function (valor) {
        // Só números (até 3); a vírgula entra sozinha depois do 1º: 170 -> 1,70
        // O 1º número vai de 0 a 3 (altura em metros, até 3 m).
        const numeros = valor.replace(/\D/g, "").replace(/^[4-9]+/, "").substring(0, 3);
        return numeros.length > 1 ? numeros[0] + "," + numeros.substring(1) : numeros;
    },

    peso: function (valor) {
        // Até 3 números inteiros e 2 decimais (ex.: 65,5 ou 120,25).
        const limpo = valor.replace(/\./g, ",").replace(/[^\d,]/g, "");
        const virgula = limpo.indexOf(",");
        let inteiro = (virgula === -1 ? limpo : limpo.substring(0, virgula)).replace(/,/g, "");
        let decimal = virgula === -1 ? "" : limpo.substring(virgula + 1).replace(/,/g, "");
        let temVirgula = virgula !== -1;
        // Digitou o 4º número sem vírgula: ele vira a parte decimal.
        if (inteiro.length > 3) {
            decimal = inteiro.substring(3) + decimal;
            inteiro = inteiro.substring(0, 3);
            temVirgula = true;
        }
        return temVirgula ? inteiro + "," + decimal.substring(0, 2) : inteiro;
    },

    nome: function (valor) {
        // Só letras (com ou sem acento) e um espaço entre as palavras.
        return valor.replace(/[^\p{L} ]/gu, "").replace(/ {2,}/g, " ").replace(/^ /, "");
    }
};

// Quantos números um valor já salvo precisa ter para ser formatado ao abrir
// a página. Valores fora disso (dados antigos) ficam como estão, para não
// cortar nenhum número.
const tamanhosValidos = {
    cpf: [11],
    telefone: [10, 11],
    rg: [],
    altura: [],
    peso: [],
    nome: []
};

document.querySelectorAll("[data-mascara]").forEach(function (campo) {
    const tipo = campo.dataset.mascara;
    const formatar = mascaras[tipo];
    if (!formatar) return;

    campo.addEventListener("input", function () {
        const novo = formatar(this.value);
        // Só troca o valor se algo mudou (não joga o cursor para o fim à toa).
        if (novo !== this.value) this.value = novo;
    });

    // Nome: tira o espaço que sobrar no fim ao sair do campo.
    if (tipo === "nome") {
        campo.addEventListener("blur", function () { this.value = this.value.trim(); });
    }

    // Formata o valor que já veio preenchido (ex.: telas de edição)
    const numeros = campo.value.replace(/\D/g, "");
    if (tamanhosValidos[tipo].includes(numeros.length)) {
        campo.value = formatar(campo.value);
    }
});

// Aplica formatação ao siape
const inputSiape = document.querySelector("#siape");

if (inputSiape) {
    inputSiape.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "").substring(0, 7);
    });
}

// impede letras no nome

const camposNome = document.querySelectorAll(
    'input[name="nome"], input[name="nome_aluno"], input[name="nome_responsavel"]'
);

camposNome.forEach(function (campo) {
    campo.addEventListener("input", function () {
        // Remove todos os números digitados
        this.value = this.value.replace(/[0-9]/g, "");
    });
});


// Formatação para a senha

const campoSenha = document.querySelector("#senha");

if (campoSenha) {
    const barra = document.querySelector("#barra-forca");
    const texto = document.querySelector("#texto-forca");

    const requisitos = {
        tamanho: document.querySelector("#req-tamanho"),
        maiuscula: document.querySelector("#req-maiuscula"),
        minuscula: document.querySelector("#req-minuscula"),
        numero: document.querySelector("#req-numero"),
        especial: document.querySelector("#req-especial")
    };

    function atualizarRequisito(elemento, valido, descricao) {
        elemento.textContent =
            (valido ? "✓ " : "○ ") + descricao;

        elemento.classList.toggle("text-success", valido);
        elemento.classList.toggle("text-white-50", !valido);
    }

    campoSenha.addEventListener("input", function () {
        const senha = this.value;

        const testes = {
            tamanho: senha.length >= 8,
            maiuscula: /[A-Z]/.test(senha),
            minuscula: /[a-z]/.test(senha),
            numero: /[0-9]/.test(senha),
            especial: /[^A-Za-z0-9\s]/.test(senha)
        };

        atualizarRequisito(
            requisitos.tamanho,
            testes.tamanho,
            "Pelo menos 8 caracteres"
        );

        atualizarRequisito(
            requisitos.maiuscula,
            testes.maiuscula,
            "Uma letra maiúscula"
        );

        atualizarRequisito(
            requisitos.minuscula,
            testes.minuscula,
            "Uma letra minúscula"
        );

        atualizarRequisito(
            requisitos.numero,
            testes.numero,
            "Um número"
        );

        atualizarRequisito(
            requisitos.especial,
            testes.especial,
            "Um caractere especial (@, #, !...)"
        );

        const pontos = Object.values(testes).filter(Boolean).length;

        const porcentagem = (pontos / 5) * 100;
        barra.style.width = porcentagem + "%";

        if (senha.length === 0) {
            barra.className = "progress-bar";
            texto.textContent =
                "Digite uma senha para ver sua força.";
        } else if (pontos <= 2) {
            barra.className = "progress-bar bg-danger";
            texto.textContent = "Senha fraca";
        } else if (pontos <= 4) {
            barra.className = "progress-bar bg-warning";
            texto.textContent = "Senha média";
        } else {
            barra.className = "progress-bar bg-success";
            texto.textContent = "Senha forte!";
        }
    });
}


// =====================================================================
// FAIXA NORMAL (altura e peso)
// Valores fora do normal deixam o campo vermelho com a mensagem e
// impedem o envio do formulário. O servidor confere de novo.
// =====================================================================

const faixasNormais = {
    altura: { min: 0, max: 3, msg: "Altura fora do normal: informe até 3 m (ex: 1,70)." },
    peso: { min: 0, max: 300, msg: "Peso fora do normal: informe até 300 kg (ex: 65,5)." }
};

function conferirFaixa(campo) {
    const faixa = faixasNormais[campo.dataset.mascara];
    const texto = campo.value.trim();
    const numero = parseFloat(texto.replace(",", "."));
    const foraDoNormal = texto !== "" && (isNaN(numero) || numero <= faixa.min || numero > faixa.max);

    campo.setCustomValidity(foraDoNormal ? faixa.msg : "");
    campo.classList.toggle("is-invalid", foraDoNormal);

    // Mostra a mensagem logo abaixo do campo (reaproveita a do servidor, se houver).
    let aviso = campo.parentElement.querySelector(".invalid-feedback");
    if (foraDoNormal) {
        if (!aviso) {
            aviso = document.createElement("div");
            aviso.className = "invalid-feedback";
            campo.insertAdjacentElement("afterend", aviso);
        }
        aviso.textContent = faixa.msg;
    } else if (aviso && aviso.dataset.faixa === "1") {
        aviso.textContent = "";
    }
    if (aviso) aviso.dataset.faixa = "1";

    return !foraDoNormal;
}

document.querySelectorAll("[data-mascara=\"altura\"], [data-mascara=\"peso\"]").forEach(function (campo) {
    // Enquanto digita só limpa o erro; a conferência é ao sair do campo
    // (senão "1" já ficaria vermelho antes de terminar "1,70").
    campo.addEventListener("input", function () {
        if (this.classList.contains("is-invalid")) conferirFaixa(this);
    });
    campo.addEventListener("blur", function () { conferirFaixa(this); });

    if (campo.form) {
        campo.form.addEventListener("submit", function (e) {
            if (!conferirFaixa(campo)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                // Abre a aba onde o campo está (tela de edição com abas).
                const aba = campo.closest(".tab-pane");
                if (aba && window.bootstrap) {
                    const botao = document.querySelector("[data-bs-target=\"#" + aba.id + "\"]");
                    if (botao) bootstrap.Tab.getOrCreateInstance(botao).show();
                }
                campo.focus();
            }
        }, true);
    }
});
