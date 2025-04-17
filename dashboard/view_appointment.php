<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';


if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to view your appointments.";
    header("Location: login.php");
    exit;
}

$donor_id = $_SESSION['donor_id'];
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header("Location: dashboard/donor.php");
    exit;
}


$query = "SELECT a.*, bc.title as camp_title, bc.location as camp_location, bc.city as camp_city, bc.state as camp_state 
          FROM appointments a
          LEFT JOIN blood_camps bc ON DATE(a.appointment_date) = DATE(bc.date) AND (bc.city = (SELECT city FROM users WHERE id = ?) AND bc.state = (SELECT state FROM users WHERE id = ?))
          WHERE a.id = ? AND a.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $donor_id, $donor_id, $appointment_id, $donor_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: dashboard/donor.php");
    exit;
}

$appointment = $result->fetch_assoc();


$appointment_date = date("F j, Y", strtotime($appointment['appointment_date']));


$status_class = '';
switch ($appointment['status']) {
    case 'pending':
        $status_class = 'bg-yellow-100 text-yellow-800';
        $status_icon = '<i class="fas fa-clock mr-1"></i>';
        break;
    case 'approved':
        $status_class = 'bg-green-100 text-green-800';
        $status_icon = '<i class="fas fa-check-circle mr-1"></i>';
        break;
    case 'completed':
        $status_class = 'bg-blue-100 text-blue-800';
        $status_icon = '<i class="fas fa-check-double mr-1"></i>';
        break;
    default:
        $status_class = 'bg-gray-100 text-gray-800';
        $status_icon = '<i class="fas fa-info-circle mr-1"></i>';
}

// Check if appointment exists and belongs to the logged-in donor
if (!$appointment || $appointment['user_id'] !== $donor_id) {
    $_SESSION['error'] = "Appointment not found or you don't have permission to view it.";
    header("Location: " . DASHBOARD_URL . "/donor.php");
    exit;
}

include_once '../includes/header.php';
?>

<div class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Appointment Details</h1>
                <a href="<?php echo DASHBOARD_URL; ?>/donor.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
            
            <?php echo display_alerts(); ?>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gray-50 p-4 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">Appointment #<?php echo $appointment_id; ?></h2>
                        <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $status_class; ?>">
                            <?php echo $status_icon; ?> <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600">Appointment Date</p>
                            <p class="font-semibold text-lg"><?php echo $appointment_date; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created On</p>
                            <p class="font-semibold"><?php echo date("F j, Y", strtotime($appointment['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($appointment['camp_title'])): ?>
                        <div class="bg-blue-50 p-4 rounded mb-6">
                            <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($appointment['camp_title']); ?></h3>
                            <p class="mb-1">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php echo htmlspecialchars($appointment['camp_location']); ?>
                            </p>
                            <p>
                                <i class="fas fa-city mr-2"></i>
                                <?php echo htmlspecialchars($appointment['camp_city']) . ', ' . htmlspecialchars($appointment['camp_state']); ?>
                            </p>
                            <div class="mt-4">
                                <iframe
                                    width="100%"
                                    height="200"
                                    style="border:0"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q=<?= urlencode($appointment['camp_location'] . ', ' . $appointment['camp_city'] . ', ' . $appointment['camp_state']) ?>&output=embed&z=15">
                                </iframe>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-4">
                        <?php if ($appointment['status'] === 'approved'): ?>
                            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">
                                            <span class="font-semibold">Your appointment is confirmed.</span> Please arrive 15 minutes before your scheduled time and bring a valid ID.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($appointment['status'] === 'pending'): ?>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <span class="font-semibold">Your appointment is pending approval.</span> You will receive a confirmation once it's approved.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($appointment['status'] === 'completed'): ?>
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-double text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <span class="font-semibold">Thank you for your donation!</span> Your generosity has helped save lives.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="bg-gray-50 p-4 rounded">
                            <h3 class="font-semibold mb-2">Important Information</h3>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                                <li>Please bring a valid ID to your appointment</li>
                                <li>Have a meal 3-4 hours before donating blood</li>
                                <li>Stay hydrated by drinking plenty of water</li>
                                <li>Get adequate rest the night before</li>
                                <li>Avoid heavy lifting or strenuous exercise for 24 hours after donation</li>
                            </ul>
                        </div>
                        
                        <?php if ($appointment['status'] === 'pending'): ?>
                            <div class="mt-6">
                                <a href="dashboard/cancel_appointment.php?id=<?php echo $appointment_id; ?>" class="inline-block bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                    <i class="fas fa-times mr-1"></i> Cancel Appointment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>