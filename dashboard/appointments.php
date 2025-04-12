<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';
include_once '../mail/send.php'; 


if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to book an appointment.";
    header("Location: /login.php");
    exit;
}

$donor_id = $_SESSION['donor_id'];
$errors = [];
$success = false;


$eligibility_query = "SELECT last_donation_date FROM users WHERE id = ?";
$eligibility_stmt = $conn->prepare($eligibility_query);
$eligibility_stmt->bind_param("i", $donor_id);
$eligibility_stmt->execute();
$eligibility_result = $eligibility_stmt->get_result();
$donor_data = $eligibility_result->fetch_assoc();

$is_eligible = true;
$days_until_eligible = 0;

if (!empty($donor_data['last_donation_date'])) {
    $last_donation = new DateTime($donor_data['last_donation_date']);
    $today = new DateTime();
    $diff = $last_donation->diff($today);
    $days_since_donation = $diff->days;
    
    
    if ($days_since_donation < 90) {
        $is_eligible = false;
        $days_until_eligible = 90 - $days_since_donation;
    }
}


$camp_id = isset($_GET['camp_id']) ? intval($_GET['camp_id']) : 0;
$camp_data = null;

if ($camp_id > 0) {
    $camp_query = "SELECT * FROM blood_camps WHERE id = ?";
    $camp_stmt = $conn->prepare($camp_query);
    $camp_stmt->bind_param("i", $camp_id);
    $camp_stmt->execute();
    $camp_result = $camp_stmt->get_result();
    if ($camp_result->num_rows > 0) {
        $camp_data = $camp_result->fetch_assoc();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    
    if (!$is_eligible) {
        $errors[] = "You are not eligible to donate blood at this time. Please wait $days_until_eligible more days.";
    } else {
        
        $appointment_date = $_POST['appointment_date'] ?? '';
        
        if (empty($appointment_date)) {
            $errors[] = "Please select an appointment date.";
        } else {
            
            $selected_date = new DateTime($appointment_date);
            $today = new DateTime();
            
            if ($selected_date <= $today) {
                $errors[] = "Appointment date must be in the future.";
            }
        }
        
        
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("is", $donor_id, $appointment_date);
            
            if ($stmt->execute()) {
                $success = true;
                
                
                $donor_email_query = "SELECT email, name FROM users WHERE id = ?";
                $donor_email_stmt = $conn->prepare($donor_email_query);
                $donor_email_stmt->bind_param("i", $donor_id);
                $donor_email_stmt->execute();
                $donor_email_result = $donor_email_stmt->get_result();
                $donor_info = $donor_email_result->fetch_assoc();
                
                if ($donor_info) {
                    $subject = "Appointment Received - Blood Donation System";
                    $formatted_date = date("F j, Y", strtotime($appointment_date));
                    $message = "Dear " . htmlspecialchars($donor_info['name']) . ",<br><br>We have received your request for a blood donation appointment on <strong>" . $formatted_date . "</strong>.<br><br>Your appointment is currently pending approval. You will receive another email once it is confirmed.<br><br>Thank you for your willingness to donate!<br><br>Best regards,<br>The Blood Donation Team";
                    send_email($donor_info['email'], $subject, $message);
                }
                
                $_SESSION['success'] = "Your appointment has been scheduled. You will be notified once it's confirmed.";
                header("Location: /dashboard/donor.php");
                exit;
            } else {
                $errors[] = "Failed to schedule your appointment. Please try again later.";
            }
        }
    }
}


$camps_query = "SELECT * FROM blood_camps WHERE date >= CURDATE() ORDER BY date ASC";
$camps_result = mysqli_query($conn, $camps_query);

include_once '../includes/header.php';
?>

<div class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Book Blood Donation Appointment</h1>
                <a href="/dashboard/donor.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
            
            <?php echo display_alerts(); ?>
            
            <?php if (!$is_eligible): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>You are not currently eligible to donate blood.</strong>
                                For health reasons, donors must wait at least 3 months between donations.
                                You will be eligible to donate again in <?php echo $days_until_eligible; ?> days.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
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
                ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <?php if ($camp_data): ?>
                        <div class="mb-6 bg-blue-50 p-4 rounded">
                            <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($camp_data['title']); ?></h3>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date("F j, Y", strtotime($camp_data['date'])); ?></p>
                            <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($camp_data['location']); ?></p>
                            <p><strong>City:</strong> <?php echo htmlspecialchars($camp_data['city']) . ', ' . htmlspecialchars($camp_data['state']); ?></p>
                            <input type="hidden" name="appointment_date" value="<?php echo $camp_data['date']; ?>">
                        </div>
                    <?php else: ?>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="appointment_date">Select Appointment Date</label>
                            <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                                   type="date" name="appointment_date" id="appointment_date" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   required <?php echo $is_eligible ? '' : 'disabled'; ?>>
                            <p class="text-sm text-gray-600 mt-1">Please select a date for your blood donation appointment.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-6">
                        <p class="text-sm text-gray-700 mb-2"><strong>Important Information:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                            <li>Please bring a valid ID to your appointment</li>
                            <li>Eat a healthy meal before donating</li>
                            <li>Stay hydrated by drinking plenty of water</li>
                            <li>Get adequate rest the night before</li>
                            <li>Your donation will take approximately 1 hour</li>
                        </ul>
                    </div>
                    
                    <div>
                        <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50" 
                                type="submit" <?php echo $is_eligible ? '' : 'disabled'; ?>>
                            Schedule Appointment
                        </button>
                        <?php if (!$is_eligible): ?>
                            <p class="text-sm text-red-600 mt-2 text-center">You are not currently eligible to make an appointment</p>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <?php if (!$camp_data && mysqli_num_rows($camps_result) > 0): ?>
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4">Upcoming Blood Donation Camps</h2>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php while ($camp = mysqli_fetch_assoc($camps_result)): ?>
                            <div class="border rounded p-4">
                                <h3 class="font-semibold"><?php echo htmlspecialchars($camp['title']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo date("F j, Y", strtotime($camp['date'])); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($camp['location']); ?></p>
                                <div class="mt-2">
                                    <a href="/dashboard/appointments.php?camp_id=<?php echo $camp['id']; ?>" class="text-blue-600 hover:underline">Select This Camp</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>