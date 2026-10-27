-- =====================================================================
-- TRIAGEM RÁPIDA
-- Rode este arquivo uma vez no banco bd_life (phpMyAdmin > SQL, ou
-- MySQL Workbench) para criar a tabela e a lista de queixas.
-- O bd_life.sql já vem com tudo isto incluído.
-- =====================================================================

USE `bd_life`;

CREATE TABLE IF NOT EXISTS `triagem_rapida` (
  `trap_id` int NOT NULL AUTO_INCREMENT,
  `alun_id` int NOT NULL,
  `serv_id` int DEFAULT NULL,
  `trap_queixa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `trap_gravidade` enum('Leve','Média','Grave') COLLATE utf8mb4_general_ci NOT NULL,
  `trap_relato_aluno` text COLLATE utf8mb4_general_ci,
  `trap_observacao` text COLLATE utf8mb4_general_ci,
  `trap_conduta` text COLLATE utf8mb4_general_ci NOT NULL,
  `trap_criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`trap_id`),
  KEY `ix_triagem_rapida_aluno` (`alun_id`,`trap_criado_em`),
  KEY `fk_triagem_rapida_servidor_idx` (`serv_id`),
  CONSTRAINT `fk_triagem_rapida_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_triagem_rapida_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Lista de queixas do menu suspenso (o admin pode mudar em "Informações do sistema").
INSERT IGNORE INTO `opcao_sistema` (`opca_grupo`, `opca_valor`, `opca_ordem`, `opca_ativo`) VALUES
('queixa', 'Dor de cabeça', 1, 1),
('queixa', 'Cólica', 2, 1),
('queixa', 'Febre', 3, 1),
('queixa', 'Dor de barriga', 4, 1),
('queixa', 'Enjoo / vômito', 5, 1),
('queixa', 'Tontura / mal-estar', 6, 1),
('queixa', 'Dor de garganta', 7, 1),
('queixa', 'Gripe / resfriado', 8, 1),
('queixa', 'Machucado / ferimento', 9, 1),
('queixa', 'Alergia', 10, 1),
('queixa', 'Crise de ansiedade', 11, 1),
('queixa', 'Pressão alta / baixa', 12, 1),
('queixa', 'Outro', 13, 1);
