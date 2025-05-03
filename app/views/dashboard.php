<?php
// Use the View instance passed to the template to include partials
// Pass necessary data like pageTitle and config to the header
global $config; // Make config available
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Dashboard',
    'config' => $config, // Pass config to header
    'currentUser' => $currentUser ?? null, // Pass user to header
    'activeNav' => $activeNav ?? 'dashboard' // Pass active nav indicator
]);

// Include flash messages
echo $view->includePartial('partials/flash-messages.php', ['flashMessages' => $flashMessages ?? null]);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>
        <p>Welcome back, <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username'] ?? 'User') ?>!</p>
    <div class="row">
        <!-- Dashboard widgets/summaries can go here -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Projects Overview</div>
                <div class="card-body">
                    <p>Placeholder for project summary...</p>
                    <a href="/projects" class="btn btn-primary">View Projects</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
             <div class="card">
                <div class="card-header">Recent Activity</div>
                <div class="card-body">
                    <p>Placeholder for recent activity feed...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Corrected: Add footer include
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>