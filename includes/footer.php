</main>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-800 py-8 mt-12 border-t border-gray-200 dark:border-gray-700 transition-colors duration-200">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4 text-red-600 dark:text-red-500">LifeFlow Blood Donation</h3>
                    <p class="text-gray-700 dark:text-gray-300">Connecting donors with those in need. Every donation counts and can save up to three lives.</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4 text-red-600 dark:text-red-500">Quick Links</h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <li><a href="<?php echo $base_url; ?>index.php" class="hover:text-red-600 dark:hover:text-red-500 transition">Home</a></li>
                        <li><a href="<?php echo $base_url; ?>search.php" class="hover:text-red-600 dark:hover:text-red-500 transition">Find Donors</a></li>
                        <li><a href="<?php echo $base_url; ?>request.php" class="hover:text-red-600 dark:hover:text-red-500 transition">Request Blood</a></li>
                        <li><a href="<?php echo $base_url; ?>camps.php" class="hover:text-red-600 dark:hover:text-red-500 transition">Blood Camps</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4 text-red-600 dark:text-red-500">Contact</h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <li><i class="fas fa-envelope mr-2 text-red-600 dark:text-red-500"></i> info@lifeflow.org</li>
                        <li><i class="fas fa-phone mr-2 text-red-600 dark:text-red-500"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt mr-2 text-red-600 dark:text-red-500"></i> 123 Health Street, Medical City</li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center text-gray-600 dark:text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> LifeFlow Blood Donation. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <div id="mouse-follower"></div> <!-- Add the blob element -->

    <?php
    // Include the floating chatbot at the very end of the body to ensure it's always on top
    include_once INCLUDES_PATH . 'components/floating_chatbot.php';
    ?>

    <!-- Chart.js for Analytics Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Optional - Chart.js plugin for animations and advanced features -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        // Dark mode toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggles = [document.getElementById('darkModeToggle'), document.getElementById('darkModeToggleMobile')];
            const darkModeIcons = [document.getElementById('darkModeIcon'), document.getElementById('darkModeIconMobile')];
            
            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme') || 
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            
            // Apply the saved theme
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                darkModeIcons.forEach(icon => {
                    if (icon) icon.classList.replace('fa-moon', 'fa-sun');
                });
            } else {
                document.documentElement.classList.remove('dark');
                darkModeIcons.forEach(icon => {
                    if (icon) icon.classList.replace('fa-sun', 'fa-moon');
                });
            }
            
            // Toggle theme when buttons are clicked
            darkModeToggles.forEach((toggle, index) => {
                if (toggle) toggle.addEventListener('click', function() {
                    document.documentElement.classList.toggle('dark');
                    
                    if (document.documentElement.classList.contains('dark')) {
                        localStorage.setItem('theme', 'dark');
                        if (darkModeIcons[index]) darkModeIcons[index].classList.replace('fa-moon', 'fa-sun');
                    } else {
                        localStorage.setItem('theme', 'light');
                        if (darkModeIcons[index]) darkModeIcons[index].classList.replace('fa-sun', 'fa-moon');
                    }
                });
            });
            
            // Mobile menu functionality
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Hamburger menu hover behavior
            const hamburgerMenu = document.getElementById('hamburger-menu');
            const hamburgerDropdown = document.getElementById('hamburger-dropdown');
            
            if (hamburgerMenu && hamburgerDropdown) {
                let timeoutId;
                
                // Show dropdown on hover
                hamburgerMenu.addEventListener('mouseenter', function() {
                    clearTimeout(timeoutId);
                    hamburgerDropdown.classList.remove('hidden');
                });
                
                // Allow moving to dropdown items by keeping menu open
                hamburgerDropdown.addEventListener('mouseenter', function() {
                    clearTimeout(timeoutId);
                });
                
                // Hide dropdown after delay when mouse leaves both menu and dropdown
                hamburgerMenu.addEventListener('mouseleave', function(event) {
                    // Check if we're not moving to the dropdown
                    if (!event.relatedTarget || !hamburgerDropdown.contains(event.relatedTarget)) {
                        timeoutId = setTimeout(() => {
                            // Only hide if mouse isn't over dropdown
                            if (!hamburgerDropdown.matches(':hover')) {
                                hamburgerDropdown.classList.add('hidden');
                            }
                        }, 300); // Small delay to allow mouse movement to dropdown
                    }
                });
                
                hamburgerDropdown.addEventListener('mouseleave', function() {
                    timeoutId = setTimeout(() => {
                        // Only hide if mouse isn't over hamburger button
                        if (!hamburgerMenu.querySelector('button').matches(':hover')) {
                            hamburgerDropdown.classList.add('hidden');
                        }
                    }, 300);
                });
                
                // Also handle click for mobile devices
                hamburgerMenu.querySelector('button').addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent document click from immediately closing it
                    hamburgerDropdown.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!hamburgerMenu.contains(e.target) && !hamburgerDropdown.contains(e.target)) {
                        hamburgerDropdown.classList.add('hidden');
                    }
                });
            }
        });

        // Mouse follower logic
        const follower = document.getElementById('mouse-follower');
        if (follower) {
            document.addEventListener('mousemove', (e) => {
                requestAnimationFrame(() => {
                    // Use pageX/pageY for correct positioning during scroll
                    follower.style.left = `${e.pageX}px`;
                    follower.style.top = `${e.pageY}px`;
                    follower.style.opacity = '1';
                });
            });

            document.addEventListener('mouseleave', () => {
                follower.style.opacity = '0';
            });

            document.addEventListener('mouseenter', () => {
                follower.style.opacity = '1';
            });
        }
    </script>
</body>
</html>