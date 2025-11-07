<?php
/**
 * Update Blog Post Handler
 * Updates existing blog post (only by owner)
 */

require_once 'db.php';
require_once 'utils.php';

header('Content-Type: application/json');

// Require authentication
requireAuth();

// Only accept PUT/POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$blogId = (int)($input['id'] ?? 0);
$title = sanitize($input['title'] ?? '');
$content = sanitize($input['content'] ?? '');
$userId = getCurrentUserId();

// Check for required fields
if ($blogId <= 0) {
    sendResponse(false, 'Blog ID is required', null, 400);
}

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

// Check if blog exists and user owns it
$stmt = $db->prepare("SELECT user_id FROM blogPost WHERE id = ?");
$stmt->bind_param("i", $blogId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    sendResponse(false, 'Blog post not found', null, 404);
}

$blog = $result->fetch_assoc();
$stmt->close();

// Check ownership
if (!checkOwnership($userId, $blog['user_id'])) {
    sendResponse(false, 'You do not have permission to update this blog post', null, 403);
}

// Update blog post
$stmt = $db->prepare("UPDATE blogPost SET title = ?, content = ? WHERE id = ?");
$stmt->bind_param("ssi", $title, $content, $blogId);

if ($stmt->execute()) {
    $stmt->close();
    
    // Get updated blog post
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
    $updatedBlog = $result->fetch_assoc();
    $stmt->close();
    
    sendResponse(true, 'Blog post updated successfully', $updatedBlog);
} else {
    $stmt->close();
    sendResponse(false, 'Failed to update blog post', null, 500);
}
?>
