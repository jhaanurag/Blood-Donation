<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php';
include_once 'includes/auth.php';


if (is_donor_logged_in()) {
    header("Location: /dashboard/donor.php");
    exit;
}

$errors = [];
$email = '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            
            if (password_verify($password, $user['password'])) {
                
                $_SESSION['donor_id'] = $user['id'];
                $_SESSION['donor_name'] = $user['name'];
                $_SESSION['donor_email'] = $user['email'];
                
                $_SESSION['success'] = "Login successful! Welcome back.";
                header("Location: /dashboard/donor.php");
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>

<div class="bg-gray-100 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-red-600 mb-6">Login to Your Donor Account</h2>
            
            <?php echo display_alerts(); ?>
            
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
            ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email Address</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="password" name="password" id="password" required>
                </div>
                
                <div class="mb-6">
                    <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50" 
                            type="submit">Login</button>
                </div>
                
                <div class="text-center text-gray-600">
                    <p class="mb-2">
                        Don't have an account? <a href="/register.php" class="text-red-600 hover:underline">Register here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>