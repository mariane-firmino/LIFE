-- =====================================================================
-- ROTINAS DO BANCO bd_life: FUNCTIONS, PROCEDURES e TRIGGERS
--
-- O sistema instala este arquivo sozinho quando alguma rotina está
-- faltando (App/Libraries/RotinasBanco.php). Para instalar à mão,
-- rode o arquivo inteiro no phpMyAdmin (aba SQL) ou no terminal:
--     mysql -uroot -P3307 bd_life < rotinas_banco.sql
--
-- Regras para editar (o PHP lê este arquivo):
--   * cada comando termina com o delimitador definido abaixo (DELIMITER);
--   * não use ":" grudado em palavras (o PDO acha que é parâmetro);
--   * evite acentos em textos fixos: devolva códigos e traduza no PHP.
-- =====================================================================

DELIMITER $$

-- ---------------------------------------------------------------------
-- FUNCTION fn_idade
-- Idade em anos completos a partir da data de nascimento.
-- Ex.: SELECT alun_nome, fn_idade(alun_dt_nascimento) FROM aluno;
-- ---------------------------------------------------------------------
DROP FUNCTION IF EXISTS fn_idade $$
CREATE FUNCTION fn_idade(p_nascimento DATE)
RETURNS INT
NOT DETERMINISTIC
NO SQL
BEGIN
    IF p_nascimento IS NULL THEN
        RETURN NULL;
    END IF;
    RETURN TIMESTAMPDIFF(YEAR, p_nascimento, CURDATE());
END $$

-- ---------------------------------------------------------------------
-- FUNCTION fn_motivos_atencao
-- Lê a ficha de saúde (anamnese) do aluno e devolve os motivos de
-- atenção como códigos separados por "|", ou NULL se não houver:
--   condicao=TDAH;Outra    doenca=asma    alergia=amendoim    medicacao=...
-- O PHP (model Home) transforma os códigos em texto.
-- ---------------------------------------------------------------------
DROP FUNCTION IF EXISTS fn_motivos_atencao $$
CREATE FUNCTION fn_motivos_atencao(p_alun_id INT)
RETURNS VARCHAR(1000)
NOT DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_achou INT DEFAULT 1;
    DECLARE v_condicoes VARCHAR(255);
    DECLARE v_outra VARCHAR(255);
    DECLARE v_doenca VARCHAR(20);
    DECLARE v_doenca_qual VARCHAR(255);
    DECLARE v_alergia VARCHAR(20);
    DECLARE v_alergia_qual VARCHAR(255);
    DECLARE v_medicacao VARCHAR(20);
    DECLARE v_medicacao_qual VARCHAR(255);
    DECLARE v_motivos VARCHAR(1000) DEFAULT NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_achou = 0;

    SELECT anam_condicoes, anam_condicao_outra,
           anam_doenca_cronica, anam_doenca_qual,
           anam_alergia_alimentar, anam_alergia_qual,
           anam_medicacao_continua, anam_medicacao_qual
      INTO v_condicoes, v_outra,
           v_doenca, v_doenca_qual,
           v_alergia, v_alergia_qual,
           v_medicacao, v_medicacao_qual
      FROM anamnese
     WHERE alun_id = p_alun_id
     ORDER BY anamn_id DESC
     LIMIT 1;

    IF v_achou = 0 THEN
        RETURN NULL;
    END IF;

    IF COALESCE(v_condicoes, '') <> '' THEN
        SET v_motivos = CONCAT_WS('|', v_motivos,
            CONCAT('condicao=', REPLACE(v_condicoes, 'Outra', COALESCE(NULLIF(v_outra, ''), 'Outra'))));
    END IF;
    IF v_doenca = 'Sim' THEN
        SET v_motivos = CONCAT_WS('|', v_motivos, CONCAT('doenca=', COALESCE(v_doenca_qual, '')));
    END IF;
    IF v_alergia = 'Sim' THEN
        SET v_motivos = CONCAT_WS('|', v_motivos, CONCAT('alergia=', COALESCE(v_alergia_qual, '')));
    END IF;
    IF v_medicacao = 'Sim' THEN
        SET v_motivos = CONCAT_WS('|', v_motivos, CONCAT('medicacao=', COALESCE(v_medicacao_qual, '')));
    END IF;

    RETURN v_motivos;
