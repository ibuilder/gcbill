-- database/initial-user-permissions.sql

-- Admin Permissions
INSERT INTO user_permissions (role, controller, action) VALUES ('admin', '*', '*');

-- Manager Permissions
INSERT INTO user_permissions (role, controller, action) VALUES ('manager', 'ProjectsController', '*');
INSERT INTO user_permissions (role, controller, action) VALUES ('manager', 'BillingPeriodsController', '*');
INSERT INTO user_permissions (role, controller, action) VALUES ('manager', 'ScheduleOfValuesController', '*');
INSERT INTO user_permissions (role, controller, action) VALUES ('manager', 'ChangeOrderController', '*');
INSERT INTO user_permissions (role, controller, action) VALUES ('manager', 'StaffController', '*');
INSERT INTO user_permissions (role, controller, action) VALUES ('manager', 'OwnersController', '*');

-- Billing Permissions
INSERT INTO user_permissions (role, controller, action) VALUES ('billing', 'BillingPeriodsController', 'index');
INSERT INTO user_permissions (role, controller, action) VALUES ('billing', 'BillingPeriodsController', 'view');
INSERT INTO user_permissions (role, controller, action) VALUES ('billing', 'ScheduleOfValuesController', 'index');
INSERT INTO user_permissions (role, controller, action) VALUES ('billing', 'ScheduleOfValuesController', 'view');

-- Staff Permissions
INSERT INTO user_permissions (role, controller, action) VALUES ('staff', 'StaffTimeEntriesController', '*');
INSERT INTO user_permissions (role, controller, action) VALUES ('staff', 'StaffController', 'view');