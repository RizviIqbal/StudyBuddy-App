<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT name, cgpa, semester, earned_credit, email, date_of_birth, profile_picture FROM users WHERE id = $user_id")->fetch_assoc();

$profile_picture = $user['profile_picture'] ?? 'default.png';
$upload_path = "uploads/" . $profile_picture;
if (!file_exists(__DIR__ . '/' . $upload_path) || empty($profile_picture)) {
    $upload_path = "assets/default.jpg"; // fallback
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Profile - StudyBuddy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #7289da;
      --secondary: #99aab5;
      --bg-light: #f2f3f5;
      --bg-dark: #2c2f33;
      --card-light: #ffffff;
      --card-dark: #23272a;
      --text-light: #2e2e2e;
      --text-dark: #e4e6eb;
      --accent: #5865f2;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-light);
      transition: background 0.3s, color 0.3s;
    }

    body.dark {
      background-color: var(--bg-dark);
      color: var(--text-dark);
    }

    .profile-card {
      background: var(--card-light);
      max-width: 500px;
      margin: 80px auto;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
      text-align: center;
      transition: background 0.3s, color 0.3s;
    }

    body.dark .profile-card {
      background: var(--card-dark);
    }

    .profile-image {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--accent);
      margin-bottom: 20px;
    }

    h2 {
      margin: 10px 0;
      font-size: 26px;
    }

    .info-section {
      text-align: left;
      margin-top: 30px;
    }

    .info-item {
      margin-bottom: 15px;
    }

    .info-label {
      font-weight: 600;
      color: var(--secondary);
      display: inline-block;
      width: 130px;
    }

    .info-value {
      color: inherit;
    }

    .btn-container {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .btn {
      padding: 10px 18px;
      border: none;
      border-radius: 10px;
      background-color: var(--accent);
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn:hover {
      background-color: #4752c4;
    }

    @media (max-width: 600px) {
      .profile-card {
        margin: 30px 15px;
        padding: 30px 20px;
      }

      .info-label {
        width: 110px;
      }

      .profile-image {
        width: 100px;
        height: 100px;
      }

      .btn-container {
        flex-direction: column;
        gap: 10px;
      }
    }
  </style>
</head>
<body>

  <div class="profile-card">
    <img class="profile-image" src="<?= htmlspecialchars($upload_path) ?>" alt="Profile Picture">
    <h2><?= htmlspecialchars($user['name']) ?></h2>

    <div class="info-section">
      <div class="info-item"><span class="info-label">📘 CGPA:</span> <span class="info-value"><?= htmlspecialchars($user['cgpa']) ?></span></div>
      <div class="info-item"><span class="info-label">🎓 Semester:</span> <span class="info-value"><?= htmlspecialchars($user['semester']) ?></span></div>
      <div class="info-item"><span class="info-label">📚 Earned Credit:</span> <span class="info-value"><?= htmlspecialchars($user['earned_credit'] ?? 'N/A') ?></span></div>
      <div class="info-item"><span class="info-label">📧 Email:</span> <span class="info-value"><?= htmlspecialchars($user['email']) ?></span></div>
      <div class="info-item"><span class="info-label">🎂 Date of Birth:</span> <span class="info-value"><?= htmlspecialchars($user['date_of_birth'] ?? 'N/A') ?></span></div>
    </div>

    <div class="btn-container">
      <button class="btn" onclick="window.location.href='dashboard.php'">🔙 Back to Dashboard</button>
      <button class="btn" onclick="toggleTheme()">🌓 Toggle Theme</button>
    </div>
  </div>

  <script>
    function toggleTheme() {
      document.body.classList.toggle("dark");
      localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
    }

    // Load theme from local storage
    window.onload = function () {
      if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark");
      }
    };
  </script>
</body>
</html>
