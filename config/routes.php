<?php

use App\Controllers\BillingController;
use App\Controllers\DashboardController;
use App\Controllers\LoginController;
use App\Controllers\OwnerController;
use App\Controllers\ProjectController;
use App\Controllers\SOVController;
use App\Controllers\StaffController;

return [
    // --- Public Routes ---
    ['GET', '/login', LoginController::class . '@showLogin'],
    ['POST', '/login', LoginController::class . '@login'],
    ['GET', '/logout', LoginController::class . '@logout'],
    // Add other public routes (e.g., password reset) here

    // --- Authenticated Routes ---
    ['GET', '/', DashboardController::class . '@index'], // Dashboard
    ['GET', '/dashboard', DashboardController::class . '@index'],

    // --- Projects ---
    ['GET', '/projects', ProjectController::class . '@index'],
    ['GET', '/projects/create', ProjectController::class . '@create'],
    ['POST', '/projects/store', ProjectController::class . '@store'],
    ['GET', '/projects/view/(\d+)', ProjectController::class . '@view'],
    ['GET', '/projects/edit/(\d+)', ProjectController::class . '@edit'],
    ['POST', '/projects/update/(\d+)', ProjectController::class . '@update'],
    ['POST', '/projects/delete/(\d+)', ProjectController::class . '@delete'], // Use POST for delete
    ['GET', '/projects/chart', ProjectController::class . '@chart'], // Placeholder

    // --- Owners ---
    ['GET', '/owners', OwnerController::class . '@index'],
    ['GET', '/owners/create', OwnerController::class . '@create'],
    ['POST', '/owners/store', OwnerController::class . '@store'],
    ['GET', '/owners/view/(\d+)', OwnerController::class . '@view'],
    ['GET', '/owners/edit/(\d+)', OwnerController::class . '@edit'],
    ['POST', '/owners/update/(\d+)', OwnerController::class . '@update'],
    ['POST', '/owners/delete/(\d+)', OwnerController::class . '@delete'], // Use POST for delete
    ['GET', '/owners/chart', OwnerController::class . '@chart'], // Placeholder

    // --- Staff & Positions ---
    ['GET', '/staff', StaffController::class . '@index'],
    ['GET', '/staff/create', StaffController::class . '@create'],
    ['POST', '/staff/store', StaffController::class . '@store'],
    ['GET', '/staff/view/(\d+)', StaffController::class . '@view'],
    ['GET', '/staff/edit/(\d+)', StaffController::class . '@edit'],
    ['POST', '/staff/update/(\d+)', StaffController::class . '@update'],
    ['POST', '/staff/delete/(\d+)', StaffController::class . '@delete'], // Use POST for delete
    ['GET', '/staff/chart', StaffController::class . '@chart'], // Placeholder
    // Staff Positions
    ['GET', '/staff/positions', StaffController::class . '@positions'],
    ['GET', '/staff/positions/create', StaffController::class . '@createPosition'],
    ['POST', '/staff/positions/store', StaffController::class . '@storePosition'],
    ['GET', '/staff/positions/edit/(\d+)', StaffController::class . '@editPosition'],
    ['POST', '/staff/positions/update/(\d+)', StaffController::class . '@updatePosition'],
    ['POST', '/staff/positions/delete/(\d+)', StaffController::class . '@deletePosition'], // Use POST for delete

    // --- SOV Routes (AJAX) ---
    ['GET', '/projects/(\d+)/sov', SOVController::class . '@getForProject'],
    ['POST', '/sov/store', SOVController::class . '@store'],
    ['POST', '/sov/update/(\d+)', SOVController::class . '@update'],
    ['POST', '/sov/delete/(\d+)', SOVController::class . '@delete'],

    // --- Billing Routes ---
    ['GET', '/projects/(\d+)/billings', BillingController::class . '@index'],          // List billings for project
    ['GET', '/projects/(\d+)/billings/create', BillingController::class . '@create'],   // Show create form
    ['POST', '/projects/(\d+)/billings/store', BillingController::class . '@store'],    // Store new billing header
    ['GET', '/billings/edit/(\d+)', BillingController::class . '@edit'],             // Show main edit form
    ['GET', '/billings/view/(\d+)', BillingController::class . '@view'],             // View/Print billing (placeholder)
    ['POST', '/billings/update/(\d+)', BillingController::class . '@update'],         // Save billing details (AJAX)
    ['POST', '/billings/update-header/(\d+)', BillingController::class . '@updateHeader'], // Save billing header info (Form POST)
    ['POST', '/billings/delete/(\d+)', BillingController::class . '@delete'],         // Delete billing (drafts only?)
    // AJAX endpoint for edit form data
    ['GET', '/billings/(\d+)/data', BillingController::class . '@getBillingData'],
];