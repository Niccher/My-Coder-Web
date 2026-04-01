$(document).ready(function() {
    const $chatInput = $('#chat-input');
    const $chatContainer = $('#chat-container');

    // Theme Logic
    const savedTheme = localStorage.getItem('chat-theme') || 'solarized';
    applyTheme(savedTheme);

    $('.theme-menu .dropdown-item').on('click', function(e) {
        e.preventDefault();
        const theme = $(this).data('theme');
        applyTheme(theme);
        localStorage.setItem('chat-theme', theme);
    });

    function applyTheme(theme) {
        $('body').attr('data-theme', theme);
        // Update dropdown menu style based on theme
        if (theme === 'white') {
            $('.dropdown-menu').removeClass('dropdown-menu-dark');
        } else {
            $('.dropdown-menu').addClass('dropdown-menu-dark');
        }
    }

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

        // Append user message
        appendMessage('user', message);
        $chatInput.val('').css('height', 'auto');

        // Simulate AI response (demo only)
        setTimeout(() => {
            appendMessage('ai', 'This is a demo response. You can connect your backend AI here.');
        }, 1000);
    }

    function appendMessage(role, text) {
        const avatar = role === 'user' ? 'U' : 'AI';
        const messageHtml = `
            <div class="message ${role} animate__animated animate__fadeInUp">
                <div class="avatar">${avatar}</div>
                <div class="message-content">${text}</div>
            </div>
        `;
        $chatContainer.append(messageHtml);
        
        // Scroll to bottom
        $chatContainer.animate({
            scrollTop: $chatContainer[0].scrollHeight
        }, 500);
    }

    // Sidebar toggle (for mobile)
    $('.toggle-sidebar').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
    });
});
