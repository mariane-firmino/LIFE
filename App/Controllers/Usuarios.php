<?php

// =========================================================
// BIBLIOTECA DE E-MAIL
// PHPMailer é usado para enviar o e-mail de recuperação
// de senha.
// =========================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Usuarios extends Controller
{
    // =========================================================
    // MODEL DO USUÁRIO
    // É usado para acessar e manipular os dados dos usuários
    // (servidores) no banco de dados.
    // =========================================================

    private $usuarioModel;


    // =========================================================
    // CONSTRUTOR
    // Carrega o Model "Usuario" para que o Controller possa
    // acessar as informações dos usuários no banco de dados.
    // =========================================================

    public function __construct()
    {
        $this->usuarioModel = $this->model('Usuario');
    }

    // =========================================================
    // SENHA FORTE
    // Verifica se a senha atende aos critérios de segurança:
    // - Pelo menos 8 caracteres
    // - Pelo menos uma letra maiúscula
    // - Pelo menos uma letra minúscula
    // - Pelo menos um número
    // - Pelo menos um caractere especial
    // =========================================================
    private function senhaForte(string $senha): bool
    {
        return strlen($senha) >= 8
            && preg_match('/[A-Z]/', $senha)
            && preg_match('/[a-z]/', $senha)
            && preg_match('/[0-9]/', $senha)
            && preg_match('/[^A-Za-z0-9\s]/', $senha);
    }



    // =========================================================
    // CADASTRAR USUÁRIO
    // Recebe os dados do formulário, valida as informações
    // e salva o usuário no banco de dados.
    // =========================================================

    public function cadastrar()
    {
        // Recebe os dados do formulário de cadastro (já sanitizados).
        $formulario = filter_input_array(
            INPUT_POST,
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        // =====================================================
        // VERIFICA SE O FORMULÁRIO FOI ENVIADO
        // =====================================================

        if (isset($formulario)):

            // Organiza os dados recebidos.
            $dados = [
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'cpf' => trim($formulario['cpf']),
                'data_nascimento' => trim($formulario['data_nascimento']),
                'telefone' => trim($formulario['telefone']),
                'siape' => trim($formulario['siape']),
                'funcao' => trim($formulario['funcao']),
                'senha' => trim($formulario['senha']),
                'confirmar_senha' => trim($formulario['confirmar_senha'])
            ];

            // -------------------------------------------------
            // VERIFICA SE ALGUM CAMPO ESTÁ VAZIO
            // Cria uma mensagem de erro para cada campo vazio.
            // -------------------------------------------------

            if (in_array("", $formulario)):

                if (empty($formulario['nome'])):
                    $dados['nome_erro'] = 'Preencha o campo nome';
                endif;

                if (empty($formulario['email'])):
                    $dados['email_erro'] = 'Preencha o campo e-mail';
                endif;

                if (empty($formulario['cpf'])):
                    $dados['cpf_erro'] = 'Preencha o campo CPF';
                endif;

                if (empty($formulario['data_nascimento'])):
                    $dados['data_nascimento_erro'] = 'Preencha o campo data de nascimento';
                endif;

                if (empty($formulario['telefone'])):
                    $dados['telefone_erro'] = 'Preencha o campo telefone';
                endif;

                if (empty($formulario['siape'])):
                    $dados['siape_erro'] = 'Preencha o campo SIAPE';
                endif;

                if (empty($formulario['funcao'])):
                    $dados['funcao_erro'] = 'Preencha o campo função';
                endif;

                if (empty($formulario['senha'])):
                    $dados['senha_erro'] = 'Preencha o campo senha';
                endif;

                if (empty($formulario['confirmar_senha'])):
                    $dados['confirmar_senha_erro'] = 'Preencha o campo confirmar senha';
                endif;

            else:

                // ---------------------------------------------
                // VALIDAÇÃO DOS DADOS
                // ---------------------------------------------

                // Verifica o nome.
                if (Checa::checarNome($formulario['nome'])):
                    $dados['nome_erro'] = 'O nome informado é invalido';

                // Verifica o formato do e-mail.
                elseif (Checa::checarEmail($formulario['email'])):
                    $dados['email_erro'] = 'O e-mail informado é invalido';

                // Verifica o formato do CPF.
                elseif (Checa::checarCpf($formulario['cpf'])):
                    $dados['cpf_erro'] = 'O CPF informado é invalido';

                // Verifica se o CPF já está cadastrado.
                elseif ($this->usuarioModel->checarCpf($formulario['cpf'])):
                    $dados['cpf_erro'] = 'O CPF informado já está cadastrado';

                // Verifica se o e-mail já está cadastrado.
                elseif ($this->usuarioModel->checarEmail($formulario['email'])):
                    $dados['email_erro'] = 'O e-mail informado já está cadastrado';

                // Verifica se o SIAPE já está cadastrado.
                elseif ($this->usuarioModel->checarSiape($formulario['siape'])):
                    $dados['siape_erro'] = 'O SIAPE informado já está cadastrado';

                // Verifica o tamanho mínimo da senha.
                elseif (!$this->senhaForte($formulario['senha'])):
                    $dados['senha_erro'] =
                        'A senha deve ter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial';

                // Verifica se as senhas são iguais.
                elseif ($formulario['senha'] != $formulario['confirmar_senha']):
                    $dados['confirmar_senha_erro'] = 'As senhas são diferentes';

                // ---------------------------------------------
                // SE TUDO ESTIVER CORRETO, SALVA O USUÁRIO
                // ---------------------------------------------

                else:

                    // Criptografa a senha do usuário.
                    $dados['senha'] = password_hash(
                        $formulario['senha'],
                        PASSWORD_DEFAULT
                    );

                    // Armazena os dados do usuário no banco de dados.
                    if ($this->usuarioModel->armazenar($dados)):

                        // Mostra mensagem de sucesso.
                        Sessao::mensagem(
                            'usuarios',
                            'Cadastro realizado com sucesso'
                        );

                        // Depois do cadastro, vai para a página inicial.
                        URL::redirecionar('paginas/index');

                    else:

                        // Caso o cadastro falhe.
                        die("Erro ao armazenar usuario no banco de dados");

                    endif;

                endif;

            endif;

        else:

            // =================================================
            // PRIMEIRO ACESSO À PÁGINA
            // Inicializa todos os campos vazios e carrega a
            // lista de funções para o formulário.
            // =================================================

            $dados = [
                'nome' => '',
                'email' => '',
                'cpf' => '',
                'data_nascimento' => '',
                'telefone' => '',
                'siape' => '',
                'senha' => '',
                'confirmar_senha' => '',
                'funcao' => '',

                'nome_erro' => '',
                'email_erro' => '',
                'cpf_erro' => '',
                'data_nascimento_erro' => '',
                'telefone_erro' => '',
                'siape_erro' => '',
                'funcao_erro' => '',
                'senha_erro' => '',
                'confirmar_senha_erro' => '',

                'funcoes' => $this->usuarioModel->listarFuncoes()
            ];

        endif;

        // Abre a view de cadastro.
        $this->view('usuarios/cadastro', $dados);
    }


    // =========================================================
    // LOGIN
    // Recebe e-mail ou SIAPE e senha, valida e, se estiverem corretos,
    // cria a sessão do usuário.
    // =========================================================

    public function login()
    {
        // Recebe os dados do formulário de login (já sanitizados).
        $formulario = filter_input_array(
            INPUT_POST,
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        // Verifica se o formulário foi enviado.
        if (isset($formulario)):

            $dados = [
                'identificador' => trim($formulario['identificador'] ?? ''),
                'senha' => trim($formulario['senha'] ?? ''),
            ];

            // Verifica se algum campo está vazio.
            if ($dados['identificador'] === '' || $dados['senha'] === ''):

                if ($dados['identificador'] === ''):
                    $dados['identificador_erro'] = 'Preencha o campo e-mail ou SIAPE';
                endif;

                if ($dados['senha'] === ''):
                    $dados['senha_erro'] = 'Preencha o campo senha';
                endif;

            else:

                // Confere e-mail ou SIAPE e senha no banco de dados.
                $usuario = $this->usuarioModel->checarLogin(
                    $dados['identificador'],
                    $dados['senha']
                );

                if ($usuario):

                    // Login correto: cria a sessão e redireciona.
                    $this->criarSessaoUsuario($usuario);

                else:

                    // Login incorreto.
                    Sessao::mensagem(
                        'usuario',
                        'Usuário/SIAPE ou senha inválidos',
                        'alert alert-danger'
                    );

                endif;

            endif;

        else:

            // Primeiro acesso à página: campos vazios.
            $dados = [
                'identificador' => '',
                'senha' => '',
                'identificador_erro' => '',
                'senha_erro' => ''
            ];

        endif;

        // Abre a view de login.
        $this->view('usuarios/login', $dados);
    }


    // =========================================================
    // CRIAR SESSÃO DO USUÁRIO
    // Guarda na sessão os dados do usuário logado e
    // redireciona para a página inicial do sistema.
    // =========================================================

    private function criarSessaoUsuario($usuario)
    {
        $_SESSION['usuario_id'] = $usuario->serv_id;
        $_SESSION['usuario_nome'] = $usuario->serv_nome;
        $_SESSION['usuario_email'] = $usuario->serv_email;
        $_SESSION['usuario_cpf'] = $usuario->serv_cpf;
        $_SESSION['usuario_siape'] = $usuario->serv_siape;
        $_SESSION['usuario_dt_nascimento'] = $usuario->serv_dt_nascimento;
        $_SESSION['usuario_telefone'] = $usuario->tele_numero;
        $_SESSION['usuario_funcao'] = $usuario->func_nome;
        $_SESSION['usuario_foto'] = $usuario->serv_foto;

        URL::redirecionar('paginas/home');
    }


    // =========================================================
    // LOGOUT
    // Remove os dados da sessão, encerra a sessão e volta
    // para a página de login.
    // =========================================================

    public function logout()
    {
        unset($_SESSION['usuario_id']);
        unset($_SESSION['usuario_nome']);
        unset($_SESSION['usuario_email']);
        unset($_SESSION['usuario_cpf']);
        unset($_SESSION['usuario_siape']);
        unset($_SESSION['usuario_dt_nascimento']);
        unset($_SESSION['usuario_telefone']);
        unset($_SESSION['usuario_funcao']);
        unset($_SESSION['usuario_foto']);

        session_destroy();

        URL::redirecionar('usuarios/login');
    }


    // =========================================================
    // ESQUECI MINHA SENHA
    // Recebe o e-mail do usuário, gera um token de recuperação
    // (válido por 30 minutos) e envia o link por e-mail.
    // =========================================================

    public function esqueciMinhaSenha1()
    {
        $dados = [
            'titulo' => 'Esqueci minha senha',
            'descricao' => 'Recuperação de senha',
            'email' => '',
            'erro' => ''
        ];

        // Verifica se o formulário foi enviado.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');

            $dados['email'] = $email;

            if (empty($email)) {

                $dados['erro'] = 'Informe seu e-mail.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $dados['erro'] = 'Informe um e-mail válido.';
            } else {

                // Procura o usuário pelo e-mail.
                $usuario = $this->usuarioModel->buscarPorEmail($email);

                // Mesmo se o e-mail não existir, mostramos a mesma
                // mensagem. Isso evita revelar quais e-mails estão
                // cadastrados no sistema.

                if ($usuario) {

                    // Gera um token seguro.
                    $token = bin2hex(random_bytes(32));

                    // Token válido por 30 minutos.
                    $expira = date('Y-m-d H:i:s', time() + (30 * 60));

                    // Salva o token no banco de dados.
                    $salvou = $this->usuarioModel->criarTokenRecuperacao(
                        $usuario->serv_id,
                        $token,
                        $expira
                    );

                    // Se salvou, envia o e-mail com o link de recuperação.
                    if ($salvou) {

                        $enviado = $this->enviarEmailRecuperacao(
                            $usuario->serv_email,
                            $usuario->serv_nome,
                            $token
                        );

                        if (!$enviado) {
                            $dados['erro'] = 'Não foi possível enviar o e-mail.';
                        }
                    }
                }

                $dados['sucesso'] =
                    'Se o e-mail estiver cadastrado, você receberá um link de recuperação.';
            }
        }

        $this->view('usuarios/esqueciMinhaSenha1', $dados);
    }


    // =========================================================
    // ENVIAR E-MAIL DE RECUPERAÇÃO
    // Usa o PHPMailer (via SMTP do Gmail) para enviar o link
    // de redefinição de senha. Retorna true se enviou.
    // =========================================================

    private function enviarEmailRecuperacao($email, $nome, $token)
    {
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {

            // Configuração do SMTP.
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            // TROQUE PELO SEU GMAIL
            $mail->Username = 'enfermariaifro@gmail.com';

            // TROQUE PELA SUA SENHA DE APP
            $mail->Password = 'cipdejmqyqmtlxps';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remetente.
            $mail->setFrom(
                'rafaelcaldeirasan@gmail.com',
                'Enfermaria IFRO'
            );

            // Destinatário.
            $mail->addAddress($email, $nome);

            // Conteúdo em HTML.
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperação de senha - Enfermaria IFRO';

            // Link de redefinição com o token.
            $link = URL . '/usuarios/redefinirSenha/' . urlencode($token);

            $mail->Body = "
                <div style='font-family: Arial, sans-serif;'>

                    <h2>Enfermaria IFRO</h2>

                    <p>
                        Olá, <strong>{$nome}</strong>.
                    </p>

                    <p>
                        Recebemos uma solicitação para
                        redefinir sua senha.
                    </p>

                    <p>
                        Clique no botão abaixo:
                    </p>

                    <p>
                        <a
                            href='{$link}'
                            style='
                                background-color: #198754;
                                color: white;
                                padding: 12px 20px;
                                text-decoration: none;
                                border-radius: 5px;
                                display: inline-block;
                            '
                        >
                            Redefinir minha senha
                        </a>
                    </p>

                    <p>
                        Este link será válido por
                        <strong>30 minutos</strong>.
                    </p>

                    <p>
                        Se você não solicitou a recuperação,
                        ignore este e-mail.
                    </p>

                </div>
            ";

            // Versão em texto simples (para quem não vê HTML).
            $mail->AltBody =
                "Acesse o seguinte link para redefinir sua senha: " . $link;

            $mail->send();

            return true;
        } catch (Exception $e) {

            // Registra o erro no log do servidor.
            error_log('Erro PHPMailer: ' . $mail->ErrorInfo);

            return false;
        }
    }


    // =========================================================
    // REDEFINIR SENHA
    // Abre a página de nova senha (link enviado por e-mail) e,
    // quando o formulário é enviado, valida o token e salva
    // a nova senha.
    // =========================================================

    public function redefinirSenha($token = null)
    {
        // Se o token não veio pela URL, tenta pegar do formulário.
        if (!$token && isset($_POST['token'])) {
            $token = $_POST['token'];
        }

        // Sem token, o link é inválido.
        if (!$token) {

            $this->view('usuarios/esqueciMinhaSenha2', [
                'erro' => 'Link de recuperação inválido.',
                'token' => ''
            ]);

            return;
        }

        // -----------------------------------------------------
        // POST = usuário enviou a nova senha
        // -----------------------------------------------------

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $senha = $_POST['senha'] ?? '';
            $confirmarSenha = $_POST['confirmarSenha'] ?? '';

            // Tamanho mínimo da senha.
            if (!$this->senhaForte($senha)) {
                $this->view('usuarios/esqueciMinhaSenha2', [
                    'erro' => 'Use pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial.',
                    'token' => $token
                ]);

                return;
            }

            // Confirmação da senha.
            if ($senha !== $confirmarSenha) {

                $this->view('usuarios/esqueciMinhaSenha2', [
                    'erro' => 'As senhas não coincidem.',
                    'token' => $token
                ]);

                return;
            }

            // Verifica se o token existe, não expirou e não foi usado.
            $recuperacao = $this->usuarioModel->buscarTokenRecuperacao($token);

            if (!$recuperacao) {

                $this->view('usuarios/esqueciMinhaSenha2', [
                    'erro' => 'O link é inválido, expirou ou já foi utilizado.',
                    'token' => ''
                ]);

                return;
            }

            // Criptografa a nova senha.
            $novaSenha = password_hash($senha, PASSWORD_DEFAULT);

            // Salva a nova senha no banco.
            $alterou = $this->usuarioModel->alterarSenha(
                $recuperacao->serv_id,
                $novaSenha
            );

            if ($alterou) {

                // Marca o token como usado para não poder ser reutilizado.
                $this->usuarioModel->marcarTokenUsado($token);

                Sessao::mensagem(
                    'usuario',
                    'Senha alterada com sucesso! Faça login novamente.',
                    'alert alert-success'
                );

                URL::redirecionar('usuarios/login');

                return;
            }

            // Caso a alteração falhe.
            $this->view('usuarios/esqueciMinhaSenha2', [
                'erro' => 'Não foi possível alterar sua senha.',
                'token' => $token
            ]);

            return;
        }

        // -----------------------------------------------------
        // GET = mostra a página de nova senha
        // -----------------------------------------------------

        $recuperacao = $this->usuarioModel->buscarTokenRecuperacao($token);

        if (!$recuperacao) {

            $this->view('usuarios/esqueciMinhaSenha2', [
                'erro' => 'O link é inválido, expirou ou já foi utilizado.',
                'token' => ''
            ]);

            return;
        }

        $this->view('usuarios/esqueciMinhaSenha2', [
            'erro' => '',
            'token' => $token
        ]);
    }


    // =========================================================
    // ALTERAR SENHA (USUÁRIO LOGADO)
    // Apenas abre a página de alteração de senha.
    // =========================================================

    public function alterarSenha()
    {
        $dados = [
            'titulo' => 'Página de alteração de senha',
            'descricao' => 'Alteração de senha de usuário'
        ];

        $this->view('usuarios/alterarSenha', $dados);
    }


    // =========================================================
    // SALVAR NOVA SENHA (USUÁRIO LOGADO)
    // Confere a senha atual, valida a nova senha e salva.
    // =========================================================

    public function salvarSenha()
    {
        // Só aceita envio via POST.
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        // Verifica se o usuário está logado.
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . URL . "/usuarios/login");
            exit;
        }

        // Recebe os dados do formulário.
        // Aceita tanto os nomes novos (senha_atual, nova_senha,
        // confirmar_senha) quanto os antigos (senha, novaSenha,
        // confirmarSenha), para funcionar com qualquer versão da view.
        $senhaAtual = trim($_POST['senha_atual'] ?? $_POST['senha'] ?? '');
        $novaSenha = trim($_POST['nova_senha'] ?? $_POST['novaSenha'] ?? '');
        $confirmarSenha = trim($_POST['confirmar_senha'] ?? $_POST['confirmarSenha'] ?? '');

        // -----------------------------------------------------
        // CAMPOS OBRIGATÓRIOS
        // -----------------------------------------------------

        if ($senhaAtual === '') {
            $_SESSION['erro'] = "Digite sua senha atual.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        if ($novaSenha === '') {
            $_SESSION['erro'] = "Digite a nova senha.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        if ($confirmarSenha === '') {
            $_SESSION['erro'] = "Confirme a nova senha.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        // -----------------------------------------------------
        // TAMANHO DA NOVA SENHA
        // -----------------------------------------------------

        if (!$this->senhaForte($novaSenha)) {
            $_SESSION['erro'] =
                'A nova senha deve ter 8 caracteres ou mais, com maiúscula, minúscula, número e caractere especial.';

            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        // -----------------------------------------------------
        // CONFIRMAÇÃO DA SENHA
        // -----------------------------------------------------

        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['erro'] = "As senhas não coincidem.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        // -----------------------------------------------------
        // BUSCA O USUÁRIO LOGADO
        // -----------------------------------------------------

        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);

        if (!$usuario) {
            $_SESSION['erro'] = "Usuário não encontrado.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        // -----------------------------------------------------
        // VERIFICA A SENHA ATUAL
        // -----------------------------------------------------

        if (!password_verify($senhaAtual, $usuario->serv_senha)) {
            $_SESSION['erro'] = "Senha atual incorreta.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }

        // -----------------------------------------------------
        // CRIPTOGRAFA E SALVA A NOVA SENHA
        // -----------------------------------------------------

        $senha = password_hash($novaSenha, PASSWORD_DEFAULT);

        if ($this->usuarioModel->alterarSenha($_SESSION['usuario_id'], $senha)) {

            $_SESSION['sucesso'] = "Senha alterada com sucesso.";
            header("Location: " . URL . "/paginas/perfil");
            exit;
        } else {

            $_SESSION['erro'] = "Erro ao alterar a senha.";
            header("Location: " . URL . "/usuarios/alterarSenha");
            exit;
        }
    }


    // =========================================================
    // EDITAR PERFIL
    // Abre a página de edição do perfil do usuário logado e,
    // quando o formulário é enviado, valida e atualiza os
    // dados (inclusive a foto de perfil).
    // =========================================================

    public function editarPerfil()
    {
        // Verifica se o usuário está logado.
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('usuarios/login');
            exit;
        }

        $id = $_SESSION['usuario_id'];

        // =====================================================
        // SALVAR ALTERAÇÕES
        // =====================================================

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $dataNascimento = trim($_POST['data_nascimento'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');

            // -------------------------------------------------
            // VALIDAÇÃO
            // -------------------------------------------------

            if ($nome === '') {
                $_SESSION['erro'] = 'Preencha o nome.';
                URL::redirecionar('usuarios/editarPerfil');
                exit;
            }

            if ($email === '') {
                $_SESSION['erro'] = 'Preencha o e-mail.';
                URL::redirecionar('usuarios/editarPerfil');
                exit;
            }

            // Busca o usuário atual.
            $usuario = $this->usuarioModel->buscarPorId($id);

            if (!$usuario) {
                $_SESSION['erro'] = 'Usuário não encontrado.';
                URL::redirecionar('paginas/perfil');
                exit;
            }

            // Mantém a foto atual caso nenhuma nova seja enviada.
            $foto = $usuario->serv_foto ?? null;

            // -------------------------------------------------
            // UPLOAD DA FOTO
            // Só é processado se um arquivo foi enviado.
            // -------------------------------------------------

            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                // Verifica se o envio ocorreu sem erros.
                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    $_SESSION['erro'] = 'Erro ao enviar a foto.';
                    URL::redirecionar('usuarios/editarPerfil');
                    exit;
                }

                $arquivo = $_FILES['foto'];

                // Limite de 5 MB.
                if ($arquivo['size'] > 5 * 1024 * 1024) {
                    $_SESSION['erro'] = 'A foto deve ter no máximo 5 MB.';
                    URL::redirecionar('usuarios/editarPerfil');
                    exit;
                }

                // Verifica o tipo real do arquivo (e não só a extensão).
                $tiposPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $tipo = mime_content_type($arquivo['tmp_name']);

                if (!isset($tiposPermitidos[$tipo])) {
                    $_SESSION['erro'] = 'Formato de foto não permitido.';
                    URL::redirecionar('usuarios/editarPerfil');
                    exit;
                }

                $extensao = $tiposPermitidos[$tipo];

                // Nome único para o arquivo: perfil_ID_TIMESTAMP.extensão
                $nomeArquivo = 'perfil_' . $id . '_' . time() . '.' . $extensao;

                // Pasta onde as fotos são salvas:
                // C:\xampp\htdocs\ENFERMARIA\Public\uploads\perfis\
                $pasta = dirname(__DIR__, 2) . '/Public/uploads/perfis/';

                // Cria a pasta caso ela ainda não exista.
                if (!is_dir($pasta)) {
                    if (!mkdir($pasta, 0755, true)) {
                        $_SESSION['erro'] = 'Não foi possível criar a pasta da foto.';
                        URL::redirecionar('usuarios/editarPerfil');
                        exit;
                    }
                }

                $destino = $pasta . $nomeArquivo;

                // Move o arquivo enviado para a pasta definitiva.
                if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
                    $_SESSION['erro'] = 'Não foi possível salvar a foto.';
                    URL::redirecionar('usuarios/editarPerfil');
                    exit;
                }

                $foto = $nomeArquivo;
            }

            // -------------------------------------------------
            // ATUALIZA O BANCO DE DADOS
            // -------------------------------------------------

            $resultado = $this->usuarioModel->atualizarPerfil(
                $id,
                $nome,
                $email,
                $dataNascimento,
                $telefone,
                $foto
            );

            if (!$resultado) {
                $_SESSION['erro'] = 'Não foi possível atualizar o perfil no banco de dados.';
                URL::redirecionar('usuarios/editarPerfil');
                exit;
            }

            // -------------------------------------------------
            // ATUALIZA A SESSÃO
            // Para que os novos dados apareçam sem novo login.
            // -------------------------------------------------

            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['usuario_dt_nascimento'] = $dataNascimento;
            $_SESSION['usuario_telefone'] = $telefone;
            $_SESSION['usuario_foto'] = $foto;

            $_SESSION['sucesso'] = 'Perfil atualizado com sucesso!';

            URL::redirecionar('paginas/perfil');
            exit;
        }

        // =====================================================
        // ABRIR PÁGINA DE EDIÇÃO
        // Busca o usuário e envia os dados para a View.
        // =====================================================

        $usuario = $this->usuarioModel->buscarPorId($id);

        if (!$usuario) {
            $_SESSION['erro'] = 'Usuário não encontrado.';
            URL::redirecionar('paginas/perfil');
            exit;
        }

        $dados = [
            'usuario' => $usuario
        ];

        $this->view('usuarios/editarPerfil', $dados);
    }
}
