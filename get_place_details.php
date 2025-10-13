<?php
include 'config.php';
header('Content-Type: application/json');

$response = ['status' => false, 'message' => 'An error occurred.'];

if (!isset($_GET['place_id']) || !is_numeric($_GET['place_id'])) {
    http_response_code(400);
    $response['message'] = 'A valid place_id is required.';
    echo json_encode($response);
    exit;
}

$placeId = intval($_GET['place_id']);

try {
    // 1. Fetch main place details
    $stmt = $conn->prepare("SELECT * FROM places WHERE id = ?");
    $stmt->bind_param("i", $placeId);
    $stmt->execute();
    $placeData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$placeData) {
        http_response_code(404);
        $response['message'] = 'Place not found.';
        echo json_encode($response);
        exit;
    }
    
    // 2. Fetch all images
    $stmt_images = $conn->prepare("SELECT image_url FROM place_images WHERE place_id = ?");
    $stmt_images->bind_param("i", $placeId);
    $stmt_images->execute();
    $images_result = $stmt_images->get_result();
    $images = [];
    while ($row = $images_result->fetch_assoc()) {
        $images[] = $row['image_url'];
    }
    $placeData['images'] = $images;
    $stmt_images->close();
    
    // 3. Fetch all top spots - INCLUDING LATITUDE AND LONGITUDE
    // START: *** THIS LINE IS UPDATED ***
    $stmt_spots = $conn->prepare("SELECT name, description, latitude, longitude FROM top_spots WHERE place_id = ?");
    // END: *** THIS LINE IS UPDATED ***
    $stmt_spots->bind_param("i", $placeId);
    $stmt_spots->execute();
    $spots_result = $stmt_spots->get_result();
    $topSpots = [];
    while ($row = $spots_result->fetch_assoc()) {
        $topSpots[] = $row;
    }
    $placeData['top_spots'] = $topSpots;
    $stmt_spots->close();

    // 4. Fetch transport options
    $stmt_transport = $conn->prepare("SELECT icon, type, info FROM transport_options WHERE place_id = ?");
    $stmt_transport->bind_param("i", $placeId);
    $stmt_transport->execute();
    $transport_result = $stmt_transport->get_result();
    $transportOptions = [];
    while ($row = $transport_result->fetch_assoc()) {
        $transportOptions[] = $row;
    }
    $placeData['transport_options'] = $transportOptions;
    $stmt_transport->close();

    // 5. Calculate average rating and review count
    $review_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as review_count FROM reviews WHERE place_id = ?");
    $review_stmt->bind_param("i", $placeId);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result()->fetch_assoc();
    $review_stmt->close();
    
    $placeData['averageRating'] = $review_result['avg_rating'] ? (float)$review_result['avg_rating'] : 0.0;
    $placeData['reviewCount'] = (int)$review_result['review_count'];

    // This is optional but good practice, ensures numbers are sent as numbers
    $numeric_fields = ['latitude', 'longitude', 'toll_cost', 'parking_cost', 'hotel_std_cost', 'hotel_high_cost', 'hotel_low_cost', 'food_std_veg', 'food_std_nonveg', 'food_std_combo', 'food_high_veg', 'food_high_nonveg', 'food_high_combo', 'food_low_veg', 'food_low_nonveg', 'food_low_combo'];
    foreach ($numeric_fields as $field) {
        if (isset($placeData[$field])) {
            $placeData[$field] = (float)$placeData[$field];
        }
    }
    
    $response = ['status' => true, 'data' => $placeData];
    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Database query failed: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>