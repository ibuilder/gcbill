<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\billings\edit.html -->
<?php
global $config;
use App\Helpers\ViewHelper; // Use the helper for formatting

// Corrected: Use .php extension for partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Edit Billing',
    'config' => $config, 'currentUser' => $currentUser ?? null, 'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
     <div>
        <h1 class="h2" id="billing-page-title"><?= htmlspecialchars($pageTitle ?? 'Edit Billing') ?></h1>
         <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
            <li class="breadcrumb-item"><a href="/projects/view/<?= $project['id'] ?>" id="breadcrumb-project-link"><?= htmlspecialchars($project['project_number']) ?></a></li>
            <li class="breadcrumb-item"><a href="/projects/<?= $project['id'] ?>/billings" id="breadcrumb-billings-link">Billings</a></li>
            <li class="breadcrumb-item active" aria-current="page" id="breadcrumb-billing-number">App #<?= $billing['billing_number'] ?></li>
          </ol>
        </nav>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
         <button type="button" class="btn btn-sm btn-success me-2" id="save-billing-details-btn" disabled>
             <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
             Save Details
         </button>
         <a href="/billings/view/<?= $billing['id'] ?>" class="btn btn-sm btn-outline-info me-2" title="View/Print">View/Print</a>
         <a href="/projects/<?= $project['id'] ?>/billings" class="btn btn-sm btn-outline-secondary">Back to Billings List</a>
    </div>
</div>

<div id="billing-alert-container"></div>

<!-- Billing Header Section -->
<div class="card mb-4">
    <div class="card-header">Billing Period Information</div>
    <div class="card-body">
        <?php
        // Include the header form, passing the specific action for updating the header
        echo $view->includePartial('billings/_form_header.html', [
            'project' => $project ?? [],
            'billing' => $billing ?? [],
            'formAction' => '/billings/update-header/' . ($billing['id'] ?? 0)
        ]);
        ?>
    </div>
</div>

