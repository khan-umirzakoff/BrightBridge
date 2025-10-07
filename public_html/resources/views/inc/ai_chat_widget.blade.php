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
                <div class="modal-header">
                    <h3>Start New Chat</h3>
                    <p>Current chat history will be deleted</p>
                </div>
                <div class="modal-buttons">
                    <button class="btn-new-chat" id="confirm-new-chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        New Chat
                    </button>
                    <button class="btn-cancel" id="cancel-new-chat">Cancel</button>
                </div>
            </div>
        </div>

        <div id="chat-input">
            <div id="image-previews" style="display:none;flex-wrap:wrap;gap:5px;margin-bottom:10px;width:100%;"></div>
            <div style="display:flex;align-items:center;gap:8px;width:100%;border:1px solid #ddd;border-radius:25px;padding:8px 12px;background:#fff;">
                <input type="file" id="chat-image-input" accept="image/*" multiple style="display:none;">
                <input type="text" id="chat-input-field" placeholder="Xabar yozing..." aria-label="Xabar kiritish maydoni" style="flex:1;border:none;outline:none;font-size:14px;padding:8px 0;">
                <button id="chat-image-button" aria-label="Rasm yuborish" title="Rasm yuklash" style="background:none;border:none;cursor:pointer;padding:6px;border-radius:50%;display:flex;align-items:center;justify-content:center;width:32px;height:32px;color:#666;transition:background-color 0.2s;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.7" d="M17 9.00195C19.175 9.01406 20.3529 9.11051 21.1213 9.8789C22 10.7576 22 12.1718 22 15.0002V16.0002C22 18.8286 22 20.2429 21.1213 21.1215C20.2426 22.0002 18.8284 22.0002 16 22.0002H8C5.17157 22.0002 3.75736 22.0002 2.87868 21.1215C2 20.2429 2 18.8286 2 16.0002L2 15.0002C2 12.1718 2 10.7576 2.87868 9.87889C3.64706 9.11051 4.82497 9.01406 7 9.00195" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M12 15L12 2M12 2L15 5.5M12 2L9 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button id="chat-send-button" aria-label="Xabarni yuborish" style="background:#0d2d62;border:none;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast bildirishnoma -->
<div id="chat-toast" style="position:fixed;top:20px;right:20px;background:#333;color:#fff;padding:12px 16px;border-radius:8px;font-size:14px;z-index:10000;display:none;max-width:300px;word-wrap:break-word;">
    <span id="toast-message"></span>
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
                const files = e.target.files;
                if (!files || files.length === 0) return;

                const existingPreviews = document.querySelectorAll('.image-preview-msg').length;
                const totalFiles = existingPreviews + files.length;

                if (totalFiles > 3) {
                    showToast('Maksimal 3 ta rasm yuborish mumkin.');
                    e.target.value = '';
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Fayl hajmini tekshirish (max 5MB)
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        showToast('Rasm hajmi juda katta! Maksimal 5MB.');
                        e.target.value = '';
                        return;
                    }

                    // Rasm preview - input ustida kichik ko'rsatish
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const imageContainer = document.getElementById('image-previews');
                        imageContainer.style.display = 'flex';
                        const imageWrapper = document.createElement('div');
                        imageWrapper.className = 'image-preview-wrapper';
                        imageWrapper.style.position = 'relative';
                        imageWrapper.style.display = 'inline-block';
                        imageWrapper.innerHTML = `
                            <img src="${event.target.result}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:transparent;">
                            <button onclick="this.parentElement.remove(); updateSendButton();" 
                                style="position:absolute;top:-6px;right:-6px;background:#ff4757;color:white;border:none;border-radius:50%;width:16px;height:16px;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:bold;">×</button>
                        `;
                        imageContainer.appendChild(imageWrapper);
                        updateSendButton();
                    };
                    reader.readAsDataURL(file);
                }

                // Input tozalash
                e.target.value = '';
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

    // Toast bildirishnoma ko'rsatish
    function showToast(message) {
        const toast = document.getElementById('chat-toast');
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }

    // Send button holatini yangilash
    function updateSendButton() {
        const imagePreviews = document.querySelectorAll('.image-preview-wrapper');
        const imageContainer = document.getElementById('image-previews');
        const hasImage = imagePreviews.length > 0;
        const hasText = chatInputField.value.trim() !== '';
        
        // Image container ni ko'rsatish/yashirish
        if (hasImage) {
            imageContainer.style.display = 'flex';
        } else {
            imageContainer.style.display = 'none';
        }
        
        chatSendButton.disabled = !(hasText || hasImage);
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
                        if (msg.text || msg.images) {
                            const textDiv = document.createElement('div');
                            textDiv.className = msg.role === 'user' ? 'user-message message' : 'bot-message message';
                            let content = '';
                            if (msg.images && msg.images.length > 0) {
                                content += '<div style="display:flex;gap:5px;margin-bottom:5px;flex-wrap:wrap;">';
                                msg.images.forEach(imageBase64 => {
                                    content += `<img src="data:image/jpeg;base64,${imageBase64}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:transparent;margin:2px;">`;
                                });
                                content += '</div>';
                            }
                            if (msg.text) {
                                content += msg.role === 'user' ? msg.text : formatMarkdown(msg.text);
                            }
                            textDiv.innerHTML = content;
                            chatMessages.appendChild(textDiv);
                        }
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
        const imagePreviews = document.querySelectorAll('.image-preview-wrapper');

        if (message === '' && imagePreviews.length === 0) {
            console.log('Empty message and no images');
            return;
        }

        // Rasm yuborish uchun matn majburiy
        if (imagePreviews.length > 0 && message === '') {
            showToast('Rasm yuborish uchun matn ham kiritish kerak.');
            return;
        }

        console.log('sendMessage called', { message, imageCount: imagePreviews.length });

        // Rasm base64 larni olish
        const images = [];
        for (let preview of imagePreviews) {
            const img = preview.querySelector('img');
            if (img && img.src.startsWith('data:image/')) {
                const base64 = img.src.split(',')[1];
                images.push(base64);
            }
        }

        // Preview larni o'chirish
        imagePreviews.forEach(preview => preview.remove());

        // User message qo'shish
        const userMessageDiv = document.createElement('div');
        userMessageDiv.className = 'user-message message';
        let content = '';
        if (images.length > 0) {
            content += '<div style="display:flex;gap:5px;margin-bottom:5px;flex-wrap:wrap;">';
            images.forEach(imageBase64 => {
                content += `<img src="data:image/jpeg;base64,${imageBase64}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;background:transparent;margin:2px;">`;
            });
            content += '</div>';
        }
        content += message;
        userMessageDiv.innerHTML = content;
        chatMessages.appendChild(userMessageDiv);

        // Input maydonini tozalash va disable qilish
        chatInputField.value = '';
        chatInputField.disabled = true;
        chatSendButton.disabled = true;
        chatImageButton.disabled = true;

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
            text: message,
            images: images
        });
        saveChatHistory();

        try {
            // Backend API ga so'rov yuborish (streaming)
            console.log('Sending request to API...', { message, imageCount: images.length });
            
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
                    images: images
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

    // Input maydonida o'zgarish bo'lganda send button yangilanishi
    if (chatInputField) {
        chatInputField.addEventListener('input', updateSendButton);

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