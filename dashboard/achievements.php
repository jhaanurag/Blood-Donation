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
$badges = get_user_badges($user_id);

// Get user streak info
$streak_info = get_donation_streak($user_id);

// Get all badges to show locked ones too
$all_badges = get_all_badges();

// Group badges by type for display
$badge_groups = [
    'donation' => ['title' => 'Donation Badges', 'badges' => []],
    'knowledge' => ['title' => 'Knowledge Badges', 'badges' => []],
    'referral' => ['title' => 'Referral Badges', 'badges' => []]
];

// Track which badges the user has
$earned_badge_ids = [];
foreach ($badges as $badge) {
    $earned_badge_ids[] = $badge['id'];
}

// Sort all badges into groups
foreach ($all_badges as $badge) {
    $badge['earned'] = in_array($badge['id'], $earned_badge_ids);
    $badge_groups[$badge['badge_type']]['badges'][] = $badge;
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8 text-gray-900 dark:text-white">My Achievements</h1>
    
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
        <?php foreach ($badge_groups as $group): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200"><?php echo $group['title']; ?></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($group['badges'] as $badge): ?>
                        <div class="relative group">
                            <div class="badge-card rounded-lg p-4 border-2 transition-all <?php echo $badge['earned'] ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 bg-gray-50 dark:bg-gray-700 opacity-60'; ?>">
                                <div class="flex items-center">
                                    <div class="badge-icon mr-4 flex items-center justify-center w-12 h-12 rounded-full <?php echo $badge['earned'] ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400'; ?>">
                                        <i class="fas <?php echo $badge['icon']; ?> text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100"><?php echo $badge['name']; ?></h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo $badge['description']; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!$badge['earned']): ?>
                                <div class="absolute top-0 right-0 m-2">
                                    <span class="bg-gray-700 text-white text-xs px-2 py-1 rounded-full">Locked</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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