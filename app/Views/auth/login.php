<?= $this->extend('auth/layout') ?>

<?= $this->section('main') ?>

<form action="<?= url_to('login') ?>" method="post">
    <!-- CSRF Placeholder -->
    <?= csrf_field() ?>
    
    <?php if (session('error') !== null) : ?>
        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
    <?php elseif (session('errors') !== null) : ?>
        <div class="alert alert-danger" role="alert">
            <?php if (is_array(session('errors'))) : ?>
                <?php foreach (session('errors') as $error) : ?>
                    <?= $error ?>
                    <br>
                <?php endforeach ?>
            <?php else : ?>
                <?= session('errors') ?>
            <?php endif ?>
        </div>
    <?php endif ?>
    
    <?php if (session('message') !== null) : ?>
        <div class="alert alert-success" role="alert"><?= session('message') ?></div>
    <?php endif ?>
    
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-muted">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);">
                <i class="fa-regular fa-envelope"></i>
            </span>
            <input type="email" class="form-control auth-input border-start-0 ps-0" id="email" name="email" placeholder="name@example.com" required autocomplete="email">
        </div>
    </div>
    
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold text-muted mb-0">Password</label>
            <a href="<?= url_to('magic-link') ?>" class="auth-link small">Forgot Password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password" class="form-control auth-input border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        </div>
    </div>
    
    <div class="form-check mb-4">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label small text-muted" for="remember">Remember me for 30 days</label>
    </div>
    
    <button type="submit" class="auth-btn mb-4">Sign In</button>
    
    <div class="text-center">
        <span class="text-muted small">Don't have an account? </span>
        <a href="<?= base_url('register') ?>" class="auth-link">Sign up</a>
    </div>
</form>

<?= $this->endSection() ?>
