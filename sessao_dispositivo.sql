-- Tabela usada pela página "Gerenciar dispositivos".
-- Cada login cria uma linha; encerrar a sessão marca sess_ativa = 0.
-- (O model Dispositivo também cria a tabela automaticamente se ela não existir.)

CREATE TABLE IF NOT EXISTS `sessao_dispositivo` (
  `sess_id` int NOT NULL AUTO_INCREMENT,
  `serv_id` int NOT NULL,
  `sess_token` char(64) NOT NULL,
  `sess_dispositivo` varchar(60) NOT NULL,
  `sess_navegador` varchar(60) NOT NULL,
  `sess_ip` varchar(45) NOT NULL,
  `sess_user_agent` varchar(500) DEFAULT NULL,
  `sess_criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sess_ultima_atividade` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sess_ativa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`sess_id`),
  UNIQUE KEY `uk_sessao_token` (`sess_token`),
  KEY `ix_sessao_servidor` (`serv_id`, `sess_ativa`),
  CONSTRAINT `fk_sessao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
