<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'General Conditions Expenses',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'gc-expenses' // Example active nav key
]);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>General Conditions Expenses</h1>
        <a href="/general-conditions/expenses/create" class="btn btn-primary">Add New Expense</a>
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
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Project</th> <!-- Optional: If expenses are project-specific -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($expenses)): ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars($expense['date'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($expense['description'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($expense['category'] ?? 'N/A') ?></td>
                        <td style="text-align: right;"><?= number_format($expense['amount'] ?? 0, 2) ?></td>
                        <td><?= htmlspecialchars($expense['project_name'] ?? 'General') ?></td>
                        <td>
                            <a href="/general-conditions/expenses/edit/<?= htmlspecialchars($expense['id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <!-- Add a delete form/button if needed -->
                            <form action="/general-conditions/expenses/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                <input type="hidden" name="expense_id" value="<?= htmlspecialchars($expense['id'] ?? '') ?>">
                                <input type="hidden" name="_method" value="DELETE"> <!-- If using method spoofing -->
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                             <!-- Adjust href/action based on your routing -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No general conditions expenses found.</td>
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
