<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];
$users = $conn->query("SELECT id, name, profile_pic FROM users WHERE id != $user_id");
$chat_user_id = $_GET['chat'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | StudyBuddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5865F2;
            --primary-hover: #4752C4;
            --bg: #F2F3F5;
            --text: #2E3338;
            --sidebar-bg: #E3E5E8;
            --chat-bg: #FFFFFF;
            --bubble-sent: #5865F2;
            --bubble-received: #E1E1E1;
            --button-bg: #5865F2;
            --button-hover: #4752C4;
        }

        body.dark {
            --bg: #2C2F33;
            --text: #FFFFFF;
            --sidebar-bg: #202225;
            --chat-bg: #36393F;
            --bubble-sent: #5865F2;
            --bubble-received: #40444B;
            --button-bg: #5865F2;
            --button-hover: #4752C4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            display: flex;
            background-color: var(--bg);
            color: var(--text);
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: var(--text);
            height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar h3 {
            margin-top: 0;
            font-size: 20px;
            color: var(--primary);
        }

        .user-entry {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .user-entry:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .user-entry img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-entry a {
            color: inherit;
            text-decoration: none;
            font-weight: 500;
        }

        .chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 30px;
            background: var(--chat-bg);
            height: 100vh;
        }

        .chat-box {
            flex: 1;
            padding: 20px;
            background: var(--chat-bg);
            border-radius: 12px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .msg {
            max-width: 70%;
            display: flex;
        }

        .msg.sent {
            align-self: flex-end;
            justify-content: flex-end;
        }

        .msg.received {
            align-self: flex-start;
            justify-content: flex-start;
        }

        .bubble {
            padding: 12px 16px;
            background-color: var(--bubble-sent);
            color: white;
            border-radius: 18px;
            font-size: 14px;
        }

        .msg.received .bubble {
            background-color: var(--bubble-received);
            color: black;
        }

        .message-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        #messageInput {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #40444B;
            color: white;
        }

        button {
            background-color: var(--button-bg);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        button:hover {
            background-color: var(--button-hover);
        }

        .typing-indicator {
            font-style: italic;
            font-size: 12px;
            color: #ccc;
            margin-top: 8px;
            height: 16px;
            visibility: hidden;
        }

        .typing-indicator.visible {
            visibility: visible;
        }

        .top-controls {
            margin-top: auto;
        }

        .top-controls button {
            margin-top: 12px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>Chats</h3>
    <?php while ($row = $users->fetch_assoc()): ?>
        <div class="user-entry">
            <img src="<?= (!empty($row['profile_pic']) && file_exists("uploads/{$row['profile_pic']}")) ? 'uploads/' . $row['profile_pic'] : 'assets/default.jpg' ?>" alt="User">
            <a href="messages.php?chat=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a>
        </div>
    <?php endwhile; ?>

    <div class="top-controls">
        <form action="dashboard.php">
            <button type="submit">⬅ Back to Dashboard</button>
        </form>
        <button onclick="toggleTheme()">✨ Toggle Theme</button>
    </div>
</div>

<div class="chat">
    <?php if ($chat_user_id): ?>
        <div class="chat-box" id="chatBox"></div>
        <div id="typingIndicator" class="typing-indicator">Typing...</div>
        <form class="message-form" onsubmit="sendMessage(); return false;">
            <input type="text" id="messageInput" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
    <?php else: ?>
        <p style="font-size: 18px;">Select a user to start chatting.</p>
    <?php endif; ?>
</div>

<script>
    const chatUserId = <?= json_encode($chat_user_id) ?>;
    const typingIndicator = document.getElementById('typingIndicator');
    const input = document.getElementById('messageInput');
    const chatBox = document.getElementById("chatBox");

    function loadMessages() {
        fetch("fetch_messages.php?chat=" + chatUserId)
            .then(res => res.text())
            .then(data => {
                chatBox.innerHTML = data;
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    function sendMessage() {
        const msg = input.value;
        fetch("send_message.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "receiver_id=" + chatUserId + "&message=" + encodeURIComponent(msg)
        }).then(() => {
            input.value = "";
            typingIndicator.classList.remove("visible");
            loadMessages();
        });
    }

    input?.addEventListener("input", () => {
        if (input.value.trim()) {
            typingIndicator.classList.add("visible");
        } else {
            typingIndicator.classList.remove("visible");
        }
    });

    if (chatUserId) {
        loadMessages();
        setInterval(loadMessages, 2000);
    }

    function toggleTheme() {
        document.body.classList.toggle("dark");
    }
</script>
</body>
</html>
