<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'General Conditions Salary Rates',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'gc-salary-rates' // Example active nav key
]);
?>

<div class="container mt-4">
     <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>General Conditions Salary Rates</h1>
        <a href="/general-conditions/salary-rates/create" class="btn btn-primary">Add New Rate</a>
         <!-- Adjust href based on your routing -->
    </div>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Position / Title</th>
                <th>Rate Type</th>
                <th>Rate Amount</th>
                <th>Effective Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($salaryRates)): ?>
                <?php foreach ($salaryRates as $rate): ?>
                    <tr>
                        <td><?= htmlspecialchars($rate['title'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($rate['rate_type'] ?? 'N/A') ?></td> <!-- e.g., Hourly, Salary -->
                        <td style="text-align: right;">
                            <?php if (($rate['rate_type'] ?? '') === 'Hourly'): ?>
                                $<?= number_format($rate['amount'] ?? 0, 2) ?> / hr
                            <?php else: ?>
                                $<?= number_format($rate['amount'] ?? 0, 2) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($rate['effective_date'] ?? 'N/A') ?></td>
                        <td>
                            <a href="/general-conditions/salary-rates/edit/<?= htmlspecialchars($rate['id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <!-- Add a delete form/button if needed -->
                             <form action="/general-conditions/salary-rates/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this rate?');">
                                <input type="hidden" name="rate_id" value="<?= htmlspecialchars($rate['id'] ?? '') ?>">
                                <input type="hidden" name="_method" value="DELETE"> <!-- If using method spoofing -->
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                            <!-- Adjust href/action based on your routing -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No salary rates found.</td>
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