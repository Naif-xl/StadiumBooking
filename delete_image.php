<?php
include 'dbc.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ids = $data['ids'] ?? [];

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT file_name FROM gallery_images WHERE gallery_id IN ($placeholders)";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();
            $files = [];

            while ($row = $result->fetch_assoc()) {
                $files[] = $row['file_name'];
            }

            $stmt->close();

            $query = "DELETE FROM gallery_images WHERE gallery_id IN ($placeholders)";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

                if ($stmt->execute()) {
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                    $response = ['success' => true];
                } else {
                    $response = ['success' => false, 'error' => $stmt->error];
                }
                $stmt->close();
            } else {
                $response = ['success' => false, 'error' => $conn->error];
            }
        } else {
            $response = ['success' => false, 'error' => $conn->error];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>