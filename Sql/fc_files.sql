/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : files

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 21/08/2020 10:48:12
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for fc_files
-- ----------------------------
DROP TABLE IF EXISTS `fc_files`;
CREATE TABLE `fc_files`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `path` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '附件路径',
  `type` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件类型',
  `file_md5` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '附件md5',
  `file_size` int(10) NOT NULL COMMENT '文件大小',
  `file_total` int(5) NOT NULL COMMENT '文件总片数',
  `file_index` int(5) NOT NULL COMMENT '当前片数',
  `file_down_count` int(5) NULL DEFAULT 0 COMMENT '文件下载次数',
  `down_time` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '下载时间',
  `down_pass` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '下载密码',
  `cdat` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '上传时间',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文件名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 0 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
