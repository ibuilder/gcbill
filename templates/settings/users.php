<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Users Settings',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'settings-users' // Example active nav key
]);

// Assume $users is an array of user objects/arrays
$users = $users ?? [];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Users Settings</h1>
        <a href="/settings/users/create" class="btn btn-primary">Add New User</a> <!-- Adjust href -->
    </div>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($user['role'] ?? 'N/A') ?></td> <!-- e.g., Admin, Project Manager, User -->
                        <td>
                            <span class="badge bg-<?= ($user['is_active'] ?? false) ? 'success' : 'secondary' ?>">
                                <?= ($user['is_active'] ?? false) ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <a href="/settings/users/edit/<?= htmlspecialchars($user['id'] ?? '') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <!-- Add delete/disable form/button if needed, ensure not deleting self -->
                            <?php if (($currentUser['id'] ?? null) !== ($user['id'] ?? null)): // Prevent deleting self ?>
                                <form action="/settings/users/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id'] ?? '') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                             <!-- Adjust href/action -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Add pagination controls if needed -->
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>