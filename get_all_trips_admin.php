<?php
header("Content-Type: application/json");
include 'config.php';

// Return status normalized to lowercase and order based on normalized status.
$sql = "SELECT
            t.id AS trip_id,
            t.place_name,
            t.place_location,
            t.start_date,
            t.end_date,
            t.num_people,
            t.num_days,
            t.total_budget,
            LOWER(t.status) AS status,
            u.fullname AS user_name
        FROM trips t
        JOIN users u ON t.user_id = u.id
        ORDER BY
            CASE
                WHEN LOWER(t.status) = 'future' THEN 1
                WHEN LOWER(t.status) = 'active' THEN 2
                WHEN LOWER(t.status) = 'finished' THEN 3
                WHEN LOWER(t.status) = 'completed' THEN 3
                WHEN LOWER(t.status) = 'cancelled' THEN 4
                ELSE 5
            END, t.start_date DESC";

$result = $conn->query($sql);

if ($result) {
    $trips = array();
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    echo json_encode(array("error" => false, "trips" => $trips));
} else {
    echo json_encode(array("error" => true, "message" => "Could not fetch trips."));
}

$conn->close();
?>
