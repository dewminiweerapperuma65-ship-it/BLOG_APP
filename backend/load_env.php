<?php
/**
 * Environment Variable Loader
 * Parses .env file and loads variables into $_ENV
 */

function loadEnv($path) {
    // Check if .env file exists
    if (!file_exists($path)) {
        die("Error: .env file not found at: " . $path);
    }
    
    // Read the file line by line
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env file from the env directory
$envPath = __DIR__ . '/../env/.env';
loadEnv($envPath);
?>
