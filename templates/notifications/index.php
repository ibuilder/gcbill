<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Notifications',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Notifications</h1>
    <!-- Notifications data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>