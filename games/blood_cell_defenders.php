<?php
include_once '../includes/header.php';
include_once '../includes/db.php';

// Check if the user is logged in
$user_logged_in = isset($_SESSION['donor_id']);
$user_name = $user_logged_in ? $_SESSION['donor_name'] : '';
$user_id = $user_logged_in ? $_SESSION['donor_id'] : '';
// Define base_url if not already defined (adjust path as needed)
$base_url = isset($base_url) ? $base_url : '/';
?>

<style>
/* Fix for navigation bar buttons (Keep these as they are essential) */
nav .hidden.md\:flex { display: flex !important; }
nav .md\:hidden { display: none !important; }
nav .md\:block { display: block !important; }
nav #hamburger-dropdown:not(.hidden) { display: block !important; }
nav #mobile-menu:not(.hidden) { display: block !important; }

/* General Page Enhancements - Inspired by Dice Game */
:root {
    --primary-light: #6a5acd; /* Example purple */
    --primary-dark: #483d8b;
    --accent-light: #48dbfb; /* Example cyan */
    --accent-dark: #00ced1;
    --secondary: #ff6b6b; /* Example coral */
    --success: #20bf6b; /* Example green */
    --danger: #eb4d4b; /* Example red */
    --light-bg1: #f0f9ff;
    --light-bg2: #e0f2fe;
    --dark-bg1: #1a202c;
    --dark-bg2: #2d3748;
    --card-light: rgba(255, 255, 255, 0.95);
    --card-dark: #2d3748; /* Adjusted for readability */
    --text-light: #2d3748;
    --text-dark: #e2e8f0;
    --border-light: #e2e8f0;
    --border-dark: #4a5568;
}

/* Fix for navbar positioning issue - modify body styles */
html {
    height: 100%;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Consistent font */
    background: linear-gradient(135deg, var(--light-bg1), var(--light-bg2));
    background-attachment: fixed; /* Keep background fixed when scrolling */
    color: var(--text-light);
    margin: 0;
    min-height: 100vh; /* Ensure the body takes at least the full viewport height */
    display: flex;
    flex-direction: column; /* Allow footer to stick to bottom */
    padding-bottom: 0; /* Remove bottom padding */
}

.dark body {
    background: linear-gradient(135deg, var(--dark-bg1), var(--dark-bg2));
    background-attachment: fixed; /* Keep background fixed in dark mode too */
    color: var(--text-dark);
}

/* Main content should grow to push footer down */
main {
    flex: 1 0 auto;
}

/* Game specific styles */
.game-container {
    max-width: 950px; /* Slightly wider */
    margin: 1rem auto; /* Add margin top */
    padding: 0 1rem;
}

.game-card {
    background-color: var(--card-light);
    border-radius: 15px; /* Match dice game */
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Softer shadow */
    color: var(--text-light);
    margin-bottom: 2rem;
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden; /* Needed for pseudo-elements */
}
.game-card::before { /* Subtle top border */
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(to right, var(--primary-light), var(--accent-light));
    opacity: 0.8;
}
.dark .game-card {
    background-color: var(--card-dark);
    color: var(--text-dark);
    border-color: var(--border-dark);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}
.dark .game-card::before {
    background: linear-gradient(to right, var(--primary-dark), var(--accent-dark));
}
.game-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}
.dark .game-card:hover {
     box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
}


/* Game Elements (Keep previous styles, minor tweaks if needed) */
.tower {
    width: 45px; height: 45px; background-color: #4299e1; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; color: white;
    font-weight: bold; cursor: pointer; position: absolute;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); border: 2px solid rgba(255, 255, 255, 0.7);
    transition: transform 0.1s ease-out; z-index: 10;
}
.enemy {
    width: 30px; height: 30px; border-radius: 50%; position: absolute;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3); border: 1px solid rgba(0,0,0,0.2);
    z-index: 20;
}
.enemy .health-bar {
    position: absolute; top: -6px; left: 0; width: 100%; height: 4px;
    background-color: #cbd5e0; border-radius: 2px; overflow: hidden;
}
.enemy .health-bar-fill {
    height: 100%; background-color: var(--danger); border-radius: 2px;
    transition: width 0.2s ease;
}
.projectile {
    width: 10px; height: 10px; border-radius: 50%; position: absolute;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2); z-index: 15;
}

#game-canvas {
    position: relative;
    background-color: #f0f9ff; /* Keep specific canvas bg */
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid #bee3f8;
    min-height: 400px;
    flex-grow: 1; /* Allow canvas to grow */
}
.dark #game-canvas {
    background-color: #1e293b; /* Slightly different dark */
    border-color: #3b82f6;
}
#game-canvas.placement-error { animation: shake 0.5s ease-in-out; }
#path-container { /* Ensure path container fills canvas */
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;
}

