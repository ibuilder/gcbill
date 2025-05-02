<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\owners\view.html -->
<?php
global $config;
echo $view->includePartial('partials/header.html', [
    'pageTitle' => $pageTitle ?? 'View Owner',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'owners'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'View Owner') ?></h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/owners/edit/<?= $owner['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">Edit Owner</a>
        <a href="/owners" class="btn btn-sm btn-outline-secondary">Back to List</a>
    </div>
</div>

<h3><?= htmlspecialchars($owner['owner_name']) ?></h3>

<div class="row">
    <div class="col-md-6">
        <p><strong>Primary Contact:</strong> <?= htmlspecialchars($owner['primary_contact_name'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($owner['primary_contact_email'] ?? 'N/A') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($owner['primary_contact_phone'] ?? 'N/A') ?></p>
    </div>
    <div class="col-md-6">
         <p><strong>Address:</strong><br>
           <?= htmlspecialchars($owner['address_line1'] ?? '') ?><br>
           <?= htmlspecialchars($owner['address_line2'] ?? '') ?><br>
           <?= htmlspecialchars($owner['city'] ?? '') ?>, <?= htmlspecialchars($owner['state'] ?? '') ?> <?= htmlspecialchars($owner['zip_code'] ?? '') ?>
        </p>
    </div>
</div>

<hr>

<h4>Linked Projects</h4>
<!-- TODO: Add query in controller to fetch projects linked to this owner -->
<p><em>(List of projects linked to this owner will appear here)</em></p>

<?php
echo $view->includePartial('partials/footer.html', ['config' => $config]);
?>