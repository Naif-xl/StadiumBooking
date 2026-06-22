<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true);
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stadiumbooking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}
?>