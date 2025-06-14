<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];

$joinedGroups = $conn->query("SELECT g.* FROM groups g JOIN group_members gm ON g.id = gm.group_id WHERE gm.user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Groups | StudyBuddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --bg-light: #f3f6fb;
            --bg-dark: #1e1e2f;
            --text-light: #fff;
            --text-dark: #111;
            --card-glass: rgba(255, 255, 255, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--text-light);
            transition: background 0.3s ease, color 0.3s ease;
        }

        body.dark {
            background: var(--bg-dark);
            color: var(--text-light);
        }

        header {
            position: relative;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            padding: 30px 20px;
            text-align: center;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .top-left-buttons {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 12px;
        }

        .top-left-buttons button {
            background: rgba(255,255,255,0.1);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .top-left-buttons button:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-bottom: 40px;
        }

        .actions input[type="text"] {
            padding: 14px 18px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            max-width: 300px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .actions input::placeholder {
            color: #ddd;
        }

        .actions button {
            padding: 14px 22px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 14px;
            color: white;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
        }

        .actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        #groupsContainer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .group-card {
            background: var(--card-glass);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 10px 24px rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(14px);
            transition: transform 0.25s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .group-card:hover {
            transform: scale(1.02);
            box-shadow: 0 14px 28px rgba(255, 255, 255, 0.25);
        }

        .group-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .group-actions a {
            display: inline-block;
            margin-right: 12px;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .group-actions a:hover {
            background-color: #fff;
            color: var(--primary);
            transform: translateY(-1px);
        }

        @media screen and (max-width: 600px) {
            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .top-left-buttons {
                flex-direction: column;
                top: 16px;
                left: 16px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="top-left-buttons">
            <button onclick="location.href='dashboard.php'">← Dashboard</button>
            <button onclick="toggleTheme()">🌓 Theme</button>
        </div>
        📚 Study Groups
    </header>

    <div class="container">
        <div class="actions">
            <input type="text" id="searchInput" placeholder="🔍 Search groups by name..." oninput="filterGroups()">
            <button onclick="location.href='create_group.php'">➕ Create Group</button>
            <button onclick="location.href='join_group.php'">🔗 Join Group</button>
        </div>

        <div id="groupsContainer">
            <?php while ($group = $joinedGroups->fetch_assoc()): ?>
                <div class="group-card" data-name="<?= strtolower($group['name']) ?>">
                    <div class="group-name"><?= htmlspecialchars($group['name']) ?></div>
                    <div class="group-actions">
                        <a href="group_chat.php?group_id=<?= $group['id'] ?>">💬 Enter Chat</a>
                        <a href="leave_group.php?group_id=<?= $group['id'] ?>" onclick="return confirm('Leave this group?')">🚪 Leave</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function filterGroups() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.group-card').forEach(card => {
                const name = card.getAttribute('data-name');
                card.style.display = name.includes(input) ? 'block' : 'none';
            });
        }

        function toggleTheme() {
            document.body.classList.toggle('dark');
        }
    </script>
</body>
</html>
