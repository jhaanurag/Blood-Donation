<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php';
include_once 'includes/auth.php';
include_once 'mail/send.php'; // Include the mail function

$errors = [];
$success = false;
$requester_name = $blood_group = $city = $state = $message = '';

// Process request form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    // Validate input
    $requester_name = trim($_POST['requester_name'] ?? '');
    $blood_group = $_POST['blood_group'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($requester_name)) {
        $errors[] = "Name is required.";
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
    
    // If no errors, save the request
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO requests (requester_name, blood_group, city, state, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $requester_name, $blood_group, $city, $state, $message);
        
        if ($stmt->execute()) {
            $success = true;
            $new_request_id = $stmt->insert_id;
            
            // Find matching donors based on blood group, city, and state who are eligible
            $donor_query = "SELECT id, name, email FROM users 
                           WHERE blood_group = ? AND city = ? AND state = ? 
                           AND (last_donation_date IS NULL OR last_donation_date <= DATE_SUB(CURDATE(), INTERVAL 3 MONTH))";
            $donor_stmt = $conn->prepare($donor_query);
            $donor_stmt->bind_param("sss", $blood_group, $city, $state);
            $donor_stmt->execute();
            $matching_donors = $donor_stmt->get_result();
            
            // Send email to matching donors
            if ($matching_donors->num_rows > 0) {
                $subject = "Urgent Blood Request Matching Your Profile";
                $email_message = "Dear Donor,<br><br>An urgent request for <strong>" . htmlspecialchars($blood_group) . "</strong> blood has been made in your area (<strong>" . htmlspecialchars($city) . ", " . htmlspecialchars($state) . "</strong>).<br><br>";
                $email_message .= "Requester: " . htmlspecialchars($requester_name) . "<br>";
                $email_message .= "Message: " . nl2br(htmlspecialchars($message)) . "<br><br>";
                $email_message .= "If you are available and willing to help, please log in to your dashboard to view the request details and respond.<br><br>";
                $email_message .= "<a href='http://" . $_SERVER['HTTP_HOST'] . "/dashboard/help_request.php?id=" . $new_request_id . "'>Click here to view the request</a><br><br>";
                $email_message .= "Thank you for being a potential lifesaver!<br><br>Best regards,<br>The Blood Donation Team";
                
                while ($donor = $matching_donors->fetch_assoc()) {
                    send_email($donor['email'], $subject, $email_message);
                }
            }
            
            // Clear form data
            $requester_name = $blood_group = $city = $state = $message = '';
        } else {
            $errors[] = "Request submission failed. Please try again later.";
        }
        $stmt->close(); // Close the statement here
    }
}
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-center text-red-600 dark:text-red-400 mb-6">Request Blood</h1>
            
            <?php if ($success): ?>
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative mb-6" role="alert">
                <p class="font-bold">Request Submitted Successfully!</p>
                <p>We'll search for matching donors in your area and contact you soon.</p>
            </div>
            <?php endif; ?>
            
            <?php 
            if (!empty($errors)) {
                echo '<div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative mb-6" role="alert">';
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
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="requester_name">Your Full Name</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white" 
                           type="text" name="requester_name" id="requester_name" value="<?php echo htmlspecialchars($requester_name); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="blood_group">Blood Group Needed</label>
                    <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white" 
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
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="city">City</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white" 
                           type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="state">State</label>
                    <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white" 
                           type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="message">Additional Details</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 dark:bg-gray-800 dark:text-white" 
                              name="message" id="message" rows="4"><?php echo htmlspecialchars($message); ?></textarea>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Please provide any additional information that might help (urgency, hospital name, etc.)</p>
                </div>
                
                <div class="flex justify-center">
                    <button class="bg-red-600 dark:bg-red-700 text-white py-2 px-6 rounded-md hover:bg-red-700 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50" 
                            type="submit">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>