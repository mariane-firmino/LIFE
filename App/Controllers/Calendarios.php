<?php

require_once dirname(__DIR__) . '/Libraries/NotificationService.php';

class Calendarios extends Controller
{
    private $consulta;
    private $notificationService;

    public function __construct()
    {
        // Só usuários logados acessam o calendário.
        if (!isset($_SESSION['usuario_id'])) {
            if ($this->querJson()) {
                $this->json(['ok' => false, 'erro' => 'Sessão expirada.'], 401);
            }
            header('Location: ' . URL . '/login');
            exit;
        }

        $this->consulta = $this->model('Consulta');
        $this->notificationService = new NotificationService($this->model('Notificacao'));
    }

    // ---------------------------------------------------------
    // Página principal
    // ---------------------------------------------------------
    public function calendario()
    {
        $this->view('calendarios/calendario', [
            'titulo'       => 'Calendário',
            'descricao'    => 'Página de calendário',
            'tipos'        => Consulta::TIPOS,
            'responsaveis' => Consulta::RESPONSAVEIS,
        ]);
    }

    // ---------------------------------------------------------
    // JSON dos eventos (FullCalendar)
    // ---------------------------------------------------------
    public function listarConsultas()
    {
        $eventos = [];

        foreach ($this->consulta->listarConsultas() as $c) {
            $eventos[] = [
                'id'    => (int) $c->cons_id,
                'title' => $c->cons_tipo,
                'start' => $c->cons_data . 'T' . substr($c->cons_horario, 0, 5),
                'color' => Consulta::TIPOS[trim($c->cons_tipo)] ?? '#6c757d',
                'extendedProps' => [
                    'tipo'        => $c->cons_tipo,
                    'responsavel' => $c->cons_responsavel ?? '',
                    'descricao'   => $c->cons_descricao ?? '',
                    'observacao'  => $c->cons_observacao ?? '',
                ],
            ];
        }

        $this->json($eventos);
    }

    // ---------------------------------------------------------
    // Cadastrar (POST)
    // ---------------------------------------------------------
    public function cadastrarConsulta()
    {
        $this->exigirPost();
        $dados = $this->lerFormulario();

        if (!$dados) {
            $this->responder(false, 'Preencha data, hora, tipo e descrição corretamente.');
        }

        $ok = $this->consulta->cadastrar($dados);
        if ($ok) {
            $this->notificationService->avisarUsuariosAtivos(
                'consulta',
                'Nova consulta na agenda',
                'Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',
                'calendarios/calendario',
                (int) $_SESSION['usuario_id']
            );
        }
        $this->responder($ok, $ok ? 'Consulta cadastrada com sucesso!' : 'Erro ao cadastrar a consulta.');
    }

    // ---------------------------------------------------------
    // Editar (POST)
    // ---------------------------------------------------------
    public function editarConsulta()
    {
        $this->exigirPost();

        $id    = (int) ($_POST['cons_id'] ?? 0);
        $dados = $this->lerFormulario();

        $consultaAnterior = $id > 0 ? $this->consulta->buscarPorId($id) : false;
        if ($id <= 0 || !$dados || !$consultaAnterior) {
            $this->responder(false, 'Consulta inválida.');
        }

        $dados['cons_id'] = $id;
        $ok = $this->consulta->atualizar($dados);
        if ($ok) {
            $this->notificationService->avisarUsuariosAtivos(
                'consulta',
                'Consulta atualizada na agenda',
                'Uma consulta da agenda foi atualizada. Acesse o calendário para ver os detalhes.',
                'calendarios/calendario',
                (int) $_SESSION['usuario_id']
            );
        }
        $this->responder($ok, $ok ? 'Consulta atualizada!' : 'Erro ao atualizar a consulta.');
    }

