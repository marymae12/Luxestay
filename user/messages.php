<?php include 'includes/header.php'; ?>

<style>
    body {
        overflow: hidden;
    }

    footer {
        display: none;
    }

    .chat-layout {
        display: flex;
        height: calc(100vh - 130px);
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: 10px;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        background: white;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
        background: #fcfcfc;
    }

    .message-wrapper {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        margin-bottom: 5px;
    }

    .message-wrapper.sent {
        justify-content: flex-end;
    }

    .message-wrapper.received {
        justify-content: flex-start;
    }

    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
        flex-shrink: 0;
    }

    .avatar.admin {
        background-color: #007bff;
    }

    .avatar.user {
        background-color: #6c757d;
    }

    .message {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 12px;
        line-height: 1.4;
    }

    .message.sent {
        background: #007bff;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.received {
        background: #e9ecef;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .message-meta {
        font-size: 0.75rem;
        margin-top: 5px;
        opacity: 0.8;
        display: block;
    }

    .chat-input {
        padding: 15px;
        background: white;
        border-top: 1px solid #dee2e6;
        display: flex;
        gap: 10px;
    }

    .chat-input input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        outline: none;
    }

    .chat-input button {
        padding: 0 20px;
    }
</style>

<div class="chat-layout">
    <div class="chat-main">
        <div class="chat-header">
            <h3 style="margin:0;">Chat with Admin</h3>
        </div>
        <div class="chat-messages" id="chat-messages">
            <!-- Messages inserted here via JS -->
        </div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Type your message..."
                onkeypress="if(event.key === 'Enter') sendMessage()">
            <button class="btn btn-primary" onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script>
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
    const chatContainer = document.getElementById('chat-messages');

    let showAssistant = true;
    let firstLoad = true;

    function fetchMessages(forceScroll = false) {
        fetch('../api_messages.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                if (data.messages) {
                    chatContainer.innerHTML = '';
                    data.messages.forEach(msg => {
                        const isSent = msg.sender_id == currentUserId;
                        
                        const initial = msg.sender_name ? msg.sender_name.charAt(0).toUpperCase() : 'U';

                        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                        const wrapperDiv = document.createElement('div');
                        wrapperDiv.className = `message-wrapper ${isSent ? 'sent' : 'received'}`;

                        wrapperDiv.innerHTML = `
                            ${!isSent ? `<div class="avatar admin" title="Admin">A</div>` : ''}
                            <div class="message ${isSent ? 'sent' : 'received'}">
                                ${msg.message}
                                <span class="message-meta">${time}</span>
                            </div>
                            ${isSent ? `<div class="avatar user" title="You">${initial}</div>` : ''}
                        `;
                        chatContainer.appendChild(wrapperDiv);
                    });
                    
                    if (showAssistant) {
                        const assistantDiv = document.createElement('div');
                        assistantDiv.className = 'message-wrapper received';
                        assistantDiv.innerHTML = `
                            <div class="avatar admin" title="Assistant" style="background-color: #17a2b8;">🤖</div>
                            <div class="message received" style="background: #e3f2fd; border-left: 4px solid #0d6efd; width: 100%; max-width: 85%;">
                                <strong>System Assistant</strong><br>
                                HOW CAN WE ASSIST YOU?<br><br>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <button class="btn btn-sm btn-outline-primary" style="text-align:left; background: white;" onclick="sendSuggestion('How to book?')">📌 How to book?</button>
                                    <button class="btn btn-sm btn-outline-primary" style="text-align:left; background: white;" onclick="sendSuggestion('Are there still available rooms?')">🏨 Are there still available rooms?</button>
                                    <button class="btn btn-sm btn-outline-primary" style="text-align:left; background: white;" onclick="sendSuggestion('What are the payment methods?')">💳 What are the payment methods?</button>
                                </div>
                            </div>
                        `;
                        chatContainer.appendChild(assistantDiv);
                    }
                    
                    // Always scroll to bottom on first load or after user sends a message
                    if (forceScroll || firstLoad) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        firstLoad = false;
                    } else {
                        // Background poll: only scroll if already near bottom (don't interrupt backreading)
                        const isNearBottom = (chatContainer.scrollHeight - chatContainer.scrollTop - chatContainer.clientHeight) < 120;
                        if (isNearBottom) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }
                }
            });
    }

    function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();

        if (!message) return;

        // /help command: show the assistant panel without sending a message
        if (message === '/help') {
            input.value = '';
            showAssistant = true;
            fetchMessages();
            return;
        }

        // Hide assistant when user manually sends any message
        showAssistant = false;

        fetch('../api_messages.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    fetchMessages(true); // force scroll to bottom after sending
                }
            });
    }

    function sendSuggestion(text) {
        showAssistant = false;
        document.getElementById('message-input').value = text;
        sendMessage();
    }

    // Poll for new messages every 3 seconds
    setInterval(fetchMessages, 3000);
    // Initial fetch
    fetchMessages();
</script>

<?php include 'includes/footer.php'; ?>
