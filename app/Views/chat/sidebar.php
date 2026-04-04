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

    <div class="history-list flex-grow-1" id="sidebar-history-list" style="overflow-y: auto;">
        <div class="section-header">Recent</div>
        <!-- Dynamically populated via JS -->
    </div>
    
    <div class="p-3 border-top border-secondary-subtle">
        <button class="btn btn-outline-secondary w-100 btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#allHistoryModal">
            <i class="fa-solid fa-clock-rotate-left me-2"></i> Show All History
        </button>
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
            <div class="user-profile-wrapper mt-1">
                <div class="dropdown">
                    <div class="history-item dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-circle-user me-2"></i> <span class="flex-grow-1 text-truncate"><?= esc(auth()->user()->username ?? 'Domino AI') ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="width: 240px;">
                        <li><h6 class="dropdown-header px-3 text-truncate"><?= esc(auth()->user()->email ?? 'Account') ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 export-chat-btn" href="#" data-format="json"><i class="fa-solid fa-file-code me-2"></i> Export as JSON</a></li>
                        <li><a class="dropdown-item py-2 export-chat-btn" href="#" data-format="xml"><i class="fa-solid fa-code me-2"></i> Export as XML</a></li>
                        <li><a class="dropdown-item py-2 export-chat-btn" href="#" data-format="yaml"><i class="fa-solid fa-file-lines me-2"></i> Export as YAML</a></li>
                        <li><a class="dropdown-item py-2 export-chat-btn" href="#" data-format="pdf"><i class="fa-solid fa-file-pdf me-2"></i> Export as PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= url_to('logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
