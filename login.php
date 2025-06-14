<?php
session_start();

$host = 'localhost';
$db = 'studybuddy';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Incorrect password.";
    }
} else {
    $error = "No user found with that email.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Error - StudyBuddy</title>
  <style>
    body {
      background: #f3f4f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .error-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 400px;
    }
    h2 {
      color: #ff3b3b;
      margin-bottom: 10px;
    }
    p {
      color: #444;
      margin-bottom: 20px;
    }
    a {
      display: inline-block;
      padding: 10px 20px;
      background: #6c63ff;
      color: white;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }
    a:hover {
      background: #574fd6;
    }
  </style>
</head>
<body>
  <div class="error-box">
    <h2>❌ Login Failed</h2>
    <p><?= htmlspecialchars($error) ?></p>
    <a href="login.html">← Try Again</a>
  </div>
</body>
</html>
