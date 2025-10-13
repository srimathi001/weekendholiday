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

// In a real app, you would also verify the user ID from a secure session token
if (!isset($_POST['trip_id']) || !isset($_POST['new_status'])) {
    http_response_code(400);
    $response['message'] = 'Missing required parameters: trip_id and new_status are required.';
    echo json_encode($response);
    exit;
}

$tripId = (int)$_POST['trip_id'];
$newStatus = $_POST['new_status'];

try {
    $sql = "UPDATE trips SET status = ? WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newStatus, $tripId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = ['status' => true, 'message' => 'Trip status updated successfully!'];
            http_response_code(200);
        } else {
            $response['message'] = 'Trip not found or status is already the same.';
            http_response_code(404);
        }
    } else {
        $response['message'] = 'Failed to update trip status.';
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