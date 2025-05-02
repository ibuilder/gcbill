# GCBill - Construction Billing Application

GCBill is a web-based application designed to help General Contractors manage project billing, including Schedule of Values (SOV), Applications for Payment (similar to AIA G702/G703 format), and Change Orders.

## Features

*   **User Authentication:** Secure login and session management.
*   **Project Management:** Create and manage construction projects (basic details, contract info).
*   **Schedule of Values (SOV):** Define line items and scheduled values for projects.
*   **Applications for Payment:**
    *   Create billing applications based on SOV.
    *   Input work completed and materials stored for the billing period.
    *   Calculates retainage and payment due (based on G702/G703 logic).
*   **Change Orders:** Track and manage project change orders affecting the contract sum.
*   **AIA G702/G703 Data Generation:** Library to calculate and structure data for standard AIA billing forms.
*   **PDF Export:** Generate PDF documents (requires configuration, e.g., for billing applications).
*   **Excel Export:** Export data arrays to XLSX format (requires configuration).
*   **Basic MVC Structure:** Organized using Models, Views, and Controllers.
*   **Helper Classes:** Utilities for calculations, security (CSRF), validation, views, etc.

## Installation

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/your-username/gcbill.git
    cd gcbill
    ```

2.  **Install Dependencies:** Requires [Composer](https://getcomposer.org/).
    ```bash
    composer install
    ```
    *   This will install necessary libraries like PhpSpreadsheet and potentially a PDF generation library (Dompdf or TCPDF - see `app/libraries/PDFExport.php` and install your choice: `composer require dompdf/dompdf` or `composer require tecnickcom/tcpdf`).

3.  **Configuration:**
    *   Copy `config.example.php` to `config.php`.
    *   Edit `config.php` and set your database connection details (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
    *   Configure other settings like `APP_URL` and security keys if necessary.

4.  **Database Setup:**
    *   Manually create the database specified in `config.php`.
    *   **Important:** This project currently lacks a database migration system. You will need to manually create the required tables based on the models in `app/models/`. Key tables include:
        *   `users`
        *   `projects`
        *   `schedule_of_values`
        *   `applications_for_payment`
        *   `application_payment_details`
        *   `change_orders`
        *   `user_permissions` (if implementing role-based access control)
    *   *Alternatively, a database schema file (`schema.sql`) could be provided.*

5.  **Web Server Setup:**
    *   Configure your web server (Apache, Nginx) to point the document root to the `public/` directory.
    *   Ensure URL rewriting (e.g., Apache's `mod_rewrite`) is enabled to handle routing via `public/index.php`. An example `.htaccess` might be needed in the `public/` directory for Apache.

    **Example Apache VirtualHost:**
    ```apache
    <VirtualHost *:80>
        ServerName gcbill.local
        DocumentRoot "/path/to/your/gcbill/public"
        <Directory "/path/to/your/gcbill/public">
            AllowOverride All
            Require all granted
        </Directory>
        ErrorLog ${APACHE_LOG_DIR}/gcbill-error.log
        CustomLog ${APACHE_LOG_DIR}/gcbill-access.log combined
    </VirtualHost>
    ```
    **Example Nginx Server Block:**
    ```nginx
    server {
        listen 80;
        server_name gcbill.local;
        root /path/to/your/gcbill/public;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.x-fpm.sock; # Adjust PHP version/path
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht {
            deny all;
        }
    }
    ```

6.  **Permissions:** Ensure the web server has write permissions for any directories used for caching, logging, or file uploads if applicable (e.g., potentially needed by PDF/Excel libraries for temporary files).

## Usage

1.  Access the application through the URL configured in your web server (e.g., `http://gcbill.local`).
2.  Register a new user or log in if an account exists.
3.  Navigate through the application to manage projects, SOVs, applications, etc.

## Roadmap

### Completed / In Progress

