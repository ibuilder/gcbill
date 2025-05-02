-- user-permissions-schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role` ENUM('admin', 'manager', 'billing', 'staff') NOT NULL DEFAULT 'staff',
  `controller` VARCHAR(255) NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  UNIQUE KEY `uk_role_controller_action` (`role`, `controller`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;