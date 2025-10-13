<?php
include 'config.php';
// include 'auth.php'; 

header('Content-Type: application/json');
$response = ['status' => false, 'data' => null];

// We expect a trip_id to be passed
if (!isset($_GET['trip_id']) || !is_numeric($_GET['trip_id'])) {
    http_response_code(400);
    $response['message'] = 'A valid trip_id is required.';
    echo json_encode($response);
    exit;
}

$tripId = (int)$_GET['trip_id'];

// --- IMPORTANT: You should also verify the user_id from a secure session ---
// $userId = get_current_user_id(); // Placeholder for your auth logic

try {
    // We also join to get the first image of the place for the trip
    $sql = "SELECT 
                t.*, 
                (SELECT pi.image_url FROM place_images pi WHERE pi.place_id = t.place_id ORDER BY pi.id ASC LIMIT 1) as place_image
            FROM trips t 
            WHERE t.id = ? 
            -- AND t.user_id = ? -- You would add this line to ensure a user can only see their own trips
            LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tripId); // If you add user_id check, it would be "ii", $tripId, $userId
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trip = $result->fetch_assoc();
    
    if ($trip) {
        $response['status'] = true;
        $response['data'] = $trip;
        http_response_code(200);
    } else {
        $response['message'] = 'Trip not found.';
        http_response_code(404);
    }

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Database query failed: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>