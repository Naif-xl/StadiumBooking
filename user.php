<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8');

$query_select = "SELECT * FROM users where user_id = $userId";
$result_select = mysqli_query($conn, $query_select);

$query_stadiums = "SELECT * FROM stadium WHERE user_id = $userId AND stadium_status = 'مقبول'";
$result_stadiums = mysqli_query($conn, $query_stadiums);
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>بياناتي</title>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="background-image"></div>

    <!-- بيانات المستخدم -->
    <?php if ($userId): ?>
        <div class="container mt-3">
            <h5>بياناتي</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr style="text-align: center;">
                            <th scope="col">الاسم الأول</th>
                            <th scope="col">الاسم الأخير</th>
                            <th scope="col">اسم المستخدم</th>
                            <th scope="col">رقم الجوال</th>
                            <th scope="col">البريد الإلكتروني</th>
                            <th scope="col">المنطقة</th>
                            <th scope="col">المدينة</th>
                            <th scope="col">التحكم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($result_select) && $result_select) {
                            while ($row = mysqli_fetch_assoc($result_select)) {
                                echo "<tr style='text-align: center;' id='row-{$row['user_id']}'>
                                <td>" . htmlspecialchars($row['users_fname'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['users_lname'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['user_mobile'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['user_email'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['user_region'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['user_city'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td><a href='update_user.php?user_id={$row['user_id']}' title='تحديث بياناتي'>
                                <i class='fa fa-edit' style='color:green' aria-hidden='true'></i></a></td>
                            </tr>";
                            }
                        } else {
                            echo '<tr><td colspan="4">لا توجد بيانات</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- ملاعب المستخدم -->
    <?php if ($result_stadiums && mysqli_num_rows($result_stadiums) > 0): ?>
        <div class="container mt-3">
            <h5>ملاعبي الخاصة</h5>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th scope="col">م</th>
                        <th scope="col">اسم الملعب</th>
                        <th scope="col">نوع الملعب</th>
                        <th scope="col">تفاصيل الملعب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($result_stadiums) && $result_stadiums) {
                        $counter = 1;
                        while ($stadium = mysqli_fetch_assoc($result_stadiums)) {
                            echo "<tr>
                            <td>" . htmlspecialchars($counter, ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($stadium['stadium_name'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($stadium['stadium_type'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>
                            <a href='stadium.php?id=" . htmlspecialchars($stadium['stadium_id'], ENT_QUOTES, 'UTF-8') . "' class='btn btn-success btn-sm'>عرض التفاصيل</a>
                            </td>
                        </tr>";
                            $counter++;
                        }
                    } else {
                        echo '<tr><td colspan="3">لا توجد بيانات</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>