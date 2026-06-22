<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stadium_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

$sqlRole = "SELECT role FROM users WHERE user_id = ?";
$stmtRole = $conn->prepare($sqlRole);
$stmtRole->bind_param("i", $user_id);
$stmtRole->execute();
$resultRole = $stmtRole->get_result();
$userRole = $resultRole->fetch_assoc()['role'];
$stmtRole->close();

$isAuthorized = false;
if ($userRole === 'admin' || ($userRole === 'owner' && checkIfOwner($conn, $user_id, $stadium_id))) {
    $isAuthorized = true;
}

function checkIfOwner($conn, $user_id, $stadium_id)
{
    $sqlOwner = "SELECT user_id FROM stadium WHERE stadium_id = ? AND user_id = ?";
    $stmtOwner = $conn->prepare($sqlOwner);
    $stmtOwner->bind_param("ii", $stadium_id, $user_id);
    $stmtOwner->execute();
    $stmtOwner->store_result();
    return $stmtOwner->num_rows > 0;
}

if (!$isAuthorized) {
    echo "غير مسموح لك بتعديل هذا الملعب.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $stadium_id > 0) {
    $stadium_name = $conn->real_escape_string($_POST['stadium_name']);
    $stadium_address = $conn->real_escape_string($_POST['stadium_address']);
    $stadium_details = $_POST['stadium_details'];
    $stadium_size = $conn->real_escape_string($_POST['stadium_size']);
    $stadium_location = $conn->real_escape_string($_POST['stadium_location']);
    $stadium_type = $conn->real_escape_string($_POST['stadium_type']);
    $stadium_time_start = $_POST['stadium_time_start'];
    $stadium_time_end = $_POST['stadium_time_end'];
    $stadium_region = $conn->real_escape_string($_POST['stadium_region']);
    $stadium_city = $conn->real_escape_string($_POST['stadium_city']);
    $stadium_booking_price = $conn->real_escape_string($_POST['stadium_booking_price']);

    $sql = "UPDATE stadium SET stadium_name = ?, stadium_address = ?, stadium_location = ?, stadium_type = ?, stadium_details = ?, stadium_time_start = ?, stadium_time_end = ?, stadium_region = ?, stadium_city = ?, stadium_size = ?, stadium_booking_price = ? WHERE stadium_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssdi", $stadium_name, $stadium_address, $stadium_location, $stadium_type, $stadium_details, $stadium_time_start, $stadium_time_end, $stadium_region, $stadium_city, $stadium_size, $stadium_booking_price, $stadium_id);

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

    if ($stmt->execute()) {
        header("Location: stadium.php?id=" . $stadium_id);
    } else {
        echo "خطأ: " . $stmt->error;
    }
    $stmt->close();
}

$regions = [];
$sql = "SELECT region_name FROM region ORDER BY region_name";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $regions[] = $row['region_name'];
}

if ($stadium_id > 0) {
    $sql = "SELECT * FROM stadium WHERE stadium_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stadium_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        extract($row);
    } else {
        echo "لم يتم العثور على الملعب";
        exit;
    }
    $stmt->close();

    $sqlImages = "SELECT gallery_id, file_name FROM gallery_images WHERE stadium_id = ?";
    $stmtImages = $conn->prepare($sqlImages);
    $stmtImages->bind_param("i", $stadium_id);
    $stmtImages->execute();
    $resultImages = $stmtImages->get_result();
    $images = [];
    while ($imageRow = $resultImages->fetch_assoc()) {
        $images[] = $imageRow;
    }
    $stmtImages->close();
}
?>


