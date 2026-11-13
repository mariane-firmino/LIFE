-- =====================================================================
-- TURMAS: ANO DE ENTRADA (24, 25, 26) -> SÉRIE (1º ano, 2º ano, 3º ano)
-- 26 -> 1º ano, 25 -> 2º ano, 24 -> 3º ano.
-- Também converte o formato curto "1º/2º/3º" para "1º ano/2º ano/3º ano".
-- Pode rodar mais de uma vez: só altera o que ainda estiver no formato antigo.
--     mysql -uroot -P3307 bd_life < turma_series.sql
-- =====================================================================

-- "3º ano" tem 6 caracteres; a coluna aceitava só 2.
ALTER TABLE `turma` MODIFY `turm_ano` varchar(10) NOT NULL;

UPDATE `turma` SET `turm_ano` = CASE `turm_ano`
    WHEN '26' THEN '1º ano'
    WHEN '25' THEN '2º ano'
    WHEN '24' THEN '3º ano'
    WHEN '1º' THEN '1º ano'
    WHEN '2º' THEN '2º ano'
    WHEN '3º' THEN '3º ano'
END
WHERE `turm_ano` IN ('24', '25', '26', '1º', '2º', '3º');

UPDATE `opcao_sistema` SET
    `opca_ordem` = CASE `opca_valor`
        WHEN '26' THEN 1 WHEN '25' THEN 2 WHEN '24' THEN 3
        WHEN '1º' THEN 1 WHEN '2º' THEN 2 WHEN '3º' THEN 3
    END,
    `opca_valor` = CASE `opca_valor`
        WHEN '26' THEN '1º ano' WHEN '25' THEN '2º ano' WHEN '24' THEN '3º ano'
        WHEN '1º' THEN '1º ano' WHEN '2º' THEN '2º ano' WHEN '3º' THEN '3º ano'
    END
WHERE `opca_grupo` = 'ano_turma' AND `opca_valor` IN ('24', '25', '26', '1º', '2º', '3º');
