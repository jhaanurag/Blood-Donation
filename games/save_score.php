<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/badges.php'; // Added badges functionality

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['donor_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if all required fields are present
if (!isset($_POST['score']) || !isset($_POST['user_id']) || !isset($_POST['game'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$score = intval($_POST['score']);
$user_id = intval($_POST['user_id']);
$game = mysqli_real_escape_string($conn, $_POST['game']);

// Validate user_id matches session
if ($user_id != $_SESSION['donor_id']) {
    echo json_encode(['success' => false, 'error' => 'Invalid user']);
    exit;
}

// First check if the game_scores table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'game_scores'");
if (mysqli_num_rows($table_check) == 0) {
    // Table doesn't exist, create it
    $create_table_query = "CREATE TABLE IF NOT EXISTS `game_scores` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `game_type` varchar(50) NOT NULL,
        `score` int(11) NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `game_type` (`game_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (!mysqli_query($conn, $create_table_query)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Error creating game_scores table: ' . mysqli_error($conn)
        ]);
        exit;
    }
}

try {
    // Check if we already have a score for this user and game
    $check_query = "SELECT * FROM game_scores WHERE user_id = ? AND game_type = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "is", $user_id, $game);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $scoreUpdated = false;
    $newBadges = [];

    if (mysqli_num_rows($result) > 0) {
        // Update existing score if new score is higher
        $existing_score = mysqli_fetch_assoc($result);
        if ($score > $existing_score['score']) {
            $update_query = "UPDATE game_scores SET score = ?, updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "ii", $score, $existing_score['id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $scoreUpdated = true;
                $message = 'Score updated';
            } else {
                throw new Exception("Update failed: " . mysqli_stmt_error($stmt));
            }
        } else {
            $message = 'Existing score is higher';
        }
    } else {
        // Insert new score
        $insert_query = "INSERT INTO game_scores (user_id, game_type, score, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = mysqli_prepare($conn, $insert_query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "isi", $user_id, $game, $score);
        
        if (mysqli_stmt_execute($stmt)) {
            $scoreUpdated = true;
            $message = 'Score saved';
        } else {
            throw new Exception("Insert failed: " . mysqli_stmt_error($stmt));
        }
    }
    
    // Check for knowledge badges if score was updated or newly inserted
    if ($scoreUpdated) {
        $newBadges = check_knowledge_badges($user_id, $score);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'new_badges' => $newBadges
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>