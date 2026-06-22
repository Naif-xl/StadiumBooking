<?php
include 'dbc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stadium_id = htmlspecialchars($_POST['stadium_id'], ENT_QUOTES, 'UTF-8');
    $booking_date = htmlspecialchars($_POST['booking_date'], ENT_QUOTES, 'UTF-8');
    $booking_time_start = htmlspecialchars($_POST['booking_time_start'], ENT_QUOTES, 'UTF-8');
    $booking_time_end = htmlspecialchars($_POST['booking_time_end'], ENT_QUOTES, 'UTF-8');


    $sql_stadium = "SELECT stadium_time_start, stadium_time_end FROM stadium WHERE stadium_id = ?";
    if ($stmt_stadium = $conn->prepare($sql_stadium)) {
        $stmt_stadium->bind_param("i", $stadium_id);
        $stmt_stadium->execute();
        $result_stadium = $stmt_stadium->get_result();
        if ($row_stadium = $result_stadium->fetch_assoc()) {
            $stadium_time_start = $row_stadium['stadium_time_start'];
            $stadium_time_end = $row_stadium['stadium_time_end'];

            $booking_start_time = DateTime::createFromFormat('H:i', $booking_time_start);
            $booking_end_time = DateTime::createFromFormat('H:i', $booking_time_end);
            $stadium_open_time = DateTime::createFromFormat('H:i', $stadium_time_start);
            $stadium_close_time = DateTime::createFromFormat('H:i', $stadium_time_end);

            if ($stadium_close_time < $stadium_open_time) {
                $stadium_close_time->modify('+1 day');
            }

            if ($booking_start_time < $stadium_open_time || $booking_end_time > $stadium_close_time) {
                echo "<script>alert('وقت الحجز خارج نطاق أوقات العمل للملعب.');
                window.location.href = 'stadium.php?id=" . $stadium_id . "';</script>";
            } else {
                $current_date = date("Y-m-d");

                if ($booking_date < $current_date) {
                    echo "<script>alert('لا يمكن حجز الملعب في تاريخ سابق.');
                    window.location.href = 'stadium.php?id=" . $stadium_id . "';</script>";
                } else {
                    $sql = "SELECT * FROM bookings WHERE stadium_id = ? AND booking_date = ? AND NOT (booking_time_start >= ? OR booking_time_end <= ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("issi", $stadium_id, $booking_date, $booking_time_end, $booking_time_start);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            echo "<script>alert('الملعب محجوز بالفعل في هذا التوقيت.');
                            window.location.href = 'stadium.php?id=" . $stadium_id . "';</script>";
                        } else {
                            $sql_insert = "INSERT INTO bookings (user_id, stadium_id, booking_date, booking_time_start, booking_time_end) VALUES (?, ?, ?, ?, ?)";
                            if ($stmt_insert = $conn->prepare($sql_insert)) {
                                $stmt_insert->bind_param("iisss", $user_id, $stadium_id, $booking_date, $booking_time_start, $booking_time_end);
                                if ($stmt_insert->execute()) {
                                    $booking_id = $stmt_insert->insert_id;
                                    echo "<script>alert('جاري التحويل إلى صفحة الدفع لإكمال الحجز');</script>";
                                    echo "<script>window.location.href='payments.php?booking_id=$booking_id';</script>";
                                    exit();
                                } else {
                                    echo "خطأ في إضافة الحجز: " . $stmt_insert->error;
                                }
                                $stmt_insert->close();
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        } else {
            echo "<script>alert('لم يتم العثور على معلومات الملعب');</script>";
        }
        $stmt_stadium->close();
    } else {
        echo "<script>alert('خطأ في استرجاع معلومات الملعب.');</script>";
    }
    $stmt_stadium->close();
} else {
    echo "<script>alert('طلب غير صحيح.');
    window.location.href = 'stadium.php?id=" . $stadium_id . "';</script>";
}

$conn->close();
?>