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
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Add Tailwind dark mode configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    // Custom theme extensions if needed
                    colors: {
                        'dark-bg': '#121212',
                        'dark-card': '#1E1E1E',
                        'dark-border': '#333333'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-200">
    
    <nav class="bg-red-600/80 backdrop-blur-sm text-white shadow-lg dark:bg-red-900 transition-colors duration-200" style="position: sticky; top: 0; z-index: 100;">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?php echo $base_url; ?>index.php" class="font-bold text-xl">
                        <i class="fas fa-heartbeat mr-2"></i>LifeFlow
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="<?php echo $base_url; ?>index.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo $base_url; ?>search.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-search"></i> Donor Search
                    </a>
                    <a href="<?php echo $base_url; ?>camps.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-tint"></i> Blood Camps
                    </a>
                    <a href="<?php echo $base_url; ?>request.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-hand-holding-medical"></i> Request Blood
                    </a>
                    <a href="<?php echo $base_url; ?>chatbot/index.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-robot"></i> Ask Assistant
                    </a>
                    <a href="<?php echo $base_url; ?>chatbot/eligibility.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-clipboard-check"></i> Eligibility Check
                    </a>
                    <?php if(isset($_SESSION['donor_id'])): ?>
                        <a href="<?php echo $base_url; ?>dashboard/donor.php" class="px-3 py-2 rounded hover:bg-red-700/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-user"></i> My Dashboard
                        </a>
                        <a href="<?php echo $base_url; ?>logout.php" class="px-3 py-2 rounded hover:bg-gray-800/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="px-3 py-2 rounded hover:bg-gray-800/80 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo $base_url; ?>register.php" class="px-3 py-2 rounded bg-white text-red-700 hover:bg-red-100 transition font-medium border border-red-200 flex items-center gap-1">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                    <!-- Dark mode toggle button -->
                    <button id="darkModeToggle" class="text-white p-2 rounded-full hover:bg-red-700 dark:hover:bg-red-800 focus:outline-none ml-2">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>
                </div>
                <div class="md:hidden flex items-center">
                    <!-- Dark mode toggle for mobile -->
                    <button id="darkModeToggleMobile" class="text-white p-2 rounded-full hover:bg-red-700 dark:hover:bg-red-800 focus:outline-none mr-2">
                        <i id="darkModeIconMobile" class="fas fa-moon"></i>
                    </button>
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
                <a href="<?php echo $base_url; ?>chatbot/index.php" class="block py-2 hover:text-red-200 transition">Ask Assistant</a>
                <a href="<?php echo $base_url; ?>chatbot/eligibility.php" class="block py-2 hover:text-red-200 transition">Eligibility Check</a>
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
    <main class="container mx-auto px-4 py-6"><?php // Main content starts here ?>
    
    <?php 
    // Include the floating chatbot component
    include_once INCLUDES_PATH . 'components/floating_chatbot.php';
    ?>
