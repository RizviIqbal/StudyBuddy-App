<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['group_id']) || !isset($_POST['message'])) {
    exit;
}

$conn = new mysqli("localhost", "root", "", "studybuddy");

$user_id = $_SESSION['user_id'];
$group_id = intval($_POST['group_id']);
$message = trim($_POST['message']);

if ($message !== "") {
    $stmt = $conn->prepare("INSERT INTO group_messages (group_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $group_id, $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
