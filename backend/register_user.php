<?php
/**
 * User Registration Handler
 * Handles new user registration with validation
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
$username = sanitize($input['username'] ?? '');
$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';

// Check for empty fields
if (empty($username) || empty($email) || empty($password)) {
    sendResponse(false, 'All fields are required', null, 400);
}

// Validate username length
if (strlen($username) < 3 || strlen($username) > 50) {
    sendResponse(false, 'Username must be between 3 and 50 characters', null, 400);
}

// Validate email format
if (!validateEmail($email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Validate password length
if (strlen($password) < 6) {
    sendResponse(false, 'Password must be at least 6 characters', null, 400);
}

// Check if email already exists
$stmt = $db->prepare("SELECT id FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    sendResponse(false, 'Email already registered', null, 409);
}
$stmt->close();

// Check if username already exists
$stmt = $db->prepare("SELECT id FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    sendResponse(false, 'Username already taken', null, 409);
}
$stmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $db->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;
    $stmt->close();
    
    sendResponse(true, 'Registration successful', [
        'id' => $userId,
        'username' => $username,
        'email' => $email
    ], 201);
} else {
    $stmt->close();
    sendResponse(false, 'Registration failed. Please try again.', null, 500);
}
?>
