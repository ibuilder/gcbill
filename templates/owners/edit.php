<?php
global $config;
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit Owner',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Edit Owner</h1>
    <form action="/owners/update" method="POST">
        <?php echo $view->includePartial('owners/_form.php'); ?>
        <div>
            <button type="submit">Edit Owner</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>