<?php
session_start();

// Clear chat history
if (isset($_SESSION['chat_history'])) {
    unset($_SESSION['chat_history']);
}

// Redirect back to the chatbot
header('Location: index.php');
exit;
?>