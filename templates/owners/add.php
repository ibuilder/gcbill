<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Add Owner',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? null
]);
?>

<div class="container mt-4">
    <h1>Add Owner</h1>
    <form action="/owners/store" method="POST">
        <?php echo $view->includePartial('owners/_form.php'); ?>
        <div>
            <button type="submit">Add Owner</button>
        </div>
    </form>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>