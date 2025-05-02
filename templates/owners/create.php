<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\owners\create.html -->
<?php
global $config;
// Corrected: Include .php partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Create Owner',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'owners'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'Create New Owner') ?></h1>
</div>

<?php
$ownerData = $_SESSION['form_data'] ?? $owner ?? [];
unset($_SESSION['form_data']);

// Corrected: Include .php partial and pass data explicitly
echo $view->includePartial('owners/_form.php', [
    'owner' => $ownerData,
    'formAction' => $formAction ?? '/owners/store'
]);
?>

<?php
// Corrected: Include .php partial
echo $view->includePartial('partials/footer.php', ['config' => $config]);
?>