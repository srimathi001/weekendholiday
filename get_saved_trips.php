<?php
header('Content-Type: application/json');
require_once 'config.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => false, 'message' => 'User ID is required.']);
    exit;
}

$sql = "SELECT p.id, p.name, p.location, 
               (SELECT image_url FROM place_images WHERE place_id = p.id ORDER BY id ASC LIMIT 1) as image_url
        FROM places p
        JOIN saved_trips st ON p.id = st.place_id
        WHERE st.user_id = ?
        ORDER BY st.saved_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$savedTrips = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status' => true, 'data' => $savedTrips]);

$stmt->close();
$conn->close();
?>