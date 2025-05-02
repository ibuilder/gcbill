<?php
// Use the View instance passed to the template to include partials
// Pass necessary data like pageTitle and config to the header
global $config; // Make config available
echo $view->includePartial('partials/header.php', [
'pageTitle' => $pageTitle ?? 'Dashboard',
    'config' => $config,
    'currentUser' => $currentUser ?? null, // Pass user data
    'activeNav' => $activeNav ?? null // Pass active nav indicator
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    <!-- Optional: Add buttons or controls here -->
    <!-- <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
        </div>
    </div> -->
</div>
    <p>Welcome back, <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username'] ?? 'User') ?>!</p>
<div class="row">
    <!-- Project Summary Cards -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                Project Summary
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($totalProjects ?? 0) ?></h5>
                                <p class="card-text">Total Projects</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($activeProjects ?? 0) ?></h5>
                                <p class="card-text">Active Projects</p>
                            </div>
                        </div>
                    </div>
                     <div class="col-12">
                         <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= $viewHelper->formatCurrency($totalContractAmount) ?></h5>
                                <p class="card-text">Total Contract Amount (Active Projects)</p>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Summary Cards -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                Billing Summary
            </div>
            <div class="card-body">
                <div class="row">
                     <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($draftBillings ?? 0) ?></h5>
                                <p class="card-text">Draft Billings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($submittedBillings ?? 0) ?></h5>
                                <p class="card-text">Submitted Billings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-secondary mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($approvedBillings ?? 0) ?></h5>
                                <p class="card-text">Approved Billings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                         <div class="card bg-light mb-3">
                             <div class="card-body">
                                <h5 class="card-title"><?= $viewHelper->formatCurrency($totalCurrentPaymentDue) ?></h5>
                                <p class="card-text">Total Current Payment Due</p>
                            </div>
                         </div>
                    </div>

                     <div class="col-12">
                         <div class="card bg-light mb-3">
                             <div class="card-body">
                                <h5 class="card-title"><?= $viewHelper->formatCurrency($totalRetainage) ?></h5>
                                <p class="card-text">Total Retainage</p>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
// Include the footer partial
echo $view->includePartial('partials/footer.php', [
     'config' => $config // Pass config if needed in footer
]);
?>