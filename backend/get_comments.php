<?php
/**
 * Get Comments Handler
 * Returns all comments for a specific blog post
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

header('Content-Type: application/json');

// Get blog ID from query string
$blogId = (int)($_GET['blog_id'] ?? 0);

if ($blogId <= 0) {
    sendResponse(false, 'Blog ID is required', null, 400);
}

// Get all comments for the blog post
$stmt = $db->prepare("
    SELECT c.id, c.user_id, c.content, c.created_at,
           u.username as author_name
    FROM comment c
    JOIN user u ON c.user_id = u.id
    WHERE c.blog_id = ?
    ORDER BY c.created_at ASC
");

$stmt->bind_param("i", $blogId);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

$stmt->close();

sendResponse(true, 'Comments retrieved successfully', $comments);
?>
