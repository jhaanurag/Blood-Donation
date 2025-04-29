<?php
/**
 * Badges Handler
 * 
 * This file handles the logic for user badges and achievements
 */

require_once 'db.php';

/**
 * Check and award donation badges based on donation count
 * 
 * @param int $user_id The user's ID
 * @return array Newly awarded badges, if any
 */
function check_donation_badges($user_id) {
    global $conn;
    
    // Count user's completed donations
    $query = "SELECT COUNT(*) as donation_count FROM appointments 
              WHERE user_id = ? AND status = 'completed'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $donation_count = $row['donation_count'];
    
    // Get donation badges
    $badge_query = "SELECT id, name, requirement_count FROM badges 
                   WHERE badge_type = 'donation' AND requirement_count <= ?
                   ORDER BY requirement_count ASC";
    $stmt = mysqli_prepare($conn, $badge_query);
    mysqli_stmt_bind_param($stmt, "i", $donation_count);
    mysqli_stmt_execute($stmt);
    $badges_result = mysqli_stmt_get_result($stmt);
    
    $new_badges = [];
    
    while ($badge = mysqli_fetch_assoc($badges_result)) {
        // Check if user already has this badge
        $check_query = "SELECT * FROM user_badges 
                       WHERE user_id = ? AND badge_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            // Award new badge
            $award_query = "INSERT INTO user_badges (user_id, badge_id) 
                          VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $award_query);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
            mysqli_stmt_execute($stmt);
            
            $new_badges[] = $badge;
        }
    }
    
    return $new_badges;
}

/**
 * Check and award knowledge badges based on game score
 * 
 * @param int $user_id The user's ID
 * @param int $score The game score (out of 10)
 * @param string $game_type The type of game played
 * @return array Newly awarded badges, if any
 */
function check_knowledge_badges($user_id, $score, $game_type = '') {
    global $conn;
    
    $new_badges = [];
    
    // If game_type is specified, check for game-specific badges
    if (!empty($game_type)) {
        $badge_query = "";
        
        if ($game_type === 'blood_cell_defenders') {
            // Check for Blood Cell Defenders badges
            $badge_query = "SELECT id, name, requirement_count FROM badges 
                        WHERE name LIKE 'Cell Defender%' AND requirement_count <= ?
                        ORDER BY requirement_count ASC";
        } elseif ($game_type === 'blood_word_guess') {
            // Check for Blood Word Guess badges
            $badge_query = "SELECT id, name, requirement_count FROM badges 
                        WHERE name LIKE 'Word Guess%' AND requirement_count <= ?
                        ORDER BY requirement_count ASC";
        }
        
        // If we have a specific game type query, run it
        if (!empty($badge_query)) {
            $stmt = mysqli_prepare($conn, $badge_query);
            mysqli_stmt_bind_param($stmt, "i", $score);
            mysqli_stmt_execute($stmt);
            $badges_result = mysqli_stmt_get_result($stmt);
            
            while ($badge = mysqli_fetch_assoc($badges_result)) {
                // Check if user already has this badge
                $check_query = "SELECT * FROM user_badges 
                              WHERE user_id = ? AND badge_id = ?";
                $stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
                mysqli_stmt_execute($stmt);
                $check_result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($check_result) == 0) {
                    // Award new badge
                    $award_query = "INSERT INTO user_badges (user_id, badge_id) 
                                  VALUES (?, ?)";
                    $stmt = mysqli_prepare($conn, $award_query);
                    mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
                    mysqli_stmt_execute($stmt);
                    
                    $new_badges[] = $badge;
                }
            }
        }
    }
    
    // Always check for general knowledge badges as well
    $badge_query = "SELECT id, name, requirement_count FROM badges 
                   WHERE badge_type = 'knowledge' AND name NOT LIKE 'Cell Defender%' 
                   AND name NOT LIKE 'Word Guess%' AND requirement_count <= ?
                   ORDER BY requirement_count ASC";
    $stmt = mysqli_prepare($conn, $badge_query);
    mysqli_stmt_bind_param($stmt, "i", $score);
    mysqli_stmt_execute($stmt);
    $badges_result = mysqli_stmt_get_result($stmt);
    
    while ($badge = mysqli_fetch_assoc($badges_result)) {
        // Check if user already has this badge
        $check_query = "SELECT * FROM user_badges 
                       WHERE user_id = ? AND badge_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            // Award new badge
            $award_query = "INSERT INTO user_badges (user_id, badge_id) 
                          VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $award_query);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
            mysqli_stmt_execute($stmt);
            
            $new_badges[] = $badge;
        }
    }
    
    return $new_badges;
}

/**
 * Track and award referral badges
 * 
 * @param int $user_id The user's ID
 * @param int $referred_count Number of people referred
 * @return array Newly awarded badges, if any
 */
function check_referral_badges($user_id, $referred_count) {
    global $conn;
    
    // Get referral badges that the user qualifies for
    $badge_query = "SELECT id, name, requirement_count FROM badges 
                   WHERE badge_type = 'referral' AND requirement_count <= ?
                   ORDER BY requirement_count ASC";
    $stmt = mysqli_prepare($conn, $badge_query);
    mysqli_stmt_bind_param($stmt, "i", $referred_count);
    mysqli_stmt_execute($stmt);
    $badges_result = mysqli_stmt_get_result($stmt);
    
    $new_badges = [];
    
    while ($badge = mysqli_fetch_assoc($badges_result)) {
        // Check if user already has this badge
        $check_query = "SELECT * FROM user_badges 
                       WHERE user_id = ? AND badge_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            // Award new badge
            $award_query = "INSERT INTO user_badges (user_id, badge_id) 
                          VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $award_query);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $badge['id']);
            mysqli_stmt_execute($stmt);
            
            $new_badges[] = $badge;
        }
    }
    
    return $new_badges;
}

/**
 * Get all badges for a user
 * 
 * @param int $user_id The user's ID
 * @return array User's badges
 */
function get_user_badges($user_id) {
    global $conn;
    
    $query = "SELECT b.id, b.name, b.description, b.badge_type, b.icon, ub.earned_date 
              FROM badges b
              JOIN user_badges ub ON b.id = ub.badge_id
              WHERE ub.user_id = ?
              ORDER BY ub.earned_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $badges = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $badges[] = $row;
    }
    
    return $badges;
}

/**
 * Get all available badges in the system
 * 
 * @return array All system badges
 */
function get_all_badges() {
    global $conn;
    
    $query = "SELECT id, name, description, badge_type, icon, requirement_count 
              FROM badges 
              ORDER BY badge_type, requirement_count";
    $result = mysqli_query($conn, $query);
    
    $badges = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $badges[] = $row;
    }
    
    return $badges;
}
?>