<?php
// toggle_favorite.php - Handle favorite add/remove (Supports both desktop & mobile)
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$wallpaper_id = isset($_POST['wallpaper_id']) ? intval($_POST['wallpaper_id']) : 0;
// FIXED: Get type parameter (desktop or mobile)
$type = isset($_POST['type']) ? $_POST['type'] : 'desktop';

if (!$wallpaper_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid wallpaper ID']);
    exit();
}

// Validate type
if (!in_array($type, ['desktop', 'mobile'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid wallpaper type']);
    exit();
}

// Check if already favorited (using both id and type)
$check_sql = "SELECT id FROM favorites WHERE user_id = ? AND wallpaper_id = ? AND type = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iis", $user_id, $wallpaper_id, $type);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Remove from favorites
    $delete_sql = "DELETE FROM favorites WHERE user_id = ? AND wallpaper_id = ? AND type = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("iis", $user_id, $wallpaper_id, $type);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from favorites']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    $delete_stmt->close();
} else {
    // Add to favorites
    $insert_sql = "INSERT INTO favorites (user_id, wallpaper_id, type, created_at) VALUES (?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iis", $user_id, $wallpaper_id, $type);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to favorites']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
?>