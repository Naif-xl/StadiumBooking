<?php
include 'dbc.php';

$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $user_password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $remember_me = isset($_POST['remember_me']) ? $_POST['remember_me'] : false;

    if (empty($user_username) || empty($user_password)) {
        $loginError = "الرجاء ملء كل من اسم المستخدم وكلمة المرور";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['is_blocked'] == 1) {
                $loginError = "تم حظر حسابك";
            } else {
                if (password_verify($user_password, $row['password'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];

                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        setcookie("remember_me_cookie", $token, time() + (30 * 24 * 60 * 60), "/");
                        $updateTokenSql = "UPDATE users SET remember_me_token = ? WHERE user_id = ?";
                        $updateTokenStmt = $conn->prepare($updateTokenSql);
                        $updateTokenStmt->bind_param("si", $token, $row['user_id']);
                        $updateTokenStmt->execute();
                        $updateTokenStmt->close();
                    }

                    header('Location: index.php');
                    exit;
                } else {
                    $loginError = "اسم المستخدم أو كلمة المرور غير صحيحة";
                }
            }
        } else {
            $loginError = "اسم المستخدم أو كلمة المرور غير صحيحة";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html dir="rtl">

<head>
    <title>تسجيل الدخول</title>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand {
            font-size: 1.5rem;
        }

        .navbar-toggler {
            border-color: #ffffff;
        }

        .navbar-nav {
            font-size: 1.2rem;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }

        .card-header {
            background-color: #343a40;
            color: #ffffff;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body {
            padding: 20px;
        }

        .form-label {
            font-size: 1.2rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-size: 1.2rem;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .alert-warning {
            background-color: #ffc107;
            color: #212529;
            border-color: #d39e00;
        }

        .icon {
            padding: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php
                if ($loginError) {
                    echo "<div class='alert alert-warning' role='alert'>" . htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') . "</div>";
                }
                ?>
                <div class="card">
                    <div class="card-header text-center">
                        <h5>تسجيل الدخول</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3 mt-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa-solid fa-user icon"></i></span>
                                    </div>
                                    <input title="اسم المستخدم" placeholder="اسم المستخدم" type="text"
                                        class="form-control" id="username" name="username" required>
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa-solid fa-key icon"></i></span>
                                    </div>
                                    <input title="كلمة المرور" placeholder="كلمة المرور" type="password"
                                        class="form-control" id="password" name="password" required>
                                </div>
                                <div class="text-end">
                                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                    <label class="form-check-label text-end" for="remember_me">تذكرني</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <p>إذا كنت مستخدماً جديداً، <a href="register.php">قم بالتسجيل هنا</a>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>
    <?php $conn->close(); ?>
</body>

</html>