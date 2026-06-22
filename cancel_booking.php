<?php
include 'dbc.php';

$bookingId = htmlspecialchars($_POST['booking_id'], ENT_QUOTES, 'UTF-8');
$userId = $_SESSION['user_id'];

$sql = "SELECT * FROM bookings WHERE booking_id = ? AND user_id = ? AND status = 'غير مؤكد'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $sql = "DELETE FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
}

$stmt->close();
$conn->close();

header('Location: control_panel.php');
exit;
?>