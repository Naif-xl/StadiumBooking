<?php
include 'dbc.php';

$stadiumsPerPage = 9;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$startFrom = ($page - 1) * $stadiumsPerPage;

$stadium_type = isset($_GET['stadium_type']) ? $_GET['stadium_type'] : '';

$sql = "SELECT * FROM stadium WHERE stadium_status = 'مقبول'";
$params = [];

if (!empty($stadium_type) && $stadium_type !== 'الكل') {
    $sql .= " AND stadium_type = ?";
    $params[] = $stadium_type;
}

$sql .= " LIMIT ?, ?";
$params[] = $startFrom;
$params[] = $stadiumsPerPage;

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

$totalQuery = $conn->query("SELECT COUNT(stadium_id) AS total FROM stadium WHERE stadium_status = 'مقبول'");
$totalRow = $totalQuery->fetch_assoc();
$totalStadiums = $totalRow['total'];
$totalPages = ceil($totalStadiums / $stadiumsPerPage);
?>


<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>Stadium Booking</title>

    <style>
        .form-select {
            width: 200px;
        }

        @media (max-width: 576px) {
            .form-select {
                width: 100%;
            }
        }

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

        select,
        optgroup,
        option {
            direction: rtl !important;
        }
    </style>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="background-image"></div>

    <div class="container-fluid p-2 text-center">
        <div class="bg-success text-white p-4 rounded-3 shadow-sm bgb">
            <h1 class="display-3">احجز ملعبك الآن!</h1>
            <p class="lead mb-0">انطلق إلى عالم الرياضة والمنافسة</p>
        </div>
        <div class="container-gaol text-center rounded-3 py-0">
            <p>هدفنا تسهيل العناء واختصار الوقت ابحث واحجز الملعب بسهولة</p>
        </div>
    </div>

    <!-- الملاعب -->
    <div class="container mt-2">
        <table>
            <tr>
                <td>فرز حسب</td>
                <td>
                    <select class="form-select form-select-sm" id="stadium_type" name="stadium_type"
                        onchange="filterStadiums(this.value)">
                        <optgroup label="نوع الملعب">
                            <option value="الكل">الكل</option>
                            <?php
                            $stadiumTypesQuery = $conn->query("SELECT DISTINCT stadium_type FROM stadium");
                            while ($stadiumTypeRow = $stadiumTypesQuery->fetch_assoc()) {
                                $type = $stadiumTypeRow['stadium_type'];
                                echo "<option value=\"$type\"";
                                if ($type == $stadium_type) {
                                    echo " selected";
                                }
                                echo ">$type</option>";
                            }
                            ?>
                        </optgroup>
                    </select>
                </td>
            </tr>
        </table>
        <h2 class="mt-3 mb-4 text-center text-success">الملاعب</h2>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card card-custom" style="height: 100%;">
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
                                                <img loading="lazy" src="<?php echo htmlspecialchars($image); ?>"
                                                    class="d-block carousel-img" alt="صورة الملعب">
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
                                <img loading="lazy" class="card-img-top stadium-thumbnail mx-auto"
                                    src="uploads/<?php echo htmlspecialchars($stadium_images[0]); ?>" alt="صورة الملعب">
                            <?php endif; ?>
                            <div class="card-body">
                                <p class="card-text">
                                <h5 class="card-title text-center">
                                    <strong>
                                        <?php echo htmlspecialchars($row['stadium_name']); ?>
                                    </strong>
                                </h5>
                                </p>
                                <strong></strong>
                                <p class="card-text">
                                    <?php echo '<strong>المنطقة: </strong>' . htmlspecialchars($row['stadium_region']); ?>
                                </p>
                                <p class="card-text">
                                    <?php echo '<strong>المدينة: </strong>' . htmlspecialchars($row['stadium_city']); ?>
                                </p>
                                <p class="card-text">
                                    <?php echo '<strong>العنوان: </strong>' . htmlspecialchars($row['stadium_address']); ?>
                                </p>
                                <p class="card-text">
                                    <?php
                                    if ($row['stadium_type'] === 'قدم') {
                                        echo '<strong>نوع الملعب: </strong>' . htmlspecialchars($row['stadium_type']) .
                                            '<br><p class="card-text"> <strong>حجم الملعب: </strong>' . htmlspecialchars($row['stadium_size']) . '</p>';
                                    } else {
                                        echo '<strong>نوع الملعب: </strong>' . htmlspecialchars($row['stadium_type']);
                                    }
                                    ?>
                                </p>
                                <p class="card-text">
                                    <?php
                                    if ($row['disable_booking'] == 1) {
                                        echo "<strong class='badge bg-warning'>الحجز مغلق مؤقتاً</strong>";
                                    } else {
                                        echo "<strong class='badge bg-success'>الملعب شاغر في الوقت الحالي</strong>";
                                    }
                                    ?>
                                </p>
                                <a href="stadium.php?id=<?php echo $row['stadium_id']; ?>" class="btn btn-primary">عرض
                                    التفاصيل</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>لم يتم العثور على ملاعب</p>
            <?php endif; ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i == $page)
                            echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <script>
        function filterStadiums(stadiumType) {
            window.location.href = 'index.php?stadium_type=' + encodeURIComponent(stadiumType);
        }

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