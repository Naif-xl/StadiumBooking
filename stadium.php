<?php
include 'dbc.php';

$stadium_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($stadium_id <= 0) {
    echo "<script type='text/javascript'>";
    echo "alert('مُعرّف الملعب غير صحيح');";
    echo "window.location.href = 'index.php';";
    echo "</script>";
    exit;
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$sql_status_condition = $is_admin ? "" : "AND s.stadium_status = 'مقبول'";

$sql = "SELECT s.*, u.user_mobile, u.user_email FROM stadium s LEFT JOIN users u ON s.user_id = u.user_id WHERE s.stadium_id = ? $sql_status_condition";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stadium_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stadium_name = $row['stadium_name'];
    $stadium_region = $row['stadium_region'];
    $stadium_city = $row['stadium_city'];
    $stadium_address = $row['stadium_address'];
    $stadium_details = $row['stadium_details'];
    $stadium_type = $row['stadium_type'];
    $stadium_size = $row['stadium_size'];
    $stadium_location = $row['stadium_location'];
    $stadium_time_start = $row['stadium_time_start'];
    $stadium_time_end = $row['stadium_time_end'];
    $show_mobile = $row['show_mobile'];
    $show_email = $row['show_email'];
    $user_mobile = $row['user_mobile'];
    $user_email = $row['user_email'];
    $disable_booking = $row['disable_booking'];
    $stadium_booking_price = $row['stadium_booking_price'];
} else {
    echo "<script type='text/javascript'>";
    echo "alert('عذرًا، الصفحة التي تحاول الوصول إليها غير متوفرة أو الرابط غير صحيح. سيتم تحويلك الآن إلى الصفحة الرئيسية.');";
    echo "window.location.href = 'index.php';";
    echo "</script>";
    exit;
}
$stmt->close();

$sqlImages = "SELECT * FROM gallery_images WHERE stadium_id = ?";
$stmtImages = $conn->prepare($sqlImages);
$stmtImages->bind_param("i", $stadium_id);
$stmtImages->execute();
$resultImages = $stmtImages->get_result();

$images = [];
while ($imageRow = $resultImages->fetch_assoc()) {
    $images[] = $imageRow['file_name'];
}
$stmtImages->close();

$comments = array();
$sql = "SELECT c.comment_id, c.comment_text, c.comment_date, u.username FROM comments c INNER JOIN users u ON c.user_id = u.user_id WHERE c.stadium_id = ? ORDER BY c.comment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stadium_id);
$stmt->execute();
$result = $stmt->get_result();

while ($comment_row = $result->fetch_assoc()) {
    $comments[] = $comment_row;
}
$stmt->close();

$sql = "SELECT COUNT(DISTINCT user_id) AS total_ratings, AVG(rating) AS average_rating FROM ratings WHERE stadium_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stadium_id);
$stmt->execute();
$result = $stmt->get_result();

if ($rating_info = $result->fetch_assoc()) {
    $total_ratings = $rating_info['total_ratings'];
    $average_rating = round($rating_info['average_rating'], 1);
}
$stmt->close();

$isOwnerOrAdmin = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $sqlRole = "SELECT role FROM users WHERE user_id = ?";
    $stmtRole = $conn->prepare($sqlRole);
    $stmtRole->bind_param("i", $user_id);
    $stmtRole->execute();
    $resultRole = $stmtRole->get_result();

    if ($resultRole->num_rows > 0) {
        $userRole = $resultRole->fetch_assoc()['role'];
        if ($userRole === 'admin' || ($userRole === 'owner' && $row['user_id'] == $user_id)) {
            $isOwnerOrAdmin = true;
        }
    }
    $stmtRole->close();
}

$isOwnerOfStadium = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $sqlOwner = "SELECT user_id FROM stadium WHERE stadium_id = ? AND user_id = ?";
    $stmtOwner = $conn->prepare($sqlOwner);
    $stmtOwner->bind_param("ii", $stadium_id, $user_id);
    $stmtOwner->execute();
    $stmtOwner->store_result();

    if ($stmtOwner->num_rows > 0) {
        $isOwnerOfStadium = true;
    }
    $stmtOwner->close();
}

