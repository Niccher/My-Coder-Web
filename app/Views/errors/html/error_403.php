<?php
$title = '403 - Forbidden';
include __DIR__ . '/_header.php';
?>

<div class="error-code">403</div>
<h3 class="fw-bold text-main">Forbidden</h3>

<p class="text-muted mt-3 mb-4 px-md-4">
    You do not have permission to access the requested resource. The server understood the request but refuses to authorize it.
</p>

<?php if (! empty($message) && $message !== '(null)') : ?>
    <div class="alert alert-warning bg-warning bg-opacity-10 border-0 text-warning rounded-3 text-start small mb-0">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= esc($message) ?>
    </div>
<?php endif ?>

<a href="<?= base_url() ?>" class="error-btn">
    <i class="fa-solid fa-house me-2"></i> Return to Main Application
</a>

<?php include __DIR__ . '/_footer.php'; ?>
