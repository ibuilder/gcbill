<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Company Settings',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'settings-company' // Example active nav key
]);

// Assume $companySettings holds the current company data
$companySettings = $companySettings ?? [];
?>

<div class="container mt-4">
    <h1>Company Settings</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form action="/settings/company/update" method="POST" enctype="multipart/form-data"> <!-- Adjust action -->
        <!-- Add CSRF token field if using CSRF protection -->
        <!-- <input type="hidden" name="csrf_token" value="<?//= htmlspecialchars($csrf_token ?? '') ?>"> -->
        <input type="hidden" name="_method" value="PUT"> <!-- Or POST -->

        <div class="card">
            <div class="card-header">Company Information</div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="company_name" class="col-sm-3 col-form-label">Company Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="company_name" name="settings[company_name]" value="<?= htmlspecialchars($companySettings['company_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="company_address1" class="col-sm-3 col-form-label">Address Line 1</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="company_address1" name="settings[company_address1]" value="<?= htmlspecialchars($companySettings['company_address1'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_address2" class="col-sm-3 col-form-label">Address Line 2</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="company_address2" name="settings[company_address2]" value="<?= htmlspecialchars($companySettings['company_address2'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_city" class="col-sm-3 col-form-label">City</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="company_city" name="settings[company_city]" value="<?= htmlspecialchars($companySettings['company_city'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_state" class="col-sm-3 col-form-label">State/Province</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="company_state" name="settings[company_state]" value="<?= htmlspecialchars($companySettings['company_state'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_zip" class="col-sm-3 col-form-label">Zip/Postal Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="company_zip" name="settings[company_zip]" value="<?= htmlspecialchars($companySettings['company_zip'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_phone" class="col-sm-3 col-form-label">Phone</label>
                    <div class="col-sm-9">
                        <input type="tel" class="form-control" id="company_phone" name="settings[company_phone]" value="<?= htmlspecialchars($companySettings['company_phone'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_email" class="col-sm-3 col-form-label">Email</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="company_email" name="settings[company_email]" value="<?= htmlspecialchars($companySettings['company_email'] ?? '') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="company_logo" class="col-sm-3 col-form-label">Company Logo</label>
                    <div class="col-sm-9">
                        <?php if (!empty($companySettings['company_logo_path'])): ?>
                            <img src="<?= htmlspecialchars($companySettings['company_logo_path']) ?>" alt="Current Logo" style="max-height: 50px; margin-bottom: 10px;"> <br>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="company_logo" name="company_logo">
                        <small class="form-text text-muted">Upload a new logo to replace the current one (if any).</small>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Save Company Settings</button>
            </div>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>