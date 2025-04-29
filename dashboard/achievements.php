<?php
include_once '../includes/header.php';
include_once '../includes/db.php';
include_once '../includes/badges.php';
include_once '../includes/donation_streaks.php';

// Check if user is logged in
if (!isset($_SESSION['donor_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['donor_id'];
$user_name = $_SESSION['donor_name'];

// Get user badges
$user_badges = get_user_badges($user_id);

// Get user streak info
$streak_info = get_donation_streak($user_id);

// Get all badges to show locked ones too
$all_badges = get_all_badges();

// Create an array of earned badge IDs for easy checking
$earned_badge_ids = array_column($user_badges, 'id');

// Group badges by type for display
$badge_groups = [
    'donation' => ['title' => 'Donation Badges', 'badges' => []],
    'knowledge' => ['title' => 'Knowledge Badges', 'badges' => []],
    'referral' => ['title' => 'Referral Badges', 'badges' => []]
];

// Sort all badges into groups
foreach ($all_badges as $badge) {
    $badge['earned'] = in_array($badge['id'], $earned_badge_ids);
    
    // Get earned date for earned badges
    if ($badge['earned']) {
        foreach ($user_badges as $user_badge) {
            if ($user_badge['id'] == $badge['id']) {
                $badge['earned_date'] = $user_badge['earned_date'];
                break;
            }
        }
    }
    
    $badge_groups[$badge['badge_type']]['badges'][] = $badge;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Achievements</h1>
        <a href="donor.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">Donation Streak</h2>
        
        <div class="flex flex-col md:flex-row items-center justify-center gap-8 mb-6">
            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-6 text-center flex-1">
                <div class="text-5xl font-bold text-red-600 dark:text-red-400"><?php echo $streak_info['current_streak']; ?></div>
                <div class="text-gray-600 dark:text-gray-400 mt-2">Current Streak</div>
            </div>
            
            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-6 text-center flex-1">
                <div class="text-5xl font-bold text-red-600 dark:text-red-400"><?php echo $streak_info['longest_streak']; ?></div>
                <div class="text-gray-600 dark:text-gray-400 mt-2">Longest Streak</div>
            </div>
        </div>
        
        <?php if ($streak_info['last_donation_date']): ?>
            <p class="text-center text-gray-600 dark:text-gray-400">
                Last donation: <?php echo date('F j, Y', strtotime($streak_info['last_donation_date'])); ?>
            </p>
            
            <?php
            // Calculate next donation eligibility (56 days after last donation)
            $last_date = new DateTime($streak_info['last_donation_date']);
            $next_eligible_date = clone $last_date;
            $next_eligible_date->add(new DateInterval('P56D'));
            $today = new DateTime();
            $days_until_eligible = $today > $next_eligible_date ? 0 : $today->diff($next_eligible_date)->days;
            ?>
            
            <?php if ($days_until_eligible > 0): ?>
                <p class="text-center text-gray-600 dark:text-gray-400 mt-2">
                    You can donate again in <span class="font-bold text-red-600 dark:text-red-400"><?php echo $days_until_eligible; ?></span> days.
                </p>
            <?php else: ?>
                <p class="text-center font-bold text-green-600 dark:text-green-400 mt-2">
                    You are eligible to donate now!
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center text-gray-600 dark:text-gray-400">
                You haven't made a donation yet. Start your donation streak today!
            </p>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 gap-8">
        <?php foreach ($badge_groups as $type => $group): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200"><?php echo $group['title']; ?></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($group['badges'] as $badge): ?>
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 flex items-start hover:shadow-md transition-shadow <?php echo !$badge['earned'] ? 'opacity-50' : ''; ?>">
                            <div class="mr-3">
                                <?php if (!empty($badge['icon'])): ?>
                                    <img src="../assets/badges/<?php echo htmlspecialchars($badge['icon']); ?>" 
                                         alt="<?php echo htmlspecialchars($badge['name']); ?> icon" 
                                         class="w-12 h-12 object-contain"
                                         onerror="this.src='../assets/badges/default-badge.svg'">
                                <?php else: ?>
                                    <!-- Default badge icon based on badge type -->
                                    <?php
                                    $iconClass = 'fas fa-award text-yellow-500';
                                    if ($type === 'donation') {
                                        $iconClass = 'fas fa-tint text-red-500';
                                    } elseif ($type === 'knowledge') {
                                        $iconClass = 'fas fa-brain text-blue-500';
                                    } elseif ($type === 'referral') {
                                        $iconClass = 'fas fa-user-friends text-green-500';
                                    }
                                    ?>
                                    <div class="w-12 h-12 flex items-center justify-center">
                                        <i class="<?php echo $iconClass; ?> text-3xl"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$badge['earned']): ?>
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
                                
                                <?php if ($badge['earned']): ?>
                                    <p class="text-xs text-green-600 dark:text-green-400 mt-2">
                                        <i class="fas fa-check-circle mr-1"></i> Earned on: 
                                        <?php echo date('F j, Y', strtotime($badge['earned_date'])); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <i class="fas fa-lock mr-1"></i> Locked
                                        <?php if (!empty($badge['requirement_count'])): ?>
                                            • Requires: <?php
                                                if ($type === 'donation') {
                                                    echo htmlspecialchars($badge['requirement_count']) . ' donations';
                                                } elseif ($type === 'knowledge') {
                                                    echo 'Score of ' . htmlspecialchars($badge['requirement_count']) . '/10';
                                                } elseif ($type === 'referral') {
                                                    echo htmlspecialchars($badge['requirement_count']) . ' referrals';
                                                }
                                            ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add tooltip functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(el => {
        const tooltipText = el.getAttribute('data-tooltip');
        
        el.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip fixed bg-gray-900 text-white p-2 rounded text-sm z-50 max-w-xs';
            tooltip.textContent = tooltipText;
            document.body.appendChild(tooltip);
            
            // Position the tooltip
            const rect = el.getBoundingClientRect();
            tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
            tooltip.style.left = `${rect.left + window.scrollX}px`;
            
            // Make sure tooltip doesn't go off-screen
            const tooltipRect = tooltip.getBoundingClientRect();
            if (tooltipRect.right > window.innerWidth) {
                tooltip.style.left = `${window.innerWidth - tooltipRect.width - 10}px`;
            }
            
            el._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (el._tooltip) {
                el._tooltip.remove();
                delete el._tooltip;
            }
        });
    });
    
    // Badge hover effects
    const badgeCards = document.querySelectorAll('.badge-card');
    badgeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('opacity-60')) {
                this.classList.add('transform', 'scale-105', 'shadow-lg');
            }
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('transform', 'scale-105', 'shadow-lg');
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>