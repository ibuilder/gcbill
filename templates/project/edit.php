<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\projects\edit.html -->
<?php
global $config;
// Corrected: Include .php partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit Project',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'Edit Project') ?></h1>
</div>

<?php
// Include the form partial
// Pass project data (potentially from session on error), owners, and form action
// Corrected: Include _form.php from the current directory
echo $view->includePartial('project/_form.php', [
    'project' => $project ?? [], // Data comes from controller
    'owners' => $owners ?? [],
    'formAction' => $formAction ?? '/projects/update/' . ($project['id'] ?? 0)
]);
?>

<?php
// Corrected: Include .php partial
echo $view->includePartial('partials/footer.php', ['config' => $config]);
?>