<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>Stadium Booking</title>

    <style>
        .icon {
            padding: 5px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- نافذة تسجيل الدخول -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">تسجيل الدخول</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="login.php" method="post">
                        <div class="mb-3 mt-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa-solid fa-user icon"></i></span>
                                </div>
                                <input title="اسم المستخدم" placeholder="اسم المستخدم" type="text" class="form-control"
                                    id="username" name="username" required>
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
                    <div class="text-center">
                        <p>إذا كنت مستخدماً جديداً، <a href="register.php">قم بالتسجيل هنا</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>