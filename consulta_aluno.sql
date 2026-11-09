-- Liga cada consulta do calendário a um aluno (opcional).
-- (O model Consulta também cria a coluna automaticamente se ela não existir.)

ALTER TABLE `consulta`
  ADD COLUMN `alun_id` int NULL DEFAULT NULL AFTER `cons_id`,
  ADD KEY `ix_consulta_aluno` (`alun_id`),
  ADD CONSTRAINT `fk_consulta_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE SET NULL;
