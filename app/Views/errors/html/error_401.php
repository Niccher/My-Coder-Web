<?php
$title = '401 - Unauthorized';
include __DIR__ . '/_header.php';
?>

<div class="error-code">401</div>
<h3 class="fw-bold text-main">Unauthorized</h3>

<p class="text-muted mt-3 mb-4 px-md-4">
    You must authenticate yourself to get the requested response. Please sign in and try again.
</p>

<?php if (! empty($message) && $message !== '(null)') : ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 text-start small mb-0">
        <i class="fa-solid fa-lock me-2"></i> <?= esc($message) ?>
    </div>
<?php endif ?>

<a href="<?= base_url('login') ?>" class="error-btn">
    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Sign In to Continue
</a>

<?php include __DIR__ . '/_footer.php'; ?>
