/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100122
 Source Host           : 192.168.10.4:3306
 Source Schema         : edu-project

 Target Server Type    : MySQL
 Target Server Version : 100122
 File Encoding         : 65001

 Date: 30/05/2018 17:29:46
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for store_express
-- ----------------------------
DROP TABLE IF EXISTS `store_express`;
CREATE TABLE `store_express` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `express_title` varchar(50) DEFAULT '' COMMENT '快递公司名称',
  `express_code` varchar(50) DEFAULT '' COMMENT '快递公司代码',
  `express_desc` text COMMENT '快递公司描述',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0.无效,1.有效)',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '排序',
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COMMENT='商城快递配置';

-- ----------------------------
-- Records of store_express
-- ----------------------------
BEGIN;
INSERT INTO `store_express` VALUES (5, 'AAE全球专递', 'aae', '顺丰快递公司', 0, 0, 0, '2017-09-12 11:53:40');
INSERT INTO `store_express` VALUES (6, '安捷快递', 'anjie', '', 0, 0, 0, '2017-09-13 15:27:26');
INSERT INTO `store_express` VALUES (7, '安信达快递', 'anxindakuaixi', NULL, 0, 0, 0, '2017-09-13 16:05:19');
INSERT INTO `store_express` VALUES (8, '彪记快递', 'biaojikuaidi', NULL, 0, 0, 0, '2017-09-13 16:05:26');
INSERT INTO `store_express` VALUES (9, 'BHT', 'bht', '', 0, 0, 0, '2017-09-13 16:05:37');
INSERT INTO `store_express` VALUES (10, '百福东方国际物流', 'baifudongfang', NULL, 0, 0, 0, '2017-09-13 16:05:41');
INSERT INTO `store_express` VALUES (11, '中国东方（COE）', 'coe', NULL, 0, 0, 0, '2017-09-13 16:05:48');
INSERT INTO `store_express` VALUES (12, '长宇物流', 'changyuwuliu', NULL, 0, 0, 0, '2017-09-13 16:05:58');
INSERT INTO `store_express` VALUES (13, '大田物流', 'datianwuliu', NULL, 0, 0, 0, '2017-09-13 16:06:06');
INSERT INTO `store_express` VALUES (14, '德邦物流', 'debangwuliu', NULL, 1, 1, 0, '2017-09-13 16:06:14');
INSERT INTO `store_express` VALUES (15, 'DHL', 'dhl', NULL, 0, 0, 0, '2017-09-13 16:06:24');
INSERT INTO `store_express` VALUES (16, 'DPEX', 'dpex', NULL, 0, 0, 0, '2017-09-13 16:06:29');
INSERT INTO `store_express` VALUES (17, 'd速快递', 'dsukuaidi', NULL, 0, 0, 0, '2017-09-13 16:06:34');
INSERT INTO `store_express` VALUES (18, '递四方', 'disifang', NULL, 0, 0, 0, '2017-09-13 16:06:40');
INSERT INTO `store_express` VALUES (19, 'EMS快递', 'ems', '', 1, 0, 0, '2017-09-13 16:06:47');
INSERT INTO `store_express` VALUES (20, 'FEDEX（国外）', 'fedex', NULL, 0, 0, 0, '2017-09-13 16:06:56');
INSERT INTO `store_express` VALUES (21, '飞康达物流', 'feikangda', NULL, 0, 0, 0, '2017-09-13 16:07:03');
INSERT INTO `store_express` VALUES (22, '凤凰快递', 'fenghuangkuaidi', NULL, 0, 0, 0, '2017-09-13 16:07:10');
INSERT INTO `store_express` VALUES (23, '飞快达', 'feikuaida', NULL, 0, 0, 0, '2017-09-13 16:07:16');
INSERT INTO `store_express` VALUES (24, '国通快递', 'guotongkuaidi', NULL, 0, 0, 0, '2017-09-13 16:07:27');
INSERT INTO `store_express` VALUES (25, '港中能达物流', 'ganzhongnengda', NULL, 0, 0, 0, '2017-09-13 16:07:33');
INSERT INTO `store_express` VALUES (26, '广东邮政物流', 'guangdongyouzhengwuliu', NULL, 0, 0, 0, '2017-09-13 16:08:22');
INSERT INTO `store_express` VALUES (27, '共速达', 'gongsuda', NULL, 0, 0, 0, '2017-09-13 16:08:48');
INSERT INTO `store_express` VALUES (28, '汇通快运', 'huitongkuaidi', NULL, 0, 0, 0, '2017-09-13 16:08:56');
INSERT INTO `store_express` VALUES (29, '恒路物流', 'hengluwuliu', NULL, 0, 0, 0, '2017-09-13 16:09:02');
INSERT INTO `store_express` VALUES (30, '华夏龙物流', 'huaxialongwuliu', NULL, 0, 0, 0, '2017-09-13 16:09:12');
INSERT INTO `store_express` VALUES (31, '海红', 'haihongwangsong', NULL, 0, 0, 0, '2017-09-13 16:09:20');
INSERT INTO `store_express` VALUES (32, '海外环球', 'haiwaihuanqiu', NULL, 0, 0, 0, '2017-09-13 16:09:27');
INSERT INTO `store_express` VALUES (33, '佳怡物流', 'jiayiwuliu', NULL, 0, 0, 0, '2017-09-13 16:09:35');
INSERT INTO `store_express` VALUES (34, '京广速递', 'jinguangsudikuaijian', NULL, 0, 0, 0, '2017-09-13 16:09:42');
INSERT INTO `store_express` VALUES (35, '急先达', 'jixianda', NULL, 0, 0, 0, '2017-09-13 16:09:49');
INSERT INTO `store_express` VALUES (36, '佳吉物流', 'jjwl', NULL, 0, 0, 0, '2017-09-13 16:10:01');
INSERT INTO `store_express` VALUES (37, '加运美物流', 'jymwl', NULL, 0, 0, 0, '2017-09-13 16:10:13');
INSERT INTO `store_express` VALUES (38, '金大物流', 'jindawuliu', NULL, 0, 0, 0, '2017-09-13 16:10:22');
INSERT INTO `store_express` VALUES (39, '嘉里大通', 'jialidatong', NULL, 0, 0, 0, '2017-09-13 16:10:33');
INSERT INTO `store_express` VALUES (40, '晋越快递', 'jykd', NULL, 0, 0, 0, '2017-09-13 16:10:40');
INSERT INTO `store_express` VALUES (41, '快捷速递', 'kuaijiesudi', NULL, 0, 0, 0, '2017-09-13 16:10:49');
INSERT INTO `store_express` VALUES (42, '联邦快递（国内）', 'lianb', NULL, 0, 0, 0, '2017-09-13 16:10:58');
INSERT INTO `store_express` VALUES (43, '联昊通物流', 'lianhaowuliu', NULL, 0, 0, 0, '2017-09-13 16:11:07');
INSERT INTO `store_express` VALUES (44, '龙邦物流', 'longbanwuliu', NULL, 0, 0, 0, '2017-09-13 16:11:15');
INSERT INTO `store_express` VALUES (45, '立即送', 'lijisong', NULL, 0, 0, 0, '2017-09-13 16:11:25');
INSERT INTO `store_express` VALUES (46, '乐捷递', 'lejiedi', NULL, 0, 0, 0, '2017-09-13 16:11:36');
INSERT INTO `store_express` VALUES (47, '民航快递', 'minghangkuaidi', NULL, 0, 0, 0, '2017-09-13 16:11:45');
INSERT INTO `store_express` VALUES (48, '美国快递', 'meiguokuaidi', NULL, 0, 0, 0, '2017-09-13 16:11:53');
INSERT INTO `store_express` VALUES (49, '门对门', 'menduimen', NULL, 0, 0, 0, '2017-09-13 16:12:01');
INSERT INTO `store_express` VALUES (50, 'OCS', 'ocs', NULL, 0, 0, 0, '2017-09-13 16:12:10');
INSERT INTO `store_express` VALUES (51, '配思货运', 'peisihuoyunkuaidi', NULL, 0, 0, 0, '2017-09-13 16:12:18');
INSERT INTO `store_express` VALUES (52, '全晨快递', 'quanchenkuaidi', NULL, 0, 0, 0, '2017-09-13 16:12:26');
INSERT INTO `store_express` VALUES (53, '全峰快递', 'quanfengkuaidi', NULL, 0, 0, 0, '2017-09-13 16:12:34');
INSERT INTO `store_express` VALUES (54, '全际通物流', 'quanjitong', NULL, 0, 0, 0, '2017-09-13 16:12:41');
INSERT INTO `store_express` VALUES (55, '全日通快递', 'quanritongkuaidi', NULL, 0, 0, 0, '2017-09-13 16:12:49');
INSERT INTO `store_express` VALUES (56, '全一快递', 'quanyikuaidi', NULL, 0, 0, 0, '2017-09-13 16:12:56');
INSERT INTO `store_express` VALUES (57, '如风达', 'rufengda', NULL, 0, 0, 0, '2017-09-13 16:13:03');
INSERT INTO `store_express` VALUES (58, '三态速递', 'santaisudi', NULL, 0, 0, 0, '2017-09-13 16:13:15');
INSERT INTO `store_express` VALUES (59, '盛辉物流', 'shenghuiwuliu', NULL, 0, 0, 0, '2017-09-13 16:13:22');
INSERT INTO `store_express` VALUES (60, '申通', 'shentong', NULL, 0, 0, 0, '2017-09-13 16:13:34');
INSERT INTO `store_express` VALUES (61, '顺丰', 'shunfeng', '', 1, 0, 0, '2017-09-13 16:13:41');
INSERT INTO `store_express` VALUES (62, '速尔物流', 'sue', NULL, 0, 0, 0, '2017-09-13 16:13:48');
INSERT INTO `store_express` VALUES (63, '盛丰物流', 'shengfeng', NULL, 0, 0, 0, '2017-09-13 16:13:55');
INSERT INTO `store_express` VALUES (64, '赛澳递', 'saiaodi', NULL, 0, 0, 0, '2017-09-13 16:14:02');
INSERT INTO `store_express` VALUES (65, '天地华宇', 'tiandihuayu', NULL, 0, 0, 0, '2017-09-13 16:14:11');
INSERT INTO `store_express` VALUES (66, '天天快递', 'tiantian', NULL, 0, 0, 0, '2017-09-13 16:14:19');
INSERT INTO `store_express` VALUES (67, 'TNT', 'tnt', NULL, 0, 0, 0, '2017-09-13 16:14:26');
INSERT INTO `store_express` VALUES (68, 'UPS', 'ups', NULL, 0, 0, 0, '2017-09-13 16:14:29');
INSERT INTO `store_express` VALUES (69, '万家物流', 'wanjiawuliu', NULL, 0, 0, 0, '2017-09-13 16:14:37');
INSERT INTO `store_express` VALUES (70, '文捷航空速递', 'wenjiesudi', NULL, 0, 0, 0, '2017-09-13 16:14:46');
INSERT INTO `store_express` VALUES (71, '伍圆', 'wuyuan', NULL, 0, 0, 0, '2017-09-13 16:14:52');
INSERT INTO `store_express` VALUES (72, '万象物流', 'wxwl', NULL, 0, 0, 0, '2017-09-13 16:15:00');
INSERT INTO `store_express` VALUES (73, '新邦物流', 'xinbangwuliu', NULL, 0, 0, 0, '2017-09-13 16:15:06');
INSERT INTO `store_express` VALUES (74, '信丰物流', 'xinfengwuliu', NULL, 0, 0, 0, '2017-09-13 16:15:15');
INSERT INTO `store_express` VALUES (75, '亚风速递', 'yafengsudi', NULL, 0, 0, 0, '2017-09-13 16:15:23');
INSERT INTO `store_express` VALUES (76, '一邦速递', 'yibangwuliu', NULL, 0, 0, 0, '2017-09-13 16:15:30');
INSERT INTO `store_express` VALUES (77, '优速物流', 'youshuwuliu', NULL, 0, 0, 0, '2017-09-13 16:15:36');
INSERT INTO `store_express` VALUES (78, '邮政包裹挂号信', 'youzhengguonei', NULL, 0, 3, 0, '2017-09-13 16:15:44');
INSERT INTO `store_express` VALUES (79, '邮政国际包裹挂号信', 'youzhengguoji', NULL, 0, 2, 0, '2017-09-13 16:15:51');
INSERT INTO `store_express` VALUES (80, '远成物流', 'yuanchengwuliu', NULL, 0, 0, 0, '2017-09-13 16:15:57');
INSERT INTO `store_express` VALUES (81, '圆通速递', 'yuantong', NULL, 1, 1, 0, '2017-09-13 16:16:03');
INSERT INTO `store_express` VALUES (82, '源伟丰快递', 'yuanweifeng', NULL, 0, 0, 0, '2017-09-13 16:16:10');
INSERT INTO `store_express` VALUES (83, '元智捷诚快递', 'yuanzhijiecheng', NULL, 0, 0, 0, '2017-09-13 16:16:17');
INSERT INTO `store_express` VALUES (84, '韵达快运', 'yunda', NULL, 0, 0, 0, '2017-09-13 16:16:24');
INSERT INTO `store_express` VALUES (85, '运通快递', 'yuntongkuaidi', NULL, 0, 0, 0, '2017-09-13 16:16:33');
INSERT INTO `store_express` VALUES (86, '越丰物流', 'yuefengwuliu', NULL, 0, 0, 0, '2017-09-13 16:16:40');
INSERT INTO `store_express` VALUES (87, '源安达', 'yad', NULL, 0, 0, 0, '2017-09-13 16:16:47');
INSERT INTO `store_express` VALUES (88, '银捷速递', 'yinjiesudi', NULL, 0, 0, 0, '2017-09-13 16:16:56');
INSERT INTO `store_express` VALUES (89, '宅急送', 'zhaijisong', NULL, 0, 0, 0, '2017-09-13 16:17:03');
INSERT INTO `store_express` VALUES (90, '中铁快运', 'zhongtiekuaiyun', NULL, 0, 0, 0, '2017-09-13 16:17:10');
INSERT INTO `store_express` VALUES (91, '中通速递', 'zhongtong', '', 0, 0, 0, '2017-09-13 16:17:16');
INSERT INTO `store_express` VALUES (92, '中邮物流', 'zhongyouwuliu', NULL, 0, 0, 0, '2017-09-13 16:17:27');
INSERT INTO `store_express` VALUES (93, '忠信达', 'zhongxinda', NULL, 0, 0, 0, '2017-09-13 16:17:34');
INSERT INTO `store_express` VALUES (94, '芝麻开门', 'zhimakaimen', NULL, 0, 0, 0, '2017-09-13 16:17:41');
COMMIT;

-- ----------------------------
-- Table structure for store_goods
-- ----------------------------
DROP TABLE IF EXISTS `store_goods`;
CREATE TABLE `store_goods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` bigint(20) unsigned DEFAULT '0' COMMENT '品牌ID',
  `cate_id` bigint(20) unsigned DEFAULT '0' COMMENT '商品分类id',
  `unit_id` bigint(20) DEFAULT NULL COMMENT '商品单位ID',
  `spec_id` bigint(20) unsigned DEFAULT '0' COMMENT '规格ID',
  `tags_id` varchar(255) DEFAULT '' COMMENT '商品标签ID',
  `is_code` bigint(1) DEFAULT '1' COMMENT '是否有码商品',
  `goods_title` varchar(255) DEFAULT '' COMMENT '商品标签',
  `goods_content` text COMMENT '商品内容',
  `goods_logo` varchar(255) DEFAULT '' COMMENT '商品LOGO',
  `goods_image` text COMMENT '商品图片地址',
  `goods_video` varchar(500) DEFAULT '' COMMENT '商品视频URL',
  `goods_desc` varchar(500) DEFAULT '' COMMENT '商品描述',
  `package_stock` bigint(20) unsigned DEFAULT '0' COMMENT '总库存数量',
  `package_sale` bigint(20) unsigned DEFAULT '0' COMMENT '已销售数量',
  `favorite_num` bigint(20) unsigned DEFAULT '0' COMMENT '收藏次数',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '数据排序',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城商品主表';

