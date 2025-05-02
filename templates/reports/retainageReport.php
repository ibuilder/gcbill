<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Retainage Report',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Retainage Report</h1>
    <!-- Report data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>