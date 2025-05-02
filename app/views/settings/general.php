<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'General Settings',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'settings-general' // Example active nav key
]);

// Assume $generalSettings holds the current general settings
$generalSettings = $generalSettings ?? [];
?>

<div class="container mt-4">
    <h1>General Settings</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

     <form action="/settings/general/update" method="POST"> <!-- Adjust action -->
        <!-- Add CSRF token field if using CSRF protection -->
        <!-- <input type="hidden" name="csrf_token" value="<?//= htmlspecialchars($csrf_token ?? '') ?>"> -->
        <input type="hidden" name="_method" value="PUT"> <!-- Or POST -->

        <div class="card">
             <div class="card-header">Application Defaults</div>
             <div class="card-body">
                 <div class="row mb-3">
                    <label for="date_format" class="col-sm-3 col-form-label">Date Format</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="date_format" name="settings[date_format]">
                            <option value="Y-m-d" <?= ($generalSettings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD (e.g., <?= date('Y-m-d') ?>)</option>
                            <option value="m/d/Y" <?= ($generalSettings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY (e.g., <?= date('m/d/Y') ?>)</option>
                            <option value="d/m/Y" <?= ($generalSettings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY (e.g., <?= date('d/m/Y') ?>)</option>
                            <option value="M j, Y" <?= ($generalSettings['date_format'] ?? '') === 'M j, Y' ? 'selected' : '' ?>>Mon DD, YYYY (e.g., <?= date('M j, Y') ?>)</option>
                        </select>
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="currency_symbol" class="col-sm-3 col-form-label">Currency Symbol</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="currency_symbol" name="settings[currency_symbol]" value="<?= htmlspecialchars($generalSettings['currency_symbol'] ?? '$') ?>">
                    </div>
                </div>
                 <div class="row mb-3">
                    <label for="items_per_page" class="col-sm-3 col-form-label">Items Per Page (Pagination)</label>
                    <div class="col-sm-9">
                        <input type="number" min="5" max="100" step="5" class="form-control" id="items_per_page" name="settings[items_per_page]" value="<?= htmlspecialchars($generalSettings['items_per_page'] ?? '25') ?>">
                    </div>
                </div>
                <!-- Add other general settings like timezone, default language, etc. -->
             </div>
             <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Save General Settings</button>
            </div>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>