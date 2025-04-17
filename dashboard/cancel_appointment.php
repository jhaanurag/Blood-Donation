<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';

// Check if user is logged in
if (!is_donor_logged_in()) {
    $_SESSION['error'] = "Please login to cancel appointments.";
    header("Location: /login.php");
    exit;
}

$donor_id = $_SESSION['donor_id'];
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if appointment ID is valid
if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header("Location: /dashboard/donor.php");
    exit;
}

// Get appointment details and make sure it belongs to the logged-in user and is in pending status
$query = "SELECT * FROM appointments WHERE id = ? AND user_id = ? AND status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $appointment_id, $donor_id);
$stmt->execute();
$result = $stmt->get_result();

// If appointment not found, doesn't belong to user, or is not in pending status
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Appointment not found or cannot be cancelled.";
    header("Location: /dashboard/donor.php");
    exit;
}

// Process cancellation
$delete_query = "DELETE FROM appointments WHERE id = ? AND user_id = ? AND status = 'pending'";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("ii", $appointment_id, $donor_id);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = "Your appointment has been cancelled successfully.";
} else {
    $_SESSION['error'] = "Failed to cancel your appointment. Please try again later.";
}

// Redirect back to dashboard
header("Location: /dashboard/donor.php");
exit;
?>