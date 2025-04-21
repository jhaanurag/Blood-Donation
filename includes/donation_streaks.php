<?php
/**
 * Donation Streaks Handler
 * 
 * This file handles the logic for donor streaks and updating streak counters
 */

require_once 'db.php';

/**
 * Update user streak when a donation is completed
 * 
 * @param int $user_id The user's ID
 * @param string $donation_date The date of donation in Y-m-d format
 * @return array Containing the updated streak information
 */
function update_donation_streak($user_id, $donation_date) {
    global $conn;

    // Check if user has a streak record
    $check_query = "SELECT * FROM donation_streaks WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $streak_info = [];
    $streak_updated = false;
    
    if (mysqli_num_rows($result) > 0) {
        // User has streak record - update it
        $streak = mysqli_fetch_assoc($result);
        $last_donation = $streak['last_donation_date'];
        $current_streak = $streak['current_streak'];
        $longest_streak = $streak['longest_streak'];
        
        // Calculate date difference
        $last_date = new DateTime($last_donation);
        $current_date = new DateTime($donation_date);
        $interval = $last_date->diff($current_date);
        $days_diff = $interval->days;
        
        // If donation is within 4 months (~120 days) of last donation, increase streak
        if ($days_diff <= 120 && $days_diff > 0) {
            $current_streak++;
            $longest_streak = max($longest_streak, $current_streak);
            $streak_updated = true;
        } else if ($days_diff > 120) {
            // Reset streak if more than 4 months
            $current_streak = 1;
            $streak_updated = true;
        }
        
        // Update streak in database
        $update_query = "UPDATE donation_streaks 
                         SET current_streak = ?, longest_streak = ?, last_donation_date = ? 
                         WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "iisi", $current_streak, $longest_streak, $donation_date, $user_id);
        mysqli_stmt_execute($stmt);
    } else {
        // First donation - create streak record
        $current_streak = 1;
        $longest_streak = 1;
        
        $insert_query = "INSERT INTO donation_streaks 
                        (user_id, current_streak, longest_streak, last_donation_date) 
                        VALUES (?, 1, 1, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $donation_date);
        mysqli_stmt_execute($stmt);
        $streak_updated = true;
    }
    
    $streak_info = [
        'current_streak' => $current_streak,
        'longest_streak' => $longest_streak,
        'updated' => $streak_updated
    ];
    
    return $streak_info;
}

/**
 * Get user's current donation streak info
 * 
 * @param int $user_id The user's ID
 * @return array Containing the user's streak information
 */
function get_donation_streak($user_id) {
    global $conn;
    
    $query = "SELECT current_streak, longest_streak, last_donation_date 
              FROM donation_streaks 
              WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return [
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_donation_date' => null
        ];
    }
}
?>