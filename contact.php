<?php
include 'dbc.php';

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $user_data['username'] = htmlspecialchars($user_data['username']);
        $user_data['user_email'] = htmlspecialchars($user_data['user_email']);
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO contact_messages (full_name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $full_name, $email, $subject, $message);

    if ($stmt->execute()) {
        $success_message = "تم إرسال رسالتك بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء إرسال الرسالة: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>اتصل بنا</title>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="background-image"></div>

    <div class="container mt-5">
        <h1 class="mb-4 text-center">اتصل بنا</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php if ($user_data): ?>
                <div class="form-group">
                    <label for="full_name">الاسم الكامل:</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="username">اسم المستخدم:</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($user_data['user_email']); ?>" required>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="full_name">الاسم الكامل:</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="subject">الموضوع:</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="message">الرسالة:</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">إرسال</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>

    <?php $conn->close(); ?>
</body>

</html>