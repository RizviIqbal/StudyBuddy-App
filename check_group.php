<?php
$conn = new mysqli("localhost", "root", "", "studybuddy");

if (isset($_GET['group_name'])) {
    $group_name = trim($_GET['group_name']);

    $stmt = $conn->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->bind_param("s", $group_name);
    $stmt->execute();
    $result = $stmt->get_result();

    echo $result->num_rows > 0 ? 'exists' : 'not_found';

    $stmt->close();
}
$conn->close();
?>
