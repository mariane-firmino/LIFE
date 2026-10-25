<?php
require_once dirname(__DIR__) . '/Libraries/NotificationService.php';

// =====================================================================
// CONTROLLER: Admin   ->   App/Controllers/Admin.php
//
// Páginas (endereços):
//   admin/home              painel com os totais do sistema
//   admin/perfis            gerenciar servidores (ativar, recusar, cadastrar)
//   admin/sistema           informações do sistema (anos, cursos, turnos...)
//
// Ações (só aceitam POST, vindas dos botões/formulários):
//   admin/alterarStatus/{id}/{ativo|inativo}
//   admin/recusarCadastro/{id}
//   admin/adicionarServidor
//   admin/adicionarOpcao/{grupo}
//   admin/editarOpcao/{id}
//   admin/alternarOpcao/{id}
//   admin/excluirOpcao/{id}
//
// Todas as páginas e ações exigem que o usuário seja ADMIN (conferido
// no banco, no construtor).
// =====================================================================

class Admin extends Controller
{
    private $admModel;     // totais e servidores
    private $opcaoModel;   // listas do sistema
    private $notificationService;

    // =========================================================
    // CONSTRUTOR
    // Barra quem não está logado e quem não é administrador.
    // =========================================================

    public function __construct()
    {
        // 1) Precisa estar logado.
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('login');
            exit;
        }

        $this->admModel = $this->model('Administracao');
        $this->opcaoModel = $this->model('Opcao');
        $this->notificationService = new NotificationService($this->model('Notificacao'));

