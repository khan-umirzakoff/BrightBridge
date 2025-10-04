
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

    :root {
        --chat-primary-color: #0d2d62;
        --chat-secondary-color: #00b894;
        --chat-font: 'Poppins', sans-serif;
    }

    #ai-chat-widget, #ai-chat-widget * {
        font-family: var(--chat-font);
        box-sizing: border-box;
    }

    #ai-chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }

    #chat-icon {
        width: 60px;
        height: 60px;
        background-color: var(--chat-primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s;
        opacity: 1;
        transform: scale(1);
    }

    #chat-icon.hidden {
        opacity: 0;
        transform: scale(0.5);
        pointer-events: none;
    }

    #chat-icon:hover {
        background-color: var(--chat-secondary-color);
    }

    #chat-icon svg {
        width: 30px;
        height: 30px;
        fill: white;
    }

    #chat-window {
        width: 360px;
        height: 520px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background-color: #ffffff;
        transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.4s ease;
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
    }

    #chat-window.open {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    #chat-header {
        padding: 12px 20px;
        background-color: var(--chat-primary-color);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .chat-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-title img {
        width: 30px;
        height: 30px;
        object-fit: contain;
    }

    #chat-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
     #chat-header h3 strong {
        font-weight: 700;
        color: var(--chat-secondary-color);
    }

    #close-chat {
        cursor: pointer;
        font-size: 28px;
        font-weight: 300;
        opacity: 0.8;
        transition: opacity 0.2s;
        padding: 0 10px;
    }
    #close-chat:hover {
        opacity: 1;
    }

    #chat-messages {
        flex-grow: 1;
        padding: 15px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message {
        padding: 10px 15px;
        border-radius: 20px;
        max-width: 85%;
        word-wrap: break-word;
        line-height: 1.5;
    }

    .user-message {
        background-color: var(--chat-secondary-color);
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 5px;
    }

    .bot-message {
        background-color: #f1f1f1;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 5px;
    }

    #chat-input {
        display: flex;
        padding: 12px;
        border-top: 1px solid #e0e0e0;
        align-items: center;
        flex-shrink: 0;
    }

    #chat-input input {
        flex-grow: 1;
        border: none;
        background: #f0f0f0;
        border-radius: 20px;
        padding: 10px 18px;
        font-size: 14px;
        margin-right: 10px;
        outline: none;
        transition: box-shadow 0.2s;
    }
    #chat-input input:focus {
        box-shadow: 0 0 0 2px rgba(0, 184, 148, 0.5);
    }

    #chat-input button {
        background-color: var(--chat-secondary-color);
        border: none;
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s;
    }

    #chat-input button:hover {
        background-color: #009c7c;
    }
</style>

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
