<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit Staff Position',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Edit Staff Position</h1>
    <form action="/staff/positions/update" method="POST">
        <?php echo $view->includePartial('staff/_position_form.php'); ?>
        <div>
            <button type="submit">Edit Staff Position</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>