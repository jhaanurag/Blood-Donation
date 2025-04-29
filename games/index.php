<?php
include_once '../includes/header.php';
include_once '../includes/db.php';

// Add model-viewer script to head
echo '<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/4.0.0/model-viewer.min.js"></script>';

// Check if the user is logged in
$user_logged_in = isset($_SESSION['donor_id']);
$user_name = $user_logged_in ? $_SESSION['donor_name'] : '';
$user_id = $user_logged_in ? $_SESSION['donor_id'] : '';
?>

<!-- particles.js container - commented out to disable animation -->
<!-- <div id="particles-js"></div> -->

<!-- stats - count particles - commented out to disable animation -->
<!-- <div class="count-particles">
    <span class="js-count-particles">--</span> particles
</div> -->

<style>
/* ---- particles.js styles commented out to disable animation ---- 
body {
    margin: 0;
    font: normal 75% Arial, Helvetica, sans-serif;
}
canvas {
    display: block;
    vertical-align: bottom;
}
#particles-js {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #043564;
    background-image: url("http://vincentgarreau.com/particles.js/assets/img/kbLd9vb_new.gif");
    background-repeat: no-repeat;
    background-size: 60%;
    background-position: 0 50%;
    z-index: 0;
}
.count-particles {
    background: #000022;
    position: absolute;
    top: 48px;
    left: 0;
    width: 80px;
    color: #13E8E9;
    font-size: .8em;
    text-align: left;
    text-indent: 4px;
    line-height: 14px;
    padding-bottom: 2px;
    font-family: Helvetica, Arial, sans-serif;
    font-weight: bold;
}
.js-count-particles {
    font-size: 1.1em;
}
#stats, .count-particles {
    -webkit-user-select: none;
    margin-top: 5px;
    margin-left: 5px;
}
#stats {
    border-radius: 3px 3px 0 0;
    overflow: hidden;
}
.count-particles {
    border-radius: 0 0 3px 3px;
}
}*/

/* 3D Barbarian Model Styles */
.header-container {
    position: relative;
    margin-bottom: 4rem;
}

.barbarian-model-container {
    position: absolute;
    width: 250px;  /* Increased from 150px */
    height: 250px; /* Increased from 150px */
    top: -40px;
    right: 50px;
    z-index: 10;
}

@media (max-width: 768px) {
    .barbarian-model-container {
        position: relative;
        width: 200px;  /* Increased from 120px */
        height: 200px; /* Increased from 120px */
        margin: 0 auto;
        top: 0;
        right: 0;
    }
}

model-viewer {
    width: 100%;
    height: 100%;
    --poster-color: transparent;
    background-color: transparent;
}

.progress-bar {
    display: block;
    width: 33%;
    height: 10%;
    max-height: 2%;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate3d(-50%, -50%, 0);
    border-radius: 25px;
    box-shadow: 0px 3px 10px 3px rgba(0, 0, 0, 0.5), 0px 0px 5px 1px rgba(0, 0, 0, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.9);
    background-color: rgba(0, 0, 0, 0.5);
}

.progress-bar.hide {
    visibility: hidden;
    transition: visibility 0.3s;
}

.update-bar {
    background-color: rgba(255, 255, 255, 0.9);
    width: 0%;
    height: 100%;
    border-radius: 25px;
    float: left;
    transition: width 0.3s;
}
</style>

