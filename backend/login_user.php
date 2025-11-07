<?php
/**
 * User Login Handler
 * Authenticates user and creates session
 */

require_once 'db.php';
require_once 'utils.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';

// Check for empty fields
if (empty($email) || empty($password)) {
    sendResponse(false, 'Email and password are required', null, 400);
}

// Validate email format
if (!validateEmail($email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Find user by email
$stmt = $db->prepare("SELECT id, username, email, password, role FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    sendResponse(false, 'Invalid email or password', null, 401);
}

// Get user data
$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password'])) {
    sendResponse(false, 'Invalid email or password', null, 401);
}

// Create session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// Return user data (without password)
sendResponse(true, 'Login successful', [
    'id' => $user['id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role']
]);
?>
