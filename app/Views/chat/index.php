<?= $this->extend('chat/layout')?>

<?= $this->section('content')?>

<!-- User Profile Dropdown -->
<div class="user-profile-wrapper position-absolute top-0 end-0 mt-3 me-4" style="z-index: 100;">
    <div class="dropdown">
        <button class="btn btn-link p-1 text-decoration-none d-flex align-items-center gap-2 profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="avatar-sm">D</div>
            <span class="fw-medium text-themed d-none d-md-block">Domino AI</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-secondary-subtle">
            <li><h6 class="dropdown-header">Account</h6></li>
            <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><i class="fa-solid fa-user-gear opacity-50"></i> View Profile</a></li>
            <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" id="openSettingsDropdown"><i class="fa-solid fa-gear opacity-50"></i> Settings</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="/logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>
</div>

<div id="chat-container" class="d-flex flex-column">
    <div id="greeting-box" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center pb-5 animate__animated animate__fadeIn">
        <h1 class="display-4 fw-bold mb-3"
            style="background: linear-gradient(to right, #4285f4, #d96570); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Hello, User</h1>
        <p class="lead text-muted">How can I help you today?</p>
    </div>
</div>

<div id="input-wrapper" class="file-upload-zone">
    <div id="file-preview-area"></div>
    <div id="input-container">
        <input type="file" id="file-upload-input" multiple style="display: none;">
        <button id="upload-btn" class="action-btn"><i class="fa-solid fa-paperclip"></i></button>
        <textarea id="chat-input" placeholder="Type a message..." rows="1"></textarea>
        <button id="send-btn" class="action-btn text-primary"><i class="fa-solid fa-paper-plane"></i></button>
    </div>

    <!-- Active Models Container -->
    <div class="active-models-container mt-3 d-flex flex-column align-items-center gap-2">
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#modelSelectionModal"><i class="fa-solid fa-microchip me-1"></i>
                Gemini 1.5</button>
            <button class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#modelSelectionModal"><i class="fa-solid fa-microchip me-1"></i>
                DeepSeek R1</button>
            <button class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#modelSelectionModal"><i class="fa-solid fa-microchip me-1"></i>
                Grok-2</button>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#modelSelectionModal" style="font-size: 0.75rem;">
                <i class="fa-solid fa-brain me-1 text-danger"></i> Evaluated by Master Model (GPT-4o)
            </button>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content settings-modal border-0 shadow-lg">

            <!-- Header -->
            <div class="modal-header px-4 py-3 border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="settingsModalLabel">
                    <i class="fa-solid fa-gear me-2 opacity-50"></i>Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body: Sidebar + Content -->
            <div class="modal-body p-0">
                <div class="d-flex settings-body">

                    <!-- Sidebar Nav -->
                    <div class="nav nav-pills flex-column settings-nav border-end border-secondary-subtle p-3" role="tablist" aria-orientation="vertical" style="width: 200px; flex-shrink: 0;">
                        <div class="nav-section-label">Account</div>
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#sp-profile" type="button" role="tab">
                            <i class="fa-solid fa-circle-user"></i> Profile
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sp-notifications" type="button" role="tab">
                            <i class="fa-solid fa-bell"></i> Notifications
                        </button>

                        <div class="nav-section-label mt-3">Interface</div>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sp-appearance" type="button" role="tab">
                            <i class="fa-solid fa-palette"></i> Appearance
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sp-general" type="button" role="tab">
                            <i class="fa-solid fa-sliders"></i> General
                        </button>

                        <div class="nav-section-label mt-3">AI Engine</div>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sp-models" type="button" role="tab">
                            <i class="fa-solid fa-microchip"></i> Models & Tokens
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content settings-content p-4 flex-grow-1">

                        <!-- ── Profile ──────────────────────────── -->
                        <div class="tab-pane fade show active" id="sp-profile">
                            <h6 class="settings-heading">User Profile</h6>
                            <p class="settings-desc">Manage your display name, email, and AI persona.</p>

                            <div class="d-flex align-items-center mb-3">
                                <div class="settings-avatar me-3">D</div>
                                <div>
                                    <div class="fw-semibold">Domino AI</div>
                                    <a href="#" class="small text-primary text-decoration-none">Change avatar</a>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Display Name</label>
                                    <input type="text" class="form-control" value="Domino AI">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Email Address</label>
                                    <input type="email" class="form-control" value="user@example.com">
                                </div>
                            </div>
                            <div>
                                <label class="form-label small fw-semibold mb-1">AI Persona / System Instructions</label>
                                <textarea class="form-control" rows="3" placeholder="e.g. You are a helpful assistant that speaks formally..."></textarea>
                            </div>
                        </div>

                        <!-- ── Notifications ────────────────────── -->
                        <div class="tab-pane fade" id="sp-notifications">
                            <h6 class="settings-heading">Notifications</h6>
                            <p class="settings-desc">Choose how you want to be alerted.</p>

                            <div class="settings-row">
                                <div>
                                    <div class="fw-semibold">Response Complete</div>
                                    <div class="text-muted small">Notify when AI finishes a long response</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                            <div class="settings-row">
                                <div>
                                    <div class="fw-semibold">Error Alerts</div>
                                    <div class="text-muted small">Show alerts when a connection fails</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                            <div class="settings-row border-0">
                                <div>
                                    <div class="fw-semibold">Sound Effects</div>
                                    <div class="text-muted small">Play sound on new messages</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </div>
                        </div>

                        <!-- ── Appearance ────────────────────────── -->
                        <div class="tab-pane fade" id="sp-appearance">
                            <h6 class="settings-heading">Interface Appearance</h6>
                            <p class="settings-desc">Customize theme and typography.</p>

                            <label class="form-label small fw-semibold mb-2">Theme</label>
                            <div class="d-flex gap-2 mb-3 theme-selector-modal">
                                <div class="theme-card" data-theme="dark">
                                    <div class="theme-preview" style="background: linear-gradient(135deg, #131314 50%, #1e1f20 50%);"></div>
                                    <span>Dark</span>
                                </div>
                                <div class="theme-card" data-theme="solarized">
                                    <div class="theme-preview" style="background: linear-gradient(135deg, #fdf6e3 50%, #eee8d5 50%);"></div>
                                    <span>Solarized</span>
                                </div>
                                <div class="theme-card" data-theme="white">
                                    <div class="theme-preview" style="background: linear-gradient(135deg, #ffffff 50%, #f0f0f0 50%); border: 1px solid #ddd;"></div>
                                    <span>Light</span>
                                </div>
                            </div>

                            <label class="form-label small fw-semibold mb-2">Font Size</label>
                            <input type="range" class="form-range" min="14" max="22" step="1" value="16">
                            <div class="d-flex justify-content-between text-muted small mb-3"><span>Small</span><span>Large</span></div>

                            <div class="settings-row border-0">
                                <div>
                                    <div class="fw-semibold">Compact Mode</div>
                                    <div class="text-muted small">Tighter spacing in chat bubbles</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </div>
                        </div>

                        <!-- ── General ───────────────────────────── -->
                        <div class="tab-pane fade" id="sp-general">
                            <h6 class="settings-heading">General</h6>
                            <p class="settings-desc">Configure basic app behavior.</p>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold mb-1">Interface Language</label>
                                <select class="form-select">
                                    <option>English</option>
                                    <option>Swahili</option>
                                    <option>French</option>
                                </select>
                            </div>

                            <div class="settings-row">
                                <div>
                                    <div class="fw-semibold">Auto-scroll</div>
                                    <div class="text-muted small">Scroll to latest message automatically</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                            <div class="settings-row border-0">
                                <div>
                                    <div class="fw-semibold">Send on Enter</div>
                                    <div class="text-muted small">Press Enter to send · Shift+Enter for newline</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                        </div>

                        <!-- ── Models & Tokens ───────────────────── -->
                        <div class="tab-pane fade" id="sp-models">
                            <h6 class="settings-heading">Simultaneous Models</h6>
                            <p class="settings-desc">Configure up to 3 AI models to run side-by-side.</p>

                            <div class="model-card mb-3">
                                <div class="model-badge bg-primary">1</div>
                                <div class="flex-grow-1">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <select class="form-select form-select-sm">
                                                <option selected>Gemini 1.5 Pro</option>
                                                <option>GPT-4o</option>
                                                <option>Claude 3.5 Sonnet</option>
                                                <option>DeepSeek R1</option>
                                                <option>Grok-2</option>
                                                <option>Llama 3.1</option>
                                                <option>Mistral Large</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7">
                                            <input type="password" class="form-control form-control-sm" placeholder="API Key / Token">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="model-card mb-3">
                                <div class="model-badge bg-success">2</div>
                                <div class="flex-grow-1">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <select class="form-select form-select-sm">
                                                <option selected>DeepSeek R1</option>
                                                <option>Claude 3.5 Sonnet</option>
                                                <option>GPT-4o mini</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7">
                                            <input type="password" class="form-control form-control-sm" placeholder="API Key / Token">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="model-card mb-3">
                                <div class="model-badge bg-warning text-dark">3</div>
                                <div class="flex-grow-1">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <select class="form-select form-select-sm">
                                                <option selected>Grok-2</option>
                                                <option>Mistral Large</option>
                                                <option>Llama 3.1</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7">
                                            <input type="password" class="form-control form-control-sm" placeholder="API Key / Token">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer px-4 py-3 border-top border-secondary-subtle">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4">
                    <i class="fa-solid fa-check me-1"></i> Save
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Model Selection Modal -->
<div class="modal fade" id="modelSelectionModal" tabindex="-1" aria-labelledby="modelSelectionModalLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content settings-modal border-0 shadow-lg" style="max-height: 85vh;">
            <div class="modal-header px-4 py-3 border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="modelSelectionModalLabel">
                    <i class="fa-solid fa-layer-group me-2 opacity-50"></i>Model Selection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="overflow-y: auto;">
                <p class="text-muted small mb-4">Choose three primary models to query simultaneously, and one master
                    model to evaluate their responses.</p>

                <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-microchip me-2"></i>Primary Models</h6>

                <!-- Primary Model 1 -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Model 1</label>
                    <select class="form-select bg-themed border-secondary-subtle">
                        <option selected="">Gemini 1.5</option>
                        <option>GPT-4o</option>
                        <option>Claude 3.5 Sonnet</option>
                        <option>DeepSeek R1</option>
                        <option>Grok-2</option>
                    </select>
                </div>

                <!-- Primary Model 2 -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Model 2</label>
                    <select class="form-select bg-themed border-secondary-subtle">
                        <option>Gemini 1.5</option>
                        <option>GPT-4o</option>
                        <option>Claude 3.5 Sonnet</option>
                        <option selected="">DeepSeek R1</option>
                        <option>Grok-2</option>
                    </select>
                </div>

                <!-- Primary Model 3 -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Model 3</label>
                    <select class="form-select bg-themed border-secondary-subtle">
                        <option>Gemini 1.5</option>
                        <option>GPT-4o</option>
                        <option>Claude 3.5 Sonnet</option>
                        <option>DeepSeek R1</option>
                        <option selected="">Grok-2</option>
                    </select>
                </div>

                <hr class="border-secondary-subtle mb-4">

                <h6 class="fw-bold mb-3 text-info"><i class="fa-solid fa-brain me-2"></i>Master Model</h6>
                <p class="text-muted" style="font-size: 0.8rem;">The Master Model evaluates the three responses and
                    selects the best one.</p>
                <!-- Master Model -->
                <div class="mb-3">
                    <select class="form-select bg-themed border-info">
                        <option>Gemini 1.5</option>
                        <option selected="">GPT-4o</option>
                        <option>Claude 3.5 Sonnet</option>
                        <option>DeepSeek R1</option>
                        <option>Grok-2</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer px-4 py-3 border-top border-secondary-subtle bg-dark bg-opacity-10">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4">
                    <i class="fa-solid fa-check me-1"></i> Apply Models
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection()?>
