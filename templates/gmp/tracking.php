<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'GMP Tracking',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'gmp-tracking' // Example active nav key
]);

// Assume $gmpSummary holds calculated GMP tracking data
$gmpSummary = $gmpSummary ?? [];
// Assume $costDetails holds detailed cost breakdown (optional)
$costDetails = $costDetails ?? [];
?>

<div class="container mt-4">
    <h1>GMP Tracking</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- GMP Summary Card -->
    <div class="card mb-4">
        <div class="card-header">
            GMP Status Summary
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-6">Original GMP Amount:</dt>
                        <dd class="col-sm-6 text-end"><?= number_format($gmpSummary['original_gmp'] ?? 0, 2) ?></dd>

                        <dt class="col-sm-6">Approved Change Orders:</dt>
                        <dd class="col-sm-6 text-end"><?= number_format($gmpSummary['approved_co'] ?? 0, 2) ?></dd>

                        <dt class="col-sm-6 fw-bold">Current GMP Amount:</dt>
                        <dd class="col-sm-6 text-end fw-bold"><?= number_format($gmpSummary['current_gmp'] ?? 0, 2) ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                     <dl class="row">
                        <dt class="col-sm-6">Total Cost to Date:</dt>
                        <dd class="col-sm-6 text-end"><?= number_format($gmpSummary['cost_to_date'] ?? 0, 2) ?></dd>

                        <dt class="col-sm-6">Remaining GMP Balance:</dt>
                        <dd class="col-sm-6 text-end"><?= number_format($gmpSummary['remaining_balance'] ?? 0, 2) ?></dd>

                        <dt class="col-sm-6 fw-bold">Projected Variance:</dt>
                        <dd class="col-sm-6 text-end fw-bold" style="color: <?= ($gmpSummary['projected_variance'] ?? 0) >= 0 ? 'green' : 'red' ?>;">
                            <?= number_format($gmpSummary['projected_variance'] ?? 0, 2) ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional: Detailed Cost Breakdown -->
    <div class="card">
         <div class="card-header">
            Detailed Cost Breakdown (Example)
        </div>
        <div class="card-body">
            <p><em>(Implement detailed table here, perhaps grouped by cost code or SOV line, showing Budget vs. Actual vs. Commitment)</em></p>
            <?php if (!empty($costDetails)): ?>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Cost Code</th>
                            <th>Description</th>
                            <th>Budget</th>
                            <th>Committed Cost</th>
                            <th>Actual Cost To Date</th>
                            <th>Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($costDetails as $detail): ?>
                            <tr>
                                <!-- Populate with data from $costDetails -->
                                <td><?= htmlspecialchars($detail['code'] ?? '') ?></td>
                                <td><?= htmlspecialchars($detail['description'] ?? '') ?></td>
                                <td class="text-end"><?= number_format($detail['budget'] ?? 0, 2) ?></td>
                                <td class="text-end"><?= number_format($detail['committed'] ?? 0, 2) ?></td>
                                <td class="text-end"><?= number_format($detail['actual'] ?? 0, 2) ?></td>
                                <td class="text-end"><?= number_format(($detail['budget'] ?? 0) - ($detail['actual'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No detailed cost data available.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>