<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View Notification',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Notification Details</h1>
    <!-- Notification details will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>