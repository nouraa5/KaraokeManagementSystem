<?php
// karaoke/includes/db.php
$host = 'localhost';
$user = 'root';         // adjust if needed
$pass = '';             // your MySQL password
$db   = 'karaoke_event'; // your db name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
