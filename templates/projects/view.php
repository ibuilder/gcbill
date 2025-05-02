<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View Projects',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Projects Details</h1>
    <!-- Projects details will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>