<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$group_id = intval($_GET['group_id']);
$user_id = $_SESSION['user_id'];

// Check if user is in the group
$check = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
$check->bind_param("ii", $group_id, $user_id);
$check->execute();
$result = $check->get_result();
if ($result->num_rows === 0) {
    echo "You are not a member of this group.";
    exit();
}

// Get group name
$groupName = "Group Chat";
$groupQuery = $conn->prepare("SELECT name FROM groups WHERE id = ?");
$groupQuery->bind_param("i", $group_id);
$groupQuery->execute();
$groupResult = $groupQuery->get_result();
if ($groupRow = $groupResult->fetch_assoc()) {
    $groupName = htmlspecialchars($groupRow['name']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $groupName ?> | StudyBuddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            color: white;
            padding: 18px 0;
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #eee;
        }
        main {
            flex: 1;
            display: flex;
        }
        #chatBox {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #fff;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        .message {
            margin-bottom: 15px;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .message img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        .text {
            max-width: 70%;
            padding: 10px;
            border-radius: 12px;
        }
        .sent .text {
            background: #e1d9ff;
            margin-left: auto;
            text-align: right;
            border-radius: 12px 12px 0 12px;
        }
        .received .text {
            background: #f0f0f0;
            text-align: left;
            border-radius: 12px 12px 12px 0;
        }
        #chatForm {
            display: flex;
            padding: 15px 20px;
            background: #f8f8f8;
        }
        #messageInput {
            flex: 1;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            padding: 12px 20px;
            background: #4A00E0;
            color: white;
            border: none;
            border-radius: 8px;
            margin-left: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #6b00ff;
        }

        /* Sidebar */
        #membersPanel {
            width: 220px;
            background: #f9f9f9;
            border-left: 1px solid #ddd;
            padding: 20px 10px;
            overflow-y: auto;
        }
        #membersPanel h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #4A00E0;
        }
        #memberList {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #memberList li {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }

        /* Dark mode */
        body.dark {
            background: #121212;
            color: #f0f0f0;
        }
        body.dark #chatBox {
            background: #1e1e1e;
            border-color: #333;
        }
        body.dark #messageInput {
            background: #2c2c2c;
            color: #fff;
            border-color: #444;
        }
        body.dark #membersPanel {
            background: #1b1b1b;
            border-color: #333;
        }
        body.dark header,
        body.dark .topbar {
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            color: #fff;
        }
        body.dark button {
            background: #4A00E0;
            color: #fff;
        }
        body.dark button:hover {
            background: #6b00ff;
        }
        body.dark .sent .text {
            background: #5f4abf;
        }
        body.dark .received .text {
            background: #2d2d2d;
        }
    </style>
</head>
<body>

<header><?= $groupName ?> Chat</header>
<div class="topbar">
    <button onclick="toggleDarkMode()">Toggle Dark Mode</button>
    <a href="dashboard.php"><button>← Back to Dashboard</button></a>
</div>

<main>
    <div id="chatBox"></div>

    <div id="membersPanel">
        <h3>Members</h3>
        <ul id="memberList">Loading...</ul>
    </div>
</main>

<form id="chatForm" onsubmit="sendMessage(event)">
    <input type="text" id="messageInput" placeholder="Type your message..." required>
    <button type="submit">Send</button>
</form>

<script>
    const groupId = <?= json_encode($group_id) ?>;
    const chatBox = document.getElementById("chatBox");
    const input = document.getElementById("messageInput");

    async function loadMessages() {
        try {
            const res = await fetch(`fetch_group_messages.php?group_id=${groupId}`);
            const data = await res.text();
            chatBox.innerHTML = data;
            chatBox.scrollTop = chatBox.scrollHeight;
        } catch (err) {
            console.error("Failed to load messages", err);
        }
    }

    async function sendMessage(e) {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;

        try {
            await fetch("send_group_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `group_id=${groupId}&message=${encodeURIComponent(msg)}`
            });
            input.value = "";
            loadMessages();
        } catch (err) {
            console.error("Failed to send message", err);
        }
    }

    async function loadGroupMembers() {
        try {
            const res = await fetch(`group_members.php?group_id=${groupId}`);
            const members = await res.json();
            const list = document.getElementById("memberList");
            list.innerHTML = "";
            members.forEach(member => {
                const li = document.createElement("li");
                li.textContent = member.username;
                list.appendChild(li);
            });
        } catch (err) {
            console.error("Failed to load group members", err);
        }
    }

    function toggleDarkMode() {
        document.body.classList.toggle("dark");
    }

    loadMessages();
    loadGroupMembers();
    setInterval(loadMessages, 3000);
    setInterval(loadGroupMembers, 10000); // Refresh members every 10s
</script>

</body>
</html>
