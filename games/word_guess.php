<?php
include_once '../includes/header.php';
include_once '../includes/db.php';

// Check if the user is logged in
$user_logged_in = isset($_SESSION['donor_id']);
$user_name = $user_logged_in ? $_SESSION['donor_name'] : '';
$user_id = $user_logged_in ? $_SESSION['donor_id'] : '';
?>

<style>
/* Fix for navigation bar buttons - MODIFIED to ensure proper display of all menu items */
nav .hidden.md\:flex {
    display: flex !important;
}
nav .md\:hidden {
    display: none !important;
}
nav .md\:block {
    display: block !important;
}
/* Ensure dropdown menus appear properly - MODIFIED to not force display: block */
nav #hamburger-dropdown {
    /* Remove the forced display property */
    z-index: 50;
}
nav #mobile-menu:not(.hidden) {
    display: block !important;
}

/* Game specific styles */
.game-container {
    max-width: 800px;
    margin: 0 auto;
}

.word-container {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.letter-box {
    width: 50px;
    height: 50px;
    border: 2px solid #e2e8f0;
    margin: 0 5px 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    border-radius: 0.25rem;
    background-color: #f7fafc;
    transition: all 0.3s ease;
}

.dark .letter-box {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}

.letter-box.revealed {
    background-color: #c6f6d5;
    border-color: #9ae6b4;
    color: #276749;
}

.dark .letter-box.revealed {
    background-color: #276749;
    border-color: #9ae6b4;
    color: #f0fff4;
}

.letter-box.space {
    border: none;
    background-color: transparent;
}

.clue-container {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #ebf8ff;
    border-radius: 0.5rem;
    border-left: 4px solid #4299e1;
}

.dark .clue-container {
    background-color: #2c5282;
    border-left-color: #4299e1;
    color: #e2e8f0;
}

.keyboard-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 2rem;
    gap: 0.5rem;
}

.key {
    min-width: 2.5rem;
    height: 3rem;
    background-color: #f7fafc;
    border: 2px solid #e2e8f0;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.dark .key {
    background-color: #4a5568;
    border-color: #2d3748;
    color: #e2e8f0;
}

.key:hover {
    background-color: #edf2f7;
    border-color: #cbd5e0;
}

.dark .key:hover {
    background-color: #2d3748;
    border-color: #4a5568;
}

.key.used {
    opacity: 0.5;
    cursor: not-allowed;
}

.key.correct {
    background-color: #c6f6d5;
    border-color: #9ae6b4;
    color: #276749;
}

.dark .key.correct {
    background-color: #276749;
    border-color: #9ae6b4;
    color: #f0fff4;
}

.key.incorrect {
    background-color: #fed7d7;
    border-color: #feb2b2;
    color: #c53030;
}

.dark .key.incorrect {
    background-color: #c53030;
    border-color: #feb2b2;
    color: #fff5f5;
}

.score-display {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 1rem;
    color: #805ad5;
}

.dark .score-display {
    color: #9f7aea;
}

.blood-drop-container {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.blood-drop {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 2rem;
    background-color: #e53e3e;
    border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
    transform: rotate(45deg);
    box-shadow: inset -10px -10px 20px rgba(0, 0, 0, 0.2);
    transition: all 0.5s ease;
}

.progress-container {
    width: 100%;
    height: 8px;
    background-color: #e2e8f0;
    border-radius: 4px;
    margin-bottom: 2rem;
    overflow: hidden;
}

.dark .progress-container {
    background-color: #4a5568;
}

.progress-bar
    height: 100%;
    background-color: #805ad5;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.leaderboard {
    margin-top: 2rem;
    padding: 1rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dark .leaderboard {
    background-color: #2d3748;
}

.leaderboard-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.dark .leaderboard-item {
    border-bottom-color: #4a5568;
}

.leaderboard-item:last-child {
    border-bottom: none;
}

.rank {
    font-weight: bold;
    width: 2rem;
}

.name {
    flex-grow: 1;
    margin: 0 1rem;
}

.score {
    font-weight: bold;
    color: #805ad5;
}

.dark .score {
    color: #9f7aea;
}

.sharing-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 1.5rem 0;
}

.sharing-buttons button {
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    cursor: pointer;
    transition: transform 0.2s, opacity 0.2s;
}

.sharing-buttons button:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

.sharing-buttons i {
    margin-right: 0.5rem;
}

.facebook {
    background-color: #3b5998;
    color: white;
}

.twitter {
    background-color: #1da1f2;
    color: white;
}

.whatsapp {
    background-color: #25d366;
    color: white;
}

.word-definition {
    background-color: #f7fafc;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
    font-style: italic;
    border-left: 4px solid #805ad5;
}

.dark .word-definition {
    background-color: #2d3748;
    color: #e2e8f0;
}

.fade-in {
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.confetti-container {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: 999;
}

.lives-container {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
    gap: 0.5rem;
}

.life {
    color: #e53e3e;
    font-size: 1.5rem;
}

.life.lost {
    opacity: 0.3;
}

.hint-button {
    background-color: #f7fafc;
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dark .hint-button {
    background-color: #4a5568;
    border-color: #2d3748;
    color: #e2e8f0;
}

.hint-button:hover {
    background-color: #edf2f7;
    border-color: #cbd5e0;
}

.dark .hint-button:hover {
    background-color: #2d3748;
    border-color: #4a5568;
}

.hint-button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hidden {
    display: none;
}

/* Badge notification animation */
@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

.animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
}

.animate__fadeInRight {
    animation-name: fadeInRight;
}

.animate__fadeOutRight {
    animation-name: fadeOutRight;
}
</style>

<!-- <div class="py-8"> -->
    <!-- <h1 class="text-3xl font-bold text-center mb-8 text-gray-900 dark:text-white">Blood Word Guess</h1>
    <p class="text-center text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">Test your knowledge of blood donation terminology! Guess the words based on the clues provided. Can you solve them all?</p> -->
    
    <div class="game-container">
        <!-- Start Screen -->
        <div id="start-screen" class="text-center">
            <div class="mb-6 bg-purple-100 dark:bg-purple-900 rounded-lg shadow-lg p-8 transform transition-all duration-500 hover:scale-105">
                <h2 class="text-3xl font-bold mb-6 dark:text-white text-purple-800 dark:text-purple-200">Blood Word Challenge</h2>
                <p class="mb-8 text-gray-600 dark:text-gray-300 text-lg">Test your knowledge of blood donation terminology! Guess as many words as you can with 5 lives.</p>
                <div class="flex justify-center mb-8">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="letter-box revealed">B</div>
                        <div class="letter-box revealed">L</div>
                        <div class="letter-box revealed">O</div>
                        <div class="letter-box">?</div>
                        <div class="letter-box">?</div>
                        <div class="letter-box">?</div>
                    </div>
                </div>
                <button id="start-button" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg transition shadow-md transform hover:scale-105 hover:shadow-xl">
                    <i class="fas fa-play-circle mr-2"></i> Start Challenge
                </button>
            </div>
        </div>

        <!-- Game Screen -->
        <div id="game-screen" class="hidden">
            <div class="mb-6 flex justify-between items-center">
                <div class="score-display"><i class="fas fa-star text-yellow-500 mr-2"></i> <span id="score">0</span></div>
                <div class="lives-container">
                    <span id="life-1" class="life"><i class="fas fa-heart"></i></span>
                    <span id="life-2" class="life"><i class="fas fa-heart"></i></span>
                    <span id="life-3" class="life"><i class="fas fa-heart"></i></span>
                    <span id="life-4" class="life"><i class="fas fa-heart"></i></span>
                    <span id="life-5" class="life"><i class="fas fa-heart"></i></span>
                </div>
            </div>
            
            <div class="progress-container">
                <div id="progress-bar" class="progress-bar" style="width: 0%;"></div>
            </div>
            
            <div class="clue-container mb-6 transform transition-all duration-300 hover:shadow-lg">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                <p id="clue-text" class="text-lg dark:text-white">The clue will appear here</p>
            </div>
            
            <div id="word-difficulty" class="text-sm text-right mb-2 text-gray-600 dark:text-gray-400">
                <span id="difficulty-badge" class="px-2 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    <i class="fas fa-circle"></i> Easy
                </span>
            </div>
            
            <div id="word-container" class="word-container mb-8 animate__animated animate__fadeIn"></div>

            <div id="word-definition" class="word-definition hidden animate__animated animate__fadeIn">
                <p id="definition-text"></p>
            </div>
            
            <div id="keyboard-container" class="keyboard-container mb-8"></div>
            
            <div class="flex justify-center gap-3">
                <button id="hint-button" class="hint-button">
                    <i class="fas fa-lightbulb mr-2"></i> Get Hint 
                    <span class="text-xs ml-1">(<span id="hints-remaining">3</span> left)</span>
                </button>
                <button id="skip-button" class="bg-gray-400 hover:bg-gray-500 text-white py-2 px-4 rounded-lg transition">
                    <i class="fas fa-forward mr-2"></i> Skip Word
                </button>
            </div>
            
            <div id="timer-container" class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Time Remaining</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                    <div id="timer-bar" class="bg-blue-500 h-full transition-all duration-1000" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <!-- Results Screen -->
        <div id="results-screen" class="hidden text-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 mb-8 transform transition-all duration-500">
                <div class="inline-block p-4 rounded-full bg-purple-100 dark:bg-purple-900 mb-4">
                    <i class="fas fa-trophy text-4xl text-purple-600 dark:text-purple-300"></i>
                </div>
                <h2 class="text-3xl font-bold mb-4 dark:text-white">Challenge Complete!</h2>
                <p class="mb-2 text-lg dark:text-white">Your Score:</p>
                <div class="score-display text-5xl mb-6 text-purple-700 dark:text-purple-300"><span id="final-score">0</span></div>
                <div id="stats-container" class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Words Guessed</p>
                        <p id="words-guessed" class="text-xl font-bold">0</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Hints Used</p>
                        <p id="total-hints" class="text-xl font-bold">0</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Accuracy</p>
                        <p id="accuracy-stat" class="text-xl font-bold">0%</p>
                    </div>
                </div>
                <p id="score-message" class="mb-6 text-gray-700 dark:text-gray-300">Great job! You know your blood donation terminology!</p>
                
                <?php if($user_logged_in): ?>
                <div class="mb-6">
                    <button id="save-score-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Score to Leaderboard
                    </button>
                </div>
                <?php else: ?>
                <div class="mb-6 text-gray-700 dark:text-gray-300">
                    <p><a href="<?php echo $base_url; ?>login.php" class="text-purple-600 dark:text-purple-400 underline">Log in</a> to save your score to the leaderboard!</p>
                </div>
                <?php endif; ?>
                
                <div class="sharing-buttons">
                    <button class="facebook" onclick="shareScore('facebook')">
                        <i class="fab fa-facebook-f"></i> Share
                    </button>
                    <button class="twitter" onclick="shareScore('twitter')">
                        <i class="fab fa-twitter"></i> Tweet
                    </button>
                    <button class="whatsapp" onclick="shareScore('whatsapp')">
                        <i class="fab fa-whatsapp"></i> Share
                    </button>
                </div>
                
                <div class="mt-8">
                    <button id="play-again-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-md">
                        <i class="fas fa-redo mr-2"></i> Play Again
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Leaderboard Section -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6 text-center dark:text-white">Leaderboard</h2>
            <div class="leaderboard">
                <div class="leaderboard-item bg-gray-100 dark:bg-gray-700">
                    <div class="rank">#</div>
                    <div class="name font-semibold">Name</div>
                    <div class="score">Score</div>
                </div>
                <div id="leaderboard-list">
                    <!-- Leaderboard items will be inserted here -->
                    <div class="leaderboard-item animate-pulse">
                        <div class="rank">1</div>
                        <div class="name">Loading...</div>
                        <div class="score">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple confetti effect container -->
<div id="confetti-container" class="confetti-container"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const startScreen = document.getElementById('start-screen');
    const gameScreen = document.getElementById('game-screen');
    const resultsScreen = document.getElementById('results-screen');
    const startButton = document.getElementById('start-button');
    const playAgainBtn = document.getElementById('play-again-btn');
    const saveScoreBtn = document.getElementById('save-score-btn');
    const scoreDisplay = document.getElementById('score');
    const finalScoreDisplay = document.getElementById('final-score');
    const progressBar = document.getElementById('progress-bar');
    const clueText = document.getElementById('clue-text');
    const wordContainer = document.getElementById('word-container');
    const keyboardContainer = document.getElementById('keyboard-container');
    const wordDefinition = document.getElementById('word-definition');
    const definitionText = document.getElementById('definition-text');
    const hintButton = document.getElementById('hint-button');
    const scoreMessage = document.getElementById('score-message');
    const leaderboardList = document.getElementById('leaderboard-list');
    const confettiContainer = document.getElementById('confetti-container');
    const timerBar = document.getElementById('timer-bar');
    const skipButton = document.getElementById('skip-button');
    
    // Game State
    let currentWordIndex = 0;
    let score = 0;
    let lives = 5;
    let wordsData = [];
    let currentWord = '';
    let revealedLetters = [];
    let usedLetters = [];
    let hintsUsed = 0;
    let userLoggedIn = <?php echo $user_logged_in ? 'true' : 'false'; ?>;
    let userName = "<?php echo $user_name; ?>";
    let userId = "<?php echo $user_id; ?>";
    
    // Timer variables
    let wordTimer = null;
    let timerDuration = 60; // seconds
    let timerRemaining = 0;
    let wordsGuessed = 0;
    let wordsFailed = 0;
    
    // Add difficulty levels based on word length
    const difficultyLevels = {
        easy: { maxLength: 6, time: 60, color: 'green' },
        medium: { maxLength: 9, time: 45, color: 'yellow' },
        hard: { maxLength: 50, time: 30, color: 'red' }
    };
    
    // Blood Donation Word List with clues and definitions
    const bloodDonationWords = [
        {
            word: "PLASMA",
            clue: "The liquid component of blood that carries cells and proteins throughout the body",
            definition: "Plasma is the yellowish liquid component of blood that holds blood cells in suspension. It makes up about 55% of total blood volume and contains water, proteins, nutrients, hormones, and waste products."
        },
        {
            word: "PLATELETS",
            clue: "Blood cells that help your body form clots to stop bleeding",
            definition: "Platelets, or thrombocytes, are small, colorless cell fragments in our blood that form clots and stop or prevent bleeding. They circulate in the blood and are involved in hemostasis, leading to the formation of blood clots."
        },
        {
            word: "HEMOGLOBIN",
            clue: "The protein in red blood cells that carries oxygen throughout the body",
            definition: "Hemoglobin is an iron-containing protein in red blood cells that transports oxygen from the lungs to the body's tissues and returns carbon dioxide from the tissues to the lungs."
        },
        {
            word: "DONATION",
            clue: "The act of giving blood to help others",
            definition: "Blood donation is a voluntary procedure that can help save the lives of others. One donation can save multiple lives, and the components of blood can be used for different medical treatments."
        },
        {
            word: "TRANSFUSION",
            clue: "The process of transferring blood or blood products into a person's circulation",
            definition: "A blood transfusion is a routine medical procedure in which donated blood is provided through a narrow tube placed within a vein to a patient who requires additional blood to function normally."
        },
        {
            word: "PHLEBOTOMIST",
            clue: "A healthcare professional who draws blood from patients",
            definition: "A phlebotomist is a healthcare professional trained to draw blood from patients for medical testing, transfusions, donations, or research. They're skilled in venipuncture techniques."
        },
        {
            word: "ANEMIA",
            clue: "A condition where you don't have enough healthy red blood cells",
            definition: "Anemia is a condition in which you lack enough healthy red blood cells to carry adequate oxygen to your body's tissues. It can make you feel tired and weak, and can be caused by iron deficiency, blood loss, or medical conditions."
        },
        {
            word: "HEMOCHROMATOSIS",
            clue: "A disorder that causes your body to absorb too much iron from food",
            definition: "Hemochromatosis is a condition in which your body absorbs too much iron from the food you eat. The excess iron is stored in your organs, especially your liver, heart, and pancreas, which can lead to life-threatening conditions."
        },
        {
            word: "APHERESIS",
            clue: "A medical technology in which blood is drawn and separated, with one component being collected and the remainder returned to the donor",
            definition: "Apheresis is a medical procedure in which the blood of a person is passed through an apparatus that separates out one particular constituent and returns the remainder to the circulation."
        },
        {
            word: "HEMATOLOGY",
            clue: "The study of blood, blood-forming organs, and blood diseases",
            definition: "Hematology is the branch of medicine concerned with the study of the cause, diagnosis, treatment, and prevention of diseases related to blood and blood-forming organs."
        },
        {
            word: "BLOODMOBILE",
            clue: "A mobile blood collection center",
            definition: "A bloodmobile is a mobile blood donation center, often housed in a large vehicle like a bus or trailer, that travels to different locations to collect blood donations from volunteers."
        },
        {
            word: "UNIVERSAL",
            clue: "Type O negative blood donors are known as this type of donor",
            definition: "O negative is known as the 'universal donor' blood type because it can be transfused to almost any patient in need, regardless of their blood type. It's often used in emergency situations when there's no time to determine a patient's blood type."
        },
        {
            word: "FERRITIN",
            clue: "A blood protein that contains iron",
            definition: "Ferritin is a protein that stores iron and releases it in a controlled fashion. The amount of ferritin in the blood is directly related to the amount of iron stored in your body."
        },
        {
            word: "LEUKOCYTES",
            clue: "White blood cells, an important part of the immune system",
            definition: "Leukocytes, or white blood cells, are cells of the immune system that are involved in protecting the body against both infectious disease and foreign invaders."
        },
        {
            word: "ANTIBODIES",
            clue: "Proteins used by the immune system to identify and neutralize foreign objects in the blood",
            definition: "Antibodies, also known as immunoglobulins, are large, Y-shaped proteins that are produced by the immune system to identify and neutralize foreign substances like bacteria and viruses."
        }
    ];
    
    // Keyboard Layout
    const keyboardLayout = [
        ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
        ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
        ['Z', 'X', 'C', 'V', 'B', 'N', 'M']
    ];
    
    // Initialize the Game
    function initGame() {
        // Shuffle and get all the words
        wordsData = shuffleArray(bloodDonationWords);
        
        // Reset game state
        currentWordIndex = 0;
        score = 0;
        lives = 5;
        hintsUsed = 0;
        
        // Update UI
        scoreDisplay.textContent = score;
        progressBar.style.width = `${((currentWordIndex) / wordsData.length) * 100}%`;
        
        // Reset lives display
        updateLivesDisplay();
        
        // Load first word
        loadWord();
        
        // Reset hint button
        hintButton.classList.remove('disabled');
        
        // Fetch leaderboard data
        fetchLeaderboard();
    }
    
    // Load Word
    function loadWord() {
        if (currentWordIndex >= wordsData.length) {
            showResults();
            return;
        }
        
        const wordData = wordsData[currentWordIndex];
        currentWord = wordData.word;
        revealedLetters = Array(currentWord.length).fill(false);
        usedLetters = [];
        
        // Set clue
        clueText.textContent = wordData.clue;
        
        // Generate letter boxes
        wordContainer.innerHTML = '';
        for (let i = 0; i < currentWord.length; i++) {
            const letterBox = document.createElement('div');
            letterBox.className = 'letter-box';
            
            if (currentWord[i] === ' ') {
                letterBox.className += ' space';
                letterBox.textContent = ' ';
                revealedLetters[i] = true;
            }
            
            wordContainer.appendChild(letterBox);
        }
        
        // Generate keyboard
        generateKeyboard();
        
        // Hide word definition
        wordDefinition.classList.add('hidden');
        
        // Reset hint button
        if (hintsUsed < 3) {
            hintButton.classList.remove('disabled');
        } else {
            hintButton.classList.add('disabled');
        }
        
        // Set timer for the current word based on difficulty level
        setTimer(wordData.word.length);
    }
    
    // Generate Keyboard
    function generateKeyboard() {
        keyboardContainer.innerHTML = '';
        
        keyboardLayout.forEach(row => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'flex justify-center gap-1 mb-2';
            
            row.forEach(letter => {
                const key = document.createElement('button');
                key.className = 'key';
                key.textContent = letter;
                key.addEventListener('click', () => handleLetterClick(letter));
                rowDiv.appendChild(key);
            });
            
            keyboardContainer.appendChild(rowDiv);
        });
    }
    
    // Handle Letter Click
    function handleLetterClick(letter) {
        // Don't process if letter was already used
        if (usedLetters.includes(letter)) return;
        
        // Mark letter as used
        usedLetters.push(letter);
        
        // Update visual state of key on keyboard
        const letterKeys = document.querySelectorAll('.key');
        letterKeys.forEach(key => {
            if (key.textContent === letter) {
                key.classList.add('used');
            }
        });
        
        // Check if letter is in the word - this should be a deliberate user action
        let found = false;
        for (let i = 0; i < currentWord.length; i++) {
            // Only mark a letter as found if it matches the exact letter the user clicked
            if (currentWord[i] === letter) {
                found = true;
                revealedLetters[i] = true;
                
                // Update the letter box only after we know it's a match
                const letterBoxes = wordContainer.querySelectorAll('.letter-box');
                letterBoxes[i].textContent = letter;
                letterBoxes[i].classList.add('revealed');
            }
        }
        
        // Update key appearance - mark the key as correct or incorrect
        updateKeyAppearance(letter, found);
        
        // Check if word is complete
        if (isWordComplete()) {
            handleCorrectWord();
        } else if (!found) {
            // Wrong guess - lose a life
            lives--;
            updateLivesDisplay();
            
            if (lives <= 0) {
                revealWord();
                setTimeout(showResults, 2000);
            }
        }
    }
    
    // Update Key Appearance
    function updateKeyAppearance(letter, isCorrect) {
        const letterKeys = document.querySelectorAll('.key');
        letterKeys.forEach(key => {
            if (key.textContent === letter) {
                key.classList.add(isCorrect ? 'correct' : 'incorrect');
            }
        });
    }
    
    // Check if Word is Complete
    function isWordComplete() {
        return !revealedLetters.includes(false);
    }
    
    // Handle Correct Word
    function handleCorrectWord() {
        // Stop the timer
        clearInterval(wordTimer);
        
        // Track this success
        wordsGuessed++;
        
        // Increment score
        score++;
        scoreDisplay.textContent = score;
        
        // Show definition
        definitionText.textContent = wordsData[currentWordIndex].definition;
        wordDefinition.classList.remove('hidden');
        
        // Show success message
        const successMessage = document.createElement('div');
        successMessage.className = 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 p-3 rounded-md text-center animate__animated animate__fadeIn mb-4';
        successMessage.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Correct!';
        wordContainer.parentNode.insertBefore(successMessage, wordContainer);
        
        // Disable keyboard
        const keys = document.querySelectorAll('.key');
        keys.forEach(key => {
            key.classList.add('used');
        });
        
        // Load next word after delay
        setTimeout(() => {
            // Remove the success message
            if (successMessage.parentNode) {
                successMessage.remove();
            }
            
            currentWordIndex++;
            progressBar.style.width = `${((currentWordIndex) / wordsData.length) * 100}%`;
            loadWord();
        }, 3000);
    }
    
    // Update Lives Display
    function updateLivesDisplay() {
        for (let i = 1; i <= 5; i++) {
            const lifeEl = document.getElementById(`life-${i}`);
            if (i > lives) {
                lifeEl.classList.add('lost');
            } else {
                lifeEl.classList.remove('lost');
            }
        }
    }
    
    // Reveal Word
    function revealWord() {
        // Stop the timer
        clearInterval(wordTimer);
        
        // Record this as a failed word
        wordsFailed++;
        
        const letterBoxes = wordContainer.querySelectorAll('.letter-box');
        
        for (let i = 0; i < currentWord.length; i++) {
            if (!revealedLetters[i]) {
                letterBoxes[i].textContent = currentWord[i];
                letterBoxes[i].classList.add('revealed');
            }
        }
        
        // Show definition
        definitionText.textContent = wordsData[currentWordIndex].definition;
        wordDefinition.classList.remove('hidden');
        
        // Disable keyboard
        const keys = document.querySelectorAll('.key');
        keys.forEach(key => {
            key.classList.add('used');
        });
    }
    
    // Get Hint
    function getHint() {
        if (hintsUsed >= 3 || isWordComplete()) return;
        
        // Find unrevealed letters
        const unrevealed = [];
        for (let i = 0; i < currentWord.length; i++) {
            if (!revealedLetters[i] && currentWord[i] !== ' ') {
                unrevealed.push(i);
            }
        }
        
        if (unrevealed.length === 0) return;
        
        // Reveal random letter
        const randomIndex = unrevealed[Math.floor(Math.random() * unrevealed.length)];
        const letterToReveal = currentWord[randomIndex];
        
        // Update letter box
        const letterBoxes = wordContainer.querySelectorAll('.letter-box');
        letterBoxes[randomIndex].textContent = letterToReveal;
        letterBoxes[randomIndex].classList.add('revealed');
        revealedLetters[randomIndex] = true;
        
        // Update key appearance
        updateKeyAppearance(letterToReveal, true);
        
        // Add to used letters
        if (!usedLetters.includes(letterToReveal)) {
            usedLetters.push(letterToReveal);
        }
        
        // Increment hints used
        hintsUsed++;
        
        // Disable hint button if max hints used
        if (hintsUsed >= 3) {
            hintButton.classList.add('disabled');
        }
        
        // Check if word is complete
        if (isWordComplete()) {
            handleCorrectWord();
        }
    }
    
    // Show Results with detailed stats
    function showResults() {
        // Stop any active timer
        clearInterval(wordTimer);
        
        // Hide game screen and show results
        gameScreen.classList.add('hidden');
        resultsScreen.classList.remove('hidden');
        
        // Update score display
        finalScoreDisplay.textContent = score;
        
        // Update detailed statistics
        document.getElementById('words-guessed').textContent = wordsGuessed;
        document.getElementById('total-hints').textContent = hintsUsed;
        
        // Calculate accuracy if any words were attempted
        const totalWordsAttempted = wordsGuessed + wordsFailed;
        let accuracy = 0;
        if (totalWordsAttempted > 0) {
            accuracy = Math.round((wordsGuessed / totalWordsAttempted) * 100);
        }
        document.getElementById('accuracy-stat').textContent = `${accuracy}%`;
        
        // Set score message based on score
        if (score === 0) {
            scoreMessage.textContent = "Don't give up! Try again to learn more blood donation terms!";
        } else if (score <= 5) {
            scoreMessage.textContent = "Good effort! You're learning about blood donation!";
        } else if (score <= 10) {
            scoreMessage.textContent = "Great job! You know many blood donation terms!";
        } else {
            scoreMessage.textContent = "Amazing! You're a blood donation expert!";
            createConfetti();
        }
        
        // Enable or disable save score button
        if (userLoggedIn && saveScoreBtn) {
            saveScoreBtn.disabled = false;
        }
    }
    
    // Save Score to Leaderboard
    function saveScore() {
        if (!userLoggedIn) return;
        
        // Disable button to prevent multiple submissions
        saveScoreBtn.disabled = true;
        saveScoreBtn.textContent = 'Saving...';
        
        // Send score to server
        fetch('save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `score=${score}&user_id=${userId}&game=blood_word_guess`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saveScoreBtn.textContent = 'Score Saved!';
                fetchLeaderboard(); // Refresh leaderboard
                
                // Check if user earned any new badges
                if (data.new_badges && data.new_badges.length > 0) {
                    showBadgeNotification(data.new_badges);
                }
            } else {
                saveScoreBtn.textContent = 'Error Saving Score';
                console.error('Error saving score:', data.error);
            }
        })
        .catch(error => {
            saveScoreBtn.textContent = 'Error Saving Score';
            console.error('Error saving score:', error);
        });
    }
    
    // Show badge notification
    function showBadgeNotification(badges) {
        // Create notification container if it doesn't exist
        let notificationContainer = document.getElementById('badge-notification');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'badge-notification';
            notificationContainer.className = 'fixed top-20 right-5 z-50 w-80 transform transition-all duration-500';
            document.body.appendChild(notificationContainer);
        }
        
        // Create notifications for each new badge
        badges.forEach(badge => {
            const notification = document.createElement('div');
            notification.className = 'bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4 mb-4 border-l-4 border-yellow-400 animate__animated animate__fadeInRight';
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="mr-4 bg-yellow-400 rounded-full p-2 text-yellow-900">
                        <i class="fas fa-award text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">New Badge Unlocked!</h4>
                        <p class="text-gray-700 dark:text-gray-300">${badge.name}</p>
                    </div>
                </div>
                <a href="../dashboard/achievements.php" class="mt-2 text-sm text-blue-600 dark:text-blue-400 block text-right">View All Achievements</a>
            `;
            
            notificationContainer.appendChild(notification);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.classList.add('animate__fadeOutRight');
                setTimeout(() => {
                    notification.remove();
                }, 1000);
            }, 5000);
        });
    }
    
    // Fetch Leaderboard
    function fetchLeaderboard() {
        fetch('get_leaderboard.php?game=blood_word_guess')
        .then(response => response.json())
        .then(data => {
            // Clear leaderboard
            leaderboardList.innerHTML = '';
            
            if (data.length === 0) {
                const noScoresItem = document.createElement('div');
                noScoresItem.className = 'leaderboard-item';
                noScoresItem.innerHTML = `
                    <div class="rank">-</div>
                    <div class="name">No scores yet</div>
                    <div class="score">-</div>
                `;
                leaderboardList.appendChild(noScoresItem);
            } else {
                // Add leaderboard items
                data.forEach((item, index) => {
                    const leaderboardItem = document.createElement('div');
                    leaderboardItem.className = 'leaderboard-item';
                    
                    // Highlight current user
                    if (userLoggedIn && item.user_id == userId) {
                        leaderboardItem.className += ' bg-yellow-100 dark:bg-yellow-900';
                    }
                    
                    leaderboardItem.innerHTML = `
                        <div class="rank">${index + 1}</div>
                        <div class="name">${item.name}</div>
                        <div class="score">${item.score}</div>
                    `;
                    leaderboardList.appendChild(leaderboardItem);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching leaderboard:', error);
            leaderboardList.innerHTML = `
                <div class="leaderboard-item">
                    <div class="rank">-</div>
                    <div class="name">Error loading leaderboard</div>
                    <div class="score">-</div>
                </div>
            `;
        });
    }
    
    // Share Score
    function shareScore(platform) {
        const message = `I scored ${score} in the Blood Word Guess game! Test your knowledge of blood donation terminology too!`;
        const url = window.location.href;
        
        let shareUrl;
        
        switch (platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(message)}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(url)}`;
                break;
            case 'whatsapp':
                shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(message + ' ' + url)}`;
                break;
        }
        
        window.open(shareUrl, '_blank');
    }
    
    // Create confetti effect
    function createConfetti() {
        const colors = ['#805ad5', '#9f7aea', '#b794f4', '#d6bcfa', '#ffffff'];
        
        // Create 100 confetti pieces
        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'absolute';
            confetti.style.width = `${Math.random() * 10 + 5}px`;
            confetti.style.height = `${Math.random() * 10 + 5}px`;
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.left = `${Math.random() * 100}vw`;
            confetti.style.opacity = Math.random();
            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
            
            // Animation
            confetti.style.animation = `float-down ${Math.random() * 3 + 2}s linear forwards`;
            
            // Append to container
           
        }
        
        // Clear confetti after animation
        setTimeout(() => {
            confettiContainer.innerHTML = '';
        }, 5000);
    }
    
    // Utility function to shuffle array
    function shuffleArray(array) {
        const newArray = [...array];
        for (let i = newArray.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
        }
        return newArray;
    }
    
    // Event Listeners
    startButton.addEventListener('click', function() {
        startScreen.classList.add('hidden');
        gameScreen.classList.remove('hidden');
        initGame();
    });
    
    hintButton.addEventListener('click', function() {
        if (!hintButton.classList.contains('disabled')) {
            getHint();
        }
    });
    
    playAgainBtn.addEventListener('click', function() {
        resultsScreen.classList.add('hidden');
        gameScreen.classList.remove('hidden');
        initGame();
    });
    
    if (saveScoreBtn) {
        saveScoreBtn.addEventListener('click', saveScore);
    }
    
    // Make share function globally available
    window.shareScore = shareScore;
    
    // Initialize leaderboard on page load
    fetchLeaderboard();
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(event) {
        // Only process if we're in game screen and not in results screen
        if (gameScreen.classList.contains('hidden')) return;
        
        const key = event.key.toUpperCase();
        
        // Only process alphabetic keys
        if (/^[A-Z]$/.test(key)) {
            // Check if letter hasn't been used yet
            if (!usedLetters.includes(key)) {
                // Find and click the corresponding key button
                const keyButtons = document.querySelectorAll('.key');
                keyButtons.forEach(button => {
                    if (button.textContent === key && !button.classList.contains('used')) {
                        button.click();
                    }
                });
            }
        }
    });
    
    // Set Timer based on word length
    function setTimer(wordLength) {
        // Clear any existing timer
        clearInterval(wordTimer);
        
        // Set difficulty level based on word length
        let difficulty = 'easy';
        if (wordLength > difficultyLevels.easy.maxLength && wordLength <= difficultyLevels.medium.maxLength) {
            difficulty = 'medium';
        } else if (wordLength > difficultyLevels.medium.maxLength) {
            difficulty = 'hard';
        }
        
        // Update difficulty badge with proper Tailwind classes that work in both light and dark mode
        const difficultyBadge = document.getElementById('difficulty-badge');
        const color = difficultyLevels[difficulty].color;
        
        // Reset classes first
        difficultyBadge.className = 'px-2 py-1 rounded-full';
        
        // Add appropriate color classes based on difficulty
        if (color === 'green') {
            difficultyBadge.classList.add('bg-green-100', 'text-green-800', 'dark:bg-green-900', 'dark:text-green-200');
        } else if (color === 'yellow') {
            difficultyBadge.classList.add('bg-yellow-100', 'text-yellow-800', 'dark:bg-yellow-900', 'dark:text-yellow-200');
        } else if (color === 'red') {
            difficultyBadge.classList.add('bg-red-100', 'text-red-800', 'dark:bg-red-900', 'dark:text-red-200');
        }
        
        difficultyBadge.innerHTML = `<i class="fas fa-circle"></i> ${difficulty.charAt(0).toUpperCase() + difficulty.slice(1)}`;
        
        // Set timer duration based on difficulty
        timerDuration = difficultyLevels[difficulty].time;
        timerRemaining = timerDuration;
        
        // Reset timer bar
        timerBar.style.width = '100%';
        
        // Add proper color class based on difficulty
        timerBar.className = 'h-full transition-all duration-1000';
        if (color === 'green') {
            timerBar.classList.add('bg-green-500');
        } else if (color === 'yellow') {
            timerBar.classList.add('bg-yellow-500');
        } else if (color === 'red') {
            timerBar.classList.add('bg-red-500');
        }
        
        // Start timer countdown
        wordTimer = setInterval(updateTimer, 1000);
    }
    
    // Update timer each second
    function updateTimer() {
        timerRemaining--;
        
        // Update timer bar
        const percentage = (timerRemaining / timerDuration) * 100;
        timerBar.style.width = `${percentage}%`;
        
        // Change color as time runs out
        if (timerRemaining <= timerDuration * 0.25) {
            timerBar.classList.remove('bg-green-500', 'bg-yellow-500');
            timerBar.classList.add('bg-red-500');
        } else if (timerRemaining <= timerDuration * 0.5) {
            timerBar.classList.remove('bg-green-500', 'bg-red-500');
            timerBar.classList.add('bg-yellow-500');
        }
        
        // Time's up!
        if (timerRemaining <= 0) {
            clearInterval(wordTimer);
            timeExpired();
        }
    }
    
    // Handle time expiration
    function timeExpired() {
        // Record failure
        wordsFailed++;
        
        // Reveal the correct word
        revealWord();
        
        // Subtract a life
        lives--;
        updateLivesDisplay();
        
        // Show message
        const timeUpMessage = document.createElement('div');
        timeUpMessage.className = 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 p-3 rounded-md text-center animate__animated animate__fadeIn mb-4';
        timeUpMessage.innerHTML = '<i class="fas fa-clock mr-2"></i> Time\'s up!';
        wordContainer.parentNode.insertBefore(timeUpMessage, wordContainer);
        
        // Check if game over
        if (lives <= 0) {
            setTimeout(showResults, 3000);
        } else {
            // Move to next word after delay
            setTimeout(() => {
                currentWordIndex++;
                progressBar.style.width = `${((currentWordIndex) / wordsData.length) * 100}%`;
                loadWord();
            }, 3000);
        }
    }
    
    // Skip Button functionality 
    skipButton.addEventListener('click', function() {
        // Skip only works if we still have lives left
        if (lives > 0) {
            // Stop the timer
            clearInterval(wordTimer);
            
            // Record this as a skipped word
            wordsFailed++;
            
            // Reveal current word
            revealWord();
            
            // Show skip message
            const skipMessage = document.createElement('div');
            skipMessage.className = 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 p-3 rounded-md text-center animate__animated animate__fadeIn mb-4';
            skipMessage.innerHTML = '<i class="fas fa-forward mr-2"></i> Word skipped';
            wordContainer.parentNode.insertBefore(skipMessage, wordContainer);
            
            // Move to the next word after a delay
            setTimeout(() => {
                if (skipMessage.parentNode) {
                    skipMessage.remove();
                }
                
                currentWordIndex++;
                progressBar.style.width = `${((currentWordIndex) / wordsData.length) * 100}%`;
                loadWord();
            }, 3000);
        }
    });
});

// Add navigation and dark mode toggle functionality 
document.addEventListener('DOMContentLoaded', function() { 
    // Mobile menu toggle 
    const mobileMenuButton = document.getElementById('mobile-menu-button'); 
    const mobileMenu = document.getElementById('mobile-menu'); 
    
    if (mobileMenuButton && mobileMenu) { 
        mobileMenuButton.addEventListener('click', function() { 
            mobileMenu.classList.toggle('hidden'); 
        }); 
    } 
    
    // Hamburger menu dropdown 
    const hamburgerButton = document.getElementById('hamburger-menu'); 
    const hamburgerDropdown = document.getElementById('hamburger-dropdown'); 
    
    if (hamburgerButton && hamburgerDropdown) { 
        hamburgerButton.addEventListener('click', function(e) { 
            e.stopPropagation(); 
            hamburgerDropdown.classList.toggle('hidden'); 
        }); 
        
        // Close dropdown when clicking outside 
        document.addEventListener('click', function() { 
            if (!hamburgerDropdown.classList.contains('hidden')) { 
                hamburgerDropdown.classList.add('hidden'); 
            } 
        }); 
    } 
    
    // Initialize dark mode toggle if it exists
    if (typeof window.toggleDarkMode === 'function') {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeToggleMobile = document.getElementById('darkModeToggleMobile');
        
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', window.toggleDarkMode);
        }
        
        if (darkModeToggleMobile) {
            darkModeToggleMobile.addEventListener('click', window.toggleDarkMode);
        }
    }
}); 
</script> 

<style> 
@keyframes float-down { 
    0% { 
        transform: translateY(-100vh) rotate(0deg); 
    } 
    100% { 
        transform: translateY(100vh) rotate(360deg); 
    } 
} 
</style> 

<?php include_once '../includes/footer.php'; ?>