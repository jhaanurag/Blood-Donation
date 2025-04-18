</main>
    <!-- Footer -->
    <footer class="bg-red-600 dark:bg-red-900 text-white py-6 mt-12 transition-colors duration-200">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-3">LifeFlow</h3>
                    <p class="max-w-xs">Connecting blood donors with those in need. Save lives through the gift of blood donation.</p>
                </div>
                <div class="mb-6 md:mb-0">
                    <h4 class="font-semibold mb-3">Quick Links</h4>
                    <ul>
                        <li class="mb-2"><a href="index.php" class="hover:text-red-200 transition">Home</a></li>
                        <li class="mb-2"><a href="search.php" class="hover:text-red-200 transition">Search Donors</a></li>
                        <li class="mb-2"><a href="camps.php" class="hover:text-red-200 transition">Blood Camps</a></li>
                        <li class="mb-2"><a href="request.php" class="hover:text-red-200 transition">Request Blood</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Contact</h4>
                    <ul>
                        <li class="mb-2"><i class="fas fa-envelope mr-2"></i> contact@lifeflow.org</li>
                        <li class="mb-2"><i class="fas fa-phone mr-2"></i> +1-800-BLOOD-HELP</li>
                    </ul>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="hover:text-red-200 transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="hover:text-red-200 transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-red-200 transition"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-red-500 dark:border-red-800 mt-6 pt-6 text-center">
                <p>&copy; <?php echo date('Y'); ?> Blood Donation Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <div id="mouse-follower"></div> <!-- Add the blob element -->

    <script>
        // Dark Mode Toggle Functionality
        function initializeDarkMode() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkModeToggleMobile = document.getElementById('darkModeToggleMobile');
            const darkModeIcon = document.getElementById('darkModeIcon');
            const darkModeIconMobile = document.getElementById('darkModeIconMobile');
            const htmlElement = document.documentElement;
            
            // Function to set the dark mode state
            function setDarkMode(isDark) {
                if (isDark) {
                    htmlElement.classList.add('dark');
                    darkModeIcon?.classList.replace('fa-moon', 'fa-sun');
                    darkModeIconMobile?.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    htmlElement.classList.remove('dark');
                    darkModeIcon?.classList.replace('fa-sun', 'fa-moon');
                    darkModeIconMobile?.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('darkMode', 'disabled');
                }
            }
            
            // Check for saved user preference
            const savedDarkMode = localStorage.getItem('darkMode');
            
            // Set the initial state based on saved preference or system preference
            if (savedDarkMode === 'enabled') {
                setDarkMode(true);
            } else if (savedDarkMode === 'disabled') {
                setDarkMode(false);
            } else {
                // If no saved preference, check system preference
                const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                setDarkMode(prefersDarkMode);
            }
            
            // Add event listeners to toggle buttons
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', () => {
                    const isDarkMode = htmlElement.classList.contains('dark');
                    setDarkMode(!isDarkMode);
                });
            }
            
            if (darkModeToggleMobile) {
                darkModeToggleMobile.addEventListener('click', () => {
                    const isDarkMode = htmlElement.classList.contains('dark');
                    setDarkMode(!isDarkMode);
                });
            }
            
            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (localStorage.getItem('darkMode') === null) {
                    // Only auto-switch if the user hasn't set a preference
                    setDarkMode(e.matches);
                }
            });
        }
        
        // Initialize dark mode functionality
        initializeDarkMode();

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

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