<?php
// download.php - FULLY SECURE VERSION (No bypasses)
session_start();
include('includes/db.php');

// ============================================================
// ONLY GET THE ID FROM URL - NO PATHS, NO TITLES, NO FILENAMES
// ============================================================
$wallpaper_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) && $_GET['type'] === 'mobile' ? 'mobile' : 'desktop';

// Validate ID
if ($wallpaper_id <= 0) {
    die("Invalid wallpaper ID.");
}

// Determine which table to use
$table = ($type === 'mobile') ? 'mobile_wallpapers' : 'desktop_wallpapers';

// ============================================================
// FETCH ALL DATA FROM DATABASE USING ONLY THE ID
// ============================================================
$stmt = $conn->prepare("SELECT w.*, c.name as category_name, c.slug as category_slug 
                        FROM $table w
                        LEFT JOIN categories c ON w.category_id = c.id
                        WHERE w.id = ?");
$stmt->bind_param("i", $wallpaper_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Wallpaper not found.");
}

$wallpaper = $result->fetch_assoc();
$stmt->close();

// ============================================================
// 🆕 INCREMENT VIEWS WHEN SOMEONE VISITS THIS PAGE
// ============================================================
if (isset($wallpaper['id'])) {
    // Check if this wallpaper was viewed in this session
    $view_key = 'viewed_wallpaper_' . $wallpaper['id'];
    if (!isset($_SESSION[$view_key])) {
        $_SESSION[$view_key] = true;
        
        $update_views = $conn->prepare("UPDATE $table SET views = views + 1 WHERE id = ?");
        if ($update_views) {
            $update_views->bind_param("i", $wallpaper['id']);
            $update_views->execute();
            $update_views->close();
        }
    }
}
// ============================================================

// Get data from database (NOT from URL)
$image_path = $wallpaper['image_path'];
$title = $wallpaper['title'];
$character = $wallpaper['character_name'] ?? 'Wallpaper';
$category_slug = $wallpaper['category_slug'] ?? '';
$downloads = $wallpaper['downloads'] ?? 0;
$views = $wallpaper['views'] ?? 0;

// Clean image path (remove any /wallhub/ prefix if present)
$clean_image_path = str_replace('/wallhub/', '', $image_path);
if (!str_starts_with($clean_image_path, '/')) {
    $clean_image_path = '/' . $clean_image_path;
}

// Build the full file system path
$full_file_path = $_SERVER['DOCUMENT_ROOT'] . $clean_image_path;

// ============================================================
// FUNCTION: Check download limits
// ============================================================
function canDownload($conn, $user_id = null, $user_role = null) {
    // Admin, Owner, and Premium have unlimited downloads
    if ($user_role == 'admin' || $user_role == 'owner' || $user_role == 'premium') {
        return ['allowed' => true, 'message' => '', 'remaining' => 'Unlimited'];
    }
    
    $today = date('Y-m-d');
    
    if ($user_id) {
        // Logged in member
        $query = "SELECT daily_downloads, last_download_date FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!$user) {
            return ['allowed' => true, 'message' => '', 'remaining' => '10'];
        }
        
        // Reset if new day
        if ($user['last_download_date'] != $today) {
            $update = $conn->prepare("UPDATE users SET daily_downloads = 0, last_download_date = ? WHERE id = ?");
            $update->bind_param("si", $today, $user_id);
            $update->execute();
            $update->close();
            return ['allowed' => true, 'message' => '', 'remaining' => '10'];
        }
        
        $remaining = 10 - $user['daily_downloads'];
        if ($user['daily_downloads'] >= 10) {
            return ['allowed' => false, 'message' => 'You have reached your daily download limit (10/day). <a href="premium.php" style="color: #ffd166;">Upgrade to Premium</a> for unlimited downloads!', 'remaining' => 0];
        }
        
        return ['allowed' => true, 'message' => '', 'remaining' => $remaining];
    } else {
        // Guest user - track by session
        if (!isset($_SESSION['guest_downloads'])) {
            $_SESSION['guest_downloads'] = 0;
            $_SESSION['guest_download_date'] = $today;
        }
        
        // Reset if new day
        if ($_SESSION['guest_download_date'] != $today) {
            $_SESSION['guest_downloads'] = 0;
            $_SESSION['guest_download_date'] = $today;
        }
        
        $remaining = 5 - ($_SESSION['guest_downloads'] ?? 0);
        if ($_SESSION['guest_downloads'] >= 5) {
            return ['allowed' => false, 'message' => 'You have reached your daily download limit (5/day). <a href="register.php" style="color: #ffd166;">Register</a> for 10 downloads/day or <a href="login.php" style="color: #ffd166;">Login</a>!', 'remaining' => 0];
        }
        
        return ['allowed' => true, 'message' => '', 'remaining' => $remaining];
    }
}

