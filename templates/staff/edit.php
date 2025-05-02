<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit Staff',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Edit Staff</h1>
    <form action="/staff/update" method="POST">
        <?php echo $view->includePartial('staff/_form.php'); ?>
        <div>
            <button type="submit">Edit Staff</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>