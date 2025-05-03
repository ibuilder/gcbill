-- GCBill SQL Schema --
-- ------------------------------------------------------

-- Users Table for Authentication
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `role` ENUM('admin', 'project_manager', 'staff', 'read_only') NOT NULL DEFAULT 'staff', -- Example roles, adjust as needed
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Owners Table
CREATE TABLE `owners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `contact_person` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects Table
CREATE TABLE `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` INT UNSIGNED NULL, -- Can be nullable if owner is optional initially
  `name` VARCHAR(255) NOT NULL,
  `project_number` VARCHAR(100) NULL UNIQUE,
  `address` TEXT NULL,
  `original_contract_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `contract_date` DATE NULL,
  `start_date` DATE NULL,
  `substantial_completion_date` DATE NULL,
  `retainage_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 10.00, -- Percentage for retainage
  `status` ENUM('active', 'completed', 'on_hold', 'archived') NOT NULL DEFAULT 'active',
  `architect_name` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_projects_owner_id` (`owner_id`),
  INDEX `idx_projects_status` (`status`),
  CONSTRAINT `fk_projects_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schedule of Values (SOV) Items Table
CREATE TABLE `sov_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `item_number` VARCHAR(50) NOT NULL, -- e.g., CSI code or custom number
  `description` TEXT NOT NULL,
  `scheduled_value` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_sov_items_project_id` (`project_id`),
  UNIQUE KEY `uq_sov_items_project_item` (`project_id`, `item_number`), -- Ensure item numbers are unique per project
  CONSTRAINT `fk_sov_items_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Change Orders Table
CREATE TABLE `change_orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `co_number` VARCHAR(50) NOT NULL, -- Change Order Number
  `description` TEXT NULL,
  `date_issued` DATE NULL,
  `date_approved` DATE NULL,
  `amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- Net change amount (can be positive or negative)
  `status` ENUM('pending', 'approved', 'rejected', 'incorporated') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_change_orders_project_id` (`project_id`),
  UNIQUE KEY `uq_change_orders_project_number` (`project_id`, `co_number`),
  CONSTRAINT `fk_change_orders_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Change Order Items Table (Optional - if COs break down into SOV-like items)
-- Alternatively, Change Orders might directly adjust the project's contract sum or modify SOV items.
-- This structure assumes COs can have their own line items affecting the CO amount.
CREATE TABLE `change_order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `change_order_id` INT UNSIGNED NOT NULL,
  `sov_item_id` INT UNSIGNED NULL, -- Optional: Link to an existing SOV item being modified
  `description` TEXT NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_co_items_change_order_id` (`change_order_id`),
  INDEX `idx_co_items_sov_item_id` (`sov_item_id`),
  CONSTRAINT `fk_co_items_change_order_id` FOREIGN KEY (`change_order_id`) REFERENCES `change_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_co_items_sov_item_id` FOREIGN KEY (`sov_item_id`) REFERENCES `sov_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE -- Or CASCADE if deleting SOV item removes related CO items
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Applications for Payment Table (Incorporates Billing Period concept)
CREATE TABLE `applications_for_payment` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `application_number` INT UNSIGNED NOT NULL,
  `period_start_date` DATE NOT NULL,
  `period_end_date` DATE NOT NULL,
  `date_submitted` DATE NULL,
  `status` ENUM('draft', 'submitted', 'approved', 'paid', 'rejected') NOT NULL DEFAULT 'draft',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_apps_project_id` (`project_id`),
  UNIQUE KEY `uq_apps_project_number` (`project_id`, `application_number`),
  CONSTRAINT `fk_apps_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Application for Payment Details Table (Links Application to SOV/CO Items)
CREATE TABLE `application_details` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` INT UNSIGNED NOT NULL,
  `sov_item_id` INT UNSIGNED NOT NULL, -- Link to the SOV item being billed against
  -- `change_order_item_id` INT UNSIGNED NULL, -- Uncomment if billing against specific CO items
  `work_completed_this_period` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `materials_stored_this_period` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  -- Calculated/Snapshot values at time of application (can be recalculated, but storing helps auditing)
  `scheduled_value_snapshot` DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- SOV item value at time of application
  `previous_work_completed` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `previous_materials_stored` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `total_completed_and_stored` DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- Calculated: previous + current work + current materials
  `percentage_complete` DECIMAL(5, 2) NOT NULL DEFAULT 0.00, -- Calculated: total_completed_and_stored / scheduled_value_snapshot
  `balance_to_finish` DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- Calculated: scheduled_value_snapshot - total_completed_and_stored
  `retainage_held_this_period` DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- Calculated based on project retainage % and work completed
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_app_details_application_id` (`application_id`),
  INDEX `idx_app_details_sov_item_id` (`sov_item_id`),
  -- INDEX `idx_app_details_co_item_id` (`change_order_item_id`),
  UNIQUE KEY `uq_app_details_app_sov` (`application_id`, `sov_item_id`), -- Only one entry per SOV item per application
  CONSTRAINT `fk_app_details_application_id` FOREIGN KEY (`application_id`) REFERENCES `applications_for_payment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_app_details_sov_item_id` FOREIGN KEY (`sov_item_id`) REFERENCES `sov_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE -- Ensure SOV item exists
  -- CONSTRAINT `fk_app_details_co_item_id` FOREIGN KEY (`change_order_item_id`) REFERENCES `change_order_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Staff Positions Table (Optional)
CREATE TABLE `staff_positions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `default_rate` DECIMAL(10, 2) NULL, -- Optional default billing rate
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Table (Can be linked to Users or separate)
CREATE TABLE `staff` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL UNIQUE, -- Link to users table if staff log in
  `position_id` INT UNSIGNED NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NULL UNIQUE,
  `phone` VARCHAR(50) NULL,
  `hire_date` DATE NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_staff_position_id` (`position_id`),
  INDEX `idx_staff_user_id` (`user_id`),
  CONSTRAINT `fk_staff_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_position_id` FOREIGN KEY (`position_id`) REFERENCES `staff_positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Time Entries Table (Optional - if tracking time)
