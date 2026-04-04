<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?? 'myCoder Chat'?>
    </title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png')?>">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Animate.css for smooth transitions -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Prism.js for code highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/chat.css')?>">
</head>

<body>

    <div class="d-flex" id="app-layout">
        <!-- Sidebar Backdrop -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <!-- Sidebar -->
        <?= $this->include('chat/sidebar')?>

        <!-- Main Content -->
        <main id="main-content" class="position-relative">
            <!-- Mobile Sidebar Toggle -->
            <button class="btn btn-link d-md-none position-absolute top-0 start-0 mt-3 ms-3 text-themed" id="mobile-sidebar-toggle" style="z-index: 100;">
                <i class="fa-solid fa-bars fs-4"></i>
            </button>

            <?= $this->renderSection('content')?>
        </main>
    </div>

    <!-- jQuery (needed for Bootstrap optionally, and our custom script) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Chat JS -->
    <!-- Prism.js & Marked.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.5/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>

    <script src="<?= base_url('js/chat.js')?>?v=<?= time()?>"></script>

    <!-- Custom Scripts from Views -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>