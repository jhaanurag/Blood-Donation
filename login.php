<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php'; // Make sure header does NOT call session_start() again
include_once 'includes/auth.php';

// Get redirect URL if set from GET parameter first
$redirect_url = BASE_URL . '/dashboard/donor.php'; // Default redirect
if (isset($_GET['redirect'])) {
    // Basic validation: ensure it's a local path and not external
    $potential_redirect = $_GET['redirect'];
    if (!empty($potential_redirect) && $potential_redirect[0] === '/' && strpos($potential_redirect, '//') === false && !preg_match('/\.\./', $potential_redirect)) {
         // Use urldecode to handle encoded characters from the URL
        $redirect_url = urldecode($potential_redirect);
    }
}
// If it's a POST request, check hidden field (takes precedence if set)
elseif (isset($_POST['redirect'])) {
    $potential_redirect = $_POST['redirect'];
     if (!empty($potential_redirect) && $potential_redirect[0] === '/' && strpos($potential_redirect, '//') === false && !preg_match('/\.\./', $potential_redirect)) {
        $redirect_url = $potential_redirect; // No need to decode here
    }
}


// If user is already logged in, redirect to dashboard or intended page
if (is_donor_logged_in()) {
    header("Location: " . $redirect_url); // Use the determined redirect URL
    exit;
}

$errors = [];
$email = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }

    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($email)) {
        $errors[] = "Email is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // If no errors, attempt to login
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        if ($stmt) { // Check if prepare succeeded
             $stmt->bind_param("s", $email);
             $stmt->execute();
             $result = $stmt->get_result();

             if ($result->num_rows === 1) {
                 $user = $result->fetch_assoc();

                 // Verify password
                 if (password_verify($password, $user['password'])) {
                     // Regenerate session ID upon login for security
                     session_regenerate_id(true);

                     // Set session variables
                     $_SESSION['donor_id'] = $user['id'];
                     $_SESSION['donor_name'] = $user['name'];
                     $_SESSION['donor_email'] = $user['email'];
                     // Unset CSRF token after successful login might be good practice
                     unset($_SESSION['csrf_token']);

                     $_SESSION['success'] = "Login successful! Welcome back.";
                     // Use the redirect URL determined earlier
                     header("Location: " . $redirect_url);
                     exit;
                 } else {
                     $errors[] = "Invalid email or password.";
                 }
             } else {
                 $errors[] = "Invalid email or password.";
             }
             $stmt->close();
        } else {
             error_log("Login statement prepare failed: " . $conn->error);
             $errors[] = "An error occurred during login. Please try again.";
        }
    }
}
?>

<div class="bg-gray-100 dark:bg-gray-800 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-700 p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-red-600 dark:text-red-400 mb-6">Login to Your Donor Account</h2>

            <?php echo display_alerts(); ?>

            <?php
            // Display any errors
            if (!empty($errors)) {
                echo '<div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative mb-4" role="alert">';
                echo '<ul class="list-disc list-inside">';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <?php // Add hidden field to pass redirect URL during POST ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_url); ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="email">Email Address</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white"
                           type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="password">Password</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white"
                           type="password" name="password" id="password" required>
                    <p class="text-right text-sm mt-1"><a href="forgot_password.php" class="text-blue-600 dark:text-blue-400 hover:underline">Forgot Password?</a></p>
                </div>

                <div class="mb-6">
                    <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50"
                            type="submit">Login</button>
                </div>

                <div class="text-center text-gray-600 dark:text-gray-300">
                    <p class="mb-2">
                        Don't have an account? <a href="register.php" class="text-red-600 dark:text-red-400 hover:underline">Register here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>