<?php

require_once dirname(__DIR__) . '/Libraries/NotificationService.php';

class Triagens extends Controller
{
    // =========================================================
    // MODELS
    // alunoModel   -> busca os dados do aluno.
    // triagemModel -> salva as respostas da triagem no banco.
    // =========================================================

    private $alunoModel;
    private $triagemModel;
    private $triagemRapidaModel;
    private $opcaoModel;
    private $notificationService;

    // Limite de cada caixa de texto da triagem rápida.
    private const MAX_TEXTO_RAPIDA = 2000;


    // =========================================================
    // CONSTRUTOR
    // Carrega os Models "Aluno" e "Triagem" para que o
    // Controller possa acessar o banco de dados.
    // =========================================================

    public function __construct()
    {
        $this->alunoModel = $this->model('Aluno');
        $this->triagemModel = $this->model('Triagem');
        $this->triagemRapidaModel = $this->model('TriagemRapida');
        $this->opcaoModel = $this->model('Opcao');
        $this->notificationService = new NotificationService($this->model('Notificacao'));
    }


    // =========================================================
    // PÁGINA INICIAL DE TRIAGENS
    // Evita erro quando alguém acessa /triagens sem informar
    // a página. Redireciona para a consulta de alunos, onde
    // a triagem deve ser iniciada.
    // =========================================================

    public function index()
    {
        URL::redirecionar('alunos/consulta');
        exit;
    }


    // =========================================================
    // ABRIR TRIAGEM PSICOLÓGICA / FÍSICA
    // A triagem é dividida em duas páginas. As perguntas mudam
    // conforme o sexo do aluno (masculina ou feminina).
    // Ex.: triagens/psicologica/5   e   triagens/fisica/5
    // =========================================================

    public function psicologica($alunoId = null)
    {
        $this->exibirFormulario('psicologica', $alunoId);
    }

    public function fisica($alunoId = null)
    {
        $this->exibirFormulario('fisica', $alunoId);
    }


    // =========================================================
    // ROTAS ANTIGAS
    // Antes a triagem era uma página só, por sexo. Links antigos
    // agora abrem a primeira parte (psicológica).
    //
    // ATENÇÃO:
    // Não remova sem verificar se alguma parte do sistema
    // (links, botões, menus) ainda usa estas rotas.
    // =========================================================

    public function triagemMasculina($alunoId = null)
    {
        $this->psicologica($alunoId);
    }

    public function triagemFeminina($alunoId = null)
    {
        $this->psicologica($alunoId);
    }

    public function triagemM3sculina($alunoId = null)
    {
        $this->psicologica($alunoId);
    }

    public function triagemFe3minina($alunoId = null)
    {
        $this->psicologica($alunoId);
    }

    // =========================================================
    // TRIAGEM RÁPIDA
    // Atendimento do dia a dia (ex.: aluno com dor de cabeça).
    // GET  triagens/rapida/5  -> abre o formulário
    // POST triagens/rapida/5  -> valida e salva
    // =========================================================

    public function rapida($alunoId = null)
    {
        $aluno = $this->alunoOuRedireciona($alunoId);
        $queixas = $this->opcaoModel->valoresAtivos('queixa');

        // Valores do formulário (vazios ao abrir; preenchidos se voltar com erro).
        $form = [
            'queixa' => '',
            'gravidade' => '',
            'relato_aluno' => '',
            'observacao' => '',
            'conduta' => '',
        ];
        $erros = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($form as $campo => $vazio) {
                $valor = $_POST[$campo] ?? '';
                $form[$campo] = is_string($valor) ? trim($valor) : '';
            }

            // Queixa: precisa ser uma das opções ativas do menu.
            if (!in_array($form['queixa'], $queixas, true)) {
                $erros['queixa'] = 'Escolha a queixa principal.';
            }

            // Gravidade: Leve, Média ou Grave.
            if (!in_array($form['gravidade'], TriagemRapida::GRAVIDADES, true)) {
                $erros['gravidade'] = 'Escolha a gravidade.';
            }

            // Como lidou: obrigatório.
            if ($form['conduta'] === '') {
                $erros['conduta'] = 'Descreva como o caso foi conduzido.';
            }

            // Limite das caixas de texto.
            foreach (['relato_aluno', 'observacao', 'conduta'] as $campo) {
                if (mb_strlen($form[$campo]) > self::MAX_TEXTO_RAPIDA) {
                    $erros[$campo] = 'Use no máximo ' . self::MAX_TEXTO_RAPIDA . ' caracteres.';
                }
            }

            if (!$erros) {
                $salvou = $this->triagemRapidaModel->salvar($form + [
                    'aluno_id' => (int) $aluno->id,
                    'serv_id' => (int) $_SESSION['usuario_id'],
                ]);

                if ($salvou) {
                    $this->notificationService->avisarUsuariosAtivos(
                        'triagem',
                        'Nova triagem rápida registrada',
                        'Uma triagem rápida foi registrada. Acesse o sistema para consultar as informações autorizadas.',
                        'alunos/consulta',
                        (int) $_SESSION['usuario_id']
                    );

                    // Volta ao perfil já na aba das triagens rápidas.
                    Sessao::mensagem('alunos', 'Triagem rápida salva com sucesso');
                    URL::redirecionar('alunos/visualizarPerfil/' . (int) $aluno->id . '#aba-rapidas');
                    exit;
                }

                Sessao::mensagem('triagens', 'Não foi possível salvar a triagem. Tente novamente.', 'alert alert-danger');
            }
        }

