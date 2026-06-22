<?php
include 'dbc.php';

$response = ['success' => false];

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin' && isset($_POST['comment_id'])) {
    $comment_id = filter_var($_POST['comment_id'], FILTER_VALIDATE_INT);

    if ($comment_id !== false && $comment_id > 0) {
        $sql = "DELETE FROM comments WHERE comment_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $comment_id);
            if ($stmt->execute()) {
                $response['success'] = true;
            }
            $stmt->close();
        }
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>