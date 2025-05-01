<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\config\routes.php
<?php

return [
    // --- Public Routes ---
    ['GET', '/login', 'AuthController@showLogin'],
    ['POST', '/login', 'AuthController@login'],
    ['GET', '/logout', 'AuthController@logout'],
    // Add other public routes (e.g., password reset) here

    // --- Authenticated Routes ---
    ['GET', '/', 'DashboardController@index'], // Dashboard
    ['GET', '/dashboard', 'DashboardController@index'],

    // --- Projects ---
    ['GET', '/projects', 'ProjectController@index'],
    ['GET', '/projects/create', 'ProjectController@create'],
    ['POST', '/projects/store', 'ProjectController@store'],
    ['GET', '/projects/view/(\d+)', 'ProjectController@view'],
    ['GET', '/projects/edit/(\d+)', 'ProjectController@edit'],
    ['POST', '/projects/update/(\d+)', 'ProjectController@update'],
    ['POST', '/projects/delete/(\d+)', 'ProjectController@delete'], // Use POST for delete
    ['GET', '/projects/chart', 'ProjectController@chart'], // Placeholder

    // --- Owners ---
    ['GET', '/owners', 'OwnerController@index'],
    ['GET', '/owners/create', 'OwnerController@create'],
    ['POST', '/owners/store', 'OwnerController@store'],
    ['GET', '/owners/view/(\d+)', 'OwnerController@view'],
    ['GET', '/owners/edit/(\d+)', 'OwnerController@edit'],
    ['POST', '/owners/update/(\d+)', 'OwnerController@update'],
    ['POST', '/owners/delete/(\d+)', 'OwnerController@delete'], // Use POST for delete
    ['GET', '/owners/chart', 'OwnerController@chart'], // Placeholder

    // --- Staff & Positions ---
    ['GET', '/staff', 'StaffController@index'],
    ['GET', '/staff/create', 'StaffController@create'],
    ['POST', '/staff/store', 'StaffController@store'],
    ['GET', '/staff/view/(\d+)', 'StaffController@view'],
    ['GET', '/staff/edit/(\d+)', 'StaffController@edit'],
    ['POST', '/staff/update/(\d+)', 'StaffController@update'],
    ['POST', '/staff/delete/(\d+)', 'StaffController@delete'], // Use POST for delete
    ['GET', '/staff/chart', 'StaffController@chart'], // Placeholder
    // Staff Positions
    ['GET', '/staff/positions', 'StaffController@positions'],
    ['GET', '/staff/positions/create', 'StaffController@createPosition'],
    ['POST', '/staff/positions/store', 'StaffController@storePosition'],
    ['GET', '/staff/positions/edit/(\d+)', 'StaffController@editPosition'],
    ['POST', '/staff/positions/update/(\d+)', 'StaffController@updatePosition'],
    ['POST', '/staff/positions/delete/(\d+)', 'StaffController@deletePosition'], // Use POST for delete

    // --- SOV Routes (AJAX) ---
    ['GET', '/projects/(\d+)/sov', 'SovController@getForProject'],
    ['POST', '/sov/store', 'SovController@store'],
    ['POST', '/sov/update/(\d+)', 'SovController@update'],
    ['POST', '/sov/delete/(\d+)', 'SovController@delete'],

    // --- Billing Routes ---
    ['GET', '/projects/(\d+)/billings', 'BillingController@index'],          // List billings for project
    ['GET', '/projects/(\d+)/billings/create', 'BillingController@create'],   // Show create form
    ['POST', '/projects/(\d+)/billings/store', 'BillingController@store'],    // Store new billing header
    ['GET', '/billings/edit/(\d+)', 'BillingController@edit'],             // Show main edit form
    ['GET', '/billings/view/(\d+)', 'BillingController@view'],             // View/Print billing (placeholder)
    ['POST', '/billings/update/(\d+)', 'BillingController@update'],         // Save billing details (AJAX)
    ['POST', '/billings/update-header/(\d+)', 'BillingController@updateHeader'], // Save billing header info (Form POST)
    ['POST', '/billings/delete/(\d+)', 'BillingController@delete'],         // Delete billing (drafts only?)
    // AJAX endpoint for edit form data
    ['GET', '/billings/(\d+)/data', 'BillingController@getBillingData'],

    // --- Users & Settings (Example) ---
    // ['GET', '/users', 'UserController@index'],
    // ['GET', '/settings', 'SettingsController@index'],
];
