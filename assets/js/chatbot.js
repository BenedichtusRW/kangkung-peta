function toggleChatbot() {
    const widget = document.getElementById('chatbotWidget');
    widget.classList.toggle('show');
}

function sendQuickReply(text) {
    const input = document.getElementById('chatInput');
    input.value = text;
    document.getElementById('chatForm').dispatchEvent(new Event('submit'));
}

async function handleChatSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;

    // Clear input
    input.value = '';

    // Show User Message
    appendMessage(message, 'user');

    // Show Typing Indicator
    const typingId = showTypingIndicator();

    try {
        // Prepare form data
        const formData = new FormData();
        formData.append('message', message);

        // Fetch response from backend
        const response = await fetch(`${window.location.origin}/api/chatbot.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        // Wait artificially for 1.5 seconds to simulate typing
        setTimeout(() => {
            // Remove typing indicator
            removeTypingIndicator(typingId);

            // Show Bot Message
            if (data.status === 'success') {
                appendMessage(data.reply, 'bot');
            } else {
                appendMessage('Maaf, saya sedang mengalami gangguan sistem. Coba lagi nanti ya!', 'bot');
            }
        }, 1500);
    } catch (error) {
        setTimeout(() => {
            removeTypingIndicator(typingId);
            appendMessage('Maaf, koneksi terputus. Silakan periksa jaringan Anda.', 'bot');
        }, 1500);
    }
}

function appendMessage(text, sender) {
    const chatBody = document.getElementById('chatBody');
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-msg ${sender}`;
    
    // We use innerHTML for bot so we can render basic HTML links like <a href="...">
    const contentDiv = document.createElement('div');
    contentDiv.className = 'msg-content';
    if(sender === 'bot') {
        contentDiv.innerHTML = text;
    } else {
        contentDiv.textContent = text;
    }

    msgDiv.appendChild(contentDiv);
    chatBody.appendChild(msgDiv);
    
    // Scroll to bottom
    chatBody.scrollTop = chatBody.scrollHeight;
}

function showTypingIndicator() {
    const chatBody = document.getElementById('chatBody');
    const msgDiv = document.createElement('div');
    const typingId = 'typing-' + Date.now();
    msgDiv.id = typingId;
    msgDiv.className = 'chat-msg bot typing';
    
    const contentDiv = document.createElement('div');
    contentDiv.className = 'msg-content typing-indicator';
    contentDiv.innerHTML = '<span></span><span></span><span></span>';

    msgDiv.appendChild(contentDiv);
    chatBody.appendChild(msgDiv);
    
    chatBody.scrollTop = chatBody.scrollHeight;
    
    return typingId;
}

function removeTypingIndicator(id) {
    const indicator = document.getElementById(id);
    if (indicator) {
        indicator.remove();
    }
}
