<?php
session_start();
if (!isset($_SESSION['user_id'], $_GET['chat'])) {
    http_response_code(400);
    exit("Invalid request");
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
if ($conn->connect_error) {
    http_response_code(500);
    exit("DB connection failed");
}

$sender = intval($_SESSION['user_id']);
$receiver = intval($_GET['chat']);

$sql = "SELECT sender_id, message, timestamp FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY timestamp ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $sender, $receiver, $receiver, $sender);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $isSender = $row['sender_id'] == $sender;
    $message = nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'));
    $time = date("H:i", strtotime($row['timestamp']));

    echo '<div class="msg ' . ($isSender ? 'sent' : 'received') . '">';
    echo '  <div class="bubble">' . $message;
    echo '    <div class="time">' . $time . '</div>';
    echo '  </div>';
    echo '</div>';
}

$stmt->close();
$conn->close();
?>
