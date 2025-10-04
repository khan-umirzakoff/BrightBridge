<link rel="stylesheet" href="{{ asset('css/ai_chat_widget.css') }}">

<div id="ai-chat-widget">
    <button id="chat-icon" aria-label="Chatni ochish">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M21 11.5c0-4.42-3.58-8-8-8S5 7.08 5 11.5c0 2.61 1.43 4.93 3.59 6.32L8 20v-2.5c-4.32-.8-7.5-3.53-7.5-6C.5 7.01 4.51 3 9.5 3s9 4.01 9 8.5c0 3.31-2.69 6-6 6h-1.5l-2.09 2.09c-.25.25-.59.39-.94.39-.36 0-.7-.14-.95-.39-.5-.5-.5-1.31 0-1.81L9.19 18H12c3.31 0 6-2.69 6-6zM9.5 15c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm5 0c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
    </button>
    <div id="chat-window" role="dialog" aria-modal="true" aria-labelledby="chat-header-title">
        <div id="chat-header">
            <div class="chat-title">
                <img src="{{ asset('upl/1111.png') }}" alt="BrightBridge Logo" class="chat-logo">
                <div class="brand-text">
                    <h2 id="chat-header-title" class="ai-title">AI Assistant</h2>
                    <div class="by-line">
                        <span class="by-text">by</span> <span class="brightbridge-text">BrightBridge</span>
                    </div>
                </div>
            </div>
            <button id="close-chat" aria-label="Chatni yopish">&times;</button>
        </div>
        <div id="chat-messages" role="log" aria-live="polite">
           <div class="bot-message message">
                Salom! Men BrightBridge AI yordamchisiman. Sizga qanday yordam bera olaman?
            </div>
        </div>
        <div id="chat-input">
            <input type="text" id="chat-input-field" placeholder="Xabar yozing..." aria-label="Xabar kiritish maydoni">
            <button id="chat-send-button" aria-label="Xabarni yuborish">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWidget = document.getElementById('ai-chat-widget');
    const chatIcon = document.getElementById('chat-icon');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');
    const chatInputField = document.getElementById('chat-input-field');
    const chatSendButton = document.getElementById('chat-send-button');
    const chatMessages = document.getElementById('chat-messages');

    if (chatIcon && chatWindow && closeChat) {
        // Chat ikonkasini bosganda oynani ochish/yopish
        chatIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            chatWindow.classList.toggle('open');
            chatIcon.classList.toggle('hidden');
        });

        // "X" tugmasini bosganda oynani yopish
        closeChat.addEventListener('click', function() {
            chatWindow.classList.remove('open');
            chatIcon.classList.remove('hidden');
        });

        // Oyna tashqarisiga bosganda oynani yopish
        document.addEventListener('click', function(event) {
            if (chatWindow.classList.contains('open') && !chatWidget.contains(event.target)) {
                chatWindow.classList.remove('open');
                chatIcon.classList.remove('hidden');
            }
        });
    }

    // Xabar yuborish funksiyasi
    function sendMessage() {
        const message = chatInputField.value.trim();
        if (message === '') return;

        // Foydalanuvchi xabarini qo'shish
        const userMessageDiv = document.createElement('div');
        userMessageDiv.className = 'user-message message';
        userMessageDiv.textContent = message;
        chatMessages.appendChild(userMessageDiv);

        // Input maydonini tozalash
        chatInputField.value = '';

        // Xabarlar oynasini pastga surish
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Bot javobini simulyatsiya qilish (demo uchun)
        setTimeout(() => {
            const botMessageDiv = document.createElement('div');
            botMessageDiv.className = 'bot-message message';
            botMessageDiv.textContent = 'Rahmat xabaringiz uchun! Men hozirda ishlab chiqilish jarayonidaman.';
            chatMessages.appendChild(botMessageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 1000);
    }

    // Enter tugmasi bosilganda xabar yuborish
    if (chatInputField) {
        chatInputField.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
    }

    // Yuborish tugmasi bosilganda xabar yuborish
    if (chatSendButton) {
        chatSendButton.addEventListener('click', function() {
            sendMessage();
        });
    }
});
</script>