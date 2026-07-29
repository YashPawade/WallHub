<?php
// my-downloads.php - User's Download History Page
session_start();
include('includes/db.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-downloads.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// FIXED: Removed 'daily_downloads' column if it doesn't exist
$userQuery = mysqli_query($conn, "SELECT username, first_name, role FROM users WHERE id = $user_id");
if (!$userQuery) {
    die("Error fetching user: " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($userQuery);

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get total downloads count
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_downloads WHERE user_id = $user_id");
if (!$totalQuery) {
    die("Error counting downloads: " . mysqli_error($conn));
}
$total = mysqli_fetch_assoc($totalQuery)['total'];
$total_pages = ceil($total / $per_page);

// FIXED: Handle NULL wallpaper_type and use COALESCE for safety
$downloadsQuery = "SELECT ud.*, 
                          COALESCE(
                              CASE 
                                  WHEN ud.wallpaper_type = 'desktop' THEN d.title
                                  WHEN ud.wallpaper_type = 'mobile' THEN m.title
                                  ELSE 'Unknown Wallpaper'
                              END, 'Unknown Wallpaper'
                          ) as title,
                          COALESCE(
                              CASE 
                                  WHEN ud.wallpaper_type = 'desktop' THEN d.image_path
                                  WHEN ud.wallpaper_type = 'mobile' THEN m.image_path
                                  ELSE NULL
                              END, ''
                          ) as image_path,
                          COALESCE(
                              CASE 
                                  WHEN ud.wallpaper_type = 'desktop' THEN d.character_name
                                  WHEN ud.wallpaper_type = 'mobile' THEN m.character_name
                                  ELSE 'N/A'
                              END, 'N/A'
                          ) as character_name,
                          COALESCE(
                              CASE 
                                  WHEN ud.wallpaper_type = 'desktop' THEN d.resolution
                                  WHEN ud.wallpaper_type = 'mobile' THEN m.resolution
                                  ELSE '4K'
                              END, '4K'
                          ) as resolution,
                          COALESCE(
                              CASE 
                                  WHEN ud.wallpaper_type = 'desktop' THEN d.downloads
                                  WHEN ud.wallpaper_type = 'mobile' THEN m.downloads
                                  ELSE 0
                              END, 0
                          ) as wallpaper_downloads,
                          COALESCE(ud.wallpaper_type, 'desktop') as wallpaper_type
                   FROM user_downloads ud 
                   LEFT JOIN desktop_wallpapers d ON ud.wallpaper_id = d.id AND ud.wallpaper_type = 'desktop'
                   LEFT JOIN mobile_wallpapers m ON ud.wallpaper_id = m.id AND ud.wallpaper_type = 'mobile'
                   WHERE ud.user_id = $user_id 
                   ORDER BY ud.download_date DESC 
                   LIMIT $offset, $per_page";

$downloadsResult = mysqli_query($conn, $downloadsQuery);
if (!$downloadsResult) {
    die("Error fetching downloads: " . mysqli_error($conn));
}

// Get download statistics
$statsQuery = "SELECT 
                    COUNT(*) as total_downloads,
                    COUNT(DISTINCT DATE(download_date)) as active_days,
                    MAX(download_date) as last_download
               FROM user_downloads 
               WHERE user_id = $user_id";
$statsResult = mysqli_query($conn, $statsQuery);
if (!$statsResult) {
    die("Error fetching stats: " . mysqli_error($conn));
}
$stats = mysqli_fetch_assoc($statsResult);

// Get today's downloads
$today = date('Y-m-d');
$todayQuery = mysqli_query($conn, "SELECT COUNT(*) as today FROM user_downloads WHERE user_id = $user_id AND DATE(download_date) = '$today'");
if (!$todayQuery) {
    die("Error fetching today's downloads: " . mysqli_error($conn));
}
$todayDownloads = mysqli_fetch_assoc($todayQuery)['today'];

$pageTitle = "My Downloads - WallHub";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
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
        
        .downloads-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Section */
        .page-header {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(225, 29, 29, 0.3);
        }
        
        .page-header h1 {
            color: #fff;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .page-header h1 i {
            color: #e11d1d;
            margin-right: 10px;
        }
        
        .page-header p {
            color: #aaa;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            color: #aaa;
            font-size: 0.85rem;
        }
        
        /* Type Badge */
        .type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .type-desktop {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
        }
        
        .type-mobile {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: white;
        }
        
        /* Downloads Grid */
        .downloads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .download-card {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s;
            border: 1px solid rgba(225, 29, 29, 0.3);
            position: relative;
        }
        
        .download-card:hover {
            transform: translateY(-5px);
            border-color: #e11d1d;
            box-shadow: 0 10px 25px rgba(225, 29, 29, 0.2);
        }
        
        .download-image {
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        
        .download-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .download-card:hover .download-image img {
            transform: scale(1.05);
        }
        
        .download-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .download-card:hover .download-overlay {
            opacity: 1;
        }
        
        .btn-redownload {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-redownload:hover {
            transform: scale(1.05);
            color: white;
        }
        
        .download-info {
            padding: 15px;
        }
        
        .download-title {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .download-character {
            font-size: 0.8rem;
            color: #ffd166;
            margin-bottom: 8px;
        }
        
        .download-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            color: #aaa;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .download-date i {
            margin-right: 5px;
        }
        
        .download-resolution {
            background: rgba(225, 29, 29, 0.2);
            padding: 2px 8px;
            border-radius: 20px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px;
            background: rgba(20, 20, 30, 0.95);
            border-radius: 20px;
            border: 1px solid rgba(225, 29, 29, 0.3);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #aaa;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #fff;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #aaa;
            margin-bottom: 20px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a, .pagination span {
            padding: 10px 15px;
            background: rgba(20, 20, 30, 0.95);
            border-radius: 10px;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s;
            border: 1px solid rgba(225, 29, 29, 0.3);
        }
        
        .pagination a:hover {
            background: #e11d1d;
            border-color: #e11d1d;
        }
        
        .pagination .active {
            background: #e11d1d;
            border-color: #e11d1d;
        }
        
        /* Limit Info */
        .limit-info {
            background: rgba(225, 29, 29, 0.1);
            border-radius: 15px;
            padding: 15px 20px;
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .limit-info .limit-text {
            color: #ffd166;
        }
        
        .limit-info a {
            color: #fff;
            background: #e11d1d;
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .limit-info a:hover {
            background: #ff3333;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .downloads-container {
                padding: 15px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .downloads-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="downloads-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-download"></i> My Downloads
            </h1>
            <p>View and manage all the wallpapers you've downloaded</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-database"></i>
                <div class="number"><?php echo number_format($stats['total_downloads']); ?></div>
                <div class="label">Total Downloads</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-day"></i>
                <div class="number"><?php echo $todayDownloads; ?></div>
                <div class="label">Downloads Today</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="number"><?php echo $stats['active_days']; ?></div>
                <div class="label">Active Days</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="number" style="font-size: 0.9rem;">
                    <?php echo $stats['last_download'] ? date('M d, Y', strtotime($stats['last_download'])) : 'Never'; ?>
                </div>
                <div class="label">Last Download</div>
            </div>
        </div>
        
        <!-- Downloads Grid -->
        <?php if(mysqli_num_rows($downloadsResult) > 0): ?>
            <div class="downloads-grid">
                <?php while($download = mysqli_fetch_assoc($downloadsResult)): ?>
                <div class="download-card">
                    <span class="type-badge type-<?php echo $download['wallpaper_type']; ?>">
                        <i class="fas <?php echo $download['wallpaper_type'] == 'desktop' ? 'fa-desktop' : 'fa-mobile-alt'; ?>"></i>
                        <?php echo ucfirst($download['wallpaper_type']); ?>
                    </span>
                    <div class="download-image">
                        <?php if(!empty($download['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($download['image_path']); ?>" alt="<?php echo htmlspecialchars($download['title']); ?>">
                        <?php else: ?>
                            <div style="width:100%; height:100%; background:#333; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-image" style="font-size:3rem; color:#666;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="download-overlay">
                            <a href="download.php?id=<?php echo $download['wallpaper_id']; ?>&type=<?php echo $download['wallpaper_type']; ?>" class="btn-redownload">
                                <i class="fas fa-download"></i> Download Again
                            </a>
                        </div>
                    </div>
                    <div class="download-info">
                        <div class="download-title" title="<?php echo htmlspecialchars($download['title']); ?>">
                            <?php echo htmlspecialchars($download['title']); ?>
                        </div>
                        <div class="download-character">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($download['character_name'] ?? 'N/A'); ?>
                        </div>
                        <div class="download-meta">
                            <span class="download-date">
                                <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($download['download_date'])); ?>
                            </span>
                            <span class="download-resolution">
                                <i class="fas fa-expand"></i> <?php echo $download['resolution'] ?? '4K'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-download"></i>
                <h3>No Downloads Yet</h3>
                <p>You haven't downloaded any wallpapers yet. Start exploring our collection!</p>
                <a href="onepiece.php" class="btn-redownload" style="display: inline-block;">
                    <i class="fas fa-image"></i> Browse Wallpapers
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Download Limit Info -->
        <div class="limit-info">
            <div class="limit-text">
                <i class="fas fa-info-circle"></i>
                <strong>Your Download Limit:</strong>
                <?php if($user_role == 'admin' || $user_role == 'owner'): ?>
                    <i class="fas fa-crown"></i> Admin/Owner - Unlimited Downloads
                <?php elseif($user_role == 'premium'): ?>
                    <i class="fas fa-gem"></i> Premium Member - Unlimited Downloads
                <?php else: ?>
                    <i class="fas fa-user"></i> Member - 10 downloads per day
                    (<?php echo $todayDownloads; ?> used today)
                <?php endif; ?>
            </div>
            <?php if($user_role == 'member'): ?>
            <a href="premium.php">
                <i class="fas fa-gem"></i> Upgrade to Premium
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>