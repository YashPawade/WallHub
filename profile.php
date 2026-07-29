<?php
// profile.php - User Profile Page (WITH VISIBLE FORM COLORS)
session_start();

// Error reporting (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ========== FORCE GET LATEST USER DATA FROM DATABASE ==========
$userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();
$userQuery->close();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Update session with latest data
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name'] = $user['last_name'];
$_SESSION['role'] = $user['role'];

$user_role = $user['role'];

// Initialize variables
$success_msg = '';
$error_msg = '';

// ========== HANDLE PROFILE UPDATE ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_msg = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format!";
    } else {
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $user_id);
        $checkEmail->execute();
        $result = $checkEmail->get_result();
        
        if ($result->num_rows == 0) {
            $update = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $update->bind_param("sssi", $first_name, $last_name, $email, $user_id);
            
            if ($update->execute()) {
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $success_msg = "Profile updated successfully!";
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['email'] = $email;
            } else {
                $error_msg = "Failed to update profile: " . $conn->error;
            }
            $update->close();
        } else {
            $error_msg = "Email already exists for another user!";
        }
        $checkEmail->close();
    }
}

// ========== HANDLE PASSWORD CHANGE ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $passQuery = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $passQuery->bind_param("i", $user_id);
    $passQuery->execute();
    $result = $passQuery->get_result();
    $user_pass = $result->fetch_assoc();
    $passQuery->close();
    
    $valid = false;
    if ($user_pass && password_verify($current_password, $user_pass['password_hash'])) {
        $valid = true;
    }
    
    if (!$valid) {
        $error_msg = "Current password is incorrect!";
    } elseif (strlen($new_password) < 8) {
        $error_msg = "New password must be at least 8 characters!";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error_msg = "Password must contain at least one uppercase letter!";
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error_msg = "Password must contain at least one lowercase letter!";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error_msg = "Password must contain at least one number!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords do not match!";
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->bind_param("si", $new_hash, $user_id);
        
        if ($update->execute()) {
            $success_msg = "Password changed successfully!";
            session_regenerate_id(true);
        } else {
            $error_msg = "Failed to update password: " . $conn->error;
        }
        $update->close();
    }
}

// ========== HANDLE SECURITY QUESTION UPDATE ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_security'])) {
    $security_question = trim($_POST['security_question']);
    $security_answer = trim($_POST['security_answer']);
    
    if (empty($security_question) || empty($security_answer)) {
        $error_msg = "Please select a security question and provide an answer!";
    } else {
        $security_answer_hash = password_hash($security_answer, PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE users SET security_question = ?, security_answer_hash = ? WHERE id = ?");
        $update->bind_param("ssi", $security_question, $security_answer_hash, $user_id);
        
        if ($update->execute()) {
            $success_msg = "Security settings updated successfully!";
            $user['security_question'] = $security_question;
        } else {
            $error_msg = "Failed to update security settings: " . $conn->error;
        }
        $update->close();
    }
}

// ========== GET DOWNLOAD STATISTICS ==========
$today = date('Y-m-d');
$todayStart = $today . ' 00:00:00';
$todayEnd = $today . ' 23:59:59';

$todayQuery = $conn->prepare("SELECT COUNT(*) as count FROM user_downloads WHERE user_id = ? AND download_date BETWEEN ? AND ?");
$todayQuery->bind_param("iss", $user_id, $todayStart, $todayEnd);
$todayQuery->execute();
$todayResult = $todayQuery->get_result();
$downloadsToday = $todayResult->fetch_assoc()['count'] ?? 0;
$todayQuery->close();

$totalQuery = $conn->prepare("SELECT COUNT(*) as total FROM user_downloads WHERE user_id = ?");
$totalQuery->bind_param("i", $user_id);
$totalQuery->execute();
$totalResult = $totalQuery->get_result();
$totalDownloads = $totalResult->fetch_assoc()['total'] ?? 0;
$totalQuery->close();

$lastQuery = $conn->prepare("SELECT MAX(download_date) as last FROM user_downloads WHERE user_id = ?");
$lastQuery->bind_param("i", $user_id);
$lastQuery->execute();
$lastResult = $lastQuery->get_result();
$lastDownload = $lastResult->fetch_assoc()['last'];
$lastQuery->close();

