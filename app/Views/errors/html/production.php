<?php
$title = '500 - Server Error';
include __DIR__ . '/_header.php';
?>

<div class="error-code">500</div>
<h3 class="fw-bold text-main">Internal Server Error</h3>

<p class="text-muted mt-3 mb-4 px-md-4">
    Whoops! We seem to have hit a snag. Please try again later. If the problem persists, please contact support.
</p>

<a href="<?= base_url() ?>" class="error-btn">
    <i class="fa-solid fa-rotate-right me-2"></i> Try Again
</a>

<?php include __DIR__ . '/_footer.php'; ?>
