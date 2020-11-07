/*
SQLyog 企业版 - MySQL GUI v7.14 
MySQL - 5.7.17-log : Database - lingshanshop
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`lingshanshop` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `lingshanshop`;

/*Table structure for table `sys_user_token` */

DROP TABLE IF EXISTS `sys_user_token`;

CREATE TABLE `sys_user_token` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户id',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT ' 过期时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `token` varchar(64) NOT NULL DEFAULT '' COMMENT 'token',
  `device_type` varchar(10) NOT NULL DEFAULT '' COMMENT '设备类型;mobile,android,iphone,ipad,web,pc,mac,wxapp',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户客户端登录 token 表';

ALTER TABLE `ns_order`
DROP COLUMN `touid`,
ADD COLUMN `touid`  int(10) NULL AFTER `fixed_telephone`;

ALTER TABLE `ns_order`
MODIFY COLUMN `tostatus`  int(10) NULL DEFAULT 0 COMMENT '转售状态，0代转售，1转售中，2已转售' AFTER `tocreate_time`;

ALTER TABLE `ns_order`
ADD COLUMN `tocreate_time`  int(11) NULL COMMENT '转售时间' AFTER `tostatus`;

ALTER TABLE `sys_user`
MODIFY COLUMN `pay_password`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 0 COMMENT '用户支付密码' AFTER `pid`;

ALTER TABLE `nfx_shop_config`
DROP COLUMN `protection`,
ADD COLUMN `protection`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否开启父级id30天保护期' AFTER `distribution_use`;
ALTER TABLE `nfx_promoter`
ADD COLUMN `updatepid_time`  int(11) NULL COMMENT '父级id修改时间' AFTER `promoter_path`;
ALTER TABLE `nfx_promoter`
DROP COLUMN `is_yes`,
ADD COLUMN `is_yes`  int(4) NULL DEFAULT 0 COMMENT '是否锁死，30天保护期，1锁住' AFTER `updatepid_time`;

ALTER TABLE `nfx_promoter_region_agent`
DROP COLUMN `id_level`,
ADD COLUMN `id_level`  int(10) NULL COMMENT '关联等级id' AFTER `agent_address`;
