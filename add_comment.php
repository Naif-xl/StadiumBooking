<?php
include 'dbc.php';

$response = ['success' => false];

if (isset($_SESSION['user_id'], $_POST['stadium_id'], $_POST['comment'])) {
    $stadium_id = $_POST['stadium_id'];
    $user_id = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $sql = "INSERT INTO comments (user_id, stadium_id, comment_text) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $user_id, $stadium_id, $comment);
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'username' => htmlspecialchars($_SESSION['username']),
                    'comment_date' => date("Y-m-d H:i:s"),
                    'comment_text' => $comment
                ];
            }
            $stmt->close();
        }
    }
}

$conn->close();
echo json_encode($response);
?>