        // 2) Precisa ser administrador ativo (confere no banco).
        if (!$this->admModel->usuarioEhAdmin($_SESSION['usuario_id'])) {
            URL::redirecionar('paginas/home');
            exit;
        }
    }


    // =========================================================
    // PÁGINA INICIAL DO ADMIN
    // Totais de servidores e alunos.
    // =========================================================

    public function home()
    {
        $dados = [
            'titulo' => 'Painel do administrador',
            'descricao' => 'Visão geral do sistema',
            'totais' => $this->admModel->totais(),
            'porCurso' => $this->admModel->alunosPorCurso(),
            'porFuncao' => $this->admModel->servidoresPorFuncao(),
        ];

        $this->renderizar('admin/home', $dados);
    }


    // =========================================================
    // GERENCIAR PERFIS (servidores)
    // =========================================================

    // Abre a página (abas: aguardando ativação, todos, adicionar).
    public function perfis()
    {
        $this->renderizar('admin/perfis', $this->dadosPerfis());
    }


    // ---------------------------------------------------------
    // ALTERAR STATUS
    // Ativa ou desativa um servidor.
    // ---------------------------------------------------------
    public function alterarStatus($id = null, $status = null)
    {
        $this->exigirPost('admin/perfis');
        $id = $this->idValido($id, 'admin/perfis');

        // Só dois valores são aceitos.
        if (!in_array((string) $status, ['ativo', 'inativo'], true)) {
            $this->voltar('admin/perfis', $this->abaDeVolta(), 'Status inválido.', true);
        }

        // O admin não pode se desativar (ficaria sem acesso).
        if ($id === (int) $_SESSION['usuario_id']) {
            $this->voltar('admin/perfis', $this->abaDeVolta(), 'Você não pode alterar o seu próprio status.', true);
        }

        $servidor = $this->admModel->buscarServidor($id);
        if ($this->admModel->alterarStatus($id, $status)) {
            if ($servidor && $servidor->status !== $status) {
                $ativo = $status === 'ativo';
                $this->notificationService->avisarUsuario(
                    $servidor,
                    $ativo ? 'Cadastro ativado' : 'Acesso desativado',
                    $ativo
                        ? 'Seu cadastro foi aprovado e seu acesso ao sistema está liberado.'
                        : 'Seu acesso ao sistema foi desativado. Entre em contato com a administração se precisar de esclarecimentos.',
                    $ativo ? 'usuarios/login' : 'usuarios/login',
                    true
                );
            }

            $texto = $status === 'ativo' ? 'Servidor ativado com sucesso.' : 'Servidor desativado com sucesso.';
            $this->voltar('admin/perfis', $this->abaDeVolta(), $texto);
        }

        $this->voltar('admin/perfis', $this->abaDeVolta(), 'Não foi possível alterar o status.', true);
    }


    // ---------------------------------------------------------
    // RECUSAR CADASTRO
    // Apaga um cadastro inativo que não foi aprovado.
    // ---------------------------------------------------------
    public function recusarCadastro($id = null)
    {
        $this->exigirPost('admin/perfis');
        $id = $this->idValido($id, 'admin/perfis');

        if ($id === (int) $_SESSION['usuario_id']) {
            $this->voltar('admin/perfis', $this->abaDeVolta(), 'Você não pode excluir a sua própria conta.', true);
        }

        if ($this->admModel->recusarCadastro($id)) {
            $this->voltar('admin/perfis', $this->abaDeVolta(), 'Cadastro recusado e removido.');
        }

        $this->voltar(
            'admin/perfis',
            $this->abaDeVolta(),
            'Não foi possível remover. Se o servidor já tem histórico no sistema, mantenha-o inativo.',
            true
        );
    }


    // ---------------------------------------------------------
    // ADICIONAR SERVIDOR
    // O admin cria o perfil e ele já nasce ATIVO.
    // Se houver erro, a página abre de novo na aba "Adicionar"
    // com os campos preenchidos e as mensagens.
    // ---------------------------------------------------------
    public function adicionarServidor()
    {
        $this->exigirPost('admin/perfis');

        // Campos do formulário. As senhas NÃO passam por trim().
        $campos = ['nome', 'siape', 'email', 'cpf', 'data_nascimento', 'func_id', 'telefone'];
        $form = [];
        foreach ($campos as $campo) {
            $form[$campo] = trim((string) ($_POST[$campo] ?? ''));
            $form[$campo . '_erro'] = '';
        }
        $form['senha'] = (string) ($_POST['senha'] ?? '');
        $form['confirmar_senha'] = (string) ($_POST['confirmar_senha'] ?? '');
        $form['senha_erro'] = '';
        $form['confirmar_senha_erro'] = '';

        $temErro = false;

        // ----- Nome -----
        if ($form['nome'] === '') {
            $form['nome_erro'] = 'Preencha este campo';
            $temErro = true;
        } elseif (mb_strlen($form['nome']) > 225 || Checa::checarNome($form['nome'])) {
            $form['nome_erro'] = 'O nome informado é inválido';
            $temErro = true;
        }

        // ----- SIAPE: 6 a 8 dígitos e único -----
        if (!preg_match('/^\d{6,8}$/', $form['siape'])) {
            $form['siape_erro'] = 'Informe de 6 a 8 números';
            $temErro = true;
        } elseif ($this->admModel->siapeExiste($form['siape'])) {
            $form['siape_erro'] = 'Este SIAPE já está cadastrado';
            $temErro = true;
        }

        // ----- E-mail: formato e único -----
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($form['email']) > 225) {
            $form['email_erro'] = 'E-mail inválido';
            $temErro = true;
        } elseif ($this->admModel->emailExiste($form['email'])) {
            $form['email_erro'] = 'Este e-mail já está cadastrado';
            $temErro = true;
        }

        // ----- CPF: 11 dígitos (guardado como 000.000.000-00) e único -----
        $cpfNumeros = preg_replace('/\D/', '', $form['cpf']);
        if (strlen($cpfNumeros) !== 11) {
            $form['cpf_erro'] = 'CPF deve ter 11 dígitos';
            $temErro = true;
        } else {
            $form['cpf'] = substr($cpfNumeros, 0, 3) . '.' . substr($cpfNumeros, 3, 3) . '.'
                . substr($cpfNumeros, 6, 3) . '-' . substr($cpfNumeros, 9, 2);

            if ($this->admModel->cpfExiste($form['cpf'])) {
                $form['cpf_erro'] = 'Este CPF já está cadastrado';
                $temErro = true;
            }
        }

        // ----- Data de nascimento: válida e não pode ser futura -----
        $nasc = DateTime::createFromFormat('Y-m-d', $form['data_nascimento']);
        if (!$nasc || $nasc->format('Y-m-d') !== $form['data_nascimento'] || $nasc > new DateTime('today')) {
            $form['data_nascimento_erro'] = 'Data de nascimento inválida';
            $temErro = true;
        }

        // ----- Função: precisa existir na tabela funcao -----
        $idsFuncao = [];
        foreach ($this->admModel->listarFuncoes() as $funcao) {
            $idsFuncao[] = (string) $funcao->id;
        }
        if (!in_array($form['func_id'], $idsFuncao, true)) {
            $form['func_id_erro'] = 'Selecione uma função válida';
            $temErro = true;
        }

        // ----- Telefone (opcional): máximo 15 caracteres -----
        if ($form['telefone'] !== '' && mb_strlen($form['telefone']) > 15) {
            $form['telefone_erro'] = 'Telefone muito longo (máx. 15 caracteres)';
            $temErro = true;
        }

        // ----- Senha: mínimo 8 caracteres e confirmação igual -----
        if (mb_strlen($form['senha']) < 8) {
            $form['senha_erro'] = 'A senha deve ter pelo menos 8 caracteres';
            $temErro = true;
        } elseif ($form['senha'] !== $form['confirmar_senha']) {
            $form['confirmar_senha_erro'] = 'As senhas não conferem';
            $temErro = true;
        }

        // ----- Com erro: reabre a aba "Adicionar" -----
        if ($temErro) {
            // Nunca devolve as senhas para a página.
            $form['senha'] = '';
            $form['confirmar_senha'] = '';

            $this->renderizar('admin/perfis', $this->dadosPerfis([
                'form' => $form,
                'abaAtiva' => 'aba-adicionar',
            ]));
            return;
        }

        // ----- Tudo certo: grava (senha criptografada) -----
        $form['senha_hash'] = password_hash($form['senha'], PASSWORD_DEFAULT);

        if ($this->admModel->criarServidor($form)) {
            $this->voltar('admin/perfis', 'aba-todos', 'Servidor cadastrado e ativado com sucesso.');
        }

        $form['senha'] = '';
        $form['confirmar_senha'] = '';
        $form['email_erro'] = 'Não foi possível cadastrar. Tente novamente.';
        $this->renderizar('admin/perfis', $this->dadosPerfis([
            'form' => $form,
            'abaAtiva' => 'aba-adicionar',
        ]));
    }


    // =========================================================
    // INFORMAÇÕES DO SISTEMA (listas controladas pelo admin)
    // =========================================================

    // Abre a página: uma aba para cada lista (anos, cursos, turnos...).
    public function sistema()
    {
        // Para cada lista: configuração + opções + quantos alunos usam cada uma.
        $grupos = [];
        foreach (Opcao::GRUPOS as $chave => $cfg) {
            $grupos[$chave] = [
                'cfg' => $cfg,
                'opcoes' => $this->opcaoModel->listar($chave),
                'uso' => $this->opcaoModel->usoPorValor($chave),
            ];
        }

        $dados = [
            'titulo' => 'Informações do sistema',
            'descricao' => 'Listas usadas nos formulários',
            'grupos' => $grupos,
        ];

        $this->renderizar('admin/sistema', $dados);
    }


    // ---------------------------------------------------------
    // ADICIONAR OPÇÃO
    // ---------------------------------------------------------
    public function adicionarOpcao($grupo = null)
    {
        $this->exigirPost('admin/sistema');

        // O grupo precisa existir na lista oficial.
        if (!isset(Opcao::GRUPOS[(string) $grupo])) {
            $this->voltar('admin/sistema', '', 'Lista inválida.', true);
        }

        $aba = 'aba-' . $grupo;
        $valor = trim((string) ($_POST['valor'] ?? ''));

        $erro = $this->validarValorOpcao($grupo, $valor, 0);
        if ($erro) {
            $this->voltar('admin/sistema', $aba, $erro, true);
        }

        if ($this->opcaoModel->adicionar($grupo, $valor)) {
            $this->voltar('admin/sistema', $aba, 'Opção adicionada com sucesso.');
        }

        $this->voltar('admin/sistema', $aba, 'Não foi possível adicionar a opção.', true);
    }


    // ---------------------------------------------------------
    // EDITAR OPÇÃO
    // Renomeia e atualiza também os registros que já usavam o nome.
    // ---------------------------------------------------------
    public function editarOpcao($id = null)
    {
        $this->exigirPost('admin/sistema');
        $opcao = $this->carregarOpcao($id);
        $aba = 'aba-' . $opcao->grupo;

        $valor = trim((string) ($_POST['valor'] ?? ''));

        // Não mudou nada: não precisa gravar.
        if ($valor === $opcao->valor) {
            $this->voltar('admin/sistema', $aba, 'Nenhuma alteração feita.');
        }

        $erro = $this->validarValorOpcao($opcao->grupo, $valor, (int) $opcao->id);
        if ($erro) {
            $this->voltar('admin/sistema', $aba, $erro, true);
        }

        if ($this->opcaoModel->renomear($opcao->id, $valor)) {
            $this->voltar('admin/sistema', $aba, 'Opção atualizada. Os registros que usavam o nome antigo também foram atualizados.');
        }

        $this->voltar('admin/sistema', $aba, 'Não foi possível atualizar a opção.', true);
    }


    // ---------------------------------------------------------
    // ATIVAR / DESATIVAR OPÇÃO
    // Opção desativada some dos formulários, mas os alunos que já
    // a usam continuam com o valor.
    // ---------------------------------------------------------
    public function alternarOpcao($id = null)
    {
        $this->exigirPost('admin/sistema');
        $opcao = $this->carregarOpcao($id);
        $aba = 'aba-' . $opcao->grupo;

        if ($this->opcaoModel->alternar($opcao->id)) {
            $this->voltar('admin/sistema', $aba, $opcao->ativo ? 'Opção desativada.' : 'Opção ativada.');
        }

        $this->voltar('admin/sistema', $aba, 'Não foi possível alterar a opção.', true);
    }


    // ---------------------------------------------------------
    // EXCLUIR OPÇÃO
    // Só permite se nenhum aluno estiver usando.
    // ---------------------------------------------------------
    public function excluirOpcao($id = null)
    {
        $this->exigirPost('admin/sistema');
        $opcao = $this->carregarOpcao($id);
        $aba = 'aba-' . $opcao->grupo;

        $uso = $this->opcaoModel->usoPorValor($opcao->grupo);
        if (($uso[$opcao->valor] ?? 0) > 0) {
            $this->voltar('admin/sistema', $aba, 'Esta opção está em uso. Desative-a em vez de excluir.', true);
        }

        if ($this->opcaoModel->excluir($opcao->id)) {
            $this->voltar('admin/sistema', $aba, 'Opção excluída com sucesso.');
        }

        $this->voltar('admin/sistema', $aba, 'Não foi possível excluir a opção.', true);
    }


    // =========================================================
    // MÉTODOS AUXILIARES (privados)
    // =========================================================

    // Abre uma View já com o total de pendentes (para o sino do menu).
    private function renderizar($view, array $dados)
    {
        $dados['totalPendentes'] = $this->admModel->contarPendentes();
        $this->view($view, $dados);
    }

    // Dados da página "Gerenciar perfis". $extra traz o formulário
    // com erros e a aba que deve abrir (quando houver).
    private function dadosPerfis(array $extra = [])
    {
        $servidores = $this->admModel->listarServidores();

        // "Aguardando ativação" = servidores inativos.
        $pendentes = array_values(array_filter($servidores, function ($s) {
            return $s->status === 'inativo';
        }));

        return array_merge([
            'titulo' => 'Gerenciar perfis',
            'descricao' => 'Ativar e cadastrar servidores',
            'servidores' => $servidores,
            'pendentes' => $pendentes,
            'funcoes' => $this->admModel->listarFuncoes(),
            'form' => null,
            'abaAtiva' => 'aba-pendentes',
        ], $extra);
    }

    // ---------------------------------------------------------
    // VALIDAR VALOR DE OPÇÃO
    // Devolve a mensagem de erro, ou '' se estiver tudo certo.
    // ---------------------------------------------------------
    private function validarValorOpcao($grupo, $valor, $ignorarId)
    {
        $cfg = Opcao::GRUPOS[$grupo];

        if ($valor === '') {
            return 'Preencha o campo antes de salvar.';
        }

        if (mb_strlen($valor) > $cfg['max']) {
            return 'O valor deve ter no máximo ' . $cfg['max'] . ' caracteres.';
        }

        // Aspas, & e < > são recusados: dariam problema na hora de comparar
        // com o que o formulário envia.
        if (preg_match('/[<>"\'&]/', $valor)) {
            return 'Não use os caracteres < > " \' & no nome.';
        }

        if ($cfg['padrao'] !== null && !preg_match($cfg['padrao'], $valor)) {
            return 'Formato inválido. ' . $cfg['ajuda'];
        }

        if ($this->opcaoModel->existe($grupo, $valor, $ignorarId)) {
            return 'Esta opção já existe nesta lista.';
        }

        return '';
    }

    // Valida o id da opção e busca; se algo estiver errado, volta à página.
    private function carregarOpcao($id)
    {
        $id = $this->idValido($id, 'admin/sistema');

        $opcao = $this->opcaoModel->buscar($id);
        if (!$opcao || !isset(Opcao::GRUPOS[$opcao->grupo])) {
            $this->voltar('admin/sistema', '', 'Opção não encontrada.', true);
        }

        return $opcao;
    }

    // Garante que o id é um número; senão volta para a página.
    private function idValido($id, $rotaVolta)
    {
        if (empty($id) || !ctype_digit((string) $id)) {
            URL::redirecionar($rotaVolta);
            exit;
        }

        return (int) $id;
    }

    // As ações só funcionam via POST (nunca por link digitado).
    private function exigirPost($rotaVolta)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            URL::redirecionar($rotaVolta);
            exit;
        }
    }

    // Aba para a qual voltar depois de ativar/desativar/recusar.
    // Vem do formulário, mas só aceita valores conhecidos.
    private function abaDeVolta()
    {
        $aba = (string) ($_POST['voltar'] ?? '');

        return in_array($aba, ['aba-pendentes', 'aba-todos'], true) ? $aba : 'aba-pendentes';
    }

    // Guarda a mensagem, volta para a página e abre a aba (#aba-xxx).
    private function voltar($rota, $aba, $mensagem, $erro = false)
    {
        Sessao::mensagem('admin', $mensagem, $erro ? 'alert alert-danger' : 'alert alert-success');
        URL::redirecionar($rota . ($aba !== '' ? '#' . $aba : ''));
        exit;
    }
}