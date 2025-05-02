<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View Staff',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Staff Details</h1>
    <!-- Staff details will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>