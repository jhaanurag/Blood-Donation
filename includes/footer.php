    </main>
    <!-- Footer -->
    <footer class="bg-red-600 text-white py-6 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-3">LifeFlow</h3>
                    <p class="max-w-xs">Connecting blood donors with those in need. Save lives through the gift of blood donation.</p>
                </div>
                <div class="mb-6 md:mb-0">
                    <h4 class="font-semibold mb-3">Quick Links</h4>
                    <ul>
                        <li class="mb-2"><a href="index.php" class="hover:text-red-200">Home</a></li>
                        <li class="mb-2"><a href="search.php" class="hover:text-red-200">Search Donors</a></li>
                        <li class="mb-2"><a href="camps.php" class="hover:text-red-200">Blood Camps</a></li>
                        <li class="mb-2"><a href="request.php" class="hover:text-red-200">Request Blood</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Contact</h4>
                    <ul>
                        <li class="mb-2"><i class="fas fa-envelope mr-2"></i> contact@lifeflow.org</li>
                        <li class="mb-2"><i class="fas fa-phone mr-2"></i> +1-800-BLOOD-HELP</li>
                    </ul>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="hover:text-red-200"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="hover:text-red-200"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-red-200"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-red-500 mt-6 pt-6 text-center">
                <p>&copy; <?php echo date('Y'); ?> Blood Donation Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>