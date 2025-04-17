<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php';
include_once 'includes/auth.php';
include_once 'mail/send.php'; // Include the mail function

// If user is already logged in, redirect to dashboard
if (is_donor_logged_in()) {
    header("Location: dashboard/donor.php");
    exit;
}

$errors = [];
$name = $email = $phone = $age = $blood_group = $city = $state = '';

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $blood_group = $_POST['blood_group'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
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
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered. Please login instead.";
    }
    $stmt->close();
    
    // If no errors, register the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, age, blood_group, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisss", $name, $email, $hashed_password, $phone, $age, $blood_group, $city, $state);
        
        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            $_SESSION['donor_id'] = $new_user_id;
            $_SESSION['donor_name'] = $name;
            $_SESSION['donor_email'] = $email;
            $_SESSION['success'] = "Registration successful! Welcome to the Blood Donation System.";
            
            // Send welcome email
            $subject = "Welcome to the Blood Donation System!";
            $message = "Dear " . htmlspecialchars($name) . ",<br><br>Thank you for registering as a blood donor. Your commitment helps save lives!<br><br>You can now log in to your dashboard to book appointments and manage your profile.<br><br>Best regards,<br>The Blood Donation Team";
            send_email($email, $subject, $message);
            
            header("Location: dashboard/donor.php");
            exit;
        } else {
            $errors[] = "Registration failed. Please try again later.";
        }
        $stmt->close();
    }
}
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-red-600 mb-6">Register as a Blood Donor</h2>
            
            <?php 
            // Display any errors
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
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Full Name</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email Address</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="password" name="password" id="password" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">Confirm Password</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="password" name="confirm_password" id="confirm_password" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone Number</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="age">Age</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="number" name="age" id="age" min="18" value="<?php echo htmlspecialchars($age); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="blood_group">Blood Group</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                            name="blood_group" id="blood_group" required>
                        <option value="" disabled <?php echo empty($blood_group) ? 'selected' : ''; ?>>Select Blood Group</option>
                        <option value="A+" <?php echo $blood_group === 'A+' ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo $blood_group === 'A-' ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo $blood_group === 'B+' ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo $blood_group === 'B-' ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo $blood_group === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo $blood_group === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo $blood_group === 'O+' ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo $blood_group === 'O-' ? 'selected' : ''; ?>>O-</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="city">City</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="state">State</label>
                    <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                           type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>" required>
                </div>
                
                <div class="mb-6">
                    <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50" 
                            type="submit">Register</button>
                </div>
                
                <p class="text-center text-gray-600">
                    Already have an account? <a href="login.php" class="text-red-600 hover:underline">Login here</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>