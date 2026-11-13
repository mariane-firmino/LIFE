-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: bd_life
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alternativa`
--

DROP TABLE IF EXISTS `alternativa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alternativa` (
  `alte_id` int(11) NOT NULL AUTO_INCREMENT,
  `alte_descricao` text NOT NULL,
  `perg_id` int(11) NOT NULL,
  PRIMARY KEY (`alte_id`),
  KEY `fk_alternativa_pergunta1_idx` (`perg_id`),
  KEY `ix_alternativa_descricao` (`alte_descricao`(100)),
  CONSTRAINT `fk_alternativa_pergunta1` FOREIGN KEY (`perg_id`) REFERENCES `pergunta` (`perg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alternativa`
--

LOCK TABLES `alternativa` WRITE;
/*!40000 ALTER TABLE `alternativa` DISABLE KEYS */;
INSERT INTO `alternativa` VALUES (1,'Muito bem',1),(2,'Bem',1),(3,'Mais ou menos',1),(4,'Mal',1),(5,'Muito bem',2),(6,'Bem',2),(7,'Mais ou menos',2),(8,'Mal',2),(9,'Estudar',5),(10,'Trabalhar',5),(11,'Entrar na faculdade',5),(12,'Outro',5),(13,'Sim',6),(14,'Não',6),(15,'Sim',7),(16,'Sim',16),(18,'Não',7),(19,'Não',16),(21,'Sempre',8),(22,'Às vezes',8),(23,'Nunca',8),(24,'Não acho necessário',9),(25,'Não gosto',9),(26,'Meu parceiro(a) não gosta',9),(27,'Outro motivo',9),(28,'Sim',10),(29,'Não',10),(30,'Sim',12),(31,'Não',12),(32,'0',13),(33,'1',13),(34,'2',13),(35,'3',13),(36,'4',13),(37,'5',13),(38,'6',13),(39,'7',13),(40,'8',13),(41,'9',13),(42,'10',13),(43,'Sim',14),(44,'Não',14),(45,'Sim',7),(46,'Sim',16),(48,'Não',7),(49,'Não',16),(51,'Preservativo',17),(52,'Anticoncepcional',17),(53,'DIU',17),(54,'Outro',17),(55,'Nenhum',17),(56,'Não acho necessário',18),(57,'Não gosto',18),(58,'Outro motivo',18),(59,'Sim',19),(60,'Não',19),(61,'Sim',10),(62,'Não',10),(63,'Sim',12),(64,'Não',12),(65,'0',13),(66,'1',13),(67,'2',13),(68,'3',13),(69,'4',13),(70,'5',13),(71,'6',13),(72,'7',13),(73,'8',13),(74,'9',13),(75,'10',13),(76,'Sim',14),(77,'Não',14),(78,'Preservativo',17),(79,'Anticoncepcional',17),(80,'DIU',17),(81,'Outro',17),(82,'Nenhum',17),(83,'Não acho necessário',18),(84,'Não gosto',18),(85,'Outro motivo',18),(86,'Sim',19),(87,'Não',19),(88,'Sim',10),(89,'Não',10),(90,'Sim',12),(91,'Não',12),(92,'0',13),(93,'1',13),(94,'2',13),(95,'3',13),(96,'4',13),(97,'5',13),(98,'6',13),(99,'7',13),(100,'8',13),(101,'9',13),(102,'10',13),(103,'Sim',14),(104,'Não',14),(105,'Preservativo',17),(106,'Anticoncepcional',17),(107,'DIU',17),(108,'Outro',17),(109,'Nenhum',17),(110,'Não acho necessário',18),(111,'Não gosto',18),(112,'Outro motivo',18),(113,'Sim',19),(114,'Não',19),(115,'Sim',10),(116,'Não',10),(117,'Sim',12),(118,'Não',12),(119,'0',13),(120,'1',13),(121,'2',13),(122,'3',13),(123,'4',13),(124,'5',13),(125,'6',13),(126,'7',13),(127,'8',13),(128,'9',13),(129,'10',13),(130,'Sim',14),(131,'Não',14),(132,'Preservativo',17),(133,'Anticoncepcional',17),(134,'DIU',17),(135,'Outro',17),(136,'Nenhum',17),(137,'Não acho necessário',18),(138,'Não gosto',18),(139,'Outro motivo',18),(140,'Sim',19),(141,'Não',19),(143,'Sim',10),(144,'Não',10),(145,'Sim',12),(146,'Não',12),(147,'0',13),(148,'1',13),(149,'2',13),(150,'3',13),(151,'4',13),(152,'5',13),(153,'6',13),(154,'7',13),(155,'8',13),(156,'9',13),(157,'10',13),(158,'Sim',14),(159,'Não',14),(160,'Preservativo',17),(161,'Anticoncepcional',17),(162,'DIU',17),(163,'Outro',17),(164,'Nenhum',17),(165,'Não acho necessário',18),(166,'Não gosto',18),(167,'Outro motivo',18),(168,'Sim',19),(169,'Não',19);
/*!40000 ALTER TABLE `alternativa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aluno`
--

DROP TABLE IF EXISTS `aluno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aluno` (
  `alun_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_nome` varchar(225) NOT NULL,
  `alun_dt_nascimento` date NOT NULL,
  `alun_sexo` varchar(25) NOT NULL,
  `alun_matricula` varchar(15) NOT NULL,
  `alun_rg` varchar(14) DEFAULT NULL,
  `alun_cartao_sus` varchar(15) DEFAULT NULL,
  `alun_foto` blob DEFAULT NULL,
  `alun_tipo_sanguineo` varchar(3) DEFAULT NULL,
  `alun_altura` decimal(3,2) DEFAULT NULL,
  `alun_peso` decimal(5,2) DEFAULT NULL,
  `alun_idade` int(11) DEFAULT NULL,
  `resp_id` int(11) NOT NULL,
  `turm_id` int(11) NOT NULL,
  `alun_data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `alun_atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `alun_info_extras` text DEFAULT NULL,
  PRIMARY KEY (`alun_id`),
  UNIQUE KEY `alun_matricula_UNIQUE` (`alun_matricula`),
  UNIQUE KEY `alun_rg_UNIQUE` (`alun_rg`),
  KEY `fk_aluno_responsavel1_idx` (`resp_id`),
  KEY `fk_aluno_turma1_idx` (`turm_id`),
  KEY `ix_aluno_nome` (`alun_nome`),
  CONSTRAINT `fk_aluno_responsavel1` FOREIGN KEY (`resp_id`) REFERENCES `responsavel` (`resp_id`),
  CONSTRAINT `fk_aluno_turma1` FOREIGN KEY (`turm_id`) REFERENCES `turma` (`turm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aluno`
--

LOCK TABLES `aluno` WRITE;
/*!40000 ALTER TABLE `aluno` DISABLE KEYS */;
INSERT INTO `aluno` VALUES (1,'Ana Beatriz Souza Lima','2011-03-14','Feminino','2026109100012','1928374','898001234567812',NULL,'O+',1.62,54.20,NULL,1,1,'2026-02-09 08:15:00',NULL,NULL),(5,'Gabriela Martins Ribeiro','2010-05-18','Feminino','2025109100051','1899240','700308912345678',NULL,'A-',1.65,57.30,NULL,5,2,'2025-02-12 09:10:00',NULL,NULL),(8,'Matheus Silva Carvalho','2010-04-11','Masculino','2025109100082','1895543','702409876543210',NULL,'A+',1.72,82.60,NULL,8,7,'2025-02-13 08:20:00',NULL,NULL),(9,'Larissa Fernandes Moura','2009-06-05','Feminino','2024109100090','1871236','898002345678901',NULL,'O+',1.67,58.90,NULL,9,3,'2024-02-15 08:05:00',NULL,NULL),(12,'Guilherme Castro Monteiro','2008-12-02','Masculino','2024109100120','1866652','898009012345671',NULL,'O+',1.76,69.80,NULL,12,6,'2024-02-16 13:50:00',NULL,NULL);
/*!40000 ALTER TABLE `aluno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aluno_anotacao`
--

DROP TABLE IF EXISTS `aluno_anotacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aluno_anotacao` (
  `anot_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) NOT NULL,
  `anot_tipo` enum('rapida','psicologica') NOT NULL,
  `anot_texto` text NOT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `anot_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`anot_id`),
  KEY `ix_anotacao_aluno_tipo` (`alun_id`,`anot_tipo`),
  KEY `fk_anotacao_servidor_idx` (`serv_id`),
  CONSTRAINT `fk_anotacao_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anotacao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aluno_anotacao`
--

LOCK TABLES `aluno_anotacao` WRITE;
/*!40000 ALTER TABLE `aluno_anotacao` DISABLE KEYS */;
INSERT INTO `aluno_anotacao` VALUES (1,8,'rapida','Guarda o glicosímetro e a insulina na mochila. Professores avisados sobre sinais de hipoglicemia.',9,'2026-10-21 12:00:00'),(2,12,'rapida','Família enviou o protocolo do neurologista para crises.',9,'2026-11-11 09:10:00');
/*!40000 ALTER TABLE `aluno_anotacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aluno_documento`
--

DROP TABLE IF EXISTS `aluno_documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aluno_documento` (
  `docu_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) NOT NULL,
  `docu_tipo` enum('vacina','sus','laudo') NOT NULL,
  `docu_titulo` varchar(150) DEFAULT NULL,
  `docu_descricao` varchar(500) DEFAULT NULL,
  `docu_data_documento` date DEFAULT NULL,
  `docu_arquivo` varchar(80) NOT NULL,
  `docu_nome_original` varchar(150) NOT NULL,
  `docu_mime` varchar(50) NOT NULL,
  `docu_tamanho` int(11) NOT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `docu_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`docu_id`),
  KEY `ix_documento_aluno_tipo` (`alun_id`,`docu_tipo`),
  KEY `fk_documento_servidor_idx` (`serv_id`),
  CONSTRAINT `fk_documento_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documento_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aluno_documento`
--

LOCK TABLES `aluno_documento` WRITE;
/*!40000 ALTER TABLE `aluno_documento` DISABLE KEYS */;
/*!40000 ALTER TABLE `aluno_documento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anamnese`
--

DROP TABLE IF EXISTS `anamnese`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anamnese` (
  `anamn_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) NOT NULL,
  `anam_tipo_escola` varchar(50) DEFAULT NULL,
  `anam_motivo_ifro` varchar(100) DEFAULT NULL,
  `anam_ja_reprovou` varchar(5) DEFAULT NULL,
  `anam_quantas_vezes` varchar(10) DEFAULT NULL,
  `anam_estuda_fora` varchar(5) DEFAULT NULL,
  `anam_quanto_tempo` varchar(50) DEFAULT NULL,
  `anam_nota_curso` tinyint(4) DEFAULT NULL,
  `anam_nota_motivacao` tinyint(4) DEFAULT NULL,
  `anam_atendimento` varchar(5) DEFAULT NULL,
  `anam_atendimento_porque` varchar(255) DEFAULT NULL,
  `anam_acesso_saude` varchar(20) DEFAULT NULL,
  `anam_alergia_alimentar` varchar(10) DEFAULT NULL,
  `anam_alergia_qual` varchar(255) DEFAULT NULL,
  `anam_doenca_cronica` varchar(10) DEFAULT NULL,
  `anam_doenca_qual` varchar(255) DEFAULT NULL,
  `anam_medicacao_continua` varchar(5) DEFAULT NULL,
  `anam_medicacao_qual` varchar(255) DEFAULT NULL,
  `anam_acompanhamento_psi` varchar(5) DEFAULT NULL,
  `anam_cartao_vacina` varchar(5) DEFAULT NULL,
  `anam_ja_internado` varchar(5) DEFAULT NULL,
  `anam_internado_motivo` varchar(255) DEFAULT NULL,
  `anam_ja_cirurgia` varchar(5) DEFAULT NULL,
  `anam_cirurgia_qual` varchar(255) DEFAULT NULL,
  `anam_padrao_sono` varchar(60) DEFAULT NULL,
  `anam_refeicoes_dia` varchar(20) DEFAULT NULL,
  `anam_alimentacao_habitual` text DEFAULT NULL,
  `anam_fuma` varchar(5) DEFAULT NULL,
  `anam_bebida_alcoolica` varchar(5) DEFAULT NULL,
  `anam_pais_sabem` varchar(5) DEFAULT NULL,
  `anam_drogas_ilicitas` varchar(5) DEFAULT NULL,
  `anam_drogas_qual` varchar(255) DEFAULT NULL,
  `anam_atividade_fisica` varchar(5) DEFAULT NULL,
  `anam_frequencia_atividade` varchar(30) DEFAULT NULL,
  `anam_banhos_dia` varchar(20) DEFAULT NULL,
  `anam_condicoes` varchar(255) DEFAULT NULL,
  `anam_condicao_outra` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`anamn_id`),
  KEY `fk_anamnese_aluno` (`alun_id`),
  CONSTRAINT `fk_anamnese_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anamnese`
--

LOCK TABLES `anamnese` WRITE;
/*!40000 ALTER TABLE `anamnese` DISABLE KEYS */;
INSERT INTO `anamnese` VALUES (4,1,'Escola pública','Qualidade do ensino','Não',NULL,'Não',NULL,9,8,'Não',NULL,'SUS','Sim','Amendoim','Sim','Asma','Sim','Salbutamol (bombinha) quando tem crise','Não','Sim','Sim','Crise de asma aos 10 anos','Não',NULL,'Restaurador','3','Arroz, feijão, frango e salada; lanche na escola.','Não','Não',NULL,'Não',NULL,'Sim','(-) de 3x por semana','2 por dia',NULL,NULL),(8,5,'Escola estadual','Recomendação de outros','Não',NULL,'Não',NULL,8,7,'Sim','Acompanhamento com psicóloga','SUS','Não',NULL,'Não',NULL,'Sim','Metilfenidato 10 mg pela manhã','Sim','Sim','Não',NULL,'Não',NULL,'Não restaurador','3','Pouco apetite no almoço por causa da medicação.','Não','Não',NULL,'Não',NULL,'Não',NULL,'2 por dia','TDAH',NULL),(11,8,'Escola pública','Proximidade da escola','Não',NULL,'Não',NULL,7,7,'Sim','Acompanhamento endocrinológico','SUS','Não',NULL,'Sim','Diabetes tipo 1','Sim','Insulina NPH e regular','Não','Sim','Sim','Cetoacidose diabética (2021)','Não',NULL,'Restaurador','Mais que 3','Controla carboidratos; leva lanche para a escola.','Não','Não',NULL,'Não',NULL,'Não',NULL,'2 por dia',NULL,NULL),(12,9,'Escola estadual','Qualidade do ensino','Não',NULL,'Sim','3 anos',9,7,'Sim','Ansiedade em época de provas','SUS','Não',NULL,'Não',NULL,'Não',NULL,'Sim','Sim','Não',NULL,'Não',NULL,'Insônia (perde o sono e não consegue voltar a dormir)','3','Come pouco em semanas de prova.','Não','Não',NULL,'Não',NULL,'Sim','(-) de 3x por semana','2 por dia',NULL,NULL),(15,12,'Escola estadual','Recomendação de outros','Não',NULL,'Sim','1 ano',8,7,'Sim','Neurologista','SUS','Não',NULL,'Sim','Epilepsia','Sim','Ácido valproico 500 mg','Não','Sim','Sim','Crise convulsiva (2023)','Não',NULL,'Restaurador','3','Sem restrições.','Não','Não',NULL,'Não',NULL,'Não',NULL,'2 por dia','Epilepsia',NULL);
/*!40000 ALTER TABLE `anamnese` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consulta`
--

DROP TABLE IF EXISTS `consulta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consulta` (
  `cons_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) DEFAULT NULL,
  `cons_data` date NOT NULL,
  `cons_horario` time NOT NULL,
  `cons_tipo` varchar(100) NOT NULL,
  `cons_responsavel` varchar(60) DEFAULT NULL,
  `cons_descricao` text DEFAULT NULL,
  `cons_observacao` text DEFAULT NULL,
  PRIMARY KEY (`cons_id`),
  KEY `ix_consulta_data` (`cons_data`),
  KEY `ix_consulta_hora` (`cons_horario`),
  KEY `ix_consulta_aluno` (`alun_id`),
  CONSTRAINT `fk_consulta_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta`
--

LOCK TABLES `consulta` WRITE;
/*!40000 ALTER TABLE `consulta` DISABLE KEYS */;
INSERT INTO `consulta` VALUES (9,1,'2026-10-20','09:00:00','Retorno','Enfermeira','Retorno após crise de asma.','Trazer a bombinha nas aulas de educação física.'),(10,8,'2026-10-29','10:30:00','Avaliação','Enfermeira','Avaliação do controle da glicemia na escola.','Combinar com a família um kit de lanche de emergência.'),(11,9,'2026-11-05','14:00:00','Psicóloga','Psicóloga','Acompanhamento após crise de ansiedade.',NULL),(12,12,'2026-11-11','08:30:00','Consulta - Emergencial','Enfermeira','Conversa com a família após a crise convulsiva.','Pedir relatório do neurologista.'),(13,5,'2026-11-17','15:00:00','Consulta - Periódica','Enfermeira','Acompanhamento do uso de medicação para TDAH.',NULL);
/*!40000 ALTER TABLE `consulta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcao`
--

DROP TABLE IF EXISTS `funcao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `funcao` (
  `func_id` int(11) NOT NULL AUTO_INCREMENT,
  `func_nome` varchar(100) NOT NULL,
  PRIMARY KEY (`func_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcao`
--

LOCK TABLES `funcao` WRITE;
/*!40000 ALTER TABLE `funcao` DISABLE KEYS */;
INSERT INTO `funcao` VALUES (1,'Enfermeiro (a)'),(2,'Psicólogo (a)');
/*!40000 ALTER TABLE `funcao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacao`
--

DROP TABLE IF EXISTS `notificacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacao` (
  `noti_id` int(11) NOT NULL AUTO_INCREMENT,
  `noti_titulo` varchar(200) DEFAULT NULL,
  `noti_data` timestamp NOT NULL DEFAULT current_timestamp(),
  `noti_descricao` text DEFAULT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `noti_status` varchar(20) DEFAULT 'Não lido',
  `noti_tipo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`noti_id`),
  KEY `fk_notificacao` (`serv_id`),
  CONSTRAINT `fk_notificacao` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacao`
--

LOCK TABLES `notificacao` WRITE;
/*!40000 ALTER TABLE `notificacao` DISABLE KEYS */;
INSERT INTO `notificacao` VALUES (70,'Nova consulta na agenda','2026-11-04 14:45:10','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',14,'Pendente','consulta'),(71,'Nova triagem rápida registrada','2026-11-04 14:56:14','Uma triagem rápida foi registrada. Acesse o sistema para consultar as informações autorizadas.',14,'Pendente','triagem'),(72,'Nova triagem rápida registrada','2026-11-04 14:57:16','Uma triagem rápida foi registrada. Acesse o sistema para consultar as informações autorizadas.',14,'Pendente','triagem'),(74,'Nova triagem psicológica registrada','2026-11-13 11:56:07','Uma triagem psicológica foi registrada. Acesse o sistema para consultar as informações autorizadas.',14,'Pendente','triagem'),(75,'Nova triagem registrada','2026-11-13 11:57:18','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',14,'Pendente','triagem'),(78,'Novo cadastro aguardando aprovação','2026-11-13 14:14:00','Há um cadastro de servidor aguardando análise.',14,'Pendente','conta');
/*!40000 ALTER TABLE `notificacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opcao_sistema`
--

DROP TABLE IF EXISTS `opcao_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opcao_sistema` (
  `opca_id` int(11) NOT NULL AUTO_INCREMENT,
  `opca_grupo` varchar(30) NOT NULL,
  `opca_valor` varchar(100) NOT NULL,
  `opca_ordem` int(11) NOT NULL DEFAULT 0,
  `opca_ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`opca_id`),
  UNIQUE KEY `uk_opcao_grupo_valor` (`opca_grupo`,`opca_valor`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opcao_sistema`
--

LOCK TABLES `opcao_sistema` WRITE;
/*!40000 ALTER TABLE `opcao_sistema` DISABLE KEYS */;
INSERT INTO `opcao_sistema` VALUES (1,'ano_turma','3º ano',3,1),(2,'ano_turma','2º ano',2,1),(3,'ano_turma','1º ano',1,1),(4,'curso','Técnico em informática',1,1),(5,'curso','Manutenção e Suporte em Informática',2,1),(6,'curso','Biotecnologia',3,1),(7,'curso','Enfermagem',4,1),(8,'curso','Licenciatura em Ciências',5,1),(9,'curso','Licenciatura em Biologia',6,1),(10,'turno','Matutino',1,1),(11,'turno','Vespertino',2,1),(12,'turno','Noturno',3,1),(13,'tipo_escola','Escola pública',1,1),(14,'tipo_escola','Escola privada',2,1),(15,'tipo_escola','Escola estadual',3,1),(16,'motivo_ifro','Qualidade do ensino',1,1),(17,'motivo_ifro','Proximidade da escola',2,1),(18,'motivo_ifro','Programa de bolsas',3,1),(19,'motivo_ifro','Recomendação de outros',4,1),(20,'acesso_saude','SUS',1,1),(21,'acesso_saude','Privado',2,1),(22,'acesso_saude','Convênio',3,1),(23,'queixa','Dor de cabeça',1,1),(24,'queixa','Cólica',2,1),(25,'queixa','Febre',3,1),(26,'queixa','Dor de barriga',4,1),(27,'queixa','Enjoo / vômito',5,1),(28,'queixa','Tontura / mal-estar',6,1),(29,'queixa','Dor de garganta',7,1),(30,'queixa','Gripe / resfriado',8,1),(31,'queixa','Machucado / ferimento',9,1),(32,'queixa','Alergia',10,1),(33,'queixa','Crise de ansiedade',11,1),(34,'queixa','Pressão alta / baixa',12,1),(35,'queixa','Outro',13,1),(36,'motivo_triagem_psicologica','Crise de ansiedade',1,1),(37,'motivo_triagem_psicologica','Ataque de pânico',2,1),(38,'motivo_triagem_psicologica','Choro intenso',3,1),(39,'motivo_triagem_psicologica','Nervosismo',4,1),(40,'motivo_triagem_psicologica','Outro',5,1),(41,'sintoma_triagem_psicologica','Falta de ar',1,1),(42,'sintoma_triagem_psicologica','Tontura',2,1),(43,'sintoma_triagem_psicologica','Tremores',3,1),(44,'sintoma_triagem_psicologica','Taquicardia',4,1),(45,'sintoma_triagem_psicologica','Choro intenso',5,1),(46,'sintoma_triagem_psicologica','Aperto no peito',6,1);
/*!40000 ALTER TABLE `opcao_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pergunta`
--

DROP TABLE IF EXISTS `pergunta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pergunta` (
  `perg_id` int(11) NOT NULL AUTO_INCREMENT,
  `perg_descricao` text NOT NULL,
  PRIMARY KEY (`perg_id`),
  KEY `ix_pergunta_descricao` (`perg_descricao`(100))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pergunta`
--

LOCK TABLES `pergunta` WRITE;
/*!40000 ALTER TABLE `pergunta` DISABLE KEYS */;
INSERT INTO `pergunta` VALUES (1,'Como você se sente em relação ao seu corpo?'),(2,'Como você tem se sentido no dia a dia?'),(3,'O que você mais gosta em você?'),(4,'O que você gosta de fazer no seu tempo livre?'),(5,'Qual é o seu maior objetivo para a vida?'),(6,'Você tem tido pensamentos que estão incomodando você?'),(7,'Você já iniciou sua vida sexual?'),(8,'Você utiliza preservativo nas relações sexuais?'),(9,'Por que você não utiliza preservativo?'),(10,'Você conhece as principais ISTs (Infecções Sexualmente Transmissíveis)?'),(11,'Com quantos anos você teve sua primeira menstruação?'),(12,'Você sente cólicas menstruais?'),(13,'Qual é a intensidade das suas cólicas de 0 a 10?'),(14,'Você utiliza algum medicamento para aliviar as cólicas?'),(15,'Qual medicamento você utiliza para aliviar as cólicas?'),(16,'Você já iniciou sua vida sexual?'),(17,'Você utiliza algum método contraceptivo?'),(18,'Por que você não utiliza método contraceptivo?'),(19,'Você já realizou uma consulta ginecológica?');
/*!40000 ALTER TABLE `pergunta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preferencia_notificacao`
--

DROP TABLE IF EXISTS `preferencia_notificacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preferencia_notificacao` (
  `serv_id` int(11) NOT NULL,
  `email_consultas` tinyint(1) NOT NULL DEFAULT 0,
  `email_triagens` tinyint(1) NOT NULL DEFAULT 0,
  `email_cadastros` tinyint(1) NOT NULL DEFAULT 0,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`serv_id`),
  CONSTRAINT `fk_preferencia_notificacao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preferencia_notificacao`
--

LOCK TABLES `preferencia_notificacao` WRITE;
/*!40000 ALTER TABLE `preferencia_notificacao` DISABLE KEYS */;
INSERT INTO `preferencia_notificacao` VALUES (9,1,1,0,'2026-10-25 00:39:35'),(14,0,0,0,'2026-11-13 12:59:01');
/*!40000 ALTER TABLE `preferencia_notificacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recuperacao_senha`
--

DROP TABLE IF EXISTS `recuperacao_senha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recuperacao_senha` (
  `rec_id` int(11) NOT NULL AUTO_INCREMENT,
  `serv_id` int(11) NOT NULL,
  `rec_token` varchar(255) NOT NULL,
  `rec_expira` datetime NOT NULL,
  `rec_usado` tinyint(1) DEFAULT 0,
  `rec_criado` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`rec_id`),
  KEY `fk_recuperacao_servidor` (`serv_id`),
  CONSTRAINT `fk_recuperacao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacao_senha`
--

LOCK TABLES `recuperacao_senha` WRITE;
/*!40000 ALTER TABLE `recuperacao_senha` DISABLE KEYS */;
/*!40000 ALTER TABLE `recuperacao_senha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `responsavel`
--

DROP TABLE IF EXISTS `responsavel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `responsavel` (
  `resp_id` int(11) NOT NULL AUTO_INCREMENT,
  `resp_nome` varchar(100) NOT NULL,
  PRIMARY KEY (`resp_id`),
  KEY `ix_responsavel_nome` (`resp_nome`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responsavel`
--

LOCK TABLES `responsavel` WRITE;
/*!40000 ALTER TABLE `responsavel` DISABLE KEYS */;
INSERT INTO `responsavel` VALUES (1,'Cláudia Souza Lima'),(8,'Eduardo Silva Carvalho'),(9,'Juliana Fernandes Moura'),(5,'Rogério Martins Ribeiro'),(12,'Sandra Castro Monteiro');
/*!40000 ALTER TABLE `responsavel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servidor`
--

DROP TABLE IF EXISTS `servidor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servidor` (
  `serv_id` int(11) NOT NULL AUTO_INCREMENT,
  `serv_nome` varchar(225) NOT NULL,
  `serv_siape` int(11) NOT NULL,
  `serv_email` varchar(225) NOT NULL,
  `serv_cpf` varchar(15) NOT NULL,
  `serv_dt_nascimento` date NOT NULL,
  `func_id` int(11) NOT NULL,
  `serv_foto` blob DEFAULT NULL,
  `serv_senha` varchar(300) NOT NULL,
  `serv_status` enum('ativo','inativo') NOT NULL DEFAULT 'inativo',
  `serv_perfil` enum('servidor','admin') NOT NULL DEFAULT 'servidor',
  `serv_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`serv_id`),
  UNIQUE KEY `serv_email_UNIQUE` (`serv_email`),
  UNIQUE KEY `uk_servidor_siape` (`serv_siape`),
  KEY `ix_servidor_cpf` (`serv_cpf`),
  KEY `ix_servidor_nome` (`serv_nome`),
  KEY `ix_servidor_email` (`serv_email`),
  KEY `fk_servidor_funcao` (`func_id`),
  CONSTRAINT `fk_servidor_funcao` FOREIGN KEY (`func_id`) REFERENCES `funcao` (`func_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servidor`
--

LOCK TABLES `servidor` WRITE;
/*!40000 ALTER TABLE `servidor` DISABLE KEYS */;
INSERT INTO `servidor` VALUES (9,'Mariane Cardoso',852963,'mari@gmail.com','111.222.333-44','2008-12-12',2,'perfil_9_1793621220.jpg','$2y$10$NPu.8HrPojEcxbzV3vECWeW6F5idWv84WZUaPl0FgvM2ivaeDT6ky','ativo','servidor','2026-10-24 23:46:12'),(14,'Admin',1,'enfermariaifro@gmail.com','000.000.000-01','2000-01-01',1,NULL,'$2y$10$M03KLdjd/4bjmxg9js/J6u9rOrBkoTGtXOCOrG89ctI5u9WhZ/cdq','ativo','admin','2026-10-24 20:01:41');
/*!40000 ALTER TABLE `servidor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessao_dispositivo`
--

DROP TABLE IF EXISTS `sessao_dispositivo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessao_dispositivo` (
  `sess_id` int(11) NOT NULL AUTO_INCREMENT,
  `serv_id` int(11) NOT NULL,
  `sess_token` char(64) NOT NULL,
  `sess_dispositivo` varchar(60) NOT NULL,
  `sess_navegador` varchar(60) NOT NULL,
  `sess_ip` varchar(45) NOT NULL,
  `sess_user_agent` varchar(500) DEFAULT NULL,
  `sess_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `sess_ultima_atividade` datetime NOT NULL DEFAULT current_timestamp(),
  `sess_ativa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`sess_id`),
  UNIQUE KEY `uk_sessao_token` (`sess_token`),
  KEY `ix_sessao_servidor` (`serv_id`,`sess_ativa`),
  CONSTRAINT `fk_sessao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessao_dispositivo`
--

LOCK TABLES `sessao_dispositivo` WRITE;
/*!40000 ALTER TABLE `sessao_dispositivo` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessao_dispositivo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telefone`
--

DROP TABLE IF EXISTS `telefone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telefone` (
  `tele_id` int(11) NOT NULL AUTO_INCREMENT,
  `tele_numero` varchar(15) NOT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `alun_id` int(11) DEFAULT NULL,
  `resp_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`tele_id`),
  KEY `fk_telefone_servidor_idx` (`serv_id`),
  KEY `fk_telefone_aluno1_idx` (`alun_id`),
  KEY `fk_telefone_responsavel1_idx` (`resp_id`),
  KEY `ix_telefone_numero` (`tele_numero`),
  CONSTRAINT `fk_telefone_aluno1` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`),
  CONSTRAINT `fk_telefone_responsavel1` FOREIGN KEY (`resp_id`) REFERENCES `responsavel` (`resp_id`),
  CONSTRAINT `fk_telefone_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telefone`
--

LOCK TABLES `telefone` WRITE;
/*!40000 ALTER TABLE `telefone` DISABLE KEYS */;
INSERT INTO `telefone` VALUES (14,'(25) 64561-2031',14,NULL,NULL),(23,'(69) 99248-2689',9,NULL,NULL),(25,'(69) 99231-4587',NULL,1,NULL),(26,'(69) 99814-2210',NULL,NULL,1),(33,'(69) 99602-8815',NULL,5,NULL),(34,'(69) 98365-4402',NULL,NULL,5),(39,'(69) 99167-4428',NULL,8,NULL),(40,'(69) 99538-1265',NULL,NULL,8),(41,'(69) 99824-7351',NULL,9,NULL),(42,'(69) 98471-2280',NULL,NULL,9),(47,'(69) 99741-2083',NULL,12,NULL),(48,'(69) 98603-7719',NULL,NULL,12);
/*!40000 ALTER TABLE `telefone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triagem`
--

DROP TABLE IF EXISTS `triagem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triagem` (
  `tria_id` int(11) NOT NULL AUTO_INCREMENT,
  `cons_id` int(11) DEFAULT NULL,
  `serv_id` int(11) NOT NULL,
  `alun_id` int(11) NOT NULL,
  `alte_id` int(11) DEFAULT NULL,
  `tria_resposta` text DEFAULT NULL,
  `tria_pergunta` text DEFAULT NULL,
  `tria_escala_dor` int(11) DEFAULT NULL,
  `tria_frequencia_cardiaca` int(11) DEFAULT NULL,
  `tria_pressao` int(11) DEFAULT NULL,
  `tria_temperatura` decimal(5,2) DEFAULT NULL,
  `tria_frequencia_resporatoria` int(11) DEFAULT NULL,
  `tria_historico_alergia` varchar(200) DEFAULT NULL,
  `tria_informacoes_adicionais` varchar(200) DEFAULT NULL,
  `tria_data` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tria_id`),
  KEY `fk_triagem_consulta1_idx` (`cons_id`),
  KEY `fk_triagem_servidor1_idx` (`serv_id`),
  KEY `fk_triagem_aluno1_idx` (`alun_id`),
  KEY `fk_triagem_alternativa1_idx` (`alte_id`),
  CONSTRAINT `fk_triagem_alternativa1` FOREIGN KEY (`alte_id`) REFERENCES `alternativa` (`alte_id`),
  CONSTRAINT `fk_triagem_aluno1` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`),
  CONSTRAINT `fk_triagem_consulta1` FOREIGN KEY (`cons_id`) REFERENCES `consulta` (`cons_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_triagem_servidor1` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triagem`
--

LOCK TABLES `triagem` WRITE;
/*!40000 ALTER TABLE `triagem` DISABLE KEYS */;
/*!40000 ALTER TABLE `triagem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triagem_psicologica`
--

DROP TABLE IF EXISTS `triagem_psicologica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triagem_psicologica` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triagem_psicologica`
--

LOCK TABLES `triagem_psicologica` WRITE;
/*!40000 ALTER TABLE `triagem_psicologica` DISABLE KEYS */;
INSERT INTO `triagem_psicologica` VALUES (1,9,9,'Crise de ansiedade','Média','[\"Falta de ar\",\"Taquicardia\",\"Tremores\"]','Começou antes da prova de matemática. Acalmou com exercícios de respiração.','2026-10-30 09:20:00');
/*!40000 ALTER TABLE `triagem_psicologica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triagem_psicologica_historico`
--

DROP TABLE IF EXISTS `triagem_psicologica_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triagem_psicologica_historico` (
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
  UNIQUE KEY `ux_trph_triagem` (`trps_id`),
  KEY `ix_trph_aluno` (`alun_id`,`trph_criado_em`),
  KEY `ix_trph_servidor` (`serv_id`),
  CONSTRAINT `fk_trph_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trph_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trph_triagem` FOREIGN KEY (`trps_id`) REFERENCES `triagem_psicologica` (`trps_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triagem_psicologica_historico`
--

LOCK TABLES `triagem_psicologica_historico` WRITE;
/*!40000 ALTER TABLE `triagem_psicologica_historico` DISABLE KEYS */;
INSERT INTO `triagem_psicologica_historico` VALUES (1,9,1,9,'sim','Transtorno de ansiedade generalizada','nao',NULL,'psicologico','sim','[\"escola\"]','boa','sim','as_vezes','Mãe também tem ansiedade. Faz terapia quinzenal pelo SUS.','2026-10-30 09:35:00');
/*!40000 ALTER TABLE `triagem_psicologica_historico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triagem_rapida`
--

DROP TABLE IF EXISTS `triagem_rapida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triagem_rapida` (
  `trap_id` int(11) NOT NULL AUTO_INCREMENT,
  `alun_id` int(11) NOT NULL,
  `serv_id` int(11) DEFAULT NULL,
  `trap_queixa` varchar(100) NOT NULL,
  `trap_gravidade` enum('Leve','Média','Grave') NOT NULL,
  `trap_relato_aluno` text DEFAULT NULL,
  `trap_observacao` text DEFAULT NULL,
  `trap_conduta` text NOT NULL,
  `trap_criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`trap_id`),
  KEY `ix_triagem_rapida_aluno` (`alun_id`,`trap_criado_em`),
  KEY `fk_triagem_rapida_servidor_idx` (`serv_id`),
  CONSTRAINT `fk_triagem_rapida_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_triagem_rapida_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triagem_rapida`
--

LOCK TABLES `triagem_rapida` WRITE;
/*!40000 ALTER TABLE `triagem_rapida` DISABLE KEYS */;
INSERT INTO `triagem_rapida` VALUES (3,1,9,'Outro','Média','Falta de ar depois da aula de educação física.','Chiado no peito, saturação 95%.','Usou a bombinha que trouxe de casa; ficou em observação 30 min e melhorou.','2026-10-06 10:20:00'),(5,8,9,'Tontura / mal-estar','Grave','Tremor e suor frio antes do almoço.','Glicemia capilar 58 mg/dL.','Dei suco com açúcar, glicemia subiu para 102; responsável avisado.','2026-10-21 11:40:00'),(6,5,9,'Cólica','Média','Cólica forte desde o início da aula.','Dor 7 de 10.','Bolsa de água morna e repouso; responsável autorizou buscá-la.','2026-10-28 14:10:00'),(8,12,9,'Outro','Grave','Colegas relataram que ele ficou desorientado e com tremores.','Crise convulsiva de cerca de 1 minuto; consciente depois.','Posicionado de lado, SAMU acionado e responsável avisado.','2026-11-06 10:50:00');
/*!40000 ALTER TABLE `triagem_rapida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turma`
--

DROP TABLE IF EXISTS `turma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `turma` (
  `turm_id` int(11) NOT NULL AUTO_INCREMENT,
  `turm_curso` varchar(50) NOT NULL,
  `turm_ano` varchar(10) NOT NULL,
  `turm_turno` varchar(25) NOT NULL,
  PRIMARY KEY (`turm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turma`
--

LOCK TABLES `turma` WRITE;
/*!40000 ALTER TABLE `turma` DISABLE KEYS */;
INSERT INTO `turma` VALUES (1,'Técnico em informática','1º ano','Matutino'),(2,'Técnico em informática','2º ano','Matutino'),(3,'Técnico em informática','3º ano','Matutino'),(4,'Biotecnologia','1º ano','Vespertino'),(5,'Biotecnologia','2º ano','Vespertino'),(6,'Enfermagem','3º ano','Vespertino'),(7,'Manutenção e Suporte em Informática','2º ano','Matutino');
/*!40000 ALTER TABLE `turma` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
