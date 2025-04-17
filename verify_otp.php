<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php';
include_once 'includes/auth.php';

// If user is already logged in, redirect to dashboard
if (is_donor_logged_in()) {
    header("Location: " . BASE_URL . '/dashboard/donor.php');
    exit;
}

// If reset_email is not set in the session, redirect to forgot_password.php
if (!isset($_SESSION['reset_email'])) {
    $_SESSION['error'] = "Please request a password reset first.";
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

    // Get OTP
    $otp = trim($_POST['otp'] ?? '');

    // Validate input
    if (empty($otp)) {
        $errors[] = "OTP is required.";
    } elseif (!preg_match('/^\d{6}$/', $otp)) {
        $errors[] = "OTP must be a 6-digit number.";
    }

    // If no errors, verify OTP
    if (empty($errors)) {
        // Check if OTP is valid
        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND created_at > (NOW() - INTERVAL 2 MINUTE)");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // OTP is valid, allow user to reset password
            $_SESSION['otp_verified'] = true;
            
            // Redirect to reset password page
            header("Location: reset_password.php");
            exit;
        } else {
            $errors[] = "Invalid or expired OTP. Please try again or request a new OTP.";
        }
        $stmt->close();
    }
}
?>

<div class="bg-gray-100 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-red-600">Verify OTP</h2>
                <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Request OTP
                </a>
            </div>

            <?php echo display_alerts(); ?>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
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
                    <p class="font-semibold text-gray-700">Request OTP</p>
                </div>
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center mr-2">2</div>
                    <p class="font-semibold text-gray-700">Verify OTP</p>
                </div>
                <div class="flex items-center opacity-50">
                    <div class="w-8 h-8 rounded-full bg-gray-400 text-white flex items-center justify-center mr-2">3</div>
                    <p class="font-semibold text-gray-700">Reset Password</p>
                </div>
            </div>

            <p class="mb-6 text-gray-600">An OTP has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>. Please enter the 6-digit code below.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="otp">OTP Code</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 text-center tracking-widest font-mono text-xl"
                           type="text" name="otp" id="otp" maxlength="6" pattern="\d{6}" placeholder="Enter 6-digit OTP" required>
                    <p class="text-xs text-gray-500 mt-1">The OTP is valid for 2 minutes.</p>
                </div>

                <div class="mb-6">
                    <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50"
                            type="submit">Verify OTP</button>
                </div>

                <div class="text-center text-gray-600">
                    <p class="mb-2">
                        Didn't receive the OTP? <a href="forgot_password.php" class="text-red-600 hover:underline">Request again</a>
                    </p>
                    <p>
                        Remember your password? <a href="login.php" class="text-red-600 hover:underline">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?> 