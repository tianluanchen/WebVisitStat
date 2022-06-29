SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for access_stat
-- ----------------------------
DROP TABLE IF EXISTS `access_stat`;
CREATE TABLE `access_stat`  (
  `domain_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `domain` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_access_count` bigint NOT NULL,
  `total_access_count` bigint NOT NULL,
  PRIMARY KEY (`domain_hash`) USING BTREE,
  INDEX `domain_hash`(`domain_hash`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;



SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for access_detail
-- ----------------------------
DROP TABLE IF EXISTS `access_detail`;
CREATE TABLE `access_detail`  (
  `hash_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `domain_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `domain` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `path` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `count` bigint NOT NULL,
  PRIMARY KEY (`hash_id`) USING BTREE,
  INDEX `domain`(`domain`) USING BTREE,
  INDEX `domain_hash`(`domain_hash`) USING BTREE,
  CONSTRAINT `access_detail_ibfk_1` FOREIGN KEY (`domain_hash`) REFERENCES `access_stat` (`domain_hash`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
