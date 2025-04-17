<?php
// Database connection

// Include config file if not already included
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

// Create connection using constants from config.php
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}