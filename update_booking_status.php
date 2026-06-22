<?php
include 'dbc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً.']);
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    try {
        $stadiumId = $_GET['id'];
        $newStatus = ($_GET['status'] == 1) ? 1 : 0;

        $sql = "UPDATE stadium SET disable_booking = ? WHERE stadium_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $newStatus, $stadiumId);
        $stmt->execute();

        header("Location: stadium.php?id=" . $stadiumId);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'لم يتم تلقي البيانات بشكل صحيح.']);
}
?>