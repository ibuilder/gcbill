<?php
$view = $this;
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Owners Chart',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Owners Chart</h1>
    <!-- Chart data will go here -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>