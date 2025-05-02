<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\staff\_form.html -->
<?php
use App\Helpers\SecurityHelper;
$isEdit = !empty($staff['id']);
?>
<form action="<?= htmlspecialchars($formAction) ?>" method="POST">
    <?= SecurityHelper::csrfField(); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($staff['first_name'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($staff['last_name'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($staff['email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($staff['phone'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label for="position_id" class="form-label">Position</label>
            <select class="form-select" id="position_id" name="position_id">
                <option value="">-- Select Position --</option>
                <?php foreach ($positions as $pos): ?>
                    <option value="<?= $pos['id'] ?>" <?= (isset($staff['position_id']) && $staff['position_id'] == $pos['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pos['position_title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="hire_date" class="form-label">Hire Date</label>
            <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?= htmlspecialchars($staff['hire_date'] ?? '') ?>">
        </div>

         <div class="col-md-6">
            <label for="user_id" class="form-label">Link to System User (Optional)</label>
            <select class="form-select" id="user_id" name="user_id">
                <option value="">-- No Linked User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= (isset($staff['user_id']) && $staff['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
             <div class="form-text">Linking allows this staff member to log in with the selected user account.</div>
        </div>

         <div class="col-md-6 align-self-center">
             <div class="form-check form-switch mt-3">
               <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= (!isset($staff['is_active']) || $staff['is_active'] == 1) ? 'checked' : '' ?>>
               <label class="form-check-label" for="is_active">Is Active</label>
             </div>
         </div>
    </div>

    <hr class="my-4">
    <button class="btn btn-primary btn-lg" type="submit"><?= $isEdit ? 'Update Staff' : 'Create Staff' ?></button>
    <a href="/staff" class="btn btn-secondary btn-lg">Cancel</a>
</form>