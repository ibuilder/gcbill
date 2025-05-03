<div class="container mt-3">
    <?php if (isset($flashMessages) && !empty($flashMessages)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessages['type']) ?>" role="alert">
            <?= htmlspecialchars($flashMessages['message']) ?>
        </div>
    <?php endif; ?>
</div>