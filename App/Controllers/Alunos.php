<?php

class Alunos extends Controller
{
    // =========================================================
    // MODEL DO ALUNO
    // É usado para acessar e manipular os dados dos alunos
    // no banco de dados.
    // =========================================================

    private $alunoModel;


    // =========================================================
    // CAMPOS DE SAÚDE E HÁBITOS
    // Todos esses campos são opcionais no cadastro.
    // =========================================================

    private const CAMPOS_SAUDE = [
        'tipo_escola',
        'motivo_ifro',
        'ja_reprovou',
        'quantas_vezes',
        'estuda_fora',
        'quanto_tempo',
        'nota_curso',
        'nota_motivacao',
        'atendimento',
        'atendimento_porque',
        'acesso_saude',
        'alergia_alimentar',
        'alergia_qual',
        'doenca_cronica',
        'doenca_qual',
        'medicacao_continua',
        'medicacao_qual',
        'acompanhamento_psi',
        'cartao_vacina',
        'ja_internado',
        'internado_motivo',
        'ja_cirurgia',
        'cirurgia_qual',
        'padrao_sono',
        'refeicoes_dia',
        'alimentacao_habitual',
        'fuma',
        'bebida_alcoolica',
        'pais_sabem',
        'drogas_ilicitas',
        'drogas_qual',
        'atividade_fisica',
        'frequencia_atividade',
        'banhos_dia',
    ];


    // =========================================================
    // CAMPOS OBRIGATÓRIOS
    // São necessários para cadastrar um aluno.
    // =========================================================

    private const CAMPOS_OBRIGATORIOS = [
        'nome',
        'data_nascimento',
        'curso',
        'turno',
        'ano_turma',
        'sexo',
        'matricula',
        'nome_responsavel',
    ];


    // =========================================================
    // CAMPOS OPCIONAIS
    // Informações que podem ser preenchidas, mas não são
    // obrigatórias para realizar o cadastro
    // (telefones, tipo sanguíneo, altura e peso).
    // =========================================================

    private const CAMPOS_OPCIONAIS_EXTRA = [
        'telefone_aluno',
        'telefone_responsavel',
        'tipo_sanguineo',
        'altura',
        'peso',
    ];


    // =========================================================
    // TIPOS SANGUÍNEOS VÁLIDOS
    // Lista usada para validar o tipo sanguíneo informado.
    // =========================================================

