<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\billings\view.html -->
<?php
global $config;
// $viewHelper is passed from the controller
// Corrected: Use .php extension for partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View Billing',
    'config' => $config, 'currentUser' => $currentUser ?? null, 'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
     <div>
        <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'View Billing') ?></h1>
         <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
            <li class="breadcrumb-item"><a href="/projects/view/<?= $project['id'] ?>"><?= htmlspecialchars($project['project_number']) ?></a></li>
            <li class="breadcrumb-item"><a href="/projects/<?= $project['id'] ?>/billings">Billings</a></li>
            <li class="breadcrumb-item active" aria-current="page">View App #<?= $billing['billing_number'] ?></li>
          </ol>
        </nav>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">

        <a href="/billings/view/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">Print PDF</a>
         <?php if ($billing['status'] === 'draft'): // Only allow editing drafts ?>
             <a href="/billings/edit/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-primary me-2">Edit Billing</a>
         <?php endif; ?>

         <?php if ($billing['status'] === 'draft'): ?>
            <a href="/billings/submit/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-primary me-2">Submit</a>
         <?php endif; ?>

         <?php if ($billing['status'] === 'submitted'): ?>
             <a href="/billings/approve/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-success me-2">Approve</a>
         <?php endif; ?>

         <?php if ($billing['status'] === 'approved'): ?>
             <a href="/billings/pay/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-success me-2">Pay</a>
         <?php endif; ?>


         <a href="/projects/<?= $project['id'] ?>/billings" class="btn btn-sm btn-outline-secondary">Back to Billings List</a>
    </div>
</div>

<!-- Billing Header Section -->
<div class="card mb-4">
    <div class="card-header">Billing Period Information</div>
    <div class="card-body">
         <div class="row g-3 mb-3">
            <div class="col-md-2"><strong>Application #:</strong> <?= htmlspecialchars($billing['billing_number']) ?></div>
            <div class="col-md-3"><strong>Period Start:</strong> <?= $viewHelper->formatDate($billing['period_start_date']) ?></div>
            <div class="col-md-3"><strong>Period End:</strong> <?= $viewHelper->formatDate($billing['period_end_date']) ?></div>
            <div class="col-md-3"><strong>Billing Date:</strong> <?= $viewHelper->formatDate($billing['billing_date']) ?></div>
            <div class="col-md-1"></div>
            <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-<?= $viewHelper->getStatusBadgeClass($billing['status']) ?>"><?= ucfirst(htmlspecialchars($billing['status'])) ?></span></div>
            <div class="col-md-3">
                <strong>Retainage Rate:</strong>
                <?= isset($billing['retainage_rate']) ? number_format($billing['retainage_rate'] * 100, 2) . '%' : 'Project Default (' . number_format($project['retainage_percentage'] * 100, 2) . '%)' ?>
            </div>
             <div class="col-12">
                 <strong>Notes:</strong><br>
                 <?= nl2br(htmlspecialchars($billing['notes'] ?? 'N/A')) ?>
             </div>
        </div>
    </div>
</div>

<!-- Continuation Sheet (G703 Style) -->
<div class="card mb-4">
    <div class="card-header">Continuation Sheet (Application #<?= $billing['billing_number'] ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered billing-table mb-0">
                <thead class="table-light" style="font-size: 0.8rem;">
                     <tr>
                        <th rowspan="2" class="text-center align-middle" style="width: 5%;">Item</th>
                        <th rowspan="2" class="align-middle" style="width: 20%;">Description</th>
                        <th rowspan="2" class="text-end align-middle" style="width: 10%;">Scheduled Value (C)</th>
                        <th colspan="2" class="text-center">Work Completed</th>
                        <th rowspan="2" class="text-end align-middle" style="width: 10%;">Materials Presently Stored (E)</th>
                        <th rowspan="2" class="text-end align-middle" style="width: 10%;">Total Completed & Stored (D+E) = F</th>
                        <th rowspan="2" class="text-center align-middle" style="width: 5%;">% (F/C)</th>
                        <th rowspan="2" class="text-end align-middle" style="width: 10%;">Balance to Finish (C-F)</th>
                        <th rowspan="2" class="text-end align-middle" style="width: 10%;">Retainage (G)</th>
                    </tr>
                    <tr>
                        <th class="text-end" style="width: 10%;">From Previous (D)</th>
                        <th class="text-end" style="width: 10%;">This Period</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    <?php if (empty($billingDetails)): ?>
                        <tr><td colspan="10" class="text-center p-3">No billing details found.</td></tr>
                    <?php else: ?>
                        <?php
                            $totals = [
                                'scheduled_value' => 0.0, 'prev_work_completed' => 0.0, 'this_work_completed' => 0.0,
                                'this_materials_stored' => 0.0, 'total_completed_stored' => 0.0, 'balance_to_finish' => 0.0, 'retainage' => 0.0
                            ];
                        ?>
                        <?php foreach ($billingDetails as $item): ?>
                            <?php
                                // Accumulate totals
                                $totals['scheduled_value'] += $item['scheduled_value'];
                                $totals['prev_work_completed'] += $item['prev_work_completed'];
                                $totals['this_work_completed'] += $item['this_work_completed'];
                                $totals['this_materials_stored'] += $item['this_materials_stored'];
                                $totals['total_completed_stored'] += $item['total_completed_stored'];
                                $totals['balance_to_finish'] += $item['balance_to_finish'];
                                $totals['retainage'] += $item['retainage'];
                            ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($item['item_number']) ?></td>
                                <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['scheduled_value']) ?></td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['prev_work_completed']) ?></td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['this_work_completed']) ?></td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['this_materials_stored']) ?></td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['total_completed_stored']) ?></td>
                                <td class="text-center"><?= number_format($item['percent_complete'], 2) ?>%</td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['balance_to_finish']) ?></td>
                                <td class="text-end"><?= $viewHelper->formatCurrency($item['retainage']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                 <?php if (!empty($billingDetails)): ?>
                <tfoot class="table-light fw-bold" style="font-size: 0.85rem;">
                    <tr>
                        <td colspan="2" class="text-end">Totals:</td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['scheduled_value']) ?></td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['prev_work_completed']) ?></td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['this_work_completed']) ?></td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['this_materials_stored']) ?></td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['total_completed_stored']) ?></td>
                        <td class="text-center"><?= $totals['scheduled_value'] > 0 ? number_format(($totals['total_completed_stored'] / $totals['scheduled_value']) * 100, 2) : '0.00' ?>%</td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['balance_to_finish']) ?></td>
                        <td class="text-end"><?= $viewHelper->formatCurrency($totals['retainage']) ?></td>
                    </tr>
                </tfoot>
                 <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Application Summary (G702 Style) -->
