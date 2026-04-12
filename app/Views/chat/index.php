<?= $this->extend('chat/layout')?>

<?= $this->section('content')?>

<!-- User Profile Dropdown -->
<div class="user-profile-wrapper position-absolute top-0 end-0 mt-3 me-4" style="z-index: 100;">
    <div class="dropdown">
        <button class="btn btn-link p-1 text-decoration-none d-flex align-items-center gap-2 profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="avatar-sm"><?= strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) ?></div>
            <span class="fw-medium text-themed d-none d-md-block"><?= esc(auth()->user()->username ?? 'User') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-secondary-subtle">
            <li><h6 class="dropdown-header">Account (<?= esc(auth()->user()->email ?? '') ?>)</h6></li>
            <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><i class="fa-solid fa-user-gear opacity-50"></i> View Profile</a></li>
            <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" id="openSettingsDropdown"><i class="fa-solid fa-gear opacity-50"></i> Settings</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 export-chat-btn d-flex align-items-center gap-2" href="#" data-format="json"><i class="fa-solid fa-file-code opacity-50"></i> Export as JSON</a></li>
            <li><a class="dropdown-item py-2 export-chat-btn d-flex align-items-center gap-2" href="#" data-format="xml"><i class="fa-solid fa-code opacity-50"></i> Export as XML</a></li>
            <li><a class="dropdown-item py-2 export-chat-btn d-flex align-items-center gap-2" href="#" data-format="yaml"><i class="fa-solid fa-file-lines opacity-50"></i> Export as YAML</a></li>
            <li><a class="dropdown-item py-2 export-chat-btn d-flex align-items-center gap-2" href="#" data-format="pdf"><i class="fa-solid fa-file-pdf opacity-50"></i> Export as PDF</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?= url_to('logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
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
    <div id="token-estimate-container" class="d-none w-100 px-3 py-1 bg-body-tertiary border-bottom border-secondary border-opacity-25 text-end small shadow-sm text-muted" style="border-top-left-radius: 20px; border-top-right-radius: 20px; font-size: 0.75rem;">
        <i class="fa-solid fa-coins me-1 text-warning"></i> 
        Tokens: <strong id="token-count-val">0</strong> (<span id="token-cost-val">~$0.00</span>)
    </div>
    <div id="file-preview-area"></div>
    <div id="input-container">
        <input type="file" id="file-upload-input" multiple style="display: none;">
        <button id="upload-btn" class="action-btn" title="Upload files"><i class="fa-solid fa-paperclip"></i></button>
        <textarea id="chat-input" placeholder="Type a message..." rows="1"></textarea>
        
        <!-- Inline Persona Selector -->
        <div class="dropdown h-100 d-flex align-items-center">
            <button class="btn btn-link text-themed p-2 d-flex align-items-center gap-1 opacity-75 hover-opacity-100" type="button" id="personaDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem; text-decoration: none;">
                <i class="fa-solid fa-user-ninja text-primary anim-pulse" style="font-size: 0.8rem;"></i>
                <span id="current-persona-name" class="d-none d-sm-inline opacity-75" style="font-size: 0.75rem;">Default</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-secondary-subtle" id="persona-list-dropdown" style="min-width: 250px; border-radius: 12px;">
                <li><h6 class="dropdown-header">Choose AI Persona</h6></li>
                <li><hr class="dropdown-divider"></li>
                <!-- Loaded dynamically -->
            </ul>
        </div>

        <button id="send-btn" class="action-btn text-primary"><i class="fa-solid fa-paper-plane"></i></button>
    </div>

    <!-- Active Models Container -->
    <div class="active-models-container mt-4 d-flex flex-column align-items-center gap-2">
        <div class="d-flex gap-2 justify-content-center" id="active-primary-models">
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-gear me-1 pe-none"></i>
                Configure Models
            </button>
        </div>
        <div id="active-master-model"></div>
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
                    <div class="nav nav-pills flex-column settings-nav border-end border-secondary-subtle p-3" role="tablist" aria-orientation="vertical">
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
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sp-personas" type="button" role="tab">
                            <i class="fa-solid fa-users-gear"></i> Personas
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content settings-content p-4 flex-grow-1">

                        <!-- ── Profile ──────────────────────────── -->
                        <div class="tab-pane fade show active" id="sp-profile">
                            <h6 class="settings-heading">User Profile</h6>
                            <p class="settings-desc">Manage your display name and email address.</p>

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
                        </div>

                        <!-- ── Personas ─────────────────────────── -->
                        <div class="tab-pane fade" id="sp-personas">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="settings-heading mb-0">AI Personas</h6>
                                <button class="btn btn-sm btn-primary rounded-pill" id="add-persona-btn">
                                    <i class="fa-solid fa-plus me-1"></i> New
                                </button>
                            </div>
                            <p class="settings-desc">Define custom system instructions for different roles.</p>

                            <div id="personas-list-container" class="mb-4">
                                <!-- Dynamically populated list of personas -->
                                <div class="text-center py-3 text-muted">
                                    <i class="fa-solid fa-circle-notch fa-spin me-2"></i> Loading personas...
                                </div>
                            </div>

                            <div id="persona-editor-card" class="card bg-themed border-secondary border-opacity-25 d-none">
                                <div class="card-body p-3">
                                    <h6 class="fs-6 fw-bold mb-3" id="persona-editor-title">Create New Persona</h6>
                                    <input type="hidden" id="edit-persona-id" value="0">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold mb-1">Persona Name</label>
                                        <input type="text" id="edit-persona-name" class="form-control form-control-sm" placeholder="e.g. Code Reviewer">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold mb-1">System Instructions</label>
                                        <textarea id="edit-persona-instructions" class="form-control form-control-sm" rows="5" placeholder="You are an expert programmer who reviews code for security and performance..."></textarea>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="edit-persona-default">
                                        <label class="form-check-label small" for="edit-persona-default">Set as default for new chats</label>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-primary flex-grow-1" id="save-persona-btn">Save Persona</button>
                                        <button class="btn btn-sm btn-outline-secondary" id="cancel-persona-btn">Cancel</button>
                                    </div>
                                </div>
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
                            <p class="settings-desc">Configure up to 3 AI models to run side-by-side. The <strong>Master Model</strong> (slot 4) evaluates which response is best.</p>

                            <div id="model-save-status" class="mb-3" style="display:none;"></div>

                            <?php
                            $slots = [
                                ['slot' => 1, 'badge' => 'bg-primary',         'label' => 'Model 1'],
                                ['slot' => 2, 'badge' => 'bg-success',         'label' => 'Model 2'],
                                ['slot' => 3, 'badge' => 'bg-warning text-dark','label' => 'Model 3'],
                                ['slot' => 4, 'badge' => 'bg-danger',           'label' => 'Master ★'],
                            ];
                            $providerOptions = [
                                'openai'     => 'OpenAI',
                                'deepseek'   => 'DeepSeek',
                                'kimi'       => 'Kimi (Moonshot)',
                                'minimax'    => 'MiniMax',
                                'gemini'     => 'Google Gemini',
                                'grok'       => 'Grok (xAI)',
                                'groq'       => 'Groq (Fast Open-Source)',
                                'openrouter' => 'OpenRouter (Free Models)',
                            ];
                            $modelOptions = [
                                'openai'    => ['GPT-4o', 'GPT-4o Mini', 'GPT-4 Turbo', 'o1', 'o3 Mini'],
                                'deepseek'  => ['DeepSeek R1', 'DeepSeek V3'],
                                'kimi'      => ['Kimi k1.5', 'moonshot-v1-8k', 'moonshot-v1-32k'],
                                'minimax'   => ['MiniMax-Text-01', 'abab6.5s-chat'],
                                'gemini'    => ['Gemini 2.0 Flash', 'Gemini 2.0 Pro', 'Gemini 1.5 Flash', 'Gemini 1.5 Pro'],
                                'grok'      => ['Grok-3', 'Grok-2', 'Grok-2 Mini'],
                                'groq'      => ['Llama 3.3 70B Versatile', 'Llama 3.1 8B Instant', 'Qwen3 32B', 'Grok Compound'],
                                'openrouter'=> ['Llama 3.3 70B (Free)', 'Llama 3.2 3B (Free)', 'Gemma 3 27B (Free)', 'Gemma 3 12B (Free)'],
                            ];
                            ?>

                            <?php foreach ($slots as $s) : ?>
                            <div class="card bg-body-tertiary border-secondary border-opacity-25 shadow-sm mb-3 rounded-4">
                                <!-- Top Row: Icon + Provider (FlexRow) -->
                                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-10 py-2 px-3 d-flex align-items-center gap-3">
                                    <div class="provider-icon-badge d-flex align-items-center justify-content-center rounded-circle shadow-sm fw-bold text-white" id="provider_icon_<?= $s['slot'] ?>" style="width:36px;height:36px;font-size:0.75rem;flex-shrink:0;background:#6c757d;transition:background 0.3s;">
                                        <?= $s['slot'] == 4 ? '<i class="fa-solid fa-star"></i>' : $s['slot'] ?>
                                    </div>
                                    <select class="form-select border-0 bg-transparent fw-semibold shadow-none model-provider p-0 text-body fs-6" data-slot="<?= $s['slot'] ?>" id="model_provider_<?= $s['slot'] ?>" style="cursor:pointer;">
                                        <option value="">-- Select Provider --</option>
                                        <?php foreach ($providerOptions as $val => $label) : ?>
                                            <option value="<?= $val ?>"><?= $label ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <!-- Rows Below: Stacked Model & Token -->
                                <div class="card-body px-3 py-3 ps-5">
                                    <div class="d-flex flex-column gap-2 border-start border-2 border-secondary border-opacity-25 ps-3 py-1 ms-1">
                                        <!-- Model Select -->
                                        <select class="form-select form-select-sm model-name bg-body" data-slot="<?= $s['slot'] ?>" id="model_name_<?= $s['slot'] ?>">
                                            <option value="">-- Select Model --</option>
                                        </select>
                                        
                                        <!-- API Key Input -->
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                            <input type="password" 
                                                class="form-control model-apikey border-start-0 ps-0 bg-body" 
                                                data-slot="<?= $s['slot'] ?>" 
                                                id="model_apikey_<?= $s['slot'] ?>" 
                                                placeholder="API Key / Token" 
                                                autocomplete="off">
                                        </div>

                                        <!-- History Context Limit -->
                                        <div class="input-group input-group-sm" title="How many past messages to send as context. Leave blank for unlimited.">
                                            <span class="input-group-text bg-body border-end-0 text-muted"><i class="fa-solid fa-clock-rotate-left"></i></span>
                                            <input type="number"
                                                class="form-control model-history-limit border-start-0 ps-0 bg-body"
                                                data-slot="<?= $s['slot'] ?>"
                                                id="model_history_limit_<?= $s['slot'] ?>"
                                                placeholder="History messages (blank = unlimited)"
                                                min="1" max="500">
                                        </div>
                                    </div>

                                    <!-- Status Message -->
                                    <div class="mt-2 ps-3 ms-1">
                                        <small class="text-muted model-key-status" id="model_status_<?= $s['slot'] ?>"></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach ?>

                            <!-- Inline JS: provider meta + model options for JS -->
                            <script>
                            window.mcModelOptions = <?= json_encode($modelOptions) ?>;
                            window.mcProviderMeta = {
                                openai:     { label: 'OpenAI',          initials: 'AI',  bg: '#10a37f' },
                                deepseek:   { label: 'DeepSeek',        initials: 'DS',  bg: '#1a73e8' },
                                kimi:       { label: 'Kimi',            initials: 'K',   bg: '#7b2ff7' },
                                minimax:    { label: 'MiniMax',         initials: 'MM',  bg: '#ff6b35' },
                                gemini:     { label: 'Google Gemini',   initials: 'G',   bg: '#4285f4' },
                                grok:       { label: 'Grok (xAI)',      initials: 'X',   bg: '#000000' },
                                groq:       { label: 'Groq',            initials: 'GQ',  bg: '#f55036' },
                                openrouter: { label: 'OpenRouter',      initials: 'OR',  bg: '#2563eb' },
                            };
                            </script>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer px-4 py-3 border-top border-secondary-subtle">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="settings-save-btn">
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
                    <i class="fa-solid fa-layer-group me-2 opacity-50"></i>Active Models
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="overflow-y: auto;">
                <p class="text-muted small mb-4">Showing the models currently configured in Settings. To change them, open <strong>Settings → Models</strong>.</p>

                <?php
                $quickSlots = [
                    ['slot' => 1, 'label' => 'Primary Model 1', 'badge_class' => 'bg-primary'],
                    ['slot' => 2, 'label' => 'Primary Model 2', 'badge_class' => 'bg-success'],
                    ['slot' => 3, 'label' => 'Primary Model 3', 'badge_class' => 'bg-info text-dark'],
                    ['slot' => 4, 'label' => 'Master Model ★',  'badge_class' => 'bg-danger'],
                ];
                foreach ($quickSlots as $qs): ?>
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3 bg-body-tertiary border border-secondary border-opacity-10">
                    <!-- Provider Icon -->
                    <div class="provider-icon-badge d-flex align-items-center justify-content-center rounded-circle shadow-sm fw-bold text-white flex-shrink-0"
                         id="quick_icon_<?= $qs['slot'] ?>"
                         style="width:38px;height:38px;font-size:0.75rem;background:#6c757d;transition:background 0.3s;">
                        <?= $qs['slot'] == 4 ? '<i class="fa-solid fa-star"></i>' : $qs['slot'] ?>
                    </div>
                    <!-- Labels + Selects -->
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge <?= $qs['badge_class'] ?> rounded-pill" style="font-size:0.65rem;"><?= $qs['label'] ?></span>
                        </div>
                        <select class="form-select form-select-sm bg-body border-secondary-subtle quick-provider-select mb-1"
                                data-slot="<?= $qs['slot'] ?>" id="quick_provider_<?= $qs['slot'] ?>">
                            <option value="">-- Provider --</option>
                            <?php foreach ($providerOptions as $val => $lbl): ?>
                            <option value="<?= $val ?>"><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-select form-select-sm bg-body border-secondary-subtle quick-model-select"
                                data-slot="<?= $qs['slot'] ?>" id="quick_model_<?= $qs['slot'] ?>">
                            <option value="">-- Model --</option>
                        </select>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-footer px-4 py-3 border-top border-secondary-subtle bg-dark bg-opacity-10">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="quick-model-save-btn" data-bs-dismiss="modal" onclick="$('#settingsModal').modal('show')">
                    <i class="fa-solid fa-gear me-1"></i> Advanced Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- All History Modal -->
<div class="modal fade" id="allHistoryModal" tabindex="-1" aria-labelledby="allHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header px-4 py-3 border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="allHistoryModalLabel">
                    <i class="fa-solid fa-clock-rotate-left me-2 opacity-50"></i>All History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group mb-4 shadow-sm rounded-pill overflow-hidden border border-secondary border-opacity-25 bg-body-tertiary">
                    <span class="input-group-text bg-transparent border-0 ps-3 text-muted"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="historySearchInput" class="form-control border-0 bg-transparent shadow-none" placeholder="Search conversations...">
                </div>
                <div id="allHistoryList" class="d-flex flex-column gap-2" style="min-height: 200px;">
                    <!-- Loaded via JS -->
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button id="historyPrevBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-chevron-left me-1"></i> Prev</button>
                    <span id="historyPageInfo" class="text-muted small">Page 1 of 1</span>
                    <button id="historyNextBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Next <i class="fa-solid fa-chevron-right ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection()?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js"></script>
<script>
window.serverData = { chatUuid: '<?= esc($chatUuid ?? '') ?>' };
</script>
<script>
// ────────────────────────────────────────────────────────────
// Settings Modal — Model Configuration
// ────────────────────────────────────────────────────────────

$(document).ready(function () {

    // Populate model-name dropdown AND update icon when provider changes
    $(document).on('change', '.model-provider', function () {
        const slot     = $(this).data('slot');
        const provider = $(this).val();

        // Update model list
        const $nameEl  = $(`#model_name_${slot}`);
        $nameEl.empty().append('<option value="">-- Select Model --</option>');
        if (provider && window.mcModelOptions && window.mcModelOptions[provider]) {
            window.mcModelOptions[provider].forEach(function (name) {
                $nameEl.append(`<option value="${name}">${name}</option>`);
            });
        }

        // Update provider icon badge
        const $icon = $(`#provider_icon_${slot}`);
        const meta  = window.mcProviderMeta && window.mcProviderMeta[provider];
        if (meta) {
            $icon.css('background', meta.bg).html(`<span>${meta.initials}</span>`);
        } else {
            $icon.css('background', '#6c757d').html(slot == 4 ? '<i class="fa-solid fa-star"></i>' : slot);
        }
    });

    // Load saved settings when Settings modal opens on Models tab
    $('#settingsModal').on('shown.bs.modal.settings', function () {
        loadModelSettings();
    });

    // Also load on page init (so values are pre-filled if modal was opened before)
    loadModelSettings();

    function loadModelSettings() {
        $.getJSON('/api/settings', function (data) {
            $.each(data, function (slot, s) {
                slot = parseInt(slot);
                // -- Settings modal --
                $(`#model_provider_${slot}`).val(s.provider || '').trigger('change');
                setTimeout(function () {
                    $(`#model_name_${slot}`).val(s.model_name || '');
                }, 50);
                const $status = $(`#model_status_${slot}`);
                if (s.has_key) {
                    $status.html('<i class="fa-solid fa-key me-1 text-success"></i>Key saved <span class="text-muted">' + (s.api_key || '') + '</span>');
                } else {
                    $status.text('');
                }

                // Populate history limit field
                const $histLimit = $(`#model_history_limit_${slot}`);
                $histLimit.val(s.history_limit != null ? s.history_limit : '');

                // -- Quick modal (mirrored) --
                $(`#quick_provider_${slot}`).val(s.provider || '').trigger('change.quick');
                setTimeout(function () {
                    $(`#quick_model_${slot}`).val(s.model_name || '');
                }, 60);
            });
            
            // Store globally for chat logic
            window.activeChatModels = data;
            renderActiveModelsUI();
        });
    }

    function renderActiveModelsUI() {
        const data = window.activeChatModels;
        const $primary = $('#active-primary-models');
        const $master = $('#active-master-model');
        $primary.empty();
        $master.empty();

        let hasReadyModel = false;
        const btnClasses = ['btn-outline-primary', 'btn-outline-success', 'btn-outline-info'];

        for (let i = 1; i <= 3; i++) {
            const s = data[i];
            if (s && s.has_key && s.model_name) {
                hasReadyModel = true;
                const cls = btnClasses[i - 1] || 'btn-outline-secondary';
                $primary.append(`<button class="btn btn-sm ${cls} rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-microchip me-1 pe-none"></i> ${escapeHtml(s.model_name)}</button>`);
            }
        }

        if (!hasReadyModel) {
            $primary.append(`<button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-triangle-exclamation me-1 pe-none"></i> API Key Required - Click to Configure</button>`);
        }

        if (data[4] && data[4].has_key && data[4].model_name) {
            $master.append(`<button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold active-model-btn" data-bs-toggle="modal" data-bs-target="#settingsModal" style="font-size: 0.75rem;"><i class="fa-solid fa-star me-1 text-warning pe-none"></i> Evaluated by ${escapeHtml(data[4].model_name)}</button>`);
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString().replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // Quick modal: cascade provider → model
    $(document).on('change.quick', '.quick-provider-select', function () {
        const slot     = $(this).data('slot');
        const provider = $(this).val();

        const $modelEl = $(`#quick_model_${slot}`);
        $modelEl.empty().append('<option value="">-- Model --</option>');
        if (provider && window.mcModelOptions && window.mcModelOptions[provider]) {
            window.mcModelOptions[provider].forEach(function (name) {
                $modelEl.append(`<option value="${name}">${name}</option>`);
            });
        }

        // Update icon
        const $icon = $(`#quick_icon_${slot}`);
        const meta  = window.mcProviderMeta && window.mcProviderMeta[provider];
        if (meta) {
            $icon.css('background', meta.bg).html(`<span>${meta.initials}</span>`);
        } else {
            $icon.css('background', '#6c757d').html(slot == 4 ? '<i class="fa-solid fa-star"></i>' : slot);
        }
    });

    // Save settings
    $('#settings-save-btn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin me-1"></i> Saving…');

        const slots = [];
        for (let i = 1; i <= 4; i++) {
            const rawLimit = $(`#model_history_limit_${i}`).val();
            slots.push({
                slot:          i,
                provider:      $(`#model_provider_${i}`).val() || '',
                model_name:    $(`#model_name_${i}`).val() || '',
                api_key:       $(`#model_apikey_${i}`).val() || '',
                history_limit: rawLimit !== '' ? parseInt(rawLimit, 10) : null,
            });
        }

        $.ajax({
            url:         '/api/settings',
            method:      'POST',
            contentType: 'application/json',
            data:        JSON.stringify({ slots }),
            success: function () {
                // Clear key inputs after saving, reload statuses
                for (let i = 1; i <= 4; i++) $(`#model_apikey_${i}`).val('');
                loadModelSettings();
                const $status = $('#model-save-status');
                $status.show().html('<div class="alert alert-success py-2 mb-0"><i class="fa-solid fa-check-circle me-1"></i> Settings saved successfully.</div>');
                setTimeout(() => $status.fadeOut(), 3500);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to save settings.';
                $('#model-save-status').show().html(`<div class="alert alert-danger py-2 mb-0">${msg}</div>`);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Save');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
