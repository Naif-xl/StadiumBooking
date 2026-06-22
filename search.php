<?php
include 'dbc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchTerm = $_POST['searchTerm'] ?? '';

    $sql = "SELECT * FROM stadium WHERE stadium_name LIKE ? OR stadium_type LIKE ? OR stadium_region LIKE ? OR stadium_city LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stadiumId = $row['stadium_id'];
            echo "<div class='card mb-3 stadium-card' data-stadium-id='{$stadiumId}'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'><strong> اسم الملعب: </strong>" . htmlspecialchars($row['stadium_name'], ENT_QUOTES, 'UTF-8') . "</strong></h5>";
            echo "<p class='card-text'><strong>المنطقة: </strong>" . htmlspecialchars($row['stadium_region'], ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p class='card-text'><strong>المدينة: </strong>" . htmlspecialchars($row['stadium_city'], ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p class='card-text'><strong>العنوان: </strong>" . htmlspecialchars($row['stadium_address'], ENT_QUOTES, 'UTF-8') . "</p>";
            if ($row['stadium_type'] === 'قدم') {
                echo '<strong>نوع الملعب: </strong>' . htmlspecialchars($row['stadium_type']) .
                    '<br><p class="card-text"><strong>حجم الملعب: </strong>' . htmlspecialchars($row['stadium_size']) . '</p>';
            } else {
                echo '<p class="card-text"><strong>نوع الملعب: </strong>' . htmlspecialchars($row['stadium_type']) . '</p>';
            }
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "لم يتم العثور على ملاعب";
    }
    $conn->close();
}
?>