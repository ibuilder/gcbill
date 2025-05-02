<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Billings Per Project Report',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'report-billings-project' // Example active nav key
]);

// Assume $reportData is an array keyed by project name,
// containing billing details for each project.
$reportData = $reportData ?? [];
?>

<div class="container mt-4">
    <h1>Billings Per Project Report</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($reportData)): ?>
        <?php foreach ($reportData as $projectName => $projectBillings): ?>
            <h3 class="mt-4"><?= htmlspecialchars($projectName) ?></h3>
            <table class="table table-sm table-striped table-hover">
                <thead>
                    <tr>
                        <th>Billing ID / App #</th>
                        <th>Billing Date</th>
                        <th>Period To</th>
                        <th>Amount Billed</th>
                        <th>Amount Certified</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $projectTotalBilled = 0;
                    $projectTotalCertified = 0;
                    if (!empty($projectBillings)):
                        foreach ($projectBillings as $billing):
                            $projectTotalBilled += $billing['amount_billed'] ?? 0;
                            $projectTotalCertified += $billing['amount_certified'] ?? 0;
                    ?>
                            <tr>
                                <td><?= htmlspecialchars($billing['billing_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($billing['billing_date'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($billing['period_to'] ?? 'N/A') ?></td>
                                <td class="text-end"><?= number_format($billing['amount_billed'] ?? 0, 2) ?></td>
                                <td class="text-end"><?= number_format($billing['amount_certified'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($billing['status'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No billings found for this project.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Project Totals:</th>
                        <th class="text-end"><?= number_format($projectTotalBilled, 2) ?></th>
                        <th class="text-end"><?= number_format($projectTotalCertified, 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No billing data available for the selected criteria.</div>
    <?php endif; ?>

</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>