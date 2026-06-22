<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$ownerBookings = array();
$ownerStadiums = array();
$pendingStadiums = array();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

if (($userRole === 'owner') || ($userRole === 'admin')) {
    $sqlOwnerBookings = "SELECT bookings.*, users.username, users.user_mobile, users.user_email, stadium.stadium_name, payments.payment_status, payments.amount, payments.created_at as payment_date
                         FROM bookings
                         INNER JOIN stadium ON bookings.stadium_id = stadium.stadium_id
                         INNER JOIN users ON bookings.user_id = users.user_id
                         LEFT JOIN payments ON bookings.booking_id = payments.booking_id
                         WHERE stadium.user_id = ?";
    $stmtOwnerBookings = $conn->prepare($sqlOwnerBookings);
    $stmtOwnerBookings->bind_param("i", $userId);
    $stmtOwnerBookings->execute();
    $ownerBookings = $stmtOwnerBookings->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtOwnerBookings->close();

    $sqlStadiums = "SELECT * FROM stadium WHERE user_id = ?";
    $stmtStadiums = $conn->prepare($sqlStadiums);
    $stmtStadiums->bind_param("i", $userId);
    $stmtStadiums->execute();
    $ownerStadiums = $stmtStadiums->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtStadiums->close();
}

if ($userRole === 'admin') {
    $sqlPendingStadiums = "SELECT * FROM stadium WHERE stadium_status = 'قيد الانتظار'";
    $pendingStadiums = $conn->query($sqlPendingStadiums)->fetch_all(MYSQLI_ASSOC);

    $query_select = "SELECT * FROM users";
    $result_select = mysqli_query($conn, $query_select);
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>لوحة التحكم</title>
    <style>
        .carousel-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .pagination {
            direction: ltr;
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .pagination .page-item {
            direction: rtl;
            list-style: none;
            margin-right: 5px;
        }

        .pagination .page-link {
            direction: rtl;
            border: 1px solid green;
            padding: 5px 10px;
            font-size: 16px;
            color: green;
        }

        .page-item.active .page-link {
            background-color: green;
            color: #fff;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .05);
        }

        .table-hover tbody tr:hover {
            color: #212529;
            background-color: rgba(0, 0, 0, .075);
        }
    </style>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="background-image"></div>

    <!--طلبات الحجز لمالك الملعب -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <?php foreach ($ownerBookings as $booking): ?>
                <?php
                $bookingDate = strtotime($booking['booking_date']);
                $currentDate = strtotime(date('Y-m-d'));
                if ($bookingDate >= $currentDate):
                    ?>
                    <div class="col-md-4 mb-1">
                        <div class="card card-custom">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a class="nav-link" title="بياناتي" href="user.php">مرحبًا يا
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </a>
                                </h5>
                                <?php if ($booking['status'] == 'غير مؤكد'): ?>
                                    <strong style="color: red;">لديك طلب حجز قيد الانتظار</strong>
                                <?php else: ?>
                                    <strong style="color: green;">حجز مؤكد</strong>
                                <?php endif; ?>
                                <?php
                                $userId = $booking['user_id'];
                                $userSql = "SELECT * FROM users WHERE user_id = ?";
                                $userStmt = $conn->prepare($userSql);
                                $userStmt->bind_param("i", $userId);
                                $userStmt->execute();
                                $userData = $userStmt->get_result()->fetch_assoc();
                                $userStmt->close();
                                ?>
                                <p class="card-text"><strong>بيانات طالب الحجز:</p></strong>
                                <?php if (!empty($userData)): ?>
                                    <ul>
                                        <li><strong>اسم المستخدم:</strong>
                                            <?php echo isset($userData['username']) ? htmlspecialchars($userData['username']) : 'غير محدد'; ?>
                                        </li>
                                        <li><strong>رقم الجوال:</strong>
                                            <?php echo isset($userData['user_mobile']) ? htmlspecialchars($userData['user_mobile']) : 'غير محدد'; ?>
                                        </li>
                                        <li><strong>البريد الإلكتروني:</strong>
                                            <?php echo isset($userData['user_email']) ? htmlspecialchars($userData['user_email']) : 'غير محدد'; ?>
                                        </li>
                                        <?php if (!empty($userData['first_name']) && !empty($userData['last_name'])): ?>
                                            <li><strong>الاسم الأول:</strong>
                                                <?php echo htmlspecialchars($userData['first_name']); ?>
                                            </li>
                                            <li><strong>الاسم الأخير:</strong>
                                                <?php echo htmlspecialchars($userData['last_name']); ?>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>بيانات المستخدم غير متاحة</p>
                                <?php endif; ?>
                                <h5 class="card-title">اسم الملعب:
                                    <?php echo $booking['stadium_name']; ?>
                                </h5>
                                <p class="card-text"><strong>تاريخ الحجز:</strong>
                                    <?php echo $booking['booking_date']; ?><br>الوقت: من
                                    <?php echo $booking['booking_time_start']; ?> إلى
                                    <?php echo $booking['booking_time_end']; ?>
                                </p>
                                <p class="card-text"><strong>حالة الحجز:</strong>
                                    <?php
                                    $status = isset($booking['status']) ? $booking['status'] : 'غير محدد';
                                    $statusColor = ($status === 'مؤكد') ? 'green' : (($status === 'مرفوض') ? 'red' : 'black');
                                    ?>
                                    <span style="color: <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </p>
                                <p><strong>حالة الدفع:</strong>
                                    <?php if ($booking['payment_status'] === 'عملية الدفع ناجحة'): ?>
                                        <br><label style="color: green;">عملية الدفع ناجحة</label>
                                        <br><strong>المبلغ:</strong>
                                        <?php echo htmlspecialchars($booking['amount']); ?>
                                        <br><strong>وقت وتاريخ الدفع:</strong>
                                        <?php echo htmlspecialchars($booking['created_at']); ?>
                                    <?php else: ?>
                                        <br><label style="color: red;">لم يتم الدفع بعد</label>
                                    <?php endif; ?>
                                </p>
                                <?php if ($booking['status'] == 'غير مؤكد'): ?>
                                    <form action="handle_booking.php" method="post"
                                        onsubmit="return confirm('هل أنت متأكد من أنك تريد الموافقة على هذا الحجز؟');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success">موافقة</button>
                                    </form>
                                    <form action="handle_booking.php" method="post"
                                        onsubmit="return confirm('هل أنت متأكد من أنك تريد رفض هذا الحجز؟');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger">رفض</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- حالة طلب الملعب لمالك الملعب -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner' || $_SESSION['role'] === 'admin' && !empty($ownerStadiums)): ?>
        <div class="container mt-5">
            <h2 class="text-center mb-4">حالة الملاعب الخاصة بك</h2>
            <div class="row justify-content-center">
                <?php foreach ($ownerStadiums as $stadium): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card card-custom">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($stadium['stadium_name']); ?>
                                </h5>
                                <p class="card-text">حالة الملعب:
                                    <?php
                                    $statusColor = ($stadium['stadium_status'] === 'مقبول') ? 'green' : (($stadium['stadium_status'] === 'مرفوض') ? 'red' : 'black');
                                    ?>
                                    <span style="color: <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($stadium['stadium_status']); ?>
                                    </span>
                                </p>
                                <?php if (!$stadium['stadium_status'] === 'مرفوض'): ?>
                                    <a href="stadium.php?id=<?php echo $stadium['stadium_id']; ?>" class="btn btn-primary">عرض
                                        التفاصيل</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- طلبات الملاعب في انتظار الموافقة إدارة الموقع -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !empty($pendingStadiums)): ?>
        <div class="container mt-5">
            <h2 class="text-center mb-4">طلبات الملاعب في انتظار الموافقة</h2>
            <div class="row justify-content-center">
                <?php foreach ($pendingStadiums as $row): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card card-custom">
                            <?php
                            $stmtImages = $conn->prepare("SELECT file_name FROM gallery_images WHERE stadium_id = ?");
                            $stmtImages->bind_param("i", $row['stadium_id']);
                            $stmtImages->execute();
                            $resultImages = $stmtImages->get_result();

                            $stadium_images = [];
                            while ($imageRow = $resultImages->fetch_assoc()) {
                                $stadium_images[] = $imageRow['file_name'];
                            }
                            $stmtImages->close();

                            if (count($stadium_images) > 0): ?>
                                <div id="carousel<?php echo $row['stadium_id']; ?>" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($stadium_images as $index => $image): ?>
                                            <div class="carousel-item <?php if ($index == 0)
                                                echo 'active'; ?>">
                                                <img src="<?php echo htmlspecialchars($image); ?>" class="d-block carousel-img"
                                                    alt="صورة الملعب">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($stadium_images) > 1): ?>
                                        <button class="carousel-control-prev" type="button"
                                            data-bs-target="#carousel<?php echo $row['stadium_id']; ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                            data-bs-target="#carousel<?php echo $row['stadium_id']; ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <img class="card-img-top stadium-thumbnail mx-auto" src="uploads/default-placeholder.png"
                                    alt="لا توجد صورة">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo $row['stadium_name']; ?>
                                </h5>
                                <p class="card-text">
                                    <?php echo $row['stadium_address']; ?>
                                </p>
                                <a href="stadium.php?id=<?php echo $row['stadium_id']; ?>" class="btn btn-primary">عرض
                                    التفاصيل</a>
                                <a href="approve_stadium.php?id=<?php echo $row['stadium_id']; ?>"
                                    class="btn btn-success">موافقة</a>
                                <a href="reject_stadium.php?id=<?php echo $row['stadium_id']; ?>" class="btn btn-danger">رفض</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Users Roles -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="container mt-5">
            <h2 class="text-center mb-4">إدارة المستخدمين</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr style='text-align: center;'>
                            <th scope="col">م</th>
                            <th scope="col">اسم المستخدم</th>
                            <th scope="col">نوع المستخدم</th>
                            <th scope="col">ملعب المالك</th>
                            <th scope="col">التحكم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($result_select) && $result_select) {
                            $counter = 1;
                            while ($row = mysqli_fetch_assoc($result_select)) {
                                echo "<tr id='row-{$row['user_id']}'>
                                    <td style='text-align: center;'>{$counter}</td>
                                    <td><a style='text-decoration: none;' href='user.php?user_id={$row['user_id']}'>{$row['username']}</a></td>
                                    <td>{$row['role']}</td>";
                                $sqlStadiums = "SELECT * FROM stadium WHERE stadium_status = 'مقبول' AND user_id = {$row['user_id']}";
                                $resultStadiums = $conn->query($sqlStadiums);
                                if ($resultStadiums) {
                                    echo "<td>";
                                    while ($stadiumRow = $resultStadiums->fetch_assoc()) {
                                        echo "<a style='text-decoration: none;' href='stadium.php?id={$stadiumRow['stadium_id']}'>{$stadiumRow['stadium_name']}</a><br>";
                                    }
                                    echo "</td>";
                                } else {
                                    echo "<td>لا يوجد ملاعب</td>";
                                }
                                ?>
                                <td style='text-align: center;'>
                                    <a style='text-decoration: none;' href='update_user.php?user_id=<?php echo $row['user_id']; ?>'
                                        title='تحديث بيانات المستخدم <?php echo $row['username']; ?>'>
                                        <i class='fa fa-edit' style='color:green' aria-hidden='true'></i>
                                    </a>&nbsp;&nbsp;

                                    <?php if ($row['is_blocked'] == 1): ?>
                                        <a style='text-decoration: none;' href='unban_user.php?user_id=<?php echo $row['user_id']; ?>'
                                            title='فتح حساب المستخدم <?php echo $row['username']; ?>'>
                                            <i class='fa fa-unlock' style='color:green' aria-hidden='true'></i>
                                        </a>
                                    <?php else: ?>
                                        <a style='text-decoration: none;' href='ban_user.php?user_id=<?php echo $row['user_id']; ?>'
                                            title='حظر المستخدم <?php echo $row['username']; ?>'>
                                            <i class='fa fa-ban' style='color:red' aria-hidden='true'></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <?php
                                echo "</tr>";
                                $counter++;
                            }
                        } else {
                            echo '<tr><td colspan="5">لا توجد بيانات</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- رسائل اتصل بنا -->
            <div class="mt-3">
                <a href="admin_messages.php" class="btn btn-primary">رسائل اتصل بنا</a>
            </div>
        </div>
    <?php endif; ?>

    <br><br>

    <script>
        window.onload = function () {
            var paginationList = document.querySelector('.pagination');
            var items = Array.from(paginationList.children);
            items.reverse();
            paginationList.innerHTML = '';
            items.forEach(function (item) {
                paginationList.appendChild(item);
            });
        };
    </script>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>