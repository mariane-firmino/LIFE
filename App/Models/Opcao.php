<?php
// =====================================================================
// MODEL: Opcao   ->   App/Models/Opcao.php
//
// Cuida das LISTAS que o administrador controla (anos das turmas,
// cursos, turnos...). É usado em dois lugares:
//  - página "Informações do sistema" (admin): criar, editar, ativar,
//    desativar e excluir opções;
//  - formulários de aluno: só mostram as opções ATIVAS.
// =====================================================================

class Opcao
{
    // ---------------------------------------------------------
    // LISTAS DISPONÍVEIS
    // A chave é o "grupo" gravado no banco (opca_grupo).
    // Para criar uma lista nova, acrescente um item aqui.
    //
    //  rotulo / singular / ajuda : textos da tela
    //  max      : tamanho máximo do valor
    //  padrao   : regra extra de formato (ou null)
    //  tabela / coluna : onde esse valor está gravado nos dados, quando
    //     o formulário já persiste o valor em uma tabela de histórico.
    //     Ao RENOMEAR uma opção, os registros antigos são
    //     atualizados junto, para nada ficar inconsistente.
    //  uso_from / uso_coluna : como contar quantos alunos usam
    //     cada opção (só pode excluir quem não está em uso).
    //  unidade  : o que é contado no "Em uso" [singular, plural]
    //     (opcional; o padrão é ['aluno', 'alunos']).
    //
    // IMPORTANTE: tabela/coluna/uso_* são fixos no código (nunca vêm
    // do usuário), por isso podem ser usados direto no SQL.
    // ---------------------------------------------------------
    public const GRUPOS = [
        'ano_turma' => [
            'rotulo' => 'Anos das turmas',
            'singular' => 'Ano da turma',
            // As turmas são guardadas pelo ano de entrada com 2 dígitos (24, 25, 26),
            // igual aos dados do banco; a regra antiga ("1º, 2º") recusava esses valores.
            'ajuda' => 'Use o ano da turma com 2 dígitos. Ex.: 24, 25, 26',
            'icone' => 'bi-calendar-event-fill',
            'max' => 2,
            'padrao' => '/^[0-9]{2}$/',
            'tabela' => 'turma',
            'coluna' => 'turm_ano',
            'uso_from' => 'aluno a JOIN turma t ON a.turm_id = t.turm_id',
            'uso_coluna' => 't.turm_ano',
        ],
        'curso' => [
            'rotulo' => 'Cursos',
            'singular' => 'Curso',
            'ajuda' => 'Nome completo do curso.',
            'icone' => 'bi-mortarboard-fill',
            'max' => 50,
            'padrao' => null,
            'tabela' => 'turma',
            'coluna' => 'turm_curso',
            'uso_from' => 'aluno a JOIN turma t ON a.turm_id = t.turm_id',
            'uso_coluna' => 't.turm_curso',
        ],
        'turno' => [
            'rotulo' => 'Turnos',
            'singular' => 'Turno',
            'ajuda' => 'Ex.: Matutino, Integral.',
            'icone' => 'bi-sun-fill',
            'max' => 25,
            'padrao' => null,
            'tabela' => 'turma',
            'coluna' => 'turm_turno',
            'uso_from' => 'aluno a JOIN turma t ON a.turm_id = t.turm_id',
            'uso_coluna' => 't.turm_turno',
        ],
        'tipo_escola' => [
            'rotulo' => 'Tipos de escola',
            'singular' => 'Tipo de escola',
            'ajuda' => 'Escola em que o aluno estudou antes do IFRO.',
            'icone' => 'bi-building-fill',
            'max' => 50,
            'padrao' => null,
            'tabela' => 'anamnese',
            'coluna' => 'anam_tipo_escola',
            'uso_from' => 'anamnese',
            'uso_coluna' => 'anam_tipo_escola',
        ],
        'motivo_ifro' => [
            'rotulo' => 'Motivos de escolha do IFRO',
            'singular' => 'Motivo',
            'ajuda' => 'Por que o aluno escolheu o IFRO.',
            'icone' => 'bi-question-circle-fill',
            'max' => 100,
            'padrao' => null,
            'tabela' => 'anamnese',
            'coluna' => 'anam_motivo_ifro',
            'uso_from' => 'anamnese',
            'uso_coluna' => 'anam_motivo_ifro',
        ],
        'acesso_saude' => [
            'rotulo' => 'Acesso à saúde',
            'singular' => 'Forma de acesso',
            'ajuda' => 'Ex.: SUS, Privado, Convênio.',
            'icone' => 'bi-hospital-fill',
            'max' => 20,
            'padrao' => null,
            'tabela' => 'anamnese',
            'coluna' => 'anam_acesso_saude',
            'uso_from' => 'anamnese',
            'uso_coluna' => 'anam_acesso_saude',
        ],
        'queixa' => [
            'rotulo' => 'Queixas da triagem rápida',
            'singular' => 'Queixa',
            'ajuda' => 'Ex.: Dor de cabeça, Cólica, Febre.',
            'icone' => 'bi-bandaid-fill',
            'max' => 100,
            'padrao' => null,
            'tabela' => 'triagem_rapida',
            'coluna' => 'trap_queixa',
            'uso_from' => 'triagem_rapida',
            'uso_coluna' => 'trap_queixa',
            'unidade' => ['triagem', 'triagens'],
        ],
        'motivo_triagem_psicologica' => [
            'rotulo' => 'Motivos da triagem psicológica',
            'singular' => 'Motivo',
            'ajuda' => 'Motivos disponíveis no formulário da triagem psicológica.',
            'icone' => 'bi-chat-heart-fill',
            'max' => 100,
            'padrao' => null,
            // Renomear atualiza as triagens já gravadas; em uso, não pode ser excluído.
            'tabela' => 'triagem_psicologica',
            'coluna' => 'trps_motivo',
            'uso_from' => 'triagem_psicologica',
            'uso_coluna' => 'trps_motivo',
            'unidade' => ['triagem', 'triagens'],
        ],
        'sintoma_triagem_psicologica' => [
            'rotulo' => 'Sintomas da triagem psicológica',
            'singular' => 'Sintoma',
            'ajuda' => 'Sintomas disponíveis para marcar no formulário da triagem psicológica.',
            'icone' => 'bi-heart-pulse-fill',
            'max' => 100,
            'padrao' => null,
            // Os sintomas ficam numa lista (JSON) em cada triagem: o renomear
            // troca o nome dentro dessas listas (ver renomearEmListaJson).
            'tabela_json' => 'triagem_psicologica',
            'coluna_json' => 'trps_sintomas',
            'chave_json' => 'trps_id',
        ],
    ];

