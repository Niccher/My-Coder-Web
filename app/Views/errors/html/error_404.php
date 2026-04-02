<?php
$title = '404 - Page Not Found';
include __DIR__ . '/_header.php';
?>

<div class="error-code">404</div>
<h3 class="fw-bold text-main">Page Not Found</h3>

<p class="text-muted mt-3 mb-4 px-md-4">
    Sorry! Cannot seem to find the page you were looking for. It might have been removed, had its name changed, or is temporarily unavailable.
</p>

<?php if (! empty($message) && $message !== '(null)') : ?>
    <div class="alert alert-info bg-info bg-opacity-10 border-0 text-info rounded-3 text-start small mb-0">
        <i class="fa-solid fa-magnifying-glass me-2"></i> <?= esc($message) ?>
    </div>
<?php endif ?>

<a href="<?= base_url() ?>" class="error-btn">
    <i class="fa-solid fa-house me-2"></i> Return to Main Application
</a>

<?php include __DIR__ . '/_footer.php'; ?>