<div class="card mb-4">
    <div class="card-header">Application Summary</div>
    <div class="card-body">
        <div class="row g-3 summary-section" style="font-size: 0.9rem;">
             <div class="col-md-8">1. ORIGINAL CONTRACT SUM</div>
            <div class="col-md-4 text-end"><?= $viewHelper->formatCurrency($summary['original_contract_sum']) ?></div>

            <div class="col-md-8">2. Net change by Change Orders</div>
            <div class="col-md-4 text-end"><?= $viewHelper->formatCurrency($summary['net_change_orders']) ?></div>

            <div class="col-md-8">3. CONTRACT SUM TO DATE (Line 1 ± 2)</div>
            <div class="col-md-4 text-end fw-bold"><?= $viewHelper->formatCurrency($summary['contract_sum_to_date']) ?></div>

            <div class="col-md-8">4. TOTAL COMPLETED & STORED TO DATE (Column F Total)</div>
            <div class="col-md-4 text-end"><?= $viewHelper->formatCurrency($summary['total_completed_stored']) ?></div>

            <div class="col-md-8">5. RETAINAGE:</div>
            <div class="col-md-4"></div>

            <div class="col-md-1 offset-md-1">a.</div>
            <div class="col-md-5"><?= number_format($summary['retainage_work_percent'], 2) ?> % of Completed Work</div>
            <div class="col-md-2 text-end"></div>
            <div class="col-md-3 text-end"><?= $viewHelper->formatCurrency($summary['retainage_work_amount']) ?></div>

            <div class="col-md-1 offset-md-1">b.</div>
            <div class="col-md-5"><?= number_format($summary['retainage_stored_percent'], 2) ?> % of Stored Material</div>
             <div class="col-md-2 text-end"></div>
            <div class="col-md-3 text-end"><?= $viewHelper->formatCurrency($summary['retainage_stored_amount']) ?></div>

            <div class="col-md-8 offset-md-1">Total Retainage (Line 5a + 5b or Total from Column G)</div>
            <div class="col-md-3 text-end fw-bold"><?= $viewHelper->formatCurrency($summary['total_retainage']) ?></div>

            <div class="col-md-8">6. TOTAL EARNED LESS RETAINAGE (Line 4 - Line 5 Total)</div>
            <div class="col-md-4 text-end"><?= $viewHelper->formatCurrency($summary['total_earned_less_retainage']) ?></div>

            <div class="col-md-8">7. LESS PREVIOUS CERTIFICATES FOR PAYMENT (Line 6 from prior Certificate)</div>
            <div class="col-md-4 text-end"><?= $viewHelper->formatCurrency($summary['less_previous_payments']) ?></div>

            <div class="col-md-8">8. CURRENT PAYMENT DUE (Line 6 - Line 7)</div>
            <div class="col-md-4 text-end fw-bold fs-5"><?= $viewHelper->formatCurrency($summary['current_payment_due']) ?></div>

            <div class="col-md-8">9. BALANCE TO FINISH, INCLUDING RETAINAGE (Line 3 - Line 4)</div>
            <div class="col-md-4 text-end"><?= $viewHelper->formatCurrency($summary['balance_to_finish']) ?></div>
        </div>
    </div>
</div>

<?php echo $view->includePartial('partials/footer.html', ['config' => $config]); ?>