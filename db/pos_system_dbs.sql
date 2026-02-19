/*
 Navicat Premium Dump SQL

 Source Server         : Mysql-192.168.88.40
 Source Server Type    : MySQL
 Source Server Version : 90100 (9.1.0)
 Source Host           : 192.168.88.40:3307
 Source Schema         : pos_system_dbs

 Target Server Type    : MySQL
 Target Server Version : 90100 (9.1.0)
 File Encoding         : 65001

 Date: 19/02/2026 12:55:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for held_bills
-- ----------------------------
DROP TABLE IF EXISTS `held_bills`;
CREATE TABLE `held_bills`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `reference_note` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'ชื่ออ้างอิง เช่น โต๊ะ 1',
  `items` json NOT NULL COMMENT 'เก็บข้อมูลสินค้าในตะกร้าเป็น JSON',
  `total_amount` decimal(10, 2) NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of held_bills
-- ----------------------------

-- ----------------------------
-- Table structure for members
-- ----------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `points` int NULL DEFAULT 0,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of members
-- ----------------------------
INSERT INTO `members` VALUES (1, '0812345678', 'คุณสมชาย ใจดี', 0, '2026-02-06 13:25:53');

-- ----------------------------
-- Table structure for menus
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NULL DEFAULT 0,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `link` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `allowed_roles` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of menus
-- ----------------------------
INSERT INTO `menus` VALUES (1, 1, 'หน้าขาย (POS)', 'pos_page', 'fa-cash-register', 'admin,staff,manager');
INSERT INTO `menus` VALUES (2, 2, 'สต็อกสินค้า', 'products', 'fa-box', 'admin,staff,manager');
INSERT INTO `menus` VALUES (3, 3, 'หมวดหมู่สินค้า', 'categories', 'fa fa-bookmark', 'admin,staff,manager');
INSERT INTO `menus` VALUES (4, 4, 'นำเข้าข้อมูลสินค้า', 'import_products', 'fa fa-table', 'admin,manager');
INSERT INTO `menus` VALUES (5, 5, 'รายงานยอดขาย', 'history', 'fa-chart-line', 'admin,manager');
INSERT INTO `menus` VALUES (6, 6, 'การสั่งซื้อ', 'purchase_order', 'fa fa-shopping-cart', 'admin,manager');
INSERT INTO `menus` VALUES (7, 7, 'จัดการเมนู', 'menus_manage', 'fa-list', 'admin');
INSERT INTO `menus` VALUES (8, 8, 'กำหนดสิทธิ์', 'permissions', 'fa-user-lock', 'admin');
INSERT INTO `menus` VALUES (9, 9, 'ผู้ใช้งานระบบ', 'users', 'fa-users-cog', 'admin');
INSERT INTO `menus` VALUES (10, 10, 'สมาชิก', 'members', 'fa-address-card', 'admin,staff,manager');
INSERT INTO `menus` VALUES (11, 11, 'เปลี่ยนรหัสผ่าน', 'change_password', 'fa fa-key', 'admin,staff,manager');

-- ----------------------------
-- Table structure for order_items
-- ----------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NULL DEFAULT NULL,
  `doc_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `barcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `product_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `price` decimal(10, 2) NULL DEFAULT NULL,
  `qty` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of order_items
-- ----------------------------

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `doc_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `order_date` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10, 2) NULL DEFAULT NULL,
  `cashier_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `member_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `member_id`(`member_id` ASC) USING BTREE,
  INDEX `doc_id`(`doc_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of orders
-- ----------------------------

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `barcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `price` decimal(10, 2) NOT NULL,
  `cost` decimal(10, 2) NULL DEFAULT 0.00,
  `quantity` decimal(10, 2) NULL DEFAULT 0.00,
  `min` decimal(10, 2) NULL DEFAULT 0.00,
  `max` decimal(10, 2) NULL DEFAULT 0.00,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `barcode`(`barcode` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of products
-- ----------------------------
INSERT INTO `products` VALUES (1, '8850123', 'น้ำอัดลม', '2', 15.00, 0.00, 0.00, 0.00, 0.00);
INSERT INTO `products` VALUES (2, '8850456', 'ขนมปัง', '4', 22.50, 20.00, 50.00, 20.00, 60.00);
INSERT INTO `products` VALUES (3, '9645500', 'ยาสีฟัน', '3', 45.00, 0.00, 0.00, 0.00, 0.00);
INSERT INTO `products` VALUES (4, '9645501', 'แปรงสีฟัน', '3', 20.00, 0.00, 0.00, 0.00, 0.00);
INSERT INTO `products` VALUES (5, '9645503', 'บะหมี่กึ่งสำเร็จรูป', '1', 7.00, 5.00, 0.00, 0.00, 0.00);
INSERT INTO `products` VALUES (6, '9645567', 'ข้าวกล่อง', '1', 50.00, 30.00, 0.00, 0.00, 0.00);

-- ----------------------------
-- Table structure for products_categories
-- ----------------------------
DROP TABLE IF EXISTS `products_categories`;
CREATE TABLE `products_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `categories` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of products_categories
-- ----------------------------
INSERT INTO `products_categories` VALUES (1, 'C-0001', 'อาหาร');
INSERT INTO `products_categories` VALUES (2, 'C-0002', 'เครื่องดื่ม');
INSERT INTO `products_categories` VALUES (3, 'C-0003', 'ของใช้ภายในบ้าน');
INSERT INTO `products_categories` VALUES (4, 'C-0004', 'ขนม-ของขบเคี้ยว');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin@myadmin.com', 'admin', 'Admin Manager', 'admin');
INSERT INTO `users` VALUES (2, 'staff', '1234', 'Cashier One', 'staff');
INSERT INTO `users` VALUES (3, 'manager', '123456', 'Manager', 'manager');
INSERT INTO `users` VALUES (4, 'cust1', '123456', 'customer', 'customer');

SET FOREIGN_KEY_CHECKS = 1;
