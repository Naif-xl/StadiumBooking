<?php
include 'dbc.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('لم يتم تحديد معرف الملعب');</script>";
    exit;
}

$stadium_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($stadium_id === false || $stadium_id === null) {
    exit("<script>alert('معرف الملعب غير صالح');</script>");
}

$conn->begin_transaction();

try {
    $sql = "SELECT file_name FROM gallery_images WHERE stadium_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stadium_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $files = [];
    while ($row = $result->fetch_assoc()) {
        $files[] = $row['file_name'];
    }
    $stmt->close();

    $tables = ['bookings', 'comments', 'ratings', 'gallery_images'];
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) FROM $table WHERE stadium_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $stadium_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $sql = "DELETE FROM $table WHERE stadium_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $stadium_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $sql = "DELETE FROM stadium WHERE stadium_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stadium_id);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    $sql = "SELECT COUNT(*) FROM stadium WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        $sql = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_role);
        $stmt->fetch();
        $stmt->close();

        if ($current_role != 'admin') {
            $sql = "UPDATE users SET role = 'user' WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->commit();
    echo "<script>
    alert('تم حذف الملعب بنجاح');
    window.location.href = 'index.php';
  </script>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('حدث خطأ أثناء حذف الملعب: " . $e->getMessage() . "');</script>";
}

$conn->close();
?>