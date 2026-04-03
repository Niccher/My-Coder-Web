$(document).ready(function() {
    const $chatInput = $('#chat-input');
    const $chatContainer = $('#chat-container');

    // Theme Logic
    const savedTheme = localStorage.getItem('chat-theme') || 'solarized';
    applyTheme(savedTheme);

    $('.theme-menu .dropdown-item, .theme-selector-modal .theme-card').on('click', function(e) {
        e.preventDefault();
        const theme = $(this).data('theme');
        applyTheme(theme);
        localStorage.setItem('chat-theme', theme);
        
        // Update active class for theme cards
        if ($(this).hasClass('theme-card')) {
            $('.theme-card').removeClass('active');
            $(this).addClass('active');
        }
    });

    function applyTheme(theme) {
        $('body').attr('data-theme', theme);
        // Sync active class in modal if it's open
        $('.theme-card').removeClass('active');
        $(`.theme-card[data-theme="${theme}"]`).addClass('active');
        
        // Update dropdown menu style based on theme
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

    function sendMessage() {
        const message = $chatInput.val().trim();
        if (message === '') return;

        appendMessage('user', message);
        $chatInput.val('').css('height', 'auto');

        // Animate greeting text away
        if ($('#greeting-box').length) {
            $('#greeting-box').fadeOut(300, function() {
                $(this).remove();
            });
        }

        // Simulate backend multi-model processing
        setTimeout(() => {
            appendMultiModelResponse(message);
        }, 600);
    }

    function appendMessage(role, text) {
        const avatar = role === 'user' ? 'U' : 'AI';
        const date = new Date();
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit' };
        const timestampStr = `${date.toLocaleDateString('en-GB', dateOptions)}, ${date.toLocaleTimeString([], timeOptions)}`;
        
        const messageHtml = `
            <div class="message ${role} animate__animated animate__fadeInUp">
                <div class="avatar">${avatar}</div>
                <div class="message-body">
                    <div class="message-content">${text}</div>
                    <div class="message-timestamp">${timestampStr}</div>
                </div>
            </div>
        `;
        $chatContainer.append(messageHtml);
        scrollToBottom();
    }

    function appendMultiModelResponse(userPrompt) {
        const date = new Date();
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit' };
        const timestampStr = `${date.toLocaleDateString('en-GB', dateOptions)}, ${date.toLocaleTimeString([], timeOptions)}`;

        const messageId = 'multi-' + Date.now();
        
        // Step 1: Append skeleton loading state
        const skeletonHtml = `
            <div id="${messageId}" class="message ai multi-model-response animate__animated animate__fadeInUp w-100 pe-md-5">
                <div class="avatar"><i class="fa-solid fa-layer-group"></i></div>
                <div class="message-body w-100">
                    <div class="row g-3 kanban-container mb-3">
                        ${[1,2,3].map(i => `
                        <div class="col-md-4">
                            <div class="kanban-card">
                                <div class="kanban-header">
                                    <i class="fa-solid fa-circle-notch fa-spin me-2 opacity-50"></i> Querying Model ${i}...
                                </div>
                                <div class="kanban-body">
                                    <div class="skeleton-line" style="width: 80%"></div>
                                    <div class="skeleton-line" style="width: 100%"></div>
                                    <div class="skeleton-line" style="width: 60%"></div>
                                    <div class="skeleton-line mt-3" style="width: 90%"></div>
                                    <div class="skeleton-line" style="width: 30%"></div>
                                </div>
                            </div>
                        </div>`).join('')}
                    </div>
                    <div class="message-timestamp">${timestampStr}</div>
                </div>
            </div>
        `;
        $chatContainer.append(skeletonHtml);
        scrollToBottom();

        // Step 2: Simulate backend returning the 3 models and master evaluation
        setTimeout(() => {
            $(`#${messageId} .message-body`).html(`
                <div class="row g-2 kanban-container mb-3">
                    <!-- Column 1: Gemini -->
                    <div class="col-md-4">
                        <div class="kanban-card kanban-bg-primary">
                            <div class="kanban-header text-primary border-primary border-opacity-25">
                                <i class="fa-solid fa-microchip me-1"></i> Gemini 1.5
                            </div>
                            <div class="kanban-body">
                                Here is my interpretation: "${userPrompt}" looks like a direct inquiry. I suggest breaking this down into smaller actionable steps.
                            </div>
                        </div>
                    </div>
                    <!-- Column 2: DeepSeek -->
                    <div class="col-md-4">
                        <div class="kanban-card kanban-bg-success">
                            <div class="kanban-header text-success border-success border-opacity-25">
                                <i class="fa-solid fa-microchip me-1"></i> DeepSeek R1
                            </div>
                            <div class="kanban-body">
                                Based on standard analytic protocols, "${userPrompt}" requires algorithmic sorting. The optimal solution is O(n log n).
                            </div>
                        </div>
                    </div>
                    <!-- Column 3: Grok -->
                    <div class="col-md-4">
                        <div class="kanban-card kanban-bg-info">
                            <div class="kanban-header text-info border-info border-opacity-25">
                                <i class="fa-solid fa-microchip me-1"></i> Grok-2
                            </div>
                            <div class="kanban-body">
                                Let's get real here: "${userPrompt}" is just the tip of the iceberg. The most efficient and witty approach is clearly a recursive fun-loop.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Master Evaluation -->
                <div class="master-evaluation-card mb-2 shadow-sm">
                    <div class="kanban-header text-danger bg-danger bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-brain me-1"></i> Master Evaluation (GPT-4o)</span>
                            <span class="badge bg-danger rounded-pill">Selected: DeepSeek R1</span>
                        </div>
                    </div>
                    <div class="kanban-body">
                        The responses vary in tone and technical depth. Gemini 1.5 is cautious, while Grok-2 is overly colloquial. 
                        <strong>DeepSeek R1</strong> provides the most robust and technically accurate direct answer. The optimal algorithmic approach it suggests should be utilized.
                    </div>
                </div>
                
                <div class="message-timestamp">${timestampStr}</div>
            `);
            scrollToBottom();
        }, 3000);
    }

    function scrollToBottom() {
        $chatContainer.animate({
            scrollTop: $chatContainer[0].scrollHeight
        }, 500);
    }

    $('.toggle-sidebar').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
    });
});
