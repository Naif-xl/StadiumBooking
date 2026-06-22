<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$is_admin = false;

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $is_admin = true;
}

$updateError = "";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$regions = [];
$sql = "SELECT region_name FROM region ORDER BY region_name";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $regions[] = $row['region_name'];
}

if ($user_id > 0) {
    $getUserSql = "SELECT * FROM users WHERE user_id = ?";
    $getUserStmt = $conn->prepare($getUserSql);
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    $userResult = $getUserStmt->get_result();
    $getUserStmt->close();

    if ($userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();

        $user_username = $userData['username'];
        $user_fname = $userData['users_fname'];
        $user_lname = $userData['users_lname'];
        $user_mobile = $userData['user_mobile'];
        $user_email = $userData['user_email'];
        $user_region = $userData['user_region'];
        $user_city = $userData['user_city'];
        $user_role = $userData['role'];
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_id > 0) {
    $user_username = $_POST['username'];
    $user_fname = $_POST['users_fname'];
    $user_lname = $_POST['users_lname'];
    $user_mobile = $_POST['user_mobile'];
    $user_email = $_POST['user_email'];
    $user_region = $conn->real_escape_string($_POST['user_region']);
    $user_city = $conn->real_escape_string($_POST['user_city']);
    $user_role = $_POST['role'];

    $is_password_changed = isset($_POST['new_password']) && !empty($_POST['new_password']) && isset($_POST['confirm_password']) && !empty($_POST['confirm_password']) && $_POST['new_password'] === $_POST['confirm_password'];

    $stadiumCheckSql = "SELECT COUNT(*) AS num_stadiums FROM stadium WHERE user_id = ?";
    $stadiumCheckStmt = $conn->prepare($stadiumCheckSql);
    $stadiumCheckStmt->bind_param("i", $user_id);
    $stadiumCheckStmt->execute();
    $stadiumCheckResult = $stadiumCheckStmt->get_result()->fetch_assoc();
    $stadiumCheckStmt->close();

    if ($stadiumCheckResult['num_stadiums'] > 0 && $user_role === 'user') {
        $updateError = "لا يمكن تغيير دور المستخدم بسبب أن المستخدم يملك ملعب أو أكثر";
        echo "<script>alert('$updateError'); history.go(-1);</script>";
        exit;
    } elseif ($stadiumCheckResult['num_stadiums'] <= 0 && $user_role === 'owner') {
        $updateError = "لا يمكن تغيير دور المستخدم بسبب أن المستخدم لا يملك ملعب";
        echo "<script>alert('$updateError'); history.go(-1);</script>";
        exit;
    }

    if ($is_admin) {
        if ($is_password_changed) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            if ($new_password !== $confirm_password) {
                $updateError = "يجب تطابق كلمة المرور";
                echo "<script>alert('$updateError'); history.go(-1);</script>";
                exit;
            }

            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password=?, username=?, users_fname=?, users_lname=?, user_mobile=?, user_email=?, user_region=?, user_city=?, role=? WHERE user_id=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sssssssssi", $hashed_new_password, $user_username, $user_fname, $user_lname, $user_mobile, $user_email, $user_region, $user_city, $user_role, $user_id);
            $updateStmt->execute();
        } else {
            $updateSql = "UPDATE users SET username=?, users_fname=?, users_lname=?, user_mobile=?, user_email=?, user_region=?, user_city=?, role=? WHERE user_id=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssssssssi", $user_username, $user_fname, $user_lname, $user_mobile, $user_email, $user_region, $user_city, $user_role, $user_id);
            $updateStmt->execute();
        }
    } else {
        $current_password = $_POST['current_password'];
        if (!password_verify($current_password, $userData['password'])) {
            $updateError = "كلمة المرور الحالية غير صحيحة";
            echo "<script>alert('$updateError'); history.go(-1);</script>";
            exit;
        }

        if ($is_password_changed) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            if ($new_password !== $confirm_password) {
                $updateError = "يجب تطابق كلمة المرور";
                echo "<script>alert('$updateError'); history.go(-1);</script>";
                exit;
            }

            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password=?, username=?, users_fname=?, users_lname=?, user_mobile=?, user_email=?, user_region=?, user_city=? WHERE user_id=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssssssssi", $hashed_new_password, $user_username, $user_fname, $user_lname, $user_mobile, $user_email, $user_region, $user_city, $user_id);
            $updateStmt->execute();
        } else {
            $updateSql = "UPDATE users SET username=?, users_fname=?, users_lname=?, user_mobile=?, user_email=?, user_region=?, user_city=? WHERE user_id=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sssssssi", $user_username, $user_fname, $user_lname, $user_mobile, $user_email, $user_region, $user_city, $user_id);
            $updateStmt->execute();
        }
    }

    if ($updateStmt->error) {
        $updateError = "Error updating user: " . $updateStmt->error;
        echo "<script>alert('$updateError');</script>";
    } else {
        $updateError = "تم التعديل بنجاح";
        echo "<script>alert('$updateError');</script>";
    }

    if (!$is_admin && $is_password_changed) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $updateStmt->close();
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>تحديث المستخدم
        <?php echo htmlspecialchars($user_username); ?>!
    </title>

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
        <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="btn btn-secondary">تراجع</a>
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <?php if ($updateError) {
                    echo "<div class='alert alert-danger' id='updateError' role='alert'>$updateError</div>";
                } ?>

                <i class="fa fa-edit" aria-hidden="true"></i>
                <h2 class="title">تحديث بيانات المستخدم
                    <?php echo htmlspecialchars($user_username); ?>!
                </h2>
                <hr class="title">

                <form action="update_user.php?user_id=<?php echo $user_id; ?>" method="post">
                    <div class="mb-3">
                        <label for="users_fname" class="form-label">الاسم الأول:</label>
                        <input title="الاسم الأول" placeholder="الاسم الأول" type="text" class="form-control"
                            id="users_fname" name="users_fname" value="<?php echo htmlspecialchars($user_fname); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="users_lname" class="form-label">الاسم الأخير:</label>
                        <input title="الاسم الأخير" placeholder="الاسم الأخير" type="text" class="form-control"
                            id="users_lname" name="users_lname" value="<?php echo htmlspecialchars($user_lname); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input title="اسم المستخدم يجب أن يحتوي على حروف إنجليزية وأرقام فقط" placeholder="اسم المستخدم"
                            type="text" class="form-control" id="username" name="username" pattern="[A-Za-z0-9]+"
                            oninput="validateEnglishOnly(this)" required
                            value="<?php echo htmlspecialchars($user_username); ?>">
                        <span id="usernameAvailability"></span>
                    </div>

                    <div class="mb-3">
                        <label for="user_mobile" class="form-label">رقم الجوال:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input dir="rtl" placeholder="رقم الجوال" type="tel" class="form-control" id="user_mobile"
                            name="user_mobile" pattern="\d{10}" title="رقم الجوال يجب أن يكون 10 أرقام"
                            oninput="validatePhoneNumber(this)" required
                            value="<?php echo htmlspecialchars($user_mobile); ?>">
                        <span id="mobileAvailability"></span>
                    </div>

                    <div class="mb-3">
                        <label for="user_email" class="form-label">البريد الإلكتروني:</label>
                        <sup class="titleSub" title="Required">*</sup>
                        <input title="البريد الإلكتروني" placeholder="البريد الإلكتروني" type="email"
                            class="form-control" id="user_email" name="user_email" required
                            oninput="checkEmailAvailability()" value="<?php echo htmlspecialchars($user_email); ?>">
                        <span id="emailAvailability"></span>
                    </div>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">كلمة المرور الحالية:</label>
                        <input title="كلمة المرور الحالية" placeholder="كلمة المرور الحالية" type="password"
                            class="form-control" id="current_password" name="current_password" <?php echo (!$is_admin) ? 'required' : ''; ?>>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">كلمة المرور الجديدة:</label>
                        <input title="كلمة المرور الجديدة" placeholder="كلمة المرور الجديدة" type="password"
                            class="form-control" id="new_password" name="new_password">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">إعادة كتابة كلمة المرور الجديدة:</label>
                        <input title="إعادة كتابة كلمة المرور الجديدة" placeholder="إعادة كتابة كلمة المرور الجديدة"
                            type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>


                    <div class="mb-3">
                        <label for="user_region" class="form-label">المنطقة:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="user_region" name="user_region"
                            onchange="loadCities(this.value)" required>
                            <option value="" disabled selected hidden>اختر المنطقة...</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo htmlspecialchars($region); ?>">
                                    <?php echo htmlspecialchars($region); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="user_city" class="form-label">المدينة:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <select class="form-select" id="user_city" name="user_city" required>
                            <option value="" disabled selected hidden>اختر المدينة...</option>
                        </select>
                    </div>

                    <?php if ($is_admin): ?>
                        <div class="mb-3">
                            <label for="role" class="form-label">الدور:</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value='' disabled selected hidden>اختر دور</option>
                                <?php
                                $sql = "SELECT DISTINCT role FROM users";
                                $result = $conn->query($sql);
                                while ($roleRow = $result->fetch_assoc()) {
                                    $selected = ($roleRow['role'] == $user_role) ? 'selected' : '';
                                    echo "<option value='{$roleRow['role']}' {$selected}>{$roleRow['role']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-success">تحديث</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mb-5"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var region = "<?php echo $user_region; ?>";
            var city = "<?php echo $user_city; ?>";
            if (region) {
                loadCities(region, city);
                selectRegion(region);
            }
        });

        function loadCities(regionName, selectedCity) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById('user_city').innerHTML = this.responseText;
                    if (selectedCity) {
                        var citySelect = document.getElementById('user_city');
                        for (var i = 0; i < citySelect.options.length; i++) {
                            if (citySelect.options[i].value == selectedCity) {
                                citySelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
            };
            xhr.open("GET", "get_cities.php?region_name=" + encodeURIComponent(regionName), true);
            xhr.send();
        }

        function selectRegion(selectedRegion) {
            var regionSelect = document.getElementById('user_region');
            for (var i = 0; i < regionSelect.options.length; i++) {
                if (regionSelect.options[i].value == selectedRegion) {
                    regionSelect.selectedIndex = i;
                    break;
                }
            }
        }
    </script>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>