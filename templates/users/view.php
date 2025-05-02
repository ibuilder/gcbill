<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View User',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>User Details</h1>
    <!-- User details will go here -->
    <dl>
        <dt>Username</dt>
        <dd><?= htmlspecialchars($user->username ?? '') ?></dd>
        <dt>Email</dt>
        <dd><?= htmlspecialchars($user->email ?? '') ?></dd>
    </dl>
    <a href="/users/<?= htmlspecialchars($user->id ?? '') ?>/edit"><button>Edit</button></a>
</div>
<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>