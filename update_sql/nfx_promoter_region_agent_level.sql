/*
Navicat MySQL Data Transfer

Source Server         : 192.168.1.234
Source Server Version : 50717
Source Host           : 192.168.1.234:3306
Source Database       : lingshanshop

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2018-03-28 15:20:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for nfx_promoter_region_agent_level
-- ----------------------------
DROP TABLE IF EXISTS `nfx_promoter_region_agent_level`;
CREATE TABLE `nfx_promoter_region_agent_level` (
  `level_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '代理商等级ID',
  `level_name` varchar(50) DEFAULT NULL COMMENT '等级名称',
  `level_rate` decimal(10,2) DEFAULT NULL COMMENT '管理费比率',
  `level_money` decimal(10,2) DEFAULT NULL COMMENT '升级钱数条件',
  `level_num` int(11) unsigned DEFAULT NULL COMMENT '升级人数条件',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
