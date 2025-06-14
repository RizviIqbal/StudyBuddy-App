<?php
session_start();

if (!isset($_SESSION['user_id'], $_POST['receiver_id'], $_POST['message'])) {
    http_response_code(400);
    exit('Invalid request');
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
if ($conn->connect_error) {
    http_response_code(500);
    exit('Database connection error');
}

$sender = intval($_SESSION['user_id']);
$receiver = intval($_POST['receiver_id']);
$message = trim($_POST['message']);

// ✅ Basic message length and content validation
if ($message === "" || strlen($message) > 1000) {
    http_response_code(400);
    exit('Message is empty or too long');
}

// ✅ Insert message
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, NOW())");
if ($stmt) {
    $stmt->bind_param("iis", $sender, $receiver, $message);
    $stmt->execute();
    $stmt->close();
}

// ✅ Remove typing status (if implemented)
$stmt = $conn->prepare("DELETE FROM typing_status WHERE sender_id = ? AND receiver_id = ?");
if ($stmt) {
    $stmt->bind_param("ii", $sender, $receiver);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

http_response_code(200);
echo "Message sent";
