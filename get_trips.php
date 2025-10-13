<?php
include 'config.php';
// include 'auth.php'; // You would have your user authentication logic here

header('Content-Type: application/json');
$response = ['status' => false, 'data' => []];

// --- IMPORTANT: Securely Get User ID ---
if (!isset($_GET['user_id'])) {
    http_response_code(401);
    $response['message'] = 'User ID is missing.';
    echo json_encode($response);
    exit;
}
$userId = (int)$_GET['user_id'];

// Get the requested trip status (e.g., 'upcoming', 'completed', 'cancelled')
$status = $_GET['status'] ?? 'upcoming';

try {
    // We also need the first image for each place to show in the list
    // ===============================================================
    // == UPDATED: MODIFIED SQL QUERY AND BINDING ==
    // ===============================================================
    $sql = "";
    $stmt = null;

    // If the request is for the 'upcoming' tab, we fetch BOTH 'upcoming' and 'in-progress' trips.
    if ($status === 'upcoming') {
        $sql = "SELECT 
                    t.*, 
                    (SELECT pi.image_url FROM place_images pi WHERE pi.place_id = t.place_id ORDER BY pi.id ASC LIMIT 1) as place_image
                FROM trips t 
                WHERE t.user_id = ? AND (t.status = 'upcoming' OR t.status = 'in-progress')
                ORDER BY t.start_date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
    } else {
        // For any other status ('cancelled', 'finished'), fetch only that specific status.
        $sql = "SELECT 
                    t.*, 
                    (SELECT pi.image_url FROM place_images pi WHERE pi.place_id = t.place_id ORDER BY pi.id ASC LIMIT 1) as place_image
                FROM trips t 
                WHERE t.user_id = ? AND t.status = ?
                ORDER BY t.start_date ASC";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $status);
    }
    // ===============================================================
    // == END OF UPDATE ==
    // ===============================================================

    $stmt->execute();
    $result = $stmt->get_result();
    
    $trips = [];
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    
    $response['status'] = true;
    $response['data'] = $trips;
    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Database query failed: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>