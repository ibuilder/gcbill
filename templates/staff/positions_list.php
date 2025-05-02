<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'List Staff Positions',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>List Staff Positions</h1>
    <!-- Staff positions data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>