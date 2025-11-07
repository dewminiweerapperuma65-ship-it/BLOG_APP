<?php
/**
 * Add Blog Post Handler
 * Creates a new blog post for authenticated user
 */

require_once 'db.php';
require_once 'utils.php';

header('Content-Type: application/json');

// Require authentication
requireAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$title = sanitize($input['title'] ?? '');
$content = sanitize($input['content'] ?? '');
$userId = getCurrentUserId();

// Check for empty fields
if (empty($title) || empty($content)) {
    sendResponse(false, 'Title and content are required', null, 400);
}

// Validate title length
if (strlen($title) < 3 || strlen($title) > 255) {
    sendResponse(false, 'Title must be between 3 and 255 characters', null, 400);
}

// Validate content length
if (strlen($content) < 10) {
    sendResponse(false, 'Content must be at least 10 characters', null, 400);
}

// Insert blog post
$stmt = $db->prepare("INSERT INTO blogPost (user_id, title, content) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $userId, $title, $content);

if ($stmt->execute()) {
    $blogId = $stmt->insert_id;
    $stmt->close();
    
    // Get the created blog post
    $stmt = $db->prepare("
        SELECT b.id, b.title, b.content, b.created_at, b.updated_at, 
               u.username as author_name
        FROM blogPost b
        JOIN user u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $blogId);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();
    $stmt->close();
    
    sendResponse(true, 'Blog post created successfully', $blog, 201);
} else {
    $stmt->close();
    sendResponse(false, 'Failed to create blog post', null, 500);
}
?>
