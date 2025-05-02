<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Create Change Order',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Create Change Order</h1>
    <form action="/change-orders/store" method="POST">
        <!-- Form fields will go here -->
        <div>
            <label for="project">Project:</label>
            <input type="text" id="project" name="project" required>
        </div>
        <div>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div>
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" required>
        </div>
        <div>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        <div>
            <button type="submit">Create Change Order</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>

</html>