<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php';
include_once 'includes/auth.php';
include_once 'mail/send.php'; // Include the mail function

// If user is already logged in, redirect to dashboard
if (is_donor_logged_in()) {
    header("Location: " . BASE_URL . '/dashboard/donor.php');
    exit;
}

$errors = [];
$success = false;
$email = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }

    // Get email
    $email = trim($_POST['email'] ?? '');

    // Validate input
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // If no errors, proceed with password reset
    if (empty($errors)) {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate a 6-digit OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            
            // Store the OTP in the database
            // First, delete any existing tokens for this email
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            // Now insert the new token
            $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $email, $otp);
            
            if ($insert_stmt->execute()) {
                // Send the OTP via email
                $subject = "Password Reset OTP - Blood Donation System";
                $message = "Dear " . htmlspecialchars($user['name']) . ",<br><br>";
                $message .= "You have requested to reset your password. Please use the following OTP code to verify your identity:<br><br>";
                $message .= "<div style='text-align: center; padding: 15px; background-color: #f7f7f7; font-size: 24px; letter-spacing: 5px; font-weight: bold;'>{$otp}</div><br><br>";
                $message .= "This OTP is valid for 2 minutes only. If you did not request a password reset, please ignore this email.<br><br>";
                $message .= "Best regards,<br>The Blood Donation Team";
                
                if (send_email($email, $subject, $message)) {
                    $success = true;
                    $_SESSION['reset_email'] = $email; // Store email in session for OTP verification
                    $_SESSION['success'] = "An OTP has been sent to your email. Please check your inbox and enter the code to continue.";
                    // Redirect to OTP verification page
                    header("Location: verify_otp.php");
                    exit;
                } else {
                    $errors[] = "Failed to send OTP. Please try again later.";
                }
            } else {
                $errors[] = "Something went wrong. Please try again later.";
            }
            $insert_stmt->close();
        } else {
            $errors[] = "No account found with that email address.";
        }
        $stmt->close();
    }
}
?>

<div class="bg-gray-100 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-red-600">Forgot Password</h2>
                <a href="login.php" class="text-sm text-blue-600 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Login
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
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center mr-2">1</div>
                    <p class="font-semibold text-gray-700">Request OTP</p>
                </div>
                <div class="flex items-center mb-4 opacity-50">
                    <div class="w-8 h-8 rounded-full bg-gray-400 text-white flex items-center justify-center mr-2">2</div>
                    <p class="font-semibold text-gray-700">Verify OTP</p>
                </div>
                <div class="flex items-center opacity-50">
                    <div class="w-8 h-8 rounded-full bg-gray-400 text-white flex items-center justify-center mr-2">3</div>
                    <p class="font-semibold text-gray-700">Reset Password</p>
                </div>
            </div>

            <p class="mb-6 text-gray-600">Enter your email address below, and we'll send you an OTP to reset your password.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email Address</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600"
                           type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="mb-6">
                    <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50"
                            type="submit">Send OTP</button>
                </div>

                <div class="text-center text-gray-600">
                    <p class="mb-2">
                        Remember your password? <a href="login.php" class="text-red-600 hover:underline">Login here</a>
                    </p>
                    <p>
                        Don't have an account? <a href="register.php" class="text-red-600 hover:underline">Register here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?> 