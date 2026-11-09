<?php
// =====================================================================
// VIEW: FICHA DO ESTUDANTE PARA IMPRESSÃO -> App/Views/alunos/imprimirFicha.php
// Versão sem menu, pensada para papel A4 (ou "Salvar como PDF").
// =====================================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL . '/usuarios/login');
    exit;
}

$aluno     = $dados['aluno'];
$saude     = $dados['saude'];
$imc       = $dados['imc'] ?? null;
$condicoes = $dados['condicoes'] ?? [];
$atencao   = $dados['atencao'];
$historico = $dados['historico'] ?? [];
$id        = (int) $aluno->id;

// Escapa e mostra "Não informado" quando vazio.
$fv = function ($valor, $padrao = 'Não informado') {
    $valor = trim((string) ($valor ?? ''));
    if ($valor === '') {
        return '<span class="fi-vazio">' . $padrao . '</span>';
    }
    return htmlspecialchars(html_entity_decode($valor, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
};

// "Sim — detalhe".
$fvSim = function ($resposta, $qual) use ($fv) {
    $resposta = trim((string) ($resposta ?? ''));
    $qual = trim((string) ($qual ?? ''));
    return ($resposta === 'Sim' && $qual !== '') ? $fv($resposta . ' — ' . $qual) : $fv($resposta);
};

// Telefone (69) 99999-9999.
$fvTel = function ($numero) use ($fv) {
    $d = preg_replace('/\D/', '', (string) $numero);
    if (strlen($d) == 11) {
        return $fv('(' . substr($d, 0, 2) . ') ' . substr($d, 2, 5) . '-' . substr($d, 7));
    }
    if (strlen($d) == 10) {
        return $fv('(' . substr($d, 0, 2) . ') ' . substr($d, 2, 4) . '-' . substr($d, 6));
    }
    return $fv($numero);
};

// Data no formato brasileiro.
$dataBr = function ($data, $comHora = false) {
    if (empty($data)) {
        return '';
    }
    $ts = strtotime((string) $data);
    return $ts ? date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $ts) : '';
};

$s = function ($campo) use ($saude) {
    return $saude->$campo ?? '';
};

$alturaTexto = ($aluno->altura !== null && $aluno->altura !== '')
    ? number_format((float) $aluno->altura, 2, ',', '') . ' m' : '';
$pesoTexto = ($aluno->peso !== null && $aluno->peso !== '')
    ? rtrim(rtrim(number_format((float) $aluno->peso, 2, ',', ''), '0'), ',') . ' kg' : '';
$imcTexto = $imc ? $imc['texto'] . (!empty($imc['classe']) ? ' (' . $imc['classe'] . ')' : '') : '';

$fotoAluno = (!empty($aluno->foto) && is_file(dirname(__DIR__, 3) . '/Public/uploads/alunos/' . basename((string) $aluno->foto)))
    ? URL . '/uploads/alunos/' . basename((string) $aluno->foto)
    : URL . '/img/user.avif';

$fuso = new DateTimeZone('America/Porto_Velho');
$emitidoEm = (new DateTime('now', $fuso))->format('d/m/Y \à\s H:i');
$emitidoPor = $_SESSION['usuario_nome'] ?? '';

// Linha de rótulo + valor.
$linha = function ($rotulo, $valorHtml) {
    return '<div class="fi-campo"><span class="fi-rotulo">' . $rotulo . '</span><span class="fi-valor">' . $valorHtml . '</span></div>';
};
?>
<style>
    .fi-pagina { max-width: 210mm; margin: 24px auto; padding: 16mm 14mm; background: #fff; color: #111;
        box-shadow: 0 2px 12px rgba(0,0,0,.15); font-size: 12px; line-height: 1.4; }
    .fi-acoes { max-width: 210mm; margin: 16px auto 0; display: flex; gap: 8px; justify-content: flex-end; padding: 0 8px; }
    .fi-topo { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #198754; padding-bottom: 8px; margin-bottom: 12px; gap: 12px; }
    .fi-topo img { height: 44px; }
    .fi-topo h1 { font-size: 18px; margin: 0; font-weight: 700; }
    .fi-topo small { color: #555; }
    .fi-ident { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 10px; }
    .fi-foto { width: 90px; height: 110px; object-fit: cover; border: 1px solid #999; border-radius: 4px; flex-shrink: 0; }
    .fi-nome { font-size: 16px; font-weight: 700; margin: 0 0 4px; }
    .fi-secao { margin-top: 12px; break-inside: avoid; page-break-inside: avoid; }
    .fi-secao h2 { font-size: 13px; font-weight: 700; text-transform: uppercase; color: #146c43; border-bottom: 1px solid #ccc; padding-bottom: 2px; margin: 0 0 6px; }
    .fi-grade { display: grid; grid-template-columns: 1fr 1fr; gap: 3px 16px; }
    .fi-grade.fi-3 { grid-template-columns: 1fr 1fr 1fr; }
    .fi-campo { display: flex; gap: 4px; }
    .fi-rotulo { color: #555; white-space: nowrap; }
    .fi-rotulo::after { content: ':'; }
    .fi-valor { font-weight: 600; word-break: break-word; }
    .fi-vazio { color: #888; font-weight: 400; font-style: italic; }
    .fi-atencao { display: inline-block; padding: 2px 8px; border: 1px solid #333; border-radius: 10px; font-weight: 700; font-size: 11px; }
    .fi-tabela { width: 100%; border-collapse: collapse; }
    .fi-tabela th, .fi-tabela td { border: 1px solid #bbb; padding: 3px 6px; text-align: left; vertical-align: top; }
    .fi-tabela th { background: #eef6f1; }
    .fi-assinatura { margin-top: 36px; display: flex; justify-content: space-around; gap: 24px; text-align: center; }
    .fi-assinatura div { border-top: 1px solid #333; padding-top: 4px; width: 40%; }
    .fi-rodape { margin-top: 16px; font-size: 10px; color: #666; text-align: center; }

    @media (max-width: 600px) {
        .fi-pagina { margin: 8px; padding: 16px; }
        .fi-grade, .fi-grade.fi-3 { grid-template-columns: 1fr; }
        .fi-ident { flex-direction: column; }
    }

    @media print {
        @page { size: A4; margin: 12mm; }
        body { background: #fff !important; }
        body > *:not(.fi-impressao) { display: none !important; }
        .fi-acoes { display: none !important; }
        .fi-pagina { box-shadow: none; margin: 0; padding: 0; max-width: none; }
        .fi-tabela th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<div class="fi-impressao">

    <div class="fi-acoes">
        <a href="<?= URL ?>/alunos/visualizarPerfil/<?= $id ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Voltar ao perfil
        </a>
        <button type="button" class="btn btn-success fw-bold" onclick="window.print()">
            <i class="bi bi-printer-fill me-1" aria-hidden="true"></i> Imprimir
        </button>
    </div>

    <main class="fi-pagina" id="conteudo-principal">

        <header class="fi-topo">
            <div>
                <h1>Ficha do Estudante</h1>
                <small><?= htmlspecialchars(APP_NOME) ?> — Setor de Saúde</small>
            </div>
            <img src="<?= URL ?>/img/life-menor.png" alt="Logo do LIFE">
        </header>

        <!-- IDENTIFICAÇÃO -->
        <div class="fi-ident">
            <img src="<?= htmlspecialchars($fotoAluno) ?>" class="fi-foto" alt="Foto do aluno">
            <div class="flex-grow-1">
                <p class="fi-nome"><?= $fv($aluno->nome) ?></p>
                <div class="fi-grade">
                    <?= $linha('Matrícula', $fv($aluno->matricula)) ?>
                    <?= $linha('Data de nascimento', $fv($dataBr($aluno->data_nascimento))) ?>
                    <?= $linha('Idade', $fv($dados['idade'] !== null ? $dados['idade'] . ' anos' : '')) ?>
                    <?= $linha('Sexo', $fv($aluno->sexo)) ?>
                    <?= $linha('RG', $fv($aluno->rg)) ?>
                    <?= $linha('Telefone', $fvTel($dados['telefone_aluno'])) ?>
                    <?= $linha('Curso', $fv($aluno->curso)) ?>
                    <?= $linha('Turma / Turno', $fv(trim(($aluno->ano_turma ?? '') . ' — ' . ($aluno->turno ?? ''), ' —'))) ?>
                </div>
                <p class="mt-2 mb-0">
                    Nível de atenção: <span class="fi-atencao"><?= htmlspecialchars($atencao['rotulo']) ?></span>
                </p>
            </div>
        </div>

        <!-- RESPONSÁVEL -->
        <section class="fi-secao">
            <h2>Responsável</h2>
            <div class="fi-grade">
                <?= $linha('Nome', $fv($aluno->responsavel)) ?>
                <?= $linha('Telefone', $fvTel($dados['telefone_responsavel'])) ?>
            </div>
        </section>

        <!-- MEDIDAS -->
        <section class="fi-secao">
            <h2>Dados físicos</h2>
            <div class="fi-grade fi-3">
                <?= $linha('Tipo sanguíneo', $fv($aluno->tipo_sanguineo)) ?>
                <?= $linha('Altura', $fv($alturaTexto)) ?>
                <?= $linha('Peso', $fv($pesoTexto)) ?>
                <?= $linha('IMC', $fv($imcTexto)) ?>
            </div>
        </section>

        <!-- SAÚDE -->
        <section class="fi-secao">
            <h2>Saúde</h2>
            <div class="fi-grade">
                <?= $linha('Condições especiais', $condicoes ? $fv(implode(', ', $condicoes)) : $fv('Nenhuma informada')) ?>
                <?= $linha('Alergia alimentar', $fvSim($s('alergia_alimentar'), $s('alergia_qual'))) ?>
                <?= $linha('Doença crônica', $fvSim($s('doenca_cronica'), $s('doenca_qual'))) ?>
                <?= $linha('Medicação contínua', $fvSim($s('medicacao_continua'), $s('medicacao_qual'))) ?>
                <?= $linha('Acompanhamento psicológico', $fv($s('acompanhamento_psi'))) ?>
                <?= $linha('Cartão de vacina', $fv($s('cartao_vacina'))) ?>
                <?= $linha('Acesso à saúde', $fv($s('acesso_saude'))) ?>
                <?= $linha('Já foi internado', $fvSim($s('ja_internado'), $s('internado_motivo'))) ?>
                <?= $linha('Já fez cirurgia', $fvSim($s('ja_cirurgia'), $s('cirurgia_qual'))) ?>
            </div>
        </section>

        <!-- HÁBITOS -->
        <section class="fi-secao">
            <h2>Hábitos</h2>
            <div class="fi-grade">
                <?= $linha('Padrão de sono', $fv($s('padrao_sono'))) ?>
                <?= $linha('Refeições por dia', $fv($s('refeicoes_dia'))) ?>
                <?= $linha('Alimentação habitual', $fv($s('alimentacao_habitual'))) ?>
                <?= $linha('Atividade física', $fv($s('atividade_fisica'))) ?>
                <?= $linha('Frequência da atividade', $fv($s('frequencia_atividade'))) ?>
                <?= $linha('Banhos por dia', $fv($s('banhos_dia'))) ?>
                <?= $linha('Fuma', $fv($s('fuma'))) ?>
                <?= $linha('Bebida alcoólica', $fv($s('bebida_alcoolica'))) ?>
                <?= $linha('Drogas ilícitas', $fvSim($s('drogas_ilicitas'), $s('drogas_qual'))) ?>
            </div>
        </section>

        <!-- ESCOLAR -->
        <section class="fi-secao">
            <h2>Vida escolar</h2>
            <div class="fi-grade">
                <?= $linha('Tipo de escola anterior', $fv($s('tipo_escola'))) ?>
                <?= $linha('Já reprovou', $fvSim($s('ja_reprovou'), $s('quantas_vezes'))) ?>
                <?= $linha('Estuda longe de casa', $fvSim($s('estuda_fora'), $s('quanto_tempo'))) ?>
                <?= $linha('Nota para o curso', $fv($s('nota_curso'))) ?>
                <?= $linha('Nota de motivação', $fv($s('nota_motivacao'))) ?>
            </div>
        </section>

        <!-- HISTÓRICO -->
        <section class="fi-secao">
            <h2>Últimos atendimentos</h2>
            <?php if (!empty($historico)): ?>
                <table class="fi-tabela">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Profissional</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $visita): ?>
                            <tr>
                                <td><?= $fv($dataBr($visita->cons_data) . (!empty($visita->cons_horario) ? ' ' . substr($visita->cons_horario, 0, 5) : ''), '--') ?></td>
                                <td><?= $fv($visita->cons_tipo, '--') ?></td>
                                <td><?= $fv(trim(($visita->serv_nome ?? '') . (!empty($visita->func_nome) ? ' (' . $visita->func_nome . ')' : '')), '--') ?></td>
                                <td><?= $fv($visita->cons_descricao, '--') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="fi-vazio mb-0">Nenhum atendimento registrado.</p>
            <?php endif; ?>
        </section>

        <div class="fi-assinatura">
            <div>Profissional responsável</div>
            <div>Responsável pelo aluno</div>
        </div>

        <p class="fi-rodape">
            Cadastrado em <?= $fv($dataBr($aluno->data_cadastro), '--') ?>
            <?php if (!empty($aluno->atualizado_em)): ?> • Atualizado em <?= $fv($dataBr($aluno->atualizado_em, true)) ?><?php endif; ?>
            • Emitido em <?= $emitidoEm ?><?= $emitidoPor !== '' ? ' por ' . htmlspecialchars($emitidoPor) : '' ?>
            <br>Documento com dados de saúde: uso restrito e confidencial.
        </p>
    </main>
</div>

<script>
    // Abre a janela de impressão automaticamente ao carregar.
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
</script>
