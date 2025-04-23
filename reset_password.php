<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/auth.php';

// If user is already logged in, redirect to dashboard
if (is_donor_logged_in()) {
    header("Location: " . BASE_URL . '/dashboard/donor.php');
    exit;
}

// If reset_email or otp_verified is not set in the session, redirect to forgot_password.php
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    $_SESSION['error'] = "Please complete OTP verification first.";
    header("Location: forgot_password.php");
    exit;
}

$errors = [];
$success = false;
$email = $_SESSION['reset_email'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }

    // Get passwords
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($new_password)) {
        $errors[] = "New password is required.";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If no errors, update password
    if (empty($errors)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Password updated successfully
            $success = true;
            
            // Delete the password reset token
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            // Clear the session variables
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            
            // Set success message
            $_SESSION['success'] = "Your password has been reset successfully. You can now log in with your new password.";
            
            // Redirect to login page
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Failed to update password. Please try again later.";
        }
        $stmt->close();
    }
}

// Include header AFTER all possible redirects
include_once 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-red-600 dark:text-red-400">Reset Password</h2>
                <a href="verify_otp.php" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Verify OTP
                </a>
            </div>

            <?php echo display_alerts(); ?>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <div class="flex items-center mb-4 opacity-50">
                    <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center mr-2">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Request OTP</p>
                </div>
                <div class="flex items-center mb-4 opacity-50">
                    <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center mr-2">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Verify OTP</p>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center mr-2">3</div>
                    <p class="font-semibold text-gray-700 dark:text-gray-200">Reset Password</p>
                </div>
            </div>

            <p class="mb-6 text-gray-600 dark:text-gray-300">Please enter your new password below for account <strong><?php echo htmlspecialchars($email); ?></strong>.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="new_password">New Password</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:focus:ring-red-400"
                           type="password" name="new_password" id="new_password" minlength="6" required>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Password must be at least 6 characters long.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="confirm_password">Confirm Password</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:focus:ring-red-400"
                           type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <div class="mb-6">
                    <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50"
                            type="submit">Reset Password</button>
                </div>
                
                <div class="text-center text-gray-600 dark:text-gray-300">
                    <p>
                        Remember your password? <a href="login.php" class="text-red-600 dark:text-red-400 hover:underline">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>