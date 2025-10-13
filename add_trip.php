<?php
include 'config.php';

header('Content-Type: application/json');
$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response = ['status' => false, 'message' => 'Invalid request method.'];
    echo json_encode($response);
    exit;
}

// Start a transaction
$conn->begin_transaction();

try {
    // 1. Insert into the main 'trips' table
    $sql_trip = "INSERT INTO trips (user_id, place_id, place_name, place_location, start_date, end_date, num_people, num_days, transport_cost, food_cost, hotel_cost, other_cost, total_budget, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')";
    $stmt_trip = $conn->prepare($sql_trip);
    $stmt_trip->bind_param(
        "iisssiiddiidd",
        $_POST['user_id'],
        $_POST['place_id'],
        $_POST['place_name'],
        $_POST['place_location'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['num_people'],
        $_POST['num_days'],
        $_POST['transport_cost'],
        $_POST['food_cost'],
        $_POST['hotel_cost'],
        $_POST['other_cost'],
        $_POST['total_budget']
    );
    $stmt_trip->execute();
    $tripId = $stmt_trip->insert_id;
    $stmt_trip->close();

    // 2. Insert into the 'itinerary_spots' table
    $itineraryJson = $_POST['itinerary_data'] ?? '[]';
    $itineraryDays = json_decode($itineraryJson, true);

    if (is_array($itineraryDays) && !empty($itineraryDays)) {
        $sql_itinerary = "INSERT INTO itinerary_spots (trip_id, day_number, spot_name) VALUES (?, ?, ?)";
        $stmt_itinerary = $conn->prepare($sql_itinerary);
        
        foreach ($itineraryDays as $dayIndex => $day) {
            $dayNumber = $dayIndex + 1;
            if (isset($day['spots']) && is_array($day['spots'])) {
                foreach ($day['spots'] as $spot) {
                    $spotName = $spot['name'];
                    $stmt_itinerary->bind_param("iis", $tripId, $dayNumber, $spotName);
                    $stmt_itinerary->execute();
                }
}
        }
        $stmt_itinerary->close();
    }

    // If everything is successful, commit the transaction
    $conn->commit();
    $response = ['status' => true, 'message' => 'Trip and itinerary saved successfully.'];
    http_response_code(201);

} catch (Exception $e) {
    // If anything fails, roll back the transaction
    $conn->rollback();
    http_response_code(500);
    $response = ['status' => false, 'message' => 'Database transaction failed: ' . $e->getMessage()];
}

echo json_encode($response);
$conn->close();
?>