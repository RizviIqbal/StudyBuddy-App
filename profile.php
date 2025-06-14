<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

$result = $conn->query("SELECT name, email, profile_picture, cgpa, semester FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();

$name = $user['name'];
$email = $user['email'];
$cgpa = $user['cgpa'] ?? '';
$semester = $user['semester'] ?? '';

if (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture'])) {
    $profile_picture = "uploads/" . $user['profile_picture'];
} else {
    $profile_picture = "assets/default.jpg";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $cgpa = isset($_POST['cgpa']) && is_numeric($_POST['cgpa']) ? floatval($_POST['cgpa']) : 0.0;
    $semester = isset($_POST['semester']) && is_numeric($_POST['semester']) ? intval($_POST['semester']) : 0;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_picture']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
        $dest = "uploads/" . $fileName;

        if (move_uploaded_file($tmp, $dest)) {
            $relative_path = basename($dest);
        } else {
            $error = "Failed to upload image.";
        }
    }

    $final_profile_picture = isset($relative_path) ? $relative_path : $user['profile_picture'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, profile_picture=?, cgpa=?, semester=? WHERE id=?");
    $stmt->bind_param("sssddi", $name, $email, $final_profile_picture, $cgpa, $semester, $user_id);
    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
        $user = array_merge($user, [
            'name' => $name,
            'email' => $email,
            'profile_picture' => $final_profile_picture,
            'cgpa' => $cgpa,
            'semester' => $semester
        ]);
        $profile_picture = file_exists("uploads/" . $final_profile_picture) ? "uploads/" . $final_profile_picture : "assets/default.jpg";
    } else {
        $error = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #1E88E5;
            --primary-dark: #1565C0;
            --light-bg: #f9f9fb;
            --dark-bg: #121212;
            --card-light: #ffffff;
            --card-dark: #1f1f1f;
            --text-light: #111;
            --text-dark: #f0f0f0;
            --input-light: #ffffff;
            --input-dark: #2a2a2a;
            --border-radius: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        body.dark {
            background-color: var(--dark-bg);
            color: var(--text-dark);
        }

        .container {
            max-width: 720px;
            margin: 50px auto;
            padding: 30px;
            border-radius: var(--border-radius);
            background-color: var(--card-light);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease;
            transition: background-color 0.3s ease;
        }

        body.dark .container {
            background-color: var(--card-dark);
        }

        h2 {
            margin-top: 0;
            font-size: 28px;
            font-weight: 600;
        }

        label {
            display: block;
            margin: 16px 0 6px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background-color: var(--input-light);
            color: inherit;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        body.dark input {
            background-color: var(--input-dark);
            border-color: #444;
            color: var(--text-dark);
        }

        input[type="file"] {
            padding: 10px;
        }

        img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
            border: 3px solid #ccc;
            transition: transform 0.3s ease;
        }

        img:hover {
            transform: scale(1.05);
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            font-size: 16px;
            border: none;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: var(--primary-dark);
        }

        .message {
            padding: 12px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .back {
            margin-top: 30px;
            text-align: center;
        }

        .back a {
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: transparent;
            color: inherit;
            border: none;
            font-size: 22px;
            cursor: pointer;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">🌓</button>

    <div class="container">
        <h2>👤 Edit Profile</h2>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Profile Picture:</label>
            <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture">
            <input type="file" name="profile_picture">

            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Current CGPA:</label>
            <input type="number" step="0.01" name="cgpa" value="<?= htmlspecialchars($user['cgpa']) ?>">

            <label>Semester:</label>
            <input type="number" name="semester" value="<?= htmlspecialchars($user['semester']) ?>">

            <button type="submit">💾 Save Changes</button>
        </form>

        <div class="back">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>

    <script>
        const toggleTheme = () => {
            document.body.classList.toggle('dark');
            localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
        };
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark');
            }
        })();
    </script>
</body>
</html>