<div class="py-8">
    <div class="container mx-auto px-4 relative z-10">
        <div class="header-container">
            <!-- 3D Barbarian Model -->
            <div class="barbarian-model-container">
                <model-viewer src="../barbarian/bk.glb" 
                    camera-controls
                    auto-rotate
                    shadow-intensity="1" 
                    background-color="transparent"
                    camera-target="0 1 0"
                    camera-orbit="45deg 60deg 3m"
                    disable-zoom
                    interaction-prompt="none">
                    <div class="progress-bar hide" slot="progress-bar">
                        <div class="update-bar"></div>
                    </div>
                </model-viewer>
            </div>
            
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-900 dark:text-white">Blood Donation Games</h1>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                Have fun while learning more about blood donation! Play our educational games and earn badges as you improve your knowledge.
            </p>
        </div>
        
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
        
        <?php if ($user_logged_in): ?>
        <!-- Achievements Section -->
        <div class="mt-12 mb-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-900 dark:text-white">Your Gaming Achievements</h2>
            
            <?php
            // Include badges helper
            include_once '../includes/badges.php';
            
            // Get user badges
            $user_badges = get_user_badges($user_id);
            
            // Filter for game-related badges (knowledge type)
            $game_badges = array_filter($user_badges, function($badge) {
                return $badge['badge_type'] === 'knowledge';
            });
            
            // Get all available badges
            $all_badges = get_all_badges();
            
            // Filter for knowledge badges
            $all_game_badges = array_filter($all_badges, function($badge) {
                return $badge['badge_type'] === 'knowledge';
            });
            
            // Create an array of earned badge IDs for easy checking
            $earned_badge_ids = array_column($game_badges, 'id');
            ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 max-w-5xl mx-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($all_game_badges as $badge): 
                        $is_earned = in_array($badge['id'], $earned_badge_ids);
                        $badge_earned_date = '';
                        
                        // Get earned date for earned badges
                        if ($is_earned) {
                            foreach ($game_badges as $user_badge) {
                                if ($user_badge['id'] == $badge['id']) {
                                    $badge_earned_date = $user_badge['earned_date'];
                                    break;
                                }
                            }
                        }
                    ?>
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 flex items-start hover:shadow-md transition-shadow <?php echo !$is_earned ? 'opacity-50' : ''; ?>">
                            <div class="mr-3 relative">
                                <?php if (!empty($badge['icon'])): ?>
                                    <img src="../assets/badges/<?php echo htmlspecialchars($badge['icon']); ?>" 
                                         alt="<?php echo htmlspecialchars($badge['name']); ?> icon" 
                                         class="w-12 h-12 object-contain"
                                         onerror="this.src='../assets/badges/default-badge.svg'">
                                <?php else: ?>
                                    <div class="w-12 h-12 flex items-center justify-center">
                                        <i class="fas fa-brain text-blue-500 text-3xl"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$is_earned): ?>
                                    <div class="absolute top-0 right-0 mt-1 mr-1">
                                        <i class="fas fa-lock text-gray-400 text-lg"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-grow">
                                <h4 class="font-bold text-gray-800 dark:text-gray-200">
                                    <span class="cursor-help" data-tooltip="<?php echo htmlspecialchars($badge['description']); ?>">
                                        <?php echo htmlspecialchars($badge['name']); ?>
                                        <i class="fas fa-info-circle text-xs text-gray-500 ml-1"></i>
                                    </span>
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                    <?php echo htmlspecialchars(substr($badge['description'], 0, 80) . (strlen($badge['description']) > 80 ? '...' : '')); ?>
                                </p>
                                
                                <?php if ($is_earned): ?>
                                    <p class="text-xs text-green-600 dark:text-green-400 mt-2">
                                        <i class="fas fa-check-circle mr-1"></i> Earned on: 
                                        <?php echo date('F j, Y', strtotime($badge_earned_date)); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <i class="fas fa-lock mr-1"></i> Locked
                                        <?php if (!empty($badge['requirement_count'])): ?>
                                            • Requires: Score of <?php echo htmlspecialchars($badge['requirement_count']); ?>/10
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($game_badges) > 0): ?>
                    <div class="text-center mt-6">
                        <a href="<?php echo $base_url; ?>dashboard/achievements.php" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition">
                            View All Achievements
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add tooltip functionality -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltips = document.querySelectorAll('[data-tooltip]');
            
            tooltips.forEach(el => {
                const tooltipText = el.getAttribute('data-tooltip');
                
                el.addEventListener('mouseenter', function() {
                    // Create tooltip element
                    const tooltipEl = document.createElement('div');
                    tooltipEl.classList.add('tooltip');
                    tooltipEl.innerText = tooltipText;
                    document.body.appendChild(tooltipEl);
                    
                    // Position tooltip
                    const rect = el.getBoundingClientRect();
                    tooltipEl.style.left = `${rect.left + window.scrollX}px`;
                    tooltipEl.style.top = `${rect.bottom + window.scrollY}px`;
                    
                    // Remove tooltip on mouseleave
                    el.addEventListener('mouseleave', function() {
                        tooltipEl.remove();
                    }, { once: true });
                });
            });
        });
        </script>
        <?php endif; ?>
        
        <!-- Script for handling the 3D model loading -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handles loading the events for model-viewer's slotted progress bar
                const onProgress = (event) => {
                    const progressBar = event.target.querySelector('.progress-bar');
                    const updatingBar = event.target.querySelector('.update-bar');
                    updatingBar.style.width = `${event.detail.totalProgress * 100}%`;
                    if (event.detail.totalProgress === 1) {
                        progressBar.classList.add('hide');
                        event.target.removeEventListener('progress', onProgress);
                    } else {
                        progressBar.classList.remove('hide');
                    }
                };
                
                const modelViewer = document.querySelector('model-viewer');
                if (modelViewer) {
                    modelViewer.addEventListener('progress', onProgress);
                }
            });
        </script>
    </div>
</div>

<!-- Add particles.js and stats.js scripts -->
<!-- Commented out to disable animation
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="https://threejs.org/examples/js/libs/stats.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize particles.js with the exact configuration requested
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 100,
                "density": {
                    "enable": false,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff"
            },
            "shape": {
                "type": "star",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                },
                "image": {
                    "src": "http://wiki.lexisnexis.com/academic/images/f/fb/Itunes_podcast_icon_300.jpg",
                    "width": 100,
                    "height": 100
                }
            },
            "opacity": {
                "value": 0.5,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 4,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": false,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.4,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 14,
                "direction": "left",
                "random": false,
                "straight": true,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": false,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "repulse"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 200,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });

    // Set up stats.js
    var count_particles, stats, update;
    stats = new Stats;
    stats.setMode(0);
    stats.domElement.style.position = 'absolute';
    stats.domElement.style.left = '0px';
    stats.domElement.style.top = '0px';
    document.body.appendChild(stats.domElement);
    count_particles = document.querySelector('.js-count-particles');
    update = function() {
        stats.begin();
        stats.end();
        if (window.pJSDom[0].pJS.particles && window.pJSDom[0].pJS.particles.array) {
            count_particles.innerText = window.pJSDom[0].pJS.particles.array.length;
        }
        requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
});
</script>
-->
<?php include_once '../includes/footer.php'; ?>