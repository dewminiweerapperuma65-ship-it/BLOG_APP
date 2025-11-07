<?php
/**
 * Delete Blog Post Handler
 * Deletes blog post (only by owner)
 */

require_once 'db.php';
require_once 'utils.php';

header('Content-Type: application/json');

// Require authentication
requireAuth();

// Only accept DELETE/POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendResponse(false, 'Invalid request method', null, 405);
}

// Get blog ID from query string or JSON input
$blogId = (int)($_GET['id'] ?? 0);

if ($blogId <= 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    $blogId = (int)($input['id'] ?? 0);
}

if ($blogId <= 0) {
    sendResponse(false, 'Blog ID is required', null, 400);
}

$userId = getCurrentUserId();

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
    sendResponse(false, 'You do not have permission to delete this blog post', null, 403);
}

// Delete blog post (comments will be deleted automatically due to CASCADE)
$stmt = $db->prepare("DELETE FROM blogPost WHERE id = ?");
$stmt->bind_param("i", $blogId);

if ($stmt->execute()) {
    $stmt->close();
    sendResponse(true, 'Blog post deleted successfully');
} else {
    $stmt->close();
    sendResponse(false, 'Failed to delete blog post', null, 500);
}
?>
