<?php
// First make sure environment variables are loaded
if (!function_exists('getenv') || !getenv('GEMINI_API_KEY')) {
    // Try to load from config if not already loaded
    require_once __DIR__ . '/../includes/config.php';
}

// AI API Configuration
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'YOUR_API_KEY_HERE'); // Replace with your actual Gemini API key
define('AI_MODEL', getenv('AI_MODEL') ?: 'gemini-2.0-flash-lite'); // Using the latest Gemini model

// Maximum tokens for AI responses
define('MAX_TOKENS', getenv('MAX_TOKENS') ? (int)getenv('MAX_TOKENS') : 150);

// Cache settings for API responses
define('CACHE_ENABLED', getenv('CACHE_ENABLED') !== false ? (bool)getenv('CACHE_ENABLED') : true);
define('CACHE_EXPIRY', getenv('CACHE_EXPIRY') ? (int)getenv('CACHE_EXPIRY') : 3600); // 1 hour in seconds