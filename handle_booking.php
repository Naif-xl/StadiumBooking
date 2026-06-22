<?php
include 'dbc.php';

handlePostRequest($conn);

$conn->close();

function handlePostRequest($conn)
{
    if (isset($_POST['booking_id'], $_POST['action'])) {
        $bookingId = intval($_POST['booking_id']);
        $action = $_POST['action'];

        if (isValidBookingAction($action)) {
            processBookingAction($conn, $bookingId, $action);
        } else {
            echo "<script>alert('إجراء غير صالح');</script>";
        }
    } else {
        echo "<script>alert('بيانات غير كافية لتحديث الحجز');</script>";
    }
}

function isValidBookingAction($action)
{
    $validActions = ['approve', 'reject'];
    return in_array($action, $validActions);
}

function processBookingAction($conn, $bookingId, $action)
{
    $status = ($action === 'approve') ? 'مؤكد' : 'مرفوض';

    $sql = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error in query: " . $conn->error);
    }

    $stmt->bind_param("si", $status, $bookingId);
    if ($stmt->execute()) {
        echo "<script>alert('تم تأكيد حجز الملعب بنجاح.'); window.location.href = 'control_panel.php';</script>";
    } else {
        echo "حدث خطأ أثناء تحديث حالة الحجز: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
    }

    $stmt->close();
}
?>