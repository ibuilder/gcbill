<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Project Cost Summary Report',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'report-cost-summary' // Example active nav key
]);

// Assume $reportData is an array of project summary objects/arrays
$reportData = $reportData ?? [];
?>

<div class="container mt-4">
    <h1>Project Cost Summary Report</h1>

     <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Original Contract / Budget</th>
                <th>Approved Changes</th>
                <th>Current Contract / Budget</th>
                <th>Committed Costs</th>
                <th>Actual Costs to Date</th>
                <th>Remaining Budget</th>
                <th>Projected Variance</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reportData)): ?>
                <?php foreach ($reportData as $project):
                    $currentBudget = ($project['original_budget'] ?? 0) + ($project['approved_changes'] ?? 0);
                    $remainingBudget = $currentBudget - ($project['actual_costs'] ?? 0);
                    // Projected variance calculation might be more complex (e.g., cost to complete)
                    $projectedVariance = $currentBudget - ($project['projected_cost'] ?? $project['actual_costs'] ?? 0);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($project['name'] ?? 'N/A') ?></td>
                        <td class="text-end"><?= number_format($project['original_budget'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($project['approved_changes'] ?? 0, 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($currentBudget, 2) ?></td>
                        <td class="text-end"><?= number_format($project['committed_costs'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($project['actual_costs'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($remainingBudget, 2) ?></td>
                        <td class="text-end" style="color: <?= $projectedVariance >= 0 ? 'green' : 'red' ?>;">
                            <?= number_format($projectedVariance, 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No project cost summary data available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>
