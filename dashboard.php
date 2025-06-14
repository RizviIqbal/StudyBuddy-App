
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, profile_picture, cgpa, semester FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : [];

$name = $user['name'] ?? 'User';
$cgpa = $user['cgpa'] ?? 'N/A';
$semester = $user['semester'] ?? 'N/A';

$profile_picture = $user['profile_picture'] ?? '';
$upload_path = "uploads/" . $profile_picture;
if (empty($profile_picture) || !file_exists(__DIR__ . '/' . $upload_path)) {
    $upload_path = "assets/default.jpg";
    $version = rand();
} else {
    $version = filemtime(__DIR__ . '/' . $upload_path);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | StudyBuddy</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --glass-bg: rgba(255, 255, 255, 0.12);
      --glass-blur: blur(12px);
      --primary: #6a11cb;
      --accent: #2575fc;
      --text-light: #E4E6EB;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: url('assets/studybuddy-bg.png') center/cover fixed;
      color: #222;
      transition: background 0.3s, color 0.3s;
    }

    body.dark {
      background-color: #0e0f13;
      background-image: url('assets/studybuddy-bg-black.jpg');
      color: var(--text-light);
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: var(--glass-bg);
      backdrop-filter: var(--glass-blur);
      border-bottom: 1px solid rgba(255,255,255,0.15);
    }

    .studybuddy-title {
      font-size: 2rem;
      font-weight: 800;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .menu-toggle {
      font-size: 1.5rem;
      cursor: pointer;
      margin-right: 1rem;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: -260px;
      width: 260px;
      height: 100%;
      background: var(--glass-bg);
      backdrop-filter: var(--glass-blur);
      padding: 2rem 1rem;
      z-index: 999;
      transition: left 0.3s ease;
    }

    .sidebar.active {
      left: 0;
    }

    .sidebar a {
      display: block;
      margin: 1rem 0;
      font-weight: 600;
      color: inherit;
      text-decoration: none;
      font-size: 1.1rem;
    }

    .profile-wrap {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .profile-pic {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .profile-pic::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -75%;
      width: 50%;
      height: 200%;
      background: linear-gradient(120deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0.05) 100%);
      transform: rotate(25deg);
      animation: shine 2.5s ease-in-out infinite;
      pointer-events: none;
      z-index: 2;
    }

    .theme-toggle, .logout-btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      margin-left: 0.5rem;
      transition: background 0.3s;
    }

    .theme-toggle {
      background: #efefef;
    }

    body.dark .theme-toggle {
      background: #FFC107;
      color: #000;
    }

    .logout-btn {
      background: #ff4d4d;
      color: #fff;
    }

    body.dark .logout-btn {
      background: #FF6B6B;
    }

    .dashboard-grid {
      padding: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      margin-left: 0;
      transition: margin 0.3s;
    }

    .sidebar.active ~ main.dashboard-grid {
      margin-left: 260px;
    }

    .card {
      position: relative;
      background: #fff;
      border-radius: 1.5rem;
      padding: 2rem 1.5rem;
      text-align: center;
      font-weight: 600;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      cursor: pointer;
      overflow: hidden;
      transition: transform 0.3s;
    }

    .card::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -75%;
      width: 50%;
      height: 200%;
      background: linear-gradient(120deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.6) 50%, rgba(255,255,255,0.1) 100%);
      transform: rotate(25deg);
      pointer-events: none;
    }

    .card:hover::before {
      animation: shine 1s ease-in-out;
    }

    .card i {
      margin-bottom: 0.8rem;
      font-size: 2.2rem;
      color: var(--primary);
    }

    .card:hover {
      transform: translateY(-6px);
      background: linear-gradient(135deg, var(--primary), var(--accent));
      color: #fff;
    }

    .card:hover i {
      color: #fff;
    }

    body.dark .card {
      background: #1a1c24;
      color: var(--text-light);
    }

    body.dark .card:hover {
      background: linear-gradient(135deg, #4A00E0, #8E2DE2);
    }

    @keyframes shine {
      0% {
        left: -75%;
      }
      100% {
        left: 125%;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        left: -260px;
      }

      .sidebar.active {
        left: 0;
      }

      .dashboard-grid {
        margin-left: 0 !important;
        padding: 1rem;
      }
    }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <a href="dashboard.php" onclick="toggleSidebar()">🏠 Dashboard</a>
  <a href="messages.php" onclick="toggleSidebar()">📩 Messages</a>
  <a href="friends.php" onclick="toggleSidebar()">👥 Friends</a>
  <a href="leaderboard.php" onclick="toggleSidebar()">🏆 Leaderboard</a>
  <a href="notifications.php" onclick="toggleSidebar()">🔔 Notifications</a>
  <a href="profile.php" onclick="toggleSidebar()">⚙️ Settings</a>
  <a href="my_profile.php" onclick="toggleSidebar()">🪪 My Profile</a>
  <a href="typing_test.php" onclick="toggleSidebar()">⌨️ Typing Test</a>
  <a href="groups.php" onclick="toggleSidebar()">👨‍🎓 Study Groups</a>
</div>

<header class="header">
  <div class="d-flex align-items-center">
    <span class="menu-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></span>
    <span class="studybuddy-title">📚 StudyBuddy</span>
  </div>
  <div class="profile-wrap">
    <img src="<?= htmlspecialchars($upload_path) ?>?v=<?= $version ?>" class="profile-pic" alt="Profile Picture">
    <div>
      <div>Hello, <strong><?= htmlspecialchars($name) ?></strong></div>
      <div>CGPA: <?= htmlspecialchars($cgpa) ?> | Sem: <?= htmlspecialchars($semester) ?></div>
    </div>
    <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
    <form method="POST" action="logout.php">
      <button class="logout-btn">Logout</button>
    </form>
  </div>
</header>

<main class="dashboard-grid" id="mainGrid">
  <div class="card" onclick="location.href='messages.php'"><i class="fas fa-envelope"></i><br>Messages</div>
  <div class="card" onclick="location.href='friends.php'"><i class="fas fa-user-group"></i><br>Friends</div>
  <div class="card" onclick="location.href='leaderboard.php'"><i class="fas fa-trophy"></i><br>Leaderboard</div>
  <div class="card" onclick="location.href='notifications.php'"><i class="fas fa-bell"></i><br>Notifications</div>
  <div class="card" onclick="location.href='profile.php'"><i class="fas fa-cog"></i><br>Settings</div>
  <div class="card" onclick="location.href='my_profile.php'"><i class="fas fa-id-card"></i><br>My Profile</div>
  <div class="card" onclick="location.href='typing_test.php'"><i class="fas fa-keyboard"></i><br>Typing Test</div>
  <div class="card" onclick="location.href='groups.php'"><i class="fas fa-users"></i><br>Study Groups</div>
  <div class="card" onclick="location.href='study_tracker.php'"><i class="fas fa-calendar-check"></i><br>Study Tracker</div>
  <div class="card" onclick="location.href='cgpa.php'"><i class="fas fa-calculator"></i><br>CGPA Predictor</div>



</main>

<script>
  function toggleTheme() {
    document.body.classList.toggle('dark');
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
  }

  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
  }

  window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('theme') === 'dark' ||
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.body.classList.add('dark');
    }
  });
</script>
</body>
</html>
