<?php
global $config;
// Corrected: Include .php partial
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'View Project',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'projects'
]);

// Ensure SecurityHelper class exists before using it
$csrfToken = '';
$csrfInputName = '_csrf_token'; // Default name, adjust if different
if (class_exists('App\Helpers\SecurityHelper')) {
    $csrfToken = App\Helpers\SecurityHelper::generateToken();
    $csrfInputName = App\Helpers\SecurityHelper::getFormInputName();
} else {
    // Fallback or error handling if SecurityHelper is missing
    // You might want to log an error or use a different CSRF mechanism
    error_log('Warning: App\Helpers\SecurityHelper class not found.');
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($pageTitle ?? 'View Project') ?></h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/projects/<?= htmlspecialchars($project['id']) ?>/billings" class="btn btn-sm btn-outline-success me-2">Manage Billings</a> <!-- Added Billings Button -->
        <a href="/projects/edit/<?= htmlspecialchars($project['id']) ?>" class="btn btn-sm btn-outline-secondary me-2">Edit Project</a>
        <a href="/projects" class="btn btn-sm btn-outline-secondary">Back to List</a>
    </div>
</div>

<h3><?= htmlspecialchars($project['project_number']) ?> - <?= htmlspecialchars($project['project_name']) ?></h3>

<div class="row">
    <div class="col-md-6">
        <p><strong>Status:</strong> <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($project['status'] ?? 'N/A')) ?></span></p>
        <p><strong>Owner:</strong> <?= htmlspecialchars($project['owner_name'] ?? 'N/A') ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($project['address_line1'] ?? '') ?><br>
           <?= !empty($project['address_line2']) ? htmlspecialchars($project['address_line2']) . '<br>' : '' ?>
           <?= htmlspecialchars($project['city'] ?? '') ?>, <?= htmlspecialchars($project['state'] ?? '') ?> <?= htmlspecialchars($project['zip_code'] ?? '') ?>
        </p>
    </div>
    <div class="col-md-6">
         <p><strong>Start Date:</strong> <?= htmlspecialchars($project['start_date'] ? date($config['app']['date_format'] ?? 'm/d/Y', strtotime($project['start_date'])) : 'N/A') ?></p>
         <p><strong>Est. Completion:</strong> <?= htmlspecialchars($project['completion_date'] ? date($config['app']['date_format'] ?? 'm/d/Y', strtotime($project['completion_date'])) : 'N/A') ?></p>
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
                <td id="sov-variance-cell" class="text-center"></td> <!-- Variance indicator -->
            </tr>
        </tfoot>
    </table>
</div>

<!-- Button to trigger adding a new row -->
<button class="btn btn-sm btn-outline-primary" id="add-sov-item-btn">
    <i class="fas fa-plus"></i> Add SOV Item <!-- Optional: Add icon -->
</button>

