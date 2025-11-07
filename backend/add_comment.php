<?php
/**
 * Add Comment Handler
 * Adds a comment to a blog post
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
$blogId = (int)($input['blog_id'] ?? 0);
$content = sanitize($input['content'] ?? '');
$userId = getCurrentUserId();

// Check for required fields
if ($blogId <= 0) {
    sendResponse(false, 'Blog ID is required', null, 400);
}

if (empty($content)) {
    sendResponse(false, 'Comment content is required', null, 400);
}

// Validate content length
if (strlen($content) < 1 || strlen($content) > 1000) {
    sendResponse(false, 'Comment must be between 1 and 1000 characters', null, 400);
}

// Check if blog exists
$stmt = $db->prepare("SELECT id FROM blogPost WHERE id = ?");
$stmt->bind_param("i", $blogId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    sendResponse(false, 'Blog post not found', null, 404);
}
$stmt->close();

// Insert comment
$stmt = $db->prepare("INSERT INTO comment (user_id, blog_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $userId, $blogId, $content);

if ($stmt->execute()) {
    $commentId = $stmt->insert_id;
    $stmt->close();
    
    // Get the created comment with user info
    $stmt = $db->prepare("
        SELECT c.id, c.content, c.created_at, 
               u.username as author_name, c.user_id
        FROM comment c
        JOIN user u ON c.user_id = u.id
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    $stmt->close();
    
    sendResponse(true, 'Comment added successfully', $comment, 201);
} else {
    $stmt->close();
    sendResponse(false, 'Failed to add comment', null, 500);
}
?>
