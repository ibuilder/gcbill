<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\projects\view.html -->
<?php
global $config;
echo $view->includePartial('partials/header.html', [
    'pageTitle' => $pageTitle ?? 'View Project',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'projects'
]);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'View Project') ?></h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/projects/<?= $project['id'] ?>/billings" class="btn btn-sm btn-outline-success me-2">Manage Billings</a> <!-- Added Billings Button -->
        <a href="/projects/edit/<?= $project['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">Edit Project</a>
        <a href="/projects" class="btn btn-sm btn-outline-secondary">Back to List</a>
    </div>
</div>

<h3><?= htmlspecialchars($project['project_number']) ?> - <?= htmlspecialchars($project['project_name']) ?></h3>

<div class="row">
    <div class="col-md-6">
        <p><strong>Status:</strong> <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($project['status'])) ?></span></p>
        <p><strong>Owner:</strong> <?= htmlspecialchars($project['owner_name'] ?? 'N/A') ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($project['address_line1'] ?? '') ?><br>
           <?= htmlspecialchars($project['address_line2'] ?? '') ?><br>
           <?= htmlspecialchars($project['city'] ?? '') ?>, <?= htmlspecialchars($project['state'] ?? '') ?> <?= htmlspecialchars($project['zip_code'] ?? '') ?>
        </p>
    </div>
    <div class="col-md-6">
         <p><strong>Start Date:</strong> <?= htmlspecialchars($project['start_date'] ? date('m/d/Y', strtotime($project['start_date'])) : 'N/A') ?></p>
         <p><strong>Est. Completion:</strong> <?= htmlspecialchars($project['completion_date'] ? date('m/d/Y', strtotime($project['completion_date'])) : 'N/A') ?></p>
         <p><strong>Contract Amount:</strong> $<?= number_format($project['contract_amount'] ?? 0, 2) ?></p>
         <p><strong>GMP Amount:</strong> $<?= number_format($project['gmp_amount'] ?? 0, 2) ?></p>
         <p><strong>GC Fee:</strong> <?= number_format($project['gc_fee_percentage'] ?? 0, 2) ?>%</p>
         <p><strong>Retainage:</strong> <?= number_format($project['retainage_percentage'] ?? 0, 2) ?>%</p>
    </div>
</div>

<hr>

<h4>Related Information</h4>
<!-- Add sections for SOV, Billings, Staff Assignments, General Conditions etc. later -->
<p><em>(Schedule of Values, Billings, Staff, etc. will be displayed here)</em></p>

<hr>

<h4>Schedule of Values (SOV)</h4>

<div id="sov-alert-container"></div> <!-- Container for success/error messages -->

<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered" id="sov-table">
        <thead class="table-light">
            <tr>
                <th style="width: 10%;">Item #</th>
                <th style="width: 55%;">Description</th>
                <th style="width: 20%;" class="text-end">Scheduled Value</th>
                <th style="width: 15%;" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody id="sov-table-body">
            <!-- SOV items will be loaded here via JavaScript -->
            <tr>
                <td colspan="4" class="text-center p-5"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Loading SOV...</td>
            </tr>
        </tbody>
        <tfoot class="table-light">
            <tr>
                <td colspan="2" class="text-end"><strong>Total Scheduled Value:</strong></td>
                <td class="text-end"><strong id="sov-total-value">$0.00</strong></td>
                <td></td>
            </tr>
             <tr>
                <td colspan="2" class="text-end"><strong>Original Contract Amount:</strong></td>
                <td class="text-end"><strong id="sov-contract-amount">$<?= number_format($project['contract_amount'] ?? 0, 2) ?></strong></td>
                <td id="sov-variance-cell"></td> <!-- Variance indicator -->
            </tr>
        </tfoot>
    </table>
</div>

<!-- Button to trigger adding a new row -->
<button class="btn btn-sm btn-outline-primary" id="add-sov-item-btn">
    Add SOV Item
</button>

<!-- Hidden Template for New/Edit Row -->
<template id="sov-form-row-template">
    <tr class="sov-form-row">
        <td><input type="text" class="form-control form-control-sm item-number" name="item_number" required></td>
        <td><textarea class="form-control form-control-sm description" name="description" rows="1" required></textarea></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm scheduled-value text-end" name="scheduled_value" required value="0.00"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-success save-sov-btn" title="Save">Save</button>
            <button type="button" class="btn btn-sm btn-secondary cancel-sov-btn" title="Cancel">Cancel</button>
            <input type="hidden" class="sov-item-id" name="sov_item_id" value="">
        </td>
    </tr>
</template>

<!-- Hidden Template for Display Row -->
<template id="sov-display-row-template">
    <tr data-id="{id}">
        <td>{item_number}</td>
        <td>{description_html}</td>
        <td class="text-end">{scheduled_value_formatted}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-secondary edit-sov-btn" title="Edit">Edit</button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-sov-btn" title="Delete">Delete</button>
        </td>
    </tr>
</template>


<!-- Remove or comment out the placeholder Billings section -->
<!--
<hr>
<h4>Billings</h4>
<p><em>(Billings based on SOV will appear here)</em></p>
-->

