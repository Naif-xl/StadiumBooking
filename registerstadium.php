<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'];
$user_mobile = $user_email = '';

$sqlUser = "SELECT user_mobile, user_email FROM users WHERE user_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($rowUser = $resultUser->fetch_assoc()) {
    $user_mobile = $rowUser['user_mobile'];
    $user_email = $rowUser['user_email'];
}
$stmtUser->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stadium_name = $conn->real_escape_string($_POST['stadium_name']);
    $stadium_address = $conn->real_escape_string($_POST['stadium_address']);
    $stadium_size = $conn->real_escape_string($_POST['stadium_size']);
    $stadium_details = $_POST['stadium_details'];
    $stadium_location = $conn->real_escape_string($_POST['stadium_location']);
    $stadium_type = $conn->real_escape_string($_POST['stadium_type']);
    $stadium_time_start = $_POST['stadium_time_start'];
    $stadium_time_end = $_POST['stadium_time_end'];
    $stadium_region = $conn->real_escape_string($_POST['stadium_region']);
    $stadium_city = $conn->real_escape_string($_POST['stadium_city']);
    $show_mobile = isset($_POST['show_mobile']) ? 1 : 0;
    $show_email = isset($_POST['show_email']) ? 1 : 0;
    $stadium_booking_price = $conn->real_escape_string($_POST['stadium_booking_price']);

    $sql = "INSERT INTO stadium (stadium_name, stadium_address, stadium_details, stadium_location, stadium_type, stadium_time_start, 
    stadium_time_end, stadium_region, stadium_city, stadium_size, user_id, show_mobile, show_email, stadium_booking_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssiiid",
        $stadium_name,
        $stadium_address,
        $stadium_details,
        $stadium_location,
        $stadium_type,
        $stadium_time_start,
        $stadium_time_end,
        $stadium_region,
        $stadium_city,
        $stadium_size,
        $user_id,
        $show_mobile,
        $show_email,
        $stadium_booking_price
    );

    if ($stmt->execute()) {
        $stadium_id = $conn->insert_id;
        $stmt->close();

        foreach ($_FILES['stadium_images']['name'] as $key => $name) {
            if ($_FILES['stadium_images']['error'][$key] == 0) {
                $uniqueFilename = md5(uniqid(rand(), true)) . basename($name);
                $destinationPath = "uploads/images/" . $uniqueFilename;

                if (move_uploaded_file($_FILES['stadium_images']['tmp_name'][$key], $destinationPath)) {
                    $sqlImg = "INSERT INTO gallery_images (file_name, stadium_id, user_id) VALUES (?, ?, ?)";
                    $stmtImg = $conn->prepare($sqlImg);
                    $stmtImg->bind_param("sii", $destinationPath, $stadium_id, $user_id);
                    $stmtImg->execute();
                    $stmtImg->close();
                }
            }
        }

        $sqlCheckRole = "SELECT role FROM users WHERE user_id = ?";
        $stmtCheckRole = $conn->prepare($sqlCheckRole);
        $stmtCheckRole->bind_param("i", $user_id);
        $stmtCheckRole->execute();
        $resultCheckRole = $stmtCheckRole->get_result();
        $currentUserRole = $resultCheckRole->fetch_assoc()['role'];
        $stmtCheckRole->close();

        if ($currentUserRole === 'user') {
            $sqlUpdateRole = "UPDATE users SET role = 'owner' WHERE user_id = ?";
            $stmtUpdateRole = $conn->prepare($sqlUpdateRole);
            $stmtUpdateRole->bind_param("i", $user_id);
            $stmtUpdateRole->execute();
            $stmtUpdateRole->close();
        }

        echo "<script type='text/javascript'>";
        echo "alert('تم رفع طلبك بنجاح بانتظار موافقة إدارة الموقع');";
        echo "window.location.href = 'control_panel.php';";
        echo "</script>";
        exit;
    } else {
        echo "خطأ: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html dir="rtl">

<head>
    <title>تسجيل ملعب</title>

    <style>
        .titleSub {
            color: red;
        }
    </style>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <?php if (isset($_SESSION['username'])): ?>
        <h4 class="msg">مرحبًا يا
            <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </h4>
    <?php endif; ?>

    <div class="container-Page mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <i class="fa fa-soccer-ball-o"></i>
                <h2 class="title">تسجيل ملعب جديد</h2>
                <hr class="title">

                <form name="form1" action="registerstadium.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="stadium_name" class="form-label">اسم الملعب:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <input placeholder="اسم الملعب" type="text" class="form-control" id="stadium_name"
                            name="stadium_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_region" class="form-label">المنطقة:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="stadium_region" name="stadium_region"
                            onchange="loadCities(this.value)" required>
                            <option value="" disabled selected hidden>اختر المنطقة...</option>
                            <?php
                            $sql = "SELECT region_name FROM region ORDER BY region_name";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['region_name'] . "'>" . $row['region_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_city" class="form-label">المدينة:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="stadium_city" name="stadium_city" required>
                            <option value="" disabled selected hidden>اختر المدينة...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_address" class="form-label">عنوان الملعب:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <input placeholder="عنوان الملعب" type="text" class="form-control" id="stadium_address"
                            name="stadium_address" required>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_details" class="form-label">تفاصيل الملعب:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <textarea class="form-control" id="stadium_details" name="stadium_details" cols="40" rows="5"
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_location" class="form-label">رابط الموقع في خرائط قوقل:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <input placeholder="رابط الموقع في خرائط قوقل" type="text" class="form-control"
                            id="stadium_location" name="stadium_location" required>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_type" class="form-label">نوع الملعب:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="stadium_type" name="stadium_type" required>
                            <option value="" disabled selected hidden>اختر نوع الملعب...</option>
                            <option value="قدم">كرة قدم</option>
                            <option value="بادل">كرة بادل</option>
                        </select>
                    </div>

                    <div class="mb-3" id="stadiumSizeContainer" style="display: none;">
                        <label for="stadium_size" class="form-label">حجم الملعب:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="stadium_size" name="stadium_size">
                            <option value="" disabled selected hidden>اختر حجم الملعب...</option>
                            <option value="6 * 6">6 * 6</option>
                            <option value="7 * 7">7 * 7</option>
                            <option value="8 * 8">8 * 8</option>
                            <option value="9 * 9">9 * 9</option>
                            <option value="10 * 10">10 * 10</option>
                            <option value="11 * 11">11 * 11</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_booking_price" class="form-label">سعر الحجز:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <input type="number" id="stadium_booking_price" name="stadium_booking_price"
                            class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stadium_time_start" class="form-label">بداية العمل:</label>
                            <sup class="titleSub" title="حقل إلزامي">*</sup>
                            <input type="time" class="form-control" id="stadium_time_start" name="stadium_time_start"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stadium_time_end" class="form-label">نهاية العمل:</label>
                            <sup class="titleSub" title="حقل إلزامي">*</sup>
                            <input type="time" class="form-control" id="stadium_time_end" name="stadium_time_end"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="show_contact_options">هل ترغب في عرض بيانات التواصل؟</label><br>
                        <input type="checkbox" id="show_mobile" name="show_mobile" <?php if ($user_mobile)
                            echo 'unchecked'; ?>>
                        <label for="show_mobile">رقم جوالك:&nbsp;
                            <?php echo $user_mobile ? $user_mobile : 'رقم الجوال غير متوفر'; ?>
                        </label><br>
                        <input type="checkbox" id="show_email" name="show_email" <?php if ($user_email)
                            echo 'unchecked'; ?>>
                        <label for="show_email">بريدك الإلكتروني:&nbsp;
                            <?php echo $user_email ? $user_email : 'البريد الإلكتروني غير متوفر'; ?>
                        </label>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stadium_image" class="form-label">صور الملعب:</label>
                            <sup class="titleSub" title="حقل إلزامي">*</sup><br>
                            <input type="file" id="stadium_image" accept=".jpg, .png, .JPEG" name="stadium_images[]"
                                multiple required>
                            <small class="form-text text-muted">رفع الصور بصيغة JPG, JPEG, أو PNG فقط.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">تسجيل الملعب</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loadCities(regionName) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("stadium_city").innerHTML = this.responseText;
                }
            };
            xhr.open(
                "GET",
                "get_cities.php?region_name=" + encodeURIComponent(regionName),
                true
            );
            xhr.send();
        }
    </script>

    <div class="mb-5"></div>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>