    private const TIPOS_SANGUINEOS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];


    // =========================================================
    // CONSTRUTOR
    // Carrega o Model "Aluno" para que o Controller possa
    // acessar as informações dos alunos no banco de dados.
    // =========================================================

    public function __construct()
    {
        $this->alunoModel = $this->model('Aluno');
    }


    // =========================================================
    // PÁGINA PRINCIPAL DE ALUNOS
    // Apenas abre a página de alunos.
    // =========================================================

    public function aluno()
    {
        $dados = [
            'titulo' => 'Página de alunos',
            'descricao' => 'pagina alunos'
        ];

        $this->view('alunos/aluno', $dados);
    }


    // =========================================================
    // PÁGINA DE CONSULTA DE ALUNOS
    // Busca todos os alunos cadastrados no banco de dados
    // e envia essas informações para a View.
    // =========================================================

    public function consulta()
    {
        // Busca todos os alunos através do Model.
        $alunos = $this->alunoModel->listarTodos();

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Página de consultas',
            'descricao' => 'Página de consulta de alunos',
            'alunos' => $alunos
        ];

        // Abre a página de consulta e envia os alunos.
        $this->view('alunos/consulta', $dados);
    }


    // =========================================================
    // CADASTRAR ALUNO
    // Recebe os dados do formulário, valida as informações
    // e salva o aluno no banco de dados.
    // =========================================================

    public function cadastrar()
    {
        // Junta todos os campos que podem aparecer no formulário.
        $todosOsCampos = array_merge(
            self::CAMPOS_OBRIGATORIOS,
            self::CAMPOS_OPCIONAIS_EXTRA,
            self::CAMPOS_SAUDE
        );

        // Recebe os dados do formulário de cadastro (já sanitizados).
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        // =====================================================
        // VERIFICA SE O FORMULÁRIO FOI ENVIADO
        // =====================================================

        if (isset($formulario)):

            // -------------------------------------------------
            // ORGANIZA OS DADOS RECEBIDOS
            // Para cada campo, guarda o valor e cria também
            // um espaço para uma possível mensagem de erro.
            // -------------------------------------------------

            $dados = [];
            foreach ($todosOsCampos as $campo) {
                $dados[$campo] = trim($formulario[$campo] ?? '');
                $dados[$campo . '_erro'] = '';
            }

            // -------------------------------------------------
            // VERIFICA CAMPOS OBRIGATÓRIOS
            // -------------------------------------------------

            $temCampoVazio = false;
            foreach (self::CAMPOS_OBRIGATORIOS as $campo) {
                if ($dados[$campo] === '') {
                    $dados[$campo . '_erro'] = 'Preencha este campo';
                    $temCampoVazio = true;
                }
            }

            // -------------------------------------------------
            // CONTINUA A VALIDAÇÃO SOMENTE SE NÃO HOUVER
            // CAMPOS OBRIGATÓRIOS VAZIOS.
            // -------------------------------------------------

            if (!$temCampoVazio):

                // Valida tipo sanguíneo, altura e peso.
                if ($this->validarMedidas($dados)) :
                    // As mensagens de erro já foram definidas em $dados.

                // Verifica o nome do aluno.
                elseif (Checa::checarNome($dados['nome'])) :
                    $dados['nome_erro'] = 'O nome informado é inválido';

                // Verifica o nome do responsável.
                elseif (Checa::checarNome($dados['nome_responsavel'])) :
                    $dados['nome_responsavel_erro'] = 'O nome informado é inválido';

                // Verifica se a matrícula já existe.
                elseif ($this->alunoModel->checarMatricula($dados['matricula'])) :
                    $dados['matricula_erro'] = 'Esta matrícula já está cadastrada';

                // Se tudo estiver correto, salva o aluno no banco.
                else :
                    if ($this->alunoModel->armazenar($dados)) :
                        // Mostra mensagem de sucesso.
                        Sessao::mensagem('alunos', 'Aluno cadastrado com sucesso');

                        // Depois do cadastro, volta para a página principal de alunos.
                        URL::redirecionar('alunos/aluno');
                    else :
                        // Caso o cadastro falhe.
                        $dados['matricula_erro'] = 'Erro ao cadastrar o aluno. Tente novamente.';
                    endif;
                endif;
            endif;

        else :

            // =================================================
            // PRIMEIRO ACESSO À PÁGINA
            // Inicializa todos os campos vazios.
            // =================================================

            $dados = [];
            foreach ($todosOsCampos as $campo) {
                $dados[$campo] = '';
                $dados[$campo . '_erro'] = '';
            }
        endif;

        // Abre a view de cadastro.
        $this->view('alunos/cadastrar', $dados);
    }


    // =========================================================
    // VISUALIZAR PERFIL
    // Abre a página de visualização do perfil do aluno,
    // com dados pessoais, saúde, telefones e histórico.
    // =========================================================

    public function visualizarPerfil($id = null)
    {
        // Se o ID não for informado ou não for numérico,
        // volta para a página de consulta.
        if (empty($id) || !ctype_digit((string) $id)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Busca o aluno pelo ID.
        $aluno = $this->alunoModel->buscarPorId((int) $id);

        // Se o aluno não existir, volta para a consulta.
        if (!$aluno) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Calcula a idade do aluno a partir da data de nascimento.
        $idade = null;
        if (!empty($aluno->data_nascimento)) {
            try {
                $idade = (new DateTime($aluno->data_nascimento))->diff(new DateTime('today'))->y;
            } catch (Exception $e) {
                $idade = null;
            }
        }

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Perfil do Aluno',
            'descricao' => 'Visualização do perfil do aluno',
            'aluno' => $aluno,
            'idade' => $idade,
            'saude' => $this->alunoModel->buscarSaude($aluno->id),
            'telefone_aluno' => $this->alunoModel->buscarTelefoneAluno($aluno->id),
            'telefone_responsavel' => $this->alunoModel->buscarTelefoneResponsavel($aluno->responsavel_id),
            'historico' => $this->alunoModel->buscarHistorico($aluno->id),
        ];

        $this->view('alunos/visualizarPerfil', $dados);
    }


    // =========================================================
    // EDITAR PERFIL
    // Abre a página de edição do perfil do aluno e, quando o
    // formulário é enviado, valida e atualiza os dados.
    // =========================================================

    public function editarPerfil($id = null)
    {
        // Se o ID não for informado ou não for numérico,
        // volta para a página de consulta.
        if (empty($id) || !ctype_digit((string) $id)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Busca o aluno pelo ID.
        $aluno = $this->alunoModel->buscarPorId((int) $id);

        // Se o aluno não existir, volta para a consulta.
        if (!$aluno) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Todos os campos do formulário de edição (inclui o RG).
        $campos = array_merge(
            self::CAMPOS_OBRIGATORIOS,
            self::CAMPOS_OPCIONAIS_EXTRA,
            self::CAMPOS_SAUDE,
            ['rg']
        );

        // Recebe os dados do formulário (sem sanitizar; a view escapa na saída).
        $formulario = filter_input_array(INPUT_POST);

        // =====================================================
        // FORMULÁRIO ENVIADO
        // Usa o que veio no POST (texto puro).
        // =====================================================

        if ($formulario !== null && $formulario !== false):

            // Organiza os dados e cria um espaço para os erros.
            $dados = [];
            foreach ($campos as $campo) {
                $dados[$campo] = trim((string) ($formulario[$campo] ?? ''));
                $dados[$campo . '_erro'] = '';
            }

            $temErro = false;

            // -------------------------------------------------
            // VERIFICA CAMPOS OBRIGATÓRIOS
            // -------------------------------------------------

            foreach (self::CAMPOS_OBRIGATORIOS as $campo) {
                if ($dados[$campo] === '') {
                    $dados[$campo . '_erro'] = 'Preencha este campo';
                    $temErro = true;
                }
            }

            // -------------------------------------------------
            // VALIDAÇÕES (somente se os obrigatórios estiverem preenchidos)
            // -------------------------------------------------

            if (!$temErro):

                // Nome do aluno.
                if (Checa::checarNome($dados['nome'])) :
                    $dados['nome_erro'] = 'O nome informado é inválido';
                    $temErro = true;
                endif;

                // Nome do responsável.
                if (Checa::checarNome($dados['nome_responsavel'])) :
                    $dados['nome_responsavel_erro'] = 'O nome informado é inválido';
                    $temErro = true;
                endif;

                // Data de nascimento: formato válido e não pode ser no futuro.
                $nasc = DateTime::createFromFormat('Y-m-d', $dados['data_nascimento']);
                if (!$nasc || $nasc->format('Y-m-d') !== $dados['data_nascimento'] || $nasc > new DateTime('today')) :
                    $dados['data_nascimento_erro'] = 'Data de nascimento inválida';
                    $temErro = true;
                endif;

                // Sexo: deve ser uma das opções permitidas.
                if (!in_array($dados['sexo'], ['Masculino', 'Feminino', 'Outro'], true)) :
                    $dados['sexo_erro'] = 'Selecione uma opção válida';
                    $temErro = true;
                endif;

                // Turno: deve ser uma das opções permitidas.
                if (!in_array($dados['turno'], ['Matutino', 'Vespertino', 'Noturno'], true)) :
                    $dados['turno_erro'] = 'Selecione uma opção válida';
                    $temErro = true;
                endif;

                // Ano da turma: exatamente 2 dígitos.
                if (!preg_match('/^\d{2}$/', $dados['ano_turma'])) :
                    $dados['ano_turma_erro'] = 'Informe 2 dígitos (ex: 26)';
                    $temErro = true;
                endif;

                // Matrícula: tamanho máximo e verificação de duplicidade
                // (ignorando o próprio aluno).
                if (mb_strlen($dados['matricula']) > 13) :
                    $dados['matricula_erro'] = 'A matrícula deve ter no máximo 13 caracteres';
                    $temErro = true;
                elseif ($this->alunoModel->checarMatriculaOutroAluno($dados['matricula'], $aluno->id)) :
                    $dados['matricula_erro'] = 'Esta matrícula já está cadastrada para outro aluno';
                    $temErro = true;
                endif;

                // RG (opcional): tamanho máximo e verificação de duplicidade.
                if ($dados['rg'] !== '') :
                    if (mb_strlen($dados['rg']) > 6) :
                        $dados['rg_erro'] = 'O RG deve ter no máximo 6 caracteres';
                        $temErro = true;
                    elseif ($this->alunoModel->checarRgOutroAluno($dados['rg'], $aluno->id)) :
                        $dados['rg_erro'] = 'Este RG já está cadastrado para outro aluno';
                        $temErro = true;
                    endif;
                endif;

                // Tipo sanguíneo, altura e peso.
                if ($this->validarMedidas($dados)) :
                    $temErro = true;
                endif;

                // Telefones (opcionais): tamanho máximo de 15 caracteres.
                foreach (['telefone_aluno', 'telefone_responsavel'] as $tel) {
                    if ($dados[$tel] !== '' && mb_strlen($dados[$tel]) > 15) {
                        $dados[$tel . '_erro'] = 'Telefone muito longo (máx. 15 caracteres)';
                        $temErro = true;
                    }
                }

                // -------------------------------------------------
                // SE NÃO HOUVER ERROS, ATUALIZA O ALUNO NO BANCO
                // -------------------------------------------------

                if (!$temErro) :
                    if ($this->alunoModel->atualizar($aluno->id, $dados)) :
                        Sessao::mensagem('alunos', 'Perfil do aluno atualizado com sucesso');
                        URL::redirecionar('alunos/visualizarPerfil/' . (int) $aluno->id);
                        exit;
                    else :
                        $dados['erro_geral'] = 'Não foi possível salvar as alterações. Tente novamente.';
                    endif;
                endif;
            endif;

        else :

            // =================================================
            // PRIMEIRO ACESSO À PÁGINA
            // Preenche o formulário com o que já está no banco.
            // =================================================

            $saude = $this->alunoModel->buscarSaude($aluno->id);

            $dados = [
                'nome' => $aluno->nome,
                'data_nascimento' => $aluno->data_nascimento,
                'curso' => $aluno->curso,
                'turno' => $aluno->turno,
                'ano_turma' => $aluno->ano_turma,
                'sexo' => $aluno->sexo,
                'matricula' => $aluno->matricula,
                'rg' => $aluno->rg ?? '',
                'tipo_sanguineo' => $aluno->tipo_sanguineo ?? '',
                'altura' => ($aluno->altura !== null && $aluno->altura !== '') ? number_format((float) $aluno->altura, 2, ',', '') : '',
                'peso' => ($aluno->peso !== null && $aluno->peso !== '') ? rtrim(rtrim(number_format((float) $aluno->peso, 2, ',', ''), '0'), ',') : '',
                'nome_responsavel' => $aluno->responsavel,
                'telefone_aluno' => $this->alunoModel->buscarTelefoneAluno($aluno->id),
                'telefone_responsavel' => $this->alunoModel->buscarTelefoneResponsavel($aluno->responsavel_id),
            ];

            // Preenche os campos de saúde com os dados salvos (ou vazio).
            foreach (self::CAMPOS_SAUDE as $campo) {
                $dados[$campo] = ($saude && $saude->$campo !== null) ? (string) $saude->$campo : '';
            }

            // Inicializa os espaços de erro.
            foreach ($campos as $campo) {
                $dados[$campo . '_erro'] = '';
            }
        endif;

        // Informações extras enviadas para a View.
        $dados['titulo'] = 'Página de edição de perfil';
        $dados['descricao'] = 'pagina edição de perfil';
        $dados['aluno_id'] = (int) $aluno->id;

        $this->view('alunos/editarPerfil', $dados);
    }


    // =========================================================
    // ATUALIZAR FOTO
    // Recebe a foto enviada na tela "Visualizar Perfil"
    // e salva para o aluno.
    // =========================================================

    public function atualizarFoto($id = null)
    {
        // Só usuários logados podem alterar a foto.
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('login');
            exit;
        }

        // Se o ID não for informado ou não for numérico,
        // volta para a página de consulta.
        if (empty($id) || !ctype_digit((string) $id)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Página para onde o usuário volta ao final.
        $destino = 'alunos/visualizarPerfil/' . (int) $id;

        // Só aceita envio via POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            URL::redirecionar($destino);
            exit;
        }

        // Busca o aluno pelo ID.
        $aluno = $this->alunoModel->buscarPorId((int) $id);

        if (!$aluno) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        // Se o arquivo passar do limite do PHP
        // (upload_max_filesize / post_max_size), mostra erro.
        if (
            !isset($_FILES['foto'])
            || $_FILES['foto']['error'] === UPLOAD_ERR_INI_SIZE
            || $_FILES['foto']['error'] === UPLOAD_ERR_FORM_SIZE
        ) {
            Sessao::mensagem('alunos', 'A imagem deve ter no máximo 5 MB.', 'alert alert-danger');
            URL::redirecionar($destino);
            exit;
        }

        // Pasta onde as fotos são salvas: Public/uploads/alunos/
        $pasta = dirname(__DIR__, 2) . '/Public/uploads/alunos/';

        // Valida e salva a imagem enviada.
        $resultado = Upload::imagem($_FILES['foto'], $pasta);

        if (!$resultado['status']) {
            Sessao::mensagem('alunos', $resultado['mensagem'], 'alert alert-danger');
            URL::redirecionar($destino);
            exit;
        }

        // Salva o nome do novo arquivo no banco de dados.
        if ($this->alunoModel->atualizarFoto($aluno->id, $resultado['arquivo'])) {

            // Apaga a foto antiga para não acumular arquivos.
            if (!empty($aluno->foto)) {
                $antiga = $pasta . basename((string) $aluno->foto);
                if (is_file($antiga)) {
                    @unlink($antiga);
                }
            }

            Sessao::mensagem('alunos', 'Foto do aluno atualizada com sucesso');

        } else {

            // Se falhar no banco, remove o arquivo recém-enviado.
            @unlink($pasta . $resultado['arquivo']);
            Sessao::mensagem('alunos', 'Não foi possível salvar a foto. Tente novamente.', 'alert alert-danger');
        }

        URL::redirecionar($destino);
        exit;
    }


    // =========================================================
    // VALIDAR MEDIDAS
    // Valida tipo sanguíneo, altura e peso (campos opcionais).
    // Recebe $dados por referência para gravar as mensagens de
    // erro. Retorna true se houver algum erro.
    // =========================================================

    private function validarMedidas(array &$dados)
    {
        $erro = false;

        // Tipo sanguíneo: se informado, precisa estar na lista válida.
        if (($dados['tipo_sanguineo'] ?? '') !== '' && !in_array($dados['tipo_sanguineo'], self::TIPOS_SANGUINEOS, true)) {
            $dados['tipo_sanguineo_erro'] = 'Tipo sanguíneo inválido';
            $erro = true;
        }

        // Altura: aceita vírgula ou ponto, entre 0,5 m e 2,5 m.
        if (($dados['altura'] ?? '') !== '') {
            $alt = str_replace(',', '.', $dados['altura']);
            if (!preg_match('/^\d(\.\d{1,2})?$/', $alt) || (float) $alt < 0.5 || (float) $alt > 2.5) {
                $dados['altura_erro'] = 'Altura inválida (ex: 1,70)';
                $erro = true;
            }
        }

        // Peso: aceita vírgula ou ponto, entre 10 kg e 300 kg.
        if (($dados['peso'] ?? '') !== '') {
            $pes = str_replace(',', '.', $dados['peso']);
            if (!preg_match('/^\d{1,3}(\.\d{1,2})?$/', $pes) || (float) $pes < 10 || (float) $pes > 300) {
                $dados['peso_erro'] = 'Peso inválido (ex: 65,5)';
                $erro = true;
            }
        }

        return $erro;
    }
}