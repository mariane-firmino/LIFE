CREATE DATABASE  IF NOT EXISTS `bd_life` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `bd_life`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: bd_life
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alternativa` (
  `alte_id` int NOT NULL AUTO_INCREMENT,
  `alte_descricao` text NOT NULL,
  `perg_id` int NOT NULL,
  PRIMARY KEY (`alte_id`),
  KEY `fk_alternativa_pergunta1_idx` (`perg_id`),
  KEY `ix_alternativa_descricao` (`alte_descricao`(100)),
  CONSTRAINT `fk_alternativa_pergunta1` FOREIGN KEY (`perg_id`) REFERENCES `pergunta` (`perg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb3;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aluno` (
  `alun_id` int NOT NULL AUTO_INCREMENT,
  `alun_nome` varchar(225) NOT NULL,
  `alun_dt_nascimento` date NOT NULL,
  `alun_sexo` varchar(25) NOT NULL,
  `alun_matricula` varchar(15) NOT NULL,
  `alun_rg` varchar(6) DEFAULT NULL,
  `alun_cartao_sus` varchar(15) DEFAULT NULL,
  `alun_foto` blob,
  `alun_tipo_sanguineo` varchar(3) DEFAULT NULL,
  `alun_altura` decimal(3,2) DEFAULT NULL,
  `alun_peso` decimal(5,2) DEFAULT NULL,
  `alun_idade` int DEFAULT NULL,
  `resp_id` int NOT NULL,
  `turm_id` int NOT NULL,
  `alun_data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alun_atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `alun_info_extras` text,
  PRIMARY KEY (`alun_id`),
  UNIQUE KEY `alun_matricula_UNIQUE` (`alun_matricula`),
  UNIQUE KEY `alun_rg_UNIQUE` (`alun_rg`),
  KEY `fk_aluno_responsavel1_idx` (`resp_id`),
  KEY `fk_aluno_turma1_idx` (`turm_id`),
  KEY `ix_aluno_nome` (`alun_nome`),
  CONSTRAINT `fk_aluno_responsavel1` FOREIGN KEY (`resp_id`) REFERENCES `responsavel` (`resp_id`),
  CONSTRAINT `fk_aluno_turma1` FOREIGN KEY (`turm_id`) REFERENCES `turma` (`turm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aluno`
--

LOCK TABLES `aluno` WRITE;
/*!40000 ALTER TABLE `aluno` DISABLE KEYS */;
INSERT INTO `aluno` VALUES (1,'Teste De Aluno','2010-02-01','Feminino','1234563256145',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2026-10-01 17:31:18',NULL,NULL),(2,'Lua Azul','2009-12-03','Feminino','1564156132265',NULL,NULL,_binary 'af98f2c56a4327f940e22c731b3d7d05.jpg',NULL,1.70,60.00,NULL,2,2,'2026-10-24 11:20:52',NULL,NULL);
/*!40000 ALTER TABLE `aluno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aluno_anotacao`
--

DROP TABLE IF EXISTS `aluno_anotacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aluno_anotacao` (
  `anot_id` int NOT NULL AUTO_INCREMENT,
  `alun_id` int NOT NULL,
  `anot_tipo` enum('rapida','psicologica') COLLATE utf8mb4_general_ci NOT NULL,
  `anot_texto` text COLLATE utf8mb4_general_ci NOT NULL,
  `serv_id` int DEFAULT NULL,
  `anot_criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`anot_id`),
  KEY `ix_anotacao_aluno_tipo` (`alun_id`,`anot_tipo`),
  KEY `fk_anotacao_servidor_idx` (`serv_id`),
  CONSTRAINT `fk_anotacao_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anotacao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aluno_anotacao`
--

LOCK TABLES `aluno_anotacao` WRITE;
/*!40000 ALTER TABLE `aluno_anotacao` DISABLE KEYS */;
/*!40000 ALTER TABLE `aluno_anotacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aluno_documento`
--

DROP TABLE IF EXISTS `aluno_documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aluno_documento` (
  `docu_id` int NOT NULL AUTO_INCREMENT,
  `alun_id` int NOT NULL,
  `docu_tipo` enum('vacina','sus','laudo') COLLATE utf8mb4_general_ci NOT NULL,
  `docu_titulo` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `docu_descricao` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `docu_data_documento` date DEFAULT NULL,
  `docu_arquivo` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `docu_nome_original` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `docu_mime` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `docu_tamanho` int NOT NULL,
  `serv_id` int DEFAULT NULL,
  `docu_criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anamnese` (
  `anamn_id` int NOT NULL AUTO_INCREMENT,
  `alun_id` int NOT NULL,
  `anam_tipo_escola` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_motivo_ifro` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_ja_reprovou` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_quantas_vezes` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_estuda_fora` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_quanto_tempo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_nota_curso` tinyint DEFAULT NULL,
  `anam_nota_motivacao` tinyint DEFAULT NULL,
  `anam_atendimento` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_atendimento_porque` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_acesso_saude` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_alergia_alimentar` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_alergia_qual` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_doenca_cronica` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_doenca_qual` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_medicacao_continua` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_medicacao_qual` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_acompanhamento_psi` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_cartao_vacina` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_ja_internado` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_internado_motivo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_ja_cirurgia` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_cirurgia_qual` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_padrao_sono` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_refeicoes_dia` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_alimentacao_habitual` text COLLATE utf8mb4_general_ci,
  `anam_fuma` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_bebida_alcoolica` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_pais_sabem` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_drogas_ilicitas` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_drogas_qual` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_atividade_fisica` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_frequencia_atividade` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_banhos_dia` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_condicoes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anam_condicao_outra` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`anamn_id`),
  KEY `fk_anamnese_aluno` (`alun_id`),
  CONSTRAINT `fk_anamnese_aluno` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anamnese`
--

LOCK TABLES `anamnese` WRITE;
/*!40000 ALTER TABLE `anamnese` DISABLE KEYS */;
INSERT INTO `anamnese` VALUES (1,1,'Escola pública','Qualidade do ensino','Não','0','Sim','5',10,8,'Sim',NULL,'Privado','Não','poeira','Não','nop','Não','oi','Não','Sim','Não','testee','Sim','Alguma aí','Restaurador','3','arroz, carne e tals','Não','Não','Sim','Não','nopp','Sim','3x por semana','3 por dia',NULL,NULL),(2,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'TDAH',NULL);
/*!40000 ALTER TABLE `anamnese` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consulta`
--

DROP TABLE IF EXISTS `consulta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta` (
  `cons_id` int NOT NULL AUTO_INCREMENT,
  `cons_data` date NOT NULL,
  `cons_horario` time NOT NULL,
  `cons_tipo` varchar(100) NOT NULL,
  `cons_responsavel` varchar(60) DEFAULT NULL,
  `cons_descricao` text,
  `cons_observacao` text,
  PRIMARY KEY (`cons_id`),
  KEY `ix_consulta_data` (`cons_data`),
  KEY `ix_consulta_hora` (`cons_horario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta`
--

LOCK TABLES `consulta` WRITE;
/*!40000 ALTER TABLE `consulta` DISABLE KEYS */;
INSERT INTO `consulta` VALUES (1,'2026-09-22','14:00:00','Consulta - Periódica',NULL,'çkjdjfnkladnfga','wefWGFerg'),(2,'2026-10-30','14:52:00','Consulta - Periódica','Enfermeira','testee','Teste'),(3,'2026-10-30','14:52:00','Consulta - Periódica','Enfermeira','testee','Teste'),(4,'2026-10-18','15:23:00','Consulta - Periódica','Enfermeira','teste notificacao','oiiii'),(5,'2026-10-18','15:23:00','Consulta - Periódica','Enfermeira','teste notificacao','oiiii');
/*!40000 ALTER TABLE `consulta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcao`
--

DROP TABLE IF EXISTS `funcao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcao` (
  `func_id` int NOT NULL AUTO_INCREMENT,
  `func_nome` varchar(100) NOT NULL,
  PRIMARY KEY (`func_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacao` (
  `noti_id` int NOT NULL AUTO_INCREMENT,
  `noti_titulo` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `noti_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `noti_descricao` text COLLATE utf8mb4_general_ci,
  `serv_id` int DEFAULT NULL,
  `noti_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'Não lido',
  `noti_tipo` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`noti_id`),
  KEY `fk_notificacao` (`serv_id`),
  CONSTRAINT `fk_notificacao` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacao`
--

LOCK TABLES `notificacao` WRITE;
/*!40000 ALTER TABLE `notificacao` DISABLE KEYS */;
INSERT INTO `notificacao` VALUES (9,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',2,'Pendente','consulta'),(10,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',6,'Pendente','consulta'),(11,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',7,'Pendente','consulta'),(12,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',10,'Pendente','consulta'),(13,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',11,'Pendente','consulta'),(14,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',12,'Pendente','consulta'),(15,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',13,'Pendente','consulta'),(16,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',14,'Pendente','consulta'),(17,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',2,'Pendente','consulta'),(18,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',6,'Pendente','consulta'),(19,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',7,'Pendente','consulta'),(20,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',10,'Pendente','consulta'),(21,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',11,'Pendente','consulta'),(22,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',12,'Pendente','consulta'),(23,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',13,'Pendente','consulta'),(24,'Nova consulta na agenda','2026-10-25 00:38:36','Uma consulta foi adicionada à agenda. Acesse o calendário para ver os detalhes.',14,'Pendente','consulta'),(25,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',2,'Pendente','triagem'),(26,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',6,'Pendente','triagem'),(27,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',7,'Pendente','triagem'),(28,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',10,'Pendente','triagem'),(29,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',11,'Pendente','triagem'),(30,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',12,'Pendente','triagem'),(31,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',13,'Pendente','triagem'),(32,'Nova triagem registrada','2026-10-25 00:40:37','Uma nova triagem foi registrada. Acesse o sistema para consultar as informações autorizadas.',14,'Pendente','triagem');
/*!40000 ALTER TABLE `notificacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opcao_sistema`
--

DROP TABLE IF EXISTS `opcao_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcao_sistema` (
  `opca_id` int NOT NULL AUTO_INCREMENT,
  `opca_grupo` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `opca_valor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `opca_ordem` int NOT NULL DEFAULT '0',
  `opca_ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`opca_id`),
  UNIQUE KEY `uk_opcao_grupo_valor` (`opca_grupo`,`opca_valor`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opcao_sistema`
--

LOCK TABLES `opcao_sistema` WRITE;
/*!40000 ALTER TABLE `opcao_sistema` DISABLE KEYS */;
INSERT INTO `opcao_sistema` VALUES (1,'ano_turma','24',1,1),(2,'ano_turma','25',2,1),(3,'ano_turma','26',3,1),(4,'curso','Técnico em informática',1,1),(5,'curso','Manutenção e Suporte em Informática',2,1),(6,'curso','Biotecnologia',3,1),(7,'curso','Enfermagem',4,1),(8,'curso','Licenciatura em Ciências',5,1),(9,'curso','Licenciatura em Biologia',6,1),(10,'turno','Matutino',1,1),(11,'turno','Vespertino',2,1),(12,'turno','Noturno',3,1),(13,'tipo_escola','Escola pública',1,1),(14,'tipo_escola','Escola privada',2,1),(15,'tipo_escola','Escola estadual',3,1),(16,'motivo_ifro','Qualidade do ensino',1,1),(17,'motivo_ifro','Proximidade da escola',2,1),(18,'motivo_ifro','Programa de bolsas',3,1),(19,'motivo_ifro','Recomendação de outros',4,1),(20,'acesso_saude','SUS',1,1),(21,'acesso_saude','Privado',2,1),(22,'acesso_saude','Convênio',3,1);
/*!40000 ALTER TABLE `opcao_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pergunta`
--

DROP TABLE IF EXISTS `pergunta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pergunta` (
  `perg_id` int NOT NULL AUTO_INCREMENT,
  `perg_descricao` text NOT NULL,
  PRIMARY KEY (`perg_id`),
  KEY `ix_pergunta_descricao` (`perg_descricao`(100))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preferencia_notificacao` (
  `serv_id` int NOT NULL,
  `email_consultas` tinyint(1) NOT NULL DEFAULT '0',
  `email_triagens` tinyint(1) NOT NULL DEFAULT '0',
  `email_cadastros` tinyint(1) NOT NULL DEFAULT '0',
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`serv_id`),
  CONSTRAINT `fk_preferencia_notificacao_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preferencia_notificacao`
--

LOCK TABLES `preferencia_notificacao` WRITE;
/*!40000 ALTER TABLE `preferencia_notificacao` DISABLE KEYS */;
INSERT INTO `preferencia_notificacao` VALUES (9,1,1,0,'2026-10-25 00:39:35');
/*!40000 ALTER TABLE `preferencia_notificacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recuperacao_senha`
--

DROP TABLE IF EXISTS `recuperacao_senha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recuperacao_senha` (
  `rec_id` int NOT NULL AUTO_INCREMENT,
  `serv_id` int NOT NULL,
  `rec_token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rec_expira` datetime NOT NULL,
  `rec_usado` tinyint(1) DEFAULT '0',
  `rec_criado` datetime DEFAULT CURRENT_TIMESTAMP,
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
INSERT INTO `recuperacao_senha` VALUES (2,13,'11391f39bbf03d9b2cb0943ddd026eaba1ff03aac8582fd9b816e75bfee9309f','2026-10-01 23:46:22',0,'2026-10-01 17:16:22'),(3,12,'2b47e44c5d7d88ee2ec13c16f57a57538a49bbbccd59ca940aef78fb6c303a62','2026-10-24 15:35:03',0,'2026-10-24 09:05:03');
/*!40000 ALTER TABLE `recuperacao_senha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `responsavel`
--

DROP TABLE IF EXISTS `responsavel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `responsavel` (
  `resp_id` int NOT NULL AUTO_INCREMENT,
  `resp_nome` varchar(100) NOT NULL,
  PRIMARY KEY (`resp_id`),
  KEY `ix_responsavel_nome` (`resp_nome`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responsavel`
--

LOCK TABLES `responsavel` WRITE;
/*!40000 ALTER TABLE `responsavel` DISABLE KEYS */;
INSERT INTO `responsavel` VALUES (2,'Lua Cheia'),(1,'Resp Legal do Aluno');
/*!40000 ALTER TABLE `responsavel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servidor`
--

DROP TABLE IF EXISTS `servidor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servidor` (
  `serv_id` int NOT NULL AUTO_INCREMENT,
  `serv_nome` varchar(225) NOT NULL,
  `serv_siape` int NOT NULL,
  `serv_email` varchar(225) NOT NULL,
  `serv_cpf` varchar(15) NOT NULL,
  `serv_dt_nascimento` date NOT NULL,
  `func_id` int NOT NULL,
  `serv_foto` blob,
  `serv_senha` varchar(300) NOT NULL,
  `serv_status` enum('ativo','inativo') NOT NULL DEFAULT 'inativo',
  `serv_perfil` enum('servidor','admin') NOT NULL DEFAULT 'servidor',
  `serv_criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`serv_id`),
  UNIQUE KEY `serv_email_UNIQUE` (`serv_email`),
  UNIQUE KEY `uk_servidor_siape` (`serv_siape`),
  KEY `ix_servidor_cpf` (`serv_cpf`),
  KEY `ix_servidor_nome` (`serv_nome`),
  KEY `ix_servidor_email` (`serv_email`),
  KEY `fk_servidor_funcao` (`func_id`),
  CONSTRAINT `fk_servidor_funcao` FOREIGN KEY (`func_id`) REFERENCES `funcao` (`func_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servidor`
--

LOCK TABLES `servidor` WRITE;
/*!40000 ALTER TABLE `servidor` DISABLE KEYS */;
INSERT INTO `servidor` VALUES (2,'Mariane Cardoso',123456,'mariane@gmail.com','045.652.632-38','2008-12-12',1,NULL,'$2y$10$gTmzNise7cHCyVyQGx6aZOlOe53uHQhHUBPLP61s.g.H8pT0Thsi.','ativo','servidor','2026-10-24 23:46:12'),(6,'gustavo',1234567,'gustavo@gmail.com','070.090.612-62','2025-06-26',1,NULL,'$2y$10$prYKDQaKEeJeu.q.nZUeT.iQ1eIDPKdgq0sqZ/.fjxvrCIYwH.8sy','ativo','servidor','2026-10-24 23:46:12'),(7,'flavia rodrigues de araujo durães',3433608,'flavia.duraes@ifro.edu.br','631.532.202-63','1978-10-28',1,NULL,'$2y$10$h/pr.bWJnh2x7r0ekwMRrOvpQsYuP5TsUY8ZUBLGNC1vMN08GJHue','ativo','servidor','2026-10-24 23:46:12'),(9,'Mariane Cardoso',852963,'mari@gmail.com','111.222.333-44','2008-12-12',2,_binary 'perfil_9_1792856679.jpg','$2y$10$NPu.8HrPojEcxbzV3vECWeW6F5idWv84WZUaPl0FgvM2ivaeDT6ky','ativo','servidor','2026-10-24 23:46:12'),(10,'testee',369852,'teste@gmail.com','111.222.333-44','2000-02-05',2,NULL,'$2y$10$o48AbXjXFvplp3TkHl98LOQuXQX0wXdI7k.PmI3UFREmC7QNRWXd6','ativo','servidor','2026-10-24 23:46:12'),(11,'Laís',2218736,'laislinda@gmail.com','000.000.000-00','2008-02-01',1,NULL,'$2y$10$dd7ynb0e5Hm/ZDuLabGRPOA4zUs5KjV3Ezo9MXvPe/yvmlmMOAWHq','ativo','servidor','2026-10-24 23:46:12'),(12,'MARI',2352353,'cardosomariane451@gmail.com','342.323.252-52','2008-06-16',2,NULL,'$2y$10$y.QdXBowMf8bFyNCRd.GSOAlT.71reN05dQxmaylv6TO0XScZqaSa','ativo','servidor','2026-10-24 23:46:12'),(13,'wdasddvcsdv',4353443,'rafaelcaldeirasan@gmail.com','214.321.534-54','2007-01-01',1,NULL,'$2y$10$lX461G0Du5fkP6UjbDzXhOtWn1xS2jGSQ9Txnsex9afo.lN2F.AIu','ativo','servidor','2026-10-24 23:46:12'),(14,'Admin',1,'admin@ifro.edu.br','000.000.000-01','2000-01-01',1,NULL,'$2y$10$M03KLdjd/4bjmxg9js/J6u9rOrBkoTGtXOCOrG89ctI5u9WhZ/cdq','ativo','admin','2026-10-24 20:01:41');
/*!40000 ALTER TABLE `servidor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telefone`
--

DROP TABLE IF EXISTS `telefone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telefone` (
  `tele_id` int NOT NULL AUTO_INCREMENT,
  `tele_numero` varchar(15) NOT NULL,
  `serv_id` int DEFAULT NULL,
  `alun_id` int DEFAULT NULL,
  `resp_id` int DEFAULT NULL,
  PRIMARY KEY (`tele_id`),
  KEY `fk_telefone_servidor_idx` (`serv_id`),
  KEY `fk_telefone_aluno1_idx` (`alun_id`),
  KEY `fk_telefone_responsavel1_idx` (`resp_id`),
  KEY `ix_telefone_numero` (`tele_numero`),
  CONSTRAINT `fk_telefone_aluno1` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`),
  CONSTRAINT `fk_telefone_responsavel1` FOREIGN KEY (`resp_id`) REFERENCES `responsavel` (`resp_id`),
  CONSTRAINT `fk_telefone_servidor` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telefone`
--

LOCK TABLES `telefone` WRITE;
/*!40000 ALTER TABLE `telefone` DISABLE KEYS */;
INSERT INTO `telefone` VALUES (1,'(96) 99926-2045',2,NULL,NULL),(3,'(69) 98423-4216',6,NULL,NULL),(4,'(69) 99600-4977',7,NULL,NULL),(5,'(69) 99375-9393',9,NULL,NULL),(6,'96 99965-25363',NULL,1,NULL),(7,'65 32365-5896',NULL,NULL,1),(8,'(96) 32145-6987',10,NULL,NULL),(9,'(99) 99999-9999',11,NULL,NULL),(10,'(23) 43423-5254',12,NULL,NULL),(11,'(55) 34345-4335',13,NULL,NULL),(12,'(65) 15656-1515',NULL,2,NULL),(13,'(55) 6555-4548',NULL,NULL,2),(14,'(25) 64561-2031',14,NULL,NULL);
/*!40000 ALTER TABLE `telefone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triagem`
--

DROP TABLE IF EXISTS `triagem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `triagem` (
  `tria_id` int NOT NULL AUTO_INCREMENT,
  `cons_id` int DEFAULT NULL,
  `serv_id` int NOT NULL,
  `alun_id` int NOT NULL,
  `alte_id` int DEFAULT NULL,
  `tria_resposta` text,
  `tria_pergunta` text,
  `tria_escala_dor` int DEFAULT NULL,
  `tria_frequencia_cardiaca` int DEFAULT NULL,
  `tria_pressao` int DEFAULT NULL,
  `tria_temperatura` decimal(5,2) DEFAULT NULL,
  `tria_frequencia_resporatoria` int DEFAULT NULL,
  `tria_historico_alergia` varchar(200) DEFAULT NULL,
  `tria_informacoes_adicionais` varchar(200) DEFAULT NULL,
  `tria_data` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tria_id`),
  KEY `fk_triagem_consulta1_idx` (`cons_id`),
  KEY `fk_triagem_servidor1_idx` (`serv_id`),
  KEY `fk_triagem_aluno1_idx` (`alun_id`),
  KEY `fk_triagem_alternativa1_idx` (`alte_id`),
  CONSTRAINT `fk_triagem_alternativa1` FOREIGN KEY (`alte_id`) REFERENCES `alternativa` (`alte_id`),
  CONSTRAINT `fk_triagem_aluno1` FOREIGN KEY (`alun_id`) REFERENCES `aluno` (`alun_id`),
  CONSTRAINT `fk_triagem_consulta1` FOREIGN KEY (`cons_id`) REFERENCES `consulta` (`cons_id`),
  CONSTRAINT `fk_triagem_servidor1` FOREIGN KEY (`serv_id`) REFERENCES `servidor` (`serv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triagem`
--

LOCK TABLES `triagem` WRITE;
/*!40000 ALTER TABLE `triagem` DISABLE KEYS */;
INSERT INTO `triagem` VALUES (2,NULL,9,2,NULL,'Satisfeito','Quando você olha no espelho, você fica:',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(3,NULL,9,2,NULL,'Motivado','Diariamente você se sente:',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(4,NULL,9,2,NULL,'tudo','O que você mais gosta em si mesmo?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(5,NULL,9,2,NULL,'ler','Qual é o seu hobby? (O que você mais gosta de fazer?)',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(6,NULL,9,2,NULL,'Sim','Você possui algum objetivo de vida?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(7,NULL,9,2,NULL,'Bons / Motivadores / Esperançosos','Geralmente, como são seus pensamentos?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(8,NULL,9,2,NULL,'Sim','Você já teve a sua primeira menstruação?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(9,NULL,9,2,NULL,'Não','Durante seu período menstrual, você sente cólicas?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(10,NULL,9,2,NULL,'2 -','Qual a intensidade das cólicas?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(11,NULL,9,2,NULL,'Não','Costuma tomar alguma medicação para cólicas?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37'),(12,NULL,9,2,NULL,'Não','Você já foi em alguma consulta Ginecológica?',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:40:37');
/*!40000 ALTER TABLE `triagem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turma`
--

DROP TABLE IF EXISTS `turma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turma` (
  `turm_id` int NOT NULL AUTO_INCREMENT,
  `turm_curso` varchar(50) NOT NULL,
  `turm_ano` varchar(2) NOT NULL,
  `turm_turno` varchar(25) NOT NULL,
  PRIMARY KEY (`turm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turma`
--

LOCK TABLES `turma` WRITE;
/*!40000 ALTER TABLE `turma` DISABLE KEYS */;
INSERT INTO `turma` VALUES (1,'Manutenção e Suporte em Informática','26','Matutino'),(2,'Licenciatura em Ciências','26','Vespertino');
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

-- Dump completed on 2026-10-24 20:46:54
