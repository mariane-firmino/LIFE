/* =========================================================
   CALENDÁRIO DA ENFERMARIA
   Busca as consultas reais no banco através da AgendaAPI.
========================================================= */

const calendarioAgendaMonths = [
    "Janeiro", "Fevereiro", "Março", "Abril",
    "Maio", "Junho", "Julho", "Agosto",
    "Setembro", "Outubro", "Novembro", "Dezembro"
];

const calendarioAgendaWeekDays = [
    "Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"
];

let calendarioAgendaDate = new Date(
    new Date().getFullYear(),
    new Date().getMonth(),
    1
);

let calendarioAgendaEvents = [];
let calendarioAgendaLoadError = false;


/* =========================================================
   INICIALIZAÇÃO
========================================================= */

document.addEventListener("DOMContentLoaded", async function () {
    const grid = document.getElementById("calendario-agenda-grid");
    const week = document.getElementById("calendario-agenda-week");
    const monthButton = document.getElementById("calendario-agenda-month-button");
    const yearButton = document.getElementById("calendario-agenda-year-button");
    const monthMenu = document.getElementById("calendario-agenda-month-menu");
    const yearMenu = document.getElementById("calendario-agenda-year-menu");
    const upcoming = document.getElementById("calendario-agenda-upcoming");
    const completed = document.getElementById("calendario-agenda-completed");

    if (!grid || !week || !monthButton || !yearButton ||
        !monthMenu || !yearMenu || !upcoming || !completed) {
        console.error("Calendário: não foi possível encontrar todos os elementos HTML.");
        return;
    }

    // Dias da semana
    week.innerHTML = "";
    calendarioAgendaWeekDays.forEach(function (nome) {
        const elemento = document.createElement("div");
        elemento.className = "calendario-agenda-week-day";
        elemento.textContent = nome;
        week.appendChild(elemento);
    });

    criarMenuMeses();
    criarMenuAnos();

    // Botões de mês e ano
    monthButton.addEventListener("click", function (event) {
        event.stopPropagation();
        yearMenu.classList.remove("is-open");
        monthMenu.classList.toggle("is-open");
    });

    yearButton.addEventListener("click", function (event) {
        event.stopPropagation();
        monthMenu.classList.remove("is-open");
        yearMenu.classList.toggle("is-open");
    });

    // Setas
    const botaoAnterior = document.getElementById("calendario-agenda-prev");
    const botaoProximo = document.getElementById("calendario-agenda-next");

    if (botaoAnterior) {
        botaoAnterior.addEventListener("click", function () {
            calendarioAgendaDate.setMonth(calendarioAgendaDate.getMonth() - 1);
            renderizarCalendario();
        });
    }

    if (botaoProximo) {
        botaoProximo.addEventListener("click", function () {
            calendarioAgendaDate.setMonth(calendarioAgendaDate.getMonth() + 1);
            renderizarCalendario();
        });
    }

    // Fecha os menus ao clicar fora
    document.addEventListener("click", fecharMenus);
    monthMenu.addEventListener("click", function (e) { e.stopPropagation(); });
    yearMenu.addEventListener("click", function (e) { e.stopPropagation(); });

    // Carrega as consultas e desenha
    await carregarConsultas();
    renderizarCalendario();


    /* =====================================================
       MENUS
    ===================================================== */

    function criarMenuMeses() {
        monthMenu.innerHTML = "";

        calendarioAgendaMonths.forEach(function (nome, indice) {
            const botao = document.createElement("button");
            botao.type = "button";
            botao.className = "calendario-agenda-dropdown-button";
            botao.textContent = nome;

            botao.addEventListener("click", function () {
                calendarioAgendaDate.setMonth(indice);
                fecharMenus();
                renderizarCalendario();
            });

            monthMenu.appendChild(botao);
        });
    }

    function criarMenuAnos() {
        yearMenu.innerHTML = "";
        const anoAtual = new Date().getFullYear();

        for (let ano = anoAtual - 10; ano <= anoAtual + 20; ano++) {
            const botao = document.createElement("button");
            botao.type = "button";
            botao.className = "calendario-agenda-dropdown-button";
            botao.textContent = ano;

            botao.addEventListener("click", function () {
                calendarioAgendaDate.setFullYear(ano);
                fecharMenus();
                renderizarCalendario();
            });

            yearMenu.appendChild(botao);
        }
    }

    function fecharMenus() {
        monthMenu.classList.remove("is-open");
        yearMenu.classList.remove("is-open");
    }


    /* =====================================================
       CARREGAR CONSULTAS DO BANCO
    ===================================================== */

    async function carregarConsultas() {
        try {
            if (
                typeof AgendaAPI === "undefined" ||
                typeof AgendaAPI.listarCompromissos !== "function"
            ) {
                throw new Error("AgendaAPI não foi carregada corretamente.");
            }

            const resposta = await AgendaAPI.listarCompromissos();

            if (!Array.isArray(resposta)) {
                throw new Error("A API não retornou uma lista de consultas.");
            }

            // Mantém somente eventos com data válida (AAAA-MM-DD)
            calendarioAgendaEvents = resposta.filter(function (evento) {
                return evento && /^\d{4}-\d{2}-\d{2}$/.test(evento.data || "");
            });

            calendarioAgendaLoadError = false;

        } catch (erro) {
            console.error("Erro ao carregar consultas:", erro);
            calendarioAgendaEvents = [];
            calendarioAgendaLoadError = true;
        }
    }


    /* =====================================================
       DESENHAR O CALENDÁRIO
    ===================================================== */

    function renderizarCalendario() {
        const ano = calendarioAgendaDate.getFullYear();
        const mes = calendarioAgendaDate.getMonth();

        monthButton.textContent = calendarioAgendaMonths[mes];
        yearButton.textContent = ano;
        grid.innerHTML = "";

        const primeiroDia = new Date(ano, mes, 1).getDay();
        const totalDias = new Date(ano, mes + 1, 0).getDate();
        const diasMesAnterior = new Date(ano, mes, 0).getDate();

        // Dias do mês anterior
        for (let i = primeiroDia - 1; i >= 0; i--) {
            criarDia(diasMesAnterior - i, true);
        }

        // Dias do mês atual
        for (let dia = 1; dia <= totalDias; dia++) {
            criarDia(dia, false, ano, mes);
        }

        // Completa a última semana com dias do próximo mês
        let proximoDia = 1;
        while (grid.children.length % 7 !== 0) {
            criarDia(proximoDia++, true);
        }

        renderizarPainelLateral();
    }

    function criarDia(numero, outroMes, ano, mes) {
        const celula = document.createElement("div");
        celula.className = "calendario-agenda-day";

        if (outroMes) {
            celula.classList.add("is-other-month");
        }

        const numeroElemento = document.createElement("span");
        numeroElemento.className = "calendario-agenda-day-number";
        numeroElemento.textContent = numero;
        celula.appendChild(numeroElemento);

        if (!outroMes) {
            obterEventos(ano, mes, numero).forEach(function (evento) {
                const item = document.createElement("div");
                item.className = "calendario-agenda-event";

                const bolinha = document.createElement("span");
                bolinha.className = "calendario-agenda-event-dot";
                bolinha.style.backgroundColor = evento.cor || "#198754";

                // No calendário aparece só o tipo (sem horário)
                const nome = document.createElement("span");
                nome.className = "calendario-agenda-event-name";
                nome.textContent = evento.tipo || "Consulta";

                item.appendChild(bolinha);
                item.appendChild(nome);
                celula.appendChild(item);
            });
        }

        grid.appendChild(celula);
    }

    function obterEventos(ano, mes, dia) {
        return calendarioAgendaEvents.filter(function (evento) {
            const partes = evento.data.split("-");

            return (
                Number(partes[0]) === ano &&
                Number(partes[1]) - 1 === mes &&
                Number(partes[2]) === dia
            );
        });
    }


    /* =====================================================
       PAINEL LATERAL
       O PHP retorna "concluido"/"realizado" ou "proximo".
    ===================================================== */

    function eventoRealizado(evento) {
        return evento.status === "concluido" || evento.status === "realizado";
    }

    function renderizarPainelLateral() {
        upcoming.innerHTML = "";
        completed.innerHTML = "";

        // Se a API falhou, mantém a mensagem de erro (não "Nenhuma consulta")
        if (calendarioAgendaLoadError) {
            upcoming.innerHTML =
                '<div class="calendario-agenda-empty">' +
                'Não foi possível carregar as consultas.' +
                '</div>';
            completed.innerHTML =
                '<div class="calendario-agenda-empty">' +
                'Verifique a conexão com a API.' +
                '</div>';
            return;
        }

        const ano = calendarioAgendaDate.getFullYear();
        const mes = calendarioAgendaDate.getMonth();

        const eventosDoMes = calendarioAgendaEvents.filter(function (evento) {
            const partes = evento.data.split("-");
            return Number(partes[0]) === ano && Number(partes[1]) - 1 === mes;
        });

        preencherPainel(eventosDoMes.filter(function (e) { return !eventoRealizado(e); }), upcoming);
        preencherPainel(eventosDoMes.filter(eventoRealizado), completed);
    }

    function preencherPainel(eventos, container) {
        if (eventos.length === 0) {
            container.innerHTML =
                '<div class="calendario-agenda-empty">Nenhuma consulta</div>';
            return;
        }

        // Ordena por data e depois por horário
        eventos.sort(function (a, b) {
            return (a.data + (a.horario || "")).localeCompare(b.data + (b.horario || ""));
        });

        eventos.forEach(function (evento) {
            const item = document.createElement("div");
            item.className = "calendario-agenda-list-item";

            const bolinha = document.createElement("span");
            bolinha.className = "calendario-agenda-list-dot";
            bolinha.style.backgroundColor = evento.cor || "#198754";

            // No painel o horário aparece (HH:MM)
            const texto = document.createElement("span");
            texto.textContent =
                (evento.horario || "").substring(0, 5) +
                " - " + (evento.tipo || "Consulta");

            item.appendChild(bolinha);
            item.appendChild(texto);
            container.appendChild(item);
        });
    }
});