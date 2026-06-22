<?php
include 'dbc.php';

if (isset($_GET['region_name']) && !empty($_GET['region_name'])) {
    $region_name = $_GET['region_name'];

    $sql = "SELECT city_name FROM city INNER JOIN region ON city.region_id = region.region_id WHERE region.region_name = ? ORDER BY city_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $region_name);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['city_name'] . "'>" . $row['city_name'] . "</option>";
    }

    $stmt->close();
}

$conn->close();
?>