CREATE TABLE `staff_time_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NULL, -- Link to project if time is project-specific
  `entry_date` DATE NOT NULL,
  `hours_worked` DECIMAL(5, 2) NOT NULL,
  `description` TEXT NULL,
  `is_billable` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_time_staff_id` (`staff_id`),
  INDEX `idx_time_project_id` (`project_id`),
  INDEX `idx_time_entry_date` (`entry_date`),
  CONSTRAINT `fk_time_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_time_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE -- Or CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL, -- Recipient user
  `type` VARCHAR(100) NOT NULL, -- e.g., 'app_submitted', 'co_approved', 'task_assigned'
  `message` TEXT NOT NULL,
  `related_entity_type` VARCHAR(50) NULL, -- e.g., 'project', 'application', 'change_order'
  `related_entity_id` INT UNSIGNED NULL,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `read_at` DATETIME NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notifications_user_id` (`user_id`),
  INDEX `idx_notifications_related` (`related_entity_type`, `related_entity_id`),
  CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Log Table
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL, -- User who performed the action (NULL for system actions)
  `action` VARCHAR(255) NOT NULL, -- e.g., 'project_created', 'sov_updated', 'app_submitted'
  `entity_type` VARCHAR(100) NULL, -- e.g., 'Project', 'SOVItem', 'ApplicationForPayment'
  `entity_id` INT UNSIGNED NULL,
  `old_value` JSON NULL, -- Store previous state (requires MySQL 5.7.8+)
  `new_value` JSON NULL, -- Store new state
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_user_id` (`user_id`),
  INDEX `idx_audit_entity` (`entity_type`, `entity_id`),
  INDEX `idx_audit_action` (`action`),
  CONSTRAINT `fk_audit_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Permissions (Example - Simple Role-Based or Fine-Grained)
-- This is a very basic example. You might need a more complex structure
-- involving roles, permissions, and a pivot table (role_user, permission_role).
-- The `role` column in the `users` table provides a simpler starting point.

CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE, -- e.g., 'create_project', 'edit_sov', 'approve_application'
  `description` TEXT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_permissions` (
  `user_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `permission_id`),
  CONSTRAINT `fk_user_perm_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_perm_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Add initial data if needed (e.g., default admin user, default positions)
 INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `is_active`) VALUES ('admin', 'admin@example.com', '$2y$10$YourSecurePasswordHashHere', 'admin', TRUE);