*   [x] Core MVC framework structure.
*   [x] Database connection and basic Model structure.
*   [x] User authentication (Login, Logout, Session).
*   [x] Basic Project CRUD.
*   [x] Basic SOV CRUD linked to Projects.
*   [x] Basic Application for Payment header CRUD.
*   [x] Application for Payment detail input (Work/Materials this period).
*   [x] Calculation logic for G703 lines (in `CalculationHelper`).
*   [x] Calculation logic for G702 summary (in `CalculationHelper`).
*   [x] Library (`AIA.php`) to orchestrate G702/G703 data generation.
*   [x] Basic Change Order CRUD.
*   [x] CSRF protection (`SecurityHelper`).
*   [x] Basic Validation (`ValidationHelper`).
*   [x] PDF Export library integration (`PDFExport.php`, `PDFHelper.php`).
*   [x] Excel Export library integration (`ExcelExport.php`, `ExcelHelper.php`).

### Planned / Future

*   [ ] **Database Migrations:** Implement a system (e.g., Phinx) for managing database schema changes.
*   [ ] **Database Seeding:** Create seeders for initial data (e.g., default user roles, permissions).
*   [ ] **Refine AIA Calculations:** Ensure full compliance with AIA standards, including complex retainage rules (e.g., tiered, reduction at 50%).
*   [ ] **PDF Generation Templates:** Create polished HTML/CSS templates for PDF output of G702/G703 forms.
*   [ ] **User Roles & Permissions:** Fully implement role-based access control using `Auth::checkPermission` and `UserPermission` model.
*   [ ] **Frontend UI/UX:** Improve user interface and experience (potentially using a CSS framework like Bootstrap more extensively or a JS framework).
*   [ ] **Reporting:** Add various project and billing reports.
*   [ ] **Unit & Integration Tests:** Implement automated tests for core logic (calculations, database interactions).
*   [ ] **File Uploads:** Allow attaching documents to projects, change orders, etc.
*   [ ] **Configuration Management:** Improve configuration loading (avoiding globals).
*   [ ] **Error Handling:** More robust and user-friendly error handling and logging.
*   [ ] **API Endpoints:** Potentially add API endpoints for integration or frontend frameworks.
*   [ ] **Dashboard:** Create a central dashboard summarizing project statuses and billing.

## Contributing

Contributions are welcome! Please follow standard fork & pull request workflows. (Add more specific contribution guidelines if desired).

## License

(Specify your license here, e.g., MIT, GPL, or Proprietary)
```// filepath: c:\Users\iphoe\OneDrive\Documents\GitHub\gcbill\README.md
# GCBill - Construction Billing Application

GCBill is a web-based application designed to help General Contractors manage project billing, including Schedule of Values (SOV), Applications for Payment (similar to AIA G702/G703 format), and Change Orders.

## Features

*   **User Authentication:** Secure login and session management.
*   **Project Management:** Create and manage construction projects (basic details, contract info).
*   **Schedule of Values (SOV):** Define line items and scheduled values for projects.
*   **Applications for Payment:**
    *   Create billing applications based on SOV.
    *   Input work completed and materials stored for the billing period.
    *   Calculates retainage and payment due (based on G702/G703 logic).
*   **Change Orders:** Track and manage project change orders affecting the contract sum.
*   **AIA G702/G703 Data Generation:** Library to calculate and structure data for standard AIA billing forms.
*   **PDF Export:** Generate PDF documents (requires configuration, e.g., for billing applications).
*   **Excel Export:** Export data arrays to XLSX format (requires configuration).
*   **Basic MVC Structure:** Organized using Models, Views, and Controllers.
*   **Helper Classes:** Utilities for calculations, security (CSRF), validation, views, etc.

## Installation

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/your-username/gcbill.git
    cd gcbill
    ```

