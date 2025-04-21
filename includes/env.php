<?php
/**
 * Simple .env file parser for Blood Donation System
 * Load environment variables from .env file
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception(".env file not found at: $path");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes from value
        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }
        
        // Handle variable substitution ${VAR}
        if (strpos($value, '${') !== false) {
            preg_match_all('/\${([^}]+)}/', $value, $matches);
            foreach ($matches[0] as $index => $match) {
                $varName = $matches[1][$index];
                
                // Handle special case for $_SERVER
                if (strpos($varName, '_SERVER[') === 0) {
                    $serverVar = str_replace(['_SERVER[', "'", '"', ']'], '', $varName);
                    $value = str_replace($match, $_SERVER[$serverVar] ?? '', $value);
                } else {
                    $value = str_replace($match, getenv($varName) ?: '', $value);
                }
            }
        }
        
        // Set environment variable
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Load .env file from project root
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);