// ============================================================
// FUNCTION: Increment download counters WITH DOWNLOAD LOGGING
// ============================================================
function incrementDownloadCount($conn, $wallpaper_id, $table, $user_id = null) {
    $today = date('Y-m-d');
    $ip = $_SERVER['REMOTE_ADDR'];
    $type = ($table === 'mobile_wallpapers') ? 'mobile' : 'desktop';
    
    if ($user_id) {
        // Update user's daily download count
        $update = $conn->prepare("UPDATE users SET daily_downloads = daily_downloads + 1, last_download_date = ? WHERE id = ?");
        $update->bind_param("si", $today, $user_id);
        $update->execute();
        $update->close();
        
        // Record download in user_downloads table
        $insert = $conn->prepare("INSERT INTO user_downloads (user_id, wallpaper_id, wallpaper_type, ip_address, download_date) VALUES (?, ?, ?, ?, NOW())");
        $insert->bind_param("iiss", $user_id, $wallpaper_id, $type, $ip);
        $insert->execute();
        $insert->close();
        
        // ============================================
        // LOG TO download_logs FOR ANALYTICS
        // ============================================
        $log_query = "INSERT INTO download_logs (wallpaper_id, wallpaper_type, user_id, ip_address) 
                      VALUES (?, ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        if ($log_stmt) {
            mysqli_stmt_bind_param($log_stmt, "isis", $wallpaper_id, $type, $user_id, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        // ============================================
        
    } else {
        // Guest download
        $_SESSION['guest_downloads'] = ($_SESSION['guest_downloads'] ?? 0) + 1;
        
        // ============================================
        // LOG GUEST DOWNLOAD TO download_logs
        // ============================================
        $log_query = "INSERT INTO download_logs (wallpaper_id, wallpaper_type, user_id, ip_address) 
                      VALUES (?, ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        if ($log_stmt) {
            $guest_id = NULL;
            mysqli_stmt_bind_param($log_stmt, "isis", $wallpaper_id, $type, $guest_id, $ip);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        // ============================================
    }
    
    // Increment wallpaper download count
    $updateWall = $conn->prepare("UPDATE $table SET downloads = downloads + 1 WHERE id = ?");
    $updateWall->bind_param("i", $wallpaper_id);
    $updateWall->execute();
    $updateWall->close();
}

// ============================================================
// CHECK DOWNLOAD LIMIT
// ============================================================
$downloadCheck = canDownload($conn, $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? null);

// ============================================================
// HANDLE DOWNLOAD REQUEST
// ============================================================
if (isset($_GET['download']) && $_GET['download'] == 1) {
    if (!$downloadCheck['allowed']) {
        $error_message = $downloadCheck['message'];
    } elseif (!file_exists($full_file_path)) {
        $error_message = "File not found. Please contact support.";
        error_log("Download.php - File not found: " . $full_file_path);
    } else {
        // Increment counters - WITH download logging
        incrementDownloadCount($conn, $wallpaper_id, $table, $_SESSION['user_id'] ?? null);
        
        // Force download
        $file_extension = pathinfo($full_file_path, PATHINFO_EXTENSION);
        $clean_title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title);
        $download_filename = 'wallhub.online - ' . $clean_title . '.' . $file_extension;
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $download_filename . '"');
        header('Content-Length: ' . filesize($full_file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Clear output buffer and send file
        ob_clean();
        flush();
        readfile($full_file_path);
        exit();
    }
}

// Get user download info for display
$remainingDownloads = $downloadCheck['remaining'] ?? null;
$userRole = $_SESSION['role'] ?? 'guest';

$pageTitle = $title ? "$title - WallHub" : "Download Wallpaper - WallHub";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@400;600;700&family=Raleway:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --red: #e8000d;
            --deep-red: #7a0000;
            --gold: #e8b923;
            --gold-light: #f5d060;
            --obsidian: #080808;
            --charcoal: #161616;
            --panel: #1c1c1c;
            --text: #f5f0e8;
            --muted: #888070;
            --glow-red: 0 0 40px rgba(232, 0, 13, 0.5);
            --glow-gold: 0 0 30px rgba(232, 185, 35, 0.4);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--obsidian);
            color: var(--text);
            font-family: 'Raleway', sans-serif;
            min-height: 100vh;
            padding-top: 80px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(232,0,13,0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .download-card {
            background: rgba(10, 10, 15, 0.7);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 40px;
            border: 1px solid rgba(232, 185, 35, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }
        
        .header-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--red), var(--gold));
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #000;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Cinzel', serif;
        }
        
        .limit-alert {
            background: linear-gradient(135deg, rgba(232,0,13,0.15), rgba(232,185,35,0.08));
            border: 1px solid var(--gold);
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .limit-alert.warning {
            background: linear-gradient(135deg, rgba(232,0,13,0.25), rgba(232,185,35,0.1));
            border-color: var(--red);
        }
        
        .limit-alert.success {
            background: linear-gradient(135deg, rgba(0,255,100,0.1), rgba(232,185,35,0.05));
            border-color: #00ff88;
        }
        
        .remaining-downloads {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--gold);
        }
        
        .image-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            margin-bottom: 30px;
            border: 1px solid rgba(232, 185, 35, 0.2);
            cursor: default;
        }
        
        /* PROTECTION: Disable right-click, drag, and selection on images */
        .preview-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background: #0a0a0f;
            pointer-events: none;
            user-select: none;
            -webkit-user-drag: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        /* PROTECTION: Overlay to block all clicks on image */
        .image-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 10;
            cursor: default;
        }
        
        /* PROTECTION: Watermark overlay on image */
        .image-container::after {
            content: 'WallHub';
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: var(--gold);
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-family: 'Cinzel', serif;
            letter-spacing: 1px;
            z-index: 15;
            pointer-events: none;
        }
        
        .info-section {
            background: linear-gradient(135deg, rgba(232,0,13,0.08), rgba(232,185,35,0.04));
            border-radius: 20px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid rgba(232,185,35,0.15);
        }
        
        .wallpaper-title {
            font-size: 2rem;
            font-weight: 800;
            font-family: 'Cinzel Decorative', serif;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
        }
        
        .character-name {
            font-size: 1.1rem;
            color: var(--gold);
            margin-bottom: 15px;
        }
        
        .download-btn-wrapper {
            text-align: center;
            margin: 40px 0;
        }
        
        .stunning-download-btn {
            position: relative;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: #000;
            padding: 20px 60px;
            font-size: 1.6rem;
            font-weight: 800;
            font-family: 'Cinzel', serif;
            border: none;
            border-radius: 80px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 35px rgba(232,185,35,0.4);
            overflow: hidden;
            letter-spacing: 2px;
            text-decoration: none;
        }
        
        .stunning-download-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            pointer-events: none;
        }
        
        .stunning-download-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .stunning-download-btn:hover::before {
            left: 100%;
        }
        
        .stunning-download-btn:hover:not(.disabled) {
            transform: scale(1.08) translateY(-5px);
            box-shadow: 0 25px 45px rgba(232,185,35,0.6);
        }
        
        .stunning-download-btn i {
            font-size: 1.8rem;
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 80px;
            background: rgba(232,185,35,0.4);
            animation: pulse 2s infinite;
            pointer-events: none;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(232,185,35,0.15);
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            border: 1px solid rgba(232,185,35,0.2);
        }
        
        .action-btn:hover {
            background: rgba(232,185,35,0.2);
            border-color: var(--gold);
            transform: translateY(-2px);
            color: var(--gold);
        }
        
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px 25px;
            background: rgba(255,255,255,0.03);
            border-radius: 15px;
            border: 1px solid rgba(232,185,35,0.1);
        }
        
        .stat-number {
            font-size: 1.6rem;
            font-weight: bold;
            color: var(--gold);
            font-family: 'Cinzel', serif;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: var(--muted);
        }
        
        .error-container {
            text-align: center;
            padding: 60px 20px;
        }
        
        .error-container i {
            font-size: 4rem;
            color: var(--red);
            margin-bottom: 20px;
        }
        
        .error-container h2 {
            font-family: 'Cinzel Decorative', serif;
            margin-bottom: 15px;
        }
        
        .protection-notice {
            text-align: center;
            margin-top: 15px;
            font-size: 0.7rem;
            color: var(--muted);
        }
        
        .protection-notice i {
            color: var(--gold);
            margin-right: 5px;
        }
        
        /* Actual image preview - shows real image */
        .actual-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background: #0a0a0f;
        }
        
        @media (max-width: 768px) {
            .download-card {
                padding: 25px;
            }
            .wallpaper-title {
                font-size: 1.5rem;
            }
            .stunning-download-btn {
                padding: 15px 40px;
                font-size: 1.2rem;
            }
            .stunning-download-btn i {
                font-size: 1.2rem;
            }
            .actual-image, .preview-image {
                max-height: 300px;
            }
            .action-btn {
                padding: 8px 18px;
                font-size: 0.85rem;
            }
            .stat-number {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="download-card">
                <div class="error-container">
                    <i class="fas fa-skull-crossbones"></i>
                    <h2>Download Failed</h2>
                    <p style="color: var(--muted); margin-bottom: 30px;"><?php echo $error_message; ?></p>
                    <div class="action-buttons" style="border-top: none;">
                        <a href="javascript:history.back()" class="action-btn">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="<?php echo ($type === 'mobile') ? 'mobile.php' : 'categories.php'; ?>" class="action-btn">
                            <i class="fas fa-image"></i> Browse Wallpapers
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="download-card">
                <div style="text-align: center;">
                    <span class="header-badge">
                        <i class="fas fa-crown"></i> PREMIUM WALLPAPER
                    </span>
                </div>
                
                <?php if (!$downloadCheck['allowed']): ?>
                <div class="limit-alert warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?php echo $downloadCheck['message']; ?>
                </div>
                <?php elseif ($remainingDownloads !== null && $remainingDownloads !== 'Unlimited' && $remainingDownloads <= 2): ?>
                <div class="limit-alert warning">
                    <i class="fas fa-clock"></i> 
                    <strong>Warning:</strong> You have only <span class="remaining-downloads"><?php echo $remainingDownloads; ?></span> downloads left today!
                    <?php if ($userRole == 'guest'): ?>
                    <a href="register.php" style="color: var(--gold);">Register</a> for 10 downloads/day!
                    <?php endif; ?>
                </div>
                <?php elseif ($remainingDownloads !== null && $remainingDownloads !== 'Unlimited'): ?>
                <div class="limit-alert success">
                    <i class="fas fa-check-circle"></i> 
                    You have <span class="remaining-downloads"><?php echo $remainingDownloads; ?></span> downloads remaining today.
                    <?php if ($userRole == 'member'): ?>
                    <i class="fas fa-star" style="color: var(--gold);"></i> Member: 10/day
                    <?php elseif ($userRole == 'premium'): ?>
                    <i class="fas fa-gem" style="color: var(--gold);"></i> Premium: Unlimited!
                    <?php elseif ($userRole == 'admin' || $userRole == 'owner'): ?>
                    <i class="fas fa-crown" style="color: var(--gold);"></i> Admin: Unlimited!
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="image-container">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                         alt="<?php echo htmlspecialchars($title); ?>" 
                         class="actual-image"
                         onerror="this.src='https://placehold.co/800x500/1a1a2e/e8b923?text=Wallpaper'">
                </div>
                
                <div class="protection-notice">
                    <i class="fas fa-shield-alt"></i> The download button has feelings too. 🥺
                </div>
                
                <div class="info-section">
                    <h1 class="wallpaper-title">
                        <i class="fas fa-image"></i> <?php echo htmlspecialchars($title ?: 'Wallpaper'); ?>
                    </h1>
                    <p class="character-name">
                        <i class="fas <?php echo ($type === 'mobile') ? 'fa-mobile-alt' : 'fa-desktop'; ?>"></i> 
                        <?php echo htmlspecialchars($character ?: ucfirst($type) . ' Wallpaper'); ?>
                    </p>
                </div>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($downloads); ?></div>
                        <div class="stat-label"><i class="fas fa-download"></i> Downloads</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($views); ?></div>
                        <div class="stat-label"><i class="fas fa-eye"></i> Views</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">4K</div>
                        <div class="stat-label"><i class="fas fa-expand"></i> Resolution</div>
                    </div>
                </div>
                
                <div class="download-btn-wrapper">
                    <div style="position: relative; display: inline-block;">
                        <div class="pulse-ring"></div>
                        <a href="?id=<?php echo $wallpaper_id; ?>&type=<?php echo $type; ?>&download=1" 
                           class="stunning-download-btn <?php echo !$downloadCheck['allowed'] ? 'disabled' : ''; ?>">
                            <i class="fas fa-download"></i>
                            DOWNLOAD
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <p style="color: var(--muted); margin-top: 20px; font-size: 0.85rem;">
                        <i class="fas fa-shield-alt"></i> Secure Download â€¢ High Quality â€¢ 4K Resolution
                    </p>
                </div>
                
                <div class="action-buttons">
                    <button onclick="copyPageUrl()" class="action-btn">
                        <i class="fas fa-link"></i> Copy Page URL
                    </button>
                    <a href="javascript:history.back()" class="action-btn">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                    <a href="<?php echo ($type === 'mobile') ? 'mobile.php' : 'categories.php'; ?>" class="action-btn">
                        <i class="fas <?php echo ($type === 'mobile') ? 'fa-mobile-alt' : 'fa-image'; ?>"></i> 
                        <?php echo ($type === 'mobile') ? 'More Mobile Walls' : 'Browse Categories'; ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script>
        // ============================================
        // COMPLETE IMAGE PROTECTION
        // ============================================
        
        // Disable right-click on entire page
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showNotification('😂 Support downloading, bro. The download button has feelings too. 🥺');
            return false;
        });
        
        // Disable all keyboard shortcuts that could save images
        document.addEventListener('keydown', function(e) {
            // Ctrl+S, Ctrl+P, Ctrl+U, F12, Ctrl+Shift+I, Ctrl+Shift+C
            if ((e.ctrlKey && (e.key === 's' || e.key === 'p' || e.key === 'u')) || 
                e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'C' || e.key === 'J'))) {
                e.preventDefault();
                showNotification('ðŸ”’ Save/Developer tools disabled. Use the download button!', 'warning');
                return false;
            }
            
            // Disable Ctrl+click on images
            if (e.ctrlKey && (e.key === 'Click' || e.button === 1)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Disable drag and drop on entire page
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Disable image dropping
        window.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        window.addEventListener('drop', function(e) {
            e.preventDefault();
        });
        
        // Copy page URL instead of image URL
        function copyPageUrl() {
            navigator.clipboard.writeText(window.location.href);
            showNotification('âœ… Page URL copied to clipboard! Share this page to download the wallpaper.');
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.background = type === 'success' ? '#e8b923' : '#e8000d';
            notification.style.color = type === 'success' ? '#000' : '#fff';
            notification.style.padding = '12px 24px';
            notification.style.borderRadius = '40px';
            notification.style.fontWeight = 'bold';
            notification.style.zIndex = '9999';
            notification.style.animation = 'slideIn 0.3s ease';
            notification.style.fontFamily = "'Raleway', sans-serif";
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 4000);
        }
        
        // Add animation style
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Disable select all (Ctrl+A) on images area
        document.addEventListener('selectstart', function(e) {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
                return false;
            }
        });
        
        // Warn users trying to access image directly via console
        console.log('%cðŸ”’ Wallpaper is protected. Use the download button!', 'color: #e8b923; font-size: 16px; font-weight: bold;');
    </script>
</body>
</html>
