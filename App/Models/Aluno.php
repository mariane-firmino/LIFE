<?php

class Aluno
{
    // =========================================================
    // CONEXÃO COM O BANCO DE DADOS
    // Objeto usado para executar as consultas (queries).
    // =========================================================

    private $db;


    // =========================================================
    // CONSTRUTOR
    // Cria a conexão com o banco de dados.
    // =========================================================

    public function __construct()
    {
        $this->db = new Database();
    }


    // =========================================================
    // CADASTRO
    // =========================================================

    // ---------------------------------------------------------
    // CHECAR MATRÍCULA
    // Verifica se a matrícula já está cadastrada no banco.
    // Retorna true se já existir, false se estiver livre.
    // ---------------------------------------------------------

    public function checarMatricula($matricula)
    {
        $this->db->query("
            SELECT alun_id
            FROM aluno
            WHERE alun_matricula = :m
        ");

        $this->db->bind(":m", $matricula);

        return $this->db->resultado() ? true : false;
    }


    // ---------------------------------------------------------
    // ARMAZENAR (CADASTRAR ALUNO)
    // Cadastra o aluno no banco de dados. Tudo é feito dentro
    // de uma transação: se algo falhar, nada é salvo.
    //
    // Etapas:
    // 1. Busca ou cria a turma.
    // 2. Cria o responsável.
    // 3. Insere o aluno.
    // 4. Salva os telefones (aluno e responsável).
    // 5. Salva os dados de saúde e hábitos.
    // ---------------------------------------------------------

    public function armazenar($dados)
    {
        try {
            $this->db->beginTransaction();

            // Turma do aluno (usa a existente ou cria uma nova).
            $turmId = $this->buscarOuCriarTurma(
                $dados['curso'],
                $dados['turno'],
                $dados['ano_turma']
            );

            // Responsável do aluno.
            $respId = $this->criarResponsavel(
                $dados['nome_responsavel']
            );

            // Insere o aluno.
            $this->db->query("
                INSERT INTO aluno(
                    alun_nome,
                    alun_dt_nascimento,
                    alun_sexo,
                    alun_matricula,
                    alun_foto,
                    alun_tipo_sanguineo,
                    alun_altura,
                    alun_peso,
                    resp_id,
                    turm_id
                )
                VALUES (
                    :nome,
                    :nascimento,
                    :sexo,
                    :matricula,
                    :foto,
                    :tipo_sanguineo,
                    :altura,
                    :peso,
                    :resp_id,
                    :turm_id
                )
            ");

            $this->db->bind(':nome', $dados['nome']);
            $this->db->bind(':nascimento', $dados['data_nascimento']);
            $this->db->bind(':sexo', $dados['sexo']);
            $this->db->bind(':matricula', $dados['matricula']);
            $this->db->bind(':foto', ($dados['foto'] ?? '') !== '' ? $dados['foto'] : null);
            $this->db->bind(':tipo_sanguineo', ($dados['tipo_sanguineo'] ?? '') !== '' ? $dados['tipo_sanguineo'] : null);
            $this->db->bind(':altura', $this->decimalOuNulo($dados['altura'] ?? ''));
            $this->db->bind(':peso', $this->decimalOuNulo($dados['peso'] ?? ''));
            $this->db->bind(':resp_id', $respId);
            $this->db->bind(':turm_id', $turmId);

            // Se o insert falhar, desfaz tudo.
            if (!$this->db->executa()) {
                $this->db->rollBack();
                return false;
            }

            // ID do aluno recém-cadastrado.
            $alunId = $this->db->ultimoIdInserido();

            // Telefone do aluno (se informado).
            if (!empty($dados['telefone_aluno'])) {
                $this->salvarTelefone(
                    $dados['telefone_aluno'],
                    $alunId,
                    null
                );
            }

            // Telefone do responsável (se informado).
            if (!empty($dados['telefone_responsavel'])) {
                $this->salvarTelefone(
                    $dados['telefone_responsavel'],
                    null,
                    $respId
                );
            }

            // Dados de saúde e hábitos.
            $this->salvarSaude($alunId, $dados);

            // Confirma todas as operações.
            $this->db->commit();

            return true;
        } catch (PDOException $e) {

            // Em caso de erro, desfaz tudo.
            $this->db->rollBack();

            return false;
        }
    }


    // ---------------------------------------------------------
    // BUSCAR OU CRIAR TURMA
    // Procura uma turma pelo curso/turno/ano. Se não existir,
    // cria uma nova. Retorna o turm_id.
    // ---------------------------------------------------------

    private function buscarOuCriarTurma($curso, $turno, $ano)
    {
        $this->db->query("
            SELECT turm_id
            FROM turma
            WHERE turm_curso = :curso
              AND turm_turno = :turno
              AND turm_ano = :ano
        ");

        $this->db->bind(':curso', $curso);
        $this->db->bind(':turno', $turno);
        $this->db->bind(':ano', $ano);

        $turma = $this->db->resultado();

        // Turma já existe: devolve o ID dela.
        if ($turma) {
            return $turma->turm_id;
        }

        // Turma não existe: cria uma nova.
        $this->db->query("
            INSERT INTO turma(
                turm_curso,
                turm_turno,
                turm_ano
            )
            VALUES (
                :curso,
                :turno,
                :ano
            )
        ");

        $this->db->bind(':curso', $curso);
        $this->db->bind(':turno', $turno);
        $this->db->bind(':ano', $ano);

        $this->db->executa();

        return $this->db->ultimoIdInserido();
    }


    // ---------------------------------------------------------
    // CRIAR RESPONSÁVEL
    // Cadastra o responsável e retorna o resp_id.
    // ---------------------------------------------------------

    private function criarResponsavel($nome)
    {
        $this->db->query("
            INSERT INTO responsavel(resp_nome)
            VALUES (:nome)
        ");

        $this->db->bind(':nome', $nome);

        $this->db->executa();

        return $this->db->ultimoIdInserido();
    }


    // ---------------------------------------------------------
    // SALVAR TELEFONE
    // Cadastra um telefone ligado a um aluno OU a um
    // responsável (o outro ID deve ser null).
    // ---------------------------------------------------------

    private function salvarTelefone($numero, $alunId, $respId)
    {
        $this->db->query("
            INSERT INTO telefone(
                tele_numero,
                serv_id,
                alun_id,
                resp_id
            )
            VALUES (
                :numero,
                NULL,
                :alun_id,
                :resp_id
            )
        ");

        $this->db->bind(':numero', $numero);
        $this->db->bind(':alun_id', $alunId);
        $this->db->bind(':resp_id', $respId);

        $this->db->executa();
    }


    // ---------------------------------------------------------
    // SALVAR SAÚDE
    // Cadastra as respostas de saúde e hábitos do aluno.
    // Campos vazios são salvos como null.
    // ---------------------------------------------------------

    private function salvarSaude($alunId, $d)
    {
        // Chave = nome usado pelo formulário; valor = coluna real no bd_life.
        $campos = [
            'tipo_escola' => 'anam_tipo_escola',
            'motivo_ifro' => 'anam_motivo_ifro',
            'ja_reprovou' => 'anam_ja_reprovou',
            'quantas_vezes' => 'anam_quantas_vezes',
            'estuda_fora' => 'anam_estuda_fora',
            'quanto_tempo' => 'anam_quanto_tempo',
            'nota_curso' => 'anam_nota_curso',
            'nota_motivacao' => 'anam_nota_motivacao',
            'atendimento' => 'anam_atendimento',
            'atendimento_porque' => 'anam_atendimento_porque',
            'acesso_saude' => 'anam_acesso_saude',
            'alergia_alimentar' => 'anam_alergia_alimentar',
            'alergia_qual' => 'anam_alergia_qual',
            'doenca_cronica' => 'anam_doenca_cronica',
            'doenca_qual' => 'anam_doenca_qual',
            'medicacao_continua' => 'anam_medicacao_continua',
            'medicacao_qual' => 'anam_medicacao_qual',
            'acompanhamento_psi' => 'anam_acompanhamento_psi',
            'cartao_vacina' => 'anam_cartao_vacina',
            'ja_internado' => 'anam_ja_internado',
            'internado_motivo' => 'anam_internado_motivo',
            'ja_cirurgia' => 'anam_ja_cirurgia',
            'cirurgia_qual' => 'anam_cirurgia_qual',
            'padrao_sono' => 'anam_padrao_sono',
            'refeicoes_dia' => 'anam_refeicoes_dia',
            'alimentacao_habitual' => 'anam_alimentacao_habitual',
            'fuma' => 'anam_fuma',
            'bebida_alcoolica' => 'anam_bebida_alcoolica',
            'pais_sabem' => 'anam_pais_sabem',
            'drogas_ilicitas' => 'anam_drogas_ilicitas',
            'drogas_qual' => 'anam_drogas_qual',
            'atividade_fisica' => 'anam_atividade_fisica',
            'frequencia_atividade' => 'anam_frequencia_atividade',
            'banhos_dia' => 'anam_banhos_dia',
            // NOVO: condições especiais (autismo, paralisia, etc.)
            'condicoes' => 'anam_condicoes',
            'condicao_outra' => 'anam_condicao_outra',
        ];

        $colunas = implode(', ', array_values($campos));
        $placeholders = ':' . implode(', :', array_keys($campos));

        $this->db->query("
            INSERT INTO anamnese (alun_id, {$colunas})
            VALUES (:alun_id, {$placeholders})
        ");

        $this->db->bind(':alun_id', (int) $alunId);

        foreach ($campos as $campoFormulario => $colunaBanco) {
            $valor = $d[$campoFormulario] ?? '';
            $this->db->bind(
                ':' . $campoFormulario,
                $valor !== '' ? $valor : null
            );
        }

        $this->db->executa();
    }


    // =========================================================
    // CONSULTAS
    // =========================================================

    // ---------------------------------------------------------
    // LISTAR TODOS OS ALUNOS
    // Retorna todos os alunos com turma e responsável,
    // em ordem alfabética pelo nome.
    // ---------------------------------------------------------

    public function listarTodos()
    {
        $this->db->query("
            SELECT
                a.alun_id AS id,
                a.alun_nome AS nome,
                a.alun_dt_nascimento AS data_nascimento,
                a.alun_sexo AS sexo,
                a.alun_matricula AS matricula,
                a.alun_foto AS foto,
                a.alun_data_cadastro AS data_cadastro,

                t.turm_id AS turma_id,
                t.turm_curso AS curso,
                t.turm_turno AS turno,
                t.turm_ano AS ano_turma,

                r.resp_id AS responsavel_id,
                r.resp_nome AS responsavel

            FROM aluno a

            LEFT JOIN turma t
                ON a.turm_id = t.turm_id

            LEFT JOIN responsavel r
                ON a.resp_id = r.resp_id

            ORDER BY a.alun_nome ASC
        ");

        return $this->db->resultados();
    }


    // ---------------------------------------------------------
    // BUSCAR UM ALUNO PELO ID
    // Retorna os dados completos do aluno, com turma e
    // responsável.
    // ---------------------------------------------------------

    public function buscarPorId($id)
    {
        $this->db->query("
            SELECT
                a.alun_id AS id,
                a.alun_nome AS nome,
                a.alun_dt_nascimento AS data_nascimento,
                a.alun_sexo AS sexo,
                a.alun_matricula AS matricula,
                a.alun_rg AS rg,
                a.alun_foto AS foto,
                a.alun_tipo_sanguineo AS tipo_sanguineo,
                a.alun_altura AS altura,
                a.alun_peso AS peso,
                a.alun_data_cadastro AS data_cadastro,
                a.alun_atualizado_em AS atualizado_em,

                t.turm_id AS turma_id,
                t.turm_curso AS curso,
                t.turm_turno AS turno,
                t.turm_ano AS ano_turma,

                r.resp_id AS responsavel_id,
                r.resp_nome AS responsavel

            FROM aluno a

            LEFT JOIN turma t
                ON a.turm_id = t.turm_id

            LEFT JOIN responsavel r
                ON a.resp_id = r.resp_id

            WHERE a.alun_id = :id

            LIMIT 1
        ");

        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }


    // =========================================================
    // PERFIL COMPLETO (VISUALIZAR / EDITAR)
    // =========================================================

    // ---------------------------------------------------------
    // BUSCAR SAÚDE
    // Dados de saúde do aluno (ou null se ainda não houver
    // registro).
    // ---------------------------------------------------------

    public function buscarSaude($alunId)
    {
        // Usa aliases para que as Views continuem acessando as propriedades
        // com os nomes antigos do formulário, embora o banco use anam_*.
        $this->db->query("
            SELECT
                anamn_id,
                alun_id,
                anam_tipo_escola AS tipo_escola,
                anam_motivo_ifro AS motivo_ifro,
                anam_ja_reprovou AS ja_reprovou,
                anam_quantas_vezes AS quantas_vezes,
                anam_estuda_fora AS estuda_fora,
                anam_quanto_tempo AS quanto_tempo,
                anam_nota_curso AS nota_curso,
                anam_nota_motivacao AS nota_motivacao,
                anam_atendimento AS atendimento,
                anam_atendimento_porque AS atendimento_porque,
                anam_acesso_saude AS acesso_saude,
                anam_alergia_alimentar AS alergia_alimentar,
                anam_alergia_qual AS alergia_qual,
                anam_doenca_cronica AS doenca_cronica,
                anam_doenca_qual AS doenca_qual,
                anam_medicacao_continua AS medicacao_continua,
                anam_medicacao_qual AS medicacao_qual,
                anam_acompanhamento_psi AS acompanhamento_psi,
                anam_cartao_vacina AS cartao_vacina,
                anam_ja_internado AS ja_internado,
                anam_internado_motivo AS internado_motivo,
                anam_ja_cirurgia AS ja_cirurgia,
                anam_cirurgia_qual AS cirurgia_qual,
                anam_padrao_sono AS padrao_sono,
                anam_refeicoes_dia AS refeicoes_dia,
                anam_alimentacao_habitual AS alimentacao_habitual,
                anam_fuma AS fuma,
                anam_bebida_alcoolica AS bebida_alcoolica,
                anam_pais_sabem AS pais_sabem,
                anam_drogas_ilicitas AS drogas_ilicitas,
                anam_drogas_qual AS drogas_qual,
                anam_atividade_fisica AS atividade_fisica,
                anam_frequencia_atividade AS frequencia_atividade,
                anam_banhos_dia AS banhos_dia,
                anam_condicoes AS condicoes,
                anam_condicao_outra AS condicao_outra
            FROM anamnese
            WHERE alun_id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $alunId);

        $saude = $this->db->resultado();

        return $saude ? $saude : null;
    }


    // ---------------------------------------------------------
    // BUSCAR TELEFONE DO ALUNO
    // Retorna o número ou string vazia se não houver.
    // ---------------------------------------------------------

    public function buscarTelefoneAluno($alunId)
    {
        $this->db->query("SELECT tele_numero FROM telefone WHERE alun_id = :id LIMIT 1");
        $this->db->bind(':id', (int) $alunId);

        $t = $this->db->resultado();

        return $t ? $t->tele_numero : '';
    }


    // ---------------------------------------------------------
    // BUSCAR TELEFONE DO RESPONSÁVEL
    // Retorna o número ou string vazia se não houver.
    // ---------------------------------------------------------

    public function buscarTelefoneResponsavel($respId)
    {
        $this->db->query("SELECT tele_numero FROM telefone WHERE resp_id = :id LIMIT 1");
        $this->db->bind(':id', (int) $respId);

        $t = $this->db->resultado();

        return $t ? $t->tele_numero : '';
    }


    // ---------------------------------------------------------
    // BUSCAR HISTÓRICO
    // Histórico de atendimentos (triagens) do aluno, com os
    // 10 mais recentes.
    // Cada triagem salva grava uma linha por pergunta; aqui
    // elas voltam agrupadas em uma só.
    // ---------------------------------------------------------

    public function buscarHistorico($alunId)
    {
        $this->db->query("
            SELECT
                MIN(tr.tria_id) AS tria_id,
                COALESCE(c.cons_data, DATE(tr.tria_data)) AS cons_data,
                COALESCE(c.cons_horario, TIME(tr.tria_data)) AS cons_horario,
                COALESCE(c.cons_tipo, 'Triagem') AS cons_tipo,
                c.cons_descricao,
                c.cons_observacao,
                COUNT(tr.tria_id) AS total_respostas,
                s.serv_nome,
                f.func_nome
            FROM triagem tr
            LEFT JOIN consulta c ON tr.cons_id = c.cons_id
            LEFT JOIN servidor s ON tr.serv_id = s.serv_id
            LEFT JOIN funcao f ON s.func_id = f.func_id
            WHERE tr.alun_id = :id
            GROUP BY tr.alun_id, tr.serv_id, tr.cons_id, tr.tria_data,
                     c.cons_data, c.cons_horario, c.cons_tipo, c.cons_descricao, c.cons_observacao,
                     s.serv_nome, f.func_nome
            ORDER BY COALESCE(c.cons_data, DATE(tr.tria_data)) DESC,
                     COALESCE(c.cons_horario, TIME(tr.tria_data)) DESC,
                     MIN(tr.tria_id) DESC
            LIMIT 10
        ");

        $this->db->bind(':id', (int) $alunId);

        return $this->db->resultados();
    }


    // =========================================================
    // EDIÇÃO
    // =========================================================

    // ---------------------------------------------------------
    // CHECAR MATRÍCULA DE OUTRO ALUNO
    // Verifica se a matrícula pertence a OUTRO aluno
    // (ignora o próprio aluno que está sendo editado).
    // ---------------------------------------------------------

    public function checarMatriculaOutroAluno($matricula, $alunId)
    {
        $this->db->query("
            SELECT alun_id FROM aluno
            WHERE alun_matricula = :m AND alun_id <> :id
            LIMIT 1
        ");

        $this->db->bind(':m', $matricula);
        $this->db->bind(':id', (int) $alunId);

        return $this->db->resultado() ? true : false;
    }


    // ---------------------------------------------------------
    // CHECAR RG DE OUTRO ALUNO
    // Verifica se o RG pertence a OUTRO aluno
    // (ignora o próprio aluno que está sendo editado).
    // ---------------------------------------------------------

    public function checarRgOutroAluno($rg, $alunId)
    {
        $this->db->query("
            SELECT alun_id FROM aluno
            WHERE alun_rg = :rg AND alun_id <> :id
            LIMIT 1
        ");

        $this->db->bind(':rg', $rg);
        $this->db->bind(':id', (int) $alunId);

        return $this->db->resultado() ? true : false;
    }


    // ---------------------------------------------------------
    // ATUALIZAR
    // Atualiza aluno, responsável, telefones e dados de saúde.
    // Também é feito dentro de uma transação: se algo falhar,
    // nenhuma alteração é salva.
    // ---------------------------------------------------------

    public function atualizar($alunId, $dados)
    {
        try {
            $this->db->beginTransaction();

            // Busca o aluno atual (precisamos do ID do responsável).
            $aluno = $this->buscarPorId($alunId);

            if (!$aluno) {
                $this->db->rollBack();
                return false;
            }

            // Turma (usa a existente ou cria uma nova).
            $turmId = $this->buscarOuCriarTurma(
                $dados['curso'],
                $dados['turno'],
                $dados['ano_turma']
            );

            // Atualiza o responsável.
            $this->db->query("
                UPDATE responsavel
                SET resp_nome = :nome
                WHERE resp_id = :id
            ");
            $this->db->bind(':nome', $dados['nome_responsavel']);
            $this->db->bind(':id', (int) $aluno->responsavel_id);
            $this->db->executa();

            // Atualiza o aluno.
            $this->db->query("
                UPDATE aluno SET
                    alun_nome = :nome,
                    alun_dt_nascimento = :nascimento,
                    alun_sexo = :sexo,
                    alun_matricula = :matricula,
                    alun_rg = :rg,
                    alun_tipo_sanguineo = :tipo_sanguineo,
                    alun_altura = :altura,
                    alun_peso = :peso,
                    turm_id = :turm_id
                WHERE alun_id = :id
            ");
            $this->db->bind(':nome', $dados['nome']);
            $this->db->bind(':nascimento', $dados['data_nascimento']);
            $this->db->bind(':sexo', $dados['sexo']);
            $this->db->bind(':matricula', $dados['matricula']);
            $this->db->bind(':rg', $dados['rg'] !== '' ? $dados['rg'] : null);
            $this->db->bind(':tipo_sanguineo', ($dados['tipo_sanguineo'] ?? '') !== '' ? $dados['tipo_sanguineo'] : null);
            $this->db->bind(':altura', $this->decimalOuNulo($dados['altura'] ?? ''));
            $this->db->bind(':peso', $this->decimalOuNulo($dados['peso'] ?? ''));
            $this->db->bind(':turm_id', (int) $turmId);
            $this->db->bind(':id', (int) $alunId);
            $this->db->executa();

            // Telefones (insere, atualiza ou remove).
            $this->atualizarTelefone($dados['telefone_aluno'], (int) $alunId, null);
            $this->atualizarTelefone($dados['telefone_responsavel'], null, (int) $aluno->responsavel_id);

            // Saúde (atualiza se existir, senão cria).
            if ($this->buscarSaude($alunId)) {
                $this->atualizarSaude($alunId, $dados);
            } else {
                $this->salvarSaude($alunId, $dados);
            }

            // Confirma todas as operações.
            $this->db->commit();

            return true;
        } catch (PDOException $e) {

            // Em caso de erro, desfaz tudo.
            $this->db->rollBack();

            return false;
        }
    }


    // ---------------------------------------------------------
    // ATUALIZAR TELEFONE
    // Insere, atualiza ou remove o telefone conforme o valor
    // informado:
    // - número vazio  -> remove o telefone existente;
    // - já existe     -> atualiza o número;
    // - não existe    -> cadastra um novo.
    // ---------------------------------------------------------

    private function atualizarTelefone($numero, $alunId, $respId)
    {
        // Define se o telefone pertence ao aluno ou ao responsável.
        $coluna = $alunId !== null ? 'alun_id' : 'resp_id';
        $ownerId = $alunId !== null ? $alunId : $respId;

        $this->db->query("SELECT tele_id FROM telefone WHERE {$coluna} = :owner LIMIT 1");
        $this->db->bind(':owner', $ownerId);
        $existente = $this->db->resultado();

        // Número vazio: remove o telefone, se existir.
        if ($numero === '') {
            if ($existente) {
                $this->db->query("DELETE FROM telefone WHERE tele_id = :id");
                $this->db->bind(':id', (int) $existente->tele_id);
                $this->db->executa();
            }
            return;
        }

        if ($existente) {

            // Já existe: atualiza o número.
            $this->db->query("UPDATE telefone SET tele_numero = :n WHERE tele_id = :id");
            $this->db->bind(':n', $numero);
            $this->db->bind(':id', (int) $existente->tele_id);
            $this->db->executa();
        } else {

            // Não existe: cadastra um novo.
            $this->salvarTelefone($numero, $alunId, $respId);
        }
    }


    // ---------------------------------------------------------
    // ATUALIZAR SAÚDE
    // Atualiza os dados de saúde e hábitos do aluno.
    // Campos vazios são salvos como null.
    // ---------------------------------------------------------

    private function atualizarSaude($alunId, $d)
    {
        // Mantém os nomes dos campos do formulário e atualiza as colunas anam_*.
        $campos = [
            'tipo_escola' => 'anam_tipo_escola',
            'motivo_ifro' => 'anam_motivo_ifro',
            'ja_reprovou' => 'anam_ja_reprovou',
            'quantas_vezes' => 'anam_quantas_vezes',
            'estuda_fora' => 'anam_estuda_fora',
            'quanto_tempo' => 'anam_quanto_tempo',
            'nota_curso' => 'anam_nota_curso',
            'nota_motivacao' => 'anam_nota_motivacao',
            'atendimento' => 'anam_atendimento',
            'atendimento_porque' => 'anam_atendimento_porque',
            'acesso_saude' => 'anam_acesso_saude',
            'alergia_alimentar' => 'anam_alergia_alimentar',
            'alergia_qual' => 'anam_alergia_qual',
            'doenca_cronica' => 'anam_doenca_cronica',
            'doenca_qual' => 'anam_doenca_qual',
            'medicacao_continua' => 'anam_medicacao_continua',
            'medicacao_qual' => 'anam_medicacao_qual',
            'acompanhamento_psi' => 'anam_acompanhamento_psi',
            'cartao_vacina' => 'anam_cartao_vacina',
            'ja_internado' => 'anam_ja_internado',
            'internado_motivo' => 'anam_internado_motivo',
            'ja_cirurgia' => 'anam_ja_cirurgia',
            'cirurgia_qual' => 'anam_cirurgia_qual',
            'padrao_sono' => 'anam_padrao_sono',
            'refeicoes_dia' => 'anam_refeicoes_dia',
            'alimentacao_habitual' => 'anam_alimentacao_habitual',
            'fuma' => 'anam_fuma',
            'bebida_alcoolica' => 'anam_bebida_alcoolica',
            'pais_sabem' => 'anam_pais_sabem',
            'drogas_ilicitas' => 'anam_drogas_ilicitas',
            'drogas_qual' => 'anam_drogas_qual',
            'atividade_fisica' => 'anam_atividade_fisica',
            'frequencia_atividade' => 'anam_frequencia_atividade',
            'banhos_dia' => 'anam_banhos_dia',
            // NOVO: condições especiais (autismo, paralisia, etc.)
            'condicoes' => 'anam_condicoes',
            'condicao_outra' => 'anam_condicao_outra',
        ];

        $sets = [];
        foreach ($campos as $campoFormulario => $colunaBanco) {
            $sets[] = "{$colunaBanco} = :{$campoFormulario}";
        }

        $this->db->query("
            UPDATE anamnese
            SET " . implode(', ', $sets) . "
            WHERE alun_id = :alun_id
        ");

        $this->db->bind(':alun_id', (int) $alunId);

        foreach ($campos as $campoFormulario => $colunaBanco) {
            $valor = $d[$campoFormulario] ?? '';
            $this->db->bind(
                ':' . $campoFormulario,
                $valor !== '' ? $valor : null
            );
        }

        $this->db->executa();
    }


    // ---------------------------------------------------------
    // ATUALIZAR FOTO
    // Atualiza apenas a foto do aluno (guarda o nome do
    // arquivo).
    // ---------------------------------------------------------

    public function atualizarFoto($alunId, $foto)
    {
        $this->db->query("UPDATE aluno SET alun_foto = :foto WHERE alun_id = :id");
        $this->db->bind(':foto', $foto);
        $this->db->bind(':id', (int) $alunId);

        return $this->db->executa();
    }


    // =========================================================
    // AUXILIARES
    // =========================================================

    // ---------------------------------------------------------
    // DECIMAL OU NULO
    // Converte "1,70" / "65,5" em "1.70" / "65.5"
    // (ou null quando o valor estiver vazio).
    // ---------------------------------------------------------

    private function decimalOuNulo($valor)
    {
        $valor = str_replace(',', '.', trim((string) $valor));

        return $valor === '' ? null : $valor;
    }
}

// FIM DA CLASSE ALUNO