<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'myCoder chat | Authentication' ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom CSS (Reusing existing theme colors) -->
    <link rel="stylesheet" href="<?= base_url('css/chat.css') ?>">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-dark);
            padding: 2rem;
        }
        .auth-card {
            width: 100%;
            max-width: 550px;
            background-color: var(--sidebar-bg);
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeIn 0.4s ease-out;
        }
        .auth-header {
            padding: 2rem 2rem 1rem;
            text-align: center;
        }
        .auth-body {
            padding: 1rem 2rem 2rem;
        }
        .auth-input {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .auth-input:focus {
            background-color: var(--input-bg);
            color: var(--text-main);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(26, 115, 232, 0.25);
            outline: none;
        }
        .auth-btn {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            width: 100%;
            transition: all 0.2s;
        }
        .auth-btn:hover {
            opacity: 0.9;
        }
        .auth-link {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .auth-link:hover {
            text-decoration: underline;
        }
        /* Sync body background automatically */
        script#theme-init { display: none; }
    </style>
</head>
<body data-theme="solarized">
    <!-- Auto-apply user theme if previously set in localStorage -->
    <script id="theme-init">
        (function() {
            var theme = localStorage.getItem('chat-theme');
            if (theme) document.body.setAttribute('data-theme', theme);
        })();
    </script>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h3 class="fw-bold m-0" style="background: linear-gradient(to right, #4285f4, #d96570); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">AI Engine</h3>
                <p class="text-muted mt-2 mb-0">Sign in to your account to continue</p>
            </div>
            <div class="auth-body">
                <?= $this->renderSection('main') ?>
            </div>
        </div>
    </div>
</body>
</html>
