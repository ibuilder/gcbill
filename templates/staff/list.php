<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'List Staff',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>List Staff</h1>
    <!-- Staff data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>