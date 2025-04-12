<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';


if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to access your profile.";
    header("Location: /login.php");
    exit;
}

$donor_id = $_SESSION['donor_id'];
$errors = [];
$success = false;


$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    
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
    
    
    if (!empty($current_password)) {
        
        if (!password_verify($current_password, $donor['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        
        
        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
    }
    
    
    if (empty($errors)) {
        
        $update_query = "UPDATE users SET name = ?, phone = ?, age = ?, blood_group = ?, city = ?, state = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssisssi", $name, $phone, $age, $blood_group, $city, $state, $donor_id);
        $update_result = $update_stmt->execute();
        
        
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
            $_SESSION['donor_name'] = $name; 
            $_SESSION['success'] = "Your profile has been updated successfully.";
            header("Location: /dashboard/donor.php");
            exit;
        } else {
            $errors[] = "Failed to update your profile. Please try again.";
        }
    }
}

include_once '../includes/header.php';
?>

<div class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Edit Your Profile</h1>
                <a href="/dashboard/donor.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
            
            <?php echo display_alerts(); ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <?php 
                if (!empty($errors)) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
                    echo '<ul class="list-disc list-inside">';
                    foreach ($errors as $error) {
                        echo '<li>' . htmlspecialchars($error) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
                
                if ($success) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">';
                    echo '<p>Your profile has been updated successfully.</p>';
                    echo '</div>';
                }
                ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Full Name</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="text" name="name" id="name" value="<?php echo htmlspecialchars($donor['name']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email Address</label>
                        <input class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-md" 
                               type="email" id="email" value="<?php echo htmlspecialchars($donor['email']); ?>" disabled>
                        <p class="text-sm text-gray-500 mt-1">Email cannot be changed. Contact support if needed.</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone Number</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($donor['phone']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="age">Age</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="number" name="age" id="age" min="18" value="<?php echo htmlspecialchars($donor['age']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="blood_group">Blood Group</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
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
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="city">City</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="text" name="city" id="city" value="<?php echo htmlspecialchars($donor['city']); ?>" required>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="state">State</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="text" name="state" id="state" value="<?php echo htmlspecialchars($donor['state']); ?>" required>
                    </div>
                    
                    <hr class="my-8">
                    
                    <h3 class="font-bold text-lg mb-4">Change Password (Optional)</h3>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="current_password">Current Password</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="password" name="current_password" id="current_password">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">New Password</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="password" name="new_password" id="new_password">
                        <p class="text-sm text-gray-500 mt-1">Min. 6 characters</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">Confirm New Password</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="password" name="confirm_password" id="confirm_password">
                    </div>
                    
                    <div>
                        <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50" 
                                type="submit">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>