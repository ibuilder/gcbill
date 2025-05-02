<?php
global $config;
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit Owner',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'owners' // Corrected activeNav key
]);
?>

<div class="container mt-4">
    <h1>Edit Owner: <?= htmlspecialchars($owner['owner_name'] ?? '') ?></h1>
    <?php
    // Corrected: Include .php partial and pass data explicitly
    echo $view->includePartial('owners/_form.php', [
        'owner' => $owner ?? [],
        'formAction' => $formAction ?? '/owners/update/' . ($owner['id'] ?? 0) // Assuming update route
    ]);
    ?>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>