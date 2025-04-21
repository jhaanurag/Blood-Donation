<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';
include_once '../includes/donation_streaks.php';
include_once '../includes/badges.php';

// Only admins should be able to update appointment status
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: appointments.php");
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// Validate inputs
if ($appointment_id <= 0 || !in_array($status, ['pending', 'approved', 'completed']) || $user_id <= 0) {
    $_SESSION['error'] = "Invalid input data.";
    header("Location: appointments.php");
    exit;
}

// Update appointment status
$update_query = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("si", $status, $appointment_id);

if (!$stmt->execute()) {
    $_SESSION['error'] = "Failed to update appointment status.";
    header("Location: appointments.php");
    exit;
}

// If appointment status is set to completed
if ($status === 'completed') {
    // Get appointment date
    $date_query = "SELECT appointment_date FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($date_query);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $donation_date = $appointment['appointment_date'];
    
    // Update user's last donation date
    $update_user_query = "UPDATE users SET last_donation_date = ? WHERE id = ?";
    $stmt = $conn->prepare($update_user_query);
    $stmt->bind_param("si", $donation_date, $user_id);
    $stmt->execute();
    
    // Update donation streaks
    $streak_info = update_donation_streak($user_id, $donation_date);
    
    // Check for donation badges
    $new_badges = check_donation_badges($user_id);
    
    // Set success message with streak information
    if ($streak_info['updated']) {
        $_SESSION['success'] = "Appointment marked as completed. Donation streak updated to {$streak_info['current_streak']}.";
        
        // Add badge information if any new badges were earned
        if (!empty($new_badges)) {
            $badge_names = array_column($new_badges, 'name');
            $_SESSION['success'] .= " New badge(s) earned: " . implode(', ', $badge_names);
        }
    } else {
        $_SESSION['success'] = "Appointment marked as completed.";
    }
} else {
    $_SESSION['success'] = "Appointment status updated to {$status}.";
}

// Redirect back to appointments page
header("Location: appointments.php");
exit;
?>