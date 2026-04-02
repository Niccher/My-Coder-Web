<?= $this->extend('auth/layout') ?>

<?= $this->section('main') ?>

<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
        <i class="fa-solid fa-key fs-4"></i>
    </div>
    <h5 class="fw-bold">Forgot Password</h5>
    <p class="text-muted small px-3">No worries, we'll send you reset instructions.</p>
</div>

<form action="<?= base_url('forgot') ?>" method="post">
    <!-- CSRF Placeholder -->
    <?= csrf_field() ?>
    
    <div class="mb-4">
        <label for="email" class="form-label small fw-semibold text-muted">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);">
                <i class="fa-regular fa-envelope"></i>
            </span>
            <input type="email" class="form-control auth-input border-start-0 ps-0" id="email" name="email" placeholder="name@example.com" required>
        </div>
    </div>
    
    <button type="submit" class="auth-btn mb-4">Reset Password</button>
    
    <div class="text-center">
        <a href="<?= base_url('login') ?>" class="auth-link text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Back to sign in</a>
    </div>
</form>

<?= $this->endSection() ?>
