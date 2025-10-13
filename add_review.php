<?php
include 'config.php'; // Your database connection

header('Content-Type: application/json');
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Make sure you are getting user_id from a secure session in a real app
    $user_id = $_POST['user_id'] ?? null; 
    $place_id = $_POST['place_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review_text = $_POST['review_text'] ?? '';

    if (empty($user_id) || empty($place_id) || empty($rating)) {
        http_response_code(400);
        $response = ['status' => false, 'message' => 'User ID, Place ID, and Rating are required.'];
        echo json_encode($response);
        exit;
    }

    try {
        // Check if the user has already reviewed this place
        $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND place_id = ?");
        $check_stmt->bind_param("ii", $user_id, $place_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            http_response_code(409); // 409 Conflict
            $response = ['status' => false, 'message' => 'You have already reviewed this place.'];
        } else {
            // Insert the new review
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, place_id, user_name, rating, review_text) VALUES (?, ?, (SELECT fullname FROM users WHERE id = ?), ?, ?)");
            $stmt->bind_param("iiids", $user_id, $place_id, $user_id, $rating, $review_text);
            
            if ($stmt->execute()) {
                $response = ['status' => true, 'message' => 'Review submitted successfully!'];
            } else {
                http_response_code(500);
                $response = ['status' => false, 'message' => 'Failed to submit review.'];
            }
            $stmt->close();
        }
        $check_stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        $response = ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
} else {
    http_response_code(405);
    $response = ['status' => false, 'message' => 'Invalid request method.'];
}

echo json_encode($response);
$conn->close();
?>