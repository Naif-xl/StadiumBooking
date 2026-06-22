<?php
include 'dbc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to_email = $_POST['email'];
    $reply_message = $_POST['reply_message'];
    $subject = "رد على رسالتك";

    $sql_user = "SELECT full_name, email FROM contact_messages WHERE email = ? LIMIT 1";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("s", $to_email);
    $stmt_user->execute();
    $stmt_user->store_result();

    if ($stmt_user->num_rows > 0) {
        $stmt_user->bind_result($full_name, $user_email);
        $stmt_user->fetch();
        $stmt_user->close();

        $headers = "From: b_3332@hotmail.com\r\n";
        $headers .= "Reply-To: $user_email\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($to_email, $subject, $reply_message, $headers)) {
            echo "تم إرسال الرد بنجاح.";
        } else {
            echo "حدث خطأ أثناء إرسال الرد. يرجى التحقق من السجلات أو الاتصال بالدعم الفني للمزيد من المساعدة.";
            $lastError = error_get_last();
            if ($lastError) {
                echo "<br>Error details: " . print_r($lastError, true);
            }
        }
    } else {
        echo "لا يمكن العثور على بيانات المستخدم.";
    }
}
?>