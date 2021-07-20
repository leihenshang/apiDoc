-- MySQL dump 10.13  Distrib 5.5.62, for Win64 (AMD64)
--
-- Host: 120.27.241.94    Database: api_doc_dev
-- ------------------------------------------------------
-- Server version	5.7.32-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `api`
--

DROP TABLE IF EXISTS `api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '组id',
  `project_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '项目id',
  `description` varchar(500) DEFAULT NULL COMMENT '备注',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常，1删除',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT NULL,
  `group_id_second` int(11) NOT NULL DEFAULT '0' COMMENT '二级分组',
  `protocol_type` varchar(100) DEFAULT NULL COMMENT '协议',
  `http_method_type` varchar(100) DEFAULT NULL COMMENT 'Http请求类型',
  `url` varchar(1000) DEFAULT NULL COMMENT 'url',
  `api_name` varchar(500) DEFAULT NULL COMMENT '名称',
  `object_name` varchar(100) DEFAULT NULL COMMENT '对象名',
  `function_name` varchar(100) DEFAULT NULL COMMENT '方法名',
  `develop_language` varchar(100) DEFAULT NULL COMMENT '开发语言',
  `http_request_header` longtext COMMENT 'http头',
  `http_request_params` longtext COMMENT 'http请求参数',
  `http_return_type` varchar(100) DEFAULT NULL COMMENT 'http请求返回值',
  `http_return_sample` longtext COMMENT 'http响应数据样例',
  `http_return_params` longtext COMMENT 'http请求响应参数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8mb4 COMMENT='api表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `database`
--

DROP TABLE IF EXISTS `database`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(50) NOT NULL COMMENT '数据库地址',
  `port` int(10) unsigned NOT NULL COMMENT '端口号',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `database_name` varchar(50) NOT NULL COMMENT '数据库名称',
  `project_id` int(11) NOT NULL COMMENT '项目ID',
  `is_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 0 未删除 1 已删除',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '数据库类型,1-mysql',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `database_name` (`database_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='项目数据库连接表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `doc`
--

DROP TABLE IF EXISTS `doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1删除',
  `project_id` int(11) NOT NULL DEFAULT '0' COMMENT '属于哪个项目',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '文档内容',
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1审核中2禁用',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT NULL,
  `view_count` int(11) NOT NULL DEFAULT '0',
  `like_count` int(11) NOT NULL DEFAULT '0',
  `is_top` bit(1) NOT NULL DEFAULT b'0',
  `priority` int(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `field_mapping`
--

DROP TABLE IF EXISTS `field_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_mapping` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `field` varchar(100) NOT NULL COMMENT '字段',
  `type` varchar(100) NOT NULL COMMENT '类型',
  `description` varchar(100) DEFAULT NULL COMMENT '描述',
  `is_deleted` varchar(100) NOT NULL DEFAULT '0' COMMENT '0正常1删除',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `project_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COMMENT='字段映射表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `path` bigint(20) unsigned DEFAULT '0' COMMENT '项目id',
  `is_deleted` tinyint(4) DEFAULT '0',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `p_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `project_id` bigint(20) unsigned DEFAULT '0' COMMENT '项目id',
  `title` varchar(100) NOT NULL COMMENT '组名',
  `is_deleted` tinyint(4) DEFAULT '0',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `priority` int(11) NOT NULL DEFAULT '0' COMMENT '优先级',
  `type` tinyint(4) DEFAULT '0' COMMENT '1api,2project,3doc,0undefined',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COMMENT='分组表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `content` varchar(500) DEFAULT NULL COMMENT '内容',
  `send_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0默认1手机2邮箱',
  `receive_source` varchar(100) NOT NULL COMMENT '发送方地址',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `used_time` datetime DEFAULT NULL COMMENT '使用时间',
  `is_used` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1已使用',
  `code` varchar(100) DEFAULT NULL COMMENT '验证码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `content` varchar(500) DEFAULT NULL COMMENT '内容',
  `send_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0默认1系统',
  `receiver` int(11) NOT NULL COMMENT '接收者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未读1已读',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `operation_log`
--

DROP TABLE IF EXISTS `operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `action` varchar(100) NOT NULL COMMENT '动作',
  `content` varchar(500) DEFAULT NULL COMMENT '操作内容',
  `object_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目id',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1项目2分组3api4用户5文档',
  `project_id` int(11) NOT NULL COMMENT '项目id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=339 DEFAULT CHARSET=utf8mb4 COMMENT='操作日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '项目标题',
  `description` varchar(100) DEFAULT NULL COMMENT '项目描述',
  `is_deleted` varchar(100) NOT NULL DEFAULT '0' COMMENT '0正常1删除',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `version` varchar(100) NOT NULL DEFAULT '0' COMMENT '版本',
  `type` varchar(100) DEFAULT NULL COMMENT '类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COMMENT='项目表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_log`
--

DROP TABLE IF EXISTS `project_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1删除',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `action` varchar(100) NOT NULL COMMENT 'create,update,delete,query',
  `project_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目id',
  `description` varchar(100) NOT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `property`
--

DROP TABLE IF EXISTS `property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `property` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) NOT NULL COMMENT '标记名',
  `group` varchar(100) NOT NULL DEFAULT 'default' COMMENT '分组',
  `tag_name` varchar(100) NOT NULL COMMENT '标记名',
  `description` varchar(100) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '登录账号名不可更改',
  `pwd` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1删除',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '1普通用户2管理员3超级管理员',
  `state` int(11) NOT NULL DEFAULT '1' COMMENT '1正常2禁用',
  `mobile_number` char(11) DEFAULT NULL COMMENT '手机号',
  `nick_name` varchar(100) DEFAULT NULL COMMENT '展示昵称可以更改',
  `last_login_ip` varchar(100) DEFAULT NULL COMMENT '最后登陆ip',
  `last_login_time` datetime DEFAULT NULL,
  `user_face` varchar(100) DEFAULT NULL COMMENT '头像地址',
  `token` varchar(100) DEFAULT NULL COMMENT '访问token',
  `token_expire_time` datetime DEFAULT NULL COMMENT 'token过期时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_info_UN1` (`name`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_project`
--

DROP TABLE IF EXISTS `user_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `is_leader` tinyint(4) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常1删除',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `permission` tinyint(4) NOT NULL DEFAULT '6' COMMENT '4读2写',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COMMENT='团队项目表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'api_doc_dev'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-07-18 14:48:17
