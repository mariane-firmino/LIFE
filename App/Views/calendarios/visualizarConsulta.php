<!-- Modal de detalhes. Preenchido pelo JavaScript ao clicar em um compromisso. -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalDetalhesLabel">Detalhes do compromisso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <span class="cal-badge" id="det-badge"></span>

                <dl class="cal-details">
                    <div>
                        <dt>Data</dt>
                        <dd><img src="<?= URL ?>/img/Calendariodois.png" width="18" alt=""> <span id="det-data"></span></dd>
                    </div>
                    <div>
                        <dt>Horário</dt>
                        <dd><img src="<?= URL ?>/img/relogio.png" width="18" alt=""> <span id="det-hora"></span></dd>
                    </div>
                    <div>
                        <dt>Tipo</dt>
                        <dd><img src="<?= URL ?>/img/Checklist.png" width="18" alt=""> <span id="det-tipo"></span></dd>
                    </div>
                    <div>
                        <dt>Responsável</dt>
                        <dd><img src="<?= URL ?>/img/Checklist.png" width="18" alt=""> <span id="det-responsavel"></span></dd>
                    </div>
                    <div class="full">
                        <dt>Descrição</dt>
                        <dd id="det-descricao"></dd>
                    </div>
                    <div class="full">
                        <dt>Observações</dt>
                        <dd id="det-observacao"></dd>
                    </div>
                </dl>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger" id="det-excluir">Excluir</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-success" id="det-editar">Editar</button>
                </div>
            </div>

        </div>
    </div>
</div>
