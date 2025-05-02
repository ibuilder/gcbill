<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'General Conditions Settings',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'gc-settings' // Example active nav key
]);
?>

<div class="container mt-4">
    <h1>General Conditions Settings</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form action="/general-conditions/settings/update" method="POST">
        <!-- Add CSRF token field if using CSRF protection -->
        <!-- <input type="hidden" name="csrf_token" value="<?//= htmlspecialchars($csrf_token ?? '') ?>"> -->
        <input type="hidden" name="_method" value="PUT"> <!-- Or POST, depending on your route -->

        <div class="row mb-3">
            <label for="default_markup_percentage" class="col-sm-3 col-form-label">Default Markup (%)</label>
            <div class="col-sm-9">
                <input type="number" step="0.01" class="form-control" id="default_markup_percentage" name="settings[default_markup_percentage]" value="<?= htmlspecialchars($settings['default_markup_percentage'] ?? '15.00') ?>">
                <small class="form-text text-muted">Default markup applied to GC costs.</small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="insurance_rate_percentage" class="col-sm-3 col-form-label">Insurance Rate (%)</label>
            <div class="col-sm-9">
                <input type="number" step="0.01" class="form-control" id="insurance_rate_percentage" name="settings[insurance_rate_percentage]" value="<?= htmlspecialchars($settings['insurance_rate_percentage'] ?? '2.50') ?>">
                 <small class="form-text text-muted">Rate for calculating insurance costs based on labor or total cost.</small>
            </div>
        </div>

         <div class="row mb-3">
            <label for="payroll_burden_percentage" class="col-sm-3 col-form-label">Payroll Burden (%)</label>
            <div class="col-sm-9">
                <input type="number" step="0.01" class="form-control" id="payroll_burden_percentage" name="settings[payroll_burden_percentage]" value="<?= htmlspecialchars($settings['payroll_burden_percentage'] ?? '25.00') ?>">
                 <small class="form-text text-muted">Percentage added to direct salary costs for taxes, benefits, etc.</small>
            </div>
        </div>

        <!-- Add more settings fields as needed -->
        <!-- Example:
        <div class="row mb-3">
            <label for="setting_name" class="col-sm-3 col-form-label">Another Setting</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="setting_name" name="settings[setting_name]" value="<?//= htmlspecialchars($settings['setting_name'] ?? '') ?>">
            </div>
        </div>
        -->

        <div class="d-flex justify-content-end">
             <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>