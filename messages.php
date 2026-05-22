<?php include 'includes/header.php';

// Fetch all users who have sent or received a message
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username 
    FROM users u
    JOIN messages m ON u.id = m.sender_id OR u.id = m.receiver_id
    WHERE u.role = 'user'
");
$stmt->execute();
$chatUsers = $stmt->fetchAll();
?>

<style>
    body {
        overflow: hidden;
        /* Prevent full page scroll */
    }

    footer {
        display: none;
        /* Hide footer for full-screen chat */
    }

    .admin-chat-layout {
        display: flex;
        height: calc(100vh - 130px);
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: 10px;
    }

    .chat-sidebar {
        width: 300px;
        background: #f8f9fa;
        border-right: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        background: white;
    }

    .user-list {
        flex: 1;
        overflow-y: auto;
    }

    .user-item {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }

    .user-item:hover,
    .user-item.active {
        background: #e9ecef;
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

    .no-chat-selected {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        flex-direction: column;
        gap: 10px;
    }
</style>

<div class="admin-chat-layout">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h3 style="margin:0; font-size: 1.1rem;">Conversations</h3>
        </div>
        <div class="user-list">
            <?php if (empty($chatUsers)): ?>
                <div style="padding: 20px; text-align: center; color: #666;">No active conversations.</div>
            <?php else: ?>
                <?php foreach ($chatUsers as $u): ?>
                    <div class="user-item"
                        onclick="selectUser(<?php echo $u['id']; ?>, '<?php echo sanitize($u['username']); ?>')">
                        <strong>
                            <?php echo sanitize($u['username']); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-main" id="chat-main">
        <div class="no-chat-selected">
            <h1 style="font-size: 3rem; margin: 0;">💬</h1>
            <h3>Select a conversation to start messaging</h3>
        </div>
    </div>
</div>

<script>
    const currentAdminId = <?php echo $_SESSION['user_id']; ?>;
    let selectedUserId = null;
    let pollInterval = null;
    const chatMain = document.getElementById('chat-main');

    function selectUser(userId, username) {
        selectedUserId = userId;

        // Update UI active state
        document.querySelectorAll('.user-item').forEach(el => el.classList.remove('active'));
        event.currentTarget.classList.add('active');

        // Build chat area
        chatMain.innerHTML = `
            <div class="chat-header">
                <h3 style="margin:0;">Chat with ${username}</h3>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div style="text-align:center; color:#666;">Loading messages...</div>
            </div>
            <div class="chat-input">
                <input type="text" id="message-input" placeholder="Type your reply..." onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="btn btn-primary" onclick="sendMessage()">Send Reply</button>
            </div>
        `;

        if (pollInterval) clearInterval(pollInterval);
        fetchMessages();
        pollInterval = setInterval(fetchMessages, 3000);
    }

    function fetchMessages() {
        if (!selectedUserId) return;

        fetch(`api_messages.php?action=fetch&other_id=${selectedUserId}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('chat-messages');
                if (!container) return; // if user navigated away

                if (data.messages && data.messages.length > 0) {
                    container.innerHTML = '';
                    data.messages.forEach(msg => {
                        const isSent = msg.sender_id == currentAdminId;
                        const initial = msg.sender_name ? msg.sender_name.charAt(0).toUpperCase() : 'U';

                        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                        const wrapperDiv = document.createElement('div');
                        wrapperDiv.className = `message-wrapper ${isSent ? 'sent' : 'received'}`;

                        wrapperDiv.innerHTML = `
                            ${!isSent ? `<div class="avatar user" title="${msg.sender_name}">${initial}</div>` : ''}
                            <div class="message ${isSent ? 'sent' : 'received'}">
                                ${msg.message}
                                <span class="message-meta">${time}</span>
                            </div>
                            ${isSent ? `<div class="avatar admin" title="Admin">A</div>` : ''}
                        `;
                        container.appendChild(wrapperDiv);
                    });
                    container.scrollTop = container.scrollHeight;
                } else {
                    container.innerHTML = '<div style="text-align:center; color:#666; margin-top:20px;">No messages yet.</div>';
                }
            });
    }

    function sendMessage() {
        if (!selectedUserId) return;

        const input = document.getElementById('message-input');
        const message = input.value.trim();

        if (message) {
            fetch('api_messages.php?action=send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    receiver_id: selectedUserId
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        fetchMessages();
                    }
                });
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
