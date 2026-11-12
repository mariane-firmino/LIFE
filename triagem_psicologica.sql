-- =====================================================================
-- TRIAGEM PSICOLÓGICA RÁPIDA
-- Tabela onde o formulário "Triagem Psicológica" (triagens/psicologico_rapido)
-- grava os atendimentos, e as opções iniciais de motivos e sintomas
-- (editáveis pelo administrador em Admin > Sistema).
--
-- O model TriagemPsicologica cria tudo isso sozinho se a tabela não existir.
-- Para instalar à mão (phpMyAdmin, aba SQL, ou terminal):
--     mysql -uroot -P3307 bd_life < triagem_psicologica.sql
-- =====================================================================

CREATE TABLE IF NOT EXISTS `triagem_psicologica` (
  `trps_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) NOT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `trps_motivo` varchar(100) NOT NULL,
  `trps_gravidade` enum('Leve','Média','Grave') NOT NULL,
  `trps_sintomas` text DEFAULT NULL,
  `trps_observacoes` text DEFAULT NULL,
  `trps_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`trps_id`),
  KEY `ix_triagem_psicologica_aluno` (`alun_id`,`trps_criado_em`),
  KEY `fk_triagem_psicologica_servidor_idx` (`serv_id`),
  CONSTRAINT `fk_triagem_psicologica_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_triagem_psicologica_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Opções iniciais (não duplica se já existirem).
INSERT IGNORE INTO `opcao_sistema` (`opca_grupo`, `opca_valor`, `opca_ordem`) VALUES
  ('motivo_triagem_psicologica', 'Crise de ansiedade', 1),
  ('motivo_triagem_psicologica', 'Ataque de pânico', 2),
  ('motivo_triagem_psicologica', 'Choro intenso', 3),
  ('motivo_triagem_psicologica', 'Nervosismo', 4),
  ('motivo_triagem_psicologica', 'Outro', 5),
  ('sintoma_triagem_psicologica', 'Falta de ar', 1),
  ('sintoma_triagem_psicologica', 'Tontura', 2),
  ('sintoma_triagem_psicologica', 'Tremores', 3),
  ('sintoma_triagem_psicologica', 'Taquicardia', 4),
  ('sintoma_triagem_psicologica', 'Choro intenso', 5),
  ('sintoma_triagem_psicologica', 'Aperto no peito', 6);