END $$

-- ---------------------------------------------------------------------
-- PROCEDURE sp_resumo_alunos
-- Números dos cards da página "Cadastro de Aluno".
-- Ex.: CALL sp_resumo_alunos();
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_resumo_alunos $$
CREATE PROCEDURE sp_resumo_alunos()
READS SQL DATA
BEGIN
    SELECT COUNT(*) AS total,
           COALESCE(SUM(a.alun_sexo = 'Masculino'), 0) AS meninos,
           COALESCE(SUM(a.alun_sexo = 'Feminino'), 0) AS meninas,
           COALESCE(SUM(EXISTS (
               SELECT 1 FROM anamnese an
                WHERE an.alun_id = a.alun_id
                  AND an.anam_doenca_cronica = 'Sim'
           )), 0) AS com_doenca
      FROM aluno a;
END $$

-- ---------------------------------------------------------------------
-- PROCEDURE sp_alunos_atencao
-- Alunos que precisam de atenção: ficha de saúde com alerta
-- (fn_motivos_atencao) ou triagem rápida Média/Grave desde p_desde.
-- Ex.: CALL sp_alunos_atencao(NOW() - INTERVAL 30 DAY);
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_alunos_atencao $$
CREATE PROCEDURE sp_alunos_atencao(IN p_desde DATETIME)
READS SQL DATA
BEGIN
    SELECT *
      FROM (
            SELECT a.alun_id,
                   a.alun_nome,
                   t.turm_curso,
                   t.turm_ano,
                   fn_idade(a.alun_dt_nascimento) AS idade,
                   fn_motivos_atencao(a.alun_id) AS motivos_ficha,
                   r.trap_gravidade AS rapida_gravidade,
                   r.trap_queixa AS rapida_queixa,
                   r.trap_criado_em AS rapida_data
              FROM aluno a
         LEFT JOIN turma t ON t.turm_id = a.turm_id
         LEFT JOIN triagem_rapida r
                ON r.trap_id = (
                       SELECT r2.trap_id
                         FROM triagem_rapida r2
                        WHERE r2.alun_id = a.alun_id
                          AND r2.trap_criado_em >= p_desde
                          AND r2.trap_gravidade <> 'Leve'
                     ORDER BY r2.trap_criado_em DESC
                        LIMIT 1
                   )
           ) x
     WHERE x.motivos_ficha IS NOT NULL
        OR x.rapida_data IS NOT NULL
     ORDER BY x.rapida_data DESC, x.alun_nome;
END $$

-- ---------------------------------------------------------------------
-- TRIGGERS do aluno (antes de inserir / atualizar)
-- Tira espaços do nome e não deixa gravar nascimento no futuro.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_aluno_antes_inserir $$
CREATE TRIGGER trg_aluno_antes_inserir
BEFORE INSERT ON aluno
FOR EACH ROW
BEGIN
    SET NEW.alun_nome = TRIM(NEW.alun_nome);
    IF NEW.alun_dt_nascimento > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Data de nascimento nao pode ser no futuro';
    END IF;
END $$

DROP TRIGGER IF EXISTS trg_aluno_antes_atualizar $$
CREATE TRIGGER trg_aluno_antes_atualizar
BEFORE UPDATE ON aluno
FOR EACH ROW
BEGIN
    SET NEW.alun_nome = TRIM(NEW.alun_nome);
    IF NEW.alun_dt_nascimento > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Data de nascimento nao pode ser no futuro';
    END IF;
END $$

-- ---------------------------------------------------------------------
-- TRIGGER da ficha de saúde (depois de atualizar)
-- Quando a anamnese muda, marca o aluno como atualizado agora
-- (aparece em "Atualizado em" no perfil e na ficha impressa).
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_anamnese_depois_atualizar $$
CREATE TRIGGER trg_anamnese_depois_atualizar
AFTER UPDATE ON anamnese
FOR EACH ROW
BEGIN
    UPDATE aluno
       SET alun_atualizado_em = NOW()
     WHERE alun_id = NEW.alun_id;
END $$

DELIMITER ;
