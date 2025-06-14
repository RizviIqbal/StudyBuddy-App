<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications | StudyBuddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            padding: 20px;
            background-color: #F7F9FC;
        }

        h2 {
            color: #333;
        }

        .notification {
            background: white;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .notification.unread {
            background: #e8f0fe;
            font-weight: bold;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 16px;
            background-color: #A05AFF;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #8b3ffc;
        }
    </style>
</head>
<body>
    <h2>Your Notifications</h2>

    <!-- Placeholder notifications -->
    <div class="notification unread">🎉 New group "CSE221 Buddies" was created. Join now!</div>
    <div class="notification">✅ Your friend request to Alex was accepted.</div>
    <div class="notification">📝 New typing test result recorded: 61 WPM, 97% accuracy.</div>

    <a class="back-btn" href="dashboard.php">← Back to Dashboard</a>
</body>
</html>
