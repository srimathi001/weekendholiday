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

if (!isset($_POST['user_id'])) {
    http_response_code(400);
    $response['message'] = 'User ID is missing.';
    echo json_encode($response);
    exit;
}

$userId = (int)$_POST['user_id'];

try {
    // This SQL query finds all trips for the user that are 'in-progress' 
    // AND whose end date is before today's date. It then updates their status to 'finished'.
    $sql = "UPDATE trips 
            SET status = 'finished' 
            WHERE user_id = ? 
            AND status = 'in-progress' 
            AND end_date < CURDATE()";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $response = ['status' => true, 'message' => "$affected_rows trip(s) marked as finished."];
        http_response_code(200);
    } else {
        $response['message'] = 'Failed to update trip statuses.';
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