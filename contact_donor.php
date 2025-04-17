<?php
// No direct access to includes/db.php, header/footer handle that
include_once 'includes/header.php'; // Starts session, includes db.php
include_once 'includes/auth.php';  // Includes authentication functions

// Require donor login to view this page
require_donor_login();

$target_donor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$donor_details = null;
$error_message = '';

if ($target_donor_id <= 0) {
    $error_message = "Invalid donor ID specified.";
} else {
    // Fetch the target donor's details (including contact info)
    $query = "SELECT name, email, phone, city, state, blood_group FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        error_log("Failed to prepare statement: " . $conn->error);
        $error_message = "An error occurred while fetching donor details.";
    } else {
        $stmt->bind_param("i", $target_donor_id);
        if (!$stmt->execute()) {
            error_log("Failed to execute statement: " . $stmt->error);
            $error_message = "An error occurred while fetching donor details.";
        } else {
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $donor_details = $result->fetch_assoc();
            } else {
                $error_message = "Donor not found or you do not have permission to view details."; // Added permission check idea
            }
        }
        $stmt->close();
    }
}

?>

<div class="bg-gray-100 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold text-center text-red-600 mb-6">Contact Donor</h1>

            <?php echo display_alerts(); // Display session alerts (e.g., if redirected here from login) ?>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
                <div class="text-center mt-4">
                    <a href="search.php" class="text-blue-600 hover:underline">« Back to Search</a>
                </div>
            <?php elseif ($donor_details): ?>
                <div class="mb-4 p-4 border rounded bg-gray-50">
                    <p class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($donor_details['name']); ?></p>
                    <p class="text-sm text-gray-600">
                        <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded font-semibold mr-2">
                            <?php echo htmlspecialchars($donor_details['blood_group']); ?>
                        </span>
                        <?php echo htmlspecialchars($donor_details['city']); ?>, <?php echo htmlspecialchars($donor_details['state']); ?>
                    </p>
                </div>

                <h2 class="text-xl font-semibold mb-3">Contact Information</h2>
                <div class="space-y-3 mb-6">
                    <p>
                       <i class="fas fa-envelope text-gray-600 mr-2"></i>
                       <strong>Email:</strong>
                       <a href="mailto:<?php echo htmlspecialchars($donor_details['email']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($donor_details['email']); ?></a>
                    </p>
                    <p>
                       <i class="fas fa-phone text-gray-600 mr-2"></i>
                       <strong>Phone:</strong>
                       <a href="tel:<?php echo htmlspecialchars($donor_details['phone']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($donor_details['phone']); ?></a>
                    </p>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 text-sm text-yellow-800">
                    <p><i class="fas fa-exclamation-triangle mr-2"></i><strong>Please Note:</strong> Use this contact information responsibly and solely for the purpose of coordinating blood donations. Respect the donor's privacy and do not misuse this information.</p>
                </div>

                <div class="text-center mt-6">
                    <a href="search.php" class="text-blue-600 hover:underline">« Back to Search</a>
                    <span class="mx-2 text-gray-400">|</span>
                    <a href="dashboard/donor.php" class="text-blue-600 hover:underline">My Dashboard »</a>
                </div>

            <?php else: // Should not happen if error_message is empty, but as a fallback ?>
                 <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Could not load donor details. Please try again.</span>
                </div>
                 <div class="text-center mt-4">
                    <a href="search.php" class="text-blue-600 hover:underline">« Back to Search</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>