<?php
include 'dbc.php';
include 'loginModal.php';
include 'searchModal.php';

$pendingStadiumsCount = 0;
$pendingOwnerBookingsCount = 0;
$totalNotifications = 0;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

    $stmtOwnerBookings = $conn->prepare("SELECT COUNT(*) AS pendingOwnerBookings FROM bookings INNER JOIN stadium ON bookings.stadium_id = stadium.stadium_id WHERE stadium.user_id = ? AND bookings.status = 'غير مؤكد'");
    $stmtOwnerBookings->bind_param("i", $userId);
    $stmtOwnerBookings->execute();
    $resultOwnerBookings = $stmtOwnerBookings->get_result();
    $rowOwnerBookings = $resultOwnerBookings->fetch_assoc();
    $pendingOwnerBookingsCount = $rowOwnerBookings['pendingOwnerBookings'];

    if ($userRole === 'admin') {
        $stmtStadiums = $conn->prepare("SELECT COUNT(*) AS pendingStadiums FROM stadium WHERE stadium_status = 'قيد الانتظار'");
        $stmtStadiums->execute();
        $resultStadiums = $stmtStadiums->get_result();
        $rowStadiums = $resultStadiums->fetch_assoc();
        $pendingStadiumsCount = $rowStadiums['pendingStadiums'];
    }

    $totalNotifications = $pendingOwnerBookingsCount + $pendingStadiumsCount;
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300&family=Cairo+Play:wght@800&family=Harmattan&family=Lemonada:wght@300&family=Readex+Pro:wght@300&display=swap"
        rel="stylesheet">
    <script src="scripts.js"></script>
    <link rel="stylesheet" href="card.css">

    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            margin-right: auto;
        }

        .logo-img {
            width: 40px;
            height: 40px;
        }

        .loading-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>
</head>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="logo-container">
                <a href="index.php">
                    <img loading="lazy" src="uploads/logo.jpg" alt="شعار الموقع" class="rounded-circle logo-img">
                </a>
            </div>
            <a class="navbar-brand" href="index.php">Stadium Booking</a>&nbsp;&nbsp;
            <button title="بحث" class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="openSearchModal()">
                <i class="fas fa-search"></i>
            </button>&nbsp;
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-outline-info my-2 my-sm-0">
                    <a title="سلة الحجوزات" style="color: #ffffff;" href="cart.php" class="nav-link">
                        <i class="fa fa-shopping-cart"></i>
                    </a>
                </button>
            <?php endif; ?>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a rel="preload" class="nav-link" href="#" data-bs-toggle="modal"
                                data-bs-target="#loginModal">تسجيل
                                الدخول</a>
                        </li>
                        <li class="nav-item"><a rel="preload" class="nav-link" href="register.php">تسجيل جديد</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a rel="preload" class="nav-link" href="registerstadium.php">تسجيل ملعبك</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                مرحبًا يا
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu text-end" aria-labelledby="navbarDropdownMenuLink">
                                <li><a class="dropdown-item" title="بياناتي" href="user.php">الملف الشخصي</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a rel="preload" class="nav-link" href="control_panel.php">
                                لوحة التحكم
                                <?php if ($totalNotifications > 0): ?>
                                    <span class="badge bg-danger">
                                        <?php echo $totalNotifications; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item"><a rel="preload" class="nav-link" href="logout.php">تسجيل الخروج</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="loading-image">
    <img src="uploads/loading.png" alt="صورة التحميل" loading="lazy">
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector('.loading-image').style.display = 'flex';

        setTimeout(function () {
            document.querySelector('.loading-image').style.display = 'none';
        }, 900);
    });
</script>