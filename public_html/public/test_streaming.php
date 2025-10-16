<!DOCTYPE html>
<html>
<head>
    <title>SSE Streaming Test</title>
    <meta name="csrf-token" content="">
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        #messages { background: #252526; padding: 15px; border: 1px solid #3e3e42; margin: 10px 0; min-height: 300px; }
        .chunk { color: #4ec9b0; margin: 2px 0; }
        .timing { color: #ce9178; font-size: 11px; }
        button { background: #007acc; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        button:hover { background: #005a9e; }
    </style>
</head>
<body>
    <h1>🔥 Real-Time Streaming Test</h1>
    <button onclick="testStreaming()">Start Streaming Test</button>
    <button onclick="clearMessages()">Clear</button>
    <div id="messages"></div>

    <script>
        const messagesDiv = document.getElementById('messages');
        let startTime;
        let chunkCount = 0;

        function clearMessages() {
            messagesDiv.innerHTML = '';
            chunkCount = 0;
        }

        async function testStreaming() {
            clearMessages();
            startTime = Date.now();
            chunkCount = 0;

            messagesDiv.innerHTML = '<div class="chunk">🚀 Starting streaming request...</div>';

            try {
                const response = await fetch('/api/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'text/event-stream',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        message: 'salom, men dasturchi',
                        history: [],
                        stream: true
                    })
                });

                if (!response.ok) {
                    messagesDiv.innerHTML += '<div style="color:red;">❌ HTTP Error: ' + response.status + '</div>';
                    return;
                }

                messagesDiv.innerHTML += '<div class="chunk">✅ Connection established!</div>';

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const { done, value } = await reader.read();

                    const elapsed = Date.now() - startTime;
                    messagesDiv.innerHTML += `<div class="timing">[${elapsed}ms] Read event (${value ? value.length : 0} bytes)</div>`;

                    if (done) {
                        messagesDiv.innerHTML += '<div class="chunk">🏁 Stream completed!</div>';
                        messagesDiv.innerHTML += `<div class="chunk">📊 Total chunks received: ${chunkCount}</div>`;
                        break;
                    }

                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            try {
                                const dataStr = line.slice(6);
                                if (!dataStr) continue;

                                // Show raw data for debugging
                                messagesDiv.innerHTML += `<div style="color:#808080; font-size:10px;">RAW: ${dataStr.substring(0, 100)}</div>`;

                                const data = JSON.parse(dataStr);

                                if (data.thinking === true) {
                                    messagesDiv.innerHTML += '<div class="chunk">🤔 Thinking...</div>';
                                }

                                if (data.thinking === false) {
                                    messagesDiv.innerHTML += '<div class="chunk">💭 Stopped thinking</div>';
                                }

                                if (data.chunk) {
                                    chunkCount++;
                                    const elapsed = Date.now() - startTime;
                                    messagesDiv.innerHTML += `<div class="chunk">[${elapsed}ms] Chunk #${chunkCount}: "${data.chunk}"</div>`;
                                }

                                if (data.done) {
                                    messagesDiv.innerHTML += '<div class="chunk">✨ Done signal received</div>';
                                }

                                // Auto scroll
                                messagesDiv.scrollTop = messagesDiv.scrollHeight;

                            } catch (e) {
                                messagesDiv.innerHTML += `<div style="color:red;">❌ Parse error: ${e.message} | Line: ${line.substring(0, 100)}</div>`;
                            }
                        }
                    }
                }

            } catch (error) {
                messagesDiv.innerHTML += `<div style="color:red;">❌ Error: ${error.message}</div>`;
                console.error('Streaming error:', error);
            }
        }
    </script>
</body>
</html>
