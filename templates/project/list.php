<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\projects\list.html -->
<?php
global $config;
// Corrected: Include .php partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Projects',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'Projects') ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/projects/create" class="btn btn-sm btn-outline-primary">
            <!-- Add icon if using FontAwesome -->
            <!-- <i class="fas fa-plus"></i> -->
            Add New Project
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Project Number</th>
                <th scope="col">Project Name</th>
                <th scope="col">Owner</th>
                <th scope="col">Status</th>
                <th scope="col">Start Date</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="7" class="text-center">No projects found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($projects as $index => $project): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($project['project_number']) ?></td>
                        <td><?= htmlspecialchars($project['project_name']) ?></td>
                        <td><?= htmlspecialchars($project['owner_name'] ?? 'N/A') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($project['status'])) ?></span></td>
                        <td><?= htmlspecialchars($project['start_date'] ? date('m/d/Y', strtotime($project['start_date'])) : 'N/A') ?></td>
                        <td>
                            <a href="/projects/view/<?= $project['id'] ?>" class="btn btn-sm btn-outline-info" title="View">View</a>
                            <a href="/projects/edit/<?= $project['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">Edit</a>
                            <!-- Delete Button using a form for CSRF -->
                            <form action="/projects/delete/<?= $project['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                <?= App\Helpers\SecurityHelper::csrfField(); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Corrected: Include .php partial
echo $view->includePartial('partials/footer.php', ['config' => $config]);
?>