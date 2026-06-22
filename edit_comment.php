<?php
include 'dbc.php';

$response = ['success' => false];

if (
    isset($_SESSION['user_id'], $_POST['comment_id'], $_POST['comment_text'], $_SESSION['role'])
    && $_SESSION['role'] === 'admin'
) {
    $comment_id = filter_var($_POST['comment_id'], FILTER_VALIDATE_INT);
    $comment_text = trim($_POST['comment_text']);

    if ($comment_id === false || $comment_id === null || empty($comment_text)) {
        exit(json_encode($response));
    }

    $sql = "UPDATE comments SET comment_text = ? WHERE comment_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $comment_text, $comment_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>