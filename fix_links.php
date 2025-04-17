<?php
/**
 * Link Fixer Script for Blood Donation System
 * 
 * This script scans through all PHP files in the project directory and its subdirectories,
 * finding and replacing absolute URL paths (starting with "/") with relative paths.
 * 
 * Run this script from the command line:
 * php fix_links.php
 */

echo "Blood Donation System - Link Fixer Script\n";
echo "----------------------------------------\n\n";

// Directory to start the scan
$startDir = __DIR__;
$modifiedFiles = 0;
$totalFiles = 0;

// Find all PHP files
$phpFiles = findPHPFiles($startDir);
$totalFiles = count($phpFiles);

echo "Found $totalFiles PHP files to scan.\n\n";

// Process each file
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Replace absolute URLs in href attributes
    $content = preg_replace('/href=["\']\/(.*?)["\']/', 'href="$1"', $content);
    
    // Replace absolute URLs in src attributes
    $content = preg_replace('/src=["\']\/(.*?)["\']/', 'src="$1"', $content);
    
    // Replace PHP redirects like header("Location: page.php")
    $content = preg_replace('/header\(["\']Location:\s*\/(.*?)["\']/', 'header("Location: $1"', $content);
    
    // If content was modified, save it
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $modifiedFiles++;
        echo "Modified: " . str_replace($startDir . '/', '', $file) . "\n";
    }
}

echo "\nCompleted! Modified $modifiedFiles of $totalFiles files.\n";
echo "Your website links should now work correctly with XAMPP.\n";

/**
 * Find all PHP files in directory and subdirectories
 */
function findPHPFiles($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Skip .git directory
            if ($file === '.git') {
                continue;
            }
            $result = array_merge($result, findPHPFiles($path));
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $result[] = $path;
        }
    }
    
    return $result;
}
?> 