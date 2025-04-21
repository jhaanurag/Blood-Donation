<?php
include_once '../includes/db.php';

header('Content-Type: application/json');

// Check if game parameter is provided
if (!isset($_GET['game'])) {
    echo json_encode(['error' => 'Game type not specified']);
    exit;
}

$game = mysqli_real_escape_string($conn, $_GET['game']);

// Query to get top 10 scores for the specific game
$query = "SELECT gs.user_id, gs.score, gs.updated_at, u.name 
          FROM game_scores gs
          JOIN users u ON gs.user_id = u.id
          WHERE gs.game_type = ?
          ORDER BY gs.score DESC, gs.updated_at ASC
          LIMIT 10";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $game);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$leaderboard = [];

while ($row = mysqli_fetch_assoc($result)) {
    $leaderboard[] = [
        'user_id' => $row['user_id'],
        'name' => $row['name'],
        'score' => $row['score'],
        'date' => $row['updated_at']
    ];
}

echo json_encode($leaderboard);
mysqli_close($conn);
?>