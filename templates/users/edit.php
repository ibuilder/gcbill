<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit User',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'users'
]);

// Assume $user holds the data for the user being edited
$user = $user ?? [];
// Assume $roles contains available user roles
$roles = $roles ?? ['User', 'Admin', 'Project Manager']; // Example roles
?>

<div class="container mt-4">
    <h1>Edit User: <?= htmlspecialchars($user['name'] ?? $user['username'] ?? '') ?></h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>
    <?php // Display validation errors if available ?>
     <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/users/update/<?= htmlspecialchars($user['id'] ?? '') ?>" method="POST"> <!-- Adjust action -->
        <!-- Add CSRF token field if using CSRF protection -->
        <!-- <input type="hidden" name="csrf_token" value="<?//= htmlspecialchars($csrf_token ?? '') ?>"> -->
        <input type="hidden" name="_method" value="PUT"> <!-- Or PATCH -->

        <div class="row mb-3">
            <label for="first_name" class="col-sm-3 col-form-label">First Name</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="first_name" name="user[first_name]" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="last_name" class="col-sm-3 col-form-label">Last Name</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="last_name" name="user[last_name]" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="email" class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="email" name="user[email]" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
        </div>
         <div class="row mb-3">
            <label for="username" class="col-sm-3 col-form-label">Username</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="username" name="user[username]" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="password" class="col-sm-3 col-form-label">New Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control" id="password" name="user[password]">
                <small class="form-text text-muted">Leave blank to keep the current password.</small>
            </div>
        </div>
        <div class="row mb-3">
            <label for="password_confirmation" class="col-sm-3 col-form-label">Confirm New Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control" id="password_confirmation" name="user[password_confirmation]">
            </div>
        </div>
        <div class="row mb-3">
            <label for="role" class="col-sm-3 col-form-label">Role</label>
            <div class="col-sm-9">
                <select class="form-select" id="role" name="user[role]" required>
                    <option value="">Select Role...</option>
                     <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>" <?= (($user['role'] ?? '') === $role) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
         <div class="row mb-3">
            <label for="is_active" class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-9">
                 <select class="form-select" id="is_active" name="user[is_active]">
                    <option value="1" <?= (($user['is_active'] ?? '1') == '1') ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (($user['is_active'] ?? '1') == '0') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="/users" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>