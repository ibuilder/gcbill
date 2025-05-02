<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'GMP Setup',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'gmp-setup' // Example active nav key
]);

// Assume $gmpData holds the current GMP settings for the project
$gmpData = $gmpData ?? [];
?>

<div class="container mt-4">
    <h1>GMP Setup</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form action="/gmp/setup/update" method="POST"> <!-- Adjust action based on your routing -->
        <!-- Add CSRF token field if using CSRF protection -->
        <!-- <input type="hidden" name="csrf_token" value="<?//= htmlspecialchars($csrf_token ?? '') ?>"> -->
        <input type="hidden" name="_method" value="PUT"> <!-- Or POST, depending on your route -->
        <input type="hidden" name="project_id" value="<?= htmlspecialchars($projectId ?? '') ?>"> <!-- Assuming a project context -->

        <div class="card">
            <div class="card-header">
                Guaranteed Maximum Price Details
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="original_gmp_amount" class="col-sm-4 col-form-label">Original GMP Amount</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" id="original_gmp_amount" name="gmp[original_gmp_amount]" value="<?= htmlspecialchars($gmpData['original_gmp_amount'] ?? '0.00') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="contractor_fee_percentage" class="col-sm-4 col-form-label">Contractor Fee (%)</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" id="contractor_fee_percentage" name="gmp[contractor_fee_percentage]" value="<?= htmlspecialchars($gmpData['contractor_fee_percentage'] ?? '0.00') ?>">
                    </div>
                </div>

                 <div class="row mb-3">
                    <label for="contingency_percentage" class="col-sm-4 col-form-label">Contingency (%)</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" id="contingency_percentage" name="gmp[contingency_percentage]" value="<?= htmlspecialchars($gmpData['contingency_percentage'] ?? '0.00') ?>">
                    </div>
                </div>

                 <div class="row mb-3">
                    <label for="general_conditions_budget" class="col-sm-4 col-form-label">General Conditions Budget</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" id="general_conditions_budget" name="gmp[general_conditions_budget]" value="<?= htmlspecialchars($gmpData['general_conditions_budget'] ?? '0.00') ?>">
                    </div>
                </div>

                 <div class="row mb-3">
                    <label for="allowances_total" class="col-sm-4 col-form-label">Allowances Total</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" id="allowances_total" name="gmp[allowances_total]" value="<?= htmlspecialchars($gmpData['allowances_total'] ?? '0.00') ?>">
                    </div>
                </div>

                <!-- Add other relevant GMP setup fields -->

            </div>
            <div class="card-footer text-end">
                 <button type="submit" class="btn btn-primary">Save GMP Setup</button>
            </div>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>