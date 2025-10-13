<?php
include 'config.php'; // Your database connection

header('Content-Type: application/json');

// --- Define Filter Parameters ---
$filterType = $_GET['filter_type'] ?? 'current_month'; // 'current_month', 'by_month', 'nearby', 'monsoon', 'all'
$userLat = $_GET['user_lat'] ?? null;
$userLng = $_GET['user_lng'] ?? null;

// --- Initialize Response Structure ---
$response = [
    'recommended_place' => null,
    'popular_places' => []
];

try {
    // ==================================================================
    // 1. LOGIC FOR THE TOP "RECOMMENDED PLACE"
    // This part only runs on the initial load ('current_month' filter).
    // It finds the most booked trip for the current month.
    // ==================================================================
    if ($filterType === 'current_month') {
        $currentMonth = date('M'); // e.g., 'Oct'
        
        $sql_recommended = "SELECT p.*, COUNT(t.id) as trip_count 
                            FROM places p
                            LEFT JOIN trips t ON p.id = t.place_id
                            WHERE FIND_IN_SET(?, REPLACE(p.suitable_months, ' ', '')) > 0
                            GROUP BY p.id
                            ORDER BY trip_count DESC
                            LIMIT 1";

        $stmt_recommended = $conn->prepare($sql_recommended);
        $stmt_recommended->bind_param("s", $currentMonth);
        $stmt_recommended->execute();
        $result_recommended = $stmt_recommended->get_result();
        $recommended_place_data = $result_recommended->fetch_assoc();
        $stmt_recommended->close();

        if ($recommended_place_data) {
            // Fetch images for the recommended place
            $img_stmt = $conn->prepare("SELECT image_url FROM place_images WHERE place_id = ? ORDER BY id");
            $img_stmt->bind_param("i", $recommended_place_data['id']);
            $img_stmt->execute();
            $images_result = $img_stmt->get_result();
            $images = $images_result->fetch_all(MYSQLI_ASSOC);
            $img_stmt->close();
            
            $recommended_place_data['images'] = array_column($images, 'image_url');
            $response['recommended_place'] = $recommended_place_data;
        }
    }

    // ==================================================================
    // 2. LOGIC FOR THE BOTTOM "POPULAR PLACES" LIST (WITH FILTERS)
    // This part runs every time, adapting to the selected filter.
    // ==================================================================
    $sql_popular = "";
    $params = [];
    $types = "";

    switch ($filterType) {
        // --- NEW CASE TO HANDLE SPECIFIC MONTH SELECTION ---
        case 'by_month':
            if (isset($_GET['month'])) {
                $selectedMonth = $_GET['month'];
                $sql_popular = "SELECT p.* FROM places p WHERE FIND_IN_SET(?, REPLACE(p.suitable_months, ' ', '')) > 0 ORDER BY p.name ASC";
                $params = [$selectedMonth];
                $types = "s";
            }
            break;

        case 'nearby':
            if ($userLat !== null && $userLng !== null) {
                // Haversine formula to calculate distance in Kilometers
                $sql_popular = "SELECT p.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( p.latitude ) ) * cos( radians( p.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( p.latitude ) ) ) ) AS distance 
                                FROM places p 
                                ORDER BY distance ASC
                                LIMIT 15";
                $params = [$userLat, $userLng, $userLat];
                $types = "ddd";
            }
            break;

        case 'monsoon':
            $sql_popular = "SELECT p.* FROM places p WHERE p.is_monsoon_destination = 1 ORDER BY p.name ASC";
            break;
            
        case 'all':
            $sql_popular = "SELECT p.* FROM places p ORDER BY p.name ASC";
            break;
            
        case 'current_month':
        default:
            $currentMonth = date('M');
            $sql_popular = "SELECT p.* FROM places p WHERE FIND_IN_SET(?, REPLACE(p.suitable_months, ' ', '')) > 0";
            if ($response['recommended_place']) {
                $sql_popular .= " AND p.id != ?";
                $params = [$currentMonth, $response['recommended_place']['id']];
                $types = "si";
            } else {
                $params = [$currentMonth];
                $types = "s";
            }
            $sql_popular .= " ORDER BY p.name ASC";
            break;
    }

    if ($sql_popular) {
        $stmt_popular = $conn->prepare($sql_popular);
        if (!empty($params)) {
            $stmt_popular->bind_param($types, ...$params);
        }
        $stmt_popular->execute();
        $result_popular = $stmt_popular->get_result();
        $popular_places_data = $result_popular->fetch_all(MYSQLI_ASSOC);
        $stmt_popular->close();
        
        foreach ($popular_places_data as $place) {
            $img_stmt = $conn->prepare("SELECT image_url FROM place_images WHERE place_id = ? ORDER BY id");
            $img_stmt->bind_param("i", $place['id']);
            $img_stmt->execute();
            $images_result = $img_stmt->get_result();
            $images = $images_result->fetch_all(MYSQLI_ASSOC);
            $img_stmt->close();
            
            $place['images'] = array_column($images, 'image_url');
            $response['popular_places'][] = $place;
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>