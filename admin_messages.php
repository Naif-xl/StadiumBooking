<?php
include 'dbc.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "الوصول مقتصر على المدراء فقط.";
    header('Location: login.php');
    exit;
}

$sql = "SELECT * FROM contact_messages";
$result = $conn->query($sql);

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <title>رسائل اتصل بنا</title>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4 text-center">رسائل اتصل بنا</h1>
        <div class="row">
            <?php foreach ($messages as $message): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <?php echo htmlspecialchars($message['full_name']); ?>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($message['subject']); ?>
                            </h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars($message['message']); ?>
                            </p>
                        </div>
                        <div class="card-body">
                            <!-- نموذج الرد -->
                            <form action="send_reply.php" method="post">
                                <input type="hidden" name="email"
                                    value="<?php echo htmlspecialchars($message['email']); ?>">
                                <textarea name="reply_message" class="form-control" rows="3"></textarea>
                                <button type="submit" class="btn btn-primary mt-2">أرسل الرد</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>