    // ---------------------------------------------------------
    // Reagendar ao arrastar no calendário (POST, JSON)
    // ---------------------------------------------------------
    public function reagendarConsulta()
    {
        $this->exigirPost();

        $id      = (int) ($_POST['cons_id'] ?? 0);
        $data    = $_POST['data'] ?? '';
        $horario = $_POST['horario'] ?? '';

        if ($id <= 0 || !$this->dataValida($data) || !$this->horaValida($horario)) {
            $this->json(['ok' => false, 'erro' => 'Dados inválidos.'], 422);
        }

        $consultaAnterior = $this->consulta->buscarPorId($id);
        if (!$consultaAnterior) {
            $this->json(['ok' => false, 'erro' => 'Consulta inválida.'], 404);
        }

        $ok = $this->consulta->reagendar($id, $data, $horario);
        if ($ok) {
            $this->notificationService->avisarUsuariosAtivos(
                'consulta',
                'Consulta reagendada',
                'Uma consulta da agenda foi reagendada. Acesse o calendário para ver os detalhes.',
                'calendarios/calendario',
                (int) $_SESSION['usuario_id']
            );
        }
        $this->json(['ok' => $ok], $ok ? 200 : 500);
    }

    // ---------------------------------------------------------
    // Excluir (POST, JSON)
    // ---------------------------------------------------------
    public function excluirConsulta()
    {
        $this->exigirPost();

        $id = (int) ($_POST['cons_id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'erro' => 'Consulta inválida.'], 422);
        }

        $consultaAnterior = $this->consulta->buscarPorId($id);
        if (!$consultaAnterior) {
            $this->json(['ok' => false, 'erro' => 'Consulta inválida.'], 404);
        }

        $ok = $this->consulta->excluir($id);
        if ($ok) {
            $this->notificationService->avisarUsuariosAtivos(
                'consulta',
                'Consulta removida da agenda',
                'Uma consulta foi removida da agenda.',
                'calendarios/calendario',
                (int) $_SESSION['usuario_id']
            );
        }
        $this->json(['ok' => $ok], $ok ? 200 : 500);
    }

    // =========================================================
    // AUXILIARES
    // =========================================================

    /** Valida e organiza os campos do formulário. Retorna null se inválido. */
    private function lerFormulario(): ?array
    {
        $data        = trim($_POST['dataConsulta'] ?? '');
        $hora        = trim($_POST['horaConsulta'] ?? '');
        $tipo        = trim($_POST['tipoConsulta'] ?? '');
        $responsavel = trim($_POST['responsavelConsulta'] ?? '');
        $descricao   = trim($_POST['descricaoConsulta'] ?? '');
        $observacao  = trim($_POST['observacaoConsulta'] ?? '');

        if (
            !$this->dataValida($data) || !$this->horaValida($hora) ||
            !array_key_exists($tipo, Consulta::TIPOS) || $descricao === ''
        ) {
            return null;
        }

        if ($responsavel !== '' && !in_array($responsavel, Consulta::RESPONSAVEIS, true)) {
            return null;
        }

        // Os dados são gravados "crus"; o escape é feito na exibição.
        return [
            'cons_data'        => $data,
            'cons_horario'     => $hora,
            'cons_tipo'        => $tipo,
            'cons_responsavel' => $responsavel !== '' ? $responsavel : null,
            'cons_descricao'   => mb_substr($descricao, 0, 500),
            'cons_observacao'  => $observacao !== '' ? mb_substr($observacao, 0, 2000) : null,
        ];
    }

    private function dataValida(string $d): bool
    {
        $o = DateTime::createFromFormat('Y-m-d', $d);
        return $o && $o->format('Y-m-d') === $d;
    }

    private function horaValida(string $h): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $h);
    }

    private function exigirPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['ok' => false, 'erro' => 'Método não permitido.'], 405);
        }
    }

    private function querJson(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /** Responde JSON (fetch) ou redireciona com mensagem (formulário comum). */
    private function responder(bool $ok, string $mensagem): void
    {
        if ($this->querJson()) {
            $this->json(['ok' => $ok, 'mensagem' => $mensagem], $ok ? 200 : 422);
        }

        $_SESSION['mensagem'] = $mensagem;
        $_SESSION['tipo_msg'] = $ok ? 'success' : 'danger';
        header('Location: ' . URL . '/calendarios/calendario');
        exit;
    }

    private function json($dados, int $status = 200): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
