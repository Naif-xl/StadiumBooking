<?php
include 'dbc.php';

$stadium_id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');

$sql = "UPDATE stadium SET stadium_status = 'مقبول' WHERE stadium_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "<script>alert('خطأ في إعداد البيانات');</script>";
    exit;
}

$stmt->bind_param("i", $stadium_id);

if (!$stmt->execute()) {
    echo "<script>alert('خطأ في تنفيذ الاستعلام');</script>";
    exit;
}

$stmt->close();
echo "<script>alert('تم قبول الملعب بنجاح');</script>";
echo "<script>window.location.href='stadium.php?id={$stadium_id}';</script>";
exit;
?>