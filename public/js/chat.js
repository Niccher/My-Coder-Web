$(document).ready(function() {
    const $chatInput = $('#chat-input');
    const $chatContainer = $('#chat-container');
    const $inputWrapper = $('#input-wrapper');
    const $filePreviewArea = $('#file-preview-area');
    let attachedFiles = [];

    // --- Marked.js & Prism Configuration ---
    const renderer = new marked.Renderer();
    
    // Custom renderer for code blocks with copy buttons
    renderer.code = function(code, language) {
        const validLang = !!(language && Prism.languages[language]);
        const highlighted = validLang ? Prism.highlight(code, Prism.languages[language], language) : code.replace(/[<>&"']/g, m => ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;',"'":"&#39;"}[m]));
        const langClass = validLang ? `language-${language}` : '';
        const langLabel = language ? language.toUpperCase() : 'CODE';
        // Map language to appropriate FontAwesome brand icon
        const langIcons = { js:'fa-brands fa-js', javascript:'fa-brands fa-js', ts:'fa-solid fa-code', typescript:'fa-solid fa-code', py:'fa-brands fa-python', python:'fa-brands fa-python', php:'fa-brands fa-php', html:'fa-brands fa-html5', css:'fa-brands fa-css3-alt', json:'fa-solid fa-brackets-curly', md:'fa-brands fa-markdown', css3:'fa-brands fa-css3-alt' };
        const iconClass = langIcons[language] || 'fa-solid fa-code';
        
        return `
            <div class="card bg-dark text-light mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-black bg-opacity-25 d-flex justify-content-between align-items-center py-2 border-bottom border-light border-opacity-10">
                    <span class="badge bg-secondary bg-opacity-50 text-light px-2 py-1"><i class="${iconClass} me-1"></i>${langLabel}</span>
                    <button class="btn btn-sm btn-outline-light copy-code-btn py-0 px-2 d-flex align-items-center gap-1" onclick="copyToClipboard(this)" style="font-size: 0.75rem;">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
                <div class="card-body p-0">
                    <pre class="${langClass} m-0 p-3" style="background-color: #212529 !important;"><code>${highlighted}</code></pre>
                </div>
            </div>
        `;
    };

    marked.setOptions({
        renderer: renderer,
        highlight: function(code, lang) {
            if (Prism.languages[lang]) {
                return Prism.highlight(code, Prism.languages[lang], lang);
            }
            return code;
        },
        breaks: true,
        gfm: true
    });

    // Global copy function
    window.copyToClipboard = function(btn) {
        const $btn = $(btn);
        const code = $btn.siblings('pre').text();
        navigator.clipboard.writeText(code).then(() => {
            const originalHtml = $btn.html();
            $btn.html('<i class="fa-solid fa-check"></i> Copied!');
            $btn.addClass('text-success');
            setTimeout(() => {
                $btn.html(originalHtml);
                $btn.removeClass('text-success');
            }, 2000);
        });
    };

    // Theme Logic
    const savedTheme = localStorage.getItem('chat-theme') || 'solarized';
    applyTheme(savedTheme);

    $('.theme-menu .dropdown-item, .theme-selector-modal .theme-card').on('click', function(e) {
        e.preventDefault();
        const theme = $(this).data('theme');
        applyTheme(theme);
        localStorage.setItem('chat-theme', theme);
        
        if ($(this).hasClass('theme-card')) {
            $('.theme-card').removeClass('active');
            $(this).addClass('active');
        }
    });

    function applyTheme(theme) {
        $('body').attr('data-theme', theme);
        $('.theme-card').removeClass('active');
        $(`.theme-card[data-theme="${theme}"]`).addClass('active');
        
        if (theme === 'white') {
            $('.dropdown-menu').removeClass('dropdown-menu-dark');
        } else {
            $('.dropdown-menu').addClass('dropdown-menu-dark');
        }
    }

    // Modal Trigger
    $('#openSettings, #openSettingsDropdown').on('click', function() {
        $('#settingsModal').modal('show');
    });

    // Auto-resize textarea
    $chatInput.on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Handle send button click
    $('#send-btn').on('click', function() {
        sendMessage();
    });

    // Handle Enter key (Shift+Enter for newline)
    $chatInput.on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Track the active conversation across messages
    let currentConversationId = null;

    function sendMessage() {
        const message = $chatInput.val().trim();
        if (message === '' && attachedFiles.length === 0) return;

        appendMessage('user', message);
        $chatInput.val('').css('height', 'auto');

        // Collect file context
        const fileContext = attachedFiles.map(f => `=== ${f.name} ===\n${f.content || ''}`).join('\n\n');
        clearFiles();

        if ($('#greeting-box').length) {
            $('#greeting-box').fadeOut(300, function() { $(this).remove(); });
        }

        appendMultiModelResponse(message, fileContext);
    }

    function appendMessage(role, text) {
        const avatar = role === 'user' ? 'U' : 'AI';
        const timestampStr = getFullTimestamp();
        const messageContent = role === 'ai' ? marked.parse(text) : escapeHtml(text);
        
        const messageHtml = `
            <div class="message ${role} animate__animated animate__fadeInUp">
                <div class="avatar">${avatar}</div>
                <div class="message-body">
                    <div class="message-content">${messageContent}</div>
                    <div class="message-timestamp">${timestampStr}</div>
                </div>
            </div>
        `;
        $chatContainer.append(messageHtml);
        scrollToBottom();
    }

    // Icon map for known providers
    const providerIcons = {
        gemini:   'fa-microchip',
        deepseek: 'fa-brain',
        grok:     'fa-bolt',
        openai:   'fa-robot',
    };
    const kanbanClasses = ['kanban-bg-primary', 'kanban-bg-success', 'kanban-bg-info'];

    function appendMultiModelResponse(userPrompt, fileContext) {
        const timestampStr = getFullTimestamp();
        const messageId    = 'multi-' + Date.now();

        // Show 3-column skeleton loader while API is in-flight
        const skeletonHtml = `
            <div id="${messageId}" class="message ai multi-model-response animate__animated animate__fadeInUp w-100 pe-md-5">
                <div class="avatar"><i class="fa-solid fa-layer-group"></i></div>
                <div class="message-body w-100">
                    <div class="row g-2 kanban-container mb-3">
                        ${[1,2,3].map(i => `
                        <div class="col-md-4">
                            <div class="kanban-card">
                                <div class="kanban-header"><i class="fa-solid fa-circle-notch fa-spin me-2 opacity-50"></i> Querying...</div>
                                <div class="kanban-body">
                                    <div class="skeleton-line" style="width: 80%"></div>
                                    <div class="skeleton-line" style="width: 100%"></div>
                                    <div class="skeleton-line" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>`).join('')}
                    </div>
                    <div class="text-muted small ps-1"><i class="fa-solid fa-circle-notch fa-spin me-1"></i> Awaiting AI responses…</div>
                </div>
            </div>
        `;
        $chatContainer.append(skeletonHtml);
        scrollToBottom();

        // POST to real backend
        $.ajax({
            url: '/api/chat',
            method: 'POST',
            contentType: 'application/json',
            timeout: 300000, // 5 minutes
            data: JSON.stringify({
                prompt:          userPrompt,
                file_context:    fileContext,
                conversation_id: currentConversationId,
            }),
            success: function(data) {
                currentConversationId = data.conversation_id;

                const models    = data.models || [];
                const masterEval = data.master_eval;
                const masterName = data.master_model_name || 'Master Evaluation';

                // Build kanban columns from real responses
                const kanbanCols = models.map((model, idx) => {
                    const icon      = providerIcons[model.provider] || 'fa-microchip';
                    const className = kanbanClasses[idx] || 'kanban-bg-primary';
                    return `
                        <div class="col-md-4">
                            <div class="kanban-card ${className}" id="${messageId}_col_${idx}">
                                <div class="kanban-header border-opacity-25">
                                    <i class="fa-solid ${icon} me-1"></i> ${escapeHtml(model.model_name)}
                                    ${model.error ? '<span class="badge bg-danger ms-2">Error</span>' : ''}
                                </div>
                                <div class="kanban-body markdown-body">
                                    <span class="streaming-content"></span><span class="typing-cursor"></span>
                                </div>
                            </div>
                        </div>`;
                }).join('');

                $(`#${messageId} .message-body`).html(`
                    <div class="row g-2 kanban-container mb-3">${kanbanCols}</div>
                    <div class="master-evaluation-card mb-2 shadow-sm" style="display:none;">
                        <div class="kanban-header text-danger bg-danger bg-opacity-10">
                            <i class="fa-solid fa-star me-1"></i> ${escapeHtml(masterName)}
                        </div>
                        <div class="kanban-body markdown-body">
                            <span class="streaming-content"></span><span class="typing-cursor"></span>
                        </div>
                    </div>
                    <div class="message-timestamp">${timestampStr}</div>
                `);

                // Stream each model response into its column
                let doneCount = 0;
                models.forEach((model, idx) => {
                    const text = model.error
                        ? `⚠️ **Error:** ${model.error}`
                        : (model.content || '*(No response)*');

                    streamText($(`#${messageId}_col_${idx} .streaming-content`), text, () => {
                        $(`#${messageId}_col_${idx} .typing-cursor`).remove();
                        doneCount++;
                        // Show master eval only after all models complete
                        if (doneCount === models.length && masterEval) {
                            showMasterEval(messageId, masterEval);
                        }
                    });
                });

                // If backend returned no master eval (e.g. not configured), keep it hidden
            },
            error: function(xhr) {
                const errMsg = xhr.responseJSON?.messages?.error || xhr.responseJSON?.message || 'Failed to contact the AI backend.';
                $(`#${messageId} .message-body`).html(`
                    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>${escapeHtml(errMsg)}</div>
                    </div>
                `);
            }
        });
    }

    function streamText($el, text, callback) {
        const words = text.split(' ');
        let i = 0;
        const interval = setInterval(() => {
            if (i < words.length) {
                const currentContent = words.slice(0, i + 1).join(' ');
                $el.html(marked.parse(currentContent));
                i++;
                scrollToBottom();
            } else {
                clearInterval(interval);
                if (callback) callback();
            }
        }, 60);
    }

    function showMasterEval(messageId, text) {
        const $master = $(`#${messageId} .master-evaluation-card`);
        $master.show().addClass('animate__animated animate__fadeInUp');
        streamText($master.find('.streaming-content'), text, () => {
            $master.find('.typing-cursor').remove();
        });
    }

    // --- File Upload Logic ---
    const $fileInput = $('#file-upload-input');

    $('#upload-btn').on('click', function() {
        $fileInput.trigger('click');
    });

    $fileInput.on('change', function(e) {
        handleFiles(e.target.files);
        // Reset input so the same file can be selected again if needed
        $(this).val('');
    });

    $inputWrapper.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $inputWrapper.on('dragleave drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    $inputWrapper.on('drop', function(e) {
        const files = e.originalEvent.dataTransfer.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        // Map of file extensions → Bootstrap colour tokens
        const extColors = {
            js:   { bg: '#f7df1e22', border: '#f7df1e55', badge: '#f7df1e', text: '#212529', icon: 'fa-js' },
            ts:   { bg: '#3178c622', border: '#3178c655', badge: '#3178c6', text: '#fff',    icon: 'fa-code' },
            py:   { bg: '#3572a522', border: '#3572a555', badge: '#3572a5', text: '#fff',    icon: 'fa-python' },
            php:  { bg: '#6e4f9e22', border: '#6e4f9e55', badge: '#6e4f9e', text: '#fff',    icon: 'fa-php' },
            html: { bg: '#e34c2622', border: '#e34c2655', badge: '#e34c26', text: '#fff',    icon: 'fa-html5' },
            css:  { bg: '#563d7c22', border: '#563d7c55', badge: '#563d7c', text: '#fff',    icon: 'fa-css3-alt' },
            json: { bg: '#0d6efd22', border: '#0d6efd55', badge: '#0d6efd', text: '#fff',    icon: 'fa-brackets-curly' },
            md:   { bg: '#6610f222', border: '#6610f255', badge: '#6610f2', text: '#fff',    icon: 'fa-markdown' },
            csv:  { bg: '#19875422', border: '#19875455', badge: '#198754', text: '#fff',    icon: 'fa-table' },
        };
        const defaultColor = { bg: '#6c757d22', border: '#6c757d55', badge: '#6c757d', text: '#fff', icon: 'fa-file-code' };

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        Array.from(files).forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            const isText = file.type.startsWith('text/') || /\.(js|ts|py|php|css|html|md|json|csv)$/i.test(file.name);
            const colors = extColors[ext] || defaultColor;
            const iconClass = 'fa-brands ' + (colors.icon.startsWith('fa-python') || colors.icon.startsWith('fa-php') || colors.icon.startsWith('fa-js') || colors.icon.startsWith('fa-html') || colors.icon.startsWith('fa-css') || colors.icon.startsWith('fa-markdown') ? colors.icon : 'fa-regular fa-file-code');
            
            // Use fa-brands for known brand icons, fa-regular for generic
            const brandIcons = ['fa-js','fa-python','fa-php','fa-html5','fa-css3-alt','fa-markdown'];
            const iconPrefix = brandIcons.includes(colors.icon) ? 'fa-brands' : 'fa-solid';

            const $chip = $(`
                <div class="card d-inline-flex flex-row align-items-center p-2 me-2 mb-2 shadow-sm border border-secondary border-opacity-25 bg-body-tertiary" style="border-radius: 12px; max-width: 260px;">
                    <span class="badge bg-primary px-2 py-2 rounded-3 me-2 fs-6 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        ${ext.toUpperCase()}
                    </span>
                    <div class="d-flex flex-column me-3 overflow-hidden text-nowrap">
                        <span class="text-truncate fw-semibold" style="font-size: 0.85rem;" title="${file.name}">${file.name}</span>
                        <small class="text-muted d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                            <i class="${iconPrefix} ${colors.icon}"></i> ${formatSize(file.size)}${!isText ? ' (No Context)' : ''}
                        </small>
                    </div>
                    <button type="button" class="btn-close ms-auto chip-remove" aria-label="Remove file" style="font-size: 0.65rem;"></button>
                </div>
            `);

            const reader = new FileReader();
            reader.onload = (e) => {
                file.content = e.target.result;
                attachedFiles.push(file);
            };

            if (isText) {
                reader.readAsText(file);
            } else {
                file.content = '[Binary file — no text context]';
                attachedFiles.push(file);
            }

            $chip.find('.chip-remove').on('click', function() {
                attachedFiles = attachedFiles.filter(f => f !== file);
                $chip.addClass('chip-exit');
                setTimeout(() => $chip.remove(), 200);
            });

            $filePreviewArea.append($chip);
        });
    }


    function clearFiles() {
        attachedFiles = [];
        $filePreviewArea.empty();
    }

    function getFullTimestamp() {
        const now = new Date();
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit' };
        return `${now.toLocaleDateString('en-GB', dateOptions)}, ${now.toLocaleTimeString([], timeOptions)}`;
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function scrollToBottom() {
        const scrollHeight = $chatContainer[0].scrollHeight;
        $chatContainer.stop().animate({ scrollTop: scrollHeight }, 500);
    }

    $('.toggle-sidebar').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
    });
});
