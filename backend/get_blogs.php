<?php
    
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get All Blog Posts Handler
 * Returns all blog posts with author information
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

header('Content-Type: application/json');

// Get all blog posts with author names
$query = "
    SELECT b.id, b.user_id, b.title, b.content, b.created_at, b.updated_at,
           u.username as author_name,
           (SELECT COUNT(*) FROM comment WHERE blog_id = b.id) as comment_count
    FROM blogPost b
    JOIN user u ON b.user_id = u.id
    ORDER BY b.created_at DESC
";

$result = $db->query($query);

if (!$result) {
    sendResponse(false, 'Failed to fetch blog posts', null, 500);
}

$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}

sendResponse(true, 'Blog posts retrieved successfully', $blogs);
?>
