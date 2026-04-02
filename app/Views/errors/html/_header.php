<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Error' ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #1e1e2d;
            --sidebar-bg: #27293d;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --border-color: #3f3f5a;
            --accent: #4285f4;
            --input-bg: rgba(255,255,255,0.05);
        }
        [data-theme='solarized'] {
            --bg-dark: #fdf6e3;
            --sidebar-bg: #eee8d5;
            --text-main: #657b83;
            --text-muted: #93a1a1;
            --border-color: #d1cda8;
            --input-bg: rgba(0,0,0,0.03);
        }
        [data-theme='white'] {
            --bg-dark: #f8f9fa;
            --sidebar-bg: #ffffff;
            --text-main: #212529;
            --text-muted: #6c757d;
            --border-color: #e9ecef;
            --input-bg: #ffffff;
        }
        body { font-family: 'Inter', sans-serif; }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-dark);
            padding: 2rem;
            text-align: center;
        }
        .error-card {
            width: 100%;
            max-width: 550px;
            background-color: var(--sidebar-bg);
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--accent), #9b72cb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .error-btn {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
        }
        .error-btn:hover { opacity: 0.9; color: white; }
        script#theme-init { display: none; }
    </style>
</head>
<body data-theme="solarized">
    <script id="theme-init">
        (function() {
            var theme = localStorage.getItem('chat-theme');
            if (theme) document.body.setAttribute('data-theme', theme);
        })();
    </script>
    <div class="error-container">
        <div class="error-card">
