<link rel="stylesheet" href="{{ asset('css/ai_chat_widget.css') }}">

<div id="ai-chat-widget">
    <button id="chat-icon" aria-label="Chatni ochish">
        <img src="{{ asset('upl/1111.png') }}" alt="Chatni ochish">
        <div class="chat-icon-text">
            <span class="chat-icon-line1">JobCare</span>
            <span class="chat-icon-line2">Assistant</span>
        </div>
    </button>
    <div id="chat-window" role="dialog" aria-modal="true" aria-labelledby="chat-header-title">
        <div id="chat-header">
            <div class="chat-title">
                <img src="{{ asset('upl/1111.png') }}" alt="JobCare Logo" class="chat-logo">
                <div class="brand-text">
                    <h2 id="chat-header-title" class="ai-title">JobCare Assistant</h2>
                </div>
            </div>
            <button id="clear-chat" aria-label="Yangi suhbat" style="margin-right: 10px;" title="Yangi suhbat boshlash">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                </svg>
            </button>
            <button id="close-chat" aria-label="Chatni yopish">&times;</button>
        </div>
        <div id="chat-messages" role="log" aria-live="polite">
           <div class="bot-message message">
                Salom! Men JobCare AI yordamchisiman. Sizga qanday yordam bera olaman?
            </div>
        </div>
        
        <!-- New Chat Modal -->
        <div id="new-chat-modal" class="new-chat-modal">
            <div class="modal-content-new">
                <h3>Yangi Suhbat Boshlash</h3>
                <p>Joriy suhbat tarixi o'chiriladi</p>
                <button class="btn-new-chat" id="confirm-new-chat">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    New Chat
                </button>
                <button class="btn-cancel" id="cancel-new-chat">Cancel</button>
            </div>
        </div>

        <div id="chat-input">
            <input type="file" id="chat-image-input" accept="image/*" style="display:none;">
            <button id="chat-image-button" aria-label="Rasm yuborish" title="Rasm yuklash">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </button>
            <input type="text" id="chat-input-field" placeholder="Xabar yozing..." aria-label="Xabar kiritish maydoni">
            <button id="chat-send-button" aria-label="Xabarni yuborish">
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
    const clearChat = document.getElementById('clear-chat');
    const chatInputField = document.getElementById('chat-input-field');
    const chatSendButton = document.getElementById('chat-send-button');
    const chatImageButton = document.getElementById('chat-image-button');
    const chatImageInput = document.getElementById('chat-image-input');
    const chatMessages = document.getElementById('chat-messages');

    if (chatIcon && chatWindow && closeChat) {
        // Asosiy tugmani bosganda oynani ochish/yopish
        chatIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            chatWindow.classList.toggle('open');
        });

        // "X" tugmasini bosganda oynani yopish
        closeChat.addEventListener('click', function() {
            chatWindow.classList.remove('open');
        });

        // Yangi suhbat modal
        const newChatModal = document.getElementById('new-chat-modal');
        const confirmNewChat = document.getElementById('confirm-new-chat');
        const cancelNewChat = document.getElementById('cancel-new-chat');

        if (clearChat) {
            clearChat.addEventListener('click', function() {
                newChatModal.style.display = 'flex';
            });
        }

        if (confirmNewChat) {
            confirmNewChat.addEventListener('click', function() {
                clearChatHistory();
                newChatModal.style.display = 'none';
            });
        }

        if (cancelNewChat) {
            cancelNewChat.addEventListener('click', function() {
                newChatModal.style.display = 'none';
            });
        }

        // User scroll detection
        chatMessages.addEventListener('scroll', function() {
            const isAtBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 50;
            
            if (!isAtBottom) {
                isUserScrolling = true;
                
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    isUserScrolling = false;
                }, 2000);
            } else {
                isUserScrolling = false;
            }
        });

        // Rasm yuborish
        if (chatImageButton && chatImageInput) {
            chatImageButton.addEventListener('click', () => {
                chatImageInput.click();
            });

            chatImageInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files[0]) {
                    const file = e.target.files[0];
                    
                    // Rasm preview - chat messages da ko'rsatish
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const imagePreview = document.createElement('div');
                        imagePreview.className = 'user-message message image-preview-msg';
                        imagePreview.id = 'temp-image-preview';
                        imagePreview.innerHTML = `
                            <div style="position:relative;display:inline-block;">
                                <img src="${event.target.result}" style="max-width:200px;max-height:200px;border-radius:12px;display:block;">
                                <button onclick="this.closest('.image-preview-msg').remove();document.getElementById('chat-image-input').value='';" 
                                    style="position:absolute;top:-6px;right:-6px;background:#0d2d62;color:white;border:2px solid white;border-radius:50%;width:22px;height:22px;cursor:pointer;font-size:12px;line-height:1;box-shadow:0 2px 8px rgba(0,0,0,0.2);display:flex;align-items:center;justify-content:center;">×</button>
                            </div>
                        `;
                        chatMessages.appendChild(imagePreview);
                        forceScroll();
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // Smart scroll - faqat user scroll qilmasa (throttled)
    function smartScroll() {
        scrollCounter++;
        // Har 5-chi chunk da scroll (sekinroq)
        if (!isUserScrolling && scrollCounter % 5 === 0) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Force scroll (xabar tugaganda)
    function forceScroll() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Chat history saqlash (LocalStorage dan yuklash)
    let chatHistory = loadChatHistory();
    
    // Auto-scroll state
    let isUserScrolling = false;
    let scrollTimeout = null;
    let scrollCounter = 0;

    // LocalStorage funksiyalari
    function saveChatHistory() {
        try {
            localStorage.setItem('jobcare_ai_history', JSON.stringify(chatHistory));
        } catch (e) {
            console.error('LocalStorage xato:', e);
        }
    }

    function loadChatHistory() {
        try {
            const saved = localStorage.getItem('jobcare_ai_history');
            if (saved) {
                const history = JSON.parse(saved);
                
                // Eski xabarlarni UI ga qayta yuklash
                setTimeout(() => {
                    history.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = msg.role === 'user' ? 'user-message message' : 'bot-message message';
                        div.innerHTML = msg.role === 'user' ? msg.text : formatMarkdown(msg.text);
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 100);
                
                return history;
            }
        } catch (e) {
            console.error('LocalStorage yuklash xato:', e);
        }
        return [];
    }

    function clearChatHistory() {
        chatHistory = [];
        localStorage.removeItem('jobcare_ai_history');
        
        // UI dan ham o'chirish (boshlang'ich xabardan tashqari)
        const messages = chatMessages.querySelectorAll('.message');
        messages.forEach((msg, index) => {
            if (index > 0) msg.remove(); // Birinchi bot xabarni saqlaymiz
        });
    }

    // Markdown to HTML converter (Full Support)
    function formatMarkdown(text) {
        if (!text) return '';
        
        // Code blocks ```code``` -> <pre>code</pre>
        text = text.replace(/```([\s\S]+?)```/g, '<pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:6px;overflow-x:auto;margin:8px 0;"><code>$1</code></pre>');
        
        // Inline code `code` -> <code>code</code>
        text = text.replace(/`(.+?)`/g, '<code style="background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;color:#e83e8c;">$1</code>');
        
        // **bold** -> <strong>bold</strong>
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        
        // *italic* -> <em>italic</em>
        text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
        
        // Headers ### -> <h3>
        text = text.replace(/^### (.+)$/gm, '<h3 style="font-size:1.1rem;font-weight:600;margin:12px 0 8px 0;">$1</h3>');
        text = text.replace(/^## (.+)$/gm, '<h2 style="font-size:1.2rem;font-weight:600;margin:14px 0 10px 0;">$1</h2>');
        text = text.replace(/^# (.+)$/gm, '<h1 style="font-size:1.3rem;font-weight:600;margin:16px 0 12px 0;">$1</h1>');
        
        // Lists - (.+) -> <li>item</li>
        text = text.replace(/^- (.+)$/gm, '<li style="margin-left:20px;">$1</li>');
        text = text.replace(/^(\d+)\. (.+)$/gm, '<li style="margin-left:20px;list-style-type:decimal;">$2</li>');
        
        // Blockquote > text -> <blockquote>
        text = text.replace(/^> (.+)$/gm, '<blockquote style="border-left:3px solid #667eea;padding-left:12px;margin:8px 0;color:#666;">$1</blockquote>');
        
        // Horizontal rule --- -> <hr>
        text = text.replace(/^---$/gm, '<hr style="border:none;border-top:1px solid #e0e0e0;margin:12px 0;">');
        
        // Links [text](url) -> <a>text</a>
        text = text.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank" style="color:#667eea;text-decoration:underline;">$1</a>');
        
        // Line breaks
        text = text.replace(/\n/g, '<br>');
        
        return text;
    }

    // Xabar yuborish funksiyasi
    async function sendMessage() {
        const message = chatInputField.value.trim();
        const imageFile = chatImageInput.files[0];
        
        if (message === '' && !imageFile) {
            console.log('Empty message and no image');
            return;
        }

        console.log('sendMessage called', { message, hasImage: !!imageFile });

        // Rasm preview SAQLAB qolish (o'chirmaslik!)
        // User message qo'shish
        if (message) {
            const userMessageDiv = document.createElement('div');
            userMessageDiv.className = 'user-message message';
            userMessageDiv.textContent = message;
            chatMessages.appendChild(userMessageDiv);
        }

        // Input maydonini tozalash va disable qilish
        chatInputField.value = '';
        chatInputField.disabled = true;
        chatSendButton.disabled = true;
        chatImageButton.disabled = true;
        
        // Image input tozalash (rasm yuborilgandan keyin)
        if (imageFile) {
            chatImageInput.value = '';
        }

        // Xabarlar oynasini pastga surish
        smartScroll();

        // Loading indicator qo'shish
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'bot-message message loading';
        loadingDiv.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // History ga user xabarini qo'shish
        chatHistory.push({
            role: 'user',
            text: message
        });
        saveChatHistory();

        // Rasm base64 ni olish
        let imageBase64 = null;
        if (imageFile) {
            const reader = new FileReader();
            imageBase64 = await new Promise((resolve) => {
                reader.onload = (e) => resolve(e.target.result.split(',')[1]);
                reader.readAsDataURL(imageFile);
            });
        }

        try {
            // Backend API ga so'rov yuborish (streaming)
            console.log('Sending request to API...', { message, hasImage: !!imageBase64 });
            
            const response = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    message: message,
                    history: chatHistory.slice(-10),
                    stream: true,
                    image: imageBase64
                })
            });
            
            console.log('Response status:', response.status);

            // Loading indicator ni o'chirish
            loadingDiv.remove();

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // Bot javob div
            const botMessageDiv = document.createElement('div');
            botMessageDiv.className = 'bot-message message';
            botMessageDiv.textContent = '';
            chatMessages.appendChild(botMessageDiv);

            let fullText = '';
            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value);
                const lines = chunk.split('\n');

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        try {
                            const data = JSON.parse(line.slice(6));
                            
                            // Thinking indicator
                            if (data.thinking === true) {
                                const thinkDiv = document.createElement('div');
                                thinkDiv.className = 'thinking-indicator';
                                thinkDiv.id = 'think-indicator';
                                thinkDiv.innerHTML = `
                                    <span class="thinking-text">thinking</span><span class="dots"><span>.</span><span>.</span><span>.</span></span>
                                `;
                                botMessageDiv.before(thinkDiv);
                                forceScroll();
                            }
                            
                            if (data.thinking === false) {
                                const thinkDiv = document.getElementById('think-indicator');
                                if (thinkDiv) thinkDiv.remove();
                            }
                            
                            if (data.chunk) {
                                fullText += data.chunk;
                                botMessageDiv.innerHTML = formatMarkdown(fullText);
                                smartScroll();
                            }
                            
                            if (data.done) {
                                chatHistory.push({
                                    role: 'model',
                                    text: fullText
                                });
                                saveChatHistory();
                                forceScroll();
                                scrollCounter = 0;
                                
                                // Rasm preview o'chirish (javob tugagandan keyin)
                                const tempPreview = document.getElementById('temp-image-preview');
                                if (tempPreview) tempPreview.remove();
                            }
                            
                            if (data.error) {
                                throw new Error(data.error);
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                        }
                    }
                }
            }

        } catch (error) {
            console.error('AI Chat Error:', error);
            
            // Loading indicator ni o'chirish
            loadingDiv.remove();

            // Xato xabarini ko'rsatish
            const errorDiv = document.createElement('div');
            errorDiv.className = 'bot-message message error-message';
            errorDiv.textContent = 'Kechirasiz, xatolik yuz berdi. Iltimos, qayta urinib ko\'ring.';
            chatMessages.appendChild(errorDiv);
            
            // Rasm preview o'chirish (xato bo'lsa ham)
            const tempPreview = document.getElementById('temp-image-preview');
            if (tempPreview) tempPreview.remove();
        } finally {
            // Input maydonini qayta yoqish
            chatInputField.disabled = false;
            chatSendButton.disabled = false;
            chatImageButton.disabled = false;
            chatInputField.focus();
            
            // Xabarlar oynasini pastga surish
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
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