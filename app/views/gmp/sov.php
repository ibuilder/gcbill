<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'GMP SOV',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'gmp-sov' // Example active nav key
]);

// Assume $sovItems holds the SOV lines for the GMP project
$sovItems = $sovItems ?? [];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>GMP Schedule of Values</h1>
        <a href="/gmp/sov/create" class="btn btn-primary">Add SOV Line</a>
        <!-- Adjust href based on your routing -->
    </div>

     <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover table-sm"> <!-- table-sm for potentially dense SOV -->
        <thead>
            <tr>
                <th>Item No.</th>
                <th>Description</th>
                <th>Scheduled Value (Budget)</th>
                <th>Cost To Date</th> <!-- Example tracking column -->
                <th>Variance</th> <!-- Example tracking column -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalScheduledValue = 0;
            $totalCostToDate = 0;
            if (!empty($sovItems)):
                foreach ($sovItems as $item):
                    $scheduledValue = $item['scheduled_value'] ?? 0;
                    $costToDate = $item['cost_to_date'] ?? 0; // Fetch this from cost tracking
                    $variance = $scheduledValue - $costToDate;
                    $totalScheduledValue += $scheduledValue;
                    $totalCostToDate += $costToDate;
            ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
                        <td style="text-align: right;"><?= number_format($scheduledValue, 2) ?></td>
                        <td style="text-align: right;"><?= number_format($costToDate, 2) ?></td>
                        <td style="text-align: right; color: <?= $variance >= 0 ? 'green' : 'red' ?>;">
                            <?= number_format($variance, 2) ?>
                        </td>
                        <td>
                            <a href="/gmp/sov/edit/<?= htmlspecialchars($item['id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <!-- Add delete form/button -->
                            <form action="/gmp/sov/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="sov_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
            <?php
                endforeach;
            else:
            ?>
                <tr>
                    <td colspan="6" class="text-center">No SOV items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="text-align: right;">Totals:</th>
                <th style="text-align: right;"><?= number_format($totalScheduledValue, 2) ?></th>
                <th style="text-align: right;"><?= number_format($totalCostToDate, 2) ?></th>
                <th style="text-align: right; color: <?= ($totalScheduledValue - $totalCostToDate) >= 0 ? 'green' : 'red' ?>;">
                    <?= number_format($totalScheduledValue - $totalCostToDate, 2) ?>
                </th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>