<!-- Continuation Sheet (G703 Style) -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Continuation Sheet (Application #<span id="continuation-sheet-app-number"><?= $billing['billing_number'] ?></span>)</span>
        <span id="calculation-status" class="text-muted small"></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <form id="billing-details-form">
                 <?= App\Helpers\SecurityHelper::csrfField(); // Add CSRF token for the details save ?>
                <table class="table table-sm table-bordered table-hover billing-table mb-0">
                    <thead class="table-light sticky-top" style="font-size: 0.8rem; z-index: 10;">
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
                    <tbody id="billing-details-body" style="font-size: 0.85rem;">
                        <!-- Rows will be loaded via JavaScript -->
                        <tr>
                            <td colspan="10" class="text-center p-5">
                                <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                                <p>Loading billing details...</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold" style="font-size: 0.85rem;">
                        <tr>
                            <td colspan="2" class="text-end">Totals:</td>
                            <td class="text-end" id="total-scheduled-value"></td>
                            <td class="text-end" id="total-prev-work"></td>
                            <td class="text-end" id="total-this-work"></td>
                            <td class="text-end" id="total-stored-materials"></td>
                            <td class="text-end" id="total-completed-stored"></td>
                            <td class="text-center" id="total-percent"></td>
                            <td class="text-end" id="total-balance"></td>
                            <td class="text-end" id="total-retainage"></td>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>

<!-- Application Summary (G702 Style) -->
<div class="card mb-4">
    <div class="card-header">Application Summary</div>
    <div class="card-body">
        <div class="row g-3 summary-section" style="font-size: 0.9rem;">
            <div class="col-md-8">1. ORIGINAL CONTRACT SUM</div>
            <div class="col-md-4 text-end" id="summary-original-contract">$0.00</div>

            <div class="col-md-8">2. Net change by Change Orders</div>
            <div class="col-md-4 text-end" id="summary-change-orders">$0.00</div> <!-- TODO: Implement Change Orders -->

            <div class="col-md-8">3. CONTRACT SUM TO DATE (Line 1 ± 2)</div>
            <div class="col-md-4 text-end fw-bold" id="summary-contract-to-date">$0.00</div>

            <div class="col-md-8">4. TOTAL COMPLETED & STORED TO DATE (Column F Total)</div>
            <div class="col-md-4 text-end" id="summary-total-completed-stored">$0.00</div>

            <div class="col-md-8">5. RETAINAGE:</div>
            <div class="col-md-4"></div> <!-- Spacer -->

            <div class="col-md-1 offset-md-1">a.</div>
            <div class="col-md-5"><input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm summary-retainage-percent" id="summary-retainage-work-percent" value="0.00"> % of Completed Work</div>
            <div class="col-md-2 text-end">(Line <span id="summary-retainage-work-ref">4</span> x %a)</div>
            <div class="col-md-3 text-end" id="summary-retainage-work-amount">$0.00</div>

            <div class="col-md-1 offset-md-1">b.</div>
            <div class="col-md-5"><input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm summary-retainage-percent" id="summary-retainage-stored-percent" value="0.00"> % of Stored Material</div>
             <div class="col-md-2 text-end">(Line <span id="summary-retainage-stored-ref">E</span> Total x %b)</div>
            <div class="col-md-3 text-end" id="summary-retainage-stored-amount">$0.00</div>

            <div class="col-md-8 offset-md-1">Total Retainage (Line 5a + 5b or Total from Column G)</div>
            <div class="col-md-3 text-end fw-bold" id="summary-total-retainage">$0.00</div>

            <div class="col-md-8">6. TOTAL EARNED LESS RETAINAGE (Line 4 - Line 5 Total)</div>
            <div class="col-md-4 text-end" id="summary-earned-less-retainage">$0.00</div>

            <div class="col-md-8">7. LESS PREVIOUS CERTIFICATES FOR PAYMENT (Line 6 from prior Certificate)</div>
            <div class="col-md-4 text-end" id="summary-less-previous-payments">$0.00</div>

            <div class="col-md-8">8. CURRENT PAYMENT DUE (Line 6 - Line 7)</div>
            <div class="col-md-4 text-end fw-bold fs-5" id="summary-current-payment-due">$0.00</div>

            <div class="col-md-8">9. BALANCE TO FINISH, INCLUDING RETAINAGE (Line 3 - Line 4)</div>
            <div class="col-md-4 text-end" id="summary-balance-to-finish">$0.00</div>
        </div>
    </div>
</div>

<!-- Hidden Template for Billing Detail Row -->
<template id="billing-detail-row-template">
    <tr data-sov-id="{sov_id}">
        <td class="text-center">{item_number}</td>
        <td>{description_html}</td>
        <td class="text-end scheduled-value">{scheduled_value_formatted}</td>
        <td class="text-end prev-work-completed">{prev_work_completed_formatted}</td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end this-work-completed"
                   name="details[{sov_id}][work_completed_this_period]" value="{this_work_completed}">
        </td>
        <td>
             <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end this-materials-stored"
                    name="details[{sov_id}][materials_stored_this_period]" value="{this_materials_stored}">
        </td>
        <td class="text-end total-completed-stored">{total_completed_stored_formatted}</td>
        <td class="text-center percent-complete">{percent_complete}</td>
        <td class="text-end balance-to-finish">{balance_to_finish_formatted}</td>
        <td class="text-end retainage">{retainage_formatted}</td>
    </tr>
</template>

<?php
// Add page-specific JavaScript for Billing Edit
$pageScripts = <<<JS
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- DOM Elements ---
        const billingId = {$billingId};
        const projectId = {$projectId};
        const detailsTableBody = document.getElementById('billing-details-body');
        const detailRowTemplate = document.getElementById('billing-detail-row-template');
        const saveDetailsBtn = document.getElementById('save-billing-details-btn');
        const saveSpinner = saveDetailsBtn.querySelector('.spinner-border');
        const alertContainer = document.getElementById('billing-alert-container');
        const csrfToken = document.querySelector('#billing-details-form input[name="_token"]')?.value;
        const csrfInputName = '<?= App\Helpers\SecurityHelper::getFormInputName(); ?>';
        const calculationStatusEl = document.getElementById('calculation-status');

        // Footer Totals
        const totalScheduledValueEl = document.getElementById('total-scheduled-value');
        const totalPrevWorkEl = document.getElementById('total-prev-work');
        const totalThisWorkEl = document.getElementById('total-this-work');
        const totalStoredMaterialsEl = document.getElementById('total-stored-materials');
        const totalCompletedStoredEl = document.getElementById('total-completed-stored');
        const totalPercentEl = document.getElementById('total-percent');
        const totalBalanceEl = document.getElementById('total-balance');
        const totalRetainageEl = document.getElementById('total-retainage');

        // Summary Elements
        const summaryOriginalContractEl = document.getElementById('summary-original-contract');
        const summaryChangeOrdersEl = document.getElementById('summary-change-orders');
        const summaryContractToDateEl = document.getElementById('summary-contract-to-date');
        const summaryTotalCompletedStoredEl = document.getElementById('summary-total-completed-stored');
        const summaryRetainageWorkPercentEl = document.getElementById('summary-retainage-work-percent');
        const summaryRetainageStoredPercentEl = document.getElementById('summary-retainage-stored-percent');
        const summaryRetainageWorkAmountEl = document.getElementById('summary-retainage-work-amount');
        const summaryRetainageStoredAmountEl = document.getElementById('summary-retainage-stored-amount');
        const summaryTotalRetainageEl = document.getElementById('summary-total-retainage');
        const summaryEarnedLessRetainageEl = document.getElementById('summary-earned-less-retainage');
        const summaryLessPreviousPaymentsEl = document.getElementById('summary-less-previous-payments');
        const summaryCurrentPaymentDueEl = document.getElementById('summary-current-payment-due');
        const summaryBalanceToFinishEl = document.getElementById('summary-balance-to-finish');

        let billingDataCache = null; // To store fetched data
        let isCalculating = false;
        let debounceTimer;

        // --- Utility Functions ---
        function formatCurrency(value, minimumFractionDigits = 2) {
            value = parseFloat(value) || 0;
            return value.toLocaleString('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: minimumFractionDigits });
        }

        function formatPercent(value) {
             value = parseFloat(value) || 0;
             return value.toFixed(2) + '%';
        }

        function parseCurrency(value) {
            if (typeof value === 'number') return value;
            if (typeof value !== 'string') return 0;
            return parseFloat(value.replace(/[$,]/g, '')) || 0;
        }

        function showAlert(message, type = 'danger') {
             alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             </div>`;
             window.scrollTo(0, 0); // Scroll to top to see alert
        }

        function clearAlert() {
            alertContainer.innerHTML = '';
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe
                 .toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        function setCalculationStatus(status) {
            calculationStatusEl.textContent = status;
        }

        // --- Calculation Logic ---
        function calculateAll() {
            if (isCalculating) return; // Prevent overlapping calculations
            isCalculating = true;
            setCalculationStatus('Calculating...');

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                try {
                    const rows = detailsTableBody.querySelectorAll('tr[data-sov-id]');
                    let grandTotals = {
                        scheduled_value: 0, prev_work_completed: 0, this_work_completed: 0,
                        this_materials_stored: 0, total_completed_stored: 0, balance_to_finish: 0, retainage: 0
                    };

                    const retainageWorkPercent = parseFloat(summaryRetainageWorkPercentEl.value) / 100 || 0;
                    const retainageStoredPercent = parseFloat(summaryRetainageStoredPercentEl.value) / 100 || 0;

                    rows.forEach(row => {
                        const rowTotals = calculateRowTotals(row, retainageWorkPercent, retainageStoredPercent);
                        grandTotals.scheduled_value += rowTotals.scheduled_value;
                        grandTotals.prev_work_completed += rowTotals.prev_work_completed;
                        grandTotals.this_work_completed += rowTotals.this_work_completed;
                        grandTotals.this_materials_stored += rowTotals.this_materials_stored;
                        grandTotals.total_completed_stored += rowTotals.total_completed_stored;
                        grandTotals.balance_to_finish += rowTotals.balance_to_finish;
                        grandTotals.retainage += rowTotals.retainage;
                    });

                    updateTableFooter(grandTotals);
                    calculateSummary(grandTotals);
                    saveDetailsBtn.disabled = false; // Enable save button after successful calculation
                    setCalculationStatus('Ready');
                } catch (error) {
                    console.error("Calculation Error:", error);
                    showAlert("Error during calculation. Check console.", "danger");
                    setCalculationStatus('Error');
                } finally {
                     isCalculating = false;
                }
            }, 300); // Debounce calculations
        }

        function calculateRowTotals(row, retainageWorkPercent, retainageStoredPercent) {
            const scheduledValue = parseCurrency(row.querySelector('.scheduled-value').textContent);
            const prevWorkCompleted = parseCurrency(row.querySelector('.prev-work-completed').textContent);
            const thisWorkCompletedInput = row.querySelector('.this-work-completed');
            const thisMaterialsStoredInput = row.querySelector('.this-materials-stored');

            let thisWorkCompleted = parseFloat(thisWorkCompletedInput.value) || 0;
            let thisMaterialsStored = parseFloat(thisMaterialsStoredInput.value) || 0;

            // --- Basic Calculations ---
            const totalWorkCompleted = prevWorkCompleted + thisWorkCompleted;
            const totalCompletedStored = totalWorkCompleted + thisMaterialsStored;
            const balanceToFinish = scheduledValue - totalCompletedStored;
            const percentComplete = scheduledValue > 0 ? (totalCompletedStored / scheduledValue) * 100 : 0;

            // --- Retainage Calculation (AIA G703 logic is complex, simplified here) ---
            // Option 1: Simple percentage of total completed & stored
            // const retainage = totalCompletedStored * retainageWorkPercent; // Assuming one rate for simplicity

            // Option 2: Separate rates for work and stored materials
            const retainageOnWork = totalWorkCompleted * retainageWorkPercent;
            const retainageOnStored = thisMaterialsStored * retainageStoredPercent; // Often retainage is only on work, or different rate for stored
            const retainage = retainageOnWork + retainageOnStored;

            // --- Update Row UI ---
            row.querySelector('.total-completed-stored').textContent = formatCurrency(totalCompletedStored);
            row.querySelector('.percent-complete').textContent = formatPercent(percentComplete);
            row.querySelector('.balance-to-finish').textContent = formatCurrency(balanceToFinish);
            row.querySelector('.retainage').textContent = formatCurrency(retainage);

            // --- Input Validation/Highlighting (Optional) ---
            if (totalCompletedStored > scheduledValue + 0.005) { // Allow small float tolerance
                thisWorkCompletedInput.classList.add('is-invalid');
                thisMaterialsStoredInput.classList.add('is-invalid');
                row.querySelector('.total-completed-stored').classList.add('text-danger');
            } else {
                thisWorkCompletedInput.classList.remove('is-invalid');
                thisMaterialsStoredInput.classList.remove('is-invalid');
                row.querySelector('.total-completed-stored').classList.remove('text-danger');
            }

            return {
                scheduled_value: scheduledValue,
                prev_work_completed: prevWorkCompleted,
                this_work_completed: thisWorkCompleted,
                this_materials_stored: thisMaterialsStored,
                total_completed_stored: totalCompletedStored,
                balance_to_finish: balanceToFinish,
                retainage: retainage
            };
        }

        function updateTableFooter(totals) {
            totalScheduledValueEl.textContent = formatCurrency(totals.scheduled_value);
            totalPrevWorkEl.textContent = formatCurrency(totals.prev_work_completed);
            totalThisWorkEl.textContent = formatCurrency(totals.this_work_completed);
            totalStoredMaterialsEl.textContent = formatCurrency(totals.this_materials_stored);
            totalCompletedStoredEl.textContent = formatCurrency(totals.total_completed_stored);
            totalPercentEl.textContent = totals.scheduled_value > 0 ? formatPercent((totals.total_completed_stored / totals.scheduled_value) * 100) : '0.00%';
            totalBalanceEl.textContent = formatCurrency(totals.balance_to_finish);
            totalRetainageEl.textContent = formatCurrency(totals.retainage);
        }

        function calculateSummary(grandTotals) {
            // Ensure billingDataCache and necessary sub-objects exist
            if (!billingDataCache || !billingDataCache.project || !billingDataCache.billingHeader) {
                 console.error("Cannot calculate summary: Missing cached data.");
                 return; // Exit if essential data is missing
            }

            const project = billingDataCache.project;
            const billingHeader = billingDataCache.billingHeader;
            // Use the previous payments total fetched from the backend
            const previousPaymentsTotal = billingDataCache.previousPaymentsTotal || 0.0;

            // 1. Original Contract Sum
            const originalContract = parseFloat(project.contract_amount || 0);
            summaryOriginalContractEl.textContent = formatCurrency(originalContract);

            // 2. Change Orders (Placeholder)
            const changeOrders = 0.00; // TODO: Fetch or calculate change orders
            summaryChangeOrdersEl.textContent = formatCurrency(changeOrders);

            // 3. Contract Sum to Date
            const contractToDate = originalContract + changeOrders;
            summaryContractToDateEl.textContent = formatCurrency(contractToDate);

            // 4. Total Completed & Stored
            const totalCompletedStored = grandTotals.total_completed_stored;
            summaryTotalCompletedStoredEl.textContent = formatCurrency(totalCompletedStored);

            // 5. Retainage
            const retainageWorkPercent = parseFloat(summaryRetainageWorkPercentEl.value) / 100 || 0;
            const retainageStoredPercent = parseFloat(summaryRetainageStoredPercentEl.value) / 100 || 0;
            const totalWorkCompleted = grandTotals.prev_work_completed + grandTotals.this_work_completed;
            const totalStored = grandTotals.this_materials_stored; // Total stored *this period* - AIA logic might differ

            const retainageWorkAmount = totalWorkCompleted * retainageWorkPercent;
            const retainageStoredAmount = totalStored * retainageStoredPercent;
            const totalRetainage = grandTotals.retainage; // Use column G total for consistency

            summaryRetainageWorkAmountEl.textContent = formatCurrency(retainageWorkAmount);
            summaryRetainageStoredAmountEl.textContent = formatCurrency(retainageStoredAmount);
            summaryTotalRetainageEl.textContent = formatCurrency(totalRetainage); // Use Column G total

            // 6. Total Earned Less Retainage
            const earnedLessRetainage = totalCompletedStored - totalRetainage;
            summaryEarnedLessRetainageEl.textContent = formatCurrency(earnedLessRetainage);

            // 7. Less Previous Payments
            summaryLessPreviousPaymentsEl.textContent = formatCurrency(previousPaymentsTotal); // Use the fetched value

            // 8. Current Payment Due
            const currentPaymentDue = earnedLessRetainage - previousPaymentsTotal; // Use the fetched value
            summaryCurrentPaymentDueEl.textContent = formatCurrency(currentPaymentDue);

            // 9. Balance to Finish
            const balanceToFinish = contractToDate - totalCompletedStored;
            summaryBalanceToFinishEl.textContent = formatCurrency(balanceToFinish);
        }

        // --- Data Loading ---
        async function loadBillingData() {
            setCalculationStatus('Loading...');
            saveDetailsBtn.disabled = true;
            try {
                const response = await fetch(`/billings/${billingId}/data`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();

                if (data.success) {
                    billingDataCache = data; // Store fetched data including previousPaymentsTotal
                    populateHeaderForm(data.billingHeader, data.project);
                    populateDetailsTable(data.billingDetails);
                    // Set default retainage percentages from project/billing header
                    const defaultRetainageRate = parseFloat(data.billingHeader?.retainage_rate ?? data.project?.retainage_percentage ?? 0) * 100;
                    summaryRetainageWorkPercentEl.value = defaultRetainageRate.toFixed(2);
                    summaryRetainageStoredPercentEl.value = defaultRetainageRate.toFixed(2); // Default stored same as work

                    calculateAll(); // Perform initial calculation
                } else {
                    showAlert(data.message || 'Failed to load billing data.');
                    detailsTableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data.</td></tr>';
                    setCalculationStatus('Error');
                }
            } catch (error) {
                console.error('Error loading billing data:', error);
                showAlert('Error loading billing data. Please check the console.');
                detailsTableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data.</td></tr>';
                setCalculationStatus('Error');
            }
        }

        function populateHeaderForm(billing, project) {
            // Update breadcrumbs and title if needed (data might be fresher than initial page load)
            document.getElementById('billing-page-title').textContent = `Edit Billing #${billing.billing_number} for ${escapeHtml(project.project_name)}`;
            document.getElementById('breadcrumb-project-link').textContent = escapeHtml(project.project_number);
            document.getElementById('breadcrumb-billing-number').textContent = `App #${billing.billing_number}`;
            document.getElementById('continuation-sheet-app-number').textContent = billing.billing_number;

            // Populate form fields (already done by PHP partial, but good for consistency if needed)
            // document.getElementById('billing_number').value = billing.billing_number;
            // ... etc ...
        }

        function populateDetailsTable(details) {
            detailsTableBody.innerHTML = ''; // Clear loading/existing rows
            if (!details || details.length === 0) {
                detailsTableBody.innerHTML = '<tr><td colspan="10" class="text-center">No Schedule of Values items found for this project. Cannot create billing details.</td></tr>';
                return;
            }

            details.forEach(item => {
                const row = createDetailRow(item);
                detailsTableBody.appendChild(row);
            });
        }

        function createDetailRow(item) {
            let template = detailRowTemplate.innerHTML;
            const descriptionHtml = item.description ? escapeHtml(item.description).replace(/\n/g, '<br>') : '';

            // Replace placeholders - calculations happen later
            template = template.replaceAll('{sov_id}', item.sov_id);
            template = template.replace('{item_number}', escapeHtml(item.item_number));
            template = template.replace('{description_html}', descriptionHtml);
            template = template.replace('{scheduled_value_formatted}', formatCurrency(item.scheduled_value));
            template = template.replace('{prev_work_completed_formatted}', formatCurrency(item.prev_work_completed));
            template = template.replace('{this_work_completed}', parseFloat(item.this_work_completed || 0).toFixed(2));
            template = template.replace('{this_materials_stored}', parseFloat(item.this_materials_stored || 0).toFixed(2));
            // Calculation placeholders - will be filled by calculateRowTotals
            template = template.replace('{total_completed_stored_formatted}', formatCurrency(0));
            template = template.replace('{percent_complete}', formatPercent(0));
            template = template.replace('{balance_to_finish_formatted}', formatCurrency(0));
            template = template.replace('{retainage_formatted}', formatCurrency(0));

            const tr = document.createElement('tr');
            tr.dataset.sovId = item.sov_id;
            tr.innerHTML = template.match(/<tr.*?>(.*)<\/tr>/s)[1]; // Get content inside the template's <tr>
            return tr;
        }

        // --- Event Handlers ---

        // Recalculate on input change (delegated)
        detailsTableBody.addEventListener('input', function(e) {
            if (e.target.classList.contains('this-work-completed') || e.target.classList.contains('this-materials-stored')) {
                calculateAll();
                saveDetailsBtn.disabled = true; // Disable save until calculation finishes
                setCalculationStatus('Recalculating...');
            }
        });

        // Recalculate on retainage percentage change
        summaryRetainageWorkPercentEl.addEventListener('input', calculateAll);
        summaryRetainageStoredPercentEl.addEventListener('input', calculateAll);

        // Save Details Button
        saveDetailsBtn.addEventListener('click', async function() {
            clearAlert();
            saveDetailsBtn.disabled = true;
            saveSpinner.classList.remove('d-none');
            setCalculationStatus('Saving...');

            const formData = new FormData(document.getElementById('billing-details-form'));
            // Add details from table inputs (FormData doesn't pick up values changed by JS directly sometimes)
            const rows = detailsTableBody.querySelectorAll('tr[data-sov-id]');
            rows.forEach(row => {
                 const sovId = row.dataset.sovId;
                 const workInput = row.querySelector('.this-work-completed');
                 const storedInput = row.querySelector('.this-materials-stored');
                 // Ensure the names match the controller expectation: details[sov_id][field_name]
                 formData.set(`details[${sovId}][work_completed_this_period]`, parseFloat(workInput.value) || 0);
                 formData.set(`details[${sovId}][materials_stored_this_period]`, parseFloat(storedInput.value) || 0);
            });

            try {
                const response = await fetch(`/billings/update/${billingId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json' // Expect JSON response
                    }
                });
                const data = await response.json();

                if (data.success) {
                    showAlert(data.message || 'Details saved successfully.', 'success');
                    // Optionally reload data to confirm, or just trust the save
                    // loadBillingData(); // Reload might reset unsaved header changes
                    setCalculationStatus('Saved');
                } else {
                    showAlert(data.message || 'Failed to save details.');
                    setCalculationStatus('Save Failed');
                }
            } catch (error) {
                console.error('Error saving billing details:', error);
                showAlert('Error saving details. Please check the console.');
                setCalculationStatus('Save Error');
            } finally {
                saveDetailsBtn.disabled = false;
                saveSpinner.classList.add('d-none');
                // Re-enable save button after a short delay if status is not 'Saved'
                if (calculationStatusEl.textContent !== 'Saved') {
                   setTimeout(() => { if (!isCalculating) saveDetailsBtn.disabled = false; }, 1000);
                }
            }
        });

        // --- Initial Load ---
        loadBillingData();
    });
</script>
JS;

echo $view->includePartial('partials/footer.php', [
    'config' => $config,
    'pageScripts' => $pageScripts // Pass the JS to the footer
]);
?>