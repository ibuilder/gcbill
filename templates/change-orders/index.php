<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'List Change Orders',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>List of Change Orders</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Project</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($changeOrders as $changeOrder): ?>
                <tr>
                    <td><?= htmlspecialchars($changeOrder->id) ?></td>
                    <td><?= htmlspecialchars($changeOrder->project) ?></td>
                    <td><?= htmlspecialchars($changeOrder->date) ?></td>
                    <td><?= htmlspecialchars($changeOrder->amount) ?></td>
                    <td><?= htmlspecialchars($changeOrder->description) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>