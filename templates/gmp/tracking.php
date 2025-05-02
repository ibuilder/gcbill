<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'GMP Tracking',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>GMP Tracking</h1>
    <!-- GMP tracking data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>