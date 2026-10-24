<?php
/**
 * Modal único de formulário: serve para ADICIONAR e para EDITAR.
 * O JavaScript troca o título, a action e preenche os campos.
 * Espera $tipos e $responsaveis (definidas em calendario.php).
 */
?>
<div class="modal fade" id="modalConsulta" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="modalConsultaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalConsultaLabel">Adicionar Consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <form id="form-consulta" action="<?= URL ?>/calendarios/cadastrarConsulta" method="POST">
                <div class="modal-body">

                    <input type="hidden" name="cons_id" id="cons_id">

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="dataConsulta" class="form-label fw-bold">Data</label>
                            <input type="date" class="form-control" id="dataConsulta" name="dataConsulta" required>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="horaConsulta" class="form-label fw-bold">Hora</label>
                            <input type="time" class="form-control" id="horaConsulta" name="horaConsulta" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tipoConsulta" class="form-label fw-bold">Tipo de Consulta</label>
                        <select class="form-select" id="tipoConsulta" name="tipoConsulta" required>
                            <option value="" selected disabled>Selecione uma opção</option>
                            <?php foreach ($tipos as $nome => $cor): ?>
                                <option value="<?= htmlspecialchars($nome) ?>"><?= htmlspecialchars($nome) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="responsavelConsulta" class="form-label fw-bold">Responsável</label>
                        <select class="form-select" id="responsavelConsulta" name="responsavelConsulta">
                            <option value="" selected>Selecione uma opção</option>
                            <?php foreach ($responsaveis as $r): ?>
                                <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descricaoConsulta" class="form-label fw-bold">Descrição</label>
                        <input type="text" class="form-control" id="descricaoConsulta" name="descricaoConsulta"
                            maxlength="500" placeholder="Digite a descrição da consulta" required>
                    </div>

                    <div class="mb-1">
                        <label for="observacaoConsulta" class="form-label fw-bold">Observação (Opcional)</label>
                        <textarea class="form-control" id="observacaoConsulta" name="observacaoConsulta" rows="3"
                            maxlength="2000" placeholder="Digite a observação da consulta"></textarea>
                    </div>

                    <div id="form-consulta-erro" class="text-danger small mt-2" role="alert" hidden></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="form-consulta-salvar">Salvar</button>
                </div>
            </form>

        </div>
    </div>
</div>