<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <title>
        <?php echo $stadium_name; ?>
    </title>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var region = "<?php echo $stadium_region; ?>";
            var city = "<?php echo $stadium_city; ?>";
            if (region) {
                loadCities(region, city);
                selectRegion(region);
            }
        });

        function loadCities(regionName, selectedCity) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById('stadium_city').innerHTML = this.responseText;
                    if (selectedCity) {
                        var citySelect = document.getElementById('stadium_city');
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
            var regionSelect = document.getElementById('stadium_region');
            for (var i = 0; i < regionSelect.options.length; i++) {
                if (regionSelect.options[i].value == selectedRegion) {
                    regionSelect.selectedIndex = i;
                    break;
                }
            }
        }

        function updateStadiumSizeVisibility() {
            var stadiumTypeSelect = document.getElementById('stadium_type');
            var stadiumSizeContainer = document.getElementById('stadiumSizeContainer');
            if (stadiumTypeSelect.value === 'قدم') {
                stadiumSizeContainer.style.display = '';
            } else {
                stadiumSizeContainer.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var stadiumTypeSelect = document.getElementById('stadium_type');
            stadiumTypeSelect.addEventListener('change', updateStadiumSizeVisibility);
            updateStadiumSizeVisibility();
        });

        document.addEventListener('DOMContentLoaded', function () {
            var stadiumTypeSelect = document.getElementById('stadium_type');
            var stadiumSizeContainer = document.getElementById('stadiumSizeContainer');

            stadiumTypeSelect.addEventListener('change', function () {
                if (this.value === 'قدم') {
                    stadiumSizeContainer.style.display = '';
                } else {
                    stadiumSizeContainer.style.display = 'none';
                }
            });
        });

        function deleteSelectedRecords() {
            var selectedIds = [];
            var checkboxes = document.querySelectorAll('.delete-checkbox');

            checkboxes.forEach(function (checkbox) {
                if (checkbox.checked) {
                    selectedIds.push(checkbox.value);
                }
            });

            if (selectedIds.length > 0) {
                if (confirm("هل أنت متأكد أنك تريد حذف الصور المحددة؟")) {
                    fetch('delete_image.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ ids: selectedIds }),
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('خطأ في حذف السجلات: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            } else {
                alert('الرجاء تحديد صورة واحدة على الأقل للحذف.');
            }
        }
    </script>

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
        <a href="stadium.php?id=<?php echo $stadium_id; ?>" class="btn btn-secondary">تراجع</a>
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <i class="fa fa-pencil-square-o"></i>
                <h2 class="title">تعديل
                    <?php echo htmlspecialchars($stadium_name); ?>
                </h2>
                <hr class="title">

                <form action="update_stadium.php?id=<?php echo $stadium_id; ?>" method="post"
                    enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>الصور الحالية:</label>
                        <div>
                            <?php foreach ($images as $image): ?>
                                <div id="image-<?php echo htmlspecialchars($image['gallery_id']); ?>">
                                    <img src="<?php echo htmlspecialchars($image['file_name']); ?>" alt="صورة الملعب"
                                        width="100" height="100">
                                    <input type="checkbox" class="delete-checkbox"
                                        value="<?php echo htmlspecialchars($image['gallery_id']); ?>">
                                    حذف
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-danger btn-sm delete-button" id="deletebtn" title="حذف"
                            onclick="deleteSelectedRecords()">حذف الصور المحددة</button>
                    </div>
                    <div class="mb-3">
                        <label for="stadiumName" class="form-label">اسم الملعب</label>
                        <input type="text" class="form-control" id="stadiumName" name="stadium_name"
                            value="<?php echo htmlspecialchars($stadium_name); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="stadium_region" class="form-label">المنطقة:</label>
                        <select class="form-select" id="stadium_region" name="stadium_region"
                            onchange="loadCities(this.value)">
                            <option value="" disabled selected hidden>اختر المنطقة...</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo htmlspecialchars($region); ?>">
                                    <?php echo htmlspecialchars($region); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_city" class="form-label">المدينة:</label>
                        <select class="form-select" id="stadium_city" name="stadium_city">
                            <option value="" disabled selected hidden>اختر المدينة...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_address" class="form-label">عنوان الملعب:</label>
                        <input type="text" class="form-control" id="stadium_address" name="stadium_address"
                            value="<?php echo htmlspecialchars($stadium_address); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_details" class="form-label">تفاصيل الملعب:</label>
                        <textarea class="form-control" id="stadium_details" name="stadium_details" style="height: auto;"
                            required><?php echo htmlspecialchars($stadium_details); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_location" class="form-label">رابط الموقع في خرائط قوقل:</label>
                        <input type="text" class="form-control" id="stadium_location" name="stadium_location"
                            value="<?php echo htmlspecialchars($stadium_location); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_type" class="form-label">نوع الملعب:</label>
                        <select class="form-select" id="stadium_type" name="stadium_type" required>
                            <option value="قدم" <?php echo ($stadium_type == 'قدم') ? 'selected' : ''; ?>>كرة قدم</option>
                            <option value="بادل" <?php echo ($stadium_type == 'بادل') ? 'selected' : ''; ?>>كرة بادل
                            </option>
                        </select>
                    </div>

                    <div class="mb-3" id="stadiumSizeContainer" style="display: none;">
                        <label for="stadium_size" class="form-label">حجم الملعب:</label>
                        <select class="form-select" id="stadium_size" name="stadium_size">
                            <option value="" disabled selected hidden>اختر حجم الملعب...</option>
                            <option value="6 * 6" <?php echo ($stadium_size == '6 * 6') ? 'selected' : ''; ?>>6 * 6
                            </option>
                            <option value="7 * 7" <?php echo ($stadium_size == '7 * 7') ? 'selected' : ''; ?>>7 * 7
                            </option>
                            <option value="8 * 8" <?php echo ($stadium_size == '8 * 8') ? 'selected' : ''; ?>>8 * 8
                            </option>
                            <option value="9 * 9" <?php echo ($stadium_size == '9 * 9') ? 'selected' : ''; ?>>9 * 9
                            </option>
                            <option value="10 * 10" <?php echo ($stadium_size == '10 * 10') ? 'selected' : ''; ?>>10 * 10
                            </option>
                            <option value="11 * 11" <?php echo ($stadium_size == '11 * 11') ? 'selected' : ''; ?>>11 * 11
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stadium_booking_price" class="form-label">سعر الحجز:</label>
                        <sup class="titleSub" title="حقل إلزامي">*</sup>
                        <input type="number" id="stadium_booking_price" name="stadium_booking_price"
                            value="<?php echo $stadium_booking_price; ?>" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stadium_time_start" class="form-label">بداية العمل:</label>
                            <input type="time" class="form-control" id="stadium_time_start" name="stadium_time_start"
                                value="<?php echo $stadium_time_start; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stadium_time_end" class="form-label">نهاية العمل:</label>
                            <input type="time" class="form-control" id="stadium_time_end" name="stadium_time_end"
                                value="<?php echo $stadium_time_end; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stadium_image" class="form-label">صور الملعب:</label>
                            <sup class="titleSub" title="حقل إلزامي">*</sup><br>
                            <input type="file" id="stadium_image" accept=".jpg, .png, .JPEG" name="stadium_images[]"
                                multiple>
                            <small class="form-text text-muted">رفع الصور بصيغة JPG, JPEG, أو PNG فقط.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">تحديث</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>