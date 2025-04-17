<?php
/**
 * Configuration file for the Blood Donation System
 * Contains global settings, paths, and other configuration variables
 */

// Define application paths
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Blood-Donation/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('DASHBOARD_PATH', ROOT_PATH . 'dashboard/');

// URL paths (without trailing slash)
define('BASE_URL', '/Blood-Donation');
define('DASHBOARD_URL', BASE_URL . '/dashboard');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blood_donation');

// Application settings
define('APP_NAME', 'Blood Donation System');
define('APP_EMAIL', 'noreply@blooddonate.local');

// Function to convert a path to a URL
function path_to_url($path) {
    return str_replace(ROOT_PATH, BASE_URL . '/', $path);
}

// Function to get the relative path for linking between directories
function get_relative_path($from, $to) {
    $from = explode('/', $from);
    $to = explode('/', $to);
    
    // Find common path
    $common = 0;
    $max = min(count($from), count($to));
    for ($i = 0; $i < $max; $i++) {
        if ($from[$i] == $to[$i]) {
            $common++;
        } else {
            break;
        }
    }
    
    // Build path
    $path = '';
    for ($i = $common; $i < count($from) - 1; $i++) {
        $path .= '../';
    }
    
    for ($i = $common; $i < count($to); $i++) {
        $path .= $to[$i] . '/';
    }
    
    return rtrim($path, '/');
}
?> 