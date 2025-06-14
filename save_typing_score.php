<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit();
}

$user_id = $_SESSION['user_id'];
$wpm = isset($_POST['wpm']) ? intval($_POST['wpm']) : 0;
$accuracy = isset($_POST['accuracy']) ? intval($_POST['accuracy']) : 0;
$mistakes = isset($_POST['mistakes']) ? intval($_POST['mistakes']) : 0;
$group_id = isset($_POST['group_id']) && is_numeric($_POST['group_id']) ? intval($_POST['group_id']) : null;

if ($wpm <= 0 || $accuracy < 0 || $accuracy > 100) {
    echo "Invalid data submitted.";
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
if ($conn->connect_error) {
    echo "Database connection error.";
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO typing_results (user_id, wpm, accuracy, mistakes, group_id, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiiii", $user_id, $wpm, $accuracy, $mistakes, $group_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Result saved successfully!";
} else {
    echo "❌ Failed to save result.";
}

$stmt->close();
$conn->close();
