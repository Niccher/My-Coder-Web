<?= $this->extend('auth/layout') ?>

<?= $this->section('main') ?>

<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
        <i class="fa-solid fa-rotate-right fs-4"></i>
    </div>
    <h5 class="fw-bold">Set New Password</h5>
    <p class="text-muted small px-3">Your new password must be different from previously used passwords.</p>
</div>

<form action="<?= base_url('reset') ?>" method="post">
    <!-- CSRF Placeholder -->
    <?= csrf_field() ?>
    
    <input type="hidden" name="token" value="<?= isset($token) ? $token : '' ?>">
    
    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold text-muted mb-1">New Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password" class="form-control auth-input border-start-0 ps-0" id="password" name="password" placeholder="Must be at least 8 characters" required autocomplete="new-password">
        </div>
    </div>
    
    <div class="mb-4">
        <label for="password_confirm" class="form-label small fw-semibold text-muted mb-1">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);">
                <i class="fa-solid fa-check"></i>
            </span>
            <input type="password" class="form-control auth-input border-start-0 ps-0" id="password_confirm" name="password_confirm" placeholder="Repeat your new password" required autocomplete="new-password">
        </div>
    </div>
    
    <button type="submit" class="auth-btn mb-4">Reset Password</button>
    
    <div class="text-center">
        <a href="<?= base_url('login') ?>" class="auth-link text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Back to sign in</a>
    </div>
</form>

<?= $this->endSection() ?>