        $dados = [
            'titulo' => 'Triagem rápida',
            'descricao' => 'Formulário de triagem rápida',
            'aluno_id' => (int) $aluno->id,
            'aluno_nome' => $aluno->nome,
            'queixas' => $queixas,
            'gravidades' => TriagemRapida::GRAVIDADES,
            'max_texto' => self::MAX_TEXTO_RAPIDA,
            'form' => $form,
            'erros' => $erros,
        ];

        $this->view('triagens/triagem_rapida', $dados);
    }


    // =========================================================
    // SALVAR TRIAGEM PSICOLÓGICA / FÍSICA
    // Recebem o formulário enviado por POST.
    // =========================================================

    public function salvarPsicologica($alunoId = null)
    {
        $this->salvar('psicologica', $alunoId);
    }

    public function salvarFisica($alunoId = null)
    {
        $this->salvar('fisica', $alunoId);
    }

    // =========================================================
    // GARANTIR LOGIN E ALUNO VÁLIDO
    // Verifica se o usuário está logado e se o ID do aluno é
    // válido e existe no banco. Se algo estiver errado,
    // redireciona. Caso contrário, devolve o aluno.
    // =========================================================

    private function alunoOuRedireciona($alunoId)
    {
        // Só usuários logados podem fazer triagem.
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('login');
            exit;
        }

        // O ID precisa ser informado e ser numérico.
        if (empty($alunoId) || !ctype_digit((string) $alunoId)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Busca o aluno pelo ID.
        $aluno = $this->alunoModel->buscarPorId((int) $alunoId);

        // Se o aluno não existir, volta para a consulta.
        if (!$aluno) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        return $aluno;
    }


    // =========================================================
    // EXIBIR FORMULÁRIO
    // Abre a View da triagem ($parte = 'psicologica' ou 'fisica'),
    // com as perguntas certas para o sexo do aluno.
    // =========================================================

    private function exibirFormulario($parte, $alunoId)
    {
        $aluno = $this->alunoOuRedireciona($alunoId);
        $tipo = $this->tipoPorSexo($aluno->sexo);

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => TriagemPerguntas::PARTES[$parte],
            'descricao' => 'pagina triagens',
            'aluno_id' => (int) $aluno->id,
            'aluno_nome' => $aluno->nome,
            'aluno_sexo' => $aluno->sexo,
            'tipo' => $tipo,
            'parte' => $parte,
            'perguntas' => TriagemPerguntas::parte($tipo, $parte),
        ];

        $this->view('triagens/adicionar_triagem', $dados);
    }

    // =========================================================
    // SALVAR TRIAGEM
    // Valida as respostas do formulário e as salva no banco,
    // associadas ao aluno e ao usuário que fez a triagem.
    // =========================================================

    private function salvar($parte, $alunoId)
    {
        $aluno = $this->alunoOuRedireciona($alunoId);
        $tipo = $this->tipoPorSexo($aluno->sexo);
        $id = (int) $aluno->id;

        // Página para onde o usuário volta em caso de erro.
        $voltar = 'triagens/' . $parte . '/' . $id;

        // Só aceita envio via POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            URL::redirecionar($voltar);
            exit;
        }

        $respostas = [];
        $temErro = false;

        // -----------------------------------------------------
        // VALIDA CADA PERGUNTA
        // As perguntas de cada parte (psicológica / física)
        // vêm da classe TriagemPerguntas.
        // -----------------------------------------------------

        foreach (TriagemPerguntas::parte($tipo, $parte) as $numero => $pergunta) {

            $valor = $_POST['pergunta' . $numero] ?? '';
            $valor = is_string($valor) ? trim($valor) : '';

            // Resposta vazia: só é erro se a pergunta for obrigatória.
            if ($valor === '') {
                if ($pergunta['obrigatoria']) {
                    $temErro = true;
                }
                continue;
            }

            // Perguntas de seleção: só aceita as opções que
            // existem no formulário.
            if ($pergunta['tipo'] === 'select' && !in_array($valor, $pergunta['opcoes'], true)) {
                $temErro = true;
                continue;
            }

            // Perguntas de texto: limite de 255 caracteres.
            if ($pergunta['tipo'] === 'texto' && mb_strlen($valor) > 255) {
                $temErro = true;
                continue;
            }

            // Resposta válida: guarda a pergunta e a resposta.
            $respostas[] = [
                'pergunta' => $pergunta['pergunta'],
                'resposta' => $valor
            ];
        }

        // -----------------------------------------------------
        // SE HOUVER ERRO, VOLTA PARA O FORMULÁRIO
        // -----------------------------------------------------

        if ($temErro) {
            Sessao::mensagem(
                'triagens',
                'Preencha corretamente todas as perguntas obrigatórias (marcadas com *).',
                'alert alert-danger'
            );
            URL::redirecionar($voltar);
            exit;
        }

        // -----------------------------------------------------
        // SALVA NO BANCO DE DADOS
        // -----------------------------------------------------

        if ($this->triagemModel->salvar($id, (int) $_SESSION['usuario_id'], $respostas)) {

            $this->notificationService->avisarUsuariosAtivos(
                'triagem',
                'Nova triagem registrada',
                'Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',
                'alunos/consulta',
                (int) $_SESSION['usuario_id']
            );

            // Sucesso: vai para o perfil do aluno.
            Sessao::mensagem('alunos', TriagemPerguntas::PARTES[$parte] . ' salva com sucesso');
            URL::redirecionar('alunos/visualizarPerfil/' . $id . '#aba-triagens');

        } else {

            // Falha ao salvar: volta para o formulário.
            Sessao::mensagem(
                'triagens',
                'Não foi possível salvar a triagem. Tente novamente.',
                'alert alert-danger'
            );
            URL::redirecionar($voltar);
        }

        exit;
    }

    private function tipoPorSexo($sexo)
    {
        return $sexo === 'Masculino' ? 'masculina' : 'feminina';
    }
}