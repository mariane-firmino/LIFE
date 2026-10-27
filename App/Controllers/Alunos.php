<?php

class Alunos extends Controller
{
    // =========================================================
    // MODEL DO ALUNO
    // É usado para acessar e manipular os dados dos alunos
    // no banco de dados.
    // =========================================================

    private $alunoModel;
    private $anexoModel;
    private $opcaoModel;


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
        'condicoes',        // <- NOVO
        'condicao_outra',   // <- NOVO
    ];

    // 1b) Nova constante: condições que podem ser marcadas no formulário.
    //     Só estes valores são aceitos (lista branca). Para incluir outra
    //     condição no futuro, basta acrescentar aqui e no formulário.
    private const CONDICOES_ESPECIAIS = [
        'Autismo (TEA)',
        'TDAH',
        'Paralisia cerebral',
        'Deficiência física / motora',
        'Deficiência visual',
        'Deficiência auditiva',
        'Deficiência intelectual',
        'Síndrome de Down',
        'Epilepsia',
        'Outra',
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
        $this->anexoModel = $this->model('AlunoAnexo');
        $this->opcaoModel = $this->model('Opcao');
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

        // Checkboxes chegam como array e o filtro acima os descartaria;
        // por isso lemos direto do $_POST e convertemos para texto.
        if (is_array($formulario)) {
            $formulario['condicoes'] = $this->normalizarCondicoes($_POST['condicoes'] ?? []);
        }

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
            $dados['foto_erro'] = '';
            $fotoValida = $this->validarFotoCadastro($_FILES['foto'] ?? null, $dados['foto_erro']);

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

                // Valida a foto opcional antes dos demais dados.
                if (!$fotoValida) :
                // Mensagem de erro já definida em foto_erro.

                // Valida tipo sanguíneo, altura e peso.
                elseif ($this->validarMedidas($dados)) :
                // As mensagens de erro já foram definidas em $dados.

                // NOVO: valida as condições especiais.
                elseif ($this->validarCondicoes($dados)) :
                // Mensagem de erro já definida em $dados.

                // Opções mantidas pelo administrador.
                elseif ($this->validarOpcoes($dados)) :
                // Mensagens de erro já definidas em $dados.

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
                    $fotoSalva = null;

                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $pastaFotos = dirname(__DIR__, 2) . '/Public/uploads/alunos/';
                        $resultadoFoto = Upload::imagem($_FILES['foto'], $pastaFotos);

                        if (!$resultadoFoto['status']) {
                            $dados['foto_erro'] = $resultadoFoto['mensagem'];
                        } else {
                            $fotoSalva = $resultadoFoto['arquivo'];
                            $dados['foto'] = $fotoSalva;
                        }
                    }

                    if ($dados['foto_erro'] === '') :
                        if ($this->alunoModel->armazenar($dados)) :
                            Sessao::mensagem('alunos', 'Aluno cadastrado com sucesso');
                            URL::redirecionar('alunos/aluno');
                        else :
                            if ($fotoSalva !== null) {
                                @unlink($pastaFotos . $fotoSalva);
                            }
                            $dados['matricula_erro'] = 'Erro ao cadastrar o aluno. Tente novamente.';
                        endif;
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
            $dados['foto_erro'] = '';
        endif;

        $dados['listas'] = $this->listasOpcoes();

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

        // Ficha de saúde (buscada uma vez só, usada em vários cálculos).
        $saude = $this->alunoModel->buscarSaude($aluno->id);

        // IMC calculado automaticamente (null se faltar altura ou peso).
        $imc = $this->calcularImc($aluno->altura, $aluno->peso, $idade);

        // Lista de condições especiais para exibir.
        $condicoes = $this->listarCondicoes($saude);

        // Dados complementares exibidos nas abas do perfil.
        $complemento = $this->anexoModel->buscarComplemento($aluno->id);

        $docs = ['vacina' => [], 'sus' => [], 'laudo' => []];
        foreach ($this->anexoModel->listarDocumentos($aluno->id) as $doc) {
            $docs[$doc->tipo][] = $doc;
        }

        $notasRapidas = $this->anexoModel->listarAnotacoes($aluno->id, 'rapida');

        $podePsico = $this->anexoModel->usuarioPodeVerPsicologico($_SESSION['usuario_id'] ?? 0);
        $notasPsico = $podePsico
            ? $this->anexoModel->listarAnotacoes($aluno->id, 'psicologica')
            : [];

        // Dados que serão enviados para a View.
        $dados = [
            'titulo' => 'Perfil do Aluno',
            'descricao' => 'Visualização do perfil do aluno',
            'aluno' => $aluno,
            'idade' => $idade,
            'saude' => $saude,
            'imc' => $imc,
            'condicoes' => $condicoes,
            'complemento' => $complemento,
            'docs' => $docs,
            'notas_rapidas' => $notasRapidas,
            'notas_psico' => $notasPsico,
            'pode_psico' => $podePsico,
            'atencao' => $this->calcularAtencao($saude, $imc, $condicoes),
            'telefone_aluno' => $this->alunoModel->buscarTelefoneAluno($aluno->id),
            'telefone_responsavel' => $this->alunoModel->buscarTelefoneResponsavel($aluno->responsavel_id),
            'historico' => $this->alunoModel->buscarHistorico($aluno->id),
            'triagens_rapidas' => $this->model('TriagemRapida')->listarPorAluno($aluno->id),
            'triagens' => $this->model('Triagem')->listarPorAluno($aluno->id),
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

        // Converte os checkboxes de condições em texto ("A;B;C").
        if (is_array($formulario)) {
            $formulario['condicoes'] = $this->normalizarCondicoes($_POST['condicoes'] ?? []);
        }

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

                // Condições especiais.
                if ($this->validarCondicoes($dados)) :
                    $temErro = true;
                endif;

                // Os valores atuais seguem válidos se o administrador desativá-los.
                $saudeAtual = $this->alunoModel->buscarSaude($aluno->id);
                $atuais = [
                    'curso' => $aluno->curso,
                    'turno' => $aluno->turno,
                    'ano_turma' => $aluno->ano_turma,
                ];
                foreach (['tipo_escola', 'motivo_ifro', 'acesso_saude'] as $campo) {
                    $atuais[$campo] = $saudeAtual->$campo ?? '';
                }
                if ($this->validarOpcoes($dados, $atuais)) :
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
        $dados['listas'] = $this->listasOpcoes();

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


    // Valida a imagem opcional enviada no cadastro sem movê-la para o armazenamento.
    private function validarFotoCadastro($arquivo, &$mensagem)
    {
        $mensagem = '';

        if ($arquivo === null || (isset($arquivo['error']) && $arquivo['error'] === UPLOAD_ERR_NO_FILE)) {
            return true;
        }

        if (!is_array($arquivo) || !isset($arquivo['error'])) {
            $mensagem = 'Não foi possível enviar a foto. Tente novamente.';
            return false;
        }

        if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE
            || (isset($arquivo['size']) && $arquivo['size'] > 5 * 1024 * 1024)) {
            $mensagem = 'A imagem deve ter no máximo 5 MB.';
            return false;
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $mensagem = 'Não foi possível enviar a foto. Tente novamente.';
            return false;
        }

        if (empty($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
            $mensagem = 'Não foi possível validar a foto enviada.';
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($arquivo['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $mensagem = 'Use uma imagem JPG, PNG ou WEBP.';
            return false;
        }

        return true;
    }

    private function listasOpcoes()
    {
        return $this->opcaoModel->todasAtivas();
    }

    private function validarOpcoes(array &$dados, array $atuais = [])
    {
        $erro = false;

        foreach ($this->listasOpcoes() as $grupo => $permitidos) {
            $valor = $dados[$grupo] ?? '';
            if ($valor === '') {
                continue;
            }

            if (!empty($atuais[$grupo])) {
                $permitidos[] = $atuais[$grupo];
            }

            if (!in_array($valor, $permitidos, true)) {
                $dados[$grupo . '_erro'] = 'Selecione uma opção válida';
                $erro = true;
            }
        }

        return $erro;
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

    // ---------------------------------------------------------------------
    // NORMALIZAR CONDIÇÕES
    // Recebe o array de checkboxes e devolve um texto "A;B;C".
    // Só aceita valores da lista CONDICOES_ESPECIAIS (ignora qualquer
    // outra coisa que alguém tente enviar).
    // ---------------------------------------------------------------------
    private function normalizarCondicoes($lista)
    {
        if (!is_array($lista)) {
            return '';
        }

        // Mantém apenas textos (descarta arrays aninhados).
        $lista = array_filter($lista, 'is_string');

        // array_intersect mantém a ordem da lista oficial e remove repetidos.
        $validas = array_intersect(self::CONDICOES_ESPECIAIS, $lista);

        return implode(';', $validas);
    }

    // ---------------------------------------------------------------------
    // VALIDAR CONDIÇÕES
    // - Se "Outra" estiver marcada, o campo de descrição é obrigatório
    //   (máx. 100 caracteres).
    // - Se "Outra" NÃO estiver marcada, a descrição é limpa.
    // Recebe $dados por referência. Retorna true se houver erro.
    // ---------------------------------------------------------------------
    private function validarCondicoes(array &$dados)
    {
        $marcadas = ($dados['condicoes'] ?? '') === '' ? [] : explode(';', $dados['condicoes']);

        if (!in_array('Outra', $marcadas, true)) {
            $dados['condicao_outra'] = '';
            return false;
        }

        if (($dados['condicao_outra'] ?? '') === '') {
            $dados['condicao_outra_erro'] = 'Descreva a condição';
            return true;
        }

        if (mb_strlen($dados['condicao_outra']) > 100) {
            $dados['condicao_outra_erro'] = 'Máximo de 100 caracteres';
            return true;
        }

        return false;
    }

    // ---------------------------------------------------------------------
    // LISTAR CONDIÇÕES
    // Transforma o texto salvo no banco ("A;B;Outra") em uma lista para
    // a View. Em "Outra", mostra a descrição digitada.
    // ---------------------------------------------------------------------
    private function listarCondicoes($saude)
    {
        if (!$saude || trim((string) $saude->condicoes) === '') {
            return [];
        }

        $lista = [];
        foreach (explode(';', $saude->condicoes) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            if ($item === 'Outra') {
                $detalhe = trim((string) $saude->condicao_outra);
                $item = $detalhe !== '' ? 'Outra: ' . $detalhe : 'Outra condição';
            }

            $lista[] = $item;
        }

        return $lista;
    }


    // ---------------------------------------------------------------------
    // CALCULAR IMC
    // IMC = peso (kg) ÷ altura (m)². Retorna null se faltar algum dado.
    //
    // ATENÇÃO: a tabela abaixo (OMS) vale para ADULTOS (19 anos ou mais).
    // Para menores de 19 anos o correto é comparar com as curvas de
    // IMC por idade e sexo; por isso, nesses casos mostramos só o valor
    // e um aviso, sem classificar (evita um diagnóstico errado).
    // ---------------------------------------------------------------------
    private function calcularImc($altura, $peso, $idade)
    {
        $a = (float) $altura;
        $p = (float) $peso;

        if ($a <= 0 || $p <= 0) {
            return null;
        }

        $valor = $p / ($a * $a);

        $imc = [
            'valor' => round($valor, 1),
            'texto' => number_format($valor, 1, ',', ''),
            'classe' => null,       // classificação (só para adultos)
            'nivel' => 'neutro',    // ok | aviso | alerta | neutro (cor da borda)
            'nota' => '',           // observação exibida no tooltip
        ];

        // Menor de 19 anos (ou idade desconhecida): não classifica.
        if ($idade === null || $idade < 19) {
            $imc['nota'] = 'Para menores de 19 anos, a classificação depende da idade e do sexo (curvas de IMC da OMS). Avalie com o profissional de saúde.';
            return $imc;
        }

        // Faixas da OMS para adultos.
        if ($valor < 18.5) {
            $imc['classe'] = 'Abaixo do peso';
            $imc['nivel'] = 'aviso';
        } elseif ($valor < 25) {
            $imc['classe'] = 'Peso normal';
            $imc['nivel'] = 'ok';
        } elseif ($valor < 30) {
            $imc['classe'] = 'Sobrepeso';
            $imc['nivel'] = 'aviso';
        } elseif ($valor < 35) {
            $imc['classe'] = 'Obesidade grau I';
            $imc['nivel'] = 'alerta';
        } elseif ($valor < 40) {
            $imc['classe'] = 'Obesidade grau II';
            $imc['nivel'] = 'alerta';
        } else {
            $imc['classe'] = 'Obesidade grau III';
            $imc['nivel'] = 'alerta';
        }

        return $imc;
    }


    // ---------------------------------------------------------------------
    // CALCULAR ATENÇÃO
    // Define o selo de atenção do aluno a partir da ficha de saúde.
    //
    //   ALTA  (vermelho): condição especial, doença crônica ou alergia.
    //   MÉDIA (amarelo) : medicação contínua, acompanhamento psicológico,
    //                     cartão de vacina "Não" ou IMC fora do normal
    //                     (somente adultos).
    //   BAIXA (verde)   : nenhum alerta.
    //   SEM DADOS (cinza): aluno sem ficha de saúde.
    //
    // Retorna ['nivel' => ..., 'rotulo' => ..., 'motivos' => [...]].
    // Para mudar as regras, basta editar este método.
    // ---------------------------------------------------------------------
    private function calcularAtencao($saude, $imc, array $condicoes)
    {
        // Sem ficha de saúde: não dá para avaliar.
        if (!$saude) {
            return [
                'nivel' => 'sem-dados',
                'rotulo' => 'Sem ficha de saúde',
                'motivos' => ['A ficha de saúde ainda não foi preenchida.'],
            ];
        }

        $sim = function ($valor) {
            return trim((string) $valor) === 'Sim';
        };

        $motivosAltos = [];
        $motivosMedios = [];

        // ----- Gravidade ALTA -----
        if (!empty($condicoes)) {
            $motivosAltos[] = 'Condição especial: ' . implode(', ', $condicoes);
        }
        if ($sim($saude->doenca_cronica)) {
            $motivosAltos[] = 'Doença crônica';
        }
        if ($sim($saude->alergia_alimentar)) {
            $motivosAltos[] = 'Alergia / restrição alimentar';
        }

        // ----- Gravidade MÉDIA -----
        if ($sim($saude->medicacao_continua)) {
            $motivosMedios[] = 'Uso contínuo de medicação';
        }
        if ($sim($saude->acompanhamento_psi)) {
            $motivosMedios[] = 'Acompanhamento psicológico';
        }
        if (trim((string) $saude->cartao_vacina) === 'Não') {
            $motivosMedios[] = 'Cartão de vacina em falta';
        }
        if ($imc && $imc['classe'] !== null && $imc['nivel'] !== 'ok') {
            $motivosMedios[] = 'IMC: ' . $imc['classe'];
        }

        // ----- Resultado -----
        if ($motivosAltos) {
            return [
                'nivel' => 'alta',
                'rotulo' => 'Atenção alta',
                'motivos' => array_merge($motivosAltos, $motivosMedios),
            ];
        }

        if ($motivosMedios) {
            return [
                'nivel' => 'media',
                'rotulo' => 'Atenção moderada',
                'motivos' => $motivosMedios,
            ];
        }

        return [
            'nivel' => 'baixa',
            'rotulo' => 'Sem alertas',
            'motivos' => [],
        ];
    }
}
