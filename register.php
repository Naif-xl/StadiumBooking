<?php
include 'dbc.php';

$registerError = "";

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_username = $_POST['username'];

    $checkUserSql = "SELECT * FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkUserSql);
    $checkStmt->bind_param("s", $user_username);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $checkStmt->close();

    if ($result->num_rows > 0) {
        $registerError = "اسم المستخدم موجود بالفعل";
    } else {
        $user_fname = $_POST['users_fname'];
        $user_lname = $_POST['users_lname'];
        $user_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_mobile = $_POST['user_mobile'];
        $user_email = $_POST['user_email'];
        $user_address = $_POST['user_address'];
        $user_region = $conn->real_escape_string($_POST['user_region']);
        $user_city = $conn->real_escape_string($_POST['user_city']);

        $sql = "INSERT INTO users (users_fname, users_lname, username, password, user_mobile, user_email, user_region, user_city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $user_fname, $user_lname, $user_username, $user_password, $user_mobile, $user_email, $user_region, $user_city);

        if ($stmt->execute()) {
            echo "<script>
                    alert('تم التسجيل بنجاح');
                    window.location.href = 'login.php';
                  </script>";
        } else {
            $registerError = "خطأ في التسجيل: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>تسجيل جديد</title>

    <style>
        .titleSub {
            color: red;
        }
    </style>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="container-Page mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-3 col-md-8 col-sm-10">
                <?php if ($registerError) {
                    echo "<div class='alert alert-danger' id='registerError' role='alert'>$registerError</div>";
                } ?>

                <i class="fa fa-user-plus" aria-hidden="true"></i>
                <h2 class="title">تسجيل مستخدم جديد</h2>
                <hr class="title">

                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label for="users_fname" class="form-label">الاسم الأول:</label>
                        <input title="الاسم الأول" placeholder="الاسم الأول" type="text" class="form-control"
                            id="users_fname" name="users_fname">
                    </div>

                    <div class="mb-3">
                        <label for="users_lname" class="form-label">الاسم الأخير:</label>
                        <input title="الاسم الأخير" placeholder="الاسم الأخير" type="text" class="form-control"
                            id="users_lname" name="users_lname">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input dir="ltr" title="اسم المستخدم يجب أن يحتوي على حروف إنجليزية وأرقام فقط"
                            placeholder="username" type="text" class="form-control" id="username" name="username"
                            pattern="[A-Za-z0-9]+" oninput="validateEnglishOnly(this)" required>
                        <span id="usernameAvailability"></span>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input dir="ltr" title="كلمة المرور" placeholder="password" type="password"
                            class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="user_mobile" class="form-label">رقم الجوال:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input dir="ltr" placeholder="رقم الجوال" type="tel" class="form-control" id="user_mobile"
                            name="user_mobile" pattern="\d{10}" title="رقم الجوال يجب أن يكون 10 أرقام"
                            oninput="validatePhoneNumber(this)" value="05" required>
                        <span id="mobileAvailability"></span>
                    </div>

                    <div class="mb-3">
                        <label for="user_email" class="form-label">البريد الإلكتروني:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input dir="ltr" title="البريد الإلكتروني" placeholder="email" type="email"
                            class="form-control" id="user_email" name="user_email" required
                            oninput="checkEmailAvailability()">
                        <span id="emailAvailability"></span>
                    </div>

                    <div class="mb-3">
                        <label for="user_region" class="form-label">المنطقة:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="user_region" name="user_region"
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
                        <label for="user_city" class="form-label">المدينة:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="user_city" name="user_city" required>
                            <option value="" disabled selected hidden>اختر المدينة...</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">تسجيل</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mb-5"></div>

    <script>
        function loadCities(regionName) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("user_city").innerHTML = this.responseText;
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

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>