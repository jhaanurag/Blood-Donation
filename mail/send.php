<?php
// mail/send.php

/**
 * Basic mail sending function using PHP's mail().
 * 
 * IMPORTANT: This function relies on PHP's mail() function, which requires
 * a properly configured mail server (MTA) on the system where PHP is running,
 * or specific php.ini settings to use an external SMTP server. 
 * It often DOES NOT work reliably on standard local development environments 
 * without specific configuration.
 *
 * @param string $to Recipient email address.
 * @param string $subject Email subject.
 * @param string $message Email body content (HTML is supported).
 * @param string $from_email Sender email address.
 * @param string $from_name Sender name.
 * @return bool True if mail() was accepted for delivery, False otherwise.
 */
function send_email($to, $subject, $message, $from_email = 'noreply@blooddonate.local', $from_name = 'Blood Donation System') {
    
    // Ensure basic headers are set for HTML email
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion(); // Optional but good practice

    // Basic HTML template enhancement
    $full_message = "<html><body style='font-family: sans-serif; padding: 15px; line-height: 1.6;'>";
    $full_message .= "<div style='max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>";
    $full_message .= "<div style='background-color: #d9534f; color: white; padding: 20px; text-align: center;'>";
    $full_message .= "<h1 style='margin: 0; font-size: 24px;'>" . htmlspecialchars($from_name) . "</h1>";
    $full_message .= "</div>";
    $full_message .= "<div style='padding: 25px;'>";
    $full_message .= "<h2 style='color: #d9534f; margin-top: 0;'>" . htmlspecialchars($subject) . "</h2>";
    $full_message .= "<p>" . nl2br($message) . "</p>"; // nl2br converts newlines in the message to <br>
    $full_message .= "</div>";
    $full_message .= "<div style='background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 0.9em; color: #6c757d;'>";
    $full_message .= "This is an automated message. Please do not reply directly.";
    $full_message .= "<br>© " . date('Y') . " " . htmlspecialchars($from_name);
    $full_message .= "</div>";
    $full_message .= "</div>";
    $full_message .= "</body></html>";

    // Attempt to send the email.
    // Removed the '@' suppression to see errors/warnings during local testing if mail fails.
    // Note: mail() returning true only means the mail was accepted for delivery by the system,
    // not that it was actually delivered.
    if (mail($to, $subject, $full_message, $headers)) {
        // Optional: Log success for debugging
        // error_log("Mail accepted for delivery to: $to, Subject: $subject");
        return true;
    } else {
        // Log error in a real application
        // error_log("PHP mail() function failed for: $to, Subject: $subject");
        return false;
    }
}
?>