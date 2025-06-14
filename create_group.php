<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "studybuddy");
    $group_name = trim($_POST['group_name']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if ($group_name === "") {
        $error = "Group name cannot be empty.";
    } else {
        $check = $conn->prepare("SELECT * FROM groups WHERE name = ?");
        $check->bind_param("s", $group_name);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Group name already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO groups (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $group_name, $description, $user_id);
            if ($stmt->execute()) {
                $group_id = $stmt->insert_id;

                $join = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $join->bind_param("ii", $group_id, $user_id);
                $join->execute();

                $success = "✅ Group created successfully! <a href='groups.php' class='btn btn-success mt-3'>Go to Groups</a>";
            } else {
                $error = "Something went wrong while creating the group.";
            }
            $stmt->close();
        }
        $check->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Study Group | StudyBuddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --glass: rgba(255, 255, 255, 0.25);
        }

        * {
            box-sizing: border-box;
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
            backdrop-filter: blur(18px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            color: #fff;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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
            color: #f0f0f0;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
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
            background: linear-gradient(to right, #43e97b, #38f9d7);
            border: none;
            color: #000;
            font-weight: 600;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.2s ease-in-out;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
        }

        .btn-link {
            color: #fff;
            text-align: center;
            display: block;
            margin-top: 10px;
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            font-size: 15px;
            padding: 12px 16px;
        }

        a.btn-success {
            background-color: #28a745 !important;
            border: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <h3>🚀 Create a Study Group</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="group_name" class="form-label">Group Name</label>
                <input type="text" class="form-control" name="group_name" placeholder="Enter group name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description (optional)</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Describe your group..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">✨ Create Group</button>
            <a href="groups.php" class="btn-link">← Back to Groups</a>
        </form>
    </div>
</body>
</html>
