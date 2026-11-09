<?php

// =========================================================
// ROTINAS DO BANCO
// Garante que as functions, procedures e triggers do arquivo
// rotinas_banco.sql (raiz do projeto) existem no bd_life.
// Se faltar alguma (ex.: banco importado em outro computador),
// o arquivo inteiro é executado de novo. Verifica uma vez
// por requisição.
// =========================================================

class RotinasBanco
{
    public const FUNCOES = ['fn_idade', 'fn_motivos_atencao'];
    public const PROCEDURES = ['sp_resumo_alunos', 'sp_alunos_atencao'];
    public const TRIGGERS = ['trg_aluno_antes_inserir', 'trg_aluno_antes_atualizar', 'trg_anamnese_depois_atualizar'];

    private static $verificado = false;

    public static function garantir(Database $db): void
    {
        if (self::$verificado) {
            return;
        }
        self::$verificado = true;

        try {
            if (self::faltando($db) === 0) {
                return;
            }
            self::instalar($db);
        } catch (Throwable $e) {
            error_log('Rotinas do banco: ' . $e->getMessage());
        }
    }

    // Quantas rotinas do arquivo ainda não existem no banco.
    private static function faltando(Database $db): int
    {
        $rotinas = array_merge(self::FUNCOES, self::PROCEDURES);

        $db->query("
            SELECT
                (SELECT COUNT(*) FROM information_schema.routines
                  WHERE routine_schema = DATABASE()
                    AND routine_name IN ('" . implode("','", $rotinas) . "'))
              + (SELECT COUNT(*) FROM information_schema.triggers
                  WHERE trigger_schema = DATABASE()
                    AND trigger_name IN ('" . implode("','", self::TRIGGERS) . "')) AS total
        ");
        $linha = $db->resultado();

        return count($rotinas) + count(self::TRIGGERS) - (int) ($linha->total ?? 0);
    }

    private static function instalar(Database $db): void
    {
        $arquivo = dirname(__DIR__, 2) . '/rotinas_banco.sql';
        $sql = is_file($arquivo) ? file_get_contents($arquivo) : '';
        if ($sql === '') {
            error_log('Rotinas do banco: arquivo rotinas_banco.sql não encontrado.');
            return;
        }

        // Tira as linhas DELIMITER e separa os comandos pelo "$$".
        $sql = preg_replace('/^\s*DELIMITER\s+\S+\s*$/mi', '', $sql);
        foreach (explode('$$', $sql) as $comando) {
            // Ignora pedaços que só têm comentários.
            $semComentarios = trim(preg_replace('/^\s*--.*$/m', '', $comando));
            if ($semComentarios === '') {
                continue;
            }
            $db->query($semComentarios);
            $db->executa();
        }
    }
}
