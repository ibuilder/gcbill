<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'General Conditions Expenses',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>General Conditions Expenses</h1>
    <!-- Expenses data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>
