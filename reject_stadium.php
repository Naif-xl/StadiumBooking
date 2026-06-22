<?php
include 'dbc.php';
$stadium_id = $_GET['id'];

$sql = "UPDATE stadium SET stadium_status = 'مرفوض' WHERE stadium_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stadium_id);
$stmt->execute();
$stmt->close();

header("Location: control_panel.php");
exit;
?>
