<?php
include 'dbc.php';

$response = ['success' => false];

if (isset($_SESSION['user_id'], $_POST['stadium_id'], $_POST['rating'])) {
    $stadium_id = $_POST['stadium_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];

    if ($rating >= 1 && $rating <= 5) {
        $sql = "INSERT INTO ratings (user_id, stadium_id, rating) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iii", $user_id, $stadium_id, $rating);
            if ($stmt->execute()) {
                sleep(1);
                $response['success'] = true;

                $sql = "SELECT AVG(rating) as average_rating, COUNT(rating) as total_ratings FROM ratings WHERE stadium_id = ?";
                if ($avgStmt = $conn->prepare($sql)) {
                    $avgStmt->bind_param("i", $stadium_id);
                    if ($avgStmt->execute()) {
                        $avgResult = $avgStmt->get_result();
                        if ($avgRow = $avgResult->fetch_assoc()) {
                            $response['averageRating'] = htmlspecialchars(round($avgRow['average_rating'], 1));
                            $response['totalRatings'] = htmlspecialchars($avgRow['total_ratings']);
                        }
                    }
                    $avgStmt->close();
                }
            }
            $stmt->close();
        }
    }
}

$conn->close();
echo json_encode($response);
?>