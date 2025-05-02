<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'GMP SOV',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>GMP SOV</h1>
    <!-- GMP SOV data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>