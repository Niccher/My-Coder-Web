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
    $('#openSettings').on('click', function() {
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

        setTimeout(() => {
            appendMessage('ai', 'This is a demo response. You can connect your backend AI here.');
        }, 1000);
    }

    function appendMessage(role, text) {
        const avatar = role === 'user' ? 'U' : 'AI';
        const date = new Date();
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit' };
        const dateStr = date.toLocaleDateString('en-GB', dateOptions);
        const timeStr = date.toLocaleTimeString([], timeOptions);
        const timestampStr = `${dateStr}, ${timeStr}`;
        
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
        
        $chatContainer.animate({
            scrollTop: $chatContainer[0].scrollHeight
        }, 500);
    }

    $('.toggle-sidebar').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
    });
});
