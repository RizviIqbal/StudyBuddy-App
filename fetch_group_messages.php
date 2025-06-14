<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    exit;
}

$conn = new mysqli("localhost", "root", "", "studybuddy");

$group_id = intval($_GET['group_id']);
$user_id = $_SESSION['user_id'];

// Get messages from this group
$sql = "SELECT gm.*, u.name, u.profile_pic
        FROM group_messages gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.created_at ASC";  // Ensure this matches your table

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $isSelf = $row['user_id'] == $user_id;
    $name = $isSelf ? "You" : htmlspecialchars($row['name']);
    $profile = file_exists("uploads/" . $row['profile_pic']) ? "uploads/" . $row['profile_pic'] : "uploads/default.png";
    $time = date("H:i", strtotime($row['created_at']));
    $bubbleClass = $isSelf ? "sent" : "received";

    echo "
    <div class='message $bubbleClass'>
        <img src='$profile' alt='PFP'>
        <div class='text'>
            <strong>$name</strong> • <small>$time</small><br>
            " . nl2br(htmlspecialchars($row['message'])) . "
        </div>
    </div>
    ";
}

$stmt->close();
$conn->close();
