<div id="ai-chat-widget">
    <div id="chat-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M21 11.5c0-4.42-3.58-8-8-8S5 7.08 5 11.5c0 2.61 1.43 4.93 3.59 6.32L8 20v-2.5c-4.32-.8-7.5-3.53-7.5-6C.5 7.01 4.51 3 9.5 3s9 4.01 9 8.5c0 3.31-2.69 6-6 6h-1.5l-2.09 2.09c-.25.25-.59.39-.94.39-.36 0-.7-.14-.95-.39-.5-.5-.5-1.31 0-1.81L9.19 18H12c3.31 0 6-2.69 6-6zM9.5 15c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm5 0c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
    </div>
    <div id="chat-window">
        <div id="chat-header">
            <div class="chat-title">
                <img src="https://brightbridge.uz/public/upl/logo.png" alt="Logo">
                <h3><strong>Bright</strong>Bridge</h3>
            </div>
            <span id="close-chat">&times;</span>
        </div>
        <div id="chat-messages">
           <div class="bot-message message">
                Salom! Men BrightBridge AI yordamchisiman. Sizga qanday yordam bera olaman?
            </div>
        </div>
        <div id="chat-input">
            <input type="text" id="chat-input-field" placeholder="Xabaringizni yozing...">
            <button id="chat-send-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatIcon = document.getElementById('chat-icon');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');

    if (chatIcon && chatWindow && closeChat) {
        chatIcon.addEventListener('click', function() {
            chatWindow.classList.toggle('open');
            chatIcon.classList.toggle('hidden');
        });

        closeChat.addEventListener('click', function() {
            chatWindow.classList.remove('open');
            chatIcon.classList.remove('hidden');
        });
    }
});
</script>