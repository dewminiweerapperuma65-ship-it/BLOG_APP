<?php
/**
 * Database Connection Handler
 * Uses environment variables from .env file
 */

// Load environment variables
require_once __DIR__ . '/load_env.php';

// Database connection function
function getDBConnection() {
    // Get credentials from environment
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $dbname = $_ENV['DB_NAME'] ?? 'blog_db';
    
    // Create connection
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Get single database connection (singleton pattern)
$db = getDBConnection();
?>
