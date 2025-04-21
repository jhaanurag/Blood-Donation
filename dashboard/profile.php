<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';
include_once '../includes/badges.php';  // Added badges inclusion

// Check if user is logged in
if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to access your profile.";
    header("Location: login.php");
    exit;
}

$donor_id = $_SESSION['donor_id'];
$errors = [];
$success = false;

// Get user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

// Get user earned badges
$user_badges = get_user_badges($donor_id);
// Create an array of earned badge IDs for easy checking
$earned_badge_ids = array_column($user_badges, 'id');

// Get all available badges in the system
$all_badges = get_all_badges();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $blood_group = $_POST['blood_group'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    
    if (empty($age) || $age < 18) {
        $errors[] = "Age must be 18 or above.";
    }
    
    if (empty($blood_group)) {
        $errors[] = "Blood group is required.";
    }
    
    if (empty($city)) {
        $errors[] = "City is required.";
    }
    
    if (empty($state)) {
        $errors[] = "State is required.";
    }
    
    // If changing password, validate it
    if (!empty($current_password)) {
        // Verify current password
        if (!password_verify($current_password, $donor['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        
        // Validate new password
        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
    }
    
    // If no errors, update user profile
    if (empty($errors)) {
        // Start with basic profile update
        $update_query = "UPDATE users SET name = ?, phone = ?, age = ?, blood_group = ?, city = ?, state = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssisssi", $name, $phone, $age, $blood_group, $city, $state, $donor_id);
        $update_result = $update_stmt->execute();
        
        // If password is being changed, update it
        if (!empty($current_password) && !empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pwd_update_query = "UPDATE users SET password = ? WHERE id = ?";
            $pwd_update_stmt = $conn->prepare($pwd_update_query);
            $pwd_update_stmt->bind_param("si", $hashed_password, $donor_id);
            $pwd_update_result = $pwd_update_stmt->execute();
            
            if (!$pwd_update_result) {
                $errors[] = "Failed to update password. Please try again.";
            }
        }
        
        if ($update_result) {
            $success = true;
            $_SESSION['donor_name'] = $name; // Update session name
            $_SESSION['success'] = "Your profile has been updated successfully.";
            header("Location: donor.php");
            exit;
        } else {
            $errors[] = "Failed to update your profile. Please try again.";
        }
    }
}

include_once '../includes/header.php';
?>

<div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold dark:text-gray-100">Edit Your Profile</h1>
                <a href="donor.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
            
            <?php echo display_alerts(); ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <?php 
                if (!empty($errors)) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-900/50 dark:border-red-700 dark:text-red-200" role="alert">';
                    echo '<ul class="list-disc list-inside">';
                    foreach ($errors as $error) {
                        echo '<li>' . htmlspecialchars($error) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
                
                if ($success) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-900/50 dark:border-green-700 dark:text-green-200" role="alert">';
                    echo '<p>Your profile has been updated successfully.</p>';
                    echo '</div>';
                }
                ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="name">Full Name</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="text" name="name" id="name" value="<?php echo htmlspecialchars($donor['name']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="email">Email Address</label>
                        <input class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-md dark:bg-gray-600 dark:border-gray-700 dark:text-gray-200" 
                               type="email" id="email" value="<?php echo htmlspecialchars($donor['email']); ?>" disabled>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Email cannot be changed. Contact support if needed.</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="phone">Phone Number</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($donor['phone']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="age">Age</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="number" name="age" id="age" min="18" value="<?php echo htmlspecialchars($donor['age']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="blood_group">Blood Group</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                                name="blood_group" id="blood_group" required>
                            <option value="A+" <?php echo $donor['blood_group'] === 'A+' ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo $donor['blood_group'] === 'A-' ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo $donor['blood_group'] === 'B+' ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo $donor['blood_group'] === 'B-' ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo $donor['blood_group'] === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo $donor['blood_group'] === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo $donor['blood_group'] === 'O+' ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo $donor['blood_group'] === 'O-' ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="city">City</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="text" name="city" id="city" value="<?php echo htmlspecialchars($donor['city']); ?>" required>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="state">State</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="text" name="state" id="state" value="<?php echo htmlspecialchars($donor['state']); ?>" required>
                    </div>
                    
                    <hr class="my-8 dark:border-gray-600">
                    
                    <h3 class="font-bold text-lg mb-4 dark:text-gray-200">Change Password (Optional)</h3>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="current_password">Current Password</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="password" name="current_password" id="current_password">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="new_password">New Password</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="password" name="new_password" id="new_password">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Min. 6 characters</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="confirm_password">Confirm New Password</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                               type="password" name="confirm_password" id="confirm_password">
                    </div>
                    
                    <div>
                        <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50 dark:bg-red-700 dark:hover:bg-red-800" 
                                type="submit">Update Profile</button>
                    </div>
                </form>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mt-6">
                <h3 class="font-bold text-lg mb-4 dark:text-gray-200">Your Badges</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($all_badges as $badge): 
                        $is_earned = in_array($badge['id'], $earned_badge_ids);
                        $badge_earned_date = '';
                        
                        // Get earned date for earned badges
                        if ($is_earned) {
                            foreach ($user_badges as $user_badge) {
                                if ($user_badge['id'] == $badge['id']) {
                                    $badge_earned_date = $user_badge['earned_date'];
                                    break;
                                }
                            }
                        }
                    ?>
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 flex items-start hover:shadow-md transition-shadow <?php echo !$is_earned ? 'opacity-50' : ''; ?>">
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
                                    if ($badge['badge_type'] === 'donation') {
                                        $iconClass = 'fas fa-tint text-red-500';
                                    } elseif ($badge['badge_type'] === 'knowledge') {
                                        $iconClass = 'fas fa-brain text-blue-500';
                                    } elseif ($badge['badge_type'] === 'referral') {
                                        $iconClass = 'fas fa-user-friends text-green-500';
                                    }
                                    ?>
                                    <div class="w-12 h-12 flex items-center justify-center">
                                        <i class="<?php echo $iconClass; ?> text-3xl"></i>
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
                                            • Requires: <?php
                                                if ($badge['badge_type'] === 'donation') {
                                                    echo htmlspecialchars($badge['requirement_count']) . ' donations';
                                                } elseif ($badge['badge_type'] === 'knowledge') {
                                                    echo 'Score of ' . htmlspecialchars($badge['requirement_count']) . '/10';
                                                } elseif ($badge['badge_type'] === 'referral') {
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
        </div>
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
});
</script>

<?php include_once '../includes/footer.php'; ?>