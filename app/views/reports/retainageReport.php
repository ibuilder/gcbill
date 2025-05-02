<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Retainage Report',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'report-retainage' // Example active nav key
]);

// Assume $reportData is an array of project retainage objects/arrays
$reportData = $reportData ?? [];
?>

<div class="container mt-4">
    <h1>Retainage Report</h1>

     <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Original Contract</th>
                <th>Total Billed to Date</th>
                <th>Total Retainage Held</th>
                <th>Retainage Released</th>
                <th>Current Retainage Balance</th>
            </tr>
        </thead>
        <tbody>
             <?php
             $grandTotalHeld = 0;
             $grandTotalReleased = 0;
             $grandTotalBalance = 0;
             if (!empty($reportData)): ?>
                <?php foreach ($reportData as $project):
                    $currentBalance = ($project['total_retainage_held'] ?? 0) - ($project['retainage_released'] ?? 0);
                    $grandTotalHeld += $project['total_retainage_held'] ?? 0;
                    $grandTotalReleased += $project['retainage_released'] ?? 0;
                    $grandTotalBalance += $currentBalance;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($project['name'] ?? 'N/A') ?></td>
                        <td class="text-end"><?= number_format($project['original_contract'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($project['total_billed'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($project['total_retainage_held'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($project['retainage_released'] ?? 0, 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($currentBalance, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No retainage data available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
         <?php if (!empty($reportData)): ?>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Grand Totals:</th>
                <th class="text-end"><?= number_format($grandTotalHeld, 2) ?></th>
                <th class="text-end"><?= number_format($grandTotalReleased, 2) ?></th>
                <th class="text-end fw-bold"><?= number_format($grandTotalBalance, 2) ?></th>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>