2.  **Install Dependencies:** Requires [Composer](https://getcomposer.org/).
    ```bash
    composer install
    ```
    *   This will install necessary libraries like PhpSpreadsheet and potentially a PDF generation library (Dompdf or TCPDF - see `app/libraries/PDFExport.php` and install your choice: `composer require dompdf/dompdf` or `composer require tecnickcom/tcpdf`).

3.  **Configuration:**
    *   Copy `config.example.php` to `config.php`.
    *   Edit `config.php` and set your database connection details (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
    *   Configure other settings like `APP_URL` and security keys if necessary.

4.  **Database Setup:**
    *   Manually create the database specified in `config.php`.
    *   **Important:** This project currently lacks a database migration system. You will need to manually create the required tables based on the models in `app/models/`. Key tables include:
        *   `users`
        *   `projects`
        *   `schedule_of_values`
        *   `applications_for_payment`
        *   `application_payment_details`
        *   `change_orders`
        *   `user_permissions` (if implementing role-based access control)
    *   *Alternatively, a database schema file (`schema.sql`) could be provided.*

5.  **Web Server Setup:**
    *   Configure your web server (Apache, Nginx) to point the document root to the `public/` directory.
    *   Ensure URL rewriting (e.g., Apache's `mod_rewrite`) is enabled to handle routing via `public/index.php`. An example `.htaccess` might be needed in the `public/` directory for Apache.

    **Example Apache VirtualHost:**
    ```apache
    <VirtualHost *:80>
        ServerName gcbill.local
        DocumentRoot "/path/to/your/gcbill/public"
        <Directory "/path/to/your/gcbill/public">
            AllowOverride All
            Require all granted
        </Directory>
        ErrorLog ${APACHE_LOG_DIR}/gcbill-error.log
        CustomLog ${APACHE_LOG_DIR}/gcbill-access.log combined
    </VirtualHost>
    ```
    **Example Nginx Server Block:**
    ```nginx
    server {
        listen 80;
        server_name gcbill.local;
        root /path/to/your/gcbill/public;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.x-fpm.sock; # Adjust PHP version/path
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht {
            deny all;
        }
    }
    ```

6.  **Permissions:** Ensure the web server has write permissions for any directories used for caching, logging, or file uploads if applicable (e.g., potentially needed by PDF/Excel libraries for temporary files).

## Usage

1.  Access the application through the URL configured in your web server (e.g., `http://gcbill.local`).
2.  Register a new user or log in if an account exists.
3.  Navigate through the application to manage projects, SOVs, applications, etc.

## Roadmap

### Completed / In Progress

*   [x] Core MVC framework structure.
*   [x] Database connection and basic Model structure.
*   [x] User authentication (Login, Logout, Session).
*   [x] Basic Project CRUD.
*   [x] Basic SOV CRUD linked to Projects.
*   [x] Basic Application for Payment header CRUD.
*   [x] Application for Payment detail input (Work/Materials this period).
*   [x] Calculation logic for G703 lines (in `CalculationHelper`).
*   [x] Calculation logic for G702 summary (in `CalculationHelper`).
*   [x] Library (`AIA.php`) to orchestrate G702/G703 data generation.
*   [x] Basic Change Order CRUD.
*   [x] CSRF protection (`SecurityHelper`).
*   [x] Basic Validation (`ValidationHelper`).
*   [x] PDF Export library integration (`PDFExport.php`, `PDFHelper.php`).
*   [x] Excel Export library integration (`ExcelExport.php`, `ExcelHelper.php`).

### Planned / Future

*   [ ] **Database Migrations:** Implement a system (e.g., Phinx) for managing database schema changes.
*   [ ] **Database Seeding:** Create seeders for initial data (e.g., default user roles, permissions).
*   [ ] **Refine AIA Calculations:** Ensure full compliance with AIA standards, including complex retainage rules (e.g., tiered, reduction at 50%).
*   [ ] **PDF Generation Templates:** Create polished HTML/CSS templates for PDF output of G702/G703 forms.
*   [ ] **User Roles & Permissions:** Fully implement role-based access control using `Auth::checkPermission` and `UserPermission` model.
*   [ ] **Frontend UI/UX:** Improve user interface and experience (potentially using a CSS framework like Bootstrap more extensively or a JS framework).
*   [ ] **Reporting:** Add various project and billing reports.
*   [ ] **Unit & Integration Tests:** Implement automated tests for core logic (calculations, database interactions).
*   [ ] **File Uploads:** Allow attaching documents to projects, change orders, etc.
*   [ ] **Configuration Management:** Improve configuration loading (avoiding globals).
*   [ ] **Error Handling:** More robust and user-friendly error handling and logging.
*   [ ] **API Endpoints:** Potentially add API endpoints for integration or frontend frameworks.
*   [ ] **Dashboard:** Create a central dashboard summarizing project statuses and billing.

## Contributing

Contributions are welcome! Please follow standard fork & pull request workflows. (Add more specific contribution guidelines if desired).

## License

(Specify your license here, e.g., MIT, GPL, or Proprietary)