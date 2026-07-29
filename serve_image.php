<?php
// serve_image.php - Securely serve wallpaper images (UNLIMITED PREVIEWS)
session_start();
require_once 'includes/db.php';

$image_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$image_type = isset($_GET['type']) && $_GET['type'] === 'mobile' ? 'mobile' : 'desktop';

if ($image_id <= 0) {
    die('Invalid request.');
}

// Determine which table to query
$table = ($image_type === 'mobile') ? 'mobile_wallpapers' : 'desktop_wallpapers';
$stmt = $conn->prepare("SELECT image_path, title FROM $table WHERE id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Image not found.');
}

$wallpaper = $result->fetch_assoc();
$stmt->close();
$image_path = $wallpaper['image_path'];
$title = $wallpaper['title'];

// Clean the path and get the full filesystem path
$clean_image_path = str_replace('/wallhub/', '', $image_path);
if (!str_starts_with($clean_image_path, '/')) {
    $clean_image_path = '/' . $clean_image_path;
}
$full_file_path = $_SERVER['DOCUMENT_ROOT'] . $clean_image_path;

if (!file_exists($full_file_path)) {
    die('File not found.');
}

// ============================================================
// NO PREVIEW LIMITS - Users can see all wallpapers freely
// Only DOWNLOADS are limited (handled in download.php)
// ============================================================

// Optional: Add a subtle watermark for non-premium users (not a block)
$user_role = $_SESSION['role'] ?? 'guest';
$is_premium = ($user_role === 'admin' || $user_role === 'owner' || $user_role === 'premium');

// Serve the real image
$mime_type = mime_content_type($full_file_path);
header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline');
header('Cache-Control: public, max-age=86400');

// For non-premium users, we can serve the image with a subtle overlay (optional)
// This doesn't block viewing, just adds a small "WallHub" text
if (!$is_premium) {
    // You can add a subtle watermark using GD if you want
    // For now, just serve the original image
    readfile($full_file_path);
} else {
    readfile($full_file_path);
}
exit();
?>