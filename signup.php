<?php
include 'config.php';

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input from form-data or raw POST
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $response['status'] = false;
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $response['status'] = false;
        $response['message'] = 'Email already exists.';
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $email, $hashedPassword);

    if ($stmt->execute()) {
        $response['status'] = true;
        $response['message'] = 'Signup successful.';
        $response['user_id'] = $stmt->insert_id;
    } else {
        $response['status'] = false;
        $response['message'] = 'Signup failed: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['status'] = false;
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
