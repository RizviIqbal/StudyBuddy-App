<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$user_id = $_SESSION['user_id'];

// Add task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $due = $_POST['due_date'] ?? null;
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, due_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $due);
    $stmt->execute();
    header("Location: study_tracker.php");
    exit();
}

// Mark as complete
if (isset($_GET['complete'])) {
    $id = intval($_GET['complete']);
    $conn->query("UPDATE tasks SET status='Completed' WHERE id=$id AND user_id=$user_id");
    header("Location: study_tracker.php");
    exit();
}

// Delete task
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tasks WHERE id=$id AND user_id=$user_id");
    header("Location: study_tracker.php");
    exit();
}

// Fetch tasks
$tasks = $conn->query("SELECT * FROM tasks WHERE user_id=$user_id ORDER BY due_date IS NULL, due_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Study Tracker | StudyBuddy</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f4f6fa;
      margin: 0;
      padding: 2rem;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    .task-form {
      max-width: 500px;
      margin: 1rem auto;
      display: flex;
      gap: 0.5rem;
    }
    .task-form input, .task-form button {
      padding: 0.7rem;
      font-size: 1rem;
    }
    .task-form input {
      flex: 1;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .task-form button {
      background: #6a11cb;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    table {
      margin: 2rem auto;
      width: 90%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 0.9rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #6a11cb;
      color: white;
    }
    .completed {
      text-decoration: line-through;
      color: gray;
    }
    .btn {
      padding: 0.4rem 0.7rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-complete {
      background: #28a745;
      color: white;
    }
    .btn-delete {
      background: #dc3545;
      color: white;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 1rem;
      color: #6a11cb;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>

<a class="back-btn" href="dashboard.php">&larr; Back to Dashboard</a>

<h1>📘 Study Tracker</h1>

<form method="POST" class="task-form">
  <input type="text" name="title" placeholder="New task..." required>
  <input type="date" name="due_date">
  <button type="submit">Add Task</button>
</form>

<?php if ($tasks->num_rows > 0): ?>
<table>
  <tr>
    <th>Task</th>
    <th>Due Date</th>
    <th>Status</th>
    <th>Actions</th>
  </tr>
  <?php while ($task = $tasks->fetch_assoc()): ?>
  <tr>
    <td class="<?= $task['status'] === 'Completed' ? 'completed' : '' ?>"><?= htmlspecialchars($task['title']) ?></td>
    <td><?= $task['due_date'] ?? 'N/A' ?></td>
    <td><?= $task['status'] ?></td>
    <td>
      <?php if ($task['status'] === 'Pending'): ?>
        <a class="btn btn-complete" href="?complete=<?= $task['id'] ?>">Mark Done</a>
      <?php endif; ?>
      <a class="btn btn-delete" href="?delete=<?= $task['id'] ?>" onclick="return confirm('Delete this task?')">Delete</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php else: ?>
  <p style="text-align:center;">No tasks yet. Add one above!</p>
<?php endif; ?>

</body>
</html>
