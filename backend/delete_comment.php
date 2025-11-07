<?php
/**
 * Delete Comment Handler
 * Deletes comment (only by comment owner or blog owner)
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

// Get comment ID from query string or JSON input
$commentId = (int)($_GET['id'] ?? 0);

if ($commentId <= 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    $commentId = (int)($input['id'] ?? 0);
}

if ($commentId <= 0) {
    sendResponse(false, 'Comment ID is required', null, 400);
}

$userId = getCurrentUserId();

// Get comment and blog info to check ownership
$stmt = $db->prepare("
    SELECT c.user_id as comment_user_id, b.user_id as blog_user_id
    FROM comment c
    JOIN blogPost b ON c.blog_id = b.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $commentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    sendResponse(false, 'Comment not found', null, 404);
}

$data = $result->fetch_assoc();
$stmt->close();

// Check if user is comment owner or blog owner
$isCommentOwner = checkOwnership($userId, $data['comment_user_id']);
$isBlogOwner = checkOwnership($userId, $data['blog_user_id']);

if (!$isCommentOwner && !$isBlogOwner) {
    sendResponse(false, 'You do not have permission to delete this comment', null, 403);
}

// Delete comment
$stmt = $db->prepare("DELETE FROM comment WHERE id = ?");
$stmt->bind_param("i", $commentId);

if ($stmt->execute()) {
    $stmt->close();
    sendResponse(true, 'Comment deleted successfully');
} else {
    $stmt->close();
    sendResponse(false, 'Failed to delete comment', null, 500);
}
?>
