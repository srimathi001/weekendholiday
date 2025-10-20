<?php
header('Content-Type: application/json');
require_once 'config.php'; // Your database connection

// You would typically get the user ID from a session or token
// For this example, we'll use a GET parameter.
if (!isset($_GET['user_id'])) {
    echo json_encode(['status' => false, 'message' => 'User ID is required.']);
    exit;
}
$userId = $_GET['user_id'];

$sql = "SELECT id, fullname, username, email, phone, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo json_encode(['status' => true, 'data' => $user]);
} else {
    echo json_encode(['status' => false, 'message' => 'User not found.']);
}

$stmt->close();
$conn->close();
?>