$is_admin = false;

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $is_admin = true;
}
?>

<!DOCTYPE html>
<html dir="rtl">

<head>
    <title>
        <?php echo $stadium_name; ?>
    </title>

    <style>
        .carousel-item img {
            width: 750px;
            height: 500px;
            object-fit: cover;
            margin: auto;
        }

        .carousel-item {
            height: 500px;
        }

        .edit-button-container {
            text-align: left;
            margin-top: 20px;
            padding: 5px;
        }

        .btn-custom {
            color: white;
            border-color: #198754;
            background-color: #198754;
        }

        .btn-custom:hover {
            color: #198754;
            background-color: white;
        }

        .comments-section {
            margin-top: 40px;
        }

        .rating .star {
            font-size: 1.2rem;
            margin-right: 5px;
            cursor: pointer;
            color: #ffd333;
        }

        .rating .star.checked {
            color: #ffac33;
        }

        td {
            text-align: center;
        }

        .fixed-scroll {
            position: fixed;
            top: 50px;
            right: 50px;
        }
    </style>

</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <!-- حالة حجز الملعب -->
    <div class="booking-status mt-3 text-center">
        <div class="row">
            <div class="col-lg-4 mx-auto">
                <?php
                $current_date = date("Y-m-d");

                $booking_exists = false;
                $bookings = [];
                $sql = "SELECT * FROM bookings WHERE status = 'مؤكد' AND stadium_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $stadium_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    if ($row["booking_date"] >= $current_date) {
                        $bookings[] = $row;
                    }
                }

                if ($bookings && $disable_booking == 0) {
                    $counter = 1;
                    echo "<div class='alert alert-danger' role='alert'>";
                    echo "<strong>الملعب محجوز في الأوقات التالية:</strong>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-bordered'>";
                    echo "<tr class='alert alert-danger' role='alert''>";
                    echo "<th>م</th>";
                    echo "<th>تاريخ الحجز</th>";
                    echo "<th>وقت الحجز</th>";
                    echo "</tr>";
                    echo "<tbody class='alert alert-danger' role='alert''>";

                    foreach ($bookings as $booking) {
                        echo "<tr class='alert alert-danger' role='alert''>";
                        echo "<td>" . $counter;
                        echo "<td>" . $booking["booking_date"] . "</td>";
                        echo "<td>من " . $booking["booking_time_start"] . " - إلى " . $booking["booking_time_end"] . "</td>";
                        echo "</tr>";
                        $counter++;
                    }
                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";
                    echo "</div>";
                } else if ($disable_booking == 1) {
                    if ($bookings) {
                        $counter = 1;
                        echo "<div class='alert alert-danger' role='alert'>";
                        echo "<strong>الملعب محجوز في الأوقات التالية:</strong>";
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-bordered'>";
                        echo "<tr class='alert alert-danger' role='alert''>";
                        echo "<th>م</th>";
                        echo "<th>تاريخ الحجز</th>";
                        echo "<th>وقت الحجز</th>";
                        echo "</tr>";
                        echo "<tbody class='alert alert-danger' role='alert''>";

                        foreach ($bookings as $booking) {
                            echo "<tr class='alert alert-danger' role='alert''>";
                            echo "<td>" . $counter;
                            echo "<td>" . $booking["booking_date"] . "</td>";
                            echo "<td>من " . $booking["booking_time_start"] . " - إلى " . $booking["booking_time_end"] . "</td>";
                            echo "</tr>";
                            $counter++;
                        }
                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "<div class='alert alert-warning' role='alert'>";
                    echo "<strong>الحجز مغلق مؤقتاً</strong>";
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-success' role='alert'>";
                    echo "<strong>الملعب شاغر في الوقت الحالي</strong>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- زر إجراء حجز -->
    <div id="Moving">
        <table style="margin-right: auto; margin-left: auto;">
            <tr>
                <td colspan="2">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a rel="preload" href="login.php" class="btn btn-primary">
                            حجز الملعب
                        </a>
                    <?php else: ?>
                        <?php
                        $buttonTitle = ($disable_booking == 1) ? 'الحجز مغلق مؤقتاً' : 'حجز الملعب';
                        ?>
                        <button type="button" title="<?php echo htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8'); ?>"
                            class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal" id="bookingBtn"
                            <?php echo ($disable_booking == 1) ? 'disabled' : ''; ?>>
                            <?php echo htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <?php if ($isOwnerOrAdmin): ?>
                    <td>
                        <button type="button" id="closebtn" onclick="updateBookingStatus(1)" class="btn btn-danger btn-sm"
                            <?php echo ($disable_booking == 1) ? 'disabled' : ''; ?>>
                            قفل الحجز
                        </button>
                    </td>
                    <td>
                        <button type="button" id="openbtn" onclick="updateBookingStatus(0)" class="btn btn-success btn-sm"
                            <?php echo ($disable_booking == 0) ? 'disabled' : ''; ?>>
                            فتح الحجز
                        </button>
                    </td>
                <?php endif; ?>
            </tr>
        </table>

        <!-- زر تعديل وحذف الملعب -->
        <table style="margin-right: auto; margin-left: auto;">
            <tr>
                <td>
                    <?php if ($isOwnerOrAdmin): ?>
                        <a href="update_stadium.php?id=<?php echo $stadium_id; ?>" class="btn btn-success btn-sm"
                            title="تعديل الملعب">
                            <i class="fa fa-pencil-square-o" style="color:white"></i>
                        </a>
                    </td>
                    <td>
                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $stadium_id; ?>)"
                            class="btn btn-danger btn-sm" title="حذف الملعب">
                            <i class="fa fa-trash-o" style="color:white"></i>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- بداية محتوى الصفحة -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="mb-4 text-center">
                    ملعب
                    <?php echo $stadium_name; ?>
                </h1>

                <!-- عرض صور الملعب -->
                <div id="stadiumCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?php if ($index == 0)
                                echo 'active'; ?>">
                                <img loading="lazy" src="<?php echo htmlspecialchars($image); ?>" class="d-block w-100"
                                    alt="صورة الملعب">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#stadiumCarousel"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#stadiumCarousel"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <br>

                <!-- بيانات التواصل -->
                <?php if ($show_mobile || $show_email): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white">بيانات التواصل مع مالك الملعب</div>
                        <div class="card-body">
                            <?php if ($show_mobile): ?>
                                <p>رقم الجوال:
                                    <?php echo htmlspecialchars($user_mobile); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($show_email): ?>
                                <p>البريد الإلكتروني:
                                    <?php echo htmlspecialchars($user_email); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- تفاصيل الملعب -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">تفاصيل الملعب</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">المنطقة</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($stadium_region); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">المدينة</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($stadium_city); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">عنوان الملعب</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($stadium_address); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">رابط الموقع في خرائط قوقل</div>
                                    <div class="card-body">
                                        <a href="<?php echo htmlspecialchars($stadium_location); ?>"
                                            class="btn btn-success mb-2" target="_blank">عرض على الخريطة</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">نوع الملعب</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($stadium_type); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">أوقات العمل</div>
                                    <div class="card-body">
                                        <p class="card-text">من
                                            <?php echo htmlspecialchars($stadium_time_start); ?> إلى
                                            <?php echo htmlspecialchars($stadium_time_end); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card mt-4">
                                        <div class="card-header bg-success text-white">معلومات إضافية للملعب</div>
                                        <div class="card-body">
                                            <p class="card-text">
                                                <?php echo nl2br(htmlspecialchars($stadium_details)); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($stadium_type === 'قدم'): ?>
                                    <div class="col-md-4">
                                        <div class="card mt-4">
                                            <div class="card-header bg-success text-white">حجم الملعب</div>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <?php echo htmlspecialchars($stadium_size); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col-md-4">
                                    <div class="card mt-4">
                                        <div class="card-header bg-success text-white">سعر الحجز</div>
                                        <div class="card-body">
                                            <p class="card-text" style="color: #008000;">
                                                <?php
                                                $formatted_price = (strpos($stadium_booking_price, '.') !== false)
                                                    ? rtrim(rtrim(number_format($stadium_booking_price, 2), '0'), '.') . " ريال سعودي"
                                                    : number_format($stadium_booking_price, 0) . " ريال سعودي";
                                                echo "<strong style='color: #008000;'>" . $formatted_price . "</strong>";
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تقييم الملعب -->
                <div class="rating-section mt-4">
                    <h3 class="mb-3">تقييم الملعب</h3>

                    <div class="mb-3">
                        <span class="badge bg-success">عدد التقييمات:
                            <span id="totalRatingsDisplay">
                                <?php echo $total_ratings; ?>
                            </span>
                        </span>
                        <span class="badge bg-warning text-dark">متوسط التقييم:
                            <span id="averageRatingDisplay">
                                <?php echo $average_rating; ?>
                            </span> / 5
                        </span>
                    </div>
                    <?php if (isset($_SESSION['user_id']) && !$isOwnerOfStadium): ?>
                        <div class="card">
                            <div class="card-body">
                                <form id="ratingForm" onsubmit="return submitRatingForm();" method="post">
                                    <input type="hidden" name="stadium_id" value="<?php echo $stadium_id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">اختر تقييمك:</label>
                                        <div class="rating d-flex justify-content-start">
                                            <i class="fa fa-star star" data-value="1"></i>
                                            <i class="fa fa-star star" data-value="2"></i>
                                            <i class="fa fa-star star" data-value="3"></i>
                                            <i class="fa fa-star star" data-value="4"></i>
                                            <i class="fa fa-star star" data-value="5"></i>
                                            <input type="hidden" name="rating" id="rating" value="0">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- التعليقات -->
                <div class="comments-section mt-4">
                    <h3 class="mb-3">تعليقات</h3>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">أضف تعليقك</h5>
                                <form id="commentForm" onsubmit="return submitCommentForm();" method="post">
                                    <input type="hidden" name="stadium_id" value="<?php echo $stadium_id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    <div class="mb-3">
                                        <label for="comment" class="form-label">تعليق</label>
                                        <textarea class="form-control" id="comment" name="comment" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-send-o"></i>
                                        إرسال</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (count($comments) > 0): ?>
                        <div id="commentsList" class="list-group">
                            <?php foreach ($comments as $comment): ?>
                                <div class="list-group-item" id="comment_<?php echo $comment['comment_id']; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <p class="mb-1">تعليق من
                                            <?php echo htmlspecialchars($comment['username']); ?>
                                        </p>
                                        <small>
                                            <?php echo htmlspecialchars($comment['comment_date']); ?>
                                        </small>
                                    </div>
                                    <hr>
                                    <p class="mb-1" id="commentText_<?php echo $comment['comment_id']; ?>">
                                        <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                                    </p>
                                    <?php if ($is_admin): ?>
                                        <button class="btn btn-warning btn-sm"
                                            onclick="editComment('<?php echo $comment['comment_id']; ?>')"><i
                                                class="fa fa-pencil-square-o" style="color:white"></i></button>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="deleteComment('<?php echo $comment['comment_id']; ?>')"><i
                                                class="fa fa-trash-o" style="color:white"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>لا يوجد تعليقات حتى الآن.</p>
                    <?php endif; ?>
                </div>

                <div class="container md=6"></div>

                <!-- نافذة تعديل التعليق -->
                <div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">تعديل التعليق</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <textarea class="form-control" id="editCommentText"></textarea>
                                <input type="hidden" id="editCommentId">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="button" class="btn btn-primary" onclick="submitEditComment()">حفظ
                                    التعديل</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- النافذة المنبثقة للحجز -->
                <?php if (!$isOwnerOfStadium): ?>
                    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bookingModalLabel">حجز الملعب</h5><br>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p style="color:darkgreen" class="card-text">أوقات العمل من
                                        <?php echo $stadium_time_start; ?> إلى
                                        <?php echo $stadium_time_end; ?>
                                    </p>
                                    <p style="color:red" class="card-text">* الحجز مدته ساعتان فقط
                                    </p>
                                    <p style="color: #a77d00">سعر الحجز:
                                        <?php echo $stadium_booking_price; ?>
                                    </p>
                                    <form id="bookingForm" action="book_stadium.php" method="post">
                                        <input type="hidden" name="stadium_id" value="<?php echo $stadium_id; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                        <div class="mb-3">
                                            <label for="booking_date" class="form-label">تاريخ الحجز</label>
                                            <input type="date" class="form-control" id="booking_date" name="booking_date"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="booking_time_start" class="form-label">وقت بداية
                                                الحجز</label>
                                            <input type="time" id="booking_time_start" name="booking_time_start"
                                                onchange="setEndTime()" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="booking_time_end" class="form-label">وقت نهاية الحجز</label>
                                            <input type="time" id="booking_time_end" name="booking_time_end" required>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                    <button type="submit" form="bookingForm" class="btn btn-primary">حجز
                                        الملعب</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <script>
                $(document).ready(function () {
                    $(window).scroll(function () {
                        var scrollPos = $(window).scrollTop();
                        $('#Moving').toggleClass('fixed-scroll', scrollPos > 100);
                    });
                });

                function updateBookingStatus(status) {
                    var stadiumId = <?php echo $stadium_id; ?>;
                    window.location.href = 'update_booking_status.php?id=' + stadiumId + '&status=' + status;
                }

                function submitCommentForm() {
                    var formData = new FormData(document.getElementById("commentForm"));
                    fetch("add_comment.php", {
                        method: "POST",
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                var commentsList = document.getElementById("commentsList");
                                if (commentsList) {
                                    var newComment = document.createElement("div");
                                    newComment.className = "list-group-item";
                                    newComment.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1">تعليق من ${data.username}</p>
                        <small>${data.comment_date}</small>
                    </div>
                    <hr>
                    <p class="mb-1">${data.comment_text}</p>
                `;
                                    commentsList.insertBefore(newComment, commentsList.firstChild);
                                } else {
                                    var newCommentsList = document.createElement("div");
                                    newCommentsList.id = "commentsList";
                                    newCommentsList.className = "list-group";
                                    document.querySelector('.comments-section').appendChild(newCommentsList);

                                    newCommentsList.innerHTML = `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <p class="mb-1">تعليق من ${data.username}</p>
                            <small>${data.comment_date}</small>
                        </div>
                        <hr>
                        <p class="mb-1">${data.comment_text}</p>
                    </div>
                `;
                                }
                            } else {
                                alert("حدث خطأ أثناء إضافة التعليق");
                            }
                        })
                        .catch((error) => {
                            console.error("Error:", error);
                        });
                    return false;
                }

                function confirmDelete(stadiumId) {
                    var confirmDelete = confirm("هل أنت متأكد أنك تريد حذف هذا الملعب؟");
                    if (confirmDelete) {
                        window.location.href = 'delete_stadium.php?id=' + stadiumId;
                    }
                }
            </script>

            <a href="#" title="أعلى الصفحة"><i class="fa fa-arrow-circle-up"
                    style="font-size:36px;color:#008000"></i></a>

            <!-- Footer -->
            <?php include('layouts/footer.php'); ?>

            <?php $conn->close(); ?>
        </div>
    </div>
</body>

</html>