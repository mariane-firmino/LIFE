<?php

class Paginas extends Controller
{
    // =========================================================
    // CÓDIGO ANTIGO
    // =========================================================
    //
    // Este trecho está comentado e atualmente não é utilizado.
    // Provavelmente era usado para carregar o Model de usuário.
    //
    // Se não for mais necessário, futuramente pode ser removido.
    // =========================================================

    /*
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = $this->model('Usuario');
    }
    */


    // =========================================================
    // PÁGINA INICIAL
    // =========================================================
    //
    // Quando alguém acessa o endereço principal do sistema,
    // é redirecionado para a página de login.
    //
    // Exemplo:
    // /ENFERMARIA
    //
    // será direcionado para:
    // /ENFERMARIA/usuarios/login
    // =========================================================

    public function index()
    {
        URL::redirecionar('usuarios/login');
    }


    // =========================================================
    // HOME
    // =========================================================
    //
    // Abre a página principal do sistema depois que o usuário
    // acessa a área correspondente.
    // =========================================================

    public function home()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        // Administrador tem a sua própria home (com o menu do admin).
        if (($_SESSION['usuario_perfil'] ?? '') === 'admin') {
            URL::redirecionar('admin/home');
            exit;
        }

        $home = $this->model('Home');
        $fusoHorario = new DateTimeZone('America/Porto_Velho');
        $agora = new DateTimeImmutable('now', $fusoHorario);
        $inicioMes = $agora->modify('first day of this month')->setTime(0, 0);
        $inicioProximoMes = $inicioMes->modify('first day of next month');

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Página de home',
            'descricao' => 'pagina home',
            'notificacoes' => $home->contarNotificacoesNaoLidas((int) $_SESSION['usuario_id']),
            'alunos' => $home->contarAlunos(),
            'atendimentos_hoje' => $home->contarAtendimentosHoje($agora->format('Y-m-d')),
            'atendimentos_periodicos' => $home->contarAtendimentosPeriodicos(
                $inicioMes->format('Y-m-d'),
                $inicioProximoMes->format('Y-m-d')
            ),
            // Alunos com alerta na triagem mais recente (últimos 30 dias)
            'alunos_atencao' => $home->listarAlunosEmAtencao(
                $agora->modify('-30 days')->format('Y-m-d H:i:s')
            ),
        ];

        // Abre: App/Views/paginas/home.php
        $this->view('paginas/home', $dados);
    }


    // =========================================================
    // PERFIL
    // =========================================================
    //
    // Abre a página de perfil do usuário.
    // =========================================================

    public function perfil()
    {
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de perfil',
            'descricao' => 'pagina perfil'
        ];

        // Abre: App/Views/paginas/perfil.php
        $this->view('paginas/perfil', $dados);
    }


    // =========================================================
    // SOBRE NÓS
    // =========================================================
    //
    // Abre a página com informações sobre o sistema/projeto.
    // =========================================================

    public function sobre()
    {
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página sobre nós',
            'descricao' => 'pagina sobre nós'
        ];

        // Abre: App/Views/paginas/sobre.php
        $this->view('paginas/sobre', $dados);
    }


    // =========================================================
    // CONFIGURAÇÕES
    // =========================================================
    //
    // Abre a página de configurações.
    // =========================================================

    public function configuracao()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        if (empty($_SESSION['csrf_token_notificacoes'])) {
            $_SESSION['csrf_token_notificacoes'] = bin2hex(random_bytes(32));
        }

        $notificacoes = $this->model('Notificacao');
        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de configuração',
            'descricao' => 'pagina configuração',
            'preferencias_email' => $notificacoes->buscarPreferenciasEmail((int) $_SESSION['usuario_id']),
            'csrf_token' => $_SESSION['csrf_token_notificacoes'],
            'is_admin' => ($_SESSION['usuario_perfil'] ?? '') === 'admin',
        ];

        // Abre: App/Views/paginas/configuracao.php
        $this->view('paginas/configuracao', $dados);
    }

    public function salvarPreferenciasNotificacao()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || empty($_SESSION['csrf_token_notificacoes'])
            || !hash_equals($_SESSION['csrf_token_notificacoes'], $token)
        ) {
            http_response_code(403);
            exit('Requisição inválida.');
        }

        $salvou = $this->model('Notificacao')->salvarPreferenciasEmail(
            (int) $_SESSION['usuario_id'],
            isset($_POST['email_consultas']),
            isset($_POST['email_triagens']),
            isset($_POST['email_cadastros'])
        );

        $_SESSION['configuracao_mensagem'] = $salvou
            ? 'Preferências de e-mail salvas.'
            : 'Não foi possível salvar as preferências.';
        $_SESSION['configuracao_tipo_mensagem'] = $salvou ? 'success' : 'danger';

        URL::redirecionar('paginas/configuracao');
        exit;
    }


    // =========================================================
    // RELATÓRIO
    // =========================================================
    //
    // Abre a página de relatórios.
    // =========================================================

    public function relatorio()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $relatorio = $this->model('Relatorio');

        $fusoHorario = new DateTimeZone('America/Porto_Velho');
        $agora = new DateTimeImmutable('now', $fusoHorario);

        // ---- Filtros (validados contra as opções reais) ----
        $periodos = Relatorio::opcoesPeriodo($agora);
        $periodo = (string) ($_GET['periodo'] ?? $agora->format('Y-m'));
        if (!isset($periodos[$periodo])) {
            $periodo = $agora->format('Y-m');
        }

        $tipo = (string) ($_GET['tipo'] ?? 'todos');
        if (!isset(Relatorio::TIPOS[$tipo])) {
            $tipo = 'todos';
        }

        $turmas = $relatorio->listarTurmas();
        $turmaId = (int) ($_GET['turma'] ?? 0);
        $turmasValidas = array_map(static fn($t) => (int) $t->turm_id, $turmas);
        if (!in_array($turmaId, $turmasValidas, true)) {
            $turmaId = 0;
        }
        $turmaFiltro = $turmaId > 0 ? $turmaId : null;

        [$inicio, $fim] = Relatorio::intervalo($periodo);

        // ---- Números dos cards ----
        $totalConsultas = $relatorio->totalConsultas($inicio, $fim);
        $consultasMarcadas = $relatorio->consultasMarcadas($inicio, $fim, $agora->format('Y-m-d H:i:s'));
        $totalEstudantes = $relatorio->totalEstudantesAtendidos($inicio, $fim, $turmaFiltro);
        $totalTriagens = $relatorio->totalTriagens($inicio, $fim, $turmaFiltro);

        // ---- Detalhes ----
        // Tipo específico: carrega só a lista escolhida.
        // Tipo "todos": carrega as três (a tela só mostra o resumo, mas a
        // versão impressa traz a relação completa de cada relatório).
        $agoraTexto = $agora->format('Y-m-d H:i:s');
        $tiposParaCarregar = $tipo === 'todos'
            ? ['consultas', 'estudantes', 'triagens']
            : [$tipo];

        $listas = [];
        foreach ($tiposParaCarregar as $t) {
            if ($t === 'consultas') {
                $listas[$t] = $relatorio->listarConsultas($inicio, $fim, $agoraTexto);
            } elseif ($t === 'estudantes') {
                $listas[$t] = $relatorio->listarEstudantesAtendidos($inicio, $fim, $turmaFiltro);
            } elseif ($t === 'triagens') {
                $listas[$t] = $relatorio->listarTriagens($inicio, $fim, $turmaFiltro);
            }
        }
        $detalhes = $listas[$tipo] ?? [];

        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de relatório',
            'descricao' => 'pagina relatorio',
            'periodos' => $periodos,
            'periodo' => $periodo,
            'tipos' => Relatorio::TIPOS,
            'tipo' => $tipo,
            'turmas' => $turmas,
            'turma' => $turmaId,
            'total_consultas' => $totalConsultas,
            'consultas_marcadas' => $consultasMarcadas,
            'total_estudantes' => $totalEstudantes,
            'total_triagens' => $totalTriagens,
            'detalhes' => $detalhes,
            'listas' => $listas,
            'limite_detalhes' => Relatorio::LIMITE_DETALHES,
            'gerado_em' => $agora->format('d/m/Y H:i'),
            // 1 = abre só o documento de impressão (página aberta pelo botão Imprimir)
            'imprimir' => (($_GET['imprimir'] ?? '') === '1'),
        ];

        // Abre: App/Views/paginas/relatorio.php
        $this->view('paginas/relatorio', $dados);
    }


    // =========================================================
    // GERENCIAR DISPOSITIVOS
    // =========================================================
    //
    // Abre a página responsável pelo gerenciamento dos
    // dispositivos.
    // =========================================================

    public function gerenciarDispositivos()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        // Token contra envio de formulário por outro site (CSRF).
        if (empty($_SESSION['csrf_dispositivos'])) {
            $_SESSION['csrf_dispositivos'] = bin2hex(random_bytes(32));
        }

        $dispositivos = ControleSessao::modelo()->listarAtivos(
            $_SESSION['usuario_id'],
            $_SESSION['sessao_token'] ?? ''
        );

        // Dados enviados para a View.
        $dados = [
            'titulo' => 'Página de gerenciamento de dispositivos',
            'descricao' => 'pagina gerenciamento de dispositivos',
            'dispositivos' => $dispositivos,
            'csrf' => $_SESSION['csrf_dispositivos'],
        ];

        // Abre: App/Views/paginas/gerenciarDispositivos.php
        $this->view('paginas/gerenciarDispositivos', $dados);
    }


    // =========================================================
    // ENCERRAR UM DISPOSITIVO (POST)
    // =========================================================

    public function encerrarDispositivo($id = null)
    {
        if (!$this->podeAlterarDispositivos()) {
            return;
        }

        $modelo = ControleSessao::modelo();

        // Encerrar o próprio dispositivo = sair do sistema.
        foreach ($modelo->listarAtivos($_SESSION['usuario_id'], $_SESSION['sessao_token'] ?? '') as $d) {
            if ((int) $d->sess_id === (int) $id && $d->atual) {
                URL::redirecionar('usuarios/logout');
                exit;
            }
        }

        if ($modelo->encerrar((int) $id, $_SESSION['usuario_id'])) {
            Sessao::mensagem('dispositivos', 'Sessão encerrada no dispositivo selecionado.', 'alert alert-success');
        } else {
            Sessao::mensagem('dispositivos', 'Dispositivo não encontrado.', 'alert alert-danger');
        }

        URL::redirecionar('paginas/gerenciarDispositivos');
        exit;
    }


    // =========================================================
    // ENCERRAR TODAS AS OUTRAS SESSÕES (POST)
    // =========================================================

    public function encerrarOutrosDispositivos()
    {
        if (!$this->podeAlterarDispositivos()) {
            return;
        }

        $total = ControleSessao::modelo()->encerrarOutras(
            $_SESSION['usuario_id'],
            $_SESSION['sessao_token'] ?? ''
        );

        if ($total > 0) {
            $texto = $total === 1
                ? '1 sessão em outro dispositivo foi encerrada.'
                : $total . ' sessões em outros dispositivos foram encerradas.';
            Sessao::mensagem('dispositivos', $texto, 'alert alert-success');
        } else {
            Sessao::mensagem('dispositivos', 'Não há outros dispositivos conectados.', 'alert alert-info');
        }

        URL::redirecionar('paginas/gerenciarDispositivos');
        exit;
    }


    // Confere login, método POST e token CSRF.
    private function podeAlterarDispositivos()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $token = $_POST['csrf'] ?? '';
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || empty($_SESSION['csrf_dispositivos'])
            || !hash_equals($_SESSION['csrf_dispositivos'], $token)
        ) {
            Sessao::mensagem('dispositivos', 'Requisição inválida. Tente novamente.', 'alert alert-danger');
            URL::redirecionar('paginas/gerenciarDispositivos');
            exit;
        }

        return true;
    }
}
