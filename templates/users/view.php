<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View User',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'users'
]);

// Assume $user holds the data for the user being viewed
$user = $user ?? [];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>User Details: <?= htmlspecialchars($user['name'] ?? $user['username'] ?? '') ?></h1>
        <div>
            <a href="/users/edit/<?= htmlspecialchars($user['id'] ?? '') ?>" class="btn btn-secondary">Edit User</a>
            <a href="/users" class="btn btn-outline-secondary">Back to List</a>
        </div>
    </div>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">First Name:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['first_name'] ?? 'N/A') ?></dd>

                <dt class="col-sm-3">Last Name:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['last_name'] ?? 'N/A') ?></dd>

                <dt class="col-sm-3">Username:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></dd>

                <dt class="col-sm-3">Email:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></dd>

                <dt class="col-sm-3">Role:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['role'] ?? 'N/A') ?></dd>

                <dt class="col-sm-3">Status:</dt>
                <dd class="col-sm-9">
                     <span class="badge bg-<?= ($user['is_active'] ?? false) ? 'success' : 'secondary' ?>">
                        <?= ($user['is_active'] ?? false) ? 'Active' : 'Inactive' ?>
                    </span>
                </dd>

                <dt class="col-sm-3">Created At:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['created_at'] ?? 'N/A') ?></dd>

                <dt class="col-sm-3">Last Updated:</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($user['updated_at'] ?? 'N/A') ?></dd>
            </dl>
        </div>
    </div>
</div>
<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>