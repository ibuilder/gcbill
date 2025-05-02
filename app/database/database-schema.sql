-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\database\database-schema.sql
-- Construction Billing Management System Database Schema
-- Version: 1.0

-- Ensure UTF8mb4 for full character support
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `role` ENUM('admin', 'manager', 'billing', 'staff') NOT NULL DEFAULT 'staff', -- Adjust roles as needed
  `is_active` BOOLEAN NOT NULL DEFAULT true,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `reset_token` VARCHAR(100) NULL DEFAULT NULL,
  `reset_token_expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects Table
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_number` VARCHAR(50) NOT NULL UNIQUE,
  `project_name` VARCHAR(255) NOT NULL,
  `address_line1` VARCHAR(255) NULL,
  `address_line2` VARCHAR(255) NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(50) NULL,
  `zip_code` VARCHAR(20) NULL,
  `start_date` DATE NULL,
  `completion_date` DATE NULL,
  `contract_amount` DECIMAL(15, 2) NULL DEFAULT 0.00,
  `gmp_amount` DECIMAL(15, 2) NULL DEFAULT 0.00, -- Guaranteed Maximum Price
  `gc_fee_percentage` DECIMAL(5, 2) NULL DEFAULT 0.00, -- General Contractor Fee %
  `retainage_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 10.00, -- Default retainage
  `status` ENUM('planning', 'active', 'completed', 'on_hold', 'cancelled') NOT NULL DEFAULT 'planning',
  `owner_id` INT UNSIGNED NULL, -- Link to the primary owner
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  -- Add FOREIGN KEY (owner_id) REFERENCES owners(id) later if owners table exists first
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Owners Table
DROP TABLE IF EXISTS `owners`;
CREATE TABLE `owners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `owner_name` VARCHAR(255) NOT NULL,
  `primary_contact_name` VARCHAR(150) NULL,
  `primary_contact_email` VARCHAR(255) NULL,
  `primary_contact_phone` VARCHAR(50) NULL,
  `address_line1` VARCHAR(255) NULL,
  `address_line2` VARCHAR(255) NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(50) NULL,
  `zip_code` VARCHAR(20) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint to projects now that owners table exists
