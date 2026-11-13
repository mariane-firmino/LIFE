-- =====================================================================
-- TRIAGEM PSICOLÓGICA - ETAPA 2: HISTÓRICO PESSOAL
-- Tabela onde o formulário "Histórico Pessoal" (triagens/historico_pessoal)
-- grava as respostas. Cada registro pode estar ligado à triagem
-- psicológica (Etapa 1) feita logo antes.
--
-- O model TriagemPsicologica cria esta tabela sozinho se ela não existir.
-- Para instalar à mão (phpMyAdmin, aba SQL, ou terminal):
--     mysql -uroot -P3307 bd_life < triagem_psicologica_historico.sql
-- (execute antes o triagem_psicologica.sql)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `triagem_psicologica_historico` (
  `trph_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) NOT NULL,
  `trps_id` int(11) DEFAULT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `trph_diagnostico` varchar(20) NOT NULL,
  `trph_transtorno` text DEFAULT NULL,
  `trph_medicamento` varchar(20) NOT NULL,
  `trph_nome_medicamento` varchar(255) DEFAULT NULL,
  `trph_acompanhamento` varchar(20) NOT NULL,
  `trph_crises` varchar(20) NOT NULL,
  `trph_gatilhos` text DEFAULT NULL,
  `trph_relacao_familiar` varchar(30) NOT NULL,
  `trph_historico_familiar` varchar(20) NOT NULL,
  `trph_alimentacao` varchar(30) NOT NULL,
  `trph_observacoes` text DEFAULT NULL,
  `trph_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`trph_id`),
  KEY `ix_trph_aluno` (`alun_id`,`trph_criado_em`),
  UNIQUE KEY `ux_trph_triagem` (`trps_id`),
  KEY `ix_trph_servidor` (`serv_id`),
  CONSTRAINT `fk_trph_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trph_triagem` FOREIGN KEY (`trps_id`) REFERENCES `triagem_psicologica` (`trps_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trph_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
