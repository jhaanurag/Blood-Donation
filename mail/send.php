<?php
// Basic mail sending function
// In a production environment, consider using a library like PHPMailer for better features and reliability.

function send_email($to, $subject, $message, $from_email = 'alwaysoutsmartyou@gmail.com', $from_name = 'Blood Donation System') {
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    
    // Basic HTML template
    $full_message = "<html><body style='font-family: sans-serif;'>";
    $full_message .= "<div style='padding: 20px; border: 1px solid #eee; border-radius: 5px;'>";
    $full_message .= "<h2 style='color: #d9534f;'>" . htmlspecialchars($subject) . "</h2>";
    $full_message .= "<p>" . nl2br($message) . "</p>";
    $full_message .= "<hr style='border: none; border-top: 1px solid #eee;'>";
    $full_message .= "<p style='font-size: 0.9em; color: #777;'>This is an automated message from the Blood Donation System.</p>";
    $full_message .= "</div>";
    $full_message .= "</body></html>";

    // Use error suppression (@) as mail() can throw warnings if sendmail isn't configured properly
    // It's better to rely on the return value for success/failure
    if (@mail($to, $subject, $full_message, $headers)) {
        return true;
    } else {
        // Log error in a real application
        // error_log("Mail sending failed to: $to, Subject: $subject");
        return false;
    }
}
?>