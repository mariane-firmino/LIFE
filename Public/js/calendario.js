/* =========================================================
   CALENDÁRIO DA ENFERMARIA — FullCalendar + Bootstrap
   Depende de: FullCalendar (global), bootstrap.bundle,
   window.CALENDARIO_CONFIG = { url, cores }
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    const cfg = window.CALENDARIO_CONFIG;
    const el = document.getElementById("calendario");
    if (!el || !cfg || typeof FullCalendar === "undefined") {
        console.error("Calendário: FullCalendar ou configuração não encontrados.");
        return;
    }

    const modalForm = new bootstrap.Modal(document.getElementById("modalConsulta"));
    const modalDet = new bootstrap.Modal(document.getElementById("modalDetalhes"));
    const form = document.getElementById("form-consulta");
    const erroBox = document.getElementById("form-consulta-erro");
    const btnSalvar = document.getElementById("form-consulta-salvar");
    const $ = (id) => document.getElementById(id);

    let eventoAtual = null; // evento aberto nos detalhes
    const celular = () => window.innerWidth < 768;

    /* ---------------------------------------------------
       Utilidades
    --------------------------------------------------- */
    const pad = (n) => String(n).padStart(2, "0");
    const dataISO = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const horaHM = (d) => `${pad(d.getHours())}:${pad(d.getMinutes())}`;
    const realizado = (ev) => ev.start < new Date();

    async function post(rota, dados) {
        const resp = await fetch(`${cfg.url}/${rota}`, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: new URLSearchParams(dados)
        });

        let json = {};
        try { json = await resp.json(); } catch (e) { /* resposta sem JSON */ }

        if (!resp.ok || json.ok === false) {
            throw new Error(json.mensagem || json.erro || "Não foi possível concluir a ação.");
        }
        return json;
    }

    /* ---------------------------------------------------
       Calendário
    --------------------------------------------------- */
    const calendar = new FullCalendar.Calendar(el, {
        locale: "pt-br",
        initialView: celular() ? "listMonth" : "dayGridMonth",
        height: "auto",
        firstDay: 0,
        nowIndicator: true,
        navLinks: true,
        dayMaxEvents: 3,
        editable: true,           // arrastar para reagendar
        eventStartEditable: true,
        eventDurationEditable: false,
        slotMinTime: "07:00:00",
        slotMaxTime: "20:00:00",
        allDaySlot: false,
        eventTimeFormat: { hour: "2-digit", minute: "2-digit", hour12: false },
        slotLabelFormat: { hour: "2-digit", minute: "2-digit", hour12: false },
        noEventsText: "Nenhuma consulta neste período",

        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,listMonth"
        },
        buttonText: { today: "Hoje", month: "Mês", week: "Semana", list: "Lista" },

        events: `${cfg.url}/listarConsultas`,

        eventClassNames: (arg) => (realizado(arg.event) ? ["cal-evento-passado"] : []),

        eventDidMount: (arg) => {
            const p = arg.event.extendedProps;
            arg.el.title =
                `${horaHM(arg.event.start)} - ${p.tipo}` +
                (p.responsavel ? `\nResponsável: ${p.responsavel}` : "") +
                (p.descricao ? `\n${p.descricao}` : "");
        },

        // Clicar em um dia vazio: abre o formulário já com a data
        dateClick: (info) => {
            if (calendar.view.type === "dayGridMonth" && celular()) {
                calendar.changeView("listMonth", info.date);
                return;
            }
            abrirFormulario(null, info.date);
        },

        eventClick: (info) => {
            info.jsEvent.preventDefault();
            abrirDetalhes(info.event);
        },

        // Arrastou: salva a nova data/hora
        eventDrop: async (info) => {
            const ev = info.event;
            try {
                await post("reagendarConsulta", {
                    cons_id: ev.id,
                    data: dataISO(ev.start),
                    horario: horaHM(ev.start)
                });
                atualizarPainel();
            } catch (e) {
                info.revert();
                alert(e.message);
            }
        },

        // Atualiza o painel lateral sempre que a lista de eventos/visão muda
        eventsSet: atualizarPainel,
        datesSet: atualizarPainel
    });

    calendar.render();

    // Ajusta a visão ao girar/redimensionar o celular
    let ultimoCelular = celular();
    window.addEventListener("resize", function () {
        if (celular() !== ultimoCelular) {
            ultimoCelular = celular();
            calendar.changeView(ultimoCelular ? "listMonth" : "dayGridMonth");
        }
    });

    /* ---------------------------------------------------
       Painel lateral (mês visível)
    --------------------------------------------------- */
    function atualizarPainel() {
        const ref = calendar.getDate(); // dentro do mês exibido
        const ano = ref.getFullYear();
        const mes = ref.getMonth();

        const doMes = calendar.getEvents()
            .filter((e) => e.start.getFullYear() === ano && e.start.getMonth() === mes)
            .sort((a, b) => a.start - b.start);

        preencher($("cal-upcoming"), doMes.filter((e) => !realizado(e)), $("cal-count-upcoming"));
        preencher($("cal-done"), doMes.filter(realizado), $("cal-count-done"));
    }

    function preencher(container, eventos, contador) {
        container.replaceChildren();
        contador.textContent = eventos.length;

        if (!eventos.length) {
            const vazio = document.createElement("div");
            vazio.className = "cal-empty";
            vazio.textContent = "Nenhuma consulta";
            container.appendChild(vazio);
            return;
        }

        eventos.forEach((ev) => {
            const item = document.createElement("button");
            item.type = "button";
            item.className = "cal-item";
            item.addEventListener("click", () => abrirDetalhes(ev));

            const dot = document.createElement("span");
            dot.className = "cal-dot";
            dot.style.background = ev.backgroundColor;

            const dia = document.createElement("span");
            dia.className = "cal-item-dia";
            dia.textContent = `${pad(ev.start.getDate())}/${pad(ev.start.getMonth() + 1)}`;

            const txt = document.createElement("span");
            txt.className = "cal-item-txt";
            txt.textContent = `${horaHM(ev.start)} · ${ev.extendedProps.tipo}`;

            item.append(dot, dia, txt);
            container.appendChild(item);
        });
    }

    /* ---------------------------------------------------
       Detalhes
    --------------------------------------------------- */
    function abrirDetalhes(ev) {
        eventoAtual = ev;
        const p = ev.extendedProps;

        $("det-data").textContent = ev.start.toLocaleDateString("pt-BR", {
            weekday: "long", day: "2-digit", month: "long", year: "numeric"
        });
        $("det-hora").textContent = horaHM(ev.start);
        $("det-tipo").textContent = p.tipo;
        $("det-responsavel").textContent = p.responsavel || "Não informado";
        $("det-descricao").textContent = p.descricao || "—";
        $("det-observacao").textContent = p.observacao || "—";

        const badge = $("det-badge");
        badge.textContent = realizado(ev) ? "Realizada" : "Agendada";
        badge.className = "cal-badge " + (realizado(ev) ? "cal-badge-done" : "cal-badge-next");
        badge.style.borderColor = ev.backgroundColor;

        modalDet.show();
    }

    $("det-editar").addEventListener("click", function () {
        modalDet.hide();
        abrirFormulario(eventoAtual);
    });

    $("det-excluir").addEventListener("click", async function () {
        if (!eventoAtual) return;
        if (!confirm("Excluir este compromisso? Essa ação não pode ser desfeita.")) return;

        try {
            await post("excluirConsulta", { cons_id: eventoAtual.id });
            eventoAtual.remove();
            modalDet.hide();
        } catch (e) {
            alert(e.message);
        }
    });

    /* ---------------------------------------------------
       Formulário (adicionar / editar)
    --------------------------------------------------- */
    function abrirFormulario(ev, dataSugerida) {
        form.reset();
        erroBox.hidden = true;

        const editando = !!ev;
        $("modalConsultaLabel").textContent = editando ? "Editar Consulta" : "Adicionar Consulta";
        form.action = `${cfg.url}/${editando ? "editarConsulta" : "cadastrarConsulta"}`;
        $("cons_id").value = editando ? ev.id : "";

        if (editando) {
            const p = ev.extendedProps;
            $("dataConsulta").value = dataISO(ev.start);
            $("horaConsulta").value = horaHM(ev.start);
            $("tipoConsulta").value = p.tipo;
            $("responsavelConsulta").value = p.responsavel || "";
            $("descricaoConsulta").value = p.descricao || "";
            $("observacaoConsulta").value = p.observacao || "";
        } else {
            $("dataConsulta").value = dataISO(dataSugerida || new Date());
        }

        modalForm.show();
    }

    $("cal-btn-novo").addEventListener("click", () => abrirFormulario(null));

    // Envia sem recarregar a página; só atualiza o calendário.
    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        erroBox.hidden = true;
        btnSalvar.disabled = true;

        try {
            await post(
                form.action.split("/").pop(),
                Object.fromEntries(new FormData(form))
            );
            modalForm.hide();
            calendar.refetchEvents();
        } catch (err) {
            erroBox.textContent = err.message;
            erroBox.hidden = false;
        } finally {
            btnSalvar.disabled = false;
        }
    });
});
