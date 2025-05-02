<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'List Users',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>List Users</h1>
    <!-- Users data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>