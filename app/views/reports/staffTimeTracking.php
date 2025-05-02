<?php
echo $view->includePartial('partials/header.php', [
    'pageTitle' => $pageTitle ?? 'Staff Time Tracking Report',
    'config' => $config,
    'currentUser' => $currentUser ?? null,
    'activeNav' => $activeNav ?? 'report-time-tracking' // Example active nav key
]);

// Assume $reportData is an array of time entry objects/arrays
// You might want filtering options (date range, staff member, project) handled in the controller
$reportData = $reportData ?? [];
?>

<div class="container mt-4">
    <h1>Staff Time Tracking Report</h1>

    <?php if (!empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type'] ?? 'info') ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <!-- Add Filter Form Here (Optional) -->
 
    <form method="GET" action="/reports/staff-time" class="mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="staff_id" class="form-label">Staff Member</label>
                <select id="staff_id" name="staff_id" class="form-select">
                    <option value="">All Staff</option>
                    <?php // Populate with staff members ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="project_id" class="form-label">Project</label>
                 <select id="project_id" name="project_id" class="form-select">
                    <option value="">All Projects</option>
                     <?php // Populate with projects ?>
                </select>
            </div>
             <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control">
            </div>
             <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>


    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Staff Member</th>
                <th>Project</th>
                <th>Task / Description</th>
                <th>Hours Logged</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandTotalHours = 0;
            if (!empty($reportData)): ?>
                <?php foreach ($reportData as $entry):
                    $grandTotalHours += $entry['hours'] ?? 0;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['date'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($entry['staff_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($entry['project_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($entry['description'] ?? '') ?></td>
                        <td class="text-end"><?= number_format($entry['hours'] ?? 0, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No time tracking data found for the selected criteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
         <?php if (!empty($reportData)): ?>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total Hours:</th>
                <th class="text-end fw-bold"><?= number_format($grandTotalHours, 2) ?></th>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php
echo $view->includePartial('partials/footer.php', [
     'config' => $config
]);
?>