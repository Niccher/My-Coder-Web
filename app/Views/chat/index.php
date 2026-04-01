<?= $this->extend('chat/layout') ?>

<?= $this->section('content') ?>

    <div id="chat-container">
        <!-- Welcome Message -->
        <div class="text-center my-5 animate__animated animate__fadeIn">
            <h1 class="display-4 fw-bold mb-4" style="background: linear-gradient(to right, #4285f4, #d96570); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Hello, User</h1>
            <p class="lead text-muted">How can I help you today?</p>
        </div>

        <!-- Messages will be appended here via jQuery -->
    </div>

    <!-- Input Wrapper -->
    <div id="input-wrapper">
        <div id="input-container">
            <button class="action-btn">
                <i class="fa-solid fa-paperclip"></i>
            </button>
            <textarea id="chat-input" placeholder="Type a message..." rows="1"></textarea>
            <button id="send-btn" class="action-btn text-primary">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>

<?= $this->endSection() ?>
