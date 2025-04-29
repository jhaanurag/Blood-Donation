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
<html lang="en" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'dark' ? 'dark' : 'light'; ?> <?php echo isset($_SESSION['theme_active']) ? $_SESSION['theme_active'] : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $base_url; ?>assets/heart.svg" type="image/svg+xml">
    <title><?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TODO: Install Tailwind locally for production -->
    <script>
        // Add Tailwind dark mode configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    // Custom theme extensions if needed
                    colors: {
                        'primary-light': '#6366F1', /* Bright indigo */
                        'primary-dark': '#4F46E5', /* Deep indigo */
                        'accent-light': '#F97316', /* Coral orange */
                        'accent-dark': '#EA580C', /* Deep coral */
                        'secondary': '#EC4899', /* Pink for highlight elements */
                        'success': '#22C55E', /* Green */
                        'danger': '#EF4444', /* Red for alerts */
                        'dark-bg': '#121212', /* Nearly black, neutral base */
                        'dark-card': '#252525', /* Card background */
                        'dark-border': '#333333'
                    }
                }
            }
        }

        // Ensure dark mode is set properly on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('darkMode');
            const html = document.documentElement;
            
            if (savedTheme === 'dark') {
                html.classList.add('dark');
                document.cookie = "darkMode=dark; path=/; max-age=31536000"; // 1 year
                updateDarkModeIcons('dark');
            } else {
                html.classList.remove('dark');
                document.cookie = "darkMode=light; path=/; max-age=31536000"; // 1 year
                updateDarkModeIcons('light');
            }
            
            // Initialize dark mode toggle functionality
            initDarkModeToggle();
            
            // Check for active theme from sessionStorage (for game pages)
            const activeTheme = sessionStorage.getItem('activeTheme');
            if (activeTheme) {
                document.body.classList.add(activeTheme);
            }
        });

        // Function to toggle dark mode that can be used across all pages
        function toggleDarkMode() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                // Switch to light mode
                html.classList.remove('dark');
                localStorage.setItem('darkMode', 'light');
                document.cookie = "darkMode=light; path=/; max-age=31536000"; // 1 year
                updateDarkModeIcons('light');
            } else {
                // Switch to dark mode
                html.classList.add('dark');
                localStorage.setItem('darkMode', 'dark');
                document.cookie = "darkMode=dark; path=/; max-age=31536000"; // 1 year
                updateDarkModeIcons('dark');
            }
        }
        
        // Function to update all dark mode icons
        function updateDarkModeIcons(mode) {
            const darkModeIcon = document.getElementById('darkModeIcon');
            const darkModeIconMobile = document.getElementById('darkModeIconMobile');
            
            if (mode === 'dark') {
                if (darkModeIcon) darkModeIcon.className = 'fas fa-sun';
                if (darkModeIconMobile) darkModeIconMobile.className = 'fas fa-sun';
            } else {
                if (darkModeIcon) darkModeIcon.className = 'fas fa-moon';
                if (darkModeIconMobile) darkModeIconMobile.className = 'fas fa-moon';
            }
        }
        
        // Function to initialize dark mode toggle buttons
        function initDarkModeToggle() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkModeToggleMobile = document.getElementById('darkModeToggleMobile');
            
            // Add event listeners to toggle buttons
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', toggleDarkMode);
            }
            
            if (darkModeToggleMobile) {
                darkModeToggleMobile.addEventListener('click', toggleDarkMode);
            }
        }
        
        // Make toggleDarkMode globally accessible
        window.toggleDarkMode = toggleDarkMode;
    </script>
    <!-- Chart.js Library for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-[#121212] dark:text-gray-100 min-h-screen transition-colors duration-200">
    
    <nav class="bg-gradient-to-r from-primary-light to-primary-dark text-white shadow-lg dark:from-primary-dark dark:to-[#3730A3] transition-colors duration-200" style="position: sticky; top: 0; z-index: 100;">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <!-- Hamburger menu - hidden on mobile -->
                    <div class="relative hidden md:block" id="hamburger-menu">
                        <button class="text-white p-2 rounded hover:bg-accent-light/30 focus:outline-none cursor-pointer">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <!-- Dropdown menu that shows on hover -->
                        <div class="absolute left-0 top-full mt-1 w-64 bg-white dark:bg-[#252525] rounded shadow-lg hidden z-50 pb-2" id="hamburger-dropdown" style="max-height: 80vh; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;">
                            <!-- Add CSS to hide the scrollbar but keep scrolling functionality -->
                            <style>
                                #hamburger-dropdown::-webkit-scrollbar {
                                    display: none;
                                }
                                /* Add a buffer area to make it easier to hover into menu */
                                #hamburger-menu::before {
                                    content: '';
                                    position: absolute;
                                    height: 20px;
                                    width: 100%;
                                    bottom: -10px;
                                    left: 0;
                                }
                                /* Add a buffer area for easier hover from button to dropdown */
                                #hamburger-dropdown::before {
                                    content: '';
                                    position: absolute;
                                    height: 20px;
                                    width: 100%;
                                    top: -10px;
                                    left: 0;
                                }
                            </style>
                            <div class="p-4 text-gray-800 dark:text-white space-y-3">
                                <a href="<?php echo $base_url; ?>index.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-home w-5 text-center"></i> <span>Home</span>
                                </a>
                                <a href="<?php echo $base_url; ?>search.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-search w-5 text-center"></i> <span>Donor Search</span>
                                </a>
                                <a href="<?php echo $base_url; ?>camps.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-tint w-5 text-center"></i> <span>Blood Camps</span>
                                </a>
                                <a href="<?php echo $base_url; ?>request.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-hand-holding-medical w-5 text-center"></i> <span>Request Blood</span>
                                </a>
                                <a href="<?php echo $base_url; ?>share.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-share-alt w-5 text-center"></i> <span>Share Benefits</span>
                                </a>
                                <a href="<?php echo $base_url; ?>chatbot/index.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-robot w-5 text-center"></i> <span>Ask Assistant</span>
                                </a>
                                <a href="<?php echo $base_url; ?>chatbot/eligibility.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-clipboard-check w-5 text-center"></i> <span>Eligibility Check</span>
                                </a>
                                <a href="<?php echo $base_url; ?>games/index.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-gamepad w-5 text-center"></i> <span>Blood Donation Games</span>
                                </a>
                                <?php if(isset($_SESSION['donor_id'])): ?>
                                <a href="<?php echo $base_url; ?>dashboard/donor.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
                                    <i class="fas fa-user w-5 text-center"></i> <span>Dashboard</span>
                                </a>
                                <a href="<?php echo $base_url; ?>dashboard/analytics.php" class="block px-3 py-3 rounded hover:bg-primary-light/20 dark:hover:bg-primary-dark/30 transition flex items-center gap-2">
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
                    <a href="<?php echo $base_url; ?>index.php" class="px-2 py-2 rounded hover:bg-primary-light/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="<?php echo $base_url; ?>search.php" class="px-2 py-2 rounded hover:bg-primary-light/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-search"></i> Donor Search
                    </a>
                    <a href="<?php echo $base_url; ?>request.php" class="px-2 py-2 rounded hover:bg-primary-light/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-hand-holding-medical"></i> Request
                    </a>
                     <a href="<?php echo $base_url; ?>share.php" class="px-2 py-2 rounded hover:bg-primary-light/30 hover:text-white transition font-medium flex items-center gap-1">
                        <i class="fas fa-share-alt"></i> Share
                    </a>
                    <?php if(isset($_SESSION['donor_id'])): ?>
                        <a href="<?php echo $base_url; ?>dashboard/donor.php" class="px-2 py-2 rounded hover:bg-primary-light/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                        <a href="<?php echo $base_url; ?>logout.php" class="px-2 py-2 rounded hover:bg-accent-light/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="px-2 py-2 rounded hover:bg-accent-light/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo $base_url; ?>register.php" class="px-2 py-2 rounded hover:bg-accent-light/30 hover:text-white transition font-medium flex items-center gap-1">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                    <!-- Dark mode toggle button -->
                    <button id="darkModeToggle" class="text-white p-2 rounded-full hover:bg-primary-light/30 dark:hover:bg-primary-dark focus:outline-none ml-2">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>
                </div>
                <div class="md:hidden flex items-center">
                    <!-- Dark mode toggle for mobile -->
                    <button id="darkModeToggleMobile" class="text-white p-2 rounded-full hover:bg-primary-light/30 dark:hover:bg-primary-dark focus:outline-none mr-2">
                        <i id="darkModeIconMobile" class="fas fa-moon"></i>
                    </button>
                    <!-- Mobile menu button -->
                    <div class="relative">
                        <button id="mobile-menu-button" class="text-white focus:outline-none cursor-pointer hover:bg-primary-light/30 p-2 rounded">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu dropdown - positioned outside the flex container for better visibility -->
        <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-[#1E1E1E] shadow-lg rounded-b-lg overflow-hidden z-50">
            <div class="py-2 px-2">
                <a href="<?php echo $base_url; ?>index.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-home w-5 text-center mr-2"></i> <span>Home</span>
                </a>
                <a href="<?php echo $base_url; ?>search.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-search w-5 text-center mr-2"></i> <span>Donor Search</span>
                </a>
                <a href="<?php echo $base_url; ?>camps.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-tint w-5 text-center mr-2"></i> <span>Blood Camps</span>
                </a>
                <a href="<?php echo $base_url; ?>request.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-hand-holding-medical w-5 text-center mr-2"></i> <span>Request Blood</span>
                </a>
                <a href="<?php echo $base_url; ?>share.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-share-alt w-5 text-center mr-2"></i> <span>Share Benefits</span>
                </a>
                <a href="<?php echo $base_url; ?>chatbot/index.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-robot w-5 text-center mr-2"></i> <span>Ask Assistant</span>
                </a>
                <a href="<?php echo $base_url; ?>chatbot/eligibility.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-clipboard-check w-5 text-center mr-2"></i> <span>Eligibility Check</span>
                </a>
                <a href="<?php echo $base_url; ?>games/index.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                    <i class="fas fa-gamepad w-5 text-center mr-2"></i> <span>Blood Donation Games</span>
                </a>
                <?php if(isset($_SESSION['donor_id'])): ?>
                    <a href="<?php echo $base_url; ?>dashboard/donor.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                        <i class="fas fa-user w-5 text-center mr-2"></i> <span>My Dashboard</span>
                    </a>
                    <a href="<?php echo $base_url; ?>dashboard/analytics.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-primary-light/20 rounded mb-1 flex items-center">
                        <i class="fas fa-chart-bar w-5 text-center mr-2"></i> <span>Analytics</span>
                    </a>
                    <a href="<?php echo $base_url; ?>logout.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-accent-light/20 rounded mb-1 flex items-center">
                        <i class="fas fa-sign-out-alt w-5 text-center mr-2"></i> <span>Logout</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>login.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-accent-light/20 rounded mb-1 flex items-center">
                        <i class="fas fa-sign-in-alt w-5 text-center mr-2"></i> <span>Login</span>
                    </a>
                    <a href="<?php echo $base_url; ?>register.php" class="block py-2 px-4 text-gray-800 dark:text-white hover:bg-accent-light/20 rounded mb-1 flex items-center">
                        <i class="fas fa-user-plus w-5 text-center mr-2"></i> <span>Register</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="container mx-auto px-4 py-6"><?php // Main content starts here ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeToggleMobile = document.getElementById('darkModeToggleMobile');
        
        // Dark mode toggle functionality
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', toggleDarkMode);
        }
        
        if (darkModeToggleMobile) {
            darkModeToggleMobile.addEventListener('click', toggleDarkMode);
        }
        
        // Mobile menu toggle functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
        
        // Hamburger menu hover functionality for desktop
        const hamburgerMenu = document.getElementById('hamburger-menu');
        const hamburgerDropdown = document.getElementById('hamburger-dropdown');
        
        if (hamburgerMenu && hamburgerDropdown) {
            hamburgerMenu.addEventListener('mouseenter', function() {
                hamburgerDropdown.classList.remove('hidden');
            });
            
            hamburgerMenu.addEventListener('mouseleave', function() {
                // Add a delay before closing to improve hover experience
                setTimeout(function() {
                    if (!hamburgerMenu.matches(':hover') && !hamburgerDropdown.matches(':hover')) {
                        hamburgerDropdown.classList.add('hidden');
                    }
                }, 200); // 200ms delay
            });
            
            // Also handle click for accessibility
            const hamburgerButton = hamburgerMenu.querySelector('button');
            if (hamburgerButton) {
                hamburgerButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    hamburgerDropdown.classList.toggle('hidden');
                });
            }
        }
        
        // Add touch support for mobile hover menus
        if ('ontouchstart' in window) {
            // For desktop hamburger menu
            const hamburgerButton = document.querySelector('#hamburger-menu button');
            if (hamburgerButton) {
                hamburgerButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('#hamburger-dropdown').classList.toggle('block');
                });
            }
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                // Check if click is outside the mobile menu and the button
                if (!mobileMenu.contains(event.target) && 
                    mobileMenuButton && !mobileMenuButton.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
        
        // Add tracking for games section links from hamburger menu
        const gamesLinkDesktop = hamburgerDropdown ? hamburgerDropdown.querySelector('a[href*="games/index.php"]') : null;
        const gamesLinkMobile = mobileMenu ? mobileMenu.querySelector('a[href*="games/index.php"]') : null;
        
        // Function to set flag when clicking on games section from menu
        function setGamesLoadingFlag() {
            sessionStorage.setItem('fromHamburgerMenu', 'true');
        }
        
        // Add event listeners to games section links
        if (gamesLinkDesktop) {
            gamesLinkDesktop.addEventListener('click', setGamesLoadingFlag);
        }
        
        if (gamesLinkMobile) {
            gamesLinkMobile.addEventListener('click', setGamesLoadingFlag);
        }
    });
</script>
