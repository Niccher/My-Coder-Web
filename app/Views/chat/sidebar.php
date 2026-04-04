<div id="sidebar">
    <div class="p-3 d-flex align-items-center gap-3">
        <img src="<?= base_url('favicon.png') ?>" alt="Logo" class="rounded-3 shadow-sm" style="width: 40px; height: 40px;">
        <span class="fs-4 fw-bold tracking-tight text-main" style="background: linear-gradient(to right, #4285f4, #d96570); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">myCoder chat</span>
    </div>
    <div class="p-3">
        <button class="new-chat-btn w-100">
            <i class="fa-solid fa-plus me-2"></i> New Chat
        </button>
    </div>

    <div class="history-list flex-grow-1" id="sidebar-history-list">
        <div class="section-header">Recent</div>
        <!-- Dynamically populated via JS -->
    </div>

    <div class="sidebar-footer border-top border-secondary-subtle">
        <div class="dropdown p-2">
            <div class="history-item dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-palette me-2"></i> Theme
            </div>
            <ul class="dropdown-menu dropdown-menu-dark shadow theme-menu" style="width: 240px;">
                <li><a class="dropdown-item py-2" href="#" data-theme="dark"><i class="fa-solid fa-moon me-2"></i> Cool Dark</a></li>
                <li><a class="dropdown-item py-2" href="#" data-theme="solarized"><i class="fa-solid fa-sun me-2"></i> Solarized</a></li>
                <li><a class="dropdown-item py-2" href="#" data-theme="white"><i class="fa-solid fa-lightbulb me-2"></i> White</a></li>
            </ul>
        </div>
        <div class="p-2 pt-0">
            <div class="history-item" id="openSettings">
                <i class="fa-solid fa-gear me-2"></i> Settings
            </div>
            <div class="history-item">
                <i class="fa-solid fa-circle-user me-2"></i> Domino AI
            </div>
        </div>
    </div>
</div>
