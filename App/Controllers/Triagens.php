<?php

class Triagens extends Controller
{
    // =========================================================
    // MODELS
    // alunoModel   -> busca os dados do aluno.
    // triagemModel -> salva as respostas da triagem no banco.
    // =========================================================

    private $alunoModel;
    private $triagemModel;


    // =========================================================
    // CONSTRUTOR
    // Carrega os Models "Aluno" e "Triagem" para que o
    // Controller possa acessar o banco de dados.
    // =========================================================

    public function __construct()
    {
        $this->alunoModel = $this->model('Aluno');
        $this->triagemModel = $this->model('Triagem');
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
    // ABRIR TRIAGEM MASCULINA
    // Carrega o formulário da triagem masculina para o aluno
    // informado na URL (ex.: triagens/triagemMasculina/5).
    // =========================================================

    public function triagemMasculina($alunoId = null)
    {
        $this->exibirFormulario('masculina', $alunoId);
    }


    // =========================================================
    // ABRIR TRIAGEM FEMININA
    // Carrega o formulário da triagem feminina para o aluno
    // informado na URL (ex.: triagens/triagemFeminina/5).
    // =========================================================

    public function triagemFeminina($alunoId = null)
    {
        $this->exibirFormulario('feminina', $alunoId);
    }


    // =========================================================
    // MÉTODOS ALTERNATIVOS (ROTAS ANTIGAS)
    //
    // Os nomes contêm "3" no lugar da letra "e". Eram métodos
    // separados que abriam as mesmas Views dos métodos acima.
    // Agora apenas repassam para os métodos principais, para
    // que links antigos continuem funcionando.
    //
    // ATENÇÃO:
    // Não remova sem verificar se alguma parte do sistema
    // (links, botões, menus) ainda usa estas rotas.
    // =========================================================

    public function triagemM3sculina($alunoId = null)
    {
        $this->triagemMasculina($alunoId);
    }

    public function triagemFe3minina($alunoId = null)
    {
        $this->triagemFeminina($alunoId);
    }


    // =========================================================
    // SALVAR TRIAGEM MASCULINA
    // Recebe o formulário masculino enviado por POST e salva
    // as respostas no banco de dados.
    // =========================================================

    public function salvarTriagemMasculina($alunoId = null)
    {
        $this->salvar('masculina', $alunoId);
    }


    // =========================================================
    // SALVAR TRIAGEM FEMININA
    // Recebe o formulário feminino enviado por POST e salva
    // as respostas no banco de dados.
    // =========================================================

    public function salvarTriagemFeminina($alunoId = null)
    {
        $this->salvar('feminina', $alunoId);
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
    // Abre a View da triagem (masculina ou feminina), enviando
    // o ID e o nome do aluno para a página.
    // =========================================================

    private function exibirFormulario($tipo, $alunoId)
    {
        $aluno = $this->alunoOuRedireciona($alunoId);

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Página de triagens',
            'descricao' => 'pagina triagens',
            'aluno_id' => (int) $aluno->id,
            'aluno_nome' => $aluno->nome
        ];

        // Escolhe a View de acordo com o tipo de triagem.
        $this->view(
            $tipo === 'masculina'
                ? 'triagens/adicionar_triagem_m'
                : 'triagens/adicionar_triagem_f',
            $dados
        );
    }


    // =========================================================
    // SALVAR TRIAGEM
    // Valida as respostas do formulário e as salva no banco,
    // associadas ao aluno e ao usuário que fez a triagem.
    // =========================================================

    private function salvar($tipo, $alunoId)
    {
        $aluno = $this->alunoOuRedireciona($alunoId);
        $id = (int) $aluno->id;

        // Página para onde o usuário volta em caso de erro.
        $voltar = 'triagens/'
            . ($tipo === 'masculina' ? 'triagemMasculina' : 'triagemFeminina')
            . '/' . $id;

        // Só aceita envio via POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            URL::redirecionar($voltar);
            exit;
        }

        $respostas = [];
        $temErro = false;

        // -----------------------------------------------------
        // VALIDA CADA PERGUNTA
        // As perguntas de cada tipo de triagem vêm da classe
        // TriagemPerguntas.
        // -----------------------------------------------------

        foreach (TriagemPerguntas::por($tipo) as $numero => $pergunta) {

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

            // Sucesso: vai para o perfil do aluno.
            Sessao::mensagem('alunos', 'Triagem salva com sucesso');
            URL::redirecionar('alunos/visualizarPerfil/' . $id);

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
}