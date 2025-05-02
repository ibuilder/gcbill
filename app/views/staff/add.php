<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Add Staff',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Add Staff</h1>
    <form action="/staff/store" method="POST">
        <?php echo $view->includePartial('staff/_form.php'); ?>
        <div>
            <button type="submit">Add Staff</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>