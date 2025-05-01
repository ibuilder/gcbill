<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\billings\list.html -->
<?php
global $config;
echo $view->includePartial('partials/header.html', [
    'pageTitle' => $pageTitle ?? 'Billings',
    'config' => $config, 'currentUser' => $currentUser ?? null, 'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'Billings') ?></h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
            <li class="breadcrumb-item"><a href="/projects/view/<?= $project['id'] ?>"><?= htmlspecialchars($project['project_number']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Billings</li>
          </ol>
        </nav>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/projects/view/<?= $project['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">Back to Project</a>
        <a href="/projects/<?= $project['id'] ?>/billings/create" class="btn btn-sm btn-outline-primary">
            Create Billing #<?= $nextBillingNumber ?>
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>App #</th>
                <th>Period Start</th>
                <th>Period End</th>
                <th>Billing Date</th>
                <th>Status</th>
                <th>Total Billed (Est.)</th> <!-- Add calculation later -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($billings)): ?>
                <tr><td colspan="7" class="text-center">No billings found for this project.</td></tr>
            <?php else: ?>
                <?php foreach ($billings as $billing): ?>
                    <tr>
                        <td><?= htmlspecialchars($billing['billing_number']) ?></td>
                        <td><?= $billing['period_start_date'] ? date('m/d/Y', strtotime($billing['period_start_date'])) : 'N/A' ?></td>
                        <td><?= date('m/d/Y', strtotime($billing['period_end_date'])) ?></td>
                        <td><?= date('m/d/Y', strtotime($billing['billing_date'])) ?></td>
                        <td>
                            <span class="badge bg-<?= App\Helpers\ViewHelper::getStatusBadgeClass($billing['status']) ?>">
                                <?= ucfirst(htmlspecialchars($billing['status'])) ?>
                            </span>
                        </td>
                        <td><?= '$ TBD' ?></td> <!-- Placeholder -->
                        <td>
                            <a href="/billings/edit/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit Details">Edit</a>
                            <a href="/billings/view/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-info" title="View/Print">View</a>
                            <?php if ($billing['status'] === 'draft'): ?>
                            <form action="/billings/delete/<?= $billing['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete Draft Billing #<?= $billing['billing_number'] ?>?');">
                                <?= App\Helpers\SecurityHelper::csrfField(); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Draft">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo $view->includePartial('partials/footer.html', ['config' => $config]); ?>