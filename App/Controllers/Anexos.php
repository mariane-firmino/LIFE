<?php
// =====================================================================
// CONTROLLER: Anexos   ->   App/Controllers/Anexos.php
//
// Recebe as ações das novas abas do perfil do aluno:
//   anexos/enviarDocumento/{aluno}/{vacina|sus|laudo}
//   anexos/excluirDocumento/{documento}
//   anexos/arquivo/{documento}            (abre a imagem/PDF)
//   anexos/salvarSus/{aluno}
//   anexos/salvarInfoExtras/{aluno}
//   anexos/adicionarAnotacao/{aluno}/{rapida|psicologica}
//   anexos/excluirAnotacao/{anotacao}
//
// SEGURANÇA DOS ARQUIVOS
// Cartão de vacina, SUS e laudos são dados sensíveis. Por isso:
//  - ficam em  storage/alunos/{id}/  (FORA da pasta Public);
//  - só são abertos por anexos/arquivo/{id}, que exige login;
//  - o tipo do arquivo é conferido pelo CONTEÚDO (não pela extensão);
//  - o nome salvo é aleatório (não dá para adivinhar o endereço).
// =====================================================================

class Anexos extends Controller
{
    private $anexoModel;
    private $alunoModel;

    // Formatos aceitos: tipo detectado => extensão usada ao salvar.
    private const MIMES_IMAGEM = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    private const MIMES_IMAGEM_PDF = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'application/pdf' => 'pdf',
    ];

    // Regras de cada tipo de documento.
    //   aba = aba que reabre depois da ação
    //   max_mb = tamanho máximo do arquivo
    //   titulo_obrigatorio = laudo precisa de título
    private const TIPOS_DOCUMENTO = [
        'vacina' => ['aba' => 'aba-vacina', 'rotulo' => 'Foto do cartão de vacina', 'mimes' => self::MIMES_IMAGEM,     'max_mb' => 5,  'titulo_obrigatorio' => false],
        'sus'    => ['aba' => 'aba-sus',    'rotulo' => 'Foto do cartão do SUS',    'mimes' => self::MIMES_IMAGEM,     'max_mb' => 5,  'titulo_obrigatorio' => false],
        'laudo'  => ['aba' => 'aba-laudos', 'rotulo' => 'Laudo médico',             'mimes' => self::MIMES_IMAGEM_PDF, 'max_mb' => 10, 'titulo_obrigatorio' => true],
    ];

    // Limites de texto.
    private const MAX_INFO_EXTRAS = 5000;
    private const MAX_ANOTACAO = 1000;


    // =========================================================
    // CONSTRUTOR
    // Exige login e carrega os Models.
    // =========================================================

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            URL::redirecionar('login');
            exit;
        }

        $this->anexoModel = $this->model('AlunoAnexo');
        $this->alunoModel = $this->model('Aluno');
    }


    // =========================================================
    // DOCUMENTOS
    // =========================================================

    // ---------------------------------------------------------
    // ENVIAR DOCUMENTO
    // Valida os campos, salva o arquivo e registra no banco.
    // ---------------------------------------------------------
    public function enviarDocumento($id = null, $tipo = null)
    {
        $aluno = $this->carregarAluno($id);

        // Tipo precisa existir na lista (vacina, sus ou laudo).
        if (!isset(self::TIPOS_DOCUMENTO[(string) $tipo])) {
            URL::redirecionar('alunos/visualizarPerfil/' . (int) $aluno->id);
            exit;
        }
        $cfg = self::TIPOS_DOCUMENTO[$tipo];
        $aba = $cfg['aba'];

        $this->exigirPost($aluno->id);

        // Campos de texto (a View escapa na saída).
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $data = trim((string) ($_POST['data_documento'] ?? ''));

        if ($cfg['titulo_obrigatorio'] && $titulo === '') {
            $this->voltar($aluno->id, $aba, 'Informe o título do documento.', true);
        }
        if (mb_strlen($titulo) > 150) {
            $this->voltar($aluno->id, $aba, 'O título deve ter no máximo 150 caracteres.', true);
        }
        if (mb_strlen($descricao) > 500) {
            $this->voltar($aluno->id, $aba, 'A descrição deve ter no máximo 500 caracteres.', true);
        }

        // Data do documento (opcional): formato válido e não pode ser futura.
        if ($data !== '') {
            $dt = DateTime::createFromFormat('Y-m-d', $data);
            if (!$dt || $dt->format('Y-m-d') !== $data || $dt > new DateTime('today')) {
                $this->voltar($aluno->id, $aba, 'Data do documento inválida.', true);
            }
        }

        // Salva o arquivo em disco.
        $arq = $this->salvarArquivo($_FILES['arquivo'] ?? [], (int) $aluno->id, $cfg['mimes'], $cfg['max_mb']);
        if (!$arq['ok']) {
            $this->voltar($aluno->id, $aba, $arq['erro'], true);
        }

        // Registra no banco. Se falhar, apaga o arquivo para não sobrar lixo.
        $gravou = $this->anexoModel->inserirDocumento([
            'aluno_id' => (int) $aluno->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'data_documento' => $data,
            'arquivo' => $arq['arquivo'],
            'nome_original' => $arq['nome_original'],
            'mime' => $arq['mime'],
            'tamanho' => $arq['tamanho'],
            'serv_id' => (int) $_SESSION['usuario_id'],
        ]);

        if (!$gravou) {
            @unlink($arq['caminho']);
            $this->voltar($aluno->id, $aba, 'Não foi possível salvar o arquivo. Tente novamente.', true);
        }

        $this->voltar($aluno->id, $aba, $cfg['rotulo'] . ' enviado com sucesso.');
    }


    // ---------------------------------------------------------
    // EXCLUIR DOCUMENTO
    // Remove o registro e o arquivo do disco.
    // ---------------------------------------------------------
    public function excluirDocumento($docId = null)
    {
        $doc = $this->carregarDocumento($docId);
        $aba = self::TIPOS_DOCUMENTO[$doc->tipo]['aba'] ?? 'aba-saude';

        $this->exigirPost($doc->aluno_id);

        if ($this->anexoModel->excluirDocumento($doc->id)) {
            $caminho = $this->pastaAluno($doc->aluno_id) . basename((string) $doc->arquivo);
            if (is_file($caminho)) {
                @unlink($caminho);
            }
            $this->voltar($doc->aluno_id, $aba, 'Arquivo excluído com sucesso.');
        }

        $this->voltar($doc->aluno_id, $aba, 'Não foi possível excluir o arquivo.', true);
    }


    // ---------------------------------------------------------
    // ARQUIVO
    // Entrega a imagem/PDF ao navegador (somente usuário logado).
    // Usado em <img src=".../anexos/arquivo/12"> e nos links "Abrir".
    // ---------------------------------------------------------
    public function arquivo($docId = null)
    {
        $doc = $this->carregarDocumento($docId);

        $caminho = $this->pastaAluno($doc->aluno_id) . basename((string) $doc->arquivo);
        if (!is_file($caminho)) {
            http_response_code(404);
            exit('Arquivo não encontrado.');
        }

        // Limpa qualquer saída pendente para não corromper o arquivo.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $extensao = pathinfo($caminho, PATHINFO_EXTENSION);

        header('Content-Type: ' . $doc->mime);
        header('Content-Length: ' . filesize($caminho));
        header('Content-Disposition: inline; filename="documento-' . (int) $doc->id . '.' . $extensao . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');

        readfile($caminho);
        exit;
    }


    // =========================================================
    // CARTÃO DO SUS (número)
    // =========================================================

    public function salvarSus($id = null)
    {
        $aluno = $this->carregarAluno($id);
        $this->exigirPost($aluno->id);

        // Só os dígitos: aceita "123 4567 8901 2345" e guarda "123456789012345".
        $numero = preg_replace('/\D/', '', (string) ($_POST['cartao_sus'] ?? ''));

        // Vazio apaga; preenchido precisa ter 15 dígitos.
        if ($numero !== '' && strlen($numero) !== 15) {
            $this->voltar($aluno->id, 'aba-sus', 'O número do cartão do SUS deve ter 15 dígitos.', true);
        }

        if ($this->anexoModel->atualizarSus($aluno->id, $numero !== '' ? $numero : null)) {
            $this->voltar($aluno->id, 'aba-sus', 'Número do SUS salvo com sucesso.');
        }

        $this->voltar($aluno->id, 'aba-sus', 'Não foi possível salvar o número do SUS.', true);
    }


    // =========================================================
    // INFORMAÇÕES EXTRAS
    // =========================================================

    public function salvarInfoExtras($id = null)
    {
        $aluno = $this->carregarAluno($id);
        $this->exigirPost($aluno->id);

        $texto = trim((string) ($_POST['info_extras'] ?? ''));

        if (mb_strlen($texto) > self::MAX_INFO_EXTRAS) {
            $this->voltar($aluno->id, 'aba-extras', 'O texto deve ter no máximo ' . self::MAX_INFO_EXTRAS . ' caracteres.', true);
        }

        if ($this->anexoModel->atualizarInfoExtras($aluno->id, $texto !== '' ? $texto : null)) {
            $this->voltar($aluno->id, 'aba-extras', 'Informações extras salvas com sucesso.');
        }

        $this->voltar($aluno->id, 'aba-extras', 'Não foi possível salvar as informações.', true);
    }


    // =========================================================
    // ANOTAÇÕES (rápidas e psicológicas)
    // =========================================================

    public function adicionarAnotacao($id = null, $tipo = null)
    {
        $aluno = $this->carregarAluno($id);

        if (!in_array((string) $tipo, ['rapida', 'psicologica'], true)) {
            URL::redirecionar('alunos/visualizarPerfil/' . (int) $aluno->id);
            exit;
        }

        $aba = $tipo === 'psicologica' ? 'aba-psico' : 'aba-notas';

        $this->exigirPost($aluno->id);

        // Conteúdo psicológico: só quem tem permissão.
        if ($tipo === 'psicologica' && !$this->podeVerPsicologico()) {
            $this->voltar($aluno->id, $aba, 'Você não tem permissão para registrar informações psicológicas.', true);
        }

        $texto = trim((string) ($_POST['texto'] ?? ''));

        if ($texto === '') {
            $this->voltar($aluno->id, $aba, 'Escreva a anotação antes de salvar.', true);
        }
        if (mb_strlen($texto) > self::MAX_ANOTACAO) {
            $this->voltar($aluno->id, $aba, 'A anotação deve ter no máximo ' . self::MAX_ANOTACAO . ' caracteres.', true);
        }

        if ($this->anexoModel->inserirAnotacao($aluno->id, $tipo, $texto, (int) $_SESSION['usuario_id'])) {
            $this->voltar($aluno->id, $aba, 'Anotação adicionada com sucesso.');
        }

        $this->voltar($aluno->id, $aba, 'Não foi possível salvar a anotação.', true);
    }


    // Só o AUTOR da anotação pode excluí-la.
    public function excluirAnotacao($anotId = null)
    {
        if (empty($anotId) || !ctype_digit((string) $anotId)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        $nota = $this->anexoModel->buscarAnotacao((int) $anotId);
        if (!$nota) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        $aba = $nota->tipo === 'psicologica' ? 'aba-psico' : 'aba-notas';

        $this->exigirPost($nota->aluno_id);

        if ($nota->tipo === 'psicologica' && !$this->podeVerPsicologico()) {
            $this->voltar($nota->aluno_id, $aba, 'Você não tem permissão para esta ação.', true);
        }

        if ((int) $nota->serv_id !== (int) $_SESSION['usuario_id']) {
            $this->voltar($nota->aluno_id, $aba, 'Somente quem escreveu a anotação pode excluí-la.', true);
        }

        if ($this->anexoModel->excluirAnotacao($nota->id)) {
            $this->voltar($nota->aluno_id, $aba, 'Anotação excluída com sucesso.');
        }

        $this->voltar($nota->aluno_id, $aba, 'Não foi possível excluir a anotação.', true);
    }


    // =========================================================
    // MÉTODOS AUXILIARES (privados)
    // =========================================================

    // O usuário logado pode ver conteúdo psicológico?
    private function podeVerPsicologico()
    {
        return $this->anexoModel->usuarioPodeVerPsicologico($_SESSION['usuario_id']);
    }

    // Valida o id e busca o aluno; se algo estiver errado, volta à consulta.
    private function carregarAluno($id)
    {
        if (empty($id) || !ctype_digit((string) $id)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        $aluno = $this->alunoModel->buscarPorId((int) $id);
        if (!$aluno) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        return $aluno;
    }

    // Valida o id e busca o documento.
    private function carregarDocumento($docId)
    {
        if (empty($docId) || !ctype_digit((string) $docId)) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        $doc = $this->anexoModel->buscarDocumento((int) $docId);
        if (!$doc) {
            URL::redirecionar('alunos/consulta');
            exit;
        }

        return $doc;
    }

    // As ações de gravação só aceitam POST (nunca por link direto).
    private function exigirPost($alunId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            URL::redirecionar('alunos/visualizarPerfil/' . (int) $alunId);
            exit;
        }
    }

    // Mostra a mensagem e volta ao perfil, já na aba certa (#aba-xxx).
    private function voltar($alunId, $aba, $mensagem, $erro = false)
    {
        Sessao::mensagem('anexos', $mensagem, $erro ? 'alert alert-danger' : 'alert alert-success');
        URL::redirecionar('alunos/visualizarPerfil/' . (int) $alunId . '#' . $aba);
        exit;
    }

    // Pasta privada dos arquivos do aluno: storage/alunos/{id}/
    private function pastaAluno($alunId)
    {
        return dirname(__DIR__, 2) . '/storage/alunos/' . (int) $alunId . '/';
    }

    // ---------------------------------------------------------
    // SALVAR ARQUIVO
    // Confere o envio e grava em disco com nome aleatório.
    // Retorna ['ok' => true, 'arquivo' => ..., ...] ou
    //         ['ok' => false, 'erro' => 'mensagem'].
    // ---------------------------------------------------------
    private function salvarArquivo($arquivo, $alunId, array $mimesPermitidos, $maxMb)
    {
        // Envio inexistente ou malformado.
        if (!is_array($arquivo) || !isset($arquivo['error']) || is_array($arquivo['error'])) {
            return ['ok' => false, 'erro' => 'Selecione um arquivo para enviar.'];
        }

        // Erros do PHP no upload.
        switch ($arquivo['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['ok' => false, 'erro' => 'Selecione um arquivo para enviar.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['ok' => false, 'erro' => 'O arquivo é maior que o limite permitido (' . $maxMb . ' MB).'];
            default:
                return ['ok' => false, 'erro' => 'Falha no envio do arquivo. Tente novamente.'];
        }

        if (!is_uploaded_file($arquivo['tmp_name'])) {
            return ['ok' => false, 'erro' => 'Envio inválido.'];
        }

        if ($arquivo['size'] > $maxMb * 1024 * 1024) {
            return ['ok' => false, 'erro' => 'O arquivo deve ter no máximo ' . $maxMb . ' MB.'];
        }

        // Descobre o tipo REAL pelo conteúdo (não confia na extensão).
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($arquivo['tmp_name']);

        if (!isset($mimesPermitidos[$mime])) {
            $aceitos = in_array('pdf', $mimesPermitidos, true) ? 'JPG, PNG, WEBP ou PDF' : 'JPG, PNG ou WEBP';
            return ['ok' => false, 'erro' => 'Formato não permitido. Envie um arquivo ' . $aceitos . '.'];
        }

        // Cria a pasta do aluno (e bloqueia acesso direto via web).
        $pasta = $this->pastaAluno($alunId);
        if (!is_dir($pasta)) {
            if (!mkdir($pasta, 0750, true) && !is_dir($pasta)) {
                return ['ok' => false, 'erro' => 'Não foi possível preparar a pasta de arquivos.'];
            }
        }
        $bloqueio = dirname(__DIR__, 2) . '/storage/.htaccess';
        if (!is_file($bloqueio)) {
            @file_put_contents($bloqueio, "Require all denied\n");
        }

        // Nome aleatório + extensão segura.
        $nome = bin2hex(random_bytes(16)) . '.' . $mimesPermitidos[$mime];
        $caminho = $pasta . $nome;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminho)) {
            return ['ok' => false, 'erro' => 'Não foi possível salvar o arquivo.'];
        }

        return [
            'ok' => true,
            'arquivo' => $nome,
            'caminho' => $caminho,
            'mime' => $mime,
            'tamanho' => (int) $arquivo['size'],
            // Nome original só para registro (limitado a 150 caracteres).
            'nome_original' => mb_substr(basename((string) $arquivo['name']), 0, 150),
        ];
    }
}