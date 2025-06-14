<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");

$result = $conn->query("
    SELECT u.name, t.wpm, t.accuracy, t.created_at
    FROM typing_results t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.wpm DESC, t.accuracy DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Typing Leaderboard | StudyBuddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            margin: 0;
            padding: 40px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        h2 {
            color: white;
            margin-bottom: 30px;
        }

        table {
            width: 90%;
            max-width: 950px;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        th, td {
            padding: 16px 20px;
            text-align: center;
            font-size: 16px;
        }

        th {
            background: #4A00E0;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .back-btn {
            margin-top: 30px;
            padding: 12px 28px;
            background: #00C896;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: #00b184;
        }
    </style>
</head>
<body>

    <h2>🏆 Typing Leaderboard</h2>

    <table>
        <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>WPM</th>
            <th>Accuracy</th>
            <th>Date</th>
        </tr>
        <?php
        $rank = 1;
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $rank++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= $row['wpm'] ?></td>
            <td><?= $row['accuracy'] ?>%</td>
            <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a class="back-btn" href="dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
