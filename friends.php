<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT cgpa, semester FROM users WHERE id = $user_id")->fetch_assoc();

$filterCgpa = $_GET['cgpa'] ?? '';
$filterSemester = $_GET['semester'] ?? '';

$where = [];
if ($filterCgpa !== '') $where[] = "cgpa >= " . floatval($filterCgpa);
if ($filterSemester !== '') $where[] = "semester = " . intval($filterSemester);
$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

$results = $conn->query("SELECT id, name, email, cgpa, semester, profile_picture FROM users $whereClause");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Friends | StudyBuddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: #333;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        :root {
            --light-bg: #f5f7fb;
            --dark-bg: #1a1d24;
            --light-card: #fff;
            --dark-card: #2a2d3e;
            --primary: #2575fc;
        }

        body.dark {
            background-color: var(--dark-bg);
            color: #e4e6eb;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header .button-group {
            display: flex;
            gap: 10px;
        }

        .theme-toggle, .back-button {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .theme-toggle {
            background-color: #ffc107;
            color: #000;
        }

        .back-button {
            background-color: var(--primary);
            color: #fff;
            text-decoration: none;
        }

        .theme-toggle:hover {
            background-color: #e0a800;
        }

        .back-button:hover {
            background-color: #1a5de0;
            color: #fff;
        }

        .dark .theme-toggle {
            background-color: #1bcfb4;
            color: #fff;
        }

        .filter-box {
            background-color: var(--light-card);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .dark .filter-box {
            background-color: var(--dark-card);
        }

        .filter-box input,
        .filter-box button {
            width: 100%;
            max-width: 250px;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .filter-box button {
            background-color: var(--primary);
            color: #fff;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }

        .results {
            background-color: var(--light-card);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .dark .results {
            background-color: var(--dark-card);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #ddd;
            padding: 20px 0;
        }

        .user-card:last-child {
            border-bottom: none;
        }

        .user-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2575fc;
        }

        .user-details strong {
            font-size: 1.1rem;
        }

        .dark .user-card {
            border-color: #444;
        }

        @media (max-width: 600px) {
            .user-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .user-card img {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Find Friends</h2>
        <div class="button-group">
            <a href="dashboard.php" class="back-button">⬅ Dashboard</a>
            <button class="theme-toggle" onclick="toggleTheme()">🌓 Toggle Theme</button>
        </div>
    </div>

    <div class="filter-box">
        <form method="GET">
            <label>Minimum CGPA:</label>
            <input type="number" step="0.01" name="cgpa" value="<?= htmlspecialchars($filterCgpa) ?>">

            <label>Semester:</label>
            <input type="number" name="semester" value="<?= htmlspecialchars($filterSemester) ?>">

            <button type="submit">Search</button>
        </form>
    </div>

    <div class="results">
        <h4>Matching Users:</h4>
        <?php while ($row = $results->fetch_assoc()): 
            $pic = $row['profile_picture'] ?: 'default.jpg';
            $upload_path = "uploads/" . $pic;
            $full_path = __DIR__ . '/' . $upload_path;
            $path = (file_exists($full_path) && !empty($row['profile_picture']))
                    ? $upload_path . '?v=' . filemtime($full_path)
                    : "assets/default.jpg?v=" . rand(1, 1000);
        ?>
            <div class="user-card">
                <img src="<?= htmlspecialchars($path) ?>" alt="Profile Picture">
                <div class="user-details">
                    <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                    📧 <?= htmlspecialchars($row['email']) ?><br>
                    🎓 CGPA: <?= htmlspecialchars($row['cgpa']) ?> | Semester: <?= htmlspecialchars($row['semester']) ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    function toggleTheme() {
        document.body.classList.toggle("dark");
        localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
    }

    (function () {
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.add("dark");
        }
    })();
</script>
</body>
</html>
