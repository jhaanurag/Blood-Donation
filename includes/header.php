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
    <!-- Chart.js Library for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-200">
    
    <nav class="bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg dark:from-red-800 dark:to-red-900 transition-colors duration-200" style="position: sticky; top: 0; z-index: 100;">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <!-- Hamburger menu with improved hover behavior - hidden on mobile -->
                    <div class="relative hidden md:block" id="hamburger-menu">
                        <button class="text-white p-2 rounded hover:bg-red-500/30 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <!-- Dropdown menu with improved spacing and hover behavior -->
                        <div class="absolute left-0 top-full mt-1 w-64 bg-white dark:bg-gray-800 rounded shadow-lg hidden z-50 pb-2" id="hamburger-dropdown">
                            <div class="p-4 text-gray-800 dark:text-white space-y-3">
                                <a href="<?php echo $base_url; ?>index.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-home w-5 text-center"></i> <span>Home</span>
                                </a>
                                <a href="<?php echo $base_url; ?>search.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-search w-5 text-center"></i> <span>Donor Search</span>
                                </a>
                                <a href="<?php echo $base_url; ?>camps.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-tint w-5 text-center"></i> <span>Blood Camps</span>
                                </a>
                                <a href="<?php echo $base_url; ?>request.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-hand-holding-medical w-5 text-center"></i> <span>Request Blood</span>
                                </a>
                                <a href="<?php echo $base_url; ?>chatbot/index.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-robot w-5 text-center"></i> <span>Ask Assistant</span>
                                </a>
                                <a href="<?php echo $base_url; ?>chatbot/eligibility.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-clipboard-check w-5 text-center"></i> <span>Eligibility Check</span>
                                </a>
                                <a href="<?php echo $base_url; ?>games/blood_facts_challenge.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-gamepad w-5 text-center"></i> <span>Blood Facts Game</span>
                                </a>
                                <?php if(isset($_SESSION['donor_id'])): ?>
                                <a href="<?php echo $base_url; ?>dashboard/donor.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-user w-5 text-center"></i> <span>Dashboard</span>
                                </a>
                                <a href="<?php echo $base_url; ?>dashboard/analytics.php" class="block px-3 py-3 rounded hover:bg-red-500/20 dark:hover:bg-red-700/30 transition flex items-center gap-2">
                                    <i class="fas fa-chart-bar w-5 text-center"></i> <span>Analytics</span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo $base_url; ?>index.php" class="font-bold text-xl">
                        <i class="fas fa-heartbeat mr-2 text-white"></i>LifeFlow
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-2 flex-nowrap overflow-x-auto whitespace-nowrap">
                    <!-- Show only the most important navigation items here -->
                    <a href="<?php echo $base_url; ?>index.php" class="px-2 py-2 rounded hover:bg-red-500/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo $base_url; ?>search.php" class="px-2 py-2 rounded hover:bg-red-500/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-search"></i> Donor Search
                    </a>
                    <a href="<?php echo $base_url; ?>request.php" class="px-2 py-2 rounded hover:bg-red-500/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-hand-holding-medical"></i> Request
                    </a>
                    <?php if(isset($_SESSION['donor_id'])): ?>
                        <a href="<?php echo $base_url; ?>dashboard/donor.php" class="px-2 py-2 rounded hover:bg-red-500/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                        <a href="<?php echo $base_url; ?>logout.php" class="px-2 py-2 rounded hover:bg-gray-800/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="px-2 py-2 rounded hover:bg-gray-800/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo $base_url; ?>register.php" class="px-2 py-2 rounded hover:bg-gray-800/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                    <!-- Dark mode toggle button -->
                    <button id="darkModeToggle" class="text-white p-2 rounded-full hover:bg-red-500/30 dark:hover:bg-red-700 focus:outline-none ml-2">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>
                </div>
                <div class="md:hidden flex items-center">
                    <!-- Dark mode toggle for mobile -->
                    <button id="darkModeToggleMobile" class="text-white p-2 rounded-full hover:bg-red-500/30 dark:hover:bg-red-700 focus:outline-none mr-2">
                        <i id="darkModeIconMobile" class="fas fa-moon"></i>
                    </button>
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div id="mobile-menu" class="md:hidden hidden pt-4 pb-2">
                <a href="<?php echo $base_url; ?>index.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Home</a>
                <a href="<?php echo $base_url; ?>search.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Donor Search</a>
                <a href="<?php echo $base_url; ?>camps.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Blood Camps</a>
                <a href="<?php echo $base_url; ?>request.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Request Blood</a>
                <a href="<?php echo $base_url; ?>chatbot/index.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Ask Assistant</a>
                <a href="<?php echo $base_url; ?>chatbot/eligibility.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Eligibility Check</a>
                <a href="<?php echo $base_url; ?>games/blood_facts_challenge.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Blood Facts Game</a>
                <?php if(isset($_SESSION['donor_id'])): ?>
                    <a href="<?php echo $base_url; ?>dashboard/donor.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">My Dashboard</a>
                    <a href="<?php echo $base_url; ?>dashboard/analytics.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Analytics</a>
                    <a href="<?php echo $base_url; ?>logout.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Logout</a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>login.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition">Login</a>
                    <a href="<?php echo $base_url; ?>register.php" class="block py-2 hover:bg-red-500/20 rounded px-2 transition font-medium">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="container mx-auto px-4 py-6"><?php // Main content starts here ?>