-- ----------------------------
-- Table structure for store_goods_brand
-- ----------------------------
DROP TABLE IF EXISTS `store_goods_brand`;
CREATE TABLE `store_goods_brand` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brand_logo` varchar(1024) DEFAULT '' COMMENT '品牌logo',
  `brand_cover` varchar(1024) DEFAULT '' COMMENT '品牌封面',
  `brand_title` varchar(255) DEFAULT '' COMMENT '商品品牌名称',
  `brand_desc` text COMMENT '商品品牌描述',
  `brand_detail` text COMMENT '品牌图文信息',
  `sort` int(11) unsigned DEFAULT '0' COMMENT '商品分类排序',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品品牌';

-- ----------------------------
-- Table structure for store_goods_cate
-- ----------------------------
DROP TABLE IF EXISTS `store_goods_cate`;
CREATE TABLE `store_goods_cate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned DEFAULT '0' COMMENT '上级分类编号',
  `brand_id` bigint(20) DEFAULT '0' COMMENT '品牌ID',
  `cate_title` varchar(255) DEFAULT '' COMMENT '商品分类名称',
  `cate_desc` text COMMENT '商品分类',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '商品分类排序',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城商品分类';

-- ----------------------------
-- Table structure for store_goods_list
-- ----------------------------
DROP TABLE IF EXISTS `store_goods_list`;
CREATE TABLE `store_goods_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` bigint(20) unsigned DEFAULT '0' COMMENT '商品ID',
  `goods_spec` varchar(255) DEFAULT '' COMMENT '商品规格名称',
  `goods_number` bigint(20) unsigned DEFAULT '0' COMMENT '商品礼品-商品数量',
  `market_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '销售价格',
  `selling_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '商品价格',
  `goods_stock` bigint(20) unsigned DEFAULT '0' COMMENT '商品库存统计',
  `goods_sale` bigint(20) unsigned DEFAULT '0' COMMENT '已销售数量',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城商品列表';