// ========== GET RECENT DOWNLOADS ==========
$recentDownloads = $conn->prepare("
    SELECT 
        ud.*,
        d.title as desktop_title,
        d.image_path as desktop_image,
        m.title as mobile_title,
        m.image_path as mobile_image
    FROM user_downloads ud 
    LEFT JOIN desktop_wallpapers d ON ud.wallpaper_id = d.id
    LEFT JOIN mobile_wallpapers m ON ud.wallpaper_id = m.id
    WHERE ud.user_id = ? 
        AND (d.id IS NOT NULL OR m.id IS NOT NULL)
    ORDER BY ud.download_date DESC 
    LIMIT 10
");

if ($recentDownloads) {
    $recentDownloads->bind_param("i", $user_id);
    $recentDownloads->execute();
    $recentDownloadsResult = $recentDownloads->get_result();
} else {
    $recentDownloadsResult = false;
}

// Check if user is owner or admin
$isOwner = ($user_role === 'owner');
$isAdmin = ($user_role === 'admin');
$isPremium = ($user_role === 'premium');
$isMember = ($user_role === 'member');

$pageTitle = "My Profile - WallHub";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            font-family: 'Poppins', sans-serif;
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, rgba(20, 20, 30, 0.95), rgba(30, 30, 45, 0.95));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(225, 29, 29, 0.4);
            text-align: center;
        }
        
        .profile-header h2 {
            color: #ffffff !important;
        }
        
        .profile-header p {
            color: #cccccc !important;
        }
        
        .avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #e11d1d, #6c5ce7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: white;
        }
        
        .role-badge {
            display: inline-block;
            padding: 5px 20px;
            border-radius: 30px;
            font-weight: 600;
            margin: 10px 0;
        }
        
        .role-owner {
            background: linear-gradient(135deg, #FFD700, #FF8C00);
            color: #000;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
        }
        
        .role-admin {
            background: #e11d1d;
            color: white;
            box-shadow: 0 0 10px rgba(225, 29, 29, 0.5);
        }
        
        .role-premium {
            background: #ffd700;
            color: #000;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }
        
        .role-member {
            background: #2d6a4f;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(225, 29, 29, 0.3);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #e11d1d;
        }
        
        .stat-card i {
            font-size: 2rem;
            color: #e11d1d;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }
        
        .stat-card .label {
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Card Styles */
        .card {
            background: rgba(35, 35, 55, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 2px solid rgba(225, 29, 29, 0.5);
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(225, 29, 29, 0.4), rgba(225, 29, 29, 0.15));
            border-bottom: 2px solid #e11d1d;
            padding: 15px 20px;
            font-weight: 700;
            color: #ffffff;
            font-size: 1.2rem;
            border-radius: 18px 18px 0 0;
        }
        
        .card-header i {
            color: #e11d1d;
            margin-right: 10px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* ========== HIGHLY VISIBLE FORM INPUTS - LIGHT BACKGROUND ========== */
        .form-label {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
            display: block;
        }
        
        /* Input fields - Light background with dark text for visibility */
        .form-control, .form-select {
            background: #f5f5f5 !important;
            border: 2px solid #e11d1d !important;
            color: #1a1a1a !important;
            border-radius: 10px !important;
            padding: 12px 15px !important;
            font-size: 1rem !important;
            width: 100% !important;
            transition: all 0.3s ease !important;
            font-weight: 500 !important;
        }
        
        /* Placeholder text color */
        .form-control::placeholder {
            color: #666666 !important;
            font-weight: normal !important;
        }
        
        /* Focus effect */
        .form-control:focus, .form-select:focus {
            background: #ffffff !important;
            border-color: #e11d1d !important;
            box-shadow: 0 0 15px rgba(225, 29, 29, 0.5) !important;
            color: #000000 !important;
            outline: none !important;
        }
        
        /* Select dropdown options */
        .form-select option {
            background: #f5f5f5;
            color: #1a1a1a;
        }
        
        /* Password field same styling */
        input[type="password"].form-control {
            background: #f5f5f5 !important;
            color: #1a1a1a !important;
        }
        
        input[type="password"].form-control:focus {
            background: #ffffff !important;
            color: #000000 !important;
        }
        
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(225, 29, 29, 0.4);
            background: linear-gradient(135deg, #f11d1d, #a00000);
        }
        
        .btn-secondary {
            background: rgba(100, 100, 130, 0.9);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-secondary:hover {
            background: rgba(120, 120, 150, 1);
            color: white;
        }
        
        .w-100 {
            width: 100%;
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 12px 20px;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }
        
        /* Download Items */
        .download-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .download-item:hover {
            background: rgba(225, 29, 29, 0.1);
        }
        
        .download-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .download-info {
            flex: 1;
        }
        
        .download-title {
            color: #ffffff;
            font-weight: 500;
        }
        
        .download-date {
            color: #cccccc;
            font-size: 0.75rem;
        }
        
        /* Limit Info Box */
        .limit-info {
            background: linear-gradient(135deg, rgba(225, 29, 29, 0.15), rgba(225, 29, 29, 0.05));
            padding: 15px 20px;
            border-radius: 12px;
            margin-top: 15px;
            color: #ffffff;
            border-left: 4px solid #e11d1d;
        }
        
        .limit-info strong {
            color: #e11d1d;
        }
        
        /* Progress Bar */
        .progress {
            background: rgba(30, 30, 40, 0.8);
            height: 8px;
            border-radius: 10px;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #e11d1d, #ffd700);
            border-radius: 10px;
        }
        
        /* Text Colors */
        .text-muted {
            color: #bbbbbb !important;
        }
        
        small.text-muted {
            font-size: 0.7rem;
            color: #aaaaaa !important;
        }
        
        .text-warning {
            color: #ffc107 !important;
        }
        
        .text-success {
            color: #28a745 !important;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: #e11d1d;
        }
        
        /* Owner Crown Animation */
        @keyframes crownGlow {
            0% { text-shadow: 0 0 5px #FFD700; }
            50% { text-shadow: 0 0 20px #FF8C00; }
            100% { text-shadow: 0 0 5px #FFD700; }
        }
        
        .owner-crown {
            animation: crownGlow 1.5s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .profile-container {
                padding: 15px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
            <div>
                <?php if($isOwner): ?>
                    <span class="role-badge role-owner">
                        <i class="fas fa-crown owner-crown"></i> OWNER <i class="fas fa-crown owner-crown"></i>
                    </span>
                <?php elseif($isAdmin): ?>
                    <span class="role-badge role-admin">
                        <i class="fas fa-shield-alt"></i> ADMIN
                    </span>
                <?php elseif($isPremium): ?>
                    <span class="role-badge role-premium">
                        <i class="fas fa-gem"></i> PREMIUM
                    </span>
                <?php else: ?>
                    <span class="role-badge role-member">
                        <i class="fas fa-user"></i> MEMBER
                    </span>
                <?php endif; ?>
            </div>
            <p class="mt-3">Member since: <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-download"></i>
                <div class="number"><?php echo (int)$downloadsToday; ?></div>
                <div class="label">Downloads Today</div>
                <?php if($isMember): ?>
                    <div class="progress mt-2">
                        <div class="progress-bar" style="width: <?php echo min(100, ($downloadsToday / 10) * 100); ?>%"></div>
                    </div>
                    <small class="text-muted"><?php echo max(0, 10 - $downloadsToday); ?> downloads remaining today</small>
                <?php elseif($isPremium): ?>
                    <small style="color: #ffd700 !important;"><i class="fas fa-gem"></i> Premium Account - Unlimited downloads</small>
                <?php elseif($isAdmin || $isOwner): ?>
                    <small style="color: #e11d1d !important;"><i class="fas fa-shield-alt"></i> <?php echo $isOwner ? 'OWNER' : 'ADMIN'; ?> Account - Unlimited downloads</small>
                <?php endif; ?>
            </div>
            <div class="stat-card">
                <i class="fas fa-history"></i>
                <div class="number"><?php echo (int)$totalDownloads; ?></div>
                <div class="label">Total Downloads</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar"></i>
                <div class="number" style="font-size: 1rem;"><?php echo $lastDownload ? date('d M, Y', strtotime($lastDownload)) : 'Never'; ?></div>
                <div class="label">Last Download</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <div class="number" style="font-size: 0.85rem; word-break: break-all;"><?php echo htmlspecialchars($user['email']); ?></div>
                <div class="label">Email Address</div>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <!-- Update Profile Form -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label" style="color: black;">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: black;">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: black;">Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn-primary w-100" style="border:none;">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Change Password Form -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-key"></i> Change Password
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label" style="color: black;">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: black;">New Password</label>
                                <input type="password" class="form-control" name="new_password" required>
                                <small class="text">Minimum 8 characters with uppercase, lowercase and number</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: black;">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn-primary w-100" style="border:none;">
                                <i class="fas fa-lock"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Settings Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-shield-alt"></i> Security Settings
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="security_question" class="form-label" style="color: black;">
                                <i class="fas fa-question-circle" style="color: black;"></i> Security Question
                            </label>
                            <select name="security_question" id="security_question" class="form-select" required>
                                <option value="">-- Select a security question --</option>
                                <option value="What is your mother's maiden name?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What is your mother's maiden name?") ? 'selected' : ''; ?>>
                                    What is your mother's maiden name?
                                </option>
                                <option value="What was the name of your first pet?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What was the name of your first pet?") ? 'selected' : ''; ?>>
                                    What was the name of your first pet?
                                </option>
                                <option value="What was your first car?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What was your first car?") ? 'selected' : ''; ?>>
                                    What was your first car?
                                </option>
                                <option value="What is your favorite book?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What is your favorite book?") ? 'selected' : ''; ?>>
                                    What is your favorite book?
                                </option>
                                <option value="What city were you born in?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What city were you born in?") ? 'selected' : ''; ?>>
                                    What city were you born in?
                                </option>
                                <option value="What is your favorite anime?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What is your favorite anime?") ? 'selected' : ''; ?>>
                                    What is your favorite anime?
                                </option>
                                <option value="Who is your favorite anime character?" <?php echo (isset($user['security_question']) && $user['security_question'] == "Who is your favorite anime character?") ? 'selected' : ''; ?>>
                                    Who is your favorite anime character?
                                </option>
                                <option value="What is your favorite food?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What is your favorite food?") ? 'selected' : ''; ?>>
                                    What is your favorite food?
                                </option>
                                <option value="What was your childhood nickname?" <?php echo (isset($user['security_question']) && $user['security_question'] == "What was your childhood nickname?") ? 'selected' : ''; ?>>
                                    What was your childhood nickname?
                                </option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="security_answer" class="form-label" style="color: black;">
                                <i class="fas fa-lock" style="color: black;"></i> Security Answer
                            </label>
                            <input type="text" name="security_answer" id="security_answer" class="form-control" 
                                   placeholder="Enter your answer" 
                                   value="" 
                                   required>
                            <small class="text">
                                <i class="fas fa-info-circle"></i> 
                                This answer will be used to verify your identity if you forget your password.
                            </small>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" name="save_security" class="btn-primary" style="border:none; padding:10px 25px;">
                                <i class="fas fa-save"></i> Save Security Settings
                            </button>
                            <?php if(empty($user['security_question']) || empty($user['security_answer_hash'])): ?>
                                <small class="text-warning ms-3">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Security question not set!
                                </small>
                            <?php else: ?>
                                <small class="text-success ms-3">
                                    <i class="fas fa-check-circle"></i> 
                                    Security question configured
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Recent Downloads -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Recent Downloads
            </div>
            <div class="card-body">
                <?php if($recentDownloadsResult && $recentDownloadsResult->num_rows > 0): ?>
                    <?php while($download = $recentDownloadsResult->fetch_assoc()): ?>
                    <div class="download-item">
                        <?php 
                        $image_path = !empty($download['desktop_image']) ? $download['desktop_image'] : $download['mobile_image'];
                        $title = !empty($download['desktop_title']) ? $download['desktop_title'] : $download['mobile_title'];
                        $source_type = !empty($download['desktop_image']) ? 'desktop' : 'mobile';
                        ?>
                        <?php if($image_path): ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                        <?php else: ?>
                            <div style="width:60px; height:60px; background:#333; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-image" style="color:#666;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="download-info">
                            <div class="download-title"><?php echo htmlspecialchars($title ?? 'Unknown Wallpaper'); ?></div>
                            <div class="download-date">
                                <i class="fas fa-clock"></i> <?php echo date('F d, Y - h:i A', strtotime($download['download_date'])); ?>
                            </div>
                        </div>
                        <a href="download.php?id=<?php echo urlencode($download['wallpaper_id']); ?>&type=<?php echo $source_type; ?>" class="btn-secondary" style="text-decoration:none;">
                            <i class="fas fa-download"></i> Again
                        </a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-download fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No downloads yet. Start exploring wallpapers!</p>
                        <a href="onepiece.php" class="btn-primary" style="text-decoration:none; display:inline-block;">Browse Wallpapers</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Download Limit Info -->
        <div class="limit-info">
            <i class="fas fa-info-circle"></i>
            <strong>Account Status:</strong><br>
            <?php if($isOwner): ?>
                <i class="fas fa-crown owner-crown"></i> <strong style="color: #FFD700;">OWNER ACCOUNT</strong><br>
                ✓ Unlimited downloads - No daily restrictions<br>
                ✓ Full access to all features<br>
                ✓ Admin panel access<br>
                ✓ Can add/delete wallpapers<br>
                ✓ No download limits apply
            <?php elseif($isAdmin): ?>
                <i class="fas fa-shield-alt"></i> <strong style="color: #e11d1d;">ADMIN ACCOUNT</strong><br>
                ✓ Unlimited downloads - No daily restrictions<br>
                ✓ Full access to all features<br>
                ✓ Can manage wallpapers<br>
                ✓ No download limits apply
            <?php elseif($isPremium): ?>
                <i class="fas fa-gem"></i> <strong style="color: #ffd700;">PREMIUM ACCOUNT</strong><br>
                ✓ Unlimited downloads - No daily restrictions<br>
                ✓ Early access to new content<br>
                ✓ Ad-free experience
            <?php else: ?>
                <i class="fas fa-user"></i> <strong>MEMBER ACCOUNT</strong><br>
                ⚠️ You have <strong>10 downloads per day</strong><br>
                Today's used: <strong><?php echo (int)$downloadsToday; ?>/10</strong>
                <?php if($downloadsToday >= 10): ?>
                    <br><span class="text-danger"><i class="fas fa-exclamation-triangle"></i> You've reached your daily limit. Upgrade to Premium for unlimited downloads!</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>