<?php
// Add page-specific JavaScript for SOV
$pageScripts = <<<JS
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const projectId = {$project['id']};
        const sovTableBody = document.getElementById('sov-table-body');
        const addSovItemBtn = document.getElementById('add-sov-item-btn');
        const sovFormRowTemplate = document.getElementById('sov-form-row-template');
        const sovDisplayRowTemplate = document.getElementById('sov-display-row-template');
        const sovTotalValueEl = document.getElementById('sov-total-value');
        const sovContractAmountEl = document.getElementById('sov-contract-amount');
        const sovVarianceCell = document.getElementById('sov-variance-cell');
        const alertContainer = document.getElementById('sov-alert-container');
        const csrfToken = document.querySelector('input[name="_token"]')?.value || '<?= App\Helpers\SecurityHelper::generateToken(); ?>'; // Get CSRF token
        const csrfInputName = '<?= App\Helpers\SecurityHelper::getFormInputName(); ?>';

        // --- Utility Functions ---
        function formatCurrency(value) {
            return parseFloat(value).toLocaleString('en-US', { style: 'currency', currency: 'USD' });
        }

        function showAlert(message, type = 'danger') {
             alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             </div>`;
        }

        function clearAlert() {
            alertContainer.innerHTML = '';
        }

        function createDisplayRow(item) {
            let template = sovDisplayRowTemplate.innerHTML;
            template = template.replace('{id}', item.id);
            template = template.replace('{item_number}', escapeHtml(item.item_number));
            // Handle potential null description and format newlines
            const descriptionHtml = item.description ? escapeHtml(item.description).replace(/\\n/g, '<br>') : '';
            template = template.replace('{description_html}', descriptionHtml);
            template = template.replace('{scheduled_value_formatted}', formatCurrency(item.scheduled_value || 0));
            const tr = document.createElement('tr');
            tr.dataset.id = item.id;
            tr.innerHTML = template.match(/<tr.*?>(.*)<\/tr>/s)[1]; // Get content inside the template's <tr>
            return tr;
        }

         function createFormRow(item = {}) {
            const templateNode = sovFormRowTemplate.content.cloneNode(true);
            const tr = templateNode.querySelector('tr');
            tr.querySelector('.item-number').value = item.item_number || '';
            tr.querySelector('.description').value = item.description || '';
            tr.querySelector('.scheduled-value').value = parseFloat(item.scheduled_value || 0.00).toFixed(2);
            tr.querySelector('.sov-item-id').value = item.id || '';
            return tr;
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

        function updateTotals(totalValue, contractAmount) {
            sovTotalValueEl.textContent = formatCurrency(totalValue);
            sovContractAmountEl.textContent = formatCurrency(contractAmount);
            const difference = totalValue - contractAmount;
            sovVarianceCell.innerHTML = ''; // Clear previous
            if (Math.abs(difference) > 0.005) { // Allow for tiny float differences
                const badgeClass = difference > 0 ? 'bg-warning text-dark' : 'bg-danger';
                const sign = difference > 0 ? '+' : '';
                sovVarianceCell.innerHTML = `<span class="badge ${badgeClass}">Variance: ${sign}${formatCurrency(difference)}</span>`;
            }
        }

        // --- Load Initial SOV Data ---
        async function loadSovItems() {
            sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center p-5"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Loading SOV...</td></tr>';
            try {
                const response = await fetch(`/projects/${projectId}/sov`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                sovTableBody.innerHTML = ''; // Clear loading indicator
                if (data.success) {
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            sovTableBody.appendChild(createDisplayRow(item));
                        });
                    } else {
                        sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No Schedule of Values items found for this project.</td></tr>';
                    }
                    updateTotals(data.totalScheduledValue, data.contractAmount);
                } else {
                     showAlert(data.message || 'Failed to load SOV items.');
                     sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading SOV.</td></tr>';
                }
            } catch (error) {
                console.error('Error loading SOV:', error);
                showAlert('Error loading SOV items. Please check the console.');
                sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading SOV.</td></tr>';
            }
        }

        // --- Event Handlers ---

        // Add New Item Button
        addSovItemBtn.addEventListener('click', () => {
            // Check if a form row already exists
            if (sovTableBody.querySelector('.sov-form-row')) {
                showAlert('Please save or cancel the current item first.', 'warning');
                return;
            }
            const newRow = createFormRow();
            sovTableBody.appendChild(newRow);
            newRow.querySelector('.item-number').focus();
            addSovItemBtn.disabled = true; // Disable add button while editing
        });

        // Save Button (Event Delegation)
        sovTableBody.addEventListener('click', async function(e) {
            if (e.target.classList.contains('save-sov-btn')) {
                clearAlert();
                const row = e.target.closest('tr');
                const itemId = row.querySelector('.sov-item-id').value;
                const isNew = !itemId;
                const url = isNew ? '/sov/store' : `/sov/update/${itemId}`;
                const method = 'POST'; // Using POST for both create and update

                const formData = new FormData();
                formData.append(csrfInputName, csrfToken);
                formData.append('project_id', projectId);
                formData.append('item_number', row.querySelector('.item-number').value.trim());
                formData.append('description', row.querySelector('.description').value.trim());
                formData.append('scheduled_value', row.querySelector('.scheduled-value').value.trim());
                // If using PUT method simulation: formData.append('_method', 'PUT');

                // Basic client-side validation
                if (!formData.get('item_number') || !formData.get('description') || formData.get('scheduled_value') === '') {
                    showAlert('Item Number, Description, and Scheduled Value are required.', 'warning');
                    return;
                }

                e.target.disabled = true; // Disable button during request
                e.target.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

                try {
                    const response = await fetch(url, { method: method, body: formData });
                    const data = await response.json();

                    if (data.success) {
                        showAlert(data.message || 'Saved successfully.', 'success');
                        const newDisplayRow = createDisplayRow(data.item);
                        if (isNew) {
                            row.replaceWith(newDisplayRow); // Replace form row with display row
                        } else {
                            const originalRow = sovTableBody.querySelector(`tr[data-id="${itemId}"]`);
                            originalRow.replaceWith(newDisplayRow); // Replace original display row
                        }
                        updateTotals(data.totalScheduledValue, data.contractAmount);
                        addSovItemBtn.disabled = false; // Re-enable add button
                    } else {
                        showAlert(data.message || 'Failed to save item.');
                        e.target.disabled = false;
                        e.target.innerHTML = 'Save';
                    }
                } catch (error) {
                    console.error('Error saving SOV item:', error);
                    showAlert('Error saving item. Please check the console.');
                    e.target.disabled = false;
                    e.target.innerHTML = 'Save';
                }
            }
        });

        // Cancel Button (Event Delegation)
        sovTableBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('cancel-sov-btn')) {
                clearAlert();
                const row = e.target.closest('tr');
                const itemId = row.querySelector('.sov-item-id').value;
                if (itemId) { // Editing existing item, revert to display row
                     const originalRow = sovTableBody.querySelector(`tr[data-id="${itemId}"]`);
                     if (originalRow) {
                         row.replaceWith(originalRow); // Put back the original display row
                     } else {
                         loadSovItems(); // Fallback: reload all if original not found
                     }
                } else { // Adding new item, just remove the form row
                    row.remove();
                }
                addSovItemBtn.disabled = false; // Re-enable add button
            }
        });

        // Edit Button (Event Delegation)
        sovTableBody.addEventListener('click', function(e) {
             if (e.target.classList.contains('edit-sov-btn')) {
                 clearAlert();
                 // Check if another form row is already open
                 if (sovTableBody.querySelector('.sov-form-row')) {
                     showAlert('Please save or cancel the current item first.', 'warning');
                     return;
                 }
                 const row = e.target.closest('tr');
                 const itemId = row.dataset.id;

                 // Fetch item data again to ensure it's current (optional but safer)
                 // Or extract from current row if confident it's up-to-date
                 const itemData = {
                     id: itemId,
                     item_number: row.cells[0].textContent,
                     description: row.cells[1].innerHTML.replace(/<br\s*\/?>/gi, "\n"), // Convert <br> back to newline for textarea
                     scheduled_value: row.cells[2].textContent.replace(/[$,]/g, '') // Remove formatting
                 };

                 const formRow = createFormRow(itemData);
                 row.replaceWith(formRow); // Replace display row with form row
                 formRow.querySelector('.item-number').focus();
                 addSovItemBtn.disabled = true; // Disable add button while editing
             }
        });

        // Delete Button (Event Delegation)
        sovTableBody.addEventListener('click', async function(e) {
             if (e.target.classList.contains('delete-sov-btn')) {
                 clearAlert();
                 if (!confirm('Are you sure you want to delete this SOV item? This cannot be undone.')) {
                     return;
                 }

                 const row = e.target.closest('tr');
                 const itemId = row.dataset.id;
                 const url = `/sov/delete/${itemId}`;
                 const method = 'POST'; // Using POST

                 const formData = new FormData();
                 formData.append(csrfInputName, csrfToken);
                 // If using DELETE method simulation: formData.append('_method', 'DELETE');

                 e.target.disabled = true; // Disable button during request

                 try {
                     const response = await fetch(url, { method: method, body: formData });
                     const data = await response.json();

                     if (data.success) {
                         showAlert(data.message || 'Item deleted successfully.', 'success');
                         row.remove(); // Remove row from table
                         updateTotals(data.totalScheduledValue, data.contractAmount);
                         // Check if table is now empty
                         if (sovTableBody.rows.length === 0) {
                             sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No Schedule of Values items found for this project.</td></tr>';
                         }
                     } else {
                         showAlert(data.message || 'Failed to delete item.');
                         e.target.disabled = false;
                     }
                 } catch (error) {
                     console.error('Error deleting SOV item:', error);
                     showAlert('Error deleting item. Please check the console.');
                     e.target.disabled = false;
                 }
             }
        });

        // --- Initial Load ---
        loadSovItems();
    });
</script>
JS;

echo $view->includePartial('partials/footer.html', [
    'config' => $config,
    'pageScripts' => $pageScripts // Pass the JS to the footer
]);
?>