-- ----------------------------
-- Table structure for store_goods_spec
-- ----------------------------
DROP TABLE IF EXISTS `store_goods_spec`;
CREATE TABLE `store_goods_spec` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mch_id` bigint(20) unsigned DEFAULT '0' COMMENT '商户ID',
  `spec_title` varchar(255) DEFAULT '' COMMENT '商品规格名称',
  `spec_param` varchar(255) DEFAULT '' COMMENT '商品规格参数',
  `spec_desc` varchar(255) DEFAULT '' COMMENT '商品规格描述',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '商品规格排序',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_store_goods_spec_mch_id` (`mch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城商品规格';

-- ----------------------------
-- Table structure for store_goods_stock
-- ----------------------------
DROP TABLE IF EXISTS `store_goods_stock`;
CREATE TABLE `store_goods_stock` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` bigint(20) unsigned DEFAULT '0' COMMENT '商品ID',
  `goods_spec` varchar(255) DEFAULT '' COMMENT '商品属性',
  `goods_stock` bigint(20) unsigned DEFAULT '0' COMMENT '商品库存',
  `stock_desc` varchar(255) DEFAULT '' COMMENT '商品库存描述',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '数据排序',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城商品库存';

-- ----------------------------
-- Table structure for store_member
-- ----------------------------
DROP TABLE IF EXISTS `store_member`;
CREATE TABLE `store_member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) DEFAULT '' COMMENT '会员微信OPENID',
  `phone` varchar(16) DEFAULT '' COMMENT '会员手机号',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码',
  `nickname` varchar(20) DEFAULT '' COMMENT '会员昵称',
  `headimg` varchar(500) DEFAULT '' COMMENT '会员头像',
  `sex` char(2) DEFAULT '' COMMENT '性别',
  `level` tinyint(2) unsigned DEFAULT '1' COMMENT '会员级别',
  `remark` varchar(500) DEFAULT '' COMMENT '会员个性签名',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '会员状态(1有效,0无效)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_store_member_openid` (`openid`) USING BTREE,
  KEY `index_store_member_phone` (`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城会员信息';

-- ----------------------------
-- Table structure for store_member_address
-- ----------------------------
DROP TABLE IF EXISTS `store_member_address`;
CREATE TABLE `store_member_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` bigint(20) unsigned DEFAULT '0' COMMENT '会员ID',
  `username` varchar(20) DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(16) DEFAULT '' COMMENT '收货手机号',
  `province` varchar(50) DEFAULT '' COMMENT '收货地址省份',
  `city` varchar(50) DEFAULT '' COMMENT '收货地址城市',
  `area` varchar(255) DEFAULT '' COMMENT '收货地址区域',
  `address` varchar(255) DEFAULT '' COMMENT '收货详细地址',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0无效, 1新快递)',
  `is_default` tinyint(1) unsigned DEFAULT '1' COMMENT '默认收货地址',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城会员收货地址';

-- ----------------------------
-- Table structure for store_order
-- ----------------------------
DROP TABLE IF EXISTS `store_order`;
CREATE TABLE `store_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '订单类型(1普通订单,2积分订单)',
  `mid` bigint(20) unsigned DEFAULT '0' COMMENT '会员ID',
  `order_no` char(10) DEFAULT '' COMMENT '订单号',
  `freight_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '快递费',
  `goods_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '商品总金额(不含快递费)',
  `real_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '真实支付金额(商品金额+快递金额)',
  `is_pay` tinyint(1) unsigned DEFAULT '0' COMMENT '订单支付状态(0.未支付,1.已支付)',
  `pay_type` varchar(255) DEFAULT '' COMMENT '支付方式 (wechat微信支付, alipay支付宝支付)',
  `pay_no` varchar(100) DEFAULT '' COMMENT '商户交易号',
  `pay_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '实际支付金额',
  `pay_at` datetime DEFAULT NULL COMMENT '支付时间',
  `desc` text COMMENT '订单描述',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '订单状态(0.无效,1.新订单,2.待发货,3.已发货,4.已收货,5.已完成,6.已退货及退款)',
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_store_order_order_no` (`order_no`) USING BTREE,
  KEY `index_store_order_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城订单主表';

-- ----------------------------
-- Table structure for store_order_express
-- ----------------------------
DROP TABLE IF EXISTS `store_order_express`;
CREATE TABLE `store_order_express` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` bigint(20) unsigned DEFAULT '0' COMMENT '会员ID',
  `type` tinyint(1) unsigned DEFAULT '0' COMMENT '快递类型(0.订单快递,1.退货快递)',
  `order_no` char(10) DEFAULT '' COMMENT '订单编号',
  `company_title` varchar(50) DEFAULT '' COMMENT '物流公司名称',
  `company_code` varchar(50) DEFAULT '' COMMENT '物流公司编码',
  `username` varchar(20) DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(16) DEFAULT '' COMMENT '收货手机号',
  `province` varchar(50) DEFAULT NULL COMMENT '收货地址省份',
  `city` varchar(50) DEFAULT '' COMMENT '收货地址城市',
  `area` varchar(255) DEFAULT '' COMMENT '收货地址区域',
  `address` varchar(255) DEFAULT '' COMMENT '收货详细地址',
  `send_no` varchar(50) DEFAULT '' COMMENT '实际物流公司单号',
  `send_company_title` varchar(50) DEFAULT '' COMMENT '实际发货快递公司',
  `send_company_code` varchar(50) DEFAULT '' COMMENT '实际发货快递代码',
  `send_username` varchar(255) DEFAULT '' COMMENT '寄件人名称',
  `send_phone` varchar(16) DEFAULT '' COMMENT '寄件人手机号',
  `send_province` varchar(50) DEFAULT '' COMMENT '寄件人地址省份',
  `send_city` varchar(50) DEFAULT '' COMMENT '寄件人地址城市',
  `send_area` varchar(255) DEFAULT '' COMMENT '寄件人地址区域',
  `send_address` varchar(255) DEFAULT '' COMMENT '寄件人详细地址',
  `send_at` datetime DEFAULT NULL COMMENT '快递发货时间',
  `desc` text COMMENT '发货描述',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0.无效,1.新快递,2.已签收,3.签收失败)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城订单快递';

-- ----------------------------
-- Table structure for store_order_goods
-- ----------------------------
DROP TABLE IF EXISTS `store_order_goods`;
CREATE TABLE `store_order_goods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` bigint(20) unsigned DEFAULT '0' COMMENT '会员ID',
  `order_no` char(10) DEFAULT '' COMMENT '订单编号',
  `goods_id` bigint(20) DEFAULT '0' COMMENT '商品ID',
  `goods_title` varchar(255) DEFAULT '' COMMENT '商品标签',
  `goods_spec` varchar(255) DEFAULT '' COMMENT '商品规格',
  `goods_logo` varchar(255) DEFAULT '' COMMENT '商品LOGO',
  `goods_image` text COMMENT '商品图片地址',
  `market_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '市场价格',
  `selling_price` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '商品销售价格',
  `price_field` varchar(20) DEFAULT 'selling_price' COMMENT '计价字段',
  `number` bigint(20) unsigned DEFAULT '0' COMMENT '订单商品数量',
  `status` bigint(1) unsigned DEFAULT '1' COMMENT '商品状态(1有效,0无效)',
  `is_deleted` bigint(1) unsigned DEFAULT '0' COMMENT '删除状态(1删除,0未删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_store_order_list_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城订单关联商品';

-- ----------------------------
-- Table structure for system_auth
-- ----------------------------
DROP TABLE IF EXISTS `system_auth`;
CREATE TABLE `system_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL COMMENT '权限名称',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(1:禁用,2:启用)',
  `sort` smallint(6) unsigned DEFAULT '0' COMMENT '排序权重',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注说明',
  `create_by` bigint(11) unsigned DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_auth_title` (`title`) USING BTREE,
  KEY `index_system_auth_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='系统权限表';

-- ----------------------------
-- Records of system_auth
-- ----------------------------
BEGIN;
INSERT INTO `system_auth` VALUES (1, '运营管理', 1, 0, 'yygkl', 0, '2018-05-24 15:19:13');
COMMIT;

-- ----------------------------
-- Table structure for system_auth_node
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_node`;
CREATE TABLE `system_auth_node` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth` bigint(20) unsigned DEFAULT NULL COMMENT '角色ID',
  `node` varchar(200) DEFAULT NULL COMMENT '节点路径',
  PRIMARY KEY (`id`),
  KEY `index_system_auth_auth` (`auth`) USING BTREE,
  KEY `index_system_auth_node` (`node`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统角色与节点绑定';

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '配置编码',
  `value` varchar(500) DEFAULT NULL COMMENT '配置值',
  PRIMARY KEY (`id`),
  KEY `index_system_config_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 COMMENT='系统参数配置';

-- ----------------------------
-- Records of system_config
-- ----------------------------
BEGIN;
INSERT INTO `system_config` VALUES (1, 'app_name', 'Test1');
INSERT INTO `system_config` VALUES (2, 'site_name', 'Test1');
INSERT INTO `system_config` VALUES (3, 'app_version', '3.0 dev');
INSERT INTO `system_config` VALUES (4, 'site_copy', '©版权所有 2014-2018 广州智深');
INSERT INTO `system_config` VALUES (5, 'browser_icon', 'http://edu.saas.zhishensoft.ls/static/upload/8d57fd4e2030e4d7/b02989f8c140159e.png');
INSERT INTO `system_config` VALUES (6, 'tongji_baidu_key', '');
INSERT INTO `system_config` VALUES (7, 'miitbeian', '粤ICP备15108904号-2');
INSERT INTO `system_config` VALUES (8, 'storage_type', 'local');
INSERT INTO `system_config` VALUES (9, 'storage_local_exts', 'png,jpg,rar,doc,icon,mp4');
INSERT INTO `system_config` VALUES (10, 'storage_qiniu_bucket', '');
INSERT INTO `system_config` VALUES (11, 'storage_qiniu_domain', '');
INSERT INTO `system_config` VALUES (12, 'storage_qiniu_access_key', '');
INSERT INTO `system_config` VALUES (13, 'storage_qiniu_secret_key', '');
INSERT INTO `system_config` VALUES (14, 'storage_oss_bucket', 'cuci');
INSERT INTO `system_config` VALUES (15, 'storage_oss_endpoint', 'oss-cn-beijing.aliyuncs.com');
INSERT INTO `system_config` VALUES (16, 'storage_oss_domain', 'cuci.oss-cn-beijing.aliyuncs.com');
INSERT INTO `system_config` VALUES (17, 'storage_oss_keyid', '用你自己的吧');
INSERT INTO `system_config` VALUES (18, 'storage_oss_secret', '用你自己的吧');
INSERT INTO `system_config` VALUES (34, 'wechat_appid', 'wx60a43dd8161666d4');
INSERT INTO `system_config` VALUES (35, 'wechat_appkey', '9890a0d7c91801a609d151099e95b61a');
INSERT INTO `system_config` VALUES (36, 'storage_oss_is_https', 'http');
INSERT INTO `system_config` VALUES (37, 'wechat_type', 'thr');
INSERT INTO `system_config` VALUES (38, 'wechat_token', 'test');
INSERT INTO `system_config` VALUES (39, 'wechat_appsecret', 'a041bec98ed015d52b99acea5c6a16ef');
INSERT INTO `system_config` VALUES (40, 'wechat_encodingaeskey', 'BJIUzE0gqlWy0GxfPp4J1oPTBmOrNDIGPNav1YFH5Z5');
INSERT INTO `system_config` VALUES (41, 'wechat_thr_appid', 'wx60a43dd8161666d4');
INSERT INTO `system_config` VALUES (42, 'wechat_thr_appkey', '05db2aa335382c66ab56d69b1a9ad0ee');
INSERT INTO `system_config` VALUES (43, 'sms_type', 'other');
INSERT INTO `system_config` VALUES (44, 'sms_code_gateway', 'http://sms.zhishensoft.com');
INSERT INTO `system_config` VALUES (45, 'sms_code_account', '10020');
INSERT INTO `system_config` VALUES (46, 'sms_code_password', 'aObjEZmY1EyIk8uaUEXL9QWCBcO7DmSICTy67ASY');
INSERT INTO `system_config` VALUES (47, 'sms_code_sign', '付二代');
INSERT INTO `system_config` VALUES (48, 'sms_other_gateway', 'http://sms.zhishensoft.com');
INSERT INTO `system_config` VALUES (49, 'sms_other_account', '10020');
INSERT INTO `system_config` VALUES (50, 'sms_other_password', 'aObjEZmY1EyIk8uaUEXL9QWCBcO7DmSICTy67ASY');
INSERT INTO `system_config` VALUES (51, 'sms_other_sign', '付二代');
INSERT INTO `system_config` VALUES (52, 'ipwhitelist', '0.0.0.0/0');
INSERT INTO `system_config` VALUES (53, 'except_group', '');
COMMIT;

-- ----------------------------
-- Table structure for system_log
-- ----------------------------
DROP TABLE IF EXISTS `system_log`;
CREATE TABLE `system_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` char(15) NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `node` char(200) NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人用户名',
  `action` varchar(200) NOT NULL DEFAULT '' COMMENT '操作行为',
  `content` text NOT NULL COMMENT '操作内容描述',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='系统操作日志表';

-- ----------------------------
-- Records of system_log
-- ----------------------------
BEGIN;
INSERT INTO `system_log` VALUES (1, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-19 11:43:58');
INSERT INTO `system_log` VALUES (2, '192.168.11.1', 'wechat/fans/sync', 'admin', '微信管理', '同步全部微信粉丝成功', '2018-05-19 14:31:11');
INSERT INTO `system_log` VALUES (3, '192.168.11.1', 'wechat/tags/sync', 'admin', '微信管理', '同步全部微信粉丝标签成功', '2018-05-19 14:31:24');
INSERT INTO `system_log` VALUES (4, '192.168.11.1', 'wechat/fans/sync', 'admin', '微信管理', '同步全部微信粉丝成功', '2018-05-19 14:31:53');
INSERT INTO `system_log` VALUES (5, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-19 15:26:06');
INSERT INTO `system_log` VALUES (6, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-19 15:29:32');
INSERT INTO `system_log` VALUES (7, '192.168.11.1', 'admin/config/sms', 'admin', '系统管理', '系统参数配置成功', '2018-05-21 11:27:07');
INSERT INTO `system_log` VALUES (8, '192.168.11.1', 'admin/config/ip_white_list', 'admin', '系统管理', '系统参数配置成功', '2018-05-21 16:33:55');
INSERT INTO `system_log` VALUES (9, '192.168.11.1', 'admin/config/ip_white_list', 'admin', '系统管理', '系统参数配置成功', '2018-05-21 16:38:56');
INSERT INTO `system_log` VALUES (10, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-21 16:38:59');
INSERT INTO `system_log` VALUES (11, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-21 16:39:05');
INSERT INTO `system_log` VALUES (12, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-21 16:52:58');
INSERT INTO `system_log` VALUES (13, '192.168.11.1', 'admin/login/index', '', '异常登录', '用户[admin]尝试从[192.168.11.1]登录!!!', '2018-05-21 16:53:04');
INSERT INTO `system_log` VALUES (14, '192.168.11.1', 'admin/login/index', '', '异常登录', '用户[admin]尝试从[192.168.11.1]登录!!!', '2018-05-21 16:53:09');
INSERT INTO `system_log` VALUES (15, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-21 16:53:22');
INSERT INTO `system_log` VALUES (16, '192.168.11.1', 'admin/config/ip_white_list', 'admin', '系统管理', '系统参数配置成功', '2018-05-21 16:53:36');
INSERT INTO `system_log` VALUES (17, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-21 16:53:43');
INSERT INTO `system_log` VALUES (18, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-21 16:53:47');
INSERT INTO `system_log` VALUES (19, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-22 10:33:26');
INSERT INTO `system_log` VALUES (20, '192.168.11.1', 'admin/config/index', 'admin', '系统管理', '系统参数配置成功', '2018-05-22 10:37:13');
INSERT INTO `system_log` VALUES (21, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-22 11:34:41');
INSERT INTO `system_log` VALUES (22, '192.168.11.1', 'admin/config/index', 'admin', '系统管理', '系统参数配置成功', '2018-05-23 15:57:29');
INSERT INTO `system_log` VALUES (23, '192.168.11.1', 'admin/config/index', 'admin', '系统管理', '系统参数配置成功', '2018-05-23 15:57:36');
INSERT INTO `system_log` VALUES (24, '192.168.11.1', 'admin/config/index', 'admin', '系统管理', '系统参数配置成功', '2018-05-23 15:57:46');
INSERT INTO `system_log` VALUES (25, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-23 15:58:55');
INSERT INTO `system_log` VALUES (26, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-24 09:19:34');
INSERT INTO `system_log` VALUES (27, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-24 11:04:09');
INSERT INTO `system_log` VALUES (28, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-24 11:07:31');
INSERT INTO `system_log` VALUES (29, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-24 11:14:41');
INSERT INTO `system_log` VALUES (30, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-24 11:14:50');
INSERT INTO `system_log` VALUES (31, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-24 11:18:35');
INSERT INTO `system_log` VALUES (32, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-24 11:25:58');
INSERT INTO `system_log` VALUES (33, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-24 14:30:50');
INSERT INTO `system_log` VALUES (34, '192.168.11.1', 'admin/login/index', '', '异常登录', '用户[17693355155]尝试从[192.168.11.1]登录!!!', '2018-05-24 14:31:05');
INSERT INTO `system_log` VALUES (35, '192.168.11.1', 'admin/login/index', '', '异常登录', '用户[17693355155]尝试从[192.168.11.1]登录!!!', '2018-05-24 14:31:09');
INSERT INTO `system_log` VALUES (36, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-24 14:31:33');
INSERT INTO `system_log` VALUES (37, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-26 11:29:27');
INSERT INTO `system_log` VALUES (38, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-26 11:39:06');
INSERT INTO `system_log` VALUES (39, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-30 09:41:59');
INSERT INTO `system_log` VALUES (40, '192.168.11.1', 'admin/login/out', 'admin', '系统管理', '用户退出系统成功', '2018-05-30 09:42:31');
INSERT INTO `system_log` VALUES (41, '192.168.11.1', 'admin/login/index', 'admin', '系统管理', '用户登录系统成功', '2018-05-30 09:49:41');
COMMIT;

-- ----------------------------
-- Table structure for system_menu
-- ----------------------------
DROP TABLE IF EXISTS `system_menu`;
CREATE TABLE `system_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `node` varchar(200) NOT NULL DEFAULT '' COMMENT '节点代码',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `url` varchar(400) NOT NULL DEFAULT '' COMMENT '链接',
  `params` varchar(500) DEFAULT '' COMMENT '链接参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '链接打开方式',
  `sort` int(11) unsigned DEFAULT '0' COMMENT '菜单排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `create_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_system_menu_node` (`node`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COMMENT='系统菜单表';

-- ----------------------------
-- Records of system_menu
-- ----------------------------
BEGIN;
INSERT INTO `system_menu` VALUES (1, 0, '系统设置', '', '', '#', '', '_self', 9000, 1, 10000, '2018-01-19 15:27:00');
INSERT INTO `system_menu` VALUES (2, 10, '后台菜单', '', 'fa fa-leaf', 'admin/menu/index', '', '_self', 10, 1, 10000, '2018-01-19 15:27:17');
INSERT INTO `system_menu` VALUES (3, 10, '系统参数', '', 'fa fa-modx', 'admin/config/index', '', '_self', 20, 1, 10000, '2018-01-19 15:27:57');
INSERT INTO `system_menu` VALUES (4, 11, '访问授权', '', 'fa fa-group', 'admin/auth/index', '', '_self', 20, 1, 10000, '2018-01-22 11:13:02');
INSERT INTO `system_menu` VALUES (5, 11, '用户管理', '', 'fa fa-user', 'admin/user/index', '', '_self', 10, 1, 0, '2018-01-23 12:15:12');
INSERT INTO `system_menu` VALUES (6, 11, '访问节点', '', 'fa fa-fort-awesome', 'admin/node/index', '', '_self', 30, 1, 0, '2018-01-23 12:36:54');
INSERT INTO `system_menu` VALUES (7, 0, '后台首页', '', '', 'admin/index/main', '', '_self', 1000, 1, 0, '2018-01-23 13:42:30');
INSERT INTO `system_menu` VALUES (8, 16, '系统日志', '', 'fa fa-code', 'admin/log/index', '', '_self', 10, 1, 0, '2018-01-24 13:52:58');
INSERT INTO `system_menu` VALUES (9, 10, '文件存储', '', 'fa fa-stop-circle', 'admin/config/file', '', '_self', 30, 1, 0, '2018-01-25 10:54:28');
INSERT INTO `system_menu` VALUES (10, 1, '系统管理', '', '', '#', '', '_self', 200, 1, 0, '2018-01-25 18:14:28');
INSERT INTO `system_menu` VALUES (11, 1, '访问权限', '', '', '#', '', '_self', 300, 1, 0, '2018-01-25 18:15:08');
INSERT INTO `system_menu` VALUES (16, 1, '日志管理', '', '', '#', '', '_self', 400, 1, 0, '2018-02-10 16:31:15');
INSERT INTO `system_menu` VALUES (17, 0, '微信管理', '', '', '#', '', '_self', 8000, 1, 0, '2018-03-06 14:42:49');
INSERT INTO `system_menu` VALUES (18, 17, '公众号配置', '', '', '#', '', '_self', 0, 1, 0, '2018-03-06 14:43:05');
INSERT INTO `system_menu` VALUES (19, 18, '微信授权绑定', '', 'fa fa-cog', 'wechat/config/index', '', '_self', 0, 1, 0, '2018-03-06 14:43:26');
INSERT INTO `system_menu` VALUES (20, 18, '关注默认回复', '', 'fa fa-comment-o', 'wechat/keys/subscribe', '', '_self', 0, 1, 0, '2018-03-06 14:44:45');
INSERT INTO `system_menu` VALUES (21, 18, '无反馈默认回复', '', 'fa fa-commenting', 'wechat/keys/defaults', '', '_self', 0, 1, 0, '2018-03-06 14:45:55');
INSERT INTO `system_menu` VALUES (22, 18, '微信关键字管理', '', 'fa fa-hashtag', 'wechat/keys/index', '', '_self', 0, 1, 0, '2018-03-06 14:46:23');
INSERT INTO `system_menu` VALUES (23, 17, '微信服务定制', '', '', '#', '', '_self', 0, 1, 0, '2018-03-06 14:47:11');
INSERT INTO `system_menu` VALUES (24, 23, '微信菜单管理', '', 'fa fa-gg-circle', 'wechat/menu/index', '', '_self', 0, 1, 0, '2018-03-06 14:47:39');
INSERT INTO `system_menu` VALUES (25, 23, '微信图文管理', '', 'fa fa-map-o', 'wechat/news/index', '', '_self', 0, 1, 0, '2018-03-06 14:48:14');
INSERT INTO `system_menu` VALUES (26, 17, '微信粉丝管理', '', 'fa fa-user', '#', '', '_self', 0, 1, 0, '2018-03-06 14:48:33');
INSERT INTO `system_menu` VALUES (27, 26, '微信粉丝列表', '', 'fa fa-users', 'wechat/fans/index', '', '_self', 20, 1, 0, '2018-03-06 14:49:04');
INSERT INTO `system_menu` VALUES (28, 26, '微信黑名单管理', '', 'fa fa-user-times', 'wechat/fans_block/index', '', '_self', 30, 1, 0, '2018-03-06 14:49:22');
INSERT INTO `system_menu` VALUES (29, 26, '微信标签管理', '', 'fa fa-tags', 'wechat/tags/index', '', '_self', 10, 1, 0, '2018-03-06 14:49:39');
INSERT INTO `system_menu` VALUES (32, 0, '商城管理', '', '', '#', '', '_self', 2000, 1, 0, '2018-03-20 16:46:07');
INSERT INTO `system_menu` VALUES (33, 32, '商品管理', '', '', '#', '', '_self', 0, 1, 0, '2018-03-20 16:46:21');
INSERT INTO `system_menu` VALUES (34, 33, '产品管理', '', 'fa fa-modx', 'store/goods/index', '', '_self', 0, 1, 0, '2018-03-20 16:46:45');
INSERT INTO `system_menu` VALUES (35, 33, '规格管理', '', 'fa fa-hashtag', 'store/goods_spec/index', '', '_self', 0, 1, 0, '2018-03-20 16:47:08');
INSERT INTO `system_menu` VALUES (36, 33, '分类管理', '', 'fa fa-product-hunt', 'store/goods_cate/index', '', '_self', 0, 1, 0, '2018-03-20 16:47:50');
INSERT INTO `system_menu` VALUES (37, 33, '品牌管理', '', 'fa fa-scribd', 'store/goods_brand/index', '', '_self', 0, 1, 0, '2018-03-20 16:48:05');
INSERT INTO `system_menu` VALUES (38, 32, '订单管理', '', '', '#', '', '_self', 0, 1, 0, '2018-04-21 15:07:36');
INSERT INTO `system_menu` VALUES (39, 38, '订单列表', '', 'fa fa-adjust', 'store/order/index', '', '_self', 0, 1, 0, '2018-04-21 15:07:54');
INSERT INTO `system_menu` VALUES (40, 32, '商城配置', '', '', '#', '', '_self', 0, 1, 0, '2018-04-21 15:08:17');
INSERT INTO `system_menu` VALUES (41, 40, '参数管理', '', '', '#', '', '_self', 0, 0, 0, '2018-04-21 15:08:25');
INSERT INTO `system_menu` VALUES (42, 40, '快递公司', '', 'fa fa-mixcloud', 'store/express/index', '', '_self', 0, 1, 0, '2018-04-21 15:08:50');
INSERT INTO `system_menu` VALUES (43, 10, '验证码短信配置', '', 'fa fa-comment-o', 'admin/config/sms', 'a=code', '_self', 40, 1, 0, '2018-05-21 11:01:20');
INSERT INTO `system_menu` VALUES (44, 10, 'IP白名单设置', '', 'fa fa-fort-awesome', 'admin/config/ip_white_list', '', '_self', 50, 1, 0, '2018-05-21 16:31:42');
COMMIT;

-- ----------------------------
-- Table structure for system_node
-- ----------------------------
DROP TABLE IF EXISTS `system_node`;
CREATE TABLE `system_node` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node` varchar(100) DEFAULT NULL COMMENT '节点代码',
  `title` varchar(500) DEFAULT NULL COMMENT '节点标题',
  `is_menu` tinyint(1) unsigned DEFAULT '0' COMMENT '是否可设置为菜单',
  `is_auth` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动RBAC权限控制',
  `is_login` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动登录控制',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_system_node_node` (`node`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COMMENT='系统节点表';

-- ----------------------------
-- Records of system_node
-- ----------------------------
BEGIN;
INSERT INTO `system_node` VALUES (13, 'admin', '系统设置', 0, 1, 1, '2018-05-04 11:02:34');
INSERT INTO `system_node` VALUES (14, 'admin/auth', '权限管理', 0, 1, 1, '2018-05-04 11:06:55');
INSERT INTO `system_node` VALUES (15, 'admin/auth/index', '权限列表', 1, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (16, 'admin/auth/apply', '权限配置', 0, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (17, 'admin/auth/add', '添加权限', 0, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (18, 'admin/auth/edit', '编辑权限', 0, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (19, 'admin/auth/forbid', '禁用权限', 0, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (20, 'admin/auth/resume', '启用权限', 0, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (21, 'admin/auth/del', '删除权限', 0, 1, 1, '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES (22, 'admin/config', '系统配置', 0, 1, 1, '2018-05-04 11:08:18');
INSERT INTO `system_node` VALUES (23, 'admin/config/index', '系统参数', 1, 1, 1, '2018-05-04 11:08:25');
INSERT INTO `system_node` VALUES (24, 'admin/config/file', '文件存储', 1, 1, 1, '2018-05-04 11:08:27');
INSERT INTO `system_node` VALUES (25, 'admin/log', '日志管理', 0, 1, 1, '2018-05-04 11:08:43');
INSERT INTO `system_node` VALUES (26, 'admin/log/index', '日志管理', 1, 1, 1, '2018-05-04 11:08:43');
INSERT INTO `system_node` VALUES (28, 'admin/log/del', '日志删除', 0, 1, 1, '2018-05-04 11:08:43');
INSERT INTO `system_node` VALUES (29, 'admin/menu', '系统菜单', 0, 1, 1, '2018-05-04 11:09:54');
INSERT INTO `system_node` VALUES (30, 'admin/menu/index', '菜单列表', 1, 1, 1, '2018-05-04 11:09:54');
INSERT INTO `system_node` VALUES (31, 'admin/menu/add', '添加菜单', 0, 1, 1, '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES (32, 'admin/menu/edit', '编辑菜单', 0, 1, 1, '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES (33, 'admin/menu/del', '删除菜单', 0, 1, 1, '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES (34, 'admin/menu/forbid', '禁用菜单', 0, 1, 1, '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES (35, 'admin/menu/resume', '启用菜单', 0, 1, 1, '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES (36, 'admin/node', '节点管理', 0, 1, 1, '2018-05-04 11:10:20');
INSERT INTO `system_node` VALUES (37, 'admin/node/index', '节点列表', 1, 1, 1, '2018-05-04 11:10:20');
INSERT INTO `system_node` VALUES (38, 'admin/node/clear', '清理节点', 0, 1, 1, '2018-05-04 11:10:21');
INSERT INTO `system_node` VALUES (39, 'admin/node/save', '更新节点', 0, 1, 1, '2018-05-04 11:10:21');
INSERT INTO `system_node` VALUES (40, 'admin/user', '系统用户', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (41, 'admin/user/index', '用户列表', 1, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (42, 'admin/user/auth', '用户授权', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (43, 'admin/user/add', '添加用户', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (44, 'admin/user/edit', '编辑用户', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (45, 'admin/user/pass', '修改密码', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (46, 'admin/user/del', '删除用户', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (47, 'admin/user/forbid', '禁用启用', 0, 1, 1, '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES (48, 'admin/user/resume', '启用用户', 0, 1, 1, '2018-05-04 11:10:44');
INSERT INTO `system_node` VALUES (49, 'store', '商城管理', 0, 1, 1, '2018-05-04 11:11:28');
INSERT INTO `system_node` VALUES (50, 'store/express', '快递公司管理', 0, 1, 1, '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES (51, 'store/express/index', '快递公司列表', 1, 1, 1, '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES (52, 'store/express/add', '添加快递公司', 0, 1, 1, '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES (53, 'store/express/edit', '编辑快递公司', 0, 1, 1, '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES (54, 'store/express/del', '删除快递公司', 0, 1, 1, '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES (55, 'store/express/forbid', '禁用快递公司', 0, 1, 1, '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES (56, 'store/express/resume', '启用快递公司', 0, 1, 1, '2018-05-04 11:11:40');
INSERT INTO `system_node` VALUES (57, 'store/order', '订单管理', 0, 1, 1, '2018-05-04 11:12:14');
INSERT INTO `system_node` VALUES (58, 'store/order/index', '订单列表', 1, 1, 1, '2018-05-04 11:12:17');
INSERT INTO `system_node` VALUES (59, 'store/order/address', '修改地址', 0, 1, 1, '2018-05-04 11:12:19');
INSERT INTO `system_node` VALUES (76, 'wechat', '微信管理', 0, 1, 1, '2018-05-04 11:14:59');
INSERT INTO `system_node` VALUES (78, 'wechat/config', '微信对接管理', 0, 1, 1, '2018-05-04 11:16:20');
INSERT INTO `system_node` VALUES (79, 'wechat/config/index', '微信对接配置', 1, 1, 1, '2018-05-04 11:16:23');
INSERT INTO `system_node` VALUES (80, 'wechat/fans', '微信粉丝管理', 0, 1, 1, '2018-05-04 11:16:31');
INSERT INTO `system_node` VALUES (81, 'wechat/fans/index', '微信粉丝列表', 1, 1, 1, '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES (82, 'wechat/fans/backadd', '微信粉丝拉黑', 0, 1, 1, '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES (83, 'wechat/fans/tagset', '设置粉丝标签', 0, 1, 1, '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES (84, 'wechat/fans/tagadd', '添加粉丝标签', 0, 1, 1, '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES (85, 'wechat/fans/tagdel', '删除粉丝标签', 0, 1, 1, '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES (86, 'wechat/fans/sync', '同步粉丝列表', 0, 1, 1, '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES (87, 'wechat/fans_block', '粉丝黑名单管理', 0, 1, 1, '2018-05-04 11:17:25');
INSERT INTO `system_node` VALUES (88, 'wechat/fans_block/index', '粉丝黑名单列表', 1, 1, 1, '2018-05-04 11:17:50');
INSERT INTO `system_node` VALUES (89, 'wechat/fans_block/backdel', '移除粉丝黑名单', 0, 1, 1, '2018-05-04 11:17:51');
INSERT INTO `system_node` VALUES (90, 'wechat/keys', '微信关键字', 0, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (91, 'wechat/keys/index', '关键字列表', 1, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (92, 'wechat/keys/add', '添加关键字', 0, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (93, 'wechat/keys/edit', '编辑关键字', 0, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (94, 'wechat/keys/del', '删除关键字', 0, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (95, 'wechat/keys/forbid', '禁用关键字', 0, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (96, 'wechat/keys/resume', '启用关键字', 0, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (97, 'wechat/keys/subscribe', '关注回复', 1, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (98, 'wechat/keys/defaults', '默认回复', 1, 1, 1, '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES (99, 'wechat/menu', '微信菜单管理', 0, 1, 1, '2018-05-04 11:18:57');
INSERT INTO `system_node` VALUES (100, 'wechat/menu/index', '微信菜单展示', 1, 1, 1, '2018-05-04 11:19:10');
INSERT INTO `system_node` VALUES (101, 'wechat/menu/edit', '编辑微信菜单', 0, 1, 1, '2018-05-04 11:19:22');
INSERT INTO `system_node` VALUES (102, 'wechat/menu/cancel', '取消微信菜单', 0, 1, 1, '2018-05-04 11:19:26');
INSERT INTO `system_node` VALUES (103, 'wechat/news/index', '微信图文列表', 1, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (104, 'wechat/news/select', '微信图文选择', 0, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (105, 'wechat/news/image', '微信图片选择', 0, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (106, 'wechat/news/add', '添加微信图文', 0, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (107, 'wechat/news/edit', '编辑微信图文', 0, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (108, 'wechat/news/del', '删除微信图文', 0, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (109, 'wechat/news/push', '推送微信图文', 0, 1, 1, '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES (110, 'wechat/news', '微信图文管理', 0, 1, 1, '2018-05-04 11:19:35');
INSERT INTO `system_node` VALUES (111, 'wechat/tags', '微信粉丝标签管理', 0, 1, 1, '2018-05-04 11:20:28');
INSERT INTO `system_node` VALUES (112, 'wechat/tags/index', '粉丝标签列表', 1, 1, 1, '2018-05-04 11:20:28');
INSERT INTO `system_node` VALUES (113, 'wechat/tags/add', '添加粉丝标签', 0, 1, 1, '2018-05-04 11:20:28');
INSERT INTO `system_node` VALUES (114, 'wechat/tags/edit', '编辑粉丝标签', 0, 1, 1, '2018-05-04 11:20:29');
INSERT INTO `system_node` VALUES (115, 'wechat/tags/del', '删除粉丝标签', 0, 1, 1, '2018-05-04 11:20:29');
INSERT INTO `system_node` VALUES (116, 'wechat/tags/sync', '同步粉丝标签', 0, 1, 1, '2018-05-04 11:20:29');
INSERT INTO `system_node` VALUES (117, 'store/goods', '商品管理', 0, 1, 1, '2018-05-04 11:29:55');
INSERT INTO `system_node` VALUES (118, 'store/goods/index', '商品列表', 1, 1, 1, '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES (119, 'store/goods/add', '添加商品', 0, 1, 1, '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES (120, 'store/goods/edit', '编辑商品', 0, 1, 1, '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES (121, 'store/goods/del', '删除商品', 0, 1, 1, '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES (122, 'store/goods/forbid', '下架商品', 0, 1, 1, '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES (123, 'store/goods/resume', '上架商品', 0, 1, 1, '2018-05-04 11:29:57');
INSERT INTO `system_node` VALUES (124, 'store/goods_brand', '商品品牌管理', 0, 1, 1, '2018-05-04 11:30:44');
INSERT INTO `system_node` VALUES (125, 'store/goods_brand/index', '商品品牌列表', 1, 1, 1, '2018-05-04 11:30:52');
INSERT INTO `system_node` VALUES (126, 'store/goods_brand/add', '添加商品品牌', 0, 1, 1, '2018-05-04 11:30:55');
INSERT INTO `system_node` VALUES (127, 'store/goods_brand/edit', '编辑商品品牌', 0, 1, 1, '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES (128, 'store/goods_brand/del', '删除商品品牌', 0, 1, 1, '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES (129, 'store/goods_brand/forbid', '禁用商品品牌', 0, 1, 1, '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES (130, 'store/goods_brand/resume', '启用商品品牌', 0, 1, 1, '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES (131, 'store/goods_cate', '商品分类管理', 0, 1, 1, '2018-05-04 11:31:19');
INSERT INTO `system_node` VALUES (132, 'store/goods_cate/index', '商品分类列表', 1, 1, 1, '2018-05-04 11:31:23');
INSERT INTO `system_node` VALUES (133, 'store/goods_cate/add', '添加商品分类', 0, 1, 1, '2018-05-04 11:31:23');
INSERT INTO `system_node` VALUES (134, 'store/goods_cate/edit', '编辑商品分类', 0, 1, 1, '2018-05-04 11:31:23');
INSERT INTO `system_node` VALUES (135, 'store/goods_cate/del', '删除商品分类', 0, 1, 1, '2018-05-04 11:31:24');
INSERT INTO `system_node` VALUES (136, 'store/goods_cate/forbid', '禁用商品分类', 0, 1, 1, '2018-05-04 11:31:24');
INSERT INTO `system_node` VALUES (137, 'store/goods_cate/resume', '启用商品分类', 0, 1, 1, '2018-05-04 11:31:24');
INSERT INTO `system_node` VALUES (138, 'store/goods_spec', '商品规格管理', 0, 1, 1, '2018-05-04 11:31:47');
INSERT INTO `system_node` VALUES (139, 'store/goods_spec/index', '商品规格列表', 1, 1, 1, '2018-05-04 11:31:47');
INSERT INTO `system_node` VALUES (140, 'store/goods_spec/add', '添加商品规格', 0, 1, 1, '2018-05-04 11:31:47');
INSERT INTO `system_node` VALUES (141, 'store/goods_spec/edit', '编辑商品规格', 0, 1, 1, '2018-05-04 11:31:48');
INSERT INTO `system_node` VALUES (142, 'store/goods_spec/del', '删除商品规格', 0, 1, 1, '2018-05-04 11:31:48');
INSERT INTO `system_node` VALUES (143, 'store/goods_spec/forbid', '禁用商品规格', 0, 1, 1, '2018-05-04 11:31:48');
INSERT INTO `system_node` VALUES (144, 'store/goods_spec/resume', '启用商品规格', 0, 1, 1, '2018-05-04 11:31:48');
COMMIT;

-- ----------------------------
-- Table structure for system_sequence
-- ----------------------------
DROP TABLE IF EXISTS `system_sequence`;
CREATE TABLE `system_sequence` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL COMMENT '序号类型',
  `sequence` char(50) NOT NULL COMMENT '序号值',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_sequence_unique` (`type`,`sequence`) USING BTREE,
  KEY `index_system_sequence_type` (`type`),
  KEY `index_system_sequence_number` (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统序号表';

-- ----------------------------
-- Table structure for system_sms
-- ----------------------------
DROP TABLE IF EXISTS `system_sms`;
CREATE TABLE `system_sms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mobiles` text COLLATE utf8mb4_unicode_ci COMMENT '电话号码列表',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '短信内容',
  `sign` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '短信签名',
  `created_at` int(11) DEFAULT NULL COMMENT '发送时间',
  `ip` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '发送IP',
  `userid` bigint(20) unsigned DEFAULT '0' COMMENT '用户ID',
  `type` tinyint(1) DEFAULT '0' COMMENT '短信类型',
  `res` tinyint(1) DEFAULT '0' COMMENT '短信发送状态0成功1失败',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `type` (`type`),
  KEY `res` (`res`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of system_sms
-- ----------------------------
BEGIN;
INSERT INTO `system_sms` VALUES (1, '13119140520', '【付二代】您的验证码为123456，打死也不要告诉其他人哦！', '付二代', NULL, '192.168.11.1', 0, 1, 1);
COMMIT;

-- ----------------------------
-- Table structure for system_user
-- ----------------------------
DROP TABLE IF EXISTS `system_user`;
CREATE TABLE `system_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户登录名',
  `password` char(60) NOT NULL DEFAULT '' COMMENT '用户登录密码',
  `qq` varchar(16) DEFAULT NULL COMMENT '联系QQ',
  `mail` varchar(32) DEFAULT NULL COMMENT '联系邮箱',
  `phone` varchar(16) DEFAULT NULL COMMENT '联系手机号',
  `desc` varchar(255) DEFAULT '' COMMENT '备注说明',
  `login_num` bigint(20) unsigned DEFAULT '0' COMMENT '登录次数',
  `login_at` datetime DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `authorize` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(1:删除,0:未删)',
  `create_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `password_encode` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_user_username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- ----------------------------
-- Records of system_user
-- ----------------------------
BEGIN;
INSERT INTO `system_user` VALUES (10000, 'admin', '$2y$10$JQY2S1a7PgIa2lNRkDDfXO9Balx6lztNKG/rBXhKv..ZQtsn89XLi', '22222222', 'yjq@zhishensoft.com', '17693355155', '', 22990, '2018-05-30 09:49:41', 1, '', 0, NULL, '2015-11-13 15:14:22', '1d1e1f1g1h1i');
COMMIT;

-- ----------------------------
-- Table structure for wechat_fans
-- ----------------------------
DROP TABLE IF EXISTS `wechat_fans`;
CREATE TABLE `wechat_fans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appid` char(50) DEFAULT '' COMMENT '公众号Appid',
  `unionid` char(100) DEFAULT '' COMMENT 'unionid',
  `openid` char(100) DEFAULT '' COMMENT '用户的标识,对当前公众号唯一',
  `spread_openid` char(100) DEFAULT '' COMMENT '推荐人OPENID',
  `spread_at` datetime DEFAULT NULL COMMENT '推荐时间',
  `tagid_list` varchar(100) DEFAULT '' COMMENT '标签id',
  `is_black` tinyint(1) unsigned DEFAULT '0' COMMENT '是否为黑名单用户',
  `subscribe` tinyint(1) unsigned DEFAULT '0' COMMENT '用户是否关注该公众号(0:未关注, 1:已关注)',
  `nickname` varchar(200) DEFAULT '' COMMENT '用户的昵称',
  `sex` tinyint(1) unsigned DEFAULT NULL COMMENT '用户的性别,值为1时是男性,值为2时是女性,值为0时是未知',
  `country` varchar(50) DEFAULT '' COMMENT '用户所在国家',
  `province` varchar(50) DEFAULT '' COMMENT '用户所在省份',
  `city` varchar(50) DEFAULT '' COMMENT '用户所在城市',
  `language` varchar(50) DEFAULT '' COMMENT '用户的语言,简体中文为zh_CN',
  `headimgurl` varchar(500) DEFAULT '' COMMENT '用户头像',
  `subscribe_time` bigint(20) unsigned DEFAULT '0' COMMENT '用户关注时间',
  `subscribe_at` datetime DEFAULT NULL COMMENT '关注时间',
  `remark` varchar(50) DEFAULT '' COMMENT '备注',
  `expires_in` bigint(20) unsigned DEFAULT '0' COMMENT '有效时间',
  `refresh_token` varchar(200) DEFAULT '' COMMENT '刷新token',
  `access_token` varchar(200) DEFAULT '' COMMENT '访问token',
  `subscribe_scene` varchar(200) DEFAULT '' COMMENT '扫码关注场景',
  `qr_scene` varchar(100) DEFAULT '' COMMENT '二维码场景值',
  `qr_scene_str` varchar(200) DEFAULT '' COMMENT '二维码场景内容',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_wechat_fans_spread_openid` (`spread_openid`) USING BTREE,
  KEY `index_wechat_fans_openid` (`openid`) USING BTREE,
  KEY `index_wechat_fans_unionid` (`unionid`),
  KEY `index_wechat_fans_is_back` (`is_black`),
  KEY `index_wechat_fans_subscribe` (`subscribe`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8 COMMENT='微信粉丝';

-- ----------------------------
-- Records of wechat_fans
-- ----------------------------
BEGIN;
INSERT INTO `wechat_fans` VALUES (115, 'wx60a43dd8161666d4', 'oGsrkszQSaou6tKM-4c2Tl625Ta8', 'o38gps8yp0qvO2Oa0OelGA66uX8k', '', NULL, '2', 0, 1, '\\ud83d\\ude36 夏俊傑', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmibWgR3lGBjD5TklmhGiabE4Xp8rxcpMaYYY6j1lDprFru604gxHdtibI0cOMyIJW6mD3RSlsJv31VQ/132', 1484634161, '2017-01-17 14:22:41', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:36');
INSERT INTO `wechat_fans` VALUES (116, 'wx60a43dd8161666d4', 'oGsrkszu7FHs2Hr-8ykeA5BJKgts', 'o38gps3O4b5xjY3PpBlzysfy5jCI', '', NULL, '2', 0, 1, 'Aa杨霄', 1, '葡萄牙', '里斯本', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLDXPX37coIglH5yxKLXdgh0ZopOce29Muq1YdRVQgPzR39uBSgTMWM2YsPGXObxDLmibg8gSLxlUOA/132', 1522372179, '2018-03-30 09:09:39', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:36');
INSERT INTO `wechat_fans` VALUES (117, 'wx60a43dd8161666d4', 'oGsrks1qovmJUGycmiAKSry3ISp4', 'o38gps4pJjHGkXVGe1aXxgzKJ7u4', '', NULL, '', 0, 1, 'Y.c', 1, '中国', '贵州', '贵阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFkzMmUKxtqiafIDXib9Lqm5oVA1ibiakiaYSBPQ6beNEtQ3a7I6QticC4j0FHlunxHBKUaQ6l4sxN3GYDYw/132', 1517301228, '2018-01-30 16:33:48', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:36');
INSERT INTO `wechat_fans` VALUES (118, 'wx60a43dd8161666d4', 'oGsrks466bfWg1LyfW_Xiw3AwGYE', 'o38gps94DZxL0ieM0sSe0hpgiZmc', '', NULL, '', 0, 1, '湘君', 1, '中国', '北京', '朝阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLBEJaohyO270mqh2SgXibg7PWZh4nwFWbIqj0jCJYWTOaYuApmgmiajc0ZltWn7xZnRJGVK0Pqp0Ojw/132', 1524492369, '2018-04-23 22:06:09', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:36');
INSERT INTO `wechat_fans` VALUES (119, 'wx60a43dd8161666d4', 'oGsrks9XUFq6O6g2len8l2TaS6dA', 'o38gps5khVMMG2trfekmkNX13VOI', '', NULL, '2', 0, 1, '成哲', 1, '中国', '福建', '泉州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM5BicBM70CuKklke6GvFFGzPibK60aR42zibKfpOe6xzwJFlUreOmTtagKXYyK6p2tlqaKAx3ISMyaCw/132', 1497508654, '2017-06-15 14:37:34', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:36');
INSERT INTO `wechat_fans` VALUES (120, 'wx60a43dd8161666d4', 'oGsrks9i1WGC341Sw8YSzLh_L-ws', 'o38gps-IeXhG6ghhF1oUHmPrC4mw', '', NULL, '', 0, 1, '胜!!!', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqWzwNeqjpichF75WDj9sib9oexCiaU8sY3fZtmNUA3Qnc7wDKG3csRuIrN5wbfospITr4icqa89IGUruHOHQstdEy4/132', 1499953283, '2017-07-13 21:41:23', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:36');
INSERT INTO `wechat_fans` VALUES (121, 'wx60a43dd8161666d4', 'oGsrkswZ9aRbMxNeADBiIoFs7Ixs', 'o38gps5cxg7TIJjhW40hcfx3ceM0', '', NULL, '', 0, 1, '大豪', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqWzwNeqjpichAgPGia3yWJjWELUkZB1ZeNLiaA5FUtiaicc4t1nicDW8j7bllJVnAlKNYOD3hrXiaricadxuzTUveqIghR/132', 1501054131, '2017-07-26 15:28:51', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (122, 'wx60a43dd8161666d4', 'oGsrkszCVFA8v-3_FGYhmTRiURlc', 'o38gpsza67FfLSapYrfBTo1Zu5zw', '', NULL, '', 0, 1, '杨永安', 1, '中国', '陕西', '汉中', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnjFHIKUiabCChkMVagHWKWmHrtxcgfrSRM9SrYjhcXvXTIuTwcfoT2g2HjdckiaxUyicU2T6hcKazca6IzF1vKz2Jic/132', 1514250857, '2017-12-26 09:14:17', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (123, 'wx60a43dd8161666d4', 'oGsrks2d7Vj8sqMh8A5RV19_gJqw', 'o38gps7Xb4ksNPSnNlVkagiVmrGM', '', NULL, '', 0, 1, '过期的电影票', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mkKSyWyia8EEu1g1fcjgHTibDIXAwBwib0qzfY7pMePkIyDv7IWXYhCM8libKKX3BSEC5DwQmjKnPDaEFGZm0JQrBg/132', 1525858013, '2018-05-09 17:26:53', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (124, 'wx60a43dd8161666d4', 'oGsrks8FUHqgI4nOt8JJP8qd8ioE', 'o38gps7jMkHnBSV6JN-Rpgog6lu0', '', NULL, '', 0, 1, '生命精神', 1, '中国', '浙江', '杭州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6KEjKT4aGIbWCNCDHrc42BLCdb9sk9mlwpWlRklzZjAicXeA1owtTOVJDHMPia0gKMJ36loRSK1vng/132', 1476402502, '2016-10-14 07:48:22', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (125, 'wx60a43dd8161666d4', 'oGsrks2Mv-0flhVG8Oi-bZZSdRFE', 'o38gps1COGG9--oRbPIazGFPmtcE', '', NULL, '2', 0, 1, 'Howard 自在', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnjFHIKUiabCChlvlBvNO4ItvQANPqmdKRIoDJTXm56vaS0ic2luQH0MwyMglnuYJlrVHNJp78RhYkknibSZI68RvZp/132', 1466066875, '2016-06-16 16:47:55', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (126, 'wx60a43dd8161666d4', 'oGsrks5PzhNfMRmV1E5dL6Ydsm4A', 'o38gps9lnht0uxYR6cTlnntT6bA0', '', NULL, '', 0, 1, '_Xiαпg™', 1, '中国', '广东', '深圳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mkKSyWyia8EEvwt23OaKQmcibicbzKmyDHP8HNeHEHqc552NUtZeoTZ4PhBNYWvYyae5WeSa6KRoq4gtwciamjI9DI/132', 1496038821, '2017-05-29 14:20:21', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (127, 'wx60a43dd8161666d4', 'oGsrks9Sl2pcIhFCLoXBwxFi5P8w', 'o38gps2DCEuBJkh-5i9kGg7lxNsE', '', NULL, '', 0, 1, '在路上', 1, '中国', '上海', '浦东新区', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLCicuAKzkTwgEBjApfbJO4leQhpxR3kLssystsrsUAKudY3laRzIzRplFLaWMOia9CILwkn3BICiarVA/132', 1496907572, '2017-06-08 15:39:32', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:37');
INSERT INTO `wechat_fans` VALUES (128, 'wx60a43dd8161666d4', 'oGsrks7bq2oijI9rA38zZ1KxTHyA', 'o38gps1-O1OEFn6Fle0jQGoDuDdg', '', NULL, '', 0, 1, '无形…', 1, '中国', '四川', '成都', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFlqFUCaneic7zYBL9qFNibfCSyyTxQfRwlmYGE7EL54lHJ6DbuWUqfbvTLnWqiaMYzGSiatDNqwKwuE2z1OLKygepqe/132', 1510799509, '2017-11-16 10:31:49', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (129, 'wx60a43dd8161666d4', 'oGsrks68g1elLyggOA9wUXezjkVY', 'o38gps-clgtfbM1BjVHTV7OdQTiY', '', NULL, '', 0, 1, '瓦力', 1, '中国', '河南', '洛阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLCM2O5qico9GsVRoicn1HHh6BXuyJ1xUkjuc0icq1e1DN6hh87P5KchiblFkpYic9cHmlcQwBXdwKebQBg/132', 1498804317, '2017-06-30 14:31:57', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (130, 'wx60a43dd8161666d4', 'oGsrksydSQWBQZBegH8J5u6CM8Jc', 'o38gpszDg5iN-1GlAObk6Lj4RhfM', '', NULL, '2', 0, 1, '谢永发', 1, '中国', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mnLu0ourZB5uibPZM6m9cCXO2AmPTHHciaMjgLbibRTjpjKwrbFmhqfPKkXbttWZj8uGibcaphnicibSng/132', 1492329188, '2017-04-16 15:53:08', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (131, 'wx60a43dd8161666d4', 'oGsrks-mJu7hqiRMhNDPVGR0Fu9o', 'o38gpszLGFY86inXygfs3klN5QMI', '', NULL, '', 0, 1, '叫我小周', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaEIxKQLuhIBibnLUkbHjfyiaKXBWaZcWbS6vibLAiaicFDkMWyxia4Mt75pUMp0WiczciaEUM8keeQg71D7X6A/132', 1461314119, '2016-04-22 16:35:19', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (132, 'wx60a43dd8161666d4', 'oGsrks0Q0Q5WAT-aSKHuJsmXjojU', 'o38gps_d4aGPEpf6eyRH73SAuCuY', '', NULL, '', 0, 1, '李彦18127877079微商模式订制', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mkKSyWyia8EEquArYSyUgT9dHEUBrFMvuUVWTAQMVcTpicEUicafhGqzYHyslrvLUvZ5TziaEKJvcYQkq6hOBw5qNk/132', 1501207299, '2017-07-28 10:01:39', '', 0, '', '', 'ADD_SCENE_PROFILE_CARD', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (133, 'wx60a43dd8161666d4', 'oGsrks3RDM763gLoXTjYf4IIdvsw', 'o38gpsxgibwocmmEPvy243rqJrnM', '', NULL, '2,212', 0, 1, '莫逸明', 1, '中国', '福建', '泉州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaEI524M8pnULgrPJWC55dSBnroZshpbGTWibWf8xjvmnaich9w3DiaAqkHwWXXWHQia6FurBEq0nR083Sw/132', 1526543434, '2018-05-17 15:50:34', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (134, 'wx60a43dd8161666d4', 'oGsrks-h2mrLJJge7Xjm7lvqmY7Q', 'o38gps9Fudsconvbbbe4V4CVRdiE', '', NULL, '', 0, 1, 'Star', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpFmLulj72Nqqc0RAwtQvSC0w4doadQ6qYvrMbib8g7mOekQ3CARFr0gib0uPQtNozooRCbBvq2XNdv0XFVH5Cpbr/132', 1521112993, '2018-03-15 19:23:13', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (135, 'wx60a43dd8161666d4', 'oGsrks6MdHVQC87MqEKpFSNDJlFw', 'o38gps_8KRAfvQe8C6oMD-U82jKA', '', NULL, '', 0, 1, 'La', 1, '不丹', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaEIHVyPlN3ypIhRkQItGPR6ftHPoJfkBU2kpAss4llZTQicuUDWicvLCh1J6P26VA3A8eFOKGP5ZqHPg/132', 1521186882, '2018-03-16 15:54:42', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:38');
INSERT INTO `wechat_fans` VALUES (136, 'wx60a43dd8161666d4', 'oGsrks1_4lRQum_CKGWAVsDZxYps', 'o38gps33cPD8u2cxNx5evYBYIVs8', '', NULL, '', 0, 1, '虢人逸尘', 1, '中国', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLBia7xaYiafnETkiczAxjqJsAuyEiapPbjcEFqO3HWtPC2Ekny5jY1Dq8dPCLDRZGFoxBy1ELOia1hLkDg/132', 1520303270, '2018-03-06 10:27:50', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (137, 'wx60a43dd8161666d4', 'oGsrksx8x7WPo5hELWahpPnbDcKw', 'o38gps1OSpEMht-JsrSZ2-WwqZpY', '', NULL, '2', 0, 1, '蚊子', 1, '中国', '浙江', '杭州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6W7naf6WCsmEcEKXZ7EDlHR4RtbKzvfAOxDf4ZicAZ9yRU0MA3icVW0PSmgHR7hd7AR8JZQcumBKxw/132', 1499867678, '2017-07-12 21:54:38', '', 0, '', '', 'ADD_SCENE_PROFILE_CARD', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (138, 'wx60a43dd8161666d4', 'oGsrkswwR675FcwfISOp8U2OU2VE', 'o38gpsy-fZ8nL60jJvW5S2xmRcNk', '', NULL, '', 0, 1, 'A.Scenery...\\ud83c\\udf6d', 2, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM5lm6xYnmaxRD8VibUT0xs83DgQw6hM3cDHW2meFn3RW2dNHyuiajKlmmRBO8BeibVyluNVQ3NV2rVgQ/132', 1521113148, '2018-03-15 19:25:48', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (139, 'wx60a43dd8161666d4', 'oGsrks6zpXTf3A9n6hJeElMM1CF0', 'o38gps31jfVXc2XAHI-kkkb68btw', '', NULL, '', 0, 1, '辫子编程', 1, '中国', '河北', '石家庄', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1lKOpDLzcicpHdUPtFHovDeujrfCwD1ibRplDic3W5aaziaZAFoX22085f82zl9FGmjGIADnPsKIgGaSg/132', 1505962042, '2017-09-21 10:47:22', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (140, 'wx60a43dd8161666d4', 'oGsrks7b6Vr4xR5zEfUxfkocvpFs', 'o38gps1zUbMdHqVrneOcRX2s9w-Y', '', NULL, '', 0, 1, '叫我小贤\\ue131', 2, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbDstmoknTNNDJ5UaLHQsbkvpjJUAT2yBtiaGYbAQcLWCr0zhyoPTonqTHxNmKSQUAHALIQ3NLVTr0/132', 1500262365, '2017-07-17 11:32:45', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (141, 'wx60a43dd8161666d4', 'oGsrks1-omPbZPIyJjqDHGrJ2uNU', 'o38gpszg_-jIQYq6Iy2zBj3N5ndM', '', NULL, '', 0, 1, '周峥宇\\ud83e\\udd20我又胖了', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaEJDT62uNn5Apd4hOGVbkYLIr0JeCuZiakMRYnpqcEibwbJhgd6tKLiba66NFNsFbWibshGup3ITAKsUhA/132', 1500284215, '2017-07-17 17:36:55', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (142, 'wx60a43dd8161666d4', 'oGsrks3xwnOorGl_tvt9oupQLxwY', 'o38gps8HQRZr3mk32MdM-KCm1rAI', '', NULL, '2', 0, 1, 'Aries', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbMBdNOwMpBIJNI4IguZ4d6BzQ6yKVxHWJmvNNZzjrMbloHj0Tm65p996cWmgib51WkLzxQiaNZ3gtl/132', 1499998442, '2017-07-14 10:14:02', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:39');
INSERT INTO `wechat_fans` VALUES (143, 'wx60a43dd8161666d4', 'oGsrksxL7JHpBw-JQkzZlkoSKL_E', 'o38gps-YmcToFI9KPCIU50EzyApM', '', NULL, '', 0, 1, '动感排骨', 1, '阿拉伯联合酋长国', '迪拜', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1licrjBnJJkGRAIetAibGZMqsdFpIBUqjsL0CpTbTLWJwz48FHKZNj5VTOeQBBlJ8ufib3Qb3pLYvYfA/132', 1493167619, '2017-04-26 08:46:59', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (144, 'wx60a43dd8161666d4', 'oGsrks_ReHkrfr-fHmd9CROHrX7k', 'o38gps3KOfAj3hqGaLuYdi-F8Pcw', '', NULL, '', 0, 1, '(´-ι_-｀)', 1, '中国', '湖北', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM5Ba4Ha2y7wJQ5pH33bxJOCJXW4kHY48gwN2iaNB5L1Vib8eHxAzTnticHb9g2aLpEjAcRzqOknMNlMw/132', 1495683774, '2017-05-25 11:42:54', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (145, 'wx60a43dd8161666d4', 'oGsrks_UK83VQUMdgSmFY7XMw1z4', 'o38gps79_Co24GmxemVbXRxOGNJ0', '', NULL, '2', 0, 1, '蒋靖铭', 1, '中国', '上海', '静安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbBxZR0YUEOVxDo1lqXGmH3gH8KRian5QZJibj7zInb6KvqogNt6sm8y6OpvEhf7c2OiaPZamo3OD0ibF/132', 1526042180, '2018-05-11 20:36:20', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (146, 'wx60a43dd8161666d4', 'oGsrks-RmoWiiG-1_Esa9BPC0ECE', 'o38gps3zHPotg2lA2108tmhmBox8', '', NULL, '2', 0, 1, 'PHP-导演', 1, '中国', '广东', '清远', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFnzibnoULhfosrQurtvzia99vGiaVOBhuLZHUGaPQAOdWSOf8ZZkrHmT5zk5TrsfkJcmcvzUlpE9KK6A/132', 1526520957, '2018-05-17 09:35:57', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (147, 'wx60a43dd8161666d4', 'oGsrks4vN3pEBSCIwOB8_Juat5l0', 'o38gpswFGKNyVQe8WEiEPj5V_o-E', '', NULL, '2', 0, 1, '喧气...炎魔', 0, '', '', '', 'zh_CN', '', 1467451373, '2016-07-02 17:22:53', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (148, 'wx60a43dd8161666d4', 'oGsrks8wZprONrqzxV50sUxsbyEM', 'o38gps6H7P1RE2pkvyLgHhK5X5yQ', '', NULL, '', 0, 1, '小朋', 1, '中国', '陕西', '西安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gaa6Zyz3ZB4pLv8YZbq0yN35tS2H27fdAkkQIA1FlmHKr4c0Uz6G8nR3zLxNsPia2qzRNjygD57ia6s/132', 1461829132, '2016-04-28 15:38:52', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (149, 'wx60a43dd8161666d4', 'oGsrks5h-iq2PsHWnomgbHQW7cbI', 'o38gps2gTDZrgo7xDQcBnOv6kSCw', '', NULL, '', 0, 1, '肖', 1, '中国', '北京', '昌平', 'zh_TW', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6P5wB3cJN5SkRHzwKTJ8Ixtyk8LDcA6ztNKr0crdWx50AlE9moNcVbrniceict4ZpCPD9BXsKKyuSg/132', 1511144044, '2017-11-20 10:14:04', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:40');
INSERT INTO `wechat_fans` VALUES (150, 'wx60a43dd8161666d4', 'oGsrks-4KSMJUTVBrkiZwmKFnKkw', 'o38gpsyHiwv9XeLT7FnB-X-wXnrY', '', NULL, '2', 0, 1, '杨志斌', 1, '中国', '江西', '吉安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFnfDEMRYcBCAVHLhWduAGWWh1SmEfGnth6Qpibvzhiax5tUePAVg6w3CjbibUrACRrG9HxegTt0zCJ6A/132', 1468834443, '2016-07-18 17:34:03', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (151, 'wx60a43dd8161666d4', 'oGsrks_ByPI_LoQcutU_Efgh9VFo', 'o38gps-hP-kkUfiDRrPNNgHt-2sk', '', NULL, '2', 0, 1, '李兴宇（大宇）', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbH3GFewwtabnLnd1dFvjVwvH232efzzR96aia0iaur7EhEC1NEuTQRKcbfiaVYNxt0Wplkj4nkEU1sw/132', 1466065790, '2016-06-16 16:29:50', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (152, 'wx60a43dd8161666d4', 'oGsrks4BUDS-NijG9-oeGM8EQe6o', 'o38gps8_cEUKk8IosX7dz7RaYTAo', '', NULL, '', 0, 1, '佳佳', 1, '中国', '江西', '赣州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gadU9f6iczEE6bd0WyAicExIZ7szOCeKeXBwuP2fiatugpZLWf9cM2x06d6V06icZia3icwVtRY2KNPDiaXp/132', 1522484891, '2018-03-31 16:28:11', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', 'aa', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (153, 'wx60a43dd8161666d4', 'oGsrks3utvazyTi5SKhmE5355uUg', 'o38gps8nSZLqMhjA33FKNIvd1Htg', '', NULL, '2', 0, 1, '风哥', 1, '中国', '广东', '茂名', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gafmYIbicUicKOjEEleUYfcYxXib1GM0vFMgRO3ZjDHLssVlZlYdibiawod1GS5eIP5dbMibHgY1l3KUNR0/132', 1522159304, '2018-03-27 22:01:44', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (154, 'wx60a43dd8161666d4', 'oGsrks33jf6hGYbr-fN4hl2O3t2E', 'o38gps0VZNX4tMBGf9wc62ze50iY', '', NULL, '', 0, 1, 'clement', 1, '中国', '河南', '郑州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFnStnyIWbGOSib3R3feZytljzGuMh82Q9oXLJejdDoXGhJjGtRXicXVJcAmc8KRBQOIL7dMKp41YXVw/132', 1501581665, '2017-08-01 18:01:05', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (155, 'wx60a43dd8161666d4', 'oGsrks0JJffPwk-8i9k1omC812ho', 'o38gpswRdt7MOJ0qTYkvcTzLVOL0', '', NULL, '', 0, 1, '孙海锋', 1, '中国', '四川', '达州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM7vSbmvPujf2VxAicl4oZOXX7D6pZ5gx7YgQ3U8rS6rCm71y2G503AsaM4qAiafAUHSlypfLg6icMztg/132', 1499827720, '2017-07-12 10:48:40', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (156, 'wx60a43dd8161666d4', 'oGsrksy8u0sYaQ3n1oYliCkMzC68', 'o38gps2Tx8cdNfGzaEis2Mc4pvTI', '', NULL, '2', 0, 1, '周星星', 1, '中国', '陕西', '西安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gaXUd33E2xm49mibmfE14cLwH2ROdxK7kI8vsVeVPOUvKZgE6JcwUmX0ZXWAVP44dKeFLUIz4zDWtia/132', 1498098232, '2017-06-22 10:23:52', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:41');
INSERT INTO `wechat_fans` VALUES (157, 'wx60a43dd8161666d4', 'oGsrks23Z-JsvGGn9rFSNqBnW9ik', 'o38gps2HY7h23CJoVvk5PM303kNY', '', NULL, '', 0, 1, 'Shortfused', 1, '中国', '广东', '深圳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbOOaHOUFD9B5wSic3q3icK8EE58G8PY0f4nALGOWdia25Z7wqSSanlfK6tBGuysyTenTLSJCBLSVnXF/132', 1506316538, '2017-09-25 13:15:38', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (158, 'wx60a43dd8161666d4', 'oGsrks_hP6tLtqnPLDO2c2mdCiKU', 'o38gpsyh30CKHUULhEf4ILUQqZ5s', '', NULL, '', 0, 1, 'Promise', 1, '奥地利', '布尔根兰', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gaUeOuxB80yqBYX6YiaYUkIS54eEmUpelLPvFd0uXfeoreo5wdb4uxSeYEaia5IYw2tiaS98Pg0Q64XK/132', 1509438514, '2017-10-31 16:28:34', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (159, 'wx60a43dd8161666d4', 'oGsrks8MgmWGcHiTXw8-MVOud_jk', 'o38gps3vNdCqaggFfrBRCRikwlWY', '', NULL, '2', 0, 1, 'Anyon', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gabdQdx2gUg5S8g0bpwruPiccFZRPhicvIiazQISqcia9kKQBETsYnJiakE1DSFhUN7t529jXh7iaibBjB5z/132', 1525517924, '2018-05-05 18:58:44', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (160, 'wx60a43dd8161666d4', 'oGsrksyuQFA4llVF36HPs5Gv2UMQ', 'o38gps-uNjXogTeIcCq_Fs5d_J6E', '', NULL, '', 0, 1, '晨曦记忆，过往云烟', 1, '中国', '广西', '南宁', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqUSBo9niatdmhianibSUbNx9dPMCMsicTS0otEnkSW2BgBgaR93H1HNglXLFFqG4Q67W0YZbaHvw8HPA/132', 1497952981, '2017-06-20 18:03:01', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (161, 'wx60a43dd8161666d4', 'oGsrksx8UJymFAg31hkR5uBxhb5Y', 'o38gpsynrRbwkefHoGXz9XUMR3T8', '', NULL, '2', 0, 1, '王牌飞行员', 1, '马达加斯加', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM5o8TJlHMOkEKAhCqMDojlB0FC9lLFibT8RDKSrx9KicQ903R5G0AkzX8AhSwsU1cSGbsQdzticoFvWg/132', 1523346997, '2018-04-10 15:56:37', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (162, 'wx60a43dd8161666d4', 'oGsrks9yOCbqOASjxEwfMwVNildI', 'o38gps79rC4d7rwnfhmWnxXi_1i8', '', NULL, '2', 0, 1, '！', 2, '中国', '湖南', '长沙', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpZfaTicicM8gadY7DhO0NRC2opmzt49NslLu5tRhlJXSnfQMF3oPp230KOU4uSbZ4PSLBoaU4lTOtFm2koRrrkdt/132', 1461593762, '2016-04-25 22:16:02', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (163, 'wx60a43dd8161666d4', 'oGsrkszZVl6PTAtbzrmfkUl4KyLM', 'o38gpsxFtOXxBpZ9AjXs5RkEfPj0', '', NULL, '', 0, 1, 'Chen', 1, '中国', '广西', '南宁', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/QxhD6QevWDypRKO3jAbt91libGBOUmV1GibwYNJ7mQmP5xkdUDVoVXvN36bYeicD71pTFL4SgTbeqhe8DVvibt3uL6adwOt8J8bH/132', 1500003321, '2017-07-14 11:35:21', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:42');
INSERT INTO `wechat_fans` VALUES (164, 'wx60a43dd8161666d4', 'oGsrks7qKxIVsxToJYHuRANPEFB8', 'o38gps83KBbjv_gvAA41Hf2JTBDI', '', NULL, '', 0, 1, '谈德茂', 1, '中国', '湖北', '武汉', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFn6rUOLzb2HTQSoVHmHiaicqz45Td0ocFib53iaAzFicpuFwR9QAt8X87eotIhib1s69T8syxMxibiatJibGQw/132', 1504061982, '2017-08-30 10:59:42', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (165, 'wx60a43dd8161666d4', 'oGsrks_Yf9S9KemX-5shUhvPkWSk', 'o38gps_3EjXgILhDBDRhhrD7DMkk', '', NULL, '', 0, 1, 'NH', 2, '中国', '陕西', '西安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaELPibepDuGJ8xMgJCJRvBfwjVyq390nibich61YlXNFFia4aibvA4jibvcQtwLiczucKrdOpC25RHrReDSUA/132', 1461314624, '2016-04-22 16:43:44', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (166, 'wx60a43dd8161666d4', 'oGsrks4Kt8yXvT62xHWZr9fb3x4o', 'o38gps63NJv0wkeTK6zvUdtC9Nxs', '', NULL, '2', 0, 1, 'Z☀️', 1, '芬兰', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLD5Z04sNs9ZDlnbUO8SicvXKOPTBwgsLgVuWAY0xIJRtuJOa2BlULeibqLQU6ParSibvcFBdPKWQEhsQ/132', 1521766608, '2018-03-23 08:56:48', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (167, 'wx60a43dd8161666d4', 'oGsrks9xLZWtXDa_-EcAC_ZcGUO8', 'o38gps-kvqeQ7Y-M9lvWUr8tiUe4', '', NULL, '', 0, 1, '重庆柯一网络有限公司', 1, '中国', '重庆', '开县', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6ckejEPr4uDmcwxnHVV9bCI3F0z9I2iafDsaibL83XaUTnZSKEq3au5muWYic143d8GkbgR3auuJLMKcBWnZNuHKCK7larrO2qSU/132', 1526269424, '2018-05-14 11:43:44', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (168, 'wx60a43dd8161666d4', 'oGsrksxRpWEHDH9IdIxdus8_h57g', 'o38gpswZkDihqLaHEIgCYg2rDFek', '', NULL, '2', 0, 1, '梁金城', 1, '中国', '广东', '湛江', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbKOjhibtPQbN6WE2l1hqFEjZYPlHxMeEjoXiaP8lnbyTmzRUxgWGcgwibTcthlLdyAAs4pwhsgFYpIc/132', 1509860364, '2017-11-05 13:39:24', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (169, 'wx60a43dd8161666d4', 'oGsrksxKC6HssWvXSv2hHXNFHf6M', 'o38gps4ssW_HpC_wiML44kJDPObo', '', NULL, '', 0, 1, '山里人', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/QxhD6QevWDypRKO3jAbt90Hc72JgV7Go0ygPRztzQXn5OBn4sTD7icSJTzrUsiaE9gGE7oPaNRmXicZsd2OVQAbZN83VswO2rmd/132', 1497665032, '2017-06-17 10:03:52', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (170, 'wx60a43dd8161666d4', 'oGsrks9XBD7I8vJBYw0Um-x1vQxk', 'o38gps7_doPnGKUbJyHPEr_MlfWg', '', NULL, '', 0, 1, 'Administrator', 1, '中国', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaa2ZABsHxhbK3mBCa5hwhfzGevkNRBTX92lkpsaMvibfPZmv0cYJ0YFzmHDJSIuicLT4u3Ria1gEewDfd8iaoRMVl6/132', 1525709132, '2018-05-08 00:05:32', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:43');
INSERT INTO `wechat_fans` VALUES (171, 'wx60a43dd8161666d4', 'oGsrks3QCAmS0w84Rz-vEId_vEjE', 'o38gpsyhNsz9oufVc9cJWX8YTkTE', '', NULL, '', 0, 1, 'Biao', 1, '中国', '广西', '南宁', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFnLq4hSElRXEqHibwT5P6gee7Q9dk9ca8icSCBhHnTNbh9KteCYKgepTUar9USzJlZLX3TvvpFVCsU3fNXyby3Bky/132', 1505788891, '2017-09-19 10:41:31', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (172, 'wx60a43dd8161666d4', 'oGsrks8UdZ5u8ir_iDpJhmHgFi6c', 'o38gpswY_u4O7KGb31GSpgT-RF3U', '', NULL, '2', 0, 1, 'Leo.雷', 1, '中国', '河南', '洛阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0undWicxHU94s42kArNibbH1ylgicIuRQTI16q3p4x9ic57CXYnAFqBYsnic4un6UfFojntic2GGIEPeIGSn/132', 1526284465, '2018-05-14 15:54:25', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (173, 'wx60a43dd8161666d4', 'oGsrks6H0hBnuX9dRGeqNQmoFm_Y', 'o38gps0ZJKvxjEc0N2BOgR-T1doM', '', NULL, '', 0, 1, 'hhaoqin', 1, '安道尔', '', '', 'en', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlB7cxtx7JXGRKSDUv3LO0t3feLvvSsFpZUpfXDTicicB6pzBfVs8I5kTTa0XluQIC5wg4opxJHIAUe/132', 1513841189, '2017-12-21 15:26:29', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (174, 'wx60a43dd8161666d4', 'oGsrkswrLrpbSvX4_tCxLVl8f4kg', 'o38gps0g0bwxdeVfrbY2LukeJ4Jk', '', NULL, '2', 0, 1, 'Mr@ME', 1, '阿尔及利亚', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKq1TWQDicS9XErTKgEicfqicklicdo56a0m23tnGffIRZuaMnmRMcpMj9wHe6m1WgAjbOWqXW8xaQiakFwd1icHtZqVY6/132', 1522733858, '2018-04-03 13:37:38', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (175, 'wx60a43dd8161666d4', 'oGsrks68I0iO8K1a-ovuXlQbe6ek', 'o38gps35ZFbur02-CpnFoCJ-a_BM', '', NULL, '', 0, 1, '代号为纯洁\\ud83d\\ude43', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/r3xV6EGjibQyXicIfiaotnoMbk5QRmI77gjqtbVt7QUZbGcEhF6VKkGnwBqZiazqrBgnA80ibgUw11ywtQh9QpffLnicibic5TOpNOYO/132', 1487058795, '2017-02-14 15:53:15', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (176, 'wx60a43dd8161666d4', 'oGsrks5oAnVDeARhvzJsXkv9cwj0', 'o38gps2FSyO8uilTJiC5RqaqPptQ', '', NULL, '', 0, 1, 'Miranda', 2, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlK9MUdQ3sMKzPChibZRlLKNDfZQRU2OUshtAyFsUZL0BhxbrR09zrH1p1QFRb67gbKm0oaG6GSl6y/132', 1461418982, '2016-04-23 21:43:02', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (177, 'wx60a43dd8161666d4', 'oGsrks_cMX730air6ZKOSPhUv5bI', 'o38gps5zUa08f5ivxsv10AqPVTM8', '', NULL, '', 0, 1, '阿科', 1, '中国', '湖北', '荆州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlCrtvWZCGVUsTQpZzGR53Ykyg1rjg5jTWiaEDQKdtsewAwEXFavouZcwfOAF390ibDC9MXWOdoDwicy/132', 1517735573, '2018-02-04 17:12:53', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (178, 'wx60a43dd8161666d4', 'oGsrks6KXJGK44csA7Ye6kIfbLn4', 'o38gps2fH8aFwmFhFhU4Aurv18os', '', NULL, '', 0, 1, 'UM - 15524468881', 1, '中国', '辽宁', '沈阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0unQpG2L0JFNuvwW19VD6ZuhR3ycpbk1SjIp4daCm9sdrzWI7E4ZjlXbibRcvy1JKHYc9U7XPAQ3W8V/132', 1516189815, '2018-01-17 19:50:15', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:44');
INSERT INTO `wechat_fans` VALUES (179, 'wx60a43dd8161666d4', 'oGsrksyJ9EZJAjlzQdZnGByZiwyE', 'o38gps9GErwzTtZPTiNPC9f8ay1s', '', NULL, '2', 0, 1, '昂', 2, '中国', '吉林', '长春', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqWzwNeqjpichAISWLs8ZKSWr9b5C5k4k7sDW21KhYiaF9IlEs9ScPDXlSGrVIm3ZAwaj0gXAFS2ib0JgnrOZqESHE/132', 1511158120, '2017-11-20 14:08:40', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (180, 'wx60a43dd8161666d4', 'oGsrksz6Y7j2xO8I431jwYZD7cqU', 'o38gps0_E5_HkuvOlIz2_nj2KC1Q', '', NULL, '', 0, 1, 'Eric', 1, '中国', '广东', '惠州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1nnlN69LkpxYKkDtWQlibhLPqCL5e4FJ1SdQWWLjqXSXF0vU1jYSaQlBGeZiaeicUTE5N9y3gXHj9XDu6RngrXzNJT/132', 1521640528, '2018-03-21 21:55:28', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (181, 'wx60a43dd8161666d4', 'oGsrks4_Z_lgmXKceWYTRB3TLX5A', 'o38gps5wHVuGWDiA_j0PT-9bhzzk', '', NULL, '2', 0, 1, '王桦\\ue32e\\ue32e', 1, '中国', '湖南', '长沙', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSng0FcEtAeOfcSNXFzcPZ9s6ia9lrVbS5w4gTicR3RPmGfVl5WZrw5WKpqmibNJxj7ynl2jrxarcHic1WPGY70wPXicmk/132', 1526005045, '2018-05-11 10:17:25', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (182, 'wx60a43dd8161666d4', 'oGsrkswvewcgA2obzWXeCj_yIHzs', 'o38gpsycVcwBKD3CfXL4hhzvOgt4', '', NULL, '', 0, 1, 'linf', 1, '中国', '上海', '青浦', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlNiaEKOh52TwmbCSiaUavoqRAGZvgjlcEbgibZlHUODepwaicMcME7EYD5q7wicOPKBKtEIYzFicibES4gK/132', 1495794329, '2017-05-26 18:25:29', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (183, 'wx60a43dd8161666d4', 'oGsrks2xiOxVbOWvaJYHEWkGxzWk', 'o38gps-cq3BpRloot4Zn_SgkERIU', '', NULL, '', 0, 1, '瑋瑋', 2, '中国', '湖南', '衡阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mWdjxsYDFxLgNia0zvtXj9NTLNic1btoLLpIGVu6CviafENqODPtZ4icH7YL07ImRciaEc6BwlJRmDxVfxic2pIiaeOTL/132', 1472542553, '2016-08-30 15:35:53', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (184, 'wx60a43dd8161666d4', 'oGsrks0jW9y25wKFxLurrUPrxvFw', 'o38gps-VE_f7mOEfN5NRf9fzYLNg', '', NULL, '2', 0, 1, '暴躁的jk苏', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0unbpictwz1iaj52jm6uia0YI65YR4sanNEZLA3mcCJW6Olz6iaHAVO36ahS1t7bgkBY7GbXdibcerSppHU/132', 1476260898, '2016-10-12 16:28:18', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (185, 'wx60a43dd8161666d4', 'oGsrksxKqIJpVprxJUsbAdZZ5tjU', 'o38gps9nSy6jFfqk8dS5QhtPh8nc', '', NULL, '', 0, 1, 'JHChan', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/bnoQ9ztOjnTj93lo94aVb4ZIwA4m2jKBZnqVpcjm7fKqua8fkgp8mIRwicl68uGpxZ65GibLCzJoNcbOVWbpQRYWib1D6VCGSrC/132', 1508309343, '2017-10-18 14:49:03', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:45');
INSERT INTO `wechat_fans` VALUES (186, 'wx60a43dd8161666d4', 'oGsrks4VBHbqs2SxQ0609pRM1vH8', 'o38gps2idCh8YqRLoPsefLL2oHAk', '', NULL, '', 0, 1, '零点', 1, '百慕大', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlPmBibkPfbc8YmOiaSpSnvmQInfUf9Bf0iaKMHCJzNHC6msVJRSHq4eYEUMCGjNTStmvu8x2jWUaPVic/132', 1525703752, '2018-05-07 22:35:52', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '222', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (187, 'wx60a43dd8161666d4', 'oGsrks3bi69ROBM7wdcnwLiCJN7g', 'o38gps0kRV2gBR97_kn69yXfiQ4o', '', NULL, '', 0, 1, '小҉楼҉一҉夜҉听҉春҉语҉', 1, '中国', '江苏', '南京', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlPKWmdmurlyTJvu98zJvDCfmfvHQUjPhaVmNdboy7p7LzXEPQfHVaOIvicS2MKaDm8HicKDKia0PdbX/132', 1512742506, '2017-12-08 22:15:06', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (188, 'wx60a43dd8161666d4', 'oGsrksy0OwP1IYm09qo_kaXyCxgc', 'o38gps76Z2Pn1Iv6y85sDS0jIgG0', '', NULL, '', 0, 1, '.', 1, '中国', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM7rYo6NN0asQjBKCjlPibyyxichoibBXLY1VeKHDw7dmdJLGHJzQtbqOgTdpsj9GGBLu77Ef2ezFp6g8qgDuhfKCQte5LcECqlPtw/132', 1503387391, '2017-08-22 15:36:31', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (189, 'wx60a43dd8161666d4', 'oGsrks8wNGTqTCcLsqXSLYAVeio8', 'o38gpsyU0XajgtHzkCbd5tGhTdEc', '', NULL, '', 0, 1, '劲松', 1, '中国', '湖北', '武汉', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlMDlhvyJq18DBIrKUJ5Fu2ibCeltbQAPEtvloLd1qornF5zHrzR4hODCuHxoYUk0daLOhsQd4WaSS/132', 1519129201, '2018-02-20 20:20:01', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (190, 'wx60a43dd8161666d4', 'oGsrks9HRARkEHMSqsZKh5L0kfhw', 'o38gps-a5LvX7yhK_q0RMmavHLZs', '', NULL, '', 0, 1, '柯靖', 1, '中国', '重庆', '江北', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mcAn4p8XickiciclKy1eG2Jxl41kaBGbib75xg8ocqKSAVuytCTKKbNoibARg3GPQDQyzjp5coqdZNBQiaEAWGtIgo9p/132', 1499769194, '2017-07-11 18:33:14', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (191, 'wx60a43dd8161666d4', 'oGsrksxBQW56NSMZvXuBDIDJh1iM', 'o38gps2sIrPg2g74VLwGgY-FRQz4', '', NULL, '', 0, 1, '二流子', 1, '中国', '四川', '成都', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/04pico2kFrly7X2ogvSRWBDd7QMXvRlU2JjI5Z6ORQ9a9lDa4Pva4eHczThGiafZzNSDlibExicFYfZBuUzsvqiafOp1k5AKO3jUz/132', 1502625756, '2017-08-13 20:02:36', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (192, 'wx60a43dd8161666d4', 'oGsrks4gMjJSBSWQbtyxwJiZQj18', 'o38gps0Trh8Ggyc7mwBH2XIRIAEc', '', NULL, '2', 0, 1, 'Mr. La', 1, '中国', '青海', '西宁', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlIw66OXtcgoHictuwB6ZyWRQGOkSeEuibMytbxyZvPmM6r1HJHdia4Hgr07toMBj4PczIlfjnIVAtaB/132', 1516598324, '2018-01-22 13:18:44', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:46');
INSERT INTO `wechat_fans` VALUES (193, 'wx60a43dd8161666d4', 'oGsrks5EfMlOFmfmwTwYG6WFlGCw', 'o38gps3PQyzns75wVKupe_8zQN14', '', NULL, '2', 0, 1, '杨翊(yì)', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlCiaF3icQUib1THBK28ZBOM4N474O3gdOKbWJP2yIBT1RiaBTbhEco6T9P3DAeq0Pt2nuibJIhghkURbV/132', 1464141541, '2016-05-25 09:59:01', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (194, 'wx60a43dd8161666d4', 'oGsrks5sAyLMOJ3PPJGNdsmP6h1c', 'o38gpszaBKphV_UFktrUqsPR6urI', '', NULL, '', 0, 1, '海', 1, '中国', '江苏', '无锡', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFlyib3icV0t4ELI8w65e0OfVz5gMhgt1aqyT5sJsQe7Wu8jXIbuhSO808mMUg0HYgAGFpOUVYsx6oHpibI6CmprcI2/132', 1522660780, '2018-04-02 17:19:40', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (195, 'wx60a43dd8161666d4', 'oGsrks5arhP8js3-C_Mc2tLOaB34', 'o38gps5soAzcDO6PvbTz3XE88VIY', '', NULL, '2', 0, 1, 'WGZXU', 2, '中国', '广东', '梅州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/KULsUKmynFu6SiahhNaO6M83UicxoTf3mIrWk80hyTKEGbyEibchVjFYT7ZILfC3JT75gicoaPU4yQFQiajgPY0wTQ9NVCAV7pN7k/132', 1472019816, '2016-08-24 14:23:36', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (196, 'wx60a43dd8161666d4', 'oGsrks9Bl7SPEjPgN-ui3eFMhDog', 'o38gpszG6ZnGcvNV7feJnT70MCkI', '', NULL, '', 0, 1, 'piu piu', 1, '澳大利亚', '堪培拉', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0unZbrGAx1eB9WAn3A6qlRrC6v49zINA1Om1zibUsQuR48enw1iajve86lEiciaWiboKzGaqTSSPF0HSRpO/132', 1496744829, '2017-06-06 18:27:09', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (197, 'wx60a43dd8161666d4', 'oGsrkszhE3kIdWyYlN4E_zgLWdUk', 'o38gpsyVfsO9gDNPoRDvsme8n3FE', '', NULL, '', 0, 1, 'A鼎盛电脑科技', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniataKbRtW1icXGOBCSkhgGs9MUyKylzOuWvqOoyX1OM8aXtTRKLek8yssWialQhneHdxBnl9vbU6OrJ6PaRQHuGm2/132', 1514213921, '2017-12-25 22:58:41', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (198, 'wx60a43dd8161666d4', 'oGsrks1Wd6i5m0YalfiuXg6X6a40', 'o38gpswc8aL1dUFMb2TcABmsksDw', '', NULL, '2', 0, 1, 'Adrian、', 1, '中国', '广东', '江门', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/6x8XdQdSNbyAXStw8vdF2G8hVk7RzNjpvZjExn3l7s7xLqlsL5unJvShahZ67kZXeytfxvySU1VXZ5JfGBvGOriahadYe9qtib/132', 1495683769, '2017-05-25 11:42:49', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (199, 'wx60a43dd8161666d4', 'oGsrks_nMhj7vI10yWxY8GzscIx0', 'o38gps4zEPecqw8rdL1b9d1D1YPo', '', NULL, '', 0, 1, '麦客', 1, '中国', '广东', '东莞', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/ajNVdqHZLLAiapCVthUgNysHHjeyicsQ8FtMhhjkcSOMX3yZaAK2IdRCsMO9J8H0ApicOvlZUsiaMiaibcouAftEwstiaTMQkMg5YB4YVicGIt8lGEc/132', 1493125763, '2017-04-25 21:09:23', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:47');
INSERT INTO `wechat_fans` VALUES (200, 'wx60a43dd8161666d4', 'oGsrkswo8fDPab1lg_UXQkK6kOFA', 'o38gps74mjt-oVMudAu7zHmD70Jk', '', NULL, '', 0, 1, 'Simon', 1, '中国', '云南', '昆明', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhmHmkWasbv8O5rEkY908OEKxibG9wgn22GbG7W8zmEiaia5rvuQggGlnWOGs5aFc34E8UG6dx01Iicg9ZDlvsyyE2V/132', 1506754302, '2017-09-30 14:51:42', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (201, 'wx60a43dd8161666d4', 'oGsrks1Vd2boIxw3KjWdfrgD2XjY', 'o38gps1Unf64JOTdxNdd424lsEmM', '', NULL, '', 0, 1, 'SuperX', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1m81icHORPw7chvQ4c1V0mJibS5Iia6elg2nJFpfmDvtibbEFvC9wIQaI4kibEybHnl2gwChAqQUBHlnh4eDKkOv7QPm/132', 1520409810, '2018-03-07 16:03:30', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '测试', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (202, 'wx60a43dd8161666d4', 'oGsrkszrkFk55kOq6LCuEA-IttMQ', 'o38gpsxVkzZd6UY_3c6qAN-LiUw8', '', NULL, '2', 0, 1, '姜敏', 1, '中国', '上海', '浦东新区', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0unXubkwiblxic68V1gx9oz6nJww5MtFOGnmlEu6fxDRUicLOlZBf16XOPiaaSWJSaXjlibshibQib0pic2ic07/132', 1520561646, '2018-03-09 10:14:06', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (203, 'wx60a43dd8161666d4', 'oGsrks_T-S1Pwwq5s4Y-2MQuhyi8', 'o38gps3mp9FP3TWfwYV__A_Tl5Wc', '', NULL, '', 0, 1, '自由翱翔', 1, '中国', '北京', '昌平', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mWdjxsYDFxLvkwZaaorkbJo5t94yw3cpmYU5d1DTo3pn2bcJozKRLRyQwbtmWPpgPg8CqOvibq5cLucjmibxHxQJ/132', 1525944068, '2018-05-10 17:21:08', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (204, 'wx60a43dd8161666d4', 'oGsrks19hs6qHZMmUlZhzA9xysys', 'o38gps742EdtTEHe4l8_p1unXbzA', '', NULL, '', 0, 1, '陈文清', 1, '中国', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0unVTJXAFdlIrHR3qsJ73JAxDuvbiaJHmibqovuwH2NAubxDKqKImlsWg6UmdRmPoiaACHe3P2IhMAmtD/132', 1526385516, '2018-05-15 19:58:36', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (205, 'wx60a43dd8161666d4', 'oGsrks5mX7Kr6b1guI9ecDgrXJVE', 'o38gps1tvPvDBPeMXFTZkFvBMxjU', '', NULL, '', 0, 1, '刘彪', 1, '中国', '广东', '深圳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFme3s8TLGP6cciaxANiaJdtJVZiaibE3x4QDqpFTsM0Hm4ibHR3bX0klc8QWNdbq2nLoRgdXo2sMGVxCnbfnb28TOMHB/132', 1515404964, '2018-01-08 17:49:24', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (206, 'wx60a43dd8161666d4', 'oGsrks6bAMKwkxgHnQtwZzrw7HH0', 'o38gps-kWCJNweHtom-Ur6-_7ipU', '', NULL, '', 0, 1, '郭树峰', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhmWzkEyg0xZlcRFJ9uzsHwpkn6mKuBCtRs8pO2wACy20joQ9fSwolf3Ny0achnCyUbKGn5ibpgJNDMkADy12ianZ/132', 1515727836, '2018-01-12 11:30:36', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:48');
INSERT INTO `wechat_fans` VALUES (207, 'wx60a43dd8161666d4', 'oGsrks93peyNDIECPpLAppB-kFvU', 'o38gps-4z7nkOJ9VljnrjZ1hJs9Q', '', NULL, '', 0, 1, '刘鹏飞 15890617626', 1, '中国', '河南', '郑州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKoicF6Kjzex0vJFrqTpJK7MOEVJgLwLz3f08sMEQLxMcwCKsJibmR4KCd9NPvTycT7w6TZ0ib1aehnt8PO2kkIWRDx/132', 1498788522, '2017-06-30 10:08:42', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (208, 'wx60a43dd8161666d4', 'oGsrks89DGZXf8VfajzOVlbAQ3z8', 'o38gps8K3eiJ3cNAU0TKkAs2a9CU', '', NULL, '2', 0, 1, 'Johnny Y.', 1, '中国', '湖南', '长沙', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKrfjA8ib5YQxVzoCRp5NbaegKQqDK1I6CRoXhXQ1Jr8P5MA8uicFmYXFibejGrKHEYd2QnUKelIgC5tSicxNJuuZ2iay/132', 1460548852, '2016-04-13 20:00:52', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (209, 'wx60a43dd8161666d4', 'oGsrks3ubNH4NlW9vTethfs9Yfk8', 'o38gps4yFLVFKQ5TRwRJF-gxhoFE', '', NULL, '', 0, 1, 'Colin', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqWzwNeqjpichLW6UrpKOcr0ct9y2nEAxfnhTWQMj6luiaMO8KYL4de5pAMoJyiaGrLaYiaH9liaIzvRhlXADgPqicyzu/132', 1513856956, '2017-12-21 19:49:16', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (210, 'wx60a43dd8161666d4', 'oGsrks7YjGV_35_A98qIyanIXx4s', 'o38gpswjzR0S7wMTdDI-lF6kvYaU', '', NULL, '', 0, 1, '卡卡', 1, '中国', '江苏', '苏州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlFh2E95z0gvulwLNVERZelr67hKMYGxCwbCMsaBapwt2Ef3a0ibLAicVtAzhVgOIvL3UGQTQOWwnq8/132', 1512692256, '2017-12-08 08:17:36', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (211, 'wx60a43dd8161666d4', 'oGsrksxkol9l-RayJxFnhi8ARb6E', 'o38gps0aBICw5jkQ9fYU77T2e-ig', '', NULL, '', 0, 1, '小时候', 1, '中国', '湖南', '长沙', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFnLhucNNeBTibnMkOfiaSPNXDl1c0ia74NCqHuk3GJO23PFy1wzFibIJCBLl1ibAxyWguBSPPAocQicelEBy8icoN163ZZ/132', 1513565731, '2017-12-18 10:55:31', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (212, 'wx60a43dd8161666d4', 'oGsrkswYnd9qL8EdvS-ABl55wb-I', 'o38gps6_chW-YV83sCx9fm_LOids', '', NULL, '2', 0, 1, '盼盼\\ud83d\\udd30雅滋美特®总部', 2, '中国', '陕西', '西安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKpbjOibFLiaRzBwY2ia7XH9Ive6oCQiazl8hRBQr8WORSph7a5swBFoCy9ZewOMqExx7wGQzt4z5Tehoa7qulnEXdO4/132', 1462429726, '2016-05-05 14:28:46', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (213, 'wx60a43dd8161666d4', 'oGsrks-DFJgLi94eTz9bR9b6hukU', 'o38gpsw8os7p6RnIW9zDF5INjd58', '', NULL, '2', 0, 1, '瑋瑋', 2, '中国', '湖南', '衡阳', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSngWiakNf5LZdzibaJ2Bzhc39n1crMbeqG55dylBrCwxX5uQ1l1WzmYfAcAn1JxKiaAicb6ym74aiba9wQD4nGWse7V8ib/132', 1472105946, '2016-08-25 14:19:06', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (214, 'wx60a43dd8161666d4', 'oGsrks-If1RQayI1JyPNp2MaK7T0', 'o38gps1CJrKQlN4iBneBRsc-6MeE', '', NULL, '', 0, 1, '一次就好', 1, '安道尔', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0undgyrM9BhrmZnKhxoByYbIeY0E4mzVibuwrK4Z7lu25HwWjBUiaa9sdFXx5s06oHd8oXn1qaicDVRCv/132', 1522825109, '2018-04-04 14:58:29', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:49');
INSERT INTO `wechat_fans` VALUES (215, 'wx60a43dd8161666d4', 'oGsrksxHmXVcZMJeySQ37uDytOmg', 'o38gpsxjv6TDDkmlPpOCybQ9tjJ0', '', NULL, '2', 0, 1, '　　　　　　　　', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqHxl82Xol99xTovqtPdth2ulKkXk8Ckibwop7NqC4SASx0iaFNB0FxtgF9ubk9xPWSDW1OLd7RNZibfp0XMJpIecia/132', 1491880522, '2017-04-11 11:15:22', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:50');
INSERT INTO `wechat_fans` VALUES (216, 'wx60a43dd8161666d4', 'oGsrks8AQ_mp1XePvYuUDAJ7oBJg', 'o38gpswwa0WyHEKVXaHtF0kcjoJk', '', NULL, '2', 0, 1, '堂堂', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKp5nibynO469KphibAhuVZ1ZWA5hR79YlLUT9l8ic4ckibpgkPlC0zm1Rwem9kLsN3kPBAhibfIWo7gBmPSvSsLjQKuE/132', 1467771020, '2016-07-06 10:10:20', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:50');
INSERT INTO `wechat_fans` VALUES (217, 'wx60a43dd8161666d4', 'oGsrkszEXoNDqIw5Og2WBNAiP2D0', 'o38gps4gSFILIoalRs0PP8FfSir8', '', NULL, '', 0, 1, '老何', 1, '中国', '福建', '福州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0unZegKCys0B6beoZrQBibU8vppYRwDfx10zxiaA7W7BSuFl0GN0lOjfpleP9h2MPpl2zOib7ZycbMCGh/132', 1502371948, '2017-08-10 21:32:28', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:50');
INSERT INTO `wechat_fans` VALUES (218, 'wx60a43dd8161666d4', 'oGsrks71BIPANqZDyv4B64e4Eozg', 'o38gps7sSKAiIosZcPtvDCIp5iqg', '', NULL, '', 0, 1, '风⃢染⃢季⃢末', 1, '北马里亚纳群岛', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlMqlYggVlxQQjEz0gI06NiaiciaLd1jFpjMmj6lc4SlQicuPtSAdmfXAoEhQeCIqY9cU7Agp8gYUYLAT/132', 1508379559, '2017-10-19 10:19:19', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (219, 'wx60a43dd8161666d4', 'oGsrks3QXReZSmopt5JNz_am1lCQ', 'o38gps2fmVA_eJ2EhBu29kvpDLOI', '', NULL, '2', 0, 1, '。', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/tqRiaNianNl1mWdjxsYDFxLqRncwW8zdd6Bk9QTRBiafaU94ZicVXPb74ydCh7a8tibJKFbuuDVRgG20RAhPhRtwLajdRU9LcFiaHo/132', 1522637873, '2018-04-02 10:57:53', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (220, 'wx60a43dd8161666d4', 'oGsrks6E_yoTD9yJy6jWbZvV7sQY', 'o38gpsx7d7_w2dxa9gTfwMWQ97U8', '', NULL, '2', 0, 1, '侯鹏₁₈₆₁₄₀₉₂₄₁₉', 1, '中国', '北京', '西城', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmr7dAfxG0uneCFichiazQclpYZKzU1ySaHqKwticfXkExrgwp7Hog0qiarfDmcaibA4lGYePbZthI2INJXibThkOWibYG/132', 1522400829, '2018-03-30 17:07:09', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '你', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (221, 'wx60a43dd8161666d4', 'oGsrks22hZFbB-J-Y98nM-h_9uMM', 'o38gps2Fh1GdM5rQkC3_Bckn3iEY', '', NULL, '', 0, 1, '永圣', 1, '中国', '陕西', '西安', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaEI86EI2Ha7IFPfp74kFRMzpgYKMwSialEYlhQrD2xKePzDU27MCmZhYayYAJ1gmsrsuBzVkfgeXl3F0k2dMElMEcNaKtRHZduxc/132', 1462429914, '2016-05-05 14:31:54', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (222, 'wx60a43dd8161666d4', 'oGsrks6Y0TUcHa-IhfxS0Me6gl4c', 'o38gps08RgM4pxI5aKCMvAC6iQNw', '', NULL, '', 0, 1, '麻水劲松_楚鸟', 1, '中国', '湖北', '荆州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/KULsUKmynFu6SiahhNaO6MwjVAA8Bb0TUf0aaElVVYCibMLqI5BNclYGhGM9Lgict7uI2UOxnfuricfcdUIa1SWb0tfBW1uqYewF/132', 1495687411, '2017-05-25 12:43:31', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (223, 'wx60a43dd8161666d4', 'oGsrks7RnY9rI0vf3urYIKH5uubQ', 'o38gps1QqE1NbrBTz_Ser_9u5vZk', '', NULL, '', 0, 1, '夜', 1, '中国', '湖南', '株洲', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqWzwNeqjpichEtIvQImQ8qq7Ty1MwD1zkJLvwF7icIBtNmnYAVuichwEY1BLnfEALblrhbFdV4oYFpDic4E0W0pg86/132', 1525395285, '2018-05-04 08:54:45', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (224, 'wx60a43dd8161666d4', 'oGsrks9nEbjHQbOOgKFjvgpw4qj4', 'o38gps4Awds1FrY2N9HAidN0FF8Q', '', NULL, '', 0, 1, 'E', 0, '', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/wLcWG0U6YFmZnSJjibNEXBmU7fMWicjOUlD8do5NtHCMicIgRE1WpW1Zgc4k5zd88icmsrDPiarV0I4vd9NOUq35eAia1icGjOuZaLI/132', 1509361509, '2017-10-30 19:05:09', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (225, 'wx60a43dd8161666d4', 'oGsrksxKTwgEWj0ApQlblA_V9NEo', 'o38gps9mF9Em-CTs7aV9B88DuTpw', '', NULL, '', 0, 1, 'yyhhyj\\ud83d\\ude02\\ud83d\\ude31', 0, '', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaZgJibibxOwB5fygtmZ1JTrNYwqxKRPBPJYhSmibVzibKeuhQxbT8r9QLk4y9ro0ictValBGaibxIOiaQHW1RbgjFViahM/132', 1503323592, '2017-08-21 21:53:12', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:51');
INSERT INTO `wechat_fans` VALUES (226, 'wx60a43dd8161666d4', 'oGsrks0mBGjUxYLw1GGbQgNdaN4s', 'o38gpszoJoC9oJYz3UHHf6bEp0Lo', '', NULL, '', 0, 1, '伟红', 0, '', '', '', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSniaaiciciagbYiahct41to1c8qKJJq00odyYm1kskyE6og7ic0VSnuWankib2DOUZuHvkicGc9XZUN8c55uRb5PMMQmhlrT/132', 1514285567, '2017-12-26 18:52:47', '', 0, '', '', 'ADD_SCENE_SEARCH', '0', '', '2018-05-19 14:31:52');
INSERT INTO `wechat_fans` VALUES (227, 'wx60a43dd8161666d4', 'oGsrksxawVziyr-ePlU6nOxALYgA', 'o38gps47H7kIFrv0pQ15ODVQR1mA', '', NULL, '2', 0, 1, '才子佳人', 1, '中国', '广东', '广州', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/cXaM0LzKLKqWzwNeqjpichC3NRcX3D5LVKUapG9XE3xzbt2jdh0qlRYiabia541bkdsY81ICFWrcKhicbAOic4iatlKts4nSVosACa/132', 1461809072, '2016-04-28 10:04:32', '', 0, '', '', 'ADD_SCENE_OTHERS', '0', '', '2018-05-19 14:31:52');
INSERT INTO `wechat_fans` VALUES (228, 'wx60a43dd8161666d4', 'oGsrks1FaExlUOm_D9GvCuIoDrlc', 'o38gps6SnGauVsoHsK-KzEdntbpY', '', NULL, '', 0, 1, '晴天', 0, '中国', '安徽', '合肥', 'zh_CN', 'http://thirdwx.qlogo.cn/mmopen/lQEcz8ricSnhlmkw5WDLWlNGKlkIbqhZbEdlCXS9iafbO3oheJWIulpIuaV3RzQFohvdHzgic6vdHNWxcdlUIy4tibHUppxmZQq5/132', 1525588382, '2018-05-06 14:33:02', '', 0, '', '', 'ADD_SCENE_QR_CODE', '0', '', '2018-05-19 14:31:52');
COMMIT;

-- ----------------------------
-- Table structure for wechat_fans_tags
-- ----------------------------
DROP TABLE IF EXISTS `wechat_fans_tags`;
CREATE TABLE `wechat_fans_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `appid` char(50) DEFAULT NULL COMMENT '公众号APPID',
  `name` varchar(35) DEFAULT NULL COMMENT '标签名称',
  `count` int(11) unsigned DEFAULT NULL COMMENT '总数',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建日期',
  KEY `index_wechat_fans_tags_id` (`id`) USING BTREE,
  KEY `index_wechat_fans_tags_appid` (`appid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8 COMMENT='微信会员标签';

-- ----------------------------
-- Records of wechat_fans_tags
-- ----------------------------
BEGIN;
INSERT INTO `wechat_fans_tags` VALUES (2, 'wx60a43dd8161666d4', '星标组', 38, '2018-05-19 14:31:53');
INSERT INTO `wechat_fans_tags` VALUES (212, 'wx60a43dd8161666d4', 'abc', 1, '2018-05-19 14:31:53');
COMMIT;

-- ----------------------------
-- Table structure for wechat_keys
-- ----------------------------
DROP TABLE IF EXISTS `wechat_keys`;
CREATE TABLE `wechat_keys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `appid` char(100) DEFAULT '' COMMENT '公众号APPID',
  `type` varchar(20) DEFAULT '' COMMENT '类型，text 文件消息，image 图片消息，news 图文消息',
  `keys` varchar(100) DEFAULT NULL COMMENT '关键字',
  `content` text COMMENT '文本内容',
  `image_url` varchar(255) DEFAULT '' COMMENT '图片链接',
  `voice_url` varchar(255) DEFAULT '' COMMENT '语音链接',
  `music_title` varchar(100) DEFAULT '' COMMENT '音乐标题',
  `music_url` varchar(255) DEFAULT '' COMMENT '音乐链接',
  `music_image` varchar(255) DEFAULT '' COMMENT '音乐缩略图链接',
  `music_desc` varchar(255) DEFAULT '' COMMENT '音乐描述',
  `video_title` varchar(100) DEFAULT '' COMMENT '视频标题',
  `video_url` varchar(255) DEFAULT '' COMMENT '视频URL',
  `video_desc` varchar(255) DEFAULT '' COMMENT '视频描述',
  `news_id` bigint(20) unsigned DEFAULT NULL COMMENT '图文ID',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '排序字段',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '0 禁用，1 启用',
  `create_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_wechat_keys_appid` (`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信关键字';

-- ----------------------------
-- Table structure for wechat_menu
-- ----------------------------
DROP TABLE IF EXISTS `wechat_menu`;
CREATE TABLE `wechat_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `index` bigint(20) DEFAULT NULL,
  `pindex` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `type` varchar(24) NOT NULL DEFAULT '' COMMENT '菜单类型 null主菜单 link链接 keys关键字',
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `content` text NOT NULL COMMENT '文字内容',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0禁用1启用)',
  `create_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_wechat_menu_pindex` (`pindex`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信菜单配置';

-- ----------------------------
-- Table structure for wechat_news
-- ----------------------------
DROP TABLE IF EXISTS `wechat_news`;
CREATE TABLE `wechat_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` varchar(100) DEFAULT '' COMMENT '永久素材MediaID',
  `local_url` varchar(300) DEFAULT '' COMMENT '永久素材显示URL',
  `article_id` varchar(60) DEFAULT '' COMMENT '关联图文ID，用，号做分割',
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '是否删除',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `create_by` bigint(20) DEFAULT NULL COMMENT '创建人',
  PRIMARY KEY (`id`),
  KEY `index_wechat_news_artcle_id` (`article_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信图文表';

-- ----------------------------
-- Table structure for wechat_news_article
-- ----------------------------
DROP TABLE IF EXISTS `wechat_news_article`;
CREATE TABLE `wechat_news_article` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT '' COMMENT '素材标题',
  `local_url` varchar(300) DEFAULT '' COMMENT '永久素材显示URL',
  `show_cover_pic` tinyint(4) unsigned DEFAULT '0' COMMENT '是否显示封面 0不显示，1 显示',
  `author` varchar(20) DEFAULT '' COMMENT '作者',
  `digest` varchar(300) DEFAULT '' COMMENT '摘要内容',
  `content` longtext COMMENT '图文内容',
  `content_source_url` varchar(200) DEFAULT '' COMMENT '图文消息原文地址',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `create_by` bigint(20) DEFAULT NULL COMMENT '创建人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信素材表';

-- ----------------------------
-- Table structure for wechat_news_image
-- ----------------------------
DROP TABLE IF EXISTS `wechat_news_image`;
CREATE TABLE `wechat_news_image` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) DEFAULT '' COMMENT '文件md5',
  `local_url` varchar(300) DEFAULT '' COMMENT '本地文件链接',
  `media_url` varchar(300) DEFAULT '' COMMENT '远程图片链接',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_wechat_news_image_md5` (`md5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信服务器图片';

-- ----------------------------
-- Table structure for wechat_news_media
-- ----------------------------
DROP TABLE IF EXISTS `wechat_news_media`;
CREATE TABLE `wechat_news_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appid` varchar(100) DEFAULT '' COMMENT '公众号ID',
  `md5` varchar(32) DEFAULT '' COMMENT '文件md5',
  `type` varchar(20) DEFAULT '' COMMENT '媒体类型',
  `media_id` varchar(100) DEFAULT '' COMMENT '永久素材MediaID',
  `local_url` varchar(300) DEFAULT '' COMMENT '本地文件链接',
  `media_url` varchar(300) DEFAULT '' COMMENT '远程图片链接',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信素材表';

SET FOREIGN_KEY_CHECKS = 1;
