
const inputCpf = document.querySelector("#cpf");
const inputTelefone = document.querySelector("#telefone");
const inputTelefoneAluno = document.querySelector("#inputtelefonealuno");

// Função para permitir somente números e formatar o CPF
if (inputCpf) {
    inputCpf.addEventListener("input", function () {
        // Remove tudo que não for número
        let numeros = this.value.replace(/\D/g, "");

        // Limita a 11 números
        numeros = numeros.substring(0, 11);

        // Formata o CPF
        numeros = numeros
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d{1,2})$/, "$1-$2");

        this.value = numeros;
    });
}

// Função para permitir somente números e formatar o telefone
function formatarTelefone(campo) {
    if (!campo) return;

    campo.addEventListener("input", function () {
        // Remove letras, espaços e outros caracteres
        let numeros = this.value.replace(/\D/g, "");

        // Limita a 11 números (DDD + telefone)
        numeros = numeros.substring(0, 11);

        // Formata o telefone
        if (numeros.length > 0) {
            numeros = numeros.replace(/^(\d{2})(\d)/, "($1) $2");
        }

        if (numeros.length > 10) {
            numeros = numeros.replace(/(\d{5})(\d{4})$/, "$1-$2");
        } else if (numeros.length > 9) {
            numeros = numeros.replace(/(\d{4})(\d{4})$/, "$1-$2");
        }

        this.value = numeros;
    });
}

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


// Aplica a formatação aos dois campos
formatarTelefone(inputTelefone);
formatarTelefone(inputTelefoneAluno);

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

