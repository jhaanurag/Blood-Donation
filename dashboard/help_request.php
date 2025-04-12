<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';


if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to respond to blood requests.";
    header("Location: /login.php");
    exit;
}

$donor_id = $_SESSION['donor_id'];
$errors = [];
$success = false;
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$request_data = null;


if ($request_id <= 0) {
    $_SESSION['error'] = "Invalid request ID.";
    header("Location: /dashboard/donor.php");
    exit;
}


$donor_query = "SELECT * FROM users WHERE id = ?";
$donor_stmt = $conn->prepare($donor_query);
$donor_stmt->bind_param("i", $donor_id);
$donor_stmt->execute();
$donor_result = $donor_stmt->get_result();
$donor = $donor_result->fetch_assoc();


$request_query = "SELECT * FROM requests WHERE id = ?";
$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("i", $request_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();

if ($request_result->num_rows === 0) {
    $_SESSION['error'] = "Blood request not found.";
    header("Location: /dashboard/donor.php");
    exit;
}

$request_data = $request_result->fetch_assoc();


if ($donor['blood_group'] !== $request_data['blood_group']) {
    $_SESSION['error'] = "Your blood group does not match the requested blood group.";
    header("Location: /dashboard/donor.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    }
    
    $contact_message = trim($_POST['contact_message'] ?? '');
    
    if (empty($contact_message)) {
        $errors[] = "Please provide a message for the requester.";
    }
    
    
    if (empty($errors)) {
        
        $update_query = "UPDATE requests SET matched_donor_id = ?, status = 'contacted' WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $donor_id, $request_id);
        
        if ($update_stmt->execute()) {
            $success = true;
            
            
            
            
            $_SESSION['success'] = "You have volunteered to help with this blood request. Thank you for your generosity!";
            header("Location: /dashboard/donor.php");
            exit;
        } else {
            $errors[] = "Failed to process your request. Please try again later.";
        }
    }
}

include_once '../includes/header.php';
?>

<div class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Respond to Blood Request</h1>
                <a href="/dashboard/donor.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
            
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
            
            if ($success) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">';
                echo '<p class="font-bold">Thank you for your help!</p>';
                echo '<p>Your contact information has been shared with the requester. They will be in touch soon.</p>';
                echo '</div>';
            }
            ?>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-red-50 p-4 border-b">
                    <h2 class="text-xl font-bold">Blood Request Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Requester Name</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($request_data['requester_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Blood Group</p>
                            <p class="font-semibold">
                                <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($request_data['blood_group']); ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Location</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($request_data['city']) . ', ' . htmlspecialchars($request_data['state']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Request Date</p>
                            <p class="font-semibold"><?php echo date("F j, Y", strtotime($request_data['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Message from Requester</p>
                        <div class="mt-1 p-3 bg-gray-50 rounded">
                            <?php echo nl2br(htmlspecialchars($request_data['message'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($request_data['status'] === 'pending'): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-50 p-4 border-b">
                        <h2 class="text-xl font-bold">Your Response</h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_message">
                                    Message to Requester
                                </label>
                                <textarea 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                                    name="contact_message" 
                                    id="contact_message" 
                                    rows="4" 
                                    placeholder="Introduce yourself and let them know you're willing to help. Include any questions or details about your availability."
                                    required
                                ></textarea>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded mb-6">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    By submitting this form, you agree to share your contact details with the requester. 
                                    They will be able to see your name, phone number, and email address to coordinate the blood donation.
                                </p>
                            </div>
                            
                            <div>
                                <button class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50" 
                                        type="submit">I Want To Help</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This request has already been matched with a donor.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>