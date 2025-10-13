<?php
require_once 'vendor/autoload.php';
require_once 'config.php'; // Your database connection

header('Content-Type: application/json');
$response = [];

// Get this from your Google Cloud Console. It's the SAME Web Client ID you use in the app.
$CLIENT_ID = "991194764836-6fticqe7mm7v40b9ap6lpe5fjogquk13.apps.googleusercontent.com";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_token = $_POST['idToken'] ?? '';

    if (empty($id_token)) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'ID token is required.']);
        exit;
    }

    $client = new Google_Client(['client_id' => $CLIENT_ID]);
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $email = $payload['email'];
        $fullname = $payload['name'];

        // Check if user already exists in your database
        $stmt = $conn->prepare("SELECT id, fullname, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            // User exists, log them in
            $user_id = $user['id'];
        } else {
            // User does not exist, create a new account with a random password hash
            $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $fullname, $email, $random_password);
            $insertStmt->execute();
            $user_id = $insertStmt->insert_id;
            $insertStmt->close();
        }

        // Generate and save a session token
        $session_token = bin2hex(random_bytes(32));
        $updateStmt = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
        $updateStmt->bind_param("si", $session_token, $user_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Send success response (same structure as login.php)
        $response['status'] = true;
        $response['message'] = 'Login successful!';
        $response['token'] = $session_token;
        $response['user'] = ['id' => $user_id, 'fullname' => $fullname, 'email' => $email];
        echo json_encode($response);

    } else {
        // Invalid ID token
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Invalid Google token.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>