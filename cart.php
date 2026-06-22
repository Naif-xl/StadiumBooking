<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_mobile = "";
$user_email = "";

$userId = $_SESSION['user_id'];
$userBookings = array();

if ($userId) {
    $sql = "SELECT bookings.*, stadium.stadium_name, users.username, users.user_mobile, users.user_email, payments.payment_status, payments.amount, payments.created_at as payment_date
        FROM bookings 
        INNER JOIN stadium ON bookings.stadium_id = stadium.stadium_id
        INNER JOIN users ON stadium.user_id = users.user_id
        LEFT JOIN payments ON bookings.booking_id = payments.booking_id
        WHERE bookings.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_mobile = $row['user_mobile'];
            $user_email = $row['user_email'];
            $userBookings[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>Cart</title>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="background-image"></div>

    <!-- طلبات الحجز -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <?php foreach ($userBookings as $booking): ?>
                <div class="col-md-4 mb-1">
                    <div class="card card-custom">
                        <div class="card-body">
                            <h5 class="card-title">
                                مرحبًا يا
                                <strong>
                                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </strong>
                                <?php
                                if ($booking['status'] === 'غير مؤكد') {
                                    echo htmlspecialchars("لديك طلب حجز بانتظار موافقة مالك الملعب");
                                } elseif ($booking['status'] === 'مؤكد') {
                                    echo htmlspecialchars("تم تأكيد حجزك بنجاح");
                                }
                                ?>
                            </h5>
                            <div class="container">
                                <?php if (isset($booking['stadium_name'])): ?>
                                    <p><span class="label"><strong>اسم الملعب:</strong></span>
                                        <?php echo htmlspecialchars($booking['stadium_name']); ?>
                                    </p>
                                <?php endif; ?>
                                <p><strong>في تاريخ: </strong>
                                    <?php echo htmlspecialchars($booking['booking_date']); ?>
                                </p>
                                <p><strong>الوقت: من</strong>
                                    <?php echo htmlspecialchars($booking['booking_time_start']); ?> إلى
                                    <?php echo htmlspecialchars($booking['booking_time_end']); ?>
                                </p>
                                <p><strong>حالة الحجز:</strong>
                                    <?php
                                    $status = isset($booking['status']) ? $booking['status'] : 'غير محدد';
                                    $statusColor = ($status === 'مؤكد') ? 'green' : (($status === 'مرفوض') ? 'red' : 'black');
                                    ?>
                                    <span style="color: <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </p>
                                <p><strong>حالة الدفع</strong>
                                    <?php if ($booking['payment_status'] === 'عملية الدفع ناجحة'): ?>
                                        <br><label style="color: green">عملية الدفع ناجحة</label>
                                        <br><strong>المبلغ:</strong>
                                        <?php echo htmlspecialchars($booking['amount']); ?>
                                        <br><strong>تاريخ الدفع:</strong>
                                        <?php echo htmlspecialchars($booking['created_at']); ?>
                                    <?php else: ?>
                                        <br><label style="color: red">لم يتم الدفع بعد</label>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php if ($booking['status'] === 'مؤكد'): ?>
                                <div class=" confirmation-details">
                                    <p class="confirmation-text">بيانات التواصل مع صاحب الملعب:</p>
                                    <ul class="contact-info">
                                        <li><strong>رقم الجوال:</strong>
                                            <?php echo htmlspecialchars($user_mobile); ?>
                                        </li>
                                        <li><strong>البريد الإلكتروني:</strong>
                                            <?php echo htmlspecialchars($user_email); ?>
                                        </li>
                                    </ul>
                                    <a href='stadium.php?id=<?php echo htmlspecialchars($booking['stadium_id'], ENT_QUOTES, 'UTF-8'); ?>'
                                        class='btn btn-success'>عرض الملعب</a>
                                </div>
                            <?php endif; ?>
                            <?php if ($booking['status'] == 'غير مؤكد'): ?>
                                <form action="cancel_booking.php" method="post">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" class="btn btn-danger">إلغاء الحجز</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $stmt->close(); ?>
</body>

</html>