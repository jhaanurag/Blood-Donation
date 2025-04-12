<?php



function is_donor_logged_in() {
    return isset($_SESSION['donor_id']);
}


function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}


function require_donor_login() {
    if (!is_donor_logged_in()) {
        $_SESSION['error'] = "You must be logged in to view that page.";
        header("Location: /login.php");
        exit;
    }
}


function require_admin_login() {
    if (!is_admin_logged_in()) {
        $_SESSION['error'] = "You must be logged in as an admin to view that page.";
        header("Location: /admin_login.php");
        exit;
    }
}


function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}


function display_alerts() {
    $html = '';
    
    
    if (isset($_SESSION['success'])) {
        $html .= '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">';
        $html .= '<span class="block sm:inline">' . htmlspecialchars($_SESSION['success']) . '</span>';
        $html .= '</div>';
        unset($_SESSION['success']);
    }
    
    
    if (isset($_SESSION['error'])) {
        $html .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
        $html .= '<span class="block sm:inline">' . htmlspecialchars($_SESSION['error']) . '</span>';
        $html .= '</div>';
        unset($_SESSION['error']);
    }
    
    return $html;
}