<?php
/**
 * Get Single Blog Post Handler
 * Returns single blog post with author information
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

header('Content-Type: application/json');

// Get blog ID from query string
$blogId = (int)($_GET['id'] ?? 0);

if ($blogId <= 0) {
    sendResponse(false, 'Blog ID is required', null, 400);
}

// Get blog post with author name
$stmt = $db->prepare("
    SELECT b.id, b.user_id, b.title, b.content, b.created_at, b.updated_at,
           u.username as author_name
    FROM blogPost b
    JOIN user u ON b.user_id = u.id
    WHERE b.id = ?
");

$stmt->bind_param("i", $blogId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    sendResponse(false, 'Blog post not found', null, 404);
}

$blog = $result->fetch_assoc();
$stmt->close();

sendResponse(true, 'Blog post retrieved successfully', $blog);
?>