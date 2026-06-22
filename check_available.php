<?php
include 'dbc.php';

if (isset($_GET['username'])) {
    checkAvailability('username', $_GET['username']);
}

if (isset($_GET['mobile'])) {
    checkAvailability('user_mobile', $_GET['mobile']);
}

if (isset($_GET['email'])) {
    checkAvailability('user_email', $_GET['email']);
}

function checkAvailability($field, $value)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE $field = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("فشل في تحضير الاستعلام: " . $conn->error);
    }

    $stmt->bind_param("s", $value);
    $stmt->execute();

    if (!$stmt->execute()) {
        die("فشل في تنفيذ الاستعلام: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $stmt->close();
    echo $result->num_rows;
}

$conn->close();
?>