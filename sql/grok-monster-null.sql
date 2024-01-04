/*
 Navicat Premium Data Transfer

 Source Server         : Server_monster
 Source Server Type    : MySQL
 Source Server Version : 80024
 Source Host           : 16.163.252.193:3306
 Source Schema         : grok-monster

 Target Server Type    : MySQL
 Target Server Version : 80024
 File Encoding         : 65001

 Date: 01/12/2023 01:05:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_extension_histories
-- ----------------------------
DROP TABLE IF EXISTS `admin_extension_histories`;
CREATE TABLE `admin_extension_histories`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type` tinyint NOT NULL DEFAULT 1,
  `version` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
  `detail` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_extension_histories_name_index`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_extension_histories
-- ----------------------------
INSERT INTO `admin_extension_histories` VALUES (2, 'abovesky.dcat-media-player', 1, '1.0.0', 'Initialize extension.', '2022-02-27 17:30:16', '2022-02-27 17:30:16');
INSERT INTO `admin_extension_histories` VALUES (3, 'abovesky.dcat-lightbox', 1, '1.0.0', 'Initialize extension.', '2022-02-27 19:01:26', '2022-02-27 19:01:26');
INSERT INTO `admin_extension_histories` VALUES (4, 'super-eggs.dcat-distpicker', 1, '1.0.0', 'Initialize extension.', '2022-10-18 15:41:03', '2022-10-18 15:41:03');
INSERT INTO `admin_extension_histories` VALUES (5, 'super-eggs.dcat-distpicker', 1, '2.1.1', '新增多级显示', '2022-10-18 15:41:51', '2022-10-18 15:41:51');
INSERT INTO `admin_extension_histories` VALUES (6, 'super-eggs.dcat-distpicker', 1, '2.1.1', '新增数据回显方法', '2022-10-18 15:41:51', '2022-10-18 15:41:51');
INSERT INTO `admin_extension_histories` VALUES (7, 'super-eggs.dcat-distpicker', 1, '2.1.1', '修复一对多显示异常', '2022-10-18 15:41:51', '2022-10-18 15:41:51');
INSERT INTO `admin_extension_histories` VALUES (8, 'cin.dcat-config', 1, '1.0.0', '创建插件', '2022-10-20 03:59:03', '2022-10-20 03:59:03');
INSERT INTO `admin_extension_histories` VALUES (9, 'cin.dcat-config', 2, '1.0.1', 'create_config.php', '2022-10-20 03:59:46', '2022-10-20 03:59:46');
INSERT INTO `admin_extension_histories` VALUES (10, 'cin.dcat-config', 1, '1.0.1', '创建配置表', '2022-10-20 03:59:46', '2022-10-20 03:59:46');
INSERT INTO `admin_extension_histories` VALUES (11, 'dcat-admin.operation-log', 2, '1.0.0', 'create_opration_log_table.php', '2023-10-30 08:56:54', '2023-10-30 08:56:54');
INSERT INTO `admin_extension_histories` VALUES (12, 'dcat-admin.operation-log', 1, '1.0.0', 'Initialize extension.', '2023-10-30 08:56:54', '2023-10-30 08:56:54');
INSERT INTO `admin_extension_histories` VALUES (20, 'death_satan.dcat-wang-editor', 1, '1.0.0', 'Initialize extension.', '2023-11-26 23:00:32', '2023-11-26 23:00:32');

-- ----------------------------
-- Table structure for admin_extensions
-- ----------------------------
DROP TABLE IF EXISTS `admin_extensions`;
CREATE TABLE `admin_extensions`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `version` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `is_enabled` tinyint NOT NULL DEFAULT 0,
  `options` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admin_extensions_name_unique`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_extensions
-- ----------------------------
INSERT INTO `admin_extensions` VALUES (2, 'abovesky.dcat-media-player', '1.0.0', 1, NULL, '2022-02-27 17:30:16', '2022-02-27 19:03:00');
INSERT INTO `admin_extensions` VALUES (3, 'abovesky.dcat-lightbox', '1.0.0', 1, NULL, '2022-02-27 19:01:26', '2022-02-27 19:01:29');
INSERT INTO `admin_extensions` VALUES (4, 'super-eggs.dcat-distpicker', '2.1.1', 1, NULL, '2022-10-18 15:41:03', '2022-10-18 15:41:51');
INSERT INTO `admin_extensions` VALUES (6, 'cin.dcat-config', '1.0.1', 1, NULL, '2022-10-20 03:59:31', '2022-10-20 03:59:50');
INSERT INTO `admin_extensions` VALUES (7, 'dcat-admin.operation-log', '1.0.0', 1, NULL, '2023-10-30 08:56:54', '2023-10-30 08:58:31');
INSERT INTO `admin_extensions` VALUES (9, 'death_satan.dcat-wang-editor', '1.0.0', 1, NULL, '2023-11-26 23:00:32', '2023-11-26 23:00:37');

-- ----------------------------
-- Table structure for admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL DEFAULT 0,
  `order` int NOT NULL DEFAULT 0,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `uri` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `extension` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `show` tinyint NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 170 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_menu
-- ----------------------------
INSERT INTO `admin_menu` VALUES (1, 0, 1, '首页', 'feather icon-bar-chart-2', '/', '', 1, '2021-06-06 18:53:14', '2022-10-27 15:23:20');
INSERT INTO `admin_menu` VALUES (2, 0, 21, '系统配置', 'feather icon-settings', NULL, '', 1, '2021-06-06 18:53:14', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (3, 2, 22, '用户配置', NULL, 'auth/users', '', 1, '2021-06-06 18:53:14', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (4, 2, 23, '角色管理', NULL, 'auth/roles', '', 1, '2021-06-06 18:53:14', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (5, 2, 24, '权限管理', NULL, 'auth/permissions', '', 1, '2021-06-06 18:53:14', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (6, 2, 25, '菜单管理', NULL, 'auth/menu', '', 1, '2021-06-06 18:53:14', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (7, 2, 26, '插件管理', NULL, 'auth/extensions', '', 1, '2021-06-06 18:53:14', '2023-10-30 08:56:38');
INSERT INTO `admin_menu` VALUES (8, 0, 15, '其他配置', 'fa-cog', NULL, '', 1, '2021-06-07 00:58:02', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (9, 0, 2, '用户管理', 'fa-user', NULL, '', 1, '2021-06-10 01:11:04', '2022-10-31 14:29:23');
INSERT INTO `admin_menu` VALUES (10, 9, 3, '用户列表', 'fa-pause-circle-o', 'users', '', 1, '2021-06-10 01:11:33', '2022-10-31 14:29:23');
INSERT INTO `admin_menu` VALUES (11, 9, 4, '推荐树', 'fa-street-view', 'tree', '', 1, '2021-06-10 01:30:36', '2022-10-31 14:29:23');
INSERT INTO `admin_menu` VALUES (12, 13, 14, '提现管理', 'fa-pie-chart', 'withdraw', '', 1, '2021-06-10 01:39:45', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (13, 0, 13, '订单管理', 'fa-shopping-cart', NULL, '', 1, '2021-06-10 22:00:59', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (14, 0, 20, '网站配置', 'fa-bandcamp', 'config', 'cin.dcat-config', 1, '2022-10-20 03:59:46', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (15, 9, 5, '资金记录', 'fa-calculator', 'income', '', 1, '2023-04-13 09:19:05', '2023-08-22 09:42:21');
INSERT INTO `admin_menu` VALUES (16, 8, 16, '轮播图', 'fa fa-fonticons', 'banner', '', 1, '2023-08-20 09:50:50', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (17, 9, 18, '等级配置', 'fa-slideshare', 'rank_config', '', 0, '2023-08-21 12:54:47', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (19, 8, 19, '公告配置', 'fa-bullhorn', 'bulletin', '', 1, '2023-10-18 08:19:11', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (21, 0, 30, '操作日志', 'fa-heartbeat', 'auth/operation-logs', 'dcat-admin.operation-log', 1, '2023-10-30 08:56:54', '2023-10-30 08:57:12');
INSERT INTO `admin_menu` VALUES (22, 0, 14, 'NFT管理', 'fa-cc-diners-club', NULL, '', 1, '2021-06-10 22:00:59', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (23, 22, 1, 'NFT列表', 'fa-diamond', 'nft_list', '', 1, '2021-06-10 22:00:59', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (24, 22, 3, '用户NFT', 'fa-medium', 'user_nft', '', 1, '2023-08-22 12:58:56', '2023-10-21 07:20:53');
INSERT INTO `admin_menu` VALUES (168, 22, 4, '盲盒管理', 'fa-plug', 'treasure_box', '', 1, '2023-10-21 07:44:59', '2023-10-21 07:44:59');
INSERT INTO `admin_menu` VALUES (169, 22, 2, '怪兽列表', 'fa-optin-monster', 'monster_list', '', 1, '2021-06-10 22:00:59', '2023-10-21 07:20:53');

-- ----------------------------
-- Table structure for admin_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_operation_log`;
CREATE TABLE `admin_operation_log`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `input` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_operation_log_user_id_index`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 124 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_operation_log
-- ----------------------------
INSERT INTO `admin_operation_log` VALUES (1, 1, 'juwMwEXSUsfq/nft_list/1', 'PUT', '27.38.127.121', '{\"name\":\"岚\",\"price\":\"200000\",\"create_rate\":\"96\",\"attack_power\":\"20\",\"life_value\":\"100\",\"defence\":\"10\",\"critical_rate\":\"10\",\"icon\":\"images\\/8ca038c2b4087d58bb1a22ba453a702f.png\",\"_file_\":null,\"_method\":\"PUT\",\"_previous_\":\"https:\\/\\/api.grokgplay.xyz\\/juwMwEXSUsfq\\/nft_list\",\"_token\":\"iRGI7knR8y0l3ctYqcn9mn5YcCTT79pV9uf1QHbb\"}', '2023-12-01 00:59:51', '2023-12-01 00:59:51');
INSERT INTO `admin_operation_log` VALUES (2, 1, 'juwMwEXSUsfq/nft_list/2', 'PUT', '27.38.127.121', '{\"name\":\"芙蕾雅\",\"price\":\"3000000\",\"create_rate\":\"1\",\"attack_power\":\"30\",\"life_value\":\"150\",\"defence\":\"15\",\"critical_rate\":\"15\",\"icon\":\"images\\/505f2e7c5df3b15564d0f40312650f02.png\",\"_file_\":null,\"_method\":\"PUT\",\"_previous_\":\"https:\\/\\/api.grokgplay.xyz\\/juwMwEXSUsfq\\/nft_list\",\"_token\":\"iRGI7knR8y0l3ctYqcn9mn5YcCTT79pV9uf1QHbb\"}', '2023-12-01 01:00:31', '2023-12-01 01:00:31');

-- ----------------------------
-- Table structure for admin_permission_menu
-- ----------------------------
DROP TABLE IF EXISTS `admin_permission_menu`;
CREATE TABLE `admin_permission_menu`  (
  `permission_id` bigint NOT NULL,
  `menu_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE INDEX `admin_permission_menu_permission_id_menu_id_unique`(`permission_id` ASC, `menu_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_permission_menu
-- ----------------------------

-- ----------------------------
-- Table structure for admin_permissions
-- ----------------------------
DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `http_method` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `http_path` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `order` int NOT NULL DEFAULT 0,
  `parent_id` bigint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admin_permissions_slug_unique`(`slug` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 34 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_permissions
-- ----------------------------
INSERT INTO `admin_permissions` VALUES (1, '系统配置', 'auth-management', '', '', 20, 0, '2021-06-06 18:53:14', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (2, '用户配置', 'users', '', '/auth/users*', 21, 1, '2021-06-06 18:53:14', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (3, '角色管理', 'roles', '', '/auth/roles*', 22, 1, '2021-06-06 18:53:14', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (4, '权限管理', 'permissions', '', '/auth/permissions*', 23, 1, '2021-06-06 18:53:14', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (5, '菜单管理', 'menu', '', '/auth/menu*', 24, 1, '2021-06-06 18:53:14', '2023-10-21 13:46:09');
INSERT INTO `admin_permissions` VALUES (6, '插件管理', 'extension', '', '/auth/extensions*', 25, 1, '2021-06-06 18:53:14', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (7, '用户管理', 'user-management', '', '', 1, 0, '2023-08-22 13:01:01', '2023-08-22 13:07:06');
INSERT INTO `admin_permissions` VALUES (8, '用户列表', 'admin-users', '', '/users*', 2, 7, '2023-08-22 13:01:40', '2023-08-22 13:07:06');
INSERT INTO `admin_permissions` VALUES (9, '推荐树', 'admin-tree', '', '/tree*', 3, 7, '2023-08-22 13:02:01', '2023-08-22 13:07:06');
INSERT INTO `admin_permissions` VALUES (10, 'USDT日志', 'admin-user_usdt', '', '/user_usdt*', 4, 7, '2023-08-22 13:02:22', '2023-08-22 13:07:06');
INSERT INTO `admin_permissions` VALUES (11, '门票日志', 'admin-user_ticket', '', '/user_ticket*', 5, 7, '2023-08-22 13:02:42', '2023-10-21 13:39:00');
INSERT INTO `admin_permissions` VALUES (15, '订单管理', 'order-management', '', '', 13, 0, '2023-08-22 13:04:13', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (16, '提现管理', 'admin-withdraw', '', '/withdraw*', 14, 15, '2023-08-22 13:04:48', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (17, '其他配置', 'other-management', '', '', 15, 0, '2023-08-22 13:05:19', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (19, '轮播图', 'admin-banner', '', '/banner*', 16, 17, '2023-08-22 13:06:01', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (22, '网站配置', 'admin-config', '', '/config*', 19, 0, '2023-08-22 13:07:34', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (23, '门票管理', 'ticket-management', '', '', 6, 0, '2023-10-21 13:39:54', '2023-10-21 13:39:59');
INSERT INTO `admin_permissions` VALUES (24, '门票币种', 'admin-ticket_currency', '', '/ticket_currency*', 7, 23, '2023-10-21 13:40:23', '2023-10-21 13:40:30');
INSERT INTO `admin_permissions` VALUES (25, '门票订单', 'admin-ticket_order', '', '/ticket_order*', 8, 23, '2023-10-21 13:40:48', '2023-10-21 13:41:15');
INSERT INTO `admin_permissions` VALUES (26, '拼团管理', 'game-management', '', '', 9, 0, '2023-10-21 13:41:11', '2023-10-21 13:41:15');
INSERT INTO `admin_permissions` VALUES (27, '拼团组团', 'admin-game_team', '', '/game_team*', 10, 26, '2023-10-21 13:41:36', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (28, '拼团日志', 'admin-game_order', '', '/game_order*', 11, 26, '2023-10-21 13:42:04', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (29, '矿机管理', 'machine-management', '', '', 12, 0, '2023-10-21 13:42:32', '2023-10-21 13:42:37');
INSERT INTO `admin_permissions` VALUES (30, '用户矿机', 'admin-user_machine', '', '/user_machine*', 26, 29, '2023-10-21 13:42:57', '2023-10-21 13:42:57');
INSERT INTO `admin_permissions` VALUES (31, '等级配置', 'admin-rank_config', '', '/rank_config*', 27, 17, '2023-10-21 13:44:49', '2023-10-21 13:44:49');
INSERT INTO `admin_permissions` VALUES (32, '公告配置', 'admin-bulletin', '', '/bulletin*', 28, 17, '2023-10-21 13:45:23', '2023-10-21 13:45:23');
INSERT INTO `admin_permissions` VALUES (33, '池子管理', 'admin-node_pool', '', '/node_pool*', 29, 17, '2023-10-21 13:45:47', '2023-10-21 13:45:47');

-- ----------------------------
-- Table structure for admin_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_menu`;
CREATE TABLE `admin_role_menu`  (
  `role_id` int NOT NULL DEFAULT 0,
  `menu_id` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE INDEX `admin_role_menu_role_id_menu_id_unique`(`role_id` ASC, `menu_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_role_menu
-- ----------------------------
INSERT INTO `admin_role_menu` VALUES (1, 1, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 2, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 3, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 4, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 5, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 6, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 7, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 8, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 15, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 16, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 17, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 20, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 23, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 48, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_menu` VALUES (1, 134, '2023-04-13 09:19:05', '2023-04-13 09:19:05');
INSERT INTO `admin_role_menu` VALUES (1, 149, '2023-08-20 09:50:50', '2023-08-20 09:50:50');
INSERT INTO `admin_role_menu` VALUES (1, 151, '2023-08-21 12:54:47', '2023-08-21 12:54:47');
INSERT INTO `admin_role_menu` VALUES (1, 152, '2023-08-22 09:42:14', '2023-08-22 09:42:14');
INSERT INTO `admin_role_menu` VALUES (1, 154, '2023-08-22 12:58:56', '2023-08-22 12:58:56');
INSERT INTO `admin_role_menu` VALUES (1, 155, '2023-10-18 08:19:11', '2023-10-18 08:19:11');
INSERT INTO `admin_role_menu` VALUES (1, 156, '2023-10-18 08:25:13', '2023-10-18 08:25:13');
INSERT INTO `admin_role_menu` VALUES (1, 157, '2023-10-18 08:31:56', '2023-10-18 08:31:56');
INSERT INTO `admin_role_menu` VALUES (1, 158, '2023-10-19 11:26:06', '2023-10-19 11:26:06');
INSERT INTO `admin_role_menu` VALUES (1, 159, '2023-10-19 11:28:21', '2023-10-19 11:28:21');
INSERT INTO `admin_role_menu` VALUES (1, 160, '2023-10-21 07:20:44', '2023-10-21 07:20:44');
INSERT INTO `admin_role_menu` VALUES (1, 161, '2023-10-21 07:22:52', '2023-10-21 07:22:52');
INSERT INTO `admin_role_menu` VALUES (1, 162, '2023-10-21 07:23:29', '2023-10-21 07:23:29');
INSERT INTO `admin_role_menu` VALUES (1, 163, '2023-10-21 07:44:59', '2023-10-21 07:44:59');
INSERT INTO `admin_role_menu` VALUES (1, 165, '2023-11-09 08:28:24', '2023-11-09 08:28:24');

-- ----------------------------
-- Table structure for admin_role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_permissions`;
CREATE TABLE `admin_role_permissions`  (
  `role_id` bigint NOT NULL,
  `permission_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE INDEX `admin_role_permissions_role_id_permission_id_unique`(`role_id` ASC, `permission_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_role_permissions
-- ----------------------------
INSERT INTO `admin_role_permissions` VALUES (1, 2, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 3, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 4, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 5, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 6, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 8, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 9, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 10, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 11, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 16, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 19, '2023-08-22 13:07:50', '2023-08-22 13:07:50');
INSERT INTO `admin_role_permissions` VALUES (1, 22, '2023-08-22 13:07:50', '2023-08-22 13:07:50');

-- ----------------------------
-- Table structure for admin_role_users
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_users`;
CREATE TABLE `admin_role_users`  (
  `role_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE INDEX `admin_role_users_role_id_user_id_unique`(`role_id` ASC, `user_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_role_users
-- ----------------------------
INSERT INTO `admin_role_users` VALUES (1, 1, '2021-06-06 18:53:15', '2021-06-06 18:53:15');

-- ----------------------------
-- Table structure for admin_roles
-- ----------------------------
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admin_roles_slug_unique`(`slug` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_roles
-- ----------------------------
INSERT INTO `admin_roles` VALUES (1, '超级管理员', 'administrator', '2021-06-06 18:53:14', '2023-08-22 12:59:20');

-- ----------------------------
-- Table structure for admin_settings
-- ----------------------------
DROP TABLE IF EXISTS `admin_settings`;
CREATE TABLE `admin_settings`  (
  `slug` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `value` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slug`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_settings
-- ----------------------------
INSERT INTO `admin_settings` VALUES ('abovesky:dcat-media-player', '', '2022-02-27 17:47:08', '2022-02-27 17:47:08');
INSERT INTO `admin_settings` VALUES ('cin:dcat-config', '{\"tab\":[{\"key\":\"basic\",\"value\":\"\\u7f51\\u7ad9\\u914d\\u7f6e\"},{\"key\":\"withdraw\",\"value\":\"\\u63d0\\u73b0\\u914d\\u7f6e\"},{\"key\":\"profit\",\"value\":\"\\u6536\\u76ca\\u8bbe\\u7f6e\"}]}', '2021-09-14 09:04:34', '2023-11-30 15:08:55');

-- ----------------------------
-- Table structure for admin_users
-- ----------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `password` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `remember_token` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `google_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '谷歌验证',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admin_users_username_unique`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_users
-- ----------------------------
INSERT INTO `admin_users` VALUES (1, '85hDm45X7BKk', '$2y$10$b/O3V/A3iUbSDFnpEtPqm.ms/GXFRaOzDNKiXCw7qA0.J.z90GJHm', 'Administrator', '', 'tP3N9iS5Zfgzq9M2mKvx6Y2d6emOT97iFHMQoNxQsimwJ2UMeAP7FF19Dtlo', 'A5QUIGXZZQEAA6M5', '2021-06-06 18:53:14', '2023-11-27 00:34:04');

-- ----------------------------
-- Table structure for banner
-- ----------------------------
DROP TABLE IF EXISTS `banner`;
CREATE TABLE `banner`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `banner` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'banner图',
  `vedio` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '视频',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0-无效 1-有效',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `lang` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '语言类型',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of banner
-- ----------------------------
INSERT INTO `banner` VALUES (4, 'Banner', 'images/801ec899f189648482280293c33c6bd9.jpg', '', 1, 0, '', '2022-11-26 09:27:27', '2023-12-01 00:33:53');
INSERT INTO `banner` VALUES (5, 'Banner2', 'images/035cbc76dec7b1ec75496c1faa6d7bcd.jpg', '', 1, 0, '', '2023-11-30 22:45:58', '2023-12-01 00:33:52');
INSERT INTO `banner` VALUES (6, 'Banner3', 'images/0a9fa2b365b49a36179d9c2fbf9a88a8.jpg', '', 1, 0, '', '2023-11-30 22:46:06', '2023-12-01 00:33:52');

-- ----------------------------
-- Table structure for battle_detail
-- ----------------------------
DROP TABLE IF EXISTS `battle_detail`;
CREATE TABLE `battle_detail`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `battle_id` int NOT NULL COMMENT '对战列表ID',
  `type` tinyint(1) NOT NULL COMMENT '1-玩家攻击,2-怪兽攻击',
  `is_critical` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否暴击0-否,1-是暴击',
  `harm` decimal(12, 2) NOT NULL COMMENT '伤害',
  `reset_life` decimal(12, 2) NOT NULL COMMENT '剩余血量',
  `round` int NOT NULL DEFAULT 1 COMMENT '第几轮',
  `is_endint` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否完结0-否,1-是',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`user_id` ASC) USING BTREE,
  INDEX `unique`(`battle_id` ASC) USING BTREE,
  INDEX `other`(`user_id` ASC, `type` ASC, `is_endint` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of battle_detail
-- ----------------------------

-- ----------------------------
-- Table structure for battle_log
-- ----------------------------
DROP TABLE IF EXISTS `battle_log`;
CREATE TABLE `battle_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'UID',
  `nft_id` int NOT NULL,
  `price` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'NFT价格',
  `user_nft_id` int NOT NULL COMMENT '用户NFTid',
  `nft_no` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'NFT标识',
  `monster_id` int NOT NULL COMMENT '对战怪兽列表id',
  `nft_life` decimal(12, 2) NOT NULL COMMENT '精灵血量',
  `monster_life` decimal(12, 2) NOT NULL COMMENT '怪兽血量',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=>对战中,2=>已完成',
  `battle_result` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1->默认,2->战胜,3-负数',
  `total` decimal(12, 2) NOT NULL COMMENT '总共需要释放数量',
  `reset` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '剩余释放数量',
  `profited` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '已释放数量',
  `created_at` datetime NULL DEFAULT NULL,
  `finshed_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_nft_id`(`user_nft_id` ASC) USING BTREE,
  INDEX `uid`(`user_id` ASC) USING BTREE,
  INDEX `nft`(`nft_id` ASC, `monster_id` ASC, `status` ASC, `battle_result` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of battle_log
-- ----------------------------

-- ----------------------------
-- Table structure for bulletin
-- ----------------------------
DROP TABLE IF EXISTS `bulletin`;
CREATE TABLE `bulletin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 0-下架 1-上架',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `lang` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '语言版本',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '公告表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of bulletin
-- ----------------------------
INSERT INTO `bulletin` VALUES (1, '公告1', '<p>公告1<br />公告1</p>', 1, 0, 'zh_CN', '2022-11-26 04:02:51', '2023-10-19 15:41:00');

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tab` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `help` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `element` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` tinyint NOT NULL,
  `rule` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 107 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config` VALUES (2, 'basic', 'site_url', '用户端域名', 'https://www.grokgplay.xyz', '用户端域名', 'url', 0, 'required', '[]', '2022-10-22 12:23:15', '2022-10-22 12:23:15');
INSERT INTO `config` VALUES (100, 'withdraw', 'withdraw_rate', 'GROK提现手续费', '0', 'GROK提现手续费', 'rate', 0, 'required', '[]', '2021-10-09 00:49:52', '2021-10-09 00:49:52');
INSERT INTO `config` VALUES (101, 'withdraw', 'min_withdraw', '最低提现金额', '500000', '最低提现金额', 'number', 0, 'required', '[]', '2021-11-05 19:52:43', '2021-11-05 19:52:43');
INSERT INTO `config` VALUES (102, 'withdraw', 'withdraw_time', '提现时间范围配置', '0,24', '提现时间范围配置', 'text', 0, 'required', '[]', '2023-01-09 14:53:46', '2023-01-09 14:53:46');
INSERT INTO `config` VALUES (34, 'profit', 'direct_rate', '直推收益', '10', '直推收益', 'rate', 0, 'required', '[]', '2023-10-30 09:23:28', '2023-10-30 09:23:28');
INSERT INTO `config` VALUES (35, 'profit', 'indirect_rate', '间推收益', '5', '间推收益', 'rate', 0, 'required', '[]', '2023-10-30 09:23:28', '2023-10-30 09:23:28');
INSERT INTO `config` VALUES (32, 'withdraw', 'daily_withdraw_num', '每日提币次数上限', '10', '每日提币次数上限', 'number', 0, 'required', '[]', '2023-10-30 09:23:28', '2023-10-30 09:23:28');
INSERT INTO `config` VALUES (103, 'basic', 'other_user', '用户基数', '1000', '用户额外基数', 'number', 0, 'required', '[]', '2022-10-22 12:23:15', '2022-10-22 12:23:15');
INSERT INTO `config` VALUES (104, 'basic', 'other_destory', '销毁总量', '500000', '用销毁总量', 'number', 0, 'required', '[]', '2022-10-22 12:23:15', '2022-10-22 12:23:15');
INSERT INTO `config` VALUES (105, 'profit', 'battle_win', '战胜概率', '70', '战胜概率', 'rate', 0, 'required', '[]', '2022-10-22 12:23:15', '2022-10-22 12:23:15');
INSERT INTO `config` VALUES (106, 'profit', 'win_profit', '战胜收益', '2', '战胜收益', 'rate', 0, 'required', '[]', '2022-10-22 12:23:15', '2022-10-22 12:23:15');

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `connection` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `queue` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for income_log
-- ----------------------------
DROP TABLE IF EXISTS `income_log`;
CREATE TABLE `income_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `from_id` int NOT NULL DEFAULT 0 COMMENT 'fromId',
  `amount_type` tinyint(1) NOT NULL COMMENT '钱包类型 1-usdt余额 2-monie余额  3-金币',
  `before` decimal(20, 2) UNSIGNED NOT NULL COMMENT '操作前',
  `total` decimal(20, 2) NOT NULL DEFAULT 0.00 COMMENT '收益数量',
  `after` decimal(20, 2) UNSIGNED NOT NULL COMMENT '操作后',
  `type` tinyint UNSIGNED NOT NULL COMMENT '操作类型 0-后台管理 1-矿机直推奖 2-级差奖 3-层次奖 4-基金池分红 5-业绩分红 6-静态奖励 7-节点分红 8-节点直推奖  9-提现 10-提现驳回',
  `remark` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '备注',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `from_id`(`from_id` ASC) USING BTREE,
  INDEX `type`(`amount_type` ASC, `type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29186 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of income_log
-- ----------------------------

-- ----------------------------
-- Table structure for main_currency
-- ----------------------------
DROP TABLE IF EXISTS `main_currency`;
CREATE TABLE `main_currency`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '币种名称',
  `coin_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '币种图片',
  `rate` decimal(18, 6) NOT NULL DEFAULT 0.000000 COMMENT '兑U汇率',
  `contract_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '代币合约地址',
  `precision` int NOT NULL DEFAULT 0 COMMENT '代币精度',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '主流币' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of main_currency
-- ----------------------------
INSERT INTO `main_currency` VALUES (1, 'GROK', 'images/d572564e24ade07d5b92d88804b0313e.png', 1.000000, '0xc53ca0d56c420e8f913316e84d2c492ede99c61e', 18, '2021-10-03 17:00:25', '2023-11-30 22:53:04');
INSERT INTO `main_currency` VALUES (2, 'USDT', 'images/cea27f0a7f18e16abd12ae5fc6313ca6.jpg', 1.000000, '0x210e2b878c8e06a4ca52a9d0e93942bfc5950b95', 18, '2021-10-03 17:00:25', '2023-11-30 22:53:05');

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `batch` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (2, '2014_10_12_100000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES (3, '2016_01_04_173148_create_admin_tables', 1);
INSERT INTO `migrations` VALUES (5, '2020_09_07_090635_create_admin_settings_table', 1);
INSERT INTO `migrations` VALUES (6, '2020_09_22_015815_create_admin_extensions_table', 1);
INSERT INTO `migrations` VALUES (7, '2020_11_01_083237_update_admin_menu_table', 1);
INSERT INTO `migrations` VALUES (8, '2014_10_12_000000_create_users_table', 2);
INSERT INTO `migrations` VALUES (9, '2019_08_19_000000_create_failed_jobs_table', 3);

-- ----------------------------
-- Table structure for monster_list
-- ----------------------------
DROP TABLE IF EXISTS `monster_list`;
CREATE TABLE `monster_list`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '价格',
  `rate` float(12, 2) NOT NULL DEFAULT 0.00 COMMENT '收益比例',
  `create_rate` float(12, 2) NOT NULL DEFAULT 0.00 COMMENT '宝盒开出概率',
  `attack_power` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '攻击力',
  `life_value` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '生命值',
  `defence` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '防御',
  `critical_rate` float(12, 2) NOT NULL DEFAULT 0.00 COMMENT '暴击率',
  `icon` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=>宝盒开出,2=>节点赠送',
  `status` tinyint(1) NOT NULL COMMENT '状态 0-下架 1-上架',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of monster_list
-- ----------------------------
INSERT INTO `monster_list` VALUES (1, '哥莫拉', 100.00, 3.00, 30.00, 20.00, 100.00, 10.00, 10.00, 'images/b53486ef6688f7c54a781c2bfd313ecc.png', 2, 1, '2023-09-12 11:47:03', '2023-12-01 00:35:52');
INSERT INTO `monster_list` VALUES (2, '贝蒙斯坦', 200.00, 5.00, 22.00, 30.00, 150.00, 15.00, 15.00, 'images/6dc30359a332e3887323c9e7fd76be9e.png', 2, 1, '2023-09-15 17:34:19', '2023-12-01 00:36:00');
INSERT INTO `monster_list` VALUES (3, '庞敦', 400.00, 10.00, 18.00, 40.00, 200.00, 20.00, 20.00, 'images/91af46c25f019f15aaa91371feb433e1.png', 2, 1, '2023-09-15 17:34:31', '2023-12-01 00:36:07');
INSERT INTO `monster_list` VALUES (4, '艾雷王', 600.00, 15.00, 14.00, 50.00, 250.00, 25.00, 25.00, 'images/3eb64c4d33d43ba5e3b6363fa77d5333.png', 2, 1, '2023-09-15 17:34:50', '2023-12-01 00:36:20');
INSERT INTO `monster_list` VALUES (5, '杰顿', 800.00, 3.00, 10.00, 80.00, 400.00, 40.00, 40.00, 'images/0c6e6f0e42861b860c600750fe0b9f27.png', 2, 1, '2023-09-15 17:35:09', '2023-12-01 00:36:29');
INSERT INTO `monster_list` VALUES (6, '泰兰特', 1000.00, 2.00, 6.00, 100.00, 600.00, 40.00, 50.00, 'images/3fb071612b478b24a72f8fb9f0ab4d1c.png', 2, 1, '2023-09-15 17:35:24', '2023-12-01 00:36:35');

-- ----------------------------
-- Table structure for nft_list
-- ----------------------------
DROP TABLE IF EXISTS `nft_list`;
CREATE TABLE `nft_list`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '价格',
  `rate` float(12, 2) NOT NULL DEFAULT 0.00 COMMENT '收益比例',
  `create_rate` float(12, 2) NOT NULL DEFAULT 0.00 COMMENT '宝盒开出概率',
  `attack_power` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '攻击力',
  `life_value` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '生命值',
  `defence` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '防御',
  `critical_rate` float(12, 2) NOT NULL DEFAULT 0.00 COMMENT '暴击率',
  `icon` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=>宝盒开出,2=>节点赠送',
  `status` tinyint(1) NOT NULL COMMENT '状态 0-下架 1-上架',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nft_list
-- ----------------------------
INSERT INTO `nft_list` VALUES (1, '岚', 200000.00, 3.00, 96.00, 20.00, 100.00, 10.00, 10.00, 'images/8ca038c2b4087d58bb1a22ba453a702f.png', 1, 1, '2023-09-12 11:47:03', '2023-12-01 00:59:51');
INSERT INTO `nft_list` VALUES (2, '芙蕾雅', 3000000.00, 5.00, 1.00, 30.00, 150.00, 15.00, 15.00, 'images/505f2e7c5df3b15564d0f40312650f02.png', 1, 1, '2023-09-15 17:34:19', '2023-12-01 01:00:31');
INSERT INTO `nft_list` VALUES (3, '西芙', 4000000.00, 10.00, 1.00, 40.00, 200.00, 20.00, 20.00, 'images/23314c7d83e33d70f2419ae240f7ad0d.png', 1, 1, '2023-09-15 17:34:31', '2023-12-01 00:51:49');
INSERT INTO `nft_list` VALUES (4, '百结', 6000000.00, 15.00, 1.00, 50.00, 250.00, 25.00, 25.00, 'images/e8ce802ee2931c4789ab4ceb7d884761.png', 1, 1, '2023-09-15 17:34:50', '2023-12-01 00:51:51');
INSERT INTO `nft_list` VALUES (5, '艾库迪', 8000000.00, 3.00, 1.00, 80.00, 400.00, 40.00, 40.00, 'images/54a92011e9960b64cfba8c9d407ebb97.png', 1, 1, '2023-09-15 17:35:09', '2023-12-01 00:51:54');

-- ----------------------------
-- Table structure for open_log
-- ----------------------------
DROP TABLE IF EXISTS `open_log`;
CREATE TABLE `open_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nft_id` int NOT NULL,
  `insert_id` int NOT NULL COMMENT '生成ID',
  `nft_name` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icon` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`user_id` ASC) USING BTREE,
  INDEX `nft`(`nft_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of open_log
-- ----------------------------

-- ----------------------------
-- Table structure for order_log
-- ----------------------------
DROP TABLE IF EXISTS `order_log`;
CREATE TABLE `order_log`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ordernum` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int NOT NULL DEFAULT 0 COMMENT '用户ID',
  `type` tinyint NOT NULL DEFAULT 0 COMMENT '订单类型1余额提币2购买门票',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态0未处理1已处理',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '回调内容',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ordernum`(`ordernum` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单记录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of order_log
-- ----------------------------

-- ----------------------------
-- Table structure for recharge
-- ----------------------------
DROP TABLE IF EXISTS `recharge`;
CREATE TABLE `recharge`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL COMMENT '1-买盲盒',
  `coin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '币种',
  `other_coin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '其他币种',
  `nums` decimal(20, 2) NOT NULL COMMENT '支付数量',
  `other_nums` decimal(20, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '购买盲盒数量',
  `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '链上hash',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 1-待确认 2-已成功 3-已失败',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `finish_time` datetime NULL DEFAULT NULL COMMENT '成功时间',
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `hash`(`hash` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of recharge
-- ----------------------------

-- ----------------------------
-- Table structure for transfer
-- ----------------------------
DROP TABLE IF EXISTS `transfer`;
CREATE TABLE `transfer`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT '用户ID',
  `transfer_id` int NOT NULL COMMENT '接收方用户ID',
  `type` tinyint(1) NOT NULL COMMENT '转账币种',
  `num` int NOT NULL COMMENT '转帐数量',
  `fee` decimal(10, 2) NOT NULL COMMENT '手续费',
  `ac_num` int NOT NULL COMMENT '实际到账金额',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 237 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of transfer
-- ----------------------------

-- ----------------------------
-- Table structure for treasure_box
-- ----------------------------
DROP TABLE IF EXISTS `treasure_box`;
CREATE TABLE `treasure_box`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` char(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `coin_id` int NOT NULL,
  `price` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '价格',
  `total` int NOT NULL COMMENT '总配额',
  `remain` int NOT NULL COMMENT '剩下',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '盲盒状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of treasure_box
-- ----------------------------
INSERT INTO `treasure_box` VALUES (1, '盲盒', 'images/42dcfaf69ee21b49536c33adf1429f25.png', 1, 2000000.00, 10000, 10000, 1, NULL, '2023-12-01 00:51:33');

-- ----------------------------
-- Table structure for user_box
-- ----------------------------
DROP TABLE IF EXISTS `user_box`;
CREATE TABLE `user_box`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `box_id` int NOT NULL,
  `num` int NOT NULL DEFAULT 0 COMMENT '盲盒数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_id`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_box
-- ----------------------------

-- ----------------------------
-- Table structure for user_nft
-- ----------------------------
DROP TABLE IF EXISTS `user_nft`;
CREATE TABLE `user_nft`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'UID',
  `nft_id` int NOT NULL,
  `price` decimal(16, 2) NOT NULL DEFAULT 0.00 COMMENT 'NFT价格',
  `create_no` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'NFT标识',
  `type` tinyint(1) NOT NULL COMMENT '1=>宝盒开出,2=>节点赠送,3=>后台分发',
  `status` tinyint(1) NOT NULL COMMENT '1=>闲置,2=>质押中,3=>报单中,4=>已熔铸',
  `battle_result` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1->默认,2->战胜,3-负数',
  `profit` decimal(16, 2) NOT NULL DEFAULT 0.00,
  `battle_time` bigint NOT NULL DEFAULT 0 COMMENT '对战时间',
  `created_at` datetime NULL DEFAULT NULL,
  `finshed_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `nft_id`(`nft_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_nft
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '钱包地址',
  `path` varchar(1500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '推荐路径',
  `deep` int NOT NULL DEFAULT 0,
  `level_id` smallint NOT NULL DEFAULT 0 COMMENT '层级',
  `code` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请码',
  `parent_id` int NOT NULL DEFAULT 0 COMMENT '上级用户',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 0-禁用 1-有效',
  `is_activate` tinyint NOT NULL DEFAULT 0 COMMENT '活跃0否1是',
  `is_effective` tinyint NOT NULL DEFAULT 0 COMMENT '有效账户0否1是',
  `zhi_num` int NOT NULL DEFAULT 0 COMMENT '直推人数',
  `group_num` int NOT NULL DEFAULT 0 COMMENT '团队人数',
  `myperfor` decimal(16, 2) NOT NULL DEFAULT 0.00 COMMENT '我的业绩',
  `teamperfor` decimal(16, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '团队业绩',
  `performance` decimal(16, 2) NOT NULL DEFAULT 0.00 COMMENT '伞下业绩',
  `total_teamperfor` decimal(16, 2) NOT NULL DEFAULT 0.00 COMMENT '总业绩(伞下+个人)',
  `static_income` decimal(16, 2) NOT NULL DEFAULT 0.00 COMMENT '静态收益',
  `dynamic_income` decimal(16, 2) NOT NULL DEFAULT 0.00 COMMENT '动态收益',
  `headimgurl` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `level`(`level_id` ASC) USING BTREE,
  INDEX `parent_id`(`parent_id` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `path`(`path`(1024) ASC) USING BTREE,
  INDEX `code`(`code` ASC) USING BTREE,
  INDEX `wallet`(`wallet` ASC) USING BTREE,
  INDEX `is_active`(`is_activate` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 206 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, '0xB9Bf8502A9FdE77a5C40BEc9867EA308572556Bd', '', 0, 1, 'Monster', 1, 1, 0, 1, 2, 2, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', '2023-11-30 16:32:23', '2023-12-01 01:04:18');
INSERT INTO `users` VALUES (2, '0x300b1b817f2431e345cde7b80229016f86ed5984', '-1-', 1, 0, 'fzplrTnd', 1, 1, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'headimgurl/default.jpg', '2023-12-01 01:02:16', '2023-12-01 01:02:16');
INSERT INTO `users` VALUES (3, '0xf1a388de669420e0c6cf993bcfd8ca725623b820', '-1-', 1, 0, 'xpB4HVMC', 1, 1, 0, 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'headimgurl/default.jpg', '2023-12-01 01:04:18', '2023-12-01 01:04:18');

-- ----------------------------
-- Table structure for users_coin
-- ----------------------------
DROP TABLE IF EXISTS `users_coin`;
CREATE TABLE `users_coin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL COMMENT '钱包类型 1-usdt余额  2-moine  3-金币',
  `amount` decimal(16, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '币种数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `search_index`(`user_id` ASC, `type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 723 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users_coin
-- ----------------------------
INSERT INTO `users_coin` VALUES (1, 1, 1, 0.00, '2023-12-01 00:51:05', NULL);
INSERT INTO `users_coin` VALUES (2, 2, 1, 0.00, '2023-12-01 01:02:17', NULL);
INSERT INTO `users_coin` VALUES (3, 3, 1, 0.00, '2023-12-01 01:04:18', NULL);

-- ----------------------------
-- Table structure for withdraw
-- ----------------------------
DROP TABLE IF EXISTS `withdraw`;
CREATE TABLE `withdraw`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '订单号',
  `type` tinyint(1) NOT NULL COMMENT '类型 1-提现',
  `user_id` int NOT NULL COMMENT '用户ID',
  `receive_address` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '接收方地址',
  `coin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '提现币种',
  `num` decimal(18, 2) NOT NULL COMMENT '出金数量',
  `fee` decimal(18, 2) NOT NULL COMMENT '手续费比例',
  `fee_amount` decimal(18, 2) NOT NULL COMMENT '手续费金额',
  `ac_amount` decimal(18, 2) NOT NULL COMMENT '实际到账金额',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 1-待通过 2-已通过 3-失败',
  `finsh_time` datetime NULL DEFAULT NULL COMMENT '到账时间',
  `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'hash',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 79 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of withdraw
-- ----------------------------
