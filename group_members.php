<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    exit;
}

$conn = new mysqli("localhost", "root", "", "studybuddy");
$group_id = intval($_GET['group_id']);

$sql = "SELECT u.id, u.name 
        FROM group_members gm 
        JOIN users u ON gm.user_id = u.id 
        WHERE gm.group_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = ['id' => $row['id'], 'username' => $row['name']];
}

echo json_encode($members);
$stmt->close();
$conn->close();
