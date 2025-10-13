<?php
include 'config.php';

header('Content-Type: application/json');
$response = ['status' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// In a real app, user ID would come from a secure session/token
if (!isset($_POST['user_id']) || !isset($_POST['trip_ids_json'])) {
    http_response_code(400);
    $response['message'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

$userId = (int)$_POST['user_id'];
$tripIdsJson = $_POST['trip_ids_json'];
$tripIds = json_decode($tripIdsJson, true);

if (empty($tripIds) || !is_array($tripIds)) {
    http_response_code(400);
    $response['message'] = 'Invalid or empty trip IDs provided.';
    echo json_encode($response);
    exit;
}

// Sanitize all IDs to ensure they are integers
$sanitizedIds = array_map('intval', $tripIds);
$placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));
$types = str_repeat('i', count($sanitizedIds)); // 'i' for integer

try {
    // The IN clause is perfect for deleting multiple rows at once.
    // We also check the user_id to ensure users can only delete their own trips.
    $sql = "DELETE FROM trips WHERE user_id = ? AND id IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    // Bind the user_id first, then all the trip IDs
    $stmt->bind_param("i" . $types, $userId, ...$sanitizedIds);
    
    if ($stmt->execute()) {
        $response = ['status' => true, 'message' => 'Selected trips deleted successfully.'];
        http_response_code(200);
    } else {
        $response['message'] = 'Failed to delete trips.';
        http_response_code(500);
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Database transaction failed: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>