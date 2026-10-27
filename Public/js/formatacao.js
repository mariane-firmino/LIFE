
// =====================================================================
// MÁSCARAS (CPF, telefone e RG)
// Para usar num campo, basta colocar no <input>:
//   data-mascara="cpf"       ->  000.000.000-00
//   data-mascara="telefone"  ->  (00) 00000-0000  ou  (00) 0000-0000
//   data-mascara="rg"        ->  só números (até 6)
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
        // O banco guarda no máximo 6 caracteres
        return valor.replace(/\D/g, "").substring(0, 6);
    }
};

// Quantos números um valor já salvo precisa ter para ser formatado ao abrir
// a página. Valores fora disso (dados antigos) ficam como estão, para não
// cortar nenhum número.
const tamanhosValidos = {
    cpf: [11],
    telefone: [10, 11],
    rg: []
};

document.querySelectorAll("[data-mascara]").forEach(function (campo) {
    const tipo = campo.dataset.mascara;
    const formatar = mascaras[tipo];
    if (!formatar) return;

    campo.addEventListener("input", function () {
        this.value = formatar(this.value);
    });

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

