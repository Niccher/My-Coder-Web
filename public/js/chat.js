$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content')
        }
    });

    const $chatInput = $('#chat-input');
    const $chatContainer = $('#chat-container');
    const $inputWrapper = $('#input-wrapper');
    const $filePreviewArea = $('#file-preview-area');
    const $sidebar = $('#sidebar');
    const $sidebarBackdrop = $('#sidebar-backdrop');
    const $mobileSidebarToggle = $('#mobile-sidebar-toggle');
    let attachedFiles = [];
    let activePersonaId = 0;
    let userMessageIndex = 0; // tracks position of user messages in current conversation
    let currentConversationId = null;
    let currentChatRequest = null;
    let userHasScrolledUp = false;
    const $sidebarHistoryList = $('#sidebar-history-list');

    $chatContainer.on('scroll', function() {
        const threshold = 50; // pixels from the bottom
        const isAtBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - threshold;
        userHasScrolledUp = !isAtBottom;
    });

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

    // Auto-resize textarea and count tokens
    $chatInput.on('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        calculateTokens();
    });

    // Handle send button click
    $('#send-btn').on('click', function() {
        sendMessage();
    });

    $('#stop-btn').on('click', function() {
        if (currentChatRequest) {
            currentChatRequest.abort();
        }
    });

    // Handle Enter key (Shift+Enter for newline)
    $chatInput.on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Track the active conversation across messages


    window.loadConversations = function() {
        $.getJSON('/api/conversations', function(data) {
            $sidebarHistoryList.find('.history-item, .no-chats').remove();

            if (!data || data.length === 0) {
                $sidebarHistoryList.append('<div class="px-3 py-2 text-muted no-chats" style="font-size: 0.85rem;">No recent chats.</div>');
                return;
            }

            // Top 5
            const recent = data.slice(0, 5);
            recent.forEach(conv => {
                const isActive = (conv.id == currentConversationId) ? 'bg-secondary bg-opacity-25' : '';
                $sidebarHistoryList.append(`
                    <div class="history-item d-flex justify-content-between align-items-center ${isActive}" data-id="${conv.id}">
                        <span class="text-truncate flex-grow-1 sidebar-chat-title" style="cursor:pointer;" title="${escapeHtml(conv.title)}">${escapeHtml(conv.title || 'New Chat')}</span>
                        <button class="btn btn-link text-danger p-0 ms-2 delete-chat-btn" data-id="${conv.id}" title="Delete chat">
                            <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>
                `);
            });
        });
    }

    window.loadConversations();


    $(document).on('click', '.sidebar-chat-title', function() {
        const id = $(this).closest('.history-item').data('id');
        loadConversation(id);
    });

    $(document).on('click', '.delete-chat-btn', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this conversation?')) return;
        $.ajax({
            url: `/api/conversations/${id}`,
            method: 'DELETE',
            success: function() {
                if (currentConversationId == id) {
                    $('.new-chat-btn').click();
                } else {
                    window.loadConversations();
                }
            }
        });
    });

    $('.new-chat-btn').on('click', function() {
        currentConversationId = null;
        userMessageIndex = 0;
        window.history.pushState({}, '', '/');
        
        const userName = (window.serverData && window.serverData.userName) ? window.serverData.userName : 'User';
        $chatContainer.empty().append(`
            <div id="greeting-box" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center pb-5 animate__animated animate__fadeIn">
                <h1 class="display-4 fw-bold mb-3" style="background: linear-gradient(to right, #4285f4, #d96570); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Hello, ${userName}</h1>
                <p class="lead text-muted">How can I help you today?</p>
            </div>
        `);
        window.loadConversations();
        if ($(window).width() <= 768) $sidebarBackdrop.click();
    });

    window.loadConversation = function(id) {
        userMessageIndex = 0; // reset branch index
        $chatContainer.empty().append('<div class="text-center mt-5 text-muted"><i class="fa-solid fa-circle-notch fa-spin fa-2x"></i> Loading...</div>');
        
        $.getJSON(`/api/conversations/${id}`, function(data) {
            currentConversationId = data.id;
            activePersonaId = data.persona_id || 0;
            $('#current-persona-name').text(data.persona_name || 'Default Assistant');
            $chatContainer.empty();
            
            if (data.messages === undefined || data.messages.length === 0) {
                $chatContainer.append('<div class="text-center mt-5 text-muted">No messages found.</div>');
            } else {
                data.messages.forEach(msg => {
                    const aiTitle = (msg.role === 'ai') ? msg.model_name : null;
                    appendMessage(msg.role, msg.content, aiTitle, msg.id);
                });
            }
            scrollToBottom(true);
            window.loadConversations();
            if ($(window).width() <= 768) $sidebarBackdrop.click();
        }).fail(function() {
            $chatContainer.empty().append('<div class="text-center mt-5 text-danger">Failed to load conversation.</div>');
        });
    }

    // Check if unique URL ID was passed (moved here after loadConversation is defined)
    if (window.serverData && window.serverData.chatUuid) {
        loadConversation(window.serverData.chatUuid);
    }

    function sendMessage() {
        if (currentChatRequest) return;
        const message = $chatInput.val().trim();
        if (message === '' && attachedFiles.length === 0) return;

        let hasValidConfig = false;
        let activeModelKeys = [];
        if (window.activeChatModels) {
            for (let i = 1; i <= 6; i++) {
                if (window.activeChatModels[i] && window.activeChatModels[i].has_key && window.activeChatModels[i].model_name) {
                    hasValidConfig = true;
                    activeModelKeys.push(i);
                }
            }
        }
        
        if (!hasValidConfig) {
            alert("No configured AI models detected! Please set up at least one model and API key before chatting.");
            $('#settingsModal').modal('show');
            return;
        }

        appendMessage('user', message);
        $chatInput.val('').css('height', 'auto');
        $('#send-btn').addClass('d-none');
        $('#stop-btn').removeClass('d-none');

        // Collect file context
        const fileContext = attachedFiles.map(f => `=== ${f.name} ===\n${f.content || ''}`).join('\n\n');
        clearFiles();
        calculateTokens();

        if ($('#greeting-box').length) {
            $('#greeting-box').fadeOut(300, function() { $(this).remove(); });
        }

        appendMultiModelResponse(message, fileContext, activeModelKeys);
    }



    function appendMessage(role, text, customTitle = null, msgId = null) {
        const avatar = role === 'user' ? 'U' : 'AI';
        const timestampStr = getFullTimestamp();
        let messageContent = text;
        if (role === 'ai') {
            messageContent = marked.parse(text);
        } else {
            messageContent = escapeHtml(text);
        }

        const titleHtml = customTitle ? `<div class="mb-2 fw-semibold text-primary"><i class="fa-solid fa-microchip me-1"></i> ${escapeHtml(customTitle)}</div>` : '';
        
        // Branch & Retry buttons only on user messages
        const msgIdx = role === 'user' ? ++userMessageIndex : null;
        const branchBtn = (role === 'user') 
            ? `<button class="btn btn-link p-0 ms-2 branch-msg-btn text-muted" title="Branch from here" data-msg-idx="${msgIdx}" ${msgId ? `data-msg-id="${msgId}"` : ''}><i class="fa-solid fa-code-branch" style="font-size:0.8rem;"></i></button>
               <button class="btn btn-link p-0 ms-2 retry-msg-btn text-muted" title="Resend this query"><i class="fa-solid fa-arrows-rotate" style="font-size:0.8rem;"></i></button>`
            : `<button class="btn btn-link p-0 ms-2 copy-ai-btn text-muted" title="Copy response"><i class="fa-regular fa-copy" style="font-size:0.8rem;"></i></button>`;
        
        const messageHtml = `
            <div class="message ${role} animate__animated animate__fadeInUp" ${msgId ? `data-msg-id="${msgId}"` : `data-msg-idx="${msgIdx}"`}>
                <div class="avatar">${avatar}</div>
                <div class="message-body">
                    <div class="message-content">${titleHtml}${messageContent}</div>
                    <div class="message-timestamp d-flex align-items-center gap-2">${timestampStr}${branchBtn}</div>
                </div>
            </div>
        `;
        $chatContainer.append(messageHtml);
        scrollToBottom(true);
    }

    // Icon map for known providers
    const providerIcons = {
        gemini:   'fa-microchip',
        deepseek: 'fa-brain',
        grok:     'fa-bolt',
        openai:   'fa-robot',
    };
    const kanbanClasses = ['kanban-bg-primary', 'kanban-bg-success', 'kanban-bg-info', 'kanban-bg-warning', 'kanban-bg-secondary', 'kanban-bg-dark'];

    function getKanbanColClass(count) {
        if (count === 1) return 'col-12';
        if (count === 2) return 'col-12 col-md-6';
        if (count === 3) return 'col-12 col-md-4';
        if (count === 4) return 'col-12 col-md-6 col-xl-3';
        return 'col-12 col-md-4 col-xl-2';
    }

    function appendMultiModelResponse(userPrompt, fileContext, activeModelKeys) {
        const timestampStr = getFullTimestamp();
        const messageId    = 'multi-' + Date.now();
        const activeCount  = activeModelKeys?.length || 1;
        const colClass     = getKanbanColClass(activeCount);
        const skeletons    = Array.from({length: activeCount}, (_, i) => i);

        // Show skeleton loader while API is in-flight
        const skeletonHtml = `
            <div id="${messageId}" class="message ai multi-model-response animate__animated animate__fadeInUp w-100 pe-md-5">
                <div class="avatar"><i class="fa-solid fa-layer-group"></i></div>
                <div class="message-body w-100">
                    <div class="row g-2 kanban-container mb-3">
                        ${skeletons.map(i => `
                        <div class="${colClass}">
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
        scrollToBottom(true);

        // POST to real backend
        currentChatRequest = $.ajax({
            url: '/api/chat',
            method: 'POST',
            contentType: 'application/json',
            timeout: 300000, // 5 minutes
            data: JSON.stringify({
                prompt:          userPrompt,
                file_context:    fileContext,
                conversation_id: currentConversationId,
                persona_id:      activePersonaId,
            }),
            success: function(data) {
                const wasNew = !currentConversationId;
                currentConversationId = data.conversation_id;

                if (data.user_message_id) {
                    const $lastUserMsg = $(`.message.user[data-msg-idx="${userMessageIndex}"]`);
                    $lastUserMsg.attr('data-msg-id', data.user_message_id);
                    $lastUserMsg.find('.branch-msg-btn').attr('data-msg-id', data.user_message_id);
                }

                if (wasNew) {
                    window.loadConversations(); // Refresh list to show the new chat dynamically
                    if (data.uuid) {
                        window.history.pushState({}, '', `/c/${data.uuid}`);
                    }
                }

                const models    = data.models || [];
                const masterEval = data.master_eval;
                const masterName = data.master_model_name || 'Master Evaluation';

                // Build kanban columns from real responses
                const realColClass = getKanbanColClass(models.length || 1);
                const kanbanCols = models.map((model, idx) => {
                    const icon      = providerIcons[model.provider] || 'fa-microchip';
                    const className = kanbanClasses[idx] || 'kanban-bg-primary';
                    return `
                        <div class="${realColClass}">
                            <div class="kanban-card ${className}" id="${messageId}_col_${idx}">
                                <div class="kanban-header border-opacity-25 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa-solid ${icon} me-1"></i> ${escapeHtml(model.model_name)}
                                        ${model.error ? '<span class="badge bg-danger ms-2">Error</span>' : ''}
                                    </div>
                                    <button class="btn btn-link p-0 copy-kanban-btn text-secondary opacity-75 hover-opacity-100" title="Copy response"><i class="fa-regular fa-copy" style="font-size:0.8rem;"></i></button>
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
                        <div class="kanban-header text-danger bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
                            <div><i class="fa-solid fa-star me-1"></i> ${escapeHtml(masterName)}</div>
                            <button class="btn btn-link p-0 copy-kanban-btn text-danger opacity-75 hover-opacity-100" title="Copy response"><i class="fa-regular fa-copy" style="font-size:0.8rem;"></i></button>
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
                    let text = model.error
                        ? `⚠️ **Error:** ${model.error}`
                        : (model.content || '*(No response)*');

                    // Remove thinking/thought blocks if any leaked through
                    text = cleanseAiResponse(text);

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
            error: function(xhr, status) {
                if (status === 'abort') {
                    $(`#${messageId} .message-body`).html(`
                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 border-warning-subtle" role="alert">
                            <i class="fa-solid fa-hand text-warning"></i>
                            <div>Generation aborted by user.</div>
                        </div>
                    `);
                    return;
                }
                const errMsg = xhr.responseJSON?.messages?.error || xhr.responseJSON?.message || 'Failed to contact the AI backend.';
                $(`#${messageId} .message-body`).html(`
                    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>${escapeHtml(errMsg)}</div>
                    </div>
                `);
            },
            complete: function() {
                $('#send-btn').removeClass('d-none');
                $('#stop-btn').addClass('d-none');
                currentChatRequest = null;
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
                calculateTokens();
            };

            if (isText) {
                reader.readAsText(file);
            } else {
                file.content = '[Binary file — no text context]';
                attachedFiles.push(file);
                calculateTokens();
            }

            $chip.find('.chip-remove').on('click', function() {
                attachedFiles = attachedFiles.filter(f => f !== file);
                calculateTokens();
                $chip.addClass('chip-exit');
                setTimeout(() => $chip.remove(), 200);
            });

            $filePreviewArea.append($chip);
        });
    }


    function clearFiles() {
        attachedFiles = [];
        $filePreviewArea.empty();
        calculateTokens();
    }
    
    function calculateTokens() {
        const text = $chatInput.val().trim() || '';
        let totalChars = text.length;
        
        attachedFiles.forEach(f => {
            if (f.content) {
                totalChars += f.content.length;
            }
        });

        if (totalChars === 0) {
            $('#token-estimate-container').addClass('d-none');
            return;
        }

        $('#token-estimate-container').removeClass('d-none');
        // Rough heuristic: ~4 characters per token for English text/code
        const estimatedTokens = Math.ceil(totalChars / 4);
        
        // Cost heuristic (e.g. $2.50 per 1M input tokens, or $0.0025 per 1k)
        // using a somewhat generic blended cost for high end models 
        const costPer1k = 0.0025; 
        let estimatedCost = (estimatedTokens / 1000) * costPer1k;
        
        // Keep to 4 decimal places unless it's practically zero
        let costStr = estimatedCost.toFixed(4);
        if (estimatedCost < 0.0001 && estimatedCost > 0) {
            costStr = '< 0.0001';
        }

        $('#token-count-val').text(estimatedTokens.toLocaleString());
        $('#token-cost-val').text(`~$${costStr}`);
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

    function cleanseAiResponse(text) {
        if (!text) return '';
        // Remove XML-style thought tags (DeepSeek/Gemini patterns)
        let cleansed = text.replace(/<thought>[\s\S]*?<\/thought>/gi, '');
        // Remove common reasoning block markers
        cleansed = cleansed.replace(/\[THOUGHT\][\s\S]*?\[\/THOUGHT\]/gi, '');
        // Remove explicit "Okay, I will think..." style patterns if they are at the very start
        // but only if there is a clear separation or if it's overly verbose.
        // For safety, we prioritize tag removal as instructed by the backend prompt.
        return cleansed.trim();
    }

    function scrollToBottom(force = false) {
        if (!force && userHasScrolledUp) return;
        const scrollHeight = $chatContainer[0].scrollHeight;
        // Faster animation (200ms) prevents fighting with user's manual scroll intent
        $chatContainer.stop().animate({ scrollTop: scrollHeight }, 200); 
    }

    $('.toggle-sidebar').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
    });

    // --- All History Modal Logic ---
    let historyPage = 1;
    let historySearch = '';

    $('#allHistoryModal').on('show.bs.modal', function() {
        historyPage = 1;
        $('#historySearchInput').val('');
        historySearch = '';
        loadPaginatedHistory();
    });

    $('#historySearchInput').on('input', function() {
        historySearch = $(this).val();
        historyPage = 1; // reset page when searching
        if (window.historySearchTimer) clearTimeout(window.historySearchTimer);
        window.historySearchTimer = setTimeout(loadPaginatedHistory, 300);
    });

    $('#historyPrevBtn').on('click', function() {
        if (historyPage > 1) { historyPage--; loadPaginatedHistory(); }
    });

    $('#historyNextBtn').on('click', function() {
        historyPage++; loadPaginatedHistory();
    });

    function loadPaginatedHistory() {
        $('#allHistoryList').html('<div class="text-center text-muted my-4"><i class="fa-solid fa-circle-notch fa-spin fa-2x"></i></div>');
        $.getJSON(`/api/conversations/paginated?page=${historyPage}&search=${encodeURIComponent(historySearch)}`, function(res) {
            $('#allHistoryList').empty();
            if (!res.data || res.data.length === 0) {
                $('#allHistoryList').html('<div class="text-center text-muted my-4">No conversations found.</div>');
                $('#historyPageInfo').text('Page 0 of 0');
                $('#historyPrevBtn').prop('disabled', true);
                $('#historyNextBtn').prop('disabled', true);
                return;
            }
            
            res.data.forEach(function(conv) {
                const dateOptions = { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                const dateStr = new Date(conv.updated_at).toLocaleDateString('en-GB', dateOptions);
                const models = conv.models ? conv.models.split(',').join(' • ') : 'No AI interaction';
                const url = window.location.origin + '/c/' + conv.uuid;

                // Create unique URL link
                $('#allHistoryList').append(`
                    <div class="card shadow-sm border border-secondary border-opacity-10 rounded-3 mb-2">
                        <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="mb-1 text-truncate">
                                    <a href="${url}" class="text-decoration-none text-body fw-bold">${escapeHtml(conv.title || 'New Chat')}</a>
                                </h6>
                                <div class="text-muted small d-flex flex-wrap gap-3">
                                    <span><i class="fa-regular fa-clock me-1"></i> ${dateStr}</span>
                                    <span><i class="fa-solid fa-microchip me-1"></i> ${escapeHtml(models)}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <a href="${url}" class="btn btn-sm btn-primary rounded-pill px-3">Open</a>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="copyUniqueUrl('${url}', this)"><i class="fa-solid fa-link"></i></button>
                            </div>
                        </div>
                    </div>
                `);
            });

            $('#historyPageInfo').text(`Page ${res.page} of ${res.pages}`);
            $('#historyPrevBtn').prop('disabled', res.page <= 1);
            $('#historyNextBtn').prop('disabled', res.page >= res.pages);
        });
    }

    window.copyUniqueUrl = function(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const $btn = $(btn);
            const originalHtml = $btn.html();
            $btn.html('<i class="fa-solid fa-check"></i>');
            $btn.addClass('text-success border-success');
            setTimeout(() => {
                $btn.html(originalHtml);
                $btn.removeClass('text-success border-success');
            }, 2000);
        });
    }

    // --- Copy Actions ---
    $(document).on('click', '.copy-kanban-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const text = $btn.closest('.kanban-card, .master-evaluation-card').find('.kanban-body').text().trim();
        if (text) {
            navigator.clipboard.writeText(text).then(() => {
                const origHtml = $btn.html();
                $btn.html('<i class="fa-solid fa-check text-success" style="font-size:0.8rem;"></i>');
                setTimeout(() => $btn.html(origHtml), 2000);
            });
        }
    });

    $(document).on('click', '.copy-ai-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const text = $btn.closest('.message').find('.message-content').text().trim();
        if (text) {
            navigator.clipboard.writeText(text).then(() => {
                const origHtml = $btn.html();
                $btn.html('<i class="fa-solid fa-check text-success" style="font-size:0.8rem;"></i>');
                setTimeout(() => $btn.html(origHtml), 2000);
            });
        }
    });

    // --- Branching & Retry Logic ---
    $(document).on('click', '.retry-msg-btn', function(e) {
        e.preventDefault();
        const $msg = $(this).closest('.message.user');
        const text = $msg.find('.message-content').text().trim();
        if (text) {
            $chatInput.val(text);
            sendMessage();
        }
    });

    $(document).on('click', '.branch-msg-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const msgId = $btn.data('msg-id');
        const msgIdx = $btn.data('msg-idx');
        
        if (!currentConversationId) return;

        if (!confirm('Branch this conversation from this point? A new chat will be created starting with messages up to here.')) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        $.ajax({
            url: `/api/conversations/${currentConversationId}/branch`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ message_id: msgId }),
            success: function(res) {
                showToast('Conversation branched successfully!', 'success');
                // Navigate to the new conversation
                if (res.uuid) {
                    window.history.pushState({}, '', `/c/${res.uuid}`);
                    loadConversation(res.uuid);
                } else {
                    loadConversation(res.conversation_id);
                }
                // Refresh sidebar to show new branch
                window.loadConversations();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to branch conversation.';
                showToast(msg, 'danger');
                $btn.prop('disabled', false).html('<i class="fa-solid fa-code-branch" style="font-size:0.8rem;"></i>');
            }
        });
    });

    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0 animate__animated animate__fadeInRight" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('body').append(toastHtml);
        const $toast = $(`#${toastId}`);
        const bsToast = new bootstrap.Toast($toast[0], { delay: 3000 });
        bsToast.show();
        $toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    // --- Export Logic ---
    $(document).on('click', '.export-chat-btn', function(e) {
        e.preventDefault();
        // Fallback for new empty chat if no ID yet
        if (!currentConversationId) {
            alert('Please select or start a conversation first.');
            return;
        }

        const format = $(this).data('format');
        const $icon = $(this).find('i').first();
        const originalIconClass = $icon.attr('class');
        $icon.attr('class', 'fa-solid fa-circle-notch fa-spin me-2 opacity-50');

        $.getJSON(`/api/conversations/${currentConversationId}`, function(data) {
            const title = (data.title && data.title !== 'New Chat') ? data.title.replace(/[^a-z0-9]/gi, '_').toLowerCase() : 'chat_export';
            const filename = `${title}_${new Date().toISOString().slice(0,10)}`;

            if (format === 'json') {
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                downloadBlob(blob, `${filename}.json`);
                restoreIcon();
            } else if (format === 'yaml') {
                if (typeof jsyaml === 'undefined') {
                    alert('YAML generation library is loading. Please try again.');
                    restoreIcon();
                    return;
                }
                const yamlStr = jsyaml.dump(data);
                const blob = new Blob([yamlStr], { type: 'text/yaml' });
                downloadBlob(blob, `${filename}.yaml`);
                restoreIcon();
            } else if (format === 'xml') {
                let xmlStr = `<?xml version="1.0" encoding="UTF-8"?>\n<conversation>\n`;
                xmlStr += `  <title>${escapeXml(data.title || 'New Chat')}</title>\n`;
                xmlStr += `  <uuid>${escapeXml(data.uuid || '')}</uuid>\n`;
                xmlStr += `  <created_at>${escapeXml(data.created_at || '')}</created_at>\n`;
                xmlStr += `  <messages>\n`;
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        xmlStr += `    <message>\n`;
                        xmlStr += `      <role>${escapeXml(msg.role)}</role>\n`;
                        if (msg.model_name) xmlStr += `      <model_name>${escapeXml(msg.model_name)}</model_name>\n`;
                        xmlStr += `      <content>${escapeXml(msg.content)}</content>\n`;
                        xmlStr += `      <created_at>${escapeXml(msg.created_at || '')}</created_at>\n`;
                        xmlStr += `    </message>\n`;
                    });
                }
                xmlStr += `  </messages>\n</conversation>`;
                const blob = new Blob([xmlStr], { type: 'application/xml' });
                downloadBlob(blob, `${filename}.xml`);
                restoreIcon();
            } else if (format === 'pdf') {
                if (typeof html2pdf === 'undefined') {
                    alert('PDF generation library is loading. Please try again in a moment.');
                    restoreIcon();
                    return;
                }
                
                const element = document.createElement('div');
                element.style.padding = '20px';
                element.style.fontFamily = 'Arial, sans-serif';
                element.style.color = '#333';
                element.style.lineHeight = '1.6';
                
                let html = `<h2 style="margin-bottom:20px; font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:10px;">${escapeHtml(data.title || 'Conversation')}</h2>`;
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const sender = msg.role === 'user' ? '<b>User</b>' : `<b>${escapeHtml(msg.model_name || 'AI')}</b>`;
                        html += `<div style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 8px; background: ${msg.role === 'user' ? '#fdfdfd' : '#f4fbff'}; page-break-inside: avoid;">`;
                        html += `<div style="margin-bottom: 10px; font-size: 0.9em; color: #555;">${sender}</div>`;
                        // parse markdown but apply some reset styles so it looks ok without Bootstrap
                        let parsed = marked.parse(msg.content);
                        parsed = parsed.replace(/<pre>/g, '<pre style="background:#2d2d2d; color:#ccc; padding:15px; border-radius:5px; overflow-x:auto;">');
                        parsed = parsed.replace(/<code>/g, '<code style="font-family:monospace; background:rgba(0,0,0,0.05); padding:2px 4px; border-radius:3px;">');
                        html += `<div>${parsed}</div>`;
                        html += `</div>`;
                    });
                } else {
                    html += '<p>No messages.</p>';
                }
                element.innerHTML = html;
                
                const opt = {
                    margin:       10,
                    filename:     `${filename}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                html2pdf().set(opt).from(element).save().then(() => {
                    restoreIcon();
                }).catch(err => {
                    console.error("PDF generation failed", err);
                    alert("Failed to generate PDF. Check console.");
                    restoreIcon();
                });
            }

            function restoreIcon() {
                $icon.attr('class', originalIconClass);
            }
        }).fail(function() {
            alert('Failed to fetch conversation data for export.');
            $icon.attr('class', originalIconClass);
        });
    });

    // --- Multi-Persona Management ---
    function loadPersonas() {
        $.getJSON('/api/personas', function(data) {
            // Populate Settings List
            const $list = $('#personas-list-container');
            $list.empty();
            
            // Populate Chat Dropdown
            const $dropdown = $('#persona-list-dropdown');
            $dropdown.find('li:gt(1)').remove(); // Keep header and divider

            if (data.length === 0) {
                $list.append('<div class="text-center py-3 text-muted small">No custom personas yet. Create one to get started!</div>');
                $dropdown.append('<li><a class="dropdown-item small text-muted" href="#">No personas found</a></li>');
            } else {
                data.forEach(persona => {
                    const isDefault = persona.is_default == 1;
                    if (isDefault && activePersonaId === 0 && !currentConversationId) {
                        activePersonaId = persona.id;
                        $('#current-persona-name').text(persona.name);
                    }

                    // Settings List Item
                    $list.append(`
                        <div class="persona-item d-flex align-items-center justify-content-between p-2 mb-2 border border-secondary border-opacity-10 rounded bg-black bg-opacity-10 shadow-sm">
                            <div class="flex-grow-1 min-width-0 pe-2">
                                <div class="fw-semibold small text-truncate">${escapeHtml(persona.name)} ${isDefault ? '<span class="badge bg-primary transform-scale-75 origin-left">Default</span>' : ''}</div>
                                <div class="text-muted small text-truncate" style="font-size: 0.7rem;">${escapeHtml(persona.instructions)}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary edit-persona-btn p-1 px-2" data-id="${persona.id}" data-name="${escapeHtml(persona.name)}" data-instructions="${escapeHtml(persona.instructions)}" data-default="${persona.is_default}"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm btn-outline-danger delete-persona-btn p-1 px-2" data-id="${persona.id}"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    `);

                    // Dropdown Item
                    $dropdown.append(`
                        <li><a class="dropdown-item persona-select-item d-flex justify-content-between align-items-center py-2" href="#" data-id="${persona.id}" data-name="${escapeHtml(persona.name)}">
                            ${escapeHtml(persona.name)}
                            ${persona.id == activePersonaId ? '<i class="fa-solid fa-check text-success ms-2"></i>' : ''}
                        </a></li>
                    `);
                });
            }
            
            // Add "Default" option to dropdown
            $dropdown.prepend(`<li><a class="dropdown-item persona-select-item" href="#" data-id="0" data-name="Default Assistant">Default Assistant ${activePersonaId == 0 ? '<i class="fa-solid fa-check text-success ms-2"></i>' : ''}</a></li>`);
        });
    }

    // Initial Load
    loadPersonas();

    // Toggle Editor
    $('#add-persona-btn').on('click', function() {
        $('#persona-editor-title').text('Create New Persona');
        $('#edit-persona-id').val('0');
        $('#edit-persona-name').val('');
        $('#edit-persona-instructions').val('');
        $('#edit-persona-default').prop('checked', false);
        $('#persona-editor-card').removeClass('d-none').addClass('animate__animated animate__fadeInDown');
        $(this).fadeOut(200);
    });

    $('#cancel-persona-btn').on('click', function() {
        $('#persona-editor-card').addClass('d-none');
        $('#add-persona-btn').fadeIn(200);
    });

    $(document).on('click', '.edit-persona-btn', function() {
        const d = $(this).data();
        $('#persona-editor-title').text('Edit Persona');
        $('#edit-persona-id').val(d.id);
        $('#edit-persona-name').val(d.name);
        $('#edit-persona-instructions').val(d.instructions);
        $('#edit-persona-default').prop('checked', d.default == 1);
        $('#persona-editor-card').removeClass('d-none');
        $('#add-persona-btn').fadeOut(200);
    });

    $('#save-persona-btn').on('click', function() {
        const data = {
            id: $('#edit-persona-id').val(),
            name: $('#edit-persona-name').val(),
            instructions: $('#edit-persona-instructions').val(),
            is_default: $('#edit-persona-default').is(':checked')
        };

        if (!data.name || !data.instructions) {
            showToast('Please fill in all fields.', 'warning');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '/api/personas',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function() {
                showToast('Persona saved successfully!', 'success');
                $('#persona-editor-card').addClass('d-none');
                $('#add-persona-btn').show();
                loadPersonas();
            },
            error: function() { showToast('Failed to save persona.', 'danger'); },
            complete: function() { $btn.prop('disabled', false).text('Save Persona'); }
        });
    });

    $(document).on('click', '.delete-persona-btn', function() {
        if (!confirm('Are you sure you want to delete this persona?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `/api/personas/${id}`,
            method: 'DELETE',
            success: function() {
                showToast('Persona deleted.', 'success');
                loadPersonas();
            }
        });
    });

    // Selecting Persona
    $(document).on('click', '.persona-select-item', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        activePersonaId = id;
        $('#current-persona-name').text(name);
        
        // Visual indicator in dropdown
        $('.persona-select-item i.fa-check').remove();
        $(this).append('<i class="fa-solid fa-check text-success ms-2"></i>');

        // If in an active conversation, we could optionally update the DB persona_id immediately
        // but for now we'll do it on the next message send.
    });

    function downloadBlob(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    }
    
    function escapeXml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString().replace(/[<>&'"]/g, function (c) {
            switch (c) {
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '&': return '&amp;';
                case '\'': return '&apos;';
                case '"': return '&quot;';
            }
        });
    }
});
