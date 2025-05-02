<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\projects\create.html -->
<?php
global $config;
// Corrected: Include .php partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Create Project',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'Create New Project') ?></h1>
</div>

<?php
// Include the form partial
// Pass project data (empty or from session on error), owners, and form action
$projectData = $_SESSION['form_data'] ?? $project ?? []; // Use session data if available
unset($_SESSION['form_data']); // Clear after use

// Corrected: Include _form.php from the current directory
echo $view->includePartial('project/_form.php', [
    'project' => $projectData,
    'owners' => $owners ?? [],
    'formAction' => $formAction ?? '/projects/store'
]);
?>

<?php
// Corrected: Include .php partial
echo $view->includePartial('partials/footer.php', ['config' => $config]);
?>