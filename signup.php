<?php
// Start session if needed
session_start();

// DB config
$host = "localhost";
$dbname = "studybuddy";
$username = "root";
$password = "";

// Connect
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(renderMessage("❌ Connection failed: " . $conn->connect_error, false));
}

// Input
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$raw_password = $_POST['password'];
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

// Prepare insert
$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $hashed_password);

// Execute and respond
if ($stmt->execute()) {
    renderMessage("✅ Signup successful! <a href='login.html'>Click here to login</a>", true);
} else {
    if (str_contains($stmt->error, 'Duplicate')) {
        renderMessage("⚠️ This email is already registered. <a href='login.html'>Log in instead?</a>", false);
    } else {
        renderMessage("❌ Something went wrong: " . htmlspecialchars($stmt->error), false);
    }
}

$stmt->close();
$conn->close();


// Reusable styled message output
function renderMessage($message, $success) {
    $color = $success ? "#4CAF50" : "#D32F2F";
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Signup Result - StudyBuddy</title>
        <style>
            body {
                font-family: 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #a18cd1, #fbc2eb);
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .message-box {
                background: #fff;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 8px 30px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }
            .message-box h2 {
                color: $color;
                margin-bottom: 20px;
            }
            a {
                display: inline-block;
                margin-top: 20px;
                text-decoration: none;
                color: #6c63ff;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class='message-box'>
            <h2>$message</h2>
        </div>
    </body>
    </html>";
    exit();
}
?>
