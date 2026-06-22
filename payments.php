<?php
include 'dbc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
    $card_number = htmlspecialchars($_POST['card_number'], ENT_QUOTES, 'UTF-8');
    $cardholder_name = htmlspecialchars($_POST['cardholder_name'], ENT_QUOTES, 'UTF-8');
    $expiry_date = $_POST['expiry_date'] . '-01';
    $user_id = $_SESSION['user_id'];
    $stadium_id = $_POST['stadium_id'];
    $booking_id = $_POST['booking_id'];

    $sql_insert_payment = "INSERT INTO payments (user_id, amount, card_number, cardholder_name, expiry_date, stadium_id, booking_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'عملية الدفع ناجحة')";
    if ($stmt_insert_payment = $conn->prepare($sql_insert_payment)) {
        $stmt_insert_payment->bind_param("idsssii", $user_id, $amount, $card_number, $cardholder_name, $expiry_date, $stadium_id, $booking_id);
        if ($stmt_insert_payment->execute()) {
            echo "<script>alert('تمت عملية الدفع بنجاح.'); window.location.href = 'cart.php';</script>";
        } else {
            echo "<script>alert('خطأ في إضافة الدفع: " . $stmt_insert_payment->error . "'); window.location.href = 'cart.php';</script>";
        }
        $stmt_insert_payment->close();
    }
} else {
    $booking_id = $_GET['booking_id'];
    if (!is_numeric($booking_id) || $booking_id <= 0) {
        echo "<script>alert('طلب غير صحيح.'); window.location.href = 'index.php';</script>";
        exit;
    }
}

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    $sql_get_booking = "SELECT b.*, s.stadium_booking_price
                        FROM bookings b
                        INNER JOIN stadium s ON b.stadium_id = s.stadium_id
                        WHERE b.booking_id = ?";
    if ($stmt_get_booking = $conn->prepare($sql_get_booking)) {
        $stmt_get_booking->bind_param("i", $booking_id);
        $stmt_get_booking->execute();
        $result_get_booking = $stmt_get_booking->get_result();

        if ($result_get_booking->num_rows > 0) {
            $row_booking = $result_get_booking->fetch_assoc();

            $user_id = $row_booking['user_id'];
            $stadium_id = $row_booking['stadium_id'];
            $booking_date = $row_booking['booking_date'];
            $booking_time_start = $row_booking['booking_time_start'];
            $booking_time_end = $row_booking['booking_time_end'];
            $stadium_booking_price = $row_booking['stadium_booking_price'];
        }
        $stmt_get_booking->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج عملية الدفع</title>
    <script src="scripts.js"></script>
    <style>
        body {
            font-family: "Calibri", sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button,
        a {
            background-color: #4caf50;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            text-decoration: none;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-group">
            <a href="stadium.php?id=<?php echo $stadium_id; ?>" class="btn btn-secondary">تراجع</a>
        </div>
        <h2>نموذج عملية الدفع</h2>
        <form action="payments.php" method="post">
            <div class="form-group">
                <label for="amount">المبلغ:</label>
                <input type="number" id="amount" name="amount"
                    value="<?php echo htmlspecialchars($stadium_booking_price); ?>" readonly required>
            </div>

            <div class="form-group">
                <label for="card_number">رقم البطاقة:</label>
                <input type="text" id="card_number" name="card_number" maxlength="16" pattern="[0-9]{16}"
                    title="رقم البطاقة يجب أن يكون 16 رقمًا" placeholder="رقم البطاقة يجب أن يكون 16 رقمًا"
                    oninput="validateCardNumber(this)" required>
            </div>

            <div class="form-group">
                <label for="cardholder_name">اسم صاحب البطاقة:</label>
                <input type="text" id="cardholder_name" name="cardholder_name" oninput="validateEnglishOnly(this)"
                    placeholder="اسم صاحب البطاقة" required>
            </div>

            <div class="form-group">
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label for="expiry_date">تاريخ انتهاء البطاقة:</label>
                        <input type="month" id="expiry_date" name="expiry_date" placeholder="تاريخ انتهاء البطاقة"
                            required>
                    </div>
                    <div style="flex: 1;">
                        <label for="cvv">CVV:</label>
                        <input type="text" id="cvv" name="cvv" maxlength="3" pattern="[0-9]{3}" placeholder="CVV"
                            required>
                    </div>
                </div>
            </div>

            <input type="hidden" name="stadium_id" value="<?php echo $stadium_id; ?>">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">

            <div class="form-group">
                <button type="submit">ادفع</button>
            </div>
        </form>
    </div>

    <script>
        function validateCardNumber(input) {
            input.value = input.value.replace(/[^0-9]/g, "");
            if (input.value.length > 16) {
                input.value = input.value.substring(0, 16);
            }
        }
    </script>
</body>

</html>