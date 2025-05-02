<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\projects\_form.html -->
<?php
// Expects $project, $owners, $formAction variables
use App\Helpers\SecurityHelper;
$isEdit = !empty($project['id']);
?>
<form action="<?= htmlspecialchars($formAction) ?>" method="POST">
    <?= SecurityHelper::csrfField(); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="project_number" class="form-label">Project Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="project_number" name="project_number" value="<?= htmlspecialchars($project['project_number'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="project_name" name="project_name" value="<?= htmlspecialchars($project['project_name'] ?? '') ?>" required>
        </div>

        <div class="col-12">
            <label for="owner_id" class="form-label">Owner</label>
            <select class="form-select" id="owner_id" name="owner_id">
                <option value="">-- Select Owner --</option>
                <?php foreach ($owners as $owner): ?>
                    <option value="<?= $owner['id'] ?>" <?= (isset($project['owner_id']) && $project['owner_id'] == $owner['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($owner['owner_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label for="address_line1" class="form-label">Address Line 1</label>
            <input type="text" class="form-control" id="address_line1" name="address_line1" value="<?= htmlspecialchars($project['address_line1'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label for="address_line2" class="form-label">Address Line 2</label>
            <input type="text" class="form-control" id="address_line2" name="address_line2" value="<?= htmlspecialchars($project['address_line2'] ?? '') ?>">
        </div>
        <div class="col-md-5">
            <label for="city" class="form-label">City</label>
            <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($project['city'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="state" class="form-label">State</label>
            <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($project['state'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label for="zip_code" class="form-label">Zip Code</label>
            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?= htmlspecialchars($project['zip_code'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="completion_date" class="form-label">Est. Completion Date</label>
            <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?= htmlspecialchars($project['completion_date'] ?? '') ?>">
        </div>

         <div class="col-md-6">
            <label for="contract_amount" class="form-label">Contract Amount</label>
            <input type="number" step="0.01" class="form-control" id="contract_amount" name="contract_amount" value="<?= htmlspecialchars($project['contract_amount'] ?? '') ?>">
        </div>
         <div class="col-md-6">
            <label for="gmp_amount" class="form-label">GMP Amount</label>
            <input type="number" step="0.01" class="form-control" id="gmp_amount" name="gmp_amount" value="<?= htmlspecialchars($project['gmp_amount'] ?? '') ?>">
        </div>

         <div class="col-md-4">
            <label for="gc_fee_percentage" class="form-label">GC Fee (%)</label>
            <input type="number" step="0.01" class="form-control" id="gc_fee_percentage" name="gc_fee_percentage" value="<?= htmlspecialchars($project['gc_fee_percentage'] ?? '') ?>">
        </div>
         <div class="col-md-4">
            <label for="retainage_percentage" class="form-label">Retainage (%)</label>
            <input type="number" step="0.01" class="form-control" id="retainage_percentage" name="retainage_percentage" value="<?= htmlspecialchars($project['retainage_percentage'] ?? '10.00') ?>">
        </div>
         <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <?php $statuses = ['planning', 'active', 'completed', 'on_hold', 'cancelled']; ?>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status ?>" <?= (isset($project['status']) && $project['status'] == $status) ? 'selected' : '' ?>>
                        <?= ucfirst($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <hr class="my-4">

    <button class="btn btn-primary btn-lg" type="submit"><?= $isEdit ? 'Update Project' : 'Create Project' ?></button>
    <a href="/projects" class="btn btn-secondary btn-lg">Cancel</a>
</form>