ALTER TABLE `projects` ADD CONSTRAINT `fk_project_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Staff Positions Table
DROP TABLE IF EXISTS `staff_positions`;
CREATE TABLE `staff_positions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `position_title` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `default_billing_rate` DECIMAL(10, 2) NULL DEFAULT 0.00, -- Optional default rate
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Table
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) UNIQUE NULL, -- Can be null if not a system user
  `phone` VARCHAR(50) NULL,
  `position_id` INT UNSIGNED NULL,
  `hire_date` DATE NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT true,
  `user_id` INT UNSIGNED NULL UNIQUE, -- Link to users table if this staff member can log in
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`position_id`) REFERENCES `staff_positions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GMP Divisions Table (e.g., CSI MasterFormat)
DROP TABLE IF EXISTS `gmp_divisions`;
CREATE TABLE `gmp_divisions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `division_number` VARCHAR(10) NOT NULL UNIQUE, -- e.g., '01', '03 30 00'
  `division_title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schedule of Values (SOV) Table
DROP TABLE IF EXISTS `schedule_of_values`;
CREATE TABLE `schedule_of_values` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT UNSIGNED NOT NULL,
  `division_id` INT UNSIGNED NULL, -- Link to GMP Division
  `sov_line_number` VARCHAR(20) NOT NULL, -- Unique within a project
  `description` TEXT NOT NULL,
  `scheduled_value` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`division_id`) REFERENCES `gmp_divisions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  UNIQUE KEY `uk_project_sov_line` (`project_id`, `sov_line_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Billing Periods Table
DROP TABLE IF EXISTS `billing_periods`;
CREATE TABLE `billing_periods` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT UNSIGNED NOT NULL,
  `period_number` INT UNSIGNED NOT NULL, -- e.g., 1, 2, 3...
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `billing_date` DATE NOT NULL, -- Date the G702/G703 is generated/submitted
  `status` ENUM('draft', 'submitted', 'approved', 'paid') NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `uk_project_period` (`project_id`, `period_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SOV Billing Items Table (Tracks progress per SOV line per period)
DROP TABLE IF EXISTS `sov_billing_items`;
CREATE TABLE `sov_billing_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `billing_period_id` INT UNSIGNED NOT NULL,
  `sov_id` INT UNSIGNED NOT NULL,
  `work_completed_this_period` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `materials_stored_this_period` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  -- Calculated fields (can be stored or calculated on the fly)
  -- `total_completed_and_stored_to_date` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  -- `percentage_complete` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
  -- `balance_to_finish` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  -- `retainage_held_this_period` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`billing_period_id`) REFERENCES `billing_periods`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`sov_id`) REFERENCES `schedule_of_values`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `uk_period_sov` (`billing_period_id`, `sov_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- General Condition Categories (Example: Staff, Expenses, Fees)
DROP TABLE IF EXISTS `general_condition_categories`;
CREATE TABLE `general_condition_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- General Conditions Items (Specific costs within categories)
DROP TABLE IF EXISTS `general_conditions`;
CREATE TABLE `general_conditions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `unit` VARCHAR(50) NULL, -- e.g., hours, lump sum, month, allowance
    `estimated_cost` DECIMAL(15, 2) NULL,
    `actual_cost_to_date` DECIMAL(15, 2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `general_condition_categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Salary Rates (Project specific overrides or standard rates)
DROP TABLE IF EXISTS `staff_salary_rates`;
CREATE TABLE `staff_salary_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `staff_id` INT UNSIGNED NOT NULL,
    `billing_rate` DECIMAL(10, 2) NOT NULL, -- Hourly rate for this project
    `effective_date` DATE NOT NULL,
    `end_date` DATE NULL, -- Null if currently active
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_project_staff_rate` (`project_id`, `staff_id`, `effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Time/Expense Tracking (Connects staff time/costs to GC or Billing)
DROP TABLE IF EXISTS `staff_time_entries`;
CREATE TABLE `staff_time_entries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `staff_id` INT UNSIGNED NOT NULL,
    `billing_period_id` INT UNSIGNED NULL, -- Link to billing period when billed
    `general_condition_id` INT UNSIGNED NULL, -- Link if it's a GC cost
    `entry_date` DATE NOT NULL,
    `hours_worked` DECIMAL(5, 2) NOT NULL,
    `rate_used` DECIMAL(10, 2) NOT NULL, -- Rate at the time of entry
    `description` TEXT NULL,
    `is_billable` BOOLEAN NOT NULL DEFAULT true,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`billing_period_id`) REFERENCES `billing_periods`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`general_condition_id`) REFERENCES `general_conditions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for Billing Periods/Applications
CREATE TABLE billings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    billing_number INT NOT NULL, -- Application number (1, 2, 3...)
    period_start_date DATE NULL,
    period_end_date DATE NOT NULL,
    billing_date DATE NOT NULL, -- Date the application is submitted
    status VARCHAR(50) DEFAULT 'draft', -- e.g., draft, submitted, approved, paid
    notes TEXT NULL,
    retainage_rate DECIMAL(5, 4) NULL, -- Specific rate for this billing (defaults to project rate)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE, -- Cascade delete if project is deleted
    UNIQUE KEY unique_billing (project_id, billing_number) -- Ensure unique billing number per project
);

-- Table for Billing Line Item Details (corresponds to SOV)
CREATE TABLE billing_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    billing_id INT NOT NULL,
    sov_item_id INT NOT NULL,
    work_completed_this_period DECIMAL(15, 2) DEFAULT 0.00,
    materials_stored_this_period DECIMAL(15, 2) DEFAULT 0.00,
    -- Calculated/derived fields can be stored or calculated on the fly
    -- work_completed_previous DECIMAL(15, 2) DEFAULT 0.00, -- Can be calculated
    -- materials_stored_previous DECIMAL(15, 2) DEFAULT 0.00, -- Can be calculated
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (billing_id) REFERENCES billings(id) ON DELETE CASCADE, -- Cascade delete if billing is deleted
    FOREIGN KEY (sov_item_id) REFERENCES schedule_of_values(id) ON DELETE RESTRICT, -- Prevent SOV deletion if billed
    UNIQUE KEY unique_billing_item (billing_id, sov_item_id) -- Ensure one entry per SOV item per billing
);

-- Optional: Add an index for faster lookups
CREATE INDEX idx_billing_details_sov ON billing_details(sov_item_id);

-- Add other tables like Change Orders, Expenses, etc. as needed

SET FOREIGN_KEY_CHECKS = 1;
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