<!-- Hidden Template for New/Edit Row -->
<template id="sov-form-row-template">
    <tr class="sov-form-row">
        <td><input type="text" class="form-control form-control-sm item-number" name="item_number" required></td>
        <td><textarea class="form-control form-control-sm description" name="description" rows="1" required></textarea></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm scheduled-value text-end" name="scheduled_value" required value="0.00"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-success save-sov-btn" title="Save"><i class="fas fa-save"></i></button> <!-- Optional: Add icon -->
            <button type="button" class="btn btn-sm btn-secondary cancel-sov-btn" title="Cancel"><i class="fas fa-times"></i></button> <!-- Optional: Add icon -->
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
            <button type="button" class="btn btn-sm btn-outline-secondary edit-sov-btn" title="Edit"><i class="fas fa-edit"></i></button> <!-- Optional: Add icon -->
            <button type="button" class="btn btn-sm btn-outline-danger delete-sov-btn" title="Delete"><i class="fas fa-trash-alt"></i></button> <!-- Optional: Add icon -->
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
        const projectId = {$project['id']}; // Ensure this is an integer
        const sovTableBody = document.getElementById('sov-table-body');
        const addSovItemBtn = document.getElementById('add-sov-item-btn');
        const sovFormRowTemplate = document.getElementById('sov-form-row-template');
        const sovDisplayRowTemplate = document.getElementById('sov-display-row-template');
        const sovTotalValueEl = document.getElementById('sov-total-value');
        const sovContractAmountEl = document.getElementById('sov-contract-amount');
        const sovVarianceCell = document.getElementById('sov-variance-cell');
        const alertContainer = document.getElementById('sov-alert-container');
        // Use the PHP-generated token and input name
        const csrfToken = '{$csrfToken}';
        const csrfInputName = '{$csrfInputName}';

        // --- Utility Functions ---
        function formatCurrency(value) {
            // Handle potential non-numeric input gracefully
            const number = parseFloat(value);
            if (isNaN(number)) {
                return '$0.00'; // Or some other default/error indicator
            }
            return number.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
        }

        function showAlert(message, type = 'danger') {
             // Ensure message is a string
             const msgString = typeof message === 'string' ? message : JSON.stringify(message);
             alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${escapeHtml(msgString)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             </div>`;
        }

        function clearAlert() {
            alertContainer.innerHTML = '';
        }

        // Refactored createDisplayRow using cloneNode
        function createDisplayRow(item) {
            const templateNode = sovDisplayRowTemplate.content.cloneNode(true);
            const tr = templateNode.querySelector('tr');
            tr.dataset.id = item.id;

            // Find elements within the cloned template to populate
            const cells = tr.cells; // More robust than assuming order
            cells[0].textContent = item.item_number || '';
            const descriptionHtml = item.description ? escapeHtml(item.description).replace(/\\n|\n/g, '<br>') : ''; // Handle \n or actual newline
            cells[1].innerHTML = descriptionHtml;
            cells[2].textContent = formatCurrency(item.scheduled_value || 0);

            // Set button actions if needed (already handled by event delegation)

            return tr;
        }

         function createFormRow(item = {}) {
            const templateNode = sovFormRowTemplate.content.cloneNode(true);
            const tr = templateNode.querySelector('tr');
            tr.querySelector('.item-number').value = item.item_number || '';
            // Ensure description is treated as a string
            tr.querySelector('.description').value = (item.description || '').toString();
            tr.querySelector('.scheduled-value').value = parseFloat(item.scheduled_value || 0.00).toFixed(2);
            tr.querySelector('.sov-item-id').value = item.id || '';
            return tr;
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            // Ensure input is a string before replacing
            return unsafe
                 .toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        function updateTotals(totalValue, contractAmount) {
            const totalValNum = parseFloat(totalValue);
            const contractAmtNum = parseFloat(contractAmount);

            sovTotalValueEl.textContent = formatCurrency(totalValNum);
            sovContractAmountEl.textContent = formatCurrency(contractAmtNum);

            if (isNaN(totalValNum) || isNaN(contractAmtNum)) {
                 sovVarianceCell.innerHTML = ''; // Cannot calculate variance
                 return;
            }

            const difference = totalValNum - contractAmtNum;
            sovVarianceCell.innerHTML = ''; // Clear previous

            // Use a small tolerance for floating point comparison
            const tolerance = 0.005;
            if (Math.abs(difference) > tolerance) {
                const badgeClass = difference > 0 ? 'bg-warning text-dark' : 'bg-danger';
                const sign = difference > 0 ? '+' : '';
                sovVarianceCell.innerHTML = `<span class="badge ${badgeClass}">Variance: ${sign}${formatCurrency(difference)}</span>`;
            } else {
                 sovVarianceCell.innerHTML = `<span class="badge bg-success">Balanced</span>`; // Indicate if balanced
            }
        }

        // --- Load Initial SOV Data ---
        async function loadSovItems() {
            sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center p-5"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Loading SOV...</td></tr>';
            try {
                // Ensure projectId is valid before fetching
                if (!projectId || isNaN(projectId)) {
                    throw new Error('Invalid Project ID.');
                }
                const response = await fetch(`/projects/${projectId}/sov`); // Make sure this route exists and returns JSON
                if (!response.ok) {
                     const errorText = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                }
                const data = await response.json();
                sovTableBody.innerHTML = ''; // Clear loading indicator

                if (data && data.success) {
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            sovTableBody.appendChild(createDisplayRow(item));
                        });
                    } else {
                        sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No Schedule of Values items found for this project.</td></tr>';
                    }
                    // Ensure values passed are numbers or can be parsed as numbers
                    updateTotals(data.totalScheduledValue ?? 0, data.contractAmount ?? 0);
                } else {
                     // Use message from server response if available
                     showAlert(data.message || 'Failed to load SOV items. Response format might be incorrect.');
                     sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading SOV.</td></tr>';
                }
            } catch (error) {
                console.error('Error loading SOV:', error);
                showAlert(`Error loading SOV items: ${error.message}. Please check the console and network tab.`);
                sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading SOV.</td></tr>';
            }
        }

        // --- Event Handlers ---

        // Add New Item Button
        addSovItemBtn.addEventListener('click', () => {
            clearAlert(); // Clear previous alerts
            // Check if a form row already exists
            if (sovTableBody.querySelector('.sov-form-row')) {
                showAlert('Please save or cancel the current item first.', 'warning');
                // Optionally focus the existing form row
                const existingForm = sovTableBody.querySelector('.sov-form-row .item-number');
                if (existingForm) existingForm.focus();
                return;
            }
            const newRow = createFormRow();
            sovTableBody.appendChild(newRow);
            newRow.querySelector('.item-number').focus();
            addSovItemBtn.disabled = true; // Disable add button while editing/adding
        });

        // Common Save/Update Logic
        async function handleSave(buttonElement) {
             clearAlert();
             const row = buttonElement.closest('tr');
             const itemId = row.querySelector('.sov-item-id').value;
             const isNew = !itemId;
             const url = isNew ? '/sov/store' : `/sov/update/${itemId}`;
             // Use POST for store, consider PUT for update if backend supports it via _method
             const method = 'POST';

             const formData = new FormData();
             formData.append(csrfInputName, csrfToken);
             formData.append('project_id', projectId);
             formData.append('item_number', row.querySelector('.item-number').value.trim());
             formData.append('description', row.querySelector('.description').value.trim());
             formData.append('scheduled_value', row.querySelector('.scheduled-value').value.trim());

             // Add _method for update if your backend expects it
             // if (!isNew) {
             //     formData.append('_method', 'PUT');
             // }

             // Basic client-side validation
             if (!formData.get('item_number') || !formData.get('description') || formData.get('scheduled_value') === '') {
                 showAlert('Item Number, Description, and Scheduled Value are required.', 'warning');
                 return;
             }
             if (isNaN(parseFloat(formData.get('scheduled_value')))) {
                 showAlert('Scheduled Value must be a valid number.', 'warning');
                 return;
             }


             buttonElement.disabled = true; // Disable button during request
             buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

             try {
                 const response = await fetch(url, { method: method, body: formData });
                 // Check if response is JSON, handle non-JSON responses
                 const contentType = response.headers.get("content-type");
                 if (!response.ok || !contentType || !contentType.includes("application/json")) {
                     const errorText = await response.text();
                     throw new Error(`Server error: ${response.status}. Response: ${errorText}`);
                 }

                 const data = await response.json();

                 if (data && data.success) {
                     showAlert(data.message || 'Saved successfully.', 'success');
                     const newDisplayRow = createDisplayRow(data.item);

                     // Find the correct row to replace (could be the form row or an existing display row)
                     let rowToReplace = row; // Assume replacing the form row initially
                     if (!isNew) {
                         // If editing, find the original display row by data-id to replace that instead
                         const originalDisplayRow = sovTableBody.querySelector(`tr[data-id="${itemId}"]`);
                         if (originalDisplayRow) {
                             rowToReplace = originalDisplayRow;
                         }
                         // If originalDisplayRow not found, replacing the current form row (row) is the fallback
                     }
                     rowToReplace.replaceWith(newDisplayRow);

                     updateTotals(data.totalScheduledValue ?? 0, data.contractAmount ?? 0);
                     addSovItemBtn.disabled = false; // Re-enable add button
                 } else {
                     showAlert(data.message || 'Failed to save item. Server response indicates failure.');
                     buttonElement.disabled = false;
                     buttonElement.innerHTML = '<i class="fas fa-save"></i>'; // Restore icon
                 }
             } catch (error) {
                 console.error('Error saving SOV item:', error);
                 showAlert(`Error saving item: ${error.message}. Please check the console.`);
                 buttonElement.disabled = false;
                 buttonElement.innerHTML = '<i class="fas fa-save"></i>'; // Restore icon
             }
        }

        // Save Button (Event Delegation)
        sovTableBody.addEventListener('click', function(e) {
            const saveButton = e.target.closest('.save-sov-btn'); // Find closest save button
            if (saveButton) {
                handleSave(saveButton);
            }
        });

        // Cancel Button (Event Delegation)
        sovTableBody.addEventListener('click', function(e) {
            const cancelButton = e.target.closest('.cancel-sov-btn');
            if (cancelButton) {
                clearAlert();
                const row = cancelButton.closest('tr');
                const itemId = row.querySelector('.sov-item-id').value;
                if (itemId) { // Editing existing item, revert to display row
                     // Need to refetch or have original data stored to revert accurately
                     // Simple approach: reload all items
                     loadSovItems(); // Reload to ensure consistency after cancel
                } else { // Adding new item, just remove the form row
                    row.remove();
                }
                addSovItemBtn.disabled = false; // Re-enable add button
            }
        });

        // Edit Button (Event Delegation)
        sovTableBody.addEventListener('click', function(e) {
             const editButton = e.target.closest('.edit-sov-btn');
             if (editButton) {
                 clearAlert();
                 // Check if another form row is already open
                 if (sovTableBody.querySelector('.sov-form-row')) {
                     showAlert('Please save or cancel the current item first.', 'warning');
                     return;
                 }
                 const row = editButton.closest('tr');
                 const itemId = row.dataset.id;

                 // Extract data from the current display row
                 const itemData = {
                     id: itemId,
                     item_number: row.cells[0].textContent.trim(),
                     // Convert <br> back to newline for textarea
                     description: row.cells[1].innerHTML.replace(/<br\s*\/?>/gi, "\n").trim(),
                     // Remove formatting before parsing
                     scheduled_value: row.cells[2].textContent.replace(/[$,]/g, '')
                 };

                 const formRow = createFormRow(itemData);
                 row.replaceWith(formRow); // Replace display row with form row
                 formRow.querySelector('.item-number').focus();
                 addSovItemBtn.disabled = true; // Disable add button while editing
             }
        });

        // Delete Button (Event Delegation)
        sovTableBody.addEventListener('click', async function(e) {
             const deleteButton = e.target.closest('.delete-sov-btn');
             if (deleteButton) {
                 clearAlert();
                 if (!confirm('Are you sure you want to delete this SOV item? This cannot be undone.')) {
                     return;
                 }

                 const row = deleteButton.closest('tr');
                 const itemId = row.dataset.id;
                 const url = `/sov/delete/${itemId}`;
                 // Use POST, add _method if backend expects DELETE simulation
                 const method = 'POST';

                 const formData = new FormData();
                 formData.append(csrfInputName, csrfToken);
                 // formData.append('_method', 'DELETE'); // If simulating DELETE

                 deleteButton.disabled = true; // Disable button during request
                 const originalIcon = deleteButton.innerHTML; // Store original icon
                 deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';


                 try {
                     const response = await fetch(url, { method: method, body: formData });
                     // Check response type
                     const contentType = response.headers.get("content-type");
                     if (!response.ok || !contentType || !contentType.includes("application/json")) {
                         const errorText = await response.text();
                         throw new Error(`Server error: ${response.status}. Response: ${errorText}`);
                     }

                     const data = await response.json();

                     if (data && data.success) {
                         showAlert(data.message || 'Item deleted successfully.', 'success');
                         row.remove(); // Remove row from table
                         updateTotals(data.totalScheduledValue ?? 0, data.contractAmount ?? 0);
                         // Check if table is now empty
                         if (sovTableBody.rows.length === 0) {
                             sovTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No Schedule of Values items found for this project.</td></tr>';
                         }
                     } else {
                         showAlert(data.message || 'Failed to delete item.');
                         deleteButton.disabled = false;
                         deleteButton.innerHTML = originalIcon; // Restore icon
                     }
                 } catch (error) {
                     console.error('Error deleting SOV item:', error);
                     showAlert(`Error deleting item: ${error.message}. Please check the console.`);
                     deleteButton.disabled = false;
                     deleteButton.innerHTML = originalIcon; // Restore icon
                 }
             }
        });

        // --- Initial Load ---
        loadSovItems();
    });
</script>
JS;

echo $view->includePartial('partials/footer.php', [
    'config' => $config,
    'pageScripts' => $pageScripts // Pass the JS to the footer
]);
?>