<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$joinError = $successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_name'])) {
    $group_name = trim($_POST['group_name']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->bind_param("s", $group_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $group_id = $row['id'];

        $check = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
        $check->bind_param("ii", $group_id, $user_id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $insert->bind_param("ii", $group_id, $user_id);
            if ($insert->execute()) {
                $successMsg = "🎉 Successfully joined the group!";
            } else {
                $joinError = "Something went wrong. Please try again.";
            }
        } else {
            $joinError = "You're already a member of this group.";
        }

        $check->close();
    } else {
        $joinError = "Group not found. Please check the name.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join a Group | StudyBuddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4A00E0;
            --secondary: #8E2DE2;
            --glass: rgba(255, 255, 255, 0.2);
        }

        body {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            color: #fff;
            animation: slideIn 0.6s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h3 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
            background: linear-gradient(to right, #ffffff, #d1d1d1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-label {
            color: #f1f1f1;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            color: #fff;
            padding: 12px;
        }

        .form-control::placeholder {
            color: #e0e0e0;
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            border: none;
            color: #000;
            font-weight: 600;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.2s ease-in-out;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
        }

        .alert {
            border-radius: 12px;
            font-size: 15px;
            padding: 12px 16px;
        }

        .back-link {
            text-align: center;
            margin-top: 12px;
            color: #e6e6e6;
            display: block;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <h3>🔑 Join a Group</h3>

        <?php if ($joinError): ?>
            <div class="alert alert-danger"><?= $joinError; ?></div>
        <?php elseif ($successMsg): ?>
            <div class="alert alert-success"><?= $successMsg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="group_name" class="form-label">Group Name</label>
                <input type="text" name="group_name" id="group_name" class="form-control" placeholder="Enter group name..." required>
                <div id="groupStatus" class="mt-2" style="font-size: 14px;"></div>

            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary w-100">🔗 Join Group</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <a href="groups.php" class="btn btn-primary w-100">← Back to Groups</a>
        </div>
    </div>
<!-- Real-time group validation -->
<script>
    const input = document.getElementById('group_name');
    const statusDiv = document.getElementById('groupStatus');
    let timeout = null;

    input.addEventListener('input', () => {
        const name = input.value.trim();
        clearTimeout(timeout);

        if (name.length === 0) {
            statusDiv.innerHTML = '';
            return;
        }

        statusDiv.innerHTML = '⏳ Checking...';
        timeout = setTimeout(() => {
            fetch(`check_group.php?group_name=${encodeURIComponent(name)}`)
                .then(res => res.text())
                .then(data => {
                    if (data === 'exists') {
                        statusDiv.innerHTML = '✅ <span style="color: yellow;">Group exists!</span>';
                    } else {
                        statusDiv.innerHTML = '❌ <span style="color: red;">Group not found.</span>';
                    }
                });
        }, 400);
    });
</script>
</body>
</html>
