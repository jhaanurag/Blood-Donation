<?php
include_once '../includes/header.php';
include_once '../includes/db.php';

// Check if the user is logged in
$user_logged_in = isset($_SESSION['donor_id']);
$user_name = $user_logged_in ? $_SESSION['donor_name'] : '';
$user_id = $user_logged_in ? $_SESSION['donor_id'] : '';
?>

<div class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-900 dark:text-white">Blood Donation Games</h1>
        <p class="text-center text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
            Have fun while learning more about blood donation! Play our educational games and earn badges as you improve your knowledge.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
            <!-- Blood Facts Challenge Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="relative">
                    <div class="bg-red-600 h-48 flex items-center justify-center">
                        <i class="fas fa-heartbeat text-white text-6xl"></i>
                    </div>
                    <div class="absolute top-4 right-4 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full">
                        Quiz Game
                    </div>
                </div>
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">Blood Facts Challenge</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Test your knowledge about blood donation with this interactive quiz. Answer questions correctly to make the heart beat faster!
                    </p>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Played by <?php
                            // Count unique players
                            $query = "SELECT COUNT(DISTINCT user_id) as player_count FROM game_scores WHERE game_type = 'blood_facts_challenge'";
                            $result = $conn->query($query);
                            if ($result && $row = $result->fetch_assoc()) {
                                echo number_format($row['player_count']);
                            } else {
                                echo "0";
                            }
                        ?> donors</span>
                    </div>
                    <a href="blood_facts_challenge.php" class="inline-block w-full bg-red-600 text-white font-bold py-3 px-4 rounded-lg text-center">
                        Play Now
                    </a>
                </div>
            </div>
            
            <!-- Word Guess Game Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="relative">
                    <div class="bg-purple-600 h-48 flex items-center justify-center">
                        <i class="fas fa-font text-white text-6xl"></i>
                    </div>
                    <div class="absolute top-4 right-4 bg-green-400 text-green-900 text-xs font-bold px-3 py-1 rounded-full">
                        Word Game
                    </div>
                </div>
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">Blood Word Guess</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Test your vocabulary by guessing blood donation related words from clues. Learn new terms while having fun!
                    </p>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Played by <?php
                            // Count unique players
                            $query = "SELECT COUNT(DISTINCT user_id) as player_count FROM game_scores WHERE game_type = 'blood_word_guess'";
                            $result = $conn->query($query);
                            if ($result && $row = $result->fetch_assoc()) {
                                echo number_format($row['player_count']);
                            } else {
                                echo "0";
                            }
                        ?> donors</span>
                    </div>
                    <a href="word_guess.php" class="inline-block w-full bg-purple-600 text-white font-bold py-3 px-4 rounded-lg text-center">
                        Play Now
                    </a>
                </div>
            </div>

            <!-- Blood Cell Defenders Game Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="relative">
                    <div class="bg-blue-600 h-48 flex items-center justify-center">
                        <i class="fas fa-shield-virus text-white text-6xl"></i>
                    </div>
                    <div class="absolute top-4 right-4 bg-blue-400 text-blue-900 text-xs font-bold px-3 py-1 rounded-full">
                        Tower Defense
                    </div>
                </div>
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">Blood Cell Defenders</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Protect the bloodstream by placing "defender cells" to stop infections. Upgrade your defenders and learn about blood components while playing!
                    </p>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Played by <?php
                            // Count unique players
                            $query = "SELECT COUNT(DISTINCT user_id) as player_count FROM game_scores WHERE game_type = 'blood_cell_defenders'";
                            $result = $conn->query($query);
                            if ($result && $row = $result->fetch_assoc()) {
                                echo number_format($row['player_count']);
                            } else {
                                echo "0";
                            }
                        ?> donors</span>
                    </div>
                    <a href="blood_cell_defenders.php" class="inline-block w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg text-center">
                        Play Now
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (!$user_logged_in): ?>
        <div class="mt-12 max-w-2xl mx-auto bg-blue-50 dark:bg-blue-900 p-6 rounded-lg text-center">
            <p class="text-blue-800 dark:text-blue-200 mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                Log in to save your scores and earn badges!
            </p>
            <a href="<?php echo $base_url; ?>login.php" class="inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded-lg">
                Log In Now
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>