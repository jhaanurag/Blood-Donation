<?php
// Start the session only if one doesn't already exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the config file (adjust path as needed)
require_once __DIR__ . '/../includes/config.php';

// Include necessary files
require_once INCLUDES_PATH . 'db.php';
require_once INCLUDES_PATH . 'auth.php';

// Check if user is logged in
if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to access your dashboard.";
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Get user details
$donor_id = $_SESSION['donor_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

// Get upcoming appointments
$appt_query = "SELECT * FROM appointments WHERE user_id = ? AND status != 'completed' ORDER BY appointment_date ASC";
$appt_stmt = $conn->prepare($appt_query);
$appt_stmt->bind_param("i", $donor_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

// Get donation history
$history_query = "SELECT * FROM appointments WHERE user_id = ? AND status = 'completed' ORDER BY appointment_date DESC";
$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $donor_id);
$history_stmt->execute();
$history = $history_stmt->get_result();

// Check for blood requests matching this donor's blood group
$request_query = "SELECT * FROM requests WHERE blood_group = ? AND city = ? AND state = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 3";
$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("sss", $donor['blood_group'], $donor['city'], $donor['state']);
$request_stmt->execute();
$matching_requests = $request_stmt->get_result();

// Get upcoming blood camps in donor's area
$camps_query = "SELECT * FROM blood_camps WHERE city = ? AND state = ? AND date >= CURDATE() ORDER BY date ASC LIMIT 3";
$camps_stmt = $conn->prepare($camps_query);
$camps_stmt->bind_param("ss", $donor['city'], $donor['state']);
$camps_stmt->execute();
$upcoming_camps = $camps_stmt->get_result();

include_once INCLUDES_PATH . 'header.php';

// Define paths for links
$dashboard_url = DASHBOARD_URL;
$dashboard_path = DASHBOARD_URL . '/';
?>

<div class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Dashboard Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Welcome, <?php echo htmlspecialchars($donor['name']); ?></h1>
                    <p class="text-gray-600 dark:text-gray-300">
                        <span class="inline-block bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100 px-2 py-1 rounded font-semibold mr-2">
                            <?php echo htmlspecialchars($donor['blood_group']); ?>
                        </span>
                        <span>
                            <?php echo htmlspecialchars($donor['city']) . ', ' . htmlspecialchars($donor['state']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="mt-4 md:mt-0">
                    <a href="<?php echo $dashboard_url; ?>/profile.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded mr-2">
                        <i class="fas fa-user mr-1"></i> Edit Profile
                    </a>
                    <a href="<?php echo $dashboard_url; ?>/appointments.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                        <i class="fas fa-calendar-plus mr-1"></i> Book Appointment
                    </a>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap -mx-3">
            <!-- Left Column -->
            <div class="w-full lg:w-8/12 px-3">
                
                <?php echo display_alerts(); ?>
                
                <!-- Upcoming Appointments Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <h2 class="text-xl font-bold dark:text-white">Your Upcoming Appointments</h2>
                    </div>
                    <div class="p-4">
                        <?php if ($appointments->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                            <tr class="dark:bg-gray-800 dark:text-gray-200">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo date("F j, Y", strtotime($appointment['appointment_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php
                                                    $status = $appointment['status'];
                                                    $status_class = '';
                                                    
                                                    switch ($status) {
                                                        case 'pending':
                                                            $status_class = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                            break;
                                                        case 'approved':
                                                            $status_class = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                            break;
                                                        default:
                                                            $status_class = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                                    }
                                                    ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <a href="<?php echo $dashboard_url; ?>/view_appointment.php?id=<?php echo $appointment['id']; ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">View</a>
                                                    <?php if ($appointment['status'] === 'pending'): ?>
                                                        <a href="<?php echo $dashboard_url; ?>/cancel_appointment.php?id=<?php echo $appointment['id']; ?>" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded" role="alert">
                                <p>You have no upcoming appointments. <a href="<?php echo $dashboard_url; ?>/appointments.php" class="font-bold underline hover:text-blue-800 dark:hover:text-blue-300">Book an appointment</a> to donate blood.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Donation History Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <h2 class="text-xl font-bold dark:text-white">Your Donation History</h2>
                    </div>
                    <div class="p-4">
                        <?php if ($history->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php while ($donation = $history->fetch_assoc()): ?>
                                            <tr class="dark:bg-gray-800 dark:text-gray-200">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo date("F j, Y", strtotime($donation['appointment_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <!-- Here you would show the location information if available -->
                                                    Blood Donation Center
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($donor['last_donation_date']): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                                    Last donation: <strong><?php echo date("F j, Y", strtotime($donor['last_donation_date'])); ?></strong>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-3 rounded" role="alert">
                                <p>You have no donation history yet. Make your first donation to save lives!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column / Sidebar -->
            <div class="w-full lg:w-4/12 px-3">
                <!-- Matching Blood Requests -->
                <?php if ($matching_requests->num_rows > 0): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6">
                        <div class="p-4 border-b border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900">
                            <h2 class="text-xl font-bold text-red-800 dark:text-red-200">Blood Requests Matching Your Type</h2>
                        </div>
                        <div class="p-4">
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while ($request = $matching_requests->fetch_assoc()): ?>
                                    <li class="py-3">
                                        <p class="font-semibold dark:text-white"><?php echo htmlspecialchars($request['requester_name']); ?> needs <?php echo htmlspecialchars($request['blood_group']); ?> blood</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($request['city']) . ', ' . htmlspecialchars($request['state']); ?></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo htmlspecialchars(substr($request['message'], 0, 100)) . (strlen($request['message']) > 100 ? '...' : ''); ?></p>
                                        <a href="<?php echo $dashboard_path; ?>help_request.php?id=<?php echo $request['id']; ?>" class="mt-2 inline-block bg-red-600 text-white text-sm px-3 py-1 rounded hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600">I Can Help</a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Upcoming Blood Camps Near You -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <h2 class="text-xl font-bold dark:text-white">Blood Camps Near You</h2>
                    </div>
                    <div class="p-4">
                        <?php if ($upcoming_camps->num_rows > 0): ?>
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while ($camp = $upcoming_camps->fetch_assoc()): ?>
                                    <li class="py-3">
                                        <p class="font-semibold dark:text-white"><?php echo htmlspecialchars($camp['title']); ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo date("F j, Y", strtotime($camp['date'])); ?></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($camp['location']); ?></p>
                                        <a href="<?php echo $dashboard_path; ?>appointments.php?camp_id=<?php echo $camp['id']; ?>" class="mt-2 inline-block bg-blue-600 text-white text-sm px-3 py-1 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600">Book Appointment</a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-700 dark:text-gray-300">No upcoming blood camps in your area at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Donor Tips -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <h2 class="text-xl font-bold dark:text-white">Donor Tips</h2>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-3 text-gray-700 dark:text-gray-300">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Stay hydrated before and after donation</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Eat iron-rich foods like spinach and red meat</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Avoid fatty foods before donating</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Get a good night's sleep before donating</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>