# Project Roadmap: Construction Billing App

This document outlines the development progress and future plans for the Construction Billing Application.

## Completed Features

*   **Phase 0: Core Setup & Dashboard (Partial)**
    *   [x] Basic project structure (MVC-like).
    *   [x] Composer for dependencies.
    *   [x] Basic routing mechanism (`App\Router`).
    *   [x] Database connection (`App\Database`).
    *   [x] Simple templating engine (`App\View`).
    *   [x] Basic Bootstrap 5 layout (`partials/header.html`, `partials/footer.html`).
    *   [x] Authentication helper (`App\Helpers\AuthHelper`) and basic session management.
    *   [x] Login/Logout functionality (`AuthController`, login view).
    *   [ ] Meaningful Dashboard content (`DashboardController`).
*   **Phase 1: Project Management**
    *   [x] Database schema (`projects` table).
    *   [x] Model (`App\Models\Project`).
    *   [x] Controller (`App\Controllers\ProjectController`) for CRUD operations.
    *   [x] Views (`templates/projects/`) for List, Create, Edit, View.
    *   [x] Link Projects to Owners.
*   **Phase 2: Owner Management**
    *   [x] Database schema (`owners` table).
    *   [x] Model (`App\Models\Owner`).
    *   [x] Controller (`App\Controllers\OwnerController`) for CRUD operations.
    *   [x] Views (`templates/owners/`) for List, Create, Edit, View.
*   **Phase 3: Staff & Position Management**
    *   [x] Database schema (`staff`, `staff_positions` tables).
    *   [x] Models (`App\Models\Staff`, `App\Models\StaffPosition`).
    *   [x] Controller (`App\Controllers\StaffController`) for CRUD operations (Staff & Positions).
    *   [x] Views (`templates/staff/`, `templates/staff/positions/`) for List, Create, Edit, View.
*   **Phase 4: Schedule of Values (SOV) & Billing**
    *   **SOV:**
        *   [x] Database schema (`schedule_of_values` table).
        *   [x] Model (`App\Models\Sov`).
        *   [x] Controller (`App\Controllers\SovController`) for AJAX-based CRUD on Project View page.
        *   [x] Integration with Project View page (`templates/projects/view.html`) using JavaScript.
        *   [x] Prevent deletion of SOV items used in billings.
    *   **Billing:**
        *   [x] Database schema (`billings`, `billing_details` tables).
        *   [x] Models (`App\Models\Billing`, `App\Models\BillingDetail`).
        *   [x] Controller (`App\Controllers\BillingController`) for:
            *   Listing billings per project.
            *   Creating new billing headers.
            *   Editing billing details (AJAX data loading, dynamic calculation via JS, AJAX saving).
            *   Updating billing headers.
            *   Viewing read-only billing application.
            *   Deleting draft billings.
        *   [x] Views (`templates/billings/`) for List, Create Header, Edit (dynamic), View (read-only).
        *   [x] Helper (`App\Helpers\CalculationHelper`) for G702/G703 style calculations.
        *   [x] Helper (`App\Helpers\ViewHelper`) for formatting currency, dates, status badges.
        *   [x] Calculation of "Previous Payments" based on prior approved/paid billings.
        *   [x] Basic CSRF protection (`App\Helpers\SecurityHelper`).

## To-Do Features

*   **Phase 0: Core Setup & Dashboard**
    *   [ ] Implement a useful Dashboard (`DashboardController`, `templates/dashboard/index.html`) showing project summaries, pending items, etc.
*   **Phase 4: Billing Enhancements**
    *   [ ] **Status Workflow:**
        *   Implement strict rules based on billing status (e.g., prevent editing/deleting 'submitted', 'approved', 'paid' billings).
        *   Add UI elements/actions to change status (e.g., "Submit", "Approve").
    *   [ ] **PDF Generation:**
        *   Integrate a PDF library (e.g., TCPDF, DomPDF).
        *   Create PDF template mirroring G702/G703 format based on `templates/billings/view.html`.
        *   Update `BillingController::view` to generate PDF output.
        *   Add "Print" button to views.
*   **Phase 5: Change Orders**
    *   [ ] Database schema (`change_orders`, `change_order_items`?).
    *   [ ] Models for Change Orders.
    *   [ ] Controller and Views for managing Change Orders (CRUD).
    *   [ ] Link Change Orders to Projects.
    *   [ ] Integrate Change Order values into Billing calculations (`CalculationHelper`, `BillingController::getBillingData`, `BillingController::view`):
        *   Update "Net change by Change Orders" (Summary Line 2).
        *   Update "CONTRACT SUM TO DATE" (Summary Line 3).
        *   Consider how COs affect SOV/Billing detail lines.
*   **Phase 6: User Management & Permissions**
    *   [ ] Define User Roles (e.g., Admin, Project Manager, Billing Clerk).
    *   [ ] Implement role-based access control (RBAC) - restrict access to controllers/actions.
    *   [ ] User management interface (CRUD for users).
*   **Phase 7: Reporting**
    *   [ ] Define required reports (e.g., Project Cost Summary, Billings per Project, Retainage Report).
    *   [ ] Implement controllers and potentially views/PDF generation for reports.
*   **General Improvements & Future**
    *   [ ] **Testing:** Implement Unit Tests (PHPUnit) for models, helpers, controllers. Consider Integration/Browser tests.
    *   [ ] **UI/UX:** Refine styling, improve user feedback, add print-specific CSS.
    *   [ ] **General Conditions / Cost Tracking:** Module to track costs outside of SOV billing.
    *   [ ] **Notifications:** Implement user notifications (e.g., billing submitted/approved).
    *   [ ] **Audit Log:** Track significant changes to data.
    *   [ ] **API:** Consider adding API endpoints for potential external integrations.
    *   [ ] **Deployment:** Document deployment process, configure environment variables (`.env` file?).