<?php
$to = "jha.anurag2017@outlook.com";
$subject = "Test Email from PHP";
$message = "Hello from PHP on WSL!";
$headers = "From: alwaysoutsmartyou@gmail.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
?>