/* Stats Bar */
.stats-bar {
    display: flex; flex-wrap: wrap; /* Allow wrapping on small screens */
    justify-content: space-around; /* Better spacing */
    align-items: center;
    background-color: rgba(255, 255, 255, 0.8);
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.dark .stats-bar { background-color: rgba(45, 55, 72, 0.85); }
.stat-item {
    display: flex; align-items: center; font-size: 1rem; font-weight: 600;
    color: var(--text-light); margin: 0.25rem 0.5rem; /* Add vertical margin for wrap */
}
.dark .stat-item { color: var(--text-dark); }
.stat-item i {
    margin-right: 0.5rem; font-size: 1.2rem; /* Slightly smaller icons */
}
.stat-item .fa-heart { color: var(--danger); }
.stat-item .fa-coins { color: #f59e0b; } /* Amber */
.stat-item .fa-wave-square { color: #3b82f6; } /* Blue */
.stat-item .fa-star { color: var(--primary-light); } /* Use primary color */
.dark .stat-item .fa-star { color: var(--primary-light); }

/* Tower Selection Panel */
.tower-panel {
    background-color: rgba(255, 255, 255, 0.6);
    padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--border-light);
}
.dark .tower-panel {
    background-color: rgba(51, 65, 85, 0.7); /* Slightly darker panel bg */
    border-color: var(--border-dark);
}

/* Tower Type Styling - Mostly kept the same, minor tweaks possible */
.tower-type {
    display: flex; align-items: center; margin-bottom: 1rem; padding: 0.75rem;
    border-radius: 8px; cursor: pointer; transition: all 0.2s ease;
    border: 2px solid transparent;
}
.tower-type:hover {
    background-color: rgba(66, 153, 225, 0.1); /* Lighter blue hover */
    transform: translateY(-2px);
}
.dark .tower-type:hover { background-color: rgba(99, 179, 237, 0.15); }
.tower-type.selected {
    background-color: rgba(66, 153, 225, 0.15);
    border: 2px solid #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.3);
}
.dark .tower-type.selected {
    background-color: rgba(99, 179, 237, 0.2);
    border-color: #63b3ed;
    box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.4);
}
.tower-type.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
.tower-type.disabled .cost { color: var(--danger); }
.dark .tower-type.disabled .cost { color: var(--secondary); }

.tower-icon {
    width: 45px; height: 45px; border-radius: 50%; margin-right: 1rem;
    display: flex; align-items: center; justify-content: center; color: white;
    font-size: 1.2rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2); flex-shrink: 0;
}
.tower-info { flex-grow: 1; } /* Allow info to take space */
.tower-info .name { font-weight: 600; color: var(--text-light); }
.dark .tower-info .name { color: var(--text-dark); }
.tower-info .cost, .tower-info .stats { font-size: 0.8rem; color: #718096; } /* Even smaller */
.dark .tower-info .cost, .dark .tower-info .stats { color: #a0aec0; }

/* Fact Box Styling */
.fact-box {
    background-color: rgba(235, 248, 255, 0.8); /* Lighter, slightly transparent */
    border-left: 4px solid var(--accent-light);
    padding: 1rem 1.5rem; border-radius: 8px; margin-top: 1.5rem;
}
.dark .fact-box {
    background-color: rgba(42, 67, 101, 0.8);
    border-left-color: var(--accent-dark);
}
.fact-box h3 {
    display: flex; align-items: center; font-weight: 700;
    color: #2c5282; margin-bottom: 0.25rem; font-size: 1rem;
}
.dark .fact-box h3 { color: #bee3f8; }
.fact-box h3 i { margin-right: 0.5rem; color: var(--accent-light); }
.dark .fact-box h3 i { color: var(--accent-dark); }
.fact-box p { font-size: 0.9rem; color: #4a5568; line-height: 1.4; }
.dark .fact-box p { color: #cbd5e0; }

/* --- Leaderboard Styling (Using Table) --- */
.leaderboard-container { /* Optional container card */
     background-color: var(--card-light);
     border-radius: 15px;
     padding: 1.5rem; /* Less padding than main card */
     box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
     color: var(--text-light);
     border: 1px solid var(--border-light);
     margin-top: 2.5rem; /* More space above */
}
.dark .leaderboard-container {
     background-color: var(--card-dark);
     border-color: var(--border-dark);
     color: var(--text-dark);
     box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}
.leaderboard-container h2 {
    margin-bottom: 1.5rem; /* Space between title and table */
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-light);
}
.dark .leaderboard-container h2 {
    color: var(--accent-light);
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse; /* Removes gaps between cells */
    margin-top: 1rem;
    background-color: rgba(255, 255, 255, 0.05); /* Subtle background */
    border-radius: 10px; /* Apply radius to container if table has border */
    overflow: hidden; /* Clip content to rounded corners */
    box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Inner shadow */
}
.dark .leaderboard-table {
    background-color: rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.leaderboard-table th,
.leaderboard-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Subtle separator */
    vertical-align: middle; /* Align content vertically */
}
.dark .leaderboard-table th,
.dark .leaderboard-table td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.leaderboard-table th {
    background-color: rgba(106, 90, 205, 0.6); /* Header bg from dice game */
    color: white;
    font-weight: 600; /* Slightly less bold */
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.dark .leaderboard-table th {
    background-color: rgba(72, 61, 139, 0.7); /* Darker header */
}

/* Column specific alignment/width */
.leaderboard-table th:first-child,
.leaderboard-table td:first-child { /* Rank */
    text-align: center;
    width: 60px; /* Fixed width for rank */
    font-weight: bold;
}
.leaderboard-table th:last-child,
.leaderboard-table td:last-child { /* Score */
    text-align: right;
    width: 100px; /* Fixed width for score */
    font-weight: bold;
    color: var(--primary-light);
}
.dark .leaderboard-table td:last-child {
     color: var(--accent-light);
}
.leaderboard-table td:nth-child(2) { /* Name */
    /* Allow name to take remaining space */
    font-weight: 500;
}


.leaderboard-table tbody tr:nth-child(even) {
    background-color: rgba(255, 255, 255, 0.04); /* Zebra striping */
}
.dark .leaderboard-table tbody tr:nth-child(even) {
    background-color: rgba(0, 0, 0, 0.08);
}

.leaderboard-table tbody tr:hover {
    background-color: rgba(106, 90, 205, 0.1); /* Hover effect */
}
.dark .leaderboard-table tbody tr:hover {
    background-color: rgba(72, 61, 139, 0.3);
}

.leaderboard-table tr.current-user { /* Style for current user's score */
    background-color: rgba(66, 153, 225, 0.15) !important; /* Use !important to override hover/zebra */
    font-weight: bold;
    border-left: 3px solid var(--primary-light); /* Accent border */
}
.dark .leaderboard-table tr.current-user {
    background-color: rgba(42, 67, 101, 0.4) !important;
    border-left-color: var(--accent-light);
}

/* Skeleton Loader for Leaderboard Table */
.skeleton-item td div { /* Target divs inside skeleton tds */
    background-color: #e2e8f0;
    border-radius: 0.25rem;
    height: 1rem;
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.dark .skeleton-item td div { background-color: #4a5568; }
.skeleton-item .rank-skel { width: 30px; margin: 0 auto; }
.skeleton-item .name-skel { width: 70%; }
.skeleton-item .score-skel { width: 50px; margin-left: auto; }


/* Pause Menu */
#pause-menu { backdrop-filter: blur(5px); }

/* Buttons - Inspired by Dice Game */
.btn {
    background: linear-gradient(to right, var(--primary-light), var(--accent-light));
    color: white; border: none; padding: 10px 20px; /* Slightly smaller padding */
    font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer;
    transition: all 0.3s; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative; overflow: hidden; text-transform: uppercase; letter-spacing: 0.5px;
}
.dark .btn {
     background: linear-gradient(to right, var(--primary-dark), var(--accent-dark));
}

.btn:hover {
    transform: translateY(-2px); box-shadow: 0 7px 10px rgba(0, 0, 0, 0.15);
}
.dark .btn:hover {
    box-shadow: 0 7px 10px rgba(0, 0, 0, 0.3);
}
.btn:active { transform: translateY(0); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }

/* Specific Button Colors */
.btn-primary { /* Use default gradient */ }
.btn-secondary {
    background: linear-gradient(to right, #f59e0b, #f8b035); /* Fixed color value */
}
.dark .btn-secondary {
    background: linear-gradient(to right, #d97706, #f59e0b);
}
.btn-danger {
    background: linear-gradient(to right, #ef4444, #f87171); /* Red gradient */
}
.dark .btn-danger {
     background: linear-gradient(to right, #dc2626, #ef4444);
}
.btn-success {
    background: linear-gradient(to right, #10b981, #34d399); /* Green gradient */
}
.dark .btn-success {
     background: linear-gradient(to right, #059669, #10b981);
}
.btn-sm { /* Modifier for smaller button */
    padding: 8px 16px;
    font-size: 0.9rem;
}


/* Badge notification styling (keep as is) */
#badge-notification { animation-duration: 0.5s; }
/* Animations */
.animate-pulse { animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
@keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); } 20%, 40%, 60%, 80% { transform: translateX(5px); } }
@keyframes fade-in-up { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.animate-fade-in-up { animation: fade-in-up 0.3s ease-out forwards; }


/* Hit effects and animations */
.hit-effect {
    position: absolute;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
    transform: translate(-50%, -50%);
    animation: hit-pulse 0.3s ease-out forwards;
    z-index: 25;
    pointer-events: none;
}

@keyframes hit-pulse {
    0% { transform: translate(-50%, -50%) scale(0.5); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
}

/* Confetti for victory/achievements */
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    z-index: 1000;
    animation: confetti-fall 4s ease-in-out forwards;
    pointer-events: none;
}

@keyframes confetti-fall {
    0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
    80% { opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}

/* Bounce animation for score increases */
@keyframes score-bounce {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.score-bounce {
    animation: score-bounce 0.4s ease-in-out;
}

/* New cursor styles when placing towers */
#game-canvas.place-tower {
    cursor: pointer;
}
#game-canvas.no-tower {
    cursor: not-allowed;
}

/* Range indicator for tower placement */
.range-indicator {
    position: absolute;
    border-radius: 50%;
    border: 2px dashed rgba(106, 90, 205, 0.4);
    background-color: rgba(106, 90, 205, 0.1);
    pointer-events: none;
    z-index: 5;
    transform: translate(-50%, -50%);
}
</style>

<div class="py-8">
    <!-- Title and Subtitle -->
    <h1 class="text-3xl md:text-4xl font-bold text-center mb-4 text-gray-800 dark:text-white">Blood Cell Defenders</h1>
    <p class="text-center text-gray-600 dark:text-gray-400 mb-8 max-w-3xl mx-auto text-lg">
        <i class="fas fa-shield-virus text-blue-500 mr-2"></i>Defend the bloodstream! Place defender cells strategically to stop invading pathogens and learn cool facts about blood.
    </p>

    <div class="game-container">
        <!-- Start Screen -->
        <div id="start-screen" class="text-center">
            <div class="game-card transform transition hover:scale-105">
                <div class="mb-6">
                    <!-- Fix the icon - replace with Font Awesome icon instead of external URL -->
                    <i class="fas fa-shield-virus text-blue-500 text-6xl"></i>
                </div>
                <h2 class="text-2xl font-bold mb-4">Ready to Defend?</h2>
                <p class="mb-8">Your bloodstream is under attack! Use different blood cells to fight off waves of bacteria, viruses, and parasites.</p>
                <button id="start-button" class="btn btn-primary text-lg">
                    <i class="fas fa-play mr-2"></i>Start Defending
                </button>
            </div>
        </div>

        <!-- Game Screen -->
        <div id="game-screen" class="hidden">
            <!-- Stats Bar -->
            <div class="stats-bar">
                 <div class="stat-item"><i class="fas fa-wave-square"></i> Wave: <span id="wave-number" class="ml-1">1</span></div>
                 <div class="stat-item"><i class="fas fa-coins"></i> <span id="resources" class="ml-1">100</span></div>
                 <div class="stat-item"><i class="fas fa-heart"></i> <span id="health" class="ml-1">100</span></div>
                 <div class="stat-item"><i class="fas fa-star"></i> Score: <span id="score" class="ml-1">0</span></div>
            </div>

            <!-- Main Game Content Card -->
            <div class="game-card !p-4 md:!p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Game Canvas -->
                    <div id="game-canvas" class="w-full lg:w-3/4 mb-4 lg:mb-0 order-1 lg:order-1">
                        <!-- Path container sits inside for absolute positioning -->
                        <div id="path-container"></div>
                        <!-- Other absolutely positioned elements (towers, enemies, projectiles) go directly inside #game-canvas -->
                    </div>

                    <!-- Right Panel (Tower Selection & Info) -->
                    <div class="w-full lg:w-1/4 flex flex-col gap-4 order-2 lg:order-2">
                        <!-- Tower Selection Panel -->
                        <div class="tower-panel">
                            <h3 class="font-bold mb-3 text-lg border-b pb-2 border-gray-300 dark:border-gray-600">Defender Cells</h3>
                            <div id="tower-selection">
                                <!-- Tower types populated by JS -->
                            </div>
                        </div>

                        <!-- Wave Info Panel -->
                        <div class="tower-panel">
                            <h3 class="font-bold mb-2 text-lg border-b pb-2 border-gray-300 dark:border-gray-600">Wave Info</h3>
                            <div class="text-sm mb-1">
                                Next wave in: <span id="wave-timer" class="font-semibold">30s</span>
                            </div>
                             <button id="pause-button" class="w-full btn btn-secondary btn-sm mt-3">
                                <i class="fas fa-pause mr-1"></i> Pause Game
                             </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blood Fact Box -->
            <div class="fact-box">
                <h3><i class="fas fa-lightbulb"></i> Blood Fact:</h3>
                <p id="blood-fact" class="italic">White blood cells (leukocytes) are part of the immune system...</p>
            </div>
        </div>

        <!-- Results Screen -->
        <div id="results-screen" class="hidden text-center">
            <div class="game-card">
                <h2 id="result-title" class="text-3xl font-bold mb-4">Game Over!</h2>
                <div class="mb-6">
                    <i id="result-icon" class="fas fa-times-circle text-red-500 text-6xl"></i>
                </div>
                <p class="mb-1 text-xl">Your Final Score:</p>
                <div class="text-4xl mb-4 font-bold text-blue-600 dark:text-blue-400"><span id="final-score">0</span></div>
                <p id="result-message" class="mb-6 text-lg">Better luck next time!</p>

                <?php if($user_logged_in): ?>
                <button id="save-score-btn" class="btn btn-success mb-4">
                    <i class="fas fa-save mr-2"></i>Save Score
                </button>
                <?php else: ?>
                <div class="mb-6 text-gray-700 dark:text-gray-300 p-3 bg-gray-100 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                    <p><a href="<?php echo htmlspecialchars($base_url); ?>login.php" class="text-blue-600 dark:text-blue-400 underline font-semibold">Log in</a> or <a href="<?php echo htmlspecialchars($base_url); ?>register.php" class="text-blue-600 dark:text-blue-400 underline font-semibold">Sign up</a> to save your score and compete on the leaderboard!</p>
                </div>
                <?php endif; ?>

                <div class="mt-6">
                    <button id="play-again-btn" class="btn btn-primary">
                        <i class="fas fa-redo mr-2"></i>Play Again
                    </button>
                </div>
            </div>
        </div>

        <!-- Pause Menu -->
        <div id="pause-menu" class="hidden fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 max-w-sm w-full mx-4 transform transition-all scale-95 opacity-0 animate-fade-in-up">
                <h3 class="text-2xl font-bold mb-6 text-center text-gray-900 dark:text-white">Game Paused</h3>
                <div class="space-y-4">
                    <button id="resume-button" class="w-full btn btn-primary">
                        <i class="fas fa-play mr-2"></i>Resume Game
                    </button>
                    <button id="restart-button" class="w-full btn btn-secondary">
                         <i class="fas fa-redo mr-2"></i>Restart Game
                    </button>
                    <button id="quit-button" class="w-full btn btn-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i>Quit Game
                    </button>
                </div>
            </div>
        </div>
         <!-- Pause menu animation style -->
         <style>
            /* .animate-fade-in-up { animation: fade-in-up 0.3s ease-out forwards; } */
         </style>

        <!-- Leaderboard Section - Now uses a container card -->
        <div class="leaderboard-container">
            <h2>Top Defenders</h2>
            <div class="overflow-x-auto"> <!-- Make table scrollable on small screens -->
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboard-list">
                        <!-- Skeleton Loader Rows -->
                        <tr class="skeleton-item"><td class="rank"><div class="rank-skel"></div></td><td><div class="name-skel"></div></td><td class="score"><div class="score-skel"></div></td></tr>
                        <tr class="skeleton-item"><td class="rank"><div class="rank-skel"></div></td><td><div class="name-skel"></div></td><td class="score"><div class="score-skel"></div></td></tr>
                        <tr class="skeleton-item"><td class="rank"><div class="rank-skel"></div></td><td><div class="name-skel"></div></td><td class="score"><div class="score-skel"></div></td></tr>
                        <tr class="skeleton-item"><td class="rank"><div class="rank-skel"></div></td><td><div class="name-skel"></div></td><td class="score"><div class="score-skel"></div></td></tr>
                        <tr class="skeleton-item"><td class="rank"><div class="rank-skel"></div></td><td><div class="name-skel"></div></td><td class="score"><div class="score-skel"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- DOM Elements (Mostly the same) ---
    const startScreen = document.getElementById('start-screen');
    const gameScreen = document.getElementById('game-screen');
    const resultsScreen = document.getElementById('results-screen');
    const startButton = document.getElementById('start-button');
    const pauseButton = document.getElementById('pause-button');
    const resumeButton = document.getElementById('resume-button');
    const restartButton = document.getElementById('restart-button');
    const quitButton = document.getElementById('quit-button');
    const playAgainBtn = document.getElementById('play-again-btn');
    const saveScoreBtn = document.getElementById('save-score-btn');
    const pauseMenu = document.getElementById('pause-menu');
    const gameCanvas = document.getElementById('game-canvas');
    const pathContainer = document.getElementById('path-container');
    const scoreDisplay = document.getElementById('score');
    const healthDisplay = document.getElementById('health');
    const resourcesDisplay = document.getElementById('resources');
    const finalScoreDisplay = document.getElementById('final-score');
    const waveNumberDisplay = document.getElementById('wave-number');
    const waveTimerDisplay = document.getElementById('wave-timer');
    const bloodFactDisplay = document.getElementById('blood-fact');
    const leaderboardList = document.getElementById('leaderboard-list'); // This is now the <tbody>
    const towerSelectionContainer = document.getElementById('tower-selection');
    const resultTitle = document.getElementById('result-title');
    const resultIcon = document.getElementById('result-icon');
    const resultMessage = document.getElementById('result-message');

    // --- Game State, Data, Facts (Keep as is) ---
    let score = 0;
    let health = 100;
    let resources = 150;
    let waveNumber = 0;
    let waveTimer = 10;
    let towers = [];
    let enemies = [];
    let projectiles = [];
    let selectedTowerType = 'white';
    let gameInterval;
    let waveInterval;
    let gameRunning = false;
    let isPaused = false;
    const userLoggedIn = <?php echo $user_logged_in ? 'true' : 'false'; ?>;
    const userName = "<?php echo addslashes($user_name); ?>";
    const userId = "<?php echo addslashes($user_id); ?>";

    const towerData = { // Keep existing data
        white: { name: 'White Cell', cost: 50, damage: 12, range: 110, fireRate: 1400, color: '#E2E8F0', borderColor: '#A0AEC0', icon: 'fa-shield-virus', description: 'Standard defender, balanced stats.' },
        red: { name: 'Red Cell', cost: 75, damage: 8, range: 160, fireRate: 900, color: '#FEB2B2', borderColor: '#F56565', icon: 'fa-tint', description: 'Fast firing, long range, lower damage.' },
        platelet: { name: 'Platelet', cost: 125, damage: 25, range: 90, fireRate: 2200, color: '#FEEBC8', borderColor: '#F6AD55', icon: 'fa-star', description: 'Slow firing, short range, high damage.' }
    };
    const enemyData = { // Keep existing data
        bacteria: { health: 35, speed: 0.9, damage: 5, color: '#68D391', points: 10, icon: 'fa-bacteria' },
        virus: { health: 25, speed: 1.4, damage: 8, color: '#F687B3', points: 15, icon: 'fa-virus' },
        parasite: { health: 60, speed: 0.6, damage: 12, color: '#9F7AEA', points: 25, icon: 'fa-bug' }
    };
    const bloodFacts = [ // Keep existing data
        "White blood cells (leukocytes) are part of the immune system and help fight infections.", "Red blood cells (erythrocytes) carry oxygen from the lungs to the body's tissues.", "Platelets (thrombocytes) are cell fragments that help blood clot to stop bleeding.", "Plasma is the liquid part of blood, making up about 55% of blood volume.", "A single drop of blood contains around 5 million red blood cells.", "The average adult has about 8-10 pints (4.5-5.7 liters) of blood in their body.", "Blood makes up approximately 7-8% of your total body weight.", "Blood cells are produced in the bone marrow, the soft tissue inside bones.", "Red blood cells live for about 120 days before being replaced.", "White blood cells can live from a few days to several months, depending on the type.", "There are five main types of white blood cells: neutrophils, lymphocytes, eosinophils, monocytes, and basophils.", "Blood type is determined by the presence or absence of certain antigens on red blood cells.", "AB+ is the universal recipient blood type, able to receive blood from any ABO/Rh type.", "O- is the universal donor blood type for red blood cells."
    ];
    let path = [];

    // --- Initialize Functions (Keep as is) ---
    function initializeGame() {
        score = 0; health = 100; resources = 150; waveNumber = 0; waveTimer = 10;
        towers = []; enemies = []; projectiles = []; selectedTowerType = 'white';
        gameRunning = false; isPaused = false;
        gameCanvas.innerHTML = ''; pathContainer.innerHTML = '';
        // Delay calculation slightly
        setTimeout(() => {
            calculatePath(); drawPath(); renderTowerSelection(); updateUI();
            startWaveTimer(); gameRunning = true;
            if(gameInterval) clearInterval(gameInterval); // Clear previous interval if restarting
            gameInterval = setInterval(gameLoop, 16);
            displayRandomFact(); fetchLeaderboard();
        }, 50); // Reduced delay slightly
    }

    function calculatePath() {
        const canvasWidth = gameCanvas.clientWidth;
        const canvasHeight = gameCanvas.clientHeight;
        if (canvasWidth === 0 || canvasHeight === 0) { // Prevent calculation if canvas not rendered
             console.warn("Canvas dimensions not ready for path calculation.");
             path = [{x:-30, y: 200}, {x: canvasWidth + 30, y: 200}]; // Simple default path
             return;
        }
        path = [
            { x: -30, y: canvasHeight * 0.4 }, { x: canvasWidth * 0.2, y: canvasHeight * 0.4 },
            { x: canvasWidth * 0.2, y: canvasHeight * 0.15 }, { x: canvasWidth * 0.6, y: canvasHeight * 0.15 },
            { x: canvasWidth * 0.6, y: canvasHeight * 0.7 }, { x: canvasWidth * 0.4, y: canvasHeight * 0.7 },
            { x: canvasWidth * 0.4, y: canvasHeight * 0.5 }, { x: canvasWidth * 0.8, y: canvasHeight * 0.5 },
            { x: canvasWidth * 0.8, y: canvasHeight * 0.3 }, { x: canvasWidth + 30, y: canvasHeight * 0.3 }
        ];
    }

    function drawPath() {
        if (path.length < 2 || !pathContainer) return;
        const svgNS = "http://www.w3.org/2000/svg";
        const svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('width', '100%'); svg.setAttribute('height', '100%');
        svg.style.position = 'absolute'; svg.style.top = '0'; svg.style.left = '0';
        svg.style.zIndex = '1'; svg.style.pointerEvents = 'none';

        const pathLine = document.createElementNS(svgNS, 'path');
        let pathD = `M ${path[0].x} ${path[0].y}`;
        for (let i = 1; i < path.length; i++) { pathD += ` L ${path[i].x} ${path[i].y}`; }
        pathLine.setAttribute('d', pathD); pathLine.setAttribute('stroke', 'currentColor');
        pathLine.setAttribute('stroke-width', '15'); pathLine.setAttribute('stroke-dasharray', '10, 10');
        pathLine.setAttribute('fill', 'none');
        pathLine.classList.add('text-blue-300', 'dark:text-gray-600', 'opacity-50'); // Adjusted colors/opacity

        svg.appendChild(pathLine);
        pathContainer.appendChild(svg); // Add to pathContainer div
        // No need to append pathContainer to gameCanvas again, it's already there in HTML
    }

    // --- Game Loop & Wave Management (Keep as is) ---
    function gameLoop() { if (!gameRunning || isPaused) return; updateEnemies(); updateTowers(); updateProjectiles(); checkGameOver(); }
    function startWaveTimer() {
        if (waveInterval) clearInterval(waveInterval);
        waveTimerDisplay.textContent = `${waveTimer}s`;
        waveInterval = setInterval(() => {
            if (!gameRunning || isPaused) return;
            waveTimer--; waveTimerDisplay.textContent = `${waveTimer}s`;
            if (waveTimer <= 0) { clearInterval(waveInterval); startNewWave(); }
        }, 1000);
    }
    function startNewWave() { waveNumber++; waveTimer = 30; resources += 50 + Math.floor(waveNumber * 7.5); spawnWaveEnemies(); updateUI(); startWaveTimer(); displayRandomFact(); }
    function spawnWaveEnemies() {
        const baseEnemyCount = 5; const enemyCount = baseEnemyCount + Math.floor(waveNumber * 2.5);
        const enemyTypes = ['bacteria']; if (waveNumber >= 3) enemyTypes.push('virus'); if (waveNumber >= 5) enemyTypes.push('parasite');
        for (let i = 0; i < enemyCount; i++) {
            setTimeout(() => {
                if (!gameRunning || isPaused) return;
                const type = enemyTypes[Math.floor(Math.random() * enemyTypes.length)]; createEnemy(type);
            }, i * Math.max(200, (800 - waveNumber * 20))); // Ensure delay doesn't go below 200ms
        }
    }

    // --- Enemy Logic ---
    function createEnemy(type) {
        const data = enemyData[type];
        const healthMultiplier = 1 + (waveNumber - 1) * 0.15;
        const enemy = {
            type: type, 
            health: data.health * healthMultiplier, 
            maxHealth: data.health * healthMultiplier,
            speed: data.speed, 
            damage: data.damage, 
            color: data.color, 
            points: data.points,
            x: path[0].x, 
            y: path[0].y, 
            pathIndex: 0, 
            id: `enemy-${Date.now()}-${Math.random()}`,
            element: document.createElement('div')
        };
        
        // Create enemy element
        enemy.element.id = enemy.id;
        enemy.element.className = 'enemy';
        enemy.element.style.backgroundColor = enemy.color;
        enemy.element.style.left = `${enemy.x - 15}px`;
        enemy.element.style.top = `${enemy.y - 15}px`;

        // Create health bar with proper structure
        const healthBar = document.createElement('div');
        healthBar.className = 'health-bar';
        
        const healthBarFill = document.createElement('div');
        healthBarFill.className = 'health-bar-fill';
        healthBarFill.style.width = '100%';
        
        healthBar.appendChild(healthBarFill);
        enemy.element.appendChild(healthBar);
        
        // Add enemy to the game canvas
        gameCanvas.appendChild(enemy.element);
        enemies.push(enemy);
    }
    function updateEnemies() {
        for (let i = enemies.length - 1; i >= 0; i--) {
            const enemy = enemies[i]; const targetPoint = path[enemy.pathIndex + 1];
            if (!targetPoint) { health -= enemy.damage; if (health < 0) health = 0; enemy.element.remove(); enemies.splice(i, 1); updateUI(); continue; }
            const dx = targetPoint.x - enemy.x; const dy = targetPoint.y - enemy.y; const distance = Math.sqrt(dx * dx + dy * dy);
            if (distance < enemy.speed * 1.5) {
                 enemy.pathIndex++;
                 if (path[enemy.pathIndex +1]) { // Snap to point
                      enemy.x = path[enemy.pathIndex].x;
                      enemy.y = path[enemy.pathIndex].y;
                 }
            } else { const vx = (dx / distance) * enemy.speed; const vy = (dy / distance) * enemy.speed; enemy.x += vx; enemy.y += vy; }
            enemy.element.style.left = `${enemy.x - 15}px`; enemy.element.style.top = `${enemy.y - 15}px`;
        }
    }
    function updateEnemyHealthBar(enemy) {
         const healthPercent = Math.max(0, (enemy.health / enemy.maxHealth) * 100);
         const healthBarFill = enemy.element.querySelector('.health-bar-fill');
         if (healthBarFill) { healthBarFill.style.width = `${healthPercent}%`; }
    }

    // --- Tower Logic (Keep as is, including render/select/affordability/place/error/update) ---
    function renderTowerSelection() {
        towerSelectionContainer.innerHTML = '';
        Object.entries(towerData).forEach(([type, data]) => {
            const div = document.createElement('div'); div.className = 'tower-type'; div.dataset.type = type;
            div.title = `${data.name} - Cost: ${data.cost}\nDamage: ${data.damage}, Range: ${data.range}, Rate: ${(1000/data.fireRate).toFixed(1)}/s\n${data.description}`;
            div.innerHTML = `<div class="tower-icon" style="background-color: ${data.borderColor};"><i class="fas ${data.icon}"></i></div><div class="tower-info"><div class="name">${data.name}</div><div class="cost">Cost: ${data.cost} <i class="fas fa-coins text-yellow-500"></i></div><div class="stats text-xs">D:${data.damage} R:${data.range} F:${(1000/data.fireRate).toFixed(1)}/s</div></div>`;
            div.addEventListener('click', () => selectTowerType(type, div));
            towerSelectionContainer.appendChild(div);
        });
        selectTowerType(selectedTowerType, towerSelectionContainer.querySelector('.tower-type'));
        updateTowerAffordability();
    }
    function selectTowerType(type, element) { selectedTowerType = type; document.querySelectorAll('.tower-type').forEach(el => el.classList.remove('selected')); if (element) { element.classList.add('selected'); } }
    function updateTowerAffordability() { document.querySelectorAll('.tower-type').forEach(el => { const type = el.dataset.type; if (towerData[type].cost > resources) { el.classList.add('disabled'); } else { el.classList.remove('disabled'); } }); }
    function placeTower(clickX, clickY) {
        const towerInfo = towerData[selectedTowerType]; const towerRadius = 22.5; const placementRadius = 40;
        const x = clickX; const y = clickY;
        if (resources < towerInfo.cost) { showPlacementError("Not enough resources!"); return; }
        let tooCloseToPath = false;
        for (let i = 0; i < path.length - 1; i++) { const dx = x - path[i].x; const dy = y - path[i].y; if (Math.sqrt(dx*dx + dy*dy) < placementRadius * 0.7) { tooCloseToPath = true; break; } } // Reduced path check radius slightly
        if(tooCloseToPath) { showPlacementError("Cannot place tower too close to the path!"); return; }
        for (const tower of towers) { const dx = tower.x - x; const dy = tower.y - y; if (Math.sqrt(dx * dx + dy * dy) < placementRadius) { showPlacementError("Too close to another tower!"); return; } }
        resources -= towerInfo.cost;
        const tower = { type: selectedTowerType, x: x, y: y, damage: towerInfo.damage, range: towerInfo.range, fireRate: towerInfo.fireRate, color: towerInfo.color, borderColor: towerInfo.borderColor, icon: towerInfo.icon, lastFired: 0, id: `tower-${Date.now()}-${Math.random()}`, element: document.createElement('div') };
        tower.element.id = tower.id; tower.element.className = 'tower'; tower.element.style.backgroundColor = tower.color; tower.element.style.borderColor = tower.borderColor;
        tower.element.style.left = `${x - towerRadius}px`; tower.element.style.top = `${y - towerRadius}px`; tower.element.innerHTML = `<i class="fas ${tower.icon}"></i>`;
        gameCanvas.appendChild(tower.element); // Append directly to canvas
        towers.push(tower); updateUI();
    }
    function showPlacementError(message) { console.warn("Placement Error:", message); gameCanvas.classList.add('placement-error'); setTimeout(() => gameCanvas.classList.remove('placement-error'), 500); }
    function updateTowers() {
        const now = Date.now();
        towers.forEach(tower => {
            if (now - tower.lastFired < tower.fireRate) return;
            let target = null; let maxProgress = -1;
            enemies.forEach((enemy) => { // Removed index as it wasn't used here
                const dx = enemy.x - tower.x; const dy = enemy.y - tower.y; const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance <= tower.range) {
                    let progress = enemy.pathIndex;
                     if (path[enemy.pathIndex + 1]) {
                        const targetPoint = path[enemy.pathIndex + 1]; const segmentStart = path[enemy.pathIndex];
                        const segDx = targetPoint.x - segmentStart.x; const segDy = targetPoint.y - segmentStart.y; const segmentLength = Math.sqrt(segDx*segDx + segDy*segDy);
                        if (segmentLength > 0) { const enemyDx = enemy.x - segmentStart.x; const enemyDy = enemy.y - segmentStart.y; const dotProduct = enemyDx * segDx + enemyDy * segDy; progress += Math.max(0, Math.min(1, dotProduct / (segmentLength * segmentLength))); }
                    }
                    if (progress > maxProgress) { maxProgress = progress; target = enemy; }
                }
            });
            if (target) { fireProjectile(tower, target); tower.lastFired = now; tower.element.style.transform = 'scale(1.15)'; setTimeout(() => { if(tower.element) tower.element.style.transform = 'scale(1)'; }, 100); }
        });
    }

    // --- Projectile Logic (Keep as is) ---
    function fireProjectile(tower, target) {
        const projectile = { x: tower.x, y: tower.y, target: target, speed: 6, damage: tower.damage, color: towerData[tower.type].borderColor, id: `proj-${Date.now()}-${Math.random()}`, element: document.createElement('div') };
        projectile.element.id = projectile.id; projectile.element.className = 'projectile'; projectile.element.style.backgroundColor = projectile.color;
        projectile.element.style.left = `${projectile.x - 5}px`; projectile.element.style.top = `${projectile.y - 5}px`;
        gameCanvas.appendChild(projectile.element); // Append directly to canvas
        projectiles.push(projectile);
    }
    function updateProjectiles() {
        for (let i = projectiles.length - 1; i >= 0; i--) {
            const p = projectiles[i];
            const target = p.target;
            
            // Remove projectile if target doesn't exist anymore
            if (!target || !enemies.includes(target) || target.health <= 0) {
                p.element.remove();
                projectiles.splice(i, 1);
                continue;
            }
            
            // Move projectile towards target
            const dx = target.x - p.x;
            const dy = target.y - p.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            // Hit target
            if (distance < p.speed + 5) {
                // Apply damage and update health bar
                target.health -= p.damage;
                
                // Update enemy health bar visualization
                const healthPercent = Math.max(0, (target.health / target.maxHealth) * 100);
                const healthBarFill = target.element.querySelector('.health-bar-fill');
                if (healthBarFill) {
                    healthBarFill.style.width = `${healthPercent}%`;
                }
                
                // Check if enemy is destroyed
                if (target.health <= 0) {
                    const targetIndex = enemies.findIndex(e => e.id === target.id);
                    if (targetIndex !== -1) {
                        // Add score and resources, then remove the enemy
                        score += target.points;
                        resources += Math.ceil(target.points * 0.6);
                        
                        // Add hit animation before removing
                        const hitEffect = document.createElement('div');
                        hitEffect.className = 'hit-effect';
                        hitEffect.style.left = `${target.x - 15}px`;
                        hitEffect.style.top = `${target.y - 15}px`;
                        gameCanvas.appendChild(hitEffect);
                        
                        setTimeout(() => {
                            hitEffect.remove();
                        }, 300);
                        
                        target.element.remove();
                        enemies.splice(targetIndex, 1);
                    }
                }
                
                // Remove projectile
                p.element.remove();
                projectiles.splice(i, 1);
                updateUI();
            } else {
                // Continue moving projectile
                const vx = (dx / distance) * p.speed;
                const vy = (dy / distance) * p.speed;
                p.x += vx;
                p.y += vy;
                p.element.style.left = `${p.x - 5}px`;
                p.element.style.top = `${p.y - 5}px`;
            }
        }
    }

    // --- UI & Game State Updates (Keep as is, including game over logic) ---
    function updateUI() { scoreDisplay.textContent = score; healthDisplay.textContent = health; resourcesDisplay.textContent = resources; waveNumberDisplay.textContent = waveNumber > 0 ? waveNumber : '-'; updateTowerAffordability(); }
    function displayRandomFact() { const fact = bloodFacts[Math.floor(Math.random() * bloodFacts.length)]; bloodFactDisplay.textContent = fact; }
    function checkGameOver() { if (health <= 0 && gameRunning) { gameOver(); } }
    function gameOver() {
        gameRunning = false; isPaused = false; clearInterval(gameInterval); clearInterval(waveInterval);
        finalScoreDisplay.textContent = score;
        if (score > 500) { resultTitle.textContent = "Excellent Defense!"; resultIcon.className = "fas fa-trophy text-yellow-500 text-6xl"; resultMessage.textContent = `Incredible work! Score: ${score}.`; }
        else if (score > 200) { resultTitle.textContent = "Good Effort!"; resultIcon.className = "fas fa-shield-alt text-blue-500 text-6xl"; resultMessage.textContent = `Nice job! Score: ${score}.`; }
        else { resultTitle.textContent = "Pathogens Overwhelmed!"; resultIcon.className = "fas fa-skull-crossbones text-red-500 text-6xl"; resultMessage.textContent = `Try again! Score: ${score}.`; }
        // Cleanup
        towers.forEach(t => { if(t.element) t.element.remove(); }); enemies.forEach(e => { if(e.element) e.element.remove(); }); projectiles.forEach(p => { if(p.element) p.element.remove(); });
        towers = []; enemies = []; projectiles = [];
        if(saveScoreBtn) { saveScoreBtn.disabled = false; saveScoreBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Score'; } // Reset button text
        gameScreen.classList.add('hidden'); resultsScreen.classList.remove('hidden'); pauseMenu.classList.add('hidden');
    }
    function pauseGame() {
        if (!gameRunning || isPaused) return; isPaused = true; // Set paused flag
        pauseButton.innerHTML = '<i class="fas fa-play mr-1"></i> Resume Game'; // Update button text/icon
        pauseMenu.classList.remove('hidden');
        const modal = pauseMenu.querySelector('.animate-fade-in-up'); // Animate modal
        if (modal) { modal.style.opacity = '0'; modal.style.transform = 'scale(0.95) translateY(10px)'; requestAnimationFrame(() => { modal.style.opacity = '1'; modal.style.transform = 'scale(1) translateY(0)'; }); }
    }
    function resumeGame() {
        if (!gameRunning || !isPaused) return; isPaused = false; // Unset paused flag
        pauseButton.innerHTML = '<i class="fas fa-pause mr-1"></i> Pause Game'; // Reset button
        pauseMenu.classList.add('hidden');
    }
     function restartGame() { gameOver(); resultsScreen.classList.add('hidden'); gameScreen.classList.remove('hidden'); initializeGame(); } // Directly initialize
     function quitGame() { gameOver(); }

    // --- Score Saving & Badge Notification (Keep as is) ---
    function saveScore() {
        if (!userLoggedIn || !saveScoreBtn || saveScoreBtn.disabled) return;
        saveScoreBtn.disabled = true; saveScoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        fetch('save_score.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', }, body: `score=${score}&user_id=${userId}&game=blood_cell_defenders` })
        .then(response => response.ok ? response.json() : Promise.reject('Save failed'))
        .then(data => {
            if (data.success) { saveScoreBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Score Saved!'; fetchLeaderboard(); if (data.new_badges && data.new_badges.length > 0) { showBadgeNotification(data.new_badges); } }
            else { saveScoreBtn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Error Saving'; console.error('Error saving score:', data.error); setTimeout(() => { if(saveScoreBtn) { saveScoreBtn.disabled = false; saveScoreBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Score'; } }, 2000); }
        })
        .catch(error => { saveScoreBtn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Network Error'; console.error('Network Error:', error); setTimeout(() => { if(saveScoreBtn) { saveScoreBtn.disabled = false; saveScoreBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Score'; } }, 2000); });
    }
    function showBadgeNotification(badges) { // Keep previous implementation
         let container = document.getElementById('badge-notification');
         if (!container) { container = document.createElement('div'); container.id = 'badge-notification'; container.className = 'fixed top-20 right-5 z-[100] w-80 space-y-3'; document.body.appendChild(container); }
         const animateCSSLoaded = !!document.querySelector('link[href*="animate.min.css"]');
         badges.forEach((badge, index) => {
             const notification = document.createElement('div');
             notification.className = `bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 border-l-4 border-yellow-400 dark:border-yellow-500 overflow-hidden transform transition-all duration-500 ${animateCSSLoaded ? 'animate__animated' : ''}`;
             notification.style.opacity = '0'; notification.style.transform = 'translateX(100%)';
             notification.innerHTML = `<div class="flex items-center"><div class="mr-3 flex-shrink-0 bg-yellow-400 dark:bg-yellow-500 rounded-full p-2 text-yellow-900 dark:text-white"><i class="fas ${badge.icon || 'fa-award'} text-xl"></i></div><div class="flex-grow"><h4 class="font-bold text-gray-900 dark:text-white">New Achievement!</h4><p class="text-sm text-gray-700 dark:text-gray-300">${badge.name}</p><p class="text-xs text-gray-500 dark:text-gray-400">${badge.description || ''}</p></div><button class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl">×</button></div><a href="../dashboard/achievements.php" class="mt-2 text-xs text-blue-600 dark:text-blue-400 block text-right hover:underline">View Achievements</a>`;
             const closeButton = notification.querySelector('button');
             closeButton.onclick = () => { notification.style.transform = 'translateX(100%)'; notification.style.opacity = '0'; setTimeout(() => notification.remove(), 500); };
             setTimeout(() => {
                  container.appendChild(notification);
                  requestAnimationFrame(() => { if (animateCSSLoaded) notification.classList.add('animate__fadeInRight'); notification.style.opacity = '1'; notification.style.transform = 'translateX(0)'; });
                  setTimeout(() => { if (notification.parentElement) { if (animateCSSLoaded) { notification.classList.remove('animate__fadeInRight'); notification.classList.add('animate__fadeOutRight'); } else { notification.style.transform = 'translateX(100%)'; notification.style.opacity = '0'; } setTimeout(() => notification.remove(), 1000); } }, 6000 + index * 500);
             }, index * 300);
         });
    }

    // --- Leaderboard Fetch (Modified for Table) ---
    function fetchLeaderboard() {
        // Show skeleton loader rows
        leaderboardList.innerHTML = Array(5).fill(0).map(() => `
            <tr class="skeleton-item">
                <td><div class="rank-skel"></div></td>
                <td><div class="name-skel"></div></td>
                <td class="score"><div class="score-skel"></div></td>
            </tr>
        `).join('');

        fetch('get_leaderboard.php?game=blood_cell_defenders&limit=10')
        .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok.'))
        .then(data => {
            leaderboardList.innerHTML = ''; // Clear loader
            if (data.length === 0) {
                leaderboardList.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-gray-500 dark:text-gray-400 py-4">
                            No scores recorded yet. Be the first!
                        </td>
                    </tr>`;
            } else {
                data.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    // Highlight current user
                    if (userLoggedIn && item.user_id == userId) {
                        tr.classList.add('current-user');
                    }
                    // Use innerHTML to create cells easily
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${item.name ? item.name : 'Anonymous'}</td>
                        <td>${item.score}</td>
                    `;
                    leaderboardList.appendChild(tr);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching leaderboard:', error);
            leaderboardList.innerHTML = `
                 <tr>
                     <td colspan="3" class="text-center text-red-500 dark:text-red-400 py-4">
                         <i class="fas fa-exclamation-triangle mr-2"></i>Could not load leaderboard.
                     </td>
                 </tr>`;
        });
    }

    // --- Event Listeners (Keep as is) ---
    startButton.addEventListener('click', () => { startScreen.classList.add('hidden'); gameScreen.classList.remove('hidden'); initializeGame(); });
    pauseButton.addEventListener('click', () => { if (isPaused) resumeGame(); else pauseGame(); });
    resumeButton.addEventListener('click', resumeGame);
    restartButton.addEventListener('click', restartGame);
    quitButton.addEventListener('click', quitGame);
    playAgainBtn.addEventListener('click', () => { resultsScreen.classList.add('hidden'); startScreen.classList.remove('hidden'); }); // Go to start screen
    if (saveScoreBtn) { saveScoreBtn.addEventListener('click', saveScore); }
    gameCanvas.addEventListener('click', (e) => { if (!gameRunning || isPaused) return; const rect = gameCanvas.getBoundingClientRect(); const x = e.clientX - rect.left; const y = e.clientY - rect.top; placeTower(x, y); });

    // --- Initial Load ---
    renderTowerSelection();
    fetchLeaderboard();

    // --- Resize Handler (Keep as is) ---
    let resizeTimeout;
    window.addEventListener('resize', () => {
         clearTimeout(resizeTimeout);
         resizeTimeout = setTimeout(() => {
             if (gameRunning || gameScreen.offsetParent !== null) { // Recalculate if game is running OR visible
                 console.log("Window resized, recalculating path.");
                 calculatePath();
                 if(pathContainer) pathContainer.innerHTML = ''; // Clear old SVG
                 drawPath(); // Redraw SVG path
             }
         }, 250);
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>