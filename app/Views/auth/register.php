<?= $this->extend('auth/layout')?>

<?= $this->section('main')?>

<form action="<?= url_to('register')?>" method="post">
    <!-- CSRF Placeholder -->
    <?= csrf_field()?>

    <?php if (session('error') !== null): ?>
    <div class="alert alert-danger" role="alert">
        <?= session('error')?>
    </div>
    <?php
elseif (session('errors') !== null): ?>
    <div class="alert alert-danger" role="alert">
        <?php if (is_array(session('errors'))): ?>
        <?php foreach (session('errors') as $error): ?>
        <?= $error?>
        <br>
        <?php
        endforeach ?>
        <?php
    else: ?>
        <?= session('errors')?>
        <?php
    endif ?>
    </div>
    <?php
endif ?>

    <div class="mb-3">
        <label for="username" class="form-label small fw-semibold text-muted mb-1">Username</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"
                style="border-color: var(--border-color);">
                <i class="fa-regular fa-user"></i>
            </span>
            <input type="text" class="form-control auth-input border-start-0 ps-0" id="username" name="username"
                placeholder="domino_ai" required autocomplete="username">
        </div>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-muted mb-1">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"
                style="border-color: var(--border-color);">
                <i class="fa-solid fa-at"></i>
            </span>
            <input type="email" class="form-control auth-input border-start-0 ps-0" id="email" name="email"
                placeholder="name@example.com" required autocomplete="email">
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold text-muted mb-1">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"
                style="border-color: var(--border-color);">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password" class="form-control auth-input border-start-0 ps-0" id="password" name="password"
                placeholder="Create a strong password" required autocomplete="new-password">
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label small fw-semibold text-muted mb-1">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"
                style="border-color: var(--border-color);">
                <i class="fa-solid fa-shield-halved"></i>
            </span>
            <input type="password" class="form-control auth-input border-start-0 ps-0" id="password_confirm"
                name="password_confirm" placeholder="Repeat your password" required autocomplete="new-password">
        </div>
    </div>

    <button type="submit" class="auth-btn mb-4">Create Account</button>

    <div class="text-center">
        <span class="text-muted small">Already have an account? </span>
        <a href="<?= base_url('login')?>" class="auth-link">Sign In</a>
    </div>
</form>

<?= $this->endSection()?>