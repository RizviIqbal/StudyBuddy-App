<?php
// Change database name if needed
$conn = mysqli_connect("localhost", "root", "", "studybuddy");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
