<?php
$title = '400 - Bad Request';
include __DIR__ . '/_header.php';
?>

<div class="error-code">400</div>
<h3 class="fw-bold text-main">Bad Request</h3>

<p class="text-muted mt-3 mb-4 px-md-4">
    The server could not understand the request due to invalid syntax or parameters. Please check your request and try again.
</p>

<?php if (! empty($message) && $message !== '(null)') : ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 text-start small mb-0">
        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= esc($message) ?>
    </div>
<?php endif ?>

<a href="<?= base_url() ?>" class="error-btn">
    <i class="fa-solid fa-house me-2"></i> Return to Main Application
</a>

<?php include __DIR__ . '/_footer.php'; ?>
