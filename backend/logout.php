<?php
/**
 * User Logout Handler
 * Destroys session and logs out user
 */

require_once 'utils.php';

header('Content-Type: application/json');

// Destroy session
session_unset();
session_destroy();

sendResponse(true, 'Logout successful');
?>
