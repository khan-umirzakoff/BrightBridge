<div id="ai-chat-widget">
    <div id="chat-icon">
        <svg fill="#FFFFFF" height="30px" width="30px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
	 viewBox="0 0 416.979 416.979" xml:space="preserve">
<g>
	<path d="M356.004,61.156c-81.37-81.47-213.375-81.551-294.844-0.182c-81.47,81.37-81.552,213.375-0.182,294.844
		c40.735,40.783,94.322,61.156,147.411,61.156c53.089,0,106.676-20.373,147.443-61.188
	C437.474,274.631,437.373,142.626,356.004,61.156z M237.6,340.786c0.391,4.363-2.933,8.198-7.296,8.589
	c-4.363,0.391-8.198-2.933-8.589-7.296v-0.569c-0.035-0.569-0.06-1.139-0.06-1.708c0-43.156-34.918-78.074-78.074-78.074
	c-0.569,0-1.139,0.025-1.708,0.06v-0.569c-4.363-0.391-7.687-4.226-7.296-8.589c0.391-4.363,4.226-7.687,8.589-7.296h0.569
	c0.584-0.025,1.168-0.035,1.74-0.035c52.073,0,94.37,42.297,94.37,94.37C237.66,339.647,237.635,340.217,237.6,340.786z
	 M299.317,218.452c-2.527-2.457-6.546-2.396-8.99,0.146c-13.564,14.136-32.308,22.424-52.02,22.424
	c-19.728,0-38.488-8.288-52.043-22.424c-2.444-2.542-6.463-2.603-8.99-0.146c-2.527,2.457-2.603,6.463-0.146,8.99
	c16.204,16.85,39.6,26.862,61.179,26.862s44.975-10.012,61.179-26.862C301.92,224.915,301.844,220.909,299.317,218.452z"/>
</g>
</svg>
    </div>
    <div id="chat-window">
        <div id="chat-header">
            <div class="chat-title">
                <img src="{{ asset('upl/1111.png') }}" alt="JobCare Logo">
                <h3>Assistant by <strong>JobCare</strong></h3>
            </div>
            <span id="close-chat">&times;</span>
        </div>
        <div id="chat-messages">
            <div class="message bot-message">
                Salom! Men JobCare AI yordamchingizman. Sizga qanday yordam bera olaman?
            </div>
        </div>
        <div id="chat-input">
            <input type="text" id="user-input" placeholder="Xabar yozing...">
            <button id="send-message">
                <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: white;"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatIcon = document.getElementById('chat-icon');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');
    const sendMessage = document.getElementById('send-message');
    const userInput = document.getElementById('user-input');
    const chatMessages = document.getElementById('chat-messages');

    chatIcon.addEventListener('click', () => {
        chatWindow.classList.add('open');
        chatIcon.classList.add('hidden');
    });

    closeChat.addEventListener('click', () => {
        chatWindow.classList.remove('open');
        chatIcon.classList.remove('hidden');
    });

    const handleSendMessage = () => {
        const messageText = userInput.value.trim();
        if (messageText) {
            // Display user message
            const userMessage = document.createElement('div');
            userMessage.className = 'message user-message';
            userMessage.textContent = messageText;
            chatMessages.appendChild(userMessage);

            userInput.value = '';
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // TODO: Backend call will be here
        }
    };

    sendMessage.addEventListener('click', handleSendMessage);
    userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleSendMessage();
        }
    });
});
</script>
