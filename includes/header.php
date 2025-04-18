<?php 
// Start the session only if one doesn't already exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the config file
require_once __DIR__ . '/config.php';

// Include the database connection
require_once INCLUDES_PATH . 'db.php';

// Set base URL for navigation links
$base_url = BASE_URL . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <nav class="bg-red-600/80 backdrop-blur-sm text-white shadow-lg" style="position: sticky; top: 0; z-index: 100;">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?php echo $base_url; ?>index.php" class="font-bold text-xl">
                        <i class="fas fa-heartbeat mr-2"></i>LifeFlow
                    </a>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="<?php echo $base_url; ?>index.php" class="hover:text-red-200 transition">Home</a>
                    <a href="<?php echo $base_url; ?>search.php" class="hover:text-red-200 transition">Donor Search</a>
                    <a href="<?php echo $base_url; ?>camps.php" class="hover:text-red-200 transition">Blood Camps</a>
                    <a href="<?php echo $base_url; ?>request.php" class="hover:text-red-200 transition">Request Blood</a>
                    <?php if(isset($_SESSION['donor_id'])): ?>
                        <a href="<?php echo $base_url; ?>dashboard/donor.php" class="hover:text-red-200 transition">My Dashboard</a>
                        <a href="<?php echo $base_url; ?>logout.php" class="hover:text-red-200 transition">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="hover:text-red-200 transition">Login</a>
                        <a href="<?php echo $base_url; ?>register.php" class="hover:text-red-200 transition">Register</a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div id="mobile-menu" class="md:hidden hidden pt-4 pb-2">
                <a href="<?php echo $base_url; ?>index.php" class="block py-2 hover:text-red-200 transition">Home</a>
                <a href="<?php echo $base_url; ?>search.php" class="block py-2 hover:text-red-200 transition">Donor Search</a>
                <a href="<?php echo $base_url; ?>camps.php" class="block py-2 hover:text-red-200 transition">Blood Camps</a>
                <a href="<?php echo $base_url; ?>request.php" class="block py-2 hover:text-red-200 transition">Request Blood</a>
                <?php if(isset($_SESSION['donor_id'])): ?>
                    <a href="<?php echo $base_url; ?>dashboard/donor.php" class="block py-2 hover:text-red-200 transition">My Dashboard</a>
                    <a href="<?php echo $base_url; ?>logout.php" class="block py-2 hover:text-red-200 transition">Logout</a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>login.php" class="block py-2 hover:text-red-200 transition">Login</a>
                    <a href="<?php echo $base_url; ?>register.php" class="block py-2 hover:text-red-200 transition">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-6">