    // Conexão com o banco.
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }


    // =========================================================
    // CONSULTAS
    // =========================================================

    // ---------------------------------------------------------
    // LISTAR
    // Todas as opções de um grupo (ativas e inativas), na ordem.
    // Usado na página do administrador.
    // ---------------------------------------------------------
    public function listar($grupo)
    {
        $this->db->query("
            SELECT opca_id AS id, opca_valor AS valor, opca_ativo AS ativo, opca_ordem AS ordem
            FROM opcao_sistema
            WHERE opca_grupo = :g
            ORDER BY opca_ordem ASC, opca_valor ASC
        ");
        $this->db->bind(':g', $grupo);

        return $this->db->resultados();
    }

    // ---------------------------------------------------------
    // VALORES ATIVOS
    // Só os textos das opções ATIVAS, em lista simples.
    // Usado para montar os <select> dos formulários.
    // ---------------------------------------------------------
    public function valoresAtivos($grupo)
    {
        $this->db->query("
            SELECT opca_valor AS valor
            FROM opcao_sistema
            WHERE opca_grupo = :g AND opca_ativo = 1
            ORDER BY opca_ordem ASC, opca_valor ASC
        ");
        $this->db->bind(':g', $grupo);

        $lista = [];
        foreach ($this->db->resultados() as $linha) {
            $lista[] = $linha->valor;
        }

        return $lista;
    }

    // ---------------------------------------------------------
    // TODAS AS LISTAS ATIVAS
    // Devolve ['curso' => [...], 'turno' => [...], ...] de uma vez.
    // ---------------------------------------------------------
    public function todasAtivas()
    {
        $listas = [];
        foreach (array_keys(self::GRUPOS) as $grupo) {
            $listas[$grupo] = $this->valoresAtivos($grupo);
        }

        return $listas;
    }

    // Uma opção pelo id.
    public function buscar($id)
    {
        $this->db->query("
            SELECT opca_id AS id, opca_grupo AS grupo, opca_valor AS valor, opca_ativo AS ativo
            FROM opcao_sistema
            WHERE opca_id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $id);

        return $this->db->resultado();
    }

    // ---------------------------------------------------------
    // EXISTE
    // O valor já está na lista? ($ignorarId permite editar uma
    // opção sem ela "colidir" consigo mesma.)
    // ---------------------------------------------------------
    public function existe($grupo, $valor, $ignorarId = 0)
    {
        $this->db->query("
            SELECT opca_id
            FROM opcao_sistema
            WHERE opca_grupo = :g AND opca_valor = :v AND opca_id <> :id
            LIMIT 1
        ");
        $this->db->bind(':g', $grupo);
        $this->db->bind(':v', $valor);
        $this->db->bind(':id', (int) $ignorarId);

        return $this->db->resultado() ? true : false;
    }

    // ---------------------------------------------------------
    // USO POR VALOR
    // Quantos alunos usam cada opção do grupo.
    // Retorna ['Matutino' => 12, 'Noturno' => 3, ...].
    // ---------------------------------------------------------
    public function usoPorValor($grupo)
    {
        $cfg = self::GRUPOS[$grupo] ?? null;
        if (!$cfg || !isset($cfg['uso_from'], $cfg['uso_coluna'])) {
            return [];
        }

        // uso_from e uso_coluna vêm da constante acima (seguros).
        $this->db->query("
            SELECT {$cfg['uso_coluna']} AS valor, COUNT(*) AS total
            FROM {$cfg['uso_from']}
            WHERE {$cfg['uso_coluna']} IS NOT NULL
            GROUP BY {$cfg['uso_coluna']}
        ");

        $uso = [];
        foreach ($this->db->resultados() as $linha) {
            $uso[$linha->valor] = (int) $linha->total;
        }

        return $uso;
    }


    // =========================================================
    // ALTERAÇÕES
    // =========================================================

    // ---------------------------------------------------------
    // ADICIONAR
    // Cria a opção no FINAL da lista (ordem = maior ordem + 1).
    // ---------------------------------------------------------
    public function adicionar($grupo, $valor)
    {
        $this->db->query("
            SELECT COALESCE(MAX(opca_ordem), 0) + 1 AS proxima
            FROM opcao_sistema
            WHERE opca_grupo = :g
        ");
        $this->db->bind(':g', $grupo);
        $ordem = (int) $this->db->resultado()->proxima;

        $this->db->query("
            INSERT INTO opcao_sistema (opca_grupo, opca_valor, opca_ordem)
            VALUES (:g, :v, :o)
        ");
        $this->db->bind(':g', $grupo);
        $this->db->bind(':v', $valor);
        $this->db->bind(':o', $ordem);

        return $this->db->executa();
    }

    // ---------------------------------------------------------
    // RENOMEAR
    // Troca o texto da opção E atualiza os registros que já usavam
    // o texto antigo (turmas ou fichas de saúde). Tudo em uma
    // transação: se algo falhar, nada muda.
    // ---------------------------------------------------------
    public function renomear($id, $novoValor)
    {
        $opcao = $this->buscar($id);
        if (!$opcao || !isset(self::GRUPOS[$opcao->grupo])) {
            return false;
        }

        $cfg = self::GRUPOS[$opcao->grupo];

        try {
            $this->db->beginTransaction();

            // 1) Atualiza a própria opção.
            $this->db->query("UPDATE opcao_sistema SET opca_valor = :v WHERE opca_id = :id");
            $this->db->bind(':v', $novoValor);
            $this->db->bind(':id', (int) $id);
            $this->db->executa();

            // 2) Atualiza os registros que usavam o valor antigo, quando
            //    este grupo estiver vinculado a uma tabela de histórico.
            if (isset($cfg['tabela'], $cfg['coluna'])) {
                $this->db->query("
                    UPDATE {$cfg['tabela']}
                    SET {$cfg['coluna']} = :novo
                    WHERE {$cfg['coluna']} = :antigo
                ");
                $this->db->bind(':novo', $novoValor);
                $this->db->bind(':antigo', $opcao->valor);
                $this->db->executa();
            }

            // 3) Grupos guardados como lista JSON (ex.: sintomas da triagem psicológica).
            if (isset($cfg['tabela_json'], $cfg['coluna_json'], $cfg['chave_json'])) {
                $this->renomearEmListaJson($cfg, $opcao->valor, $novoValor);
            }

            $this->db->commit();

            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();

            return false;
        }
    }

    // Troca $antigo por $novo dentro das listas JSON de uma coluna
    // (ex.: ["Tontura","Tremores"]). Nomes de tabela/coluna vêm de GRUPOS.
    private function renomearEmListaJson(array $cfg, $antigo, $novo)
    {
        $tabela = $cfg['tabela_json'];
        $coluna = $cfg['coluna_json'];
        $chave = $cfg['chave_json'];

        $this->db->query("SELECT {$chave} AS id, {$coluna} AS lista FROM {$tabela} WHERE {$coluna} IS NOT NULL");
        foreach ($this->db->resultados() as $linha) {
            $lista = json_decode((string) $linha->lista, true);
            if (!is_array($lista) || !in_array($antigo, $lista, true)) {
                continue;
            }

            $lista = array_values(array_unique(array_map(function ($item) use ($antigo, $novo) {
                return $item === $antigo ? $novo : $item;
            }, $lista)));

            $this->db->query("UPDATE {$tabela} SET {$coluna} = :lista WHERE {$chave} = :id");
            $this->db->bind(':lista', json_encode($lista, JSON_UNESCAPED_UNICODE));
            $this->db->bind(':id', (int) $linha->id);
            $this->db->executa();
        }
    }

    // Ativa/desativa (inverte o estado atual).
    public function alternar($id)
    {
        $this->db->query("UPDATE opcao_sistema SET opca_ativo = 1 - opca_ativo WHERE opca_id = :id");
        $this->db->bind(':id', (int) $id);

        return $this->db->executa();
    }

    // Exclui a opção (o Controller só chama quando ninguém usa).
    public function excluir($id)
    {
        $this->db->query("DELETE FROM opcao_sistema WHERE opca_id = :id");
        $this->db->bind(':id', (int) $id);

        return $this->db->executa();
    }
}