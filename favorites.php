<?php
// favorites.php - User's favorite wallpapers (Supports both desktop & mobile)
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's favorites - supports both desktop AND mobile wallpapers
$sql = "
    SELECT 
        f.id as favorite_id,
        f.wallpaper_id,
        f.type,
        f.created_at as favorited_at,
        CASE 
            WHEN f.type = 'desktop' THEN d.title
            WHEN f.type = 'mobile' THEN m.title
        END as title,
        CASE 
            WHEN f.type = 'desktop' THEN d.image_path
            WHEN f.type = 'mobile' THEN m.image_path
        END as image_path,
        CASE 
            WHEN f.type = 'desktop' THEN d.character_name
            WHEN f.type = 'mobile' THEN m.character_name
        END as character_name,
        CASE 
            WHEN f.type = 'desktop' THEN d.views
            WHEN f.type = 'mobile' THEN m.views
        END as views,
        CASE 
            WHEN f.type = 'desktop' THEN d.downloads
            WHEN f.type = 'mobile' THEN m.downloads
        END as downloads,
        CASE 
            WHEN f.type = 'desktop' THEN d.resolution
            WHEN f.type = 'mobile' THEN m.resolution
        END as resolution,
        c.name as category_name,
        c.slug as category_slug
    FROM favorites f
    LEFT JOIN desktop_wallpapers d ON f.wallpaper_id = d.id AND f.type = 'desktop'
    LEFT JOIN mobile_wallpapers m ON f.wallpaper_id = m.id AND f.type = 'mobile'
    LEFT JOIN categories c ON 
        CASE 
            WHEN f.type = 'desktop' THEN d.category_id
            WHEN f.type = 'mobile' THEN m.category_id
        END = c.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$favorites = $result->fetch_all(MYSQLI_ASSOC);

function fmtNum(int $n): string {
    if ($n >= 1000000) return round($n/1000000,1).'M';
    if ($n >= 1000) return round($n/1000,1).'K';
    return (string)$n;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - WallHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* ═══ DESIGN TOKENS — matches WallHub's cinematic dark identity ═══ */
        :root {
            --ink:        #03030a;
            --ink1:       #07071a;
            --ink2:       #0d0d23;
            --ink3:       #131332;
            --surface:    rgba(255,255,255,0.032);
            --glass:      rgba(13,13,35,0.72);
            --border:     rgba(255,255,255,0.06);
            --border2:    rgba(255,255,255,0.12);
            --text:       #e8eaf6;
            --text2:      #9ea3c0;
            --text3:      #525780;
            --accent:     #FF003F;
            --accent2:    #CC0033;
            --gold:       #f59e0b;
            --gold2:      #d97706;
            --green:      #00e676;
            --r-lg:        22px;
            --shadow:     0 12px 50px rgba(0,0,0,0.6);
            --shadow-lg:  0 30px 80px rgba(0,0,0,0.7);
            --ease:       cubic-bezier(0.23,1,0.32,1);
            --font-display: 'Bebas Neue', cursive;
            --font-body:    'DM Sans', system-ui, sans-serif;
            --font-mono:    'DM Mono', monospace;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--ink);
            color: var(--text);
            font-family: var(--font-body);
            overflow-x: hidden;
            padding-top: 80px;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        .favorites-container { max-width: 1340px; margin: 0 auto; padding: 40px 28px 90px; }

        /* ═══ PAGE HEADER ═══ */
        .page-hero {
            position: relative;
            padding: 30px 0 40px;
            overflow: hidden;
            border-bottom: 1px solid var(--border);
            margin-bottom: 40px;
        }
        .page-hero__glow {
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 50% 90% at 0% 20%, rgba(255,0,63,0.1), transparent 65%);
            pointer-events: none;
        }
        .page-hero__kicker {
            position: relative;
            display: inline-flex; align-items: center; gap: 9px;
            font-family: var(--font-mono);
            font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 14px;
        }
        .page-hero__kicker span {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 12px var(--accent);
        }
        .page-hero h1 {
            position: relative;
            font-family: var(--font-display);
            font-size: clamp(2.6rem, 5vw, 3.8rem);
            letter-spacing: 0.01em;
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 10px;
        }
        .page-hero h1 i {
            background: linear-gradient(135deg, var(--accent), var(--gold));
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .page-hero p {
            position: relative;
            font-size: 0.95rem; color: var(--text2); font-weight: 300;
        }
        .page-hero p strong { color: var(--text); font-weight: 600; }

        /* ═══ FAVORITES GRID ═══ */
        .fav-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }
        .fav-card {
            position: relative;
            background: var(--ink3);
            border-radius: var(--r-lg);
            overflow: hidden;
            border: 1px solid var(--border);
            transition: all 0.35s var(--ease);
        }
        .fav-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255,255,255,0.18);
            box-shadow: var(--shadow-lg);
        }
        .fav-thumb {
            aspect-ratio: 3/4;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .fav-thumb img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.6s var(--ease);
        }
        .fav-card:hover .fav-thumb img { transform: scale(1.08); }
        .fav-thumb::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(3,3,10,0.75) 0%, rgba(3,3,10,0.05) 40%, transparent 60%);
            pointer-events: none;
        }

        .fav-heart {
            position: absolute; top: 12px; left: 12px; z-index: 3;
            width: 34px; height: 34px; border-radius: 50%;
            background: rgba(3,3,10,0.75); backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            box-shadow: 0 0 10px rgba(255,0,63,0.35);
        }

        .fav-type-badge {
            position: absolute; top: 12px; right: 12px; z-index: 3;
            display: flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-family: var(--font-mono); font-size: 0.6rem;
            letter-spacing: 0.08em; text-transform: uppercase;
            backdrop-filter: blur(8px);
        }
        .fav-type-badge.desktop { background: rgba(255,0,63,0.16); color: var(--accent); border: 1px solid rgba(255,0,63,0.3); }
        .fav-type-badge.mobile { background: rgba(245,158,11,0.16); color: var(--gold); border: 1px solid rgba(245,158,11,0.3); }

        .fav-new-badge {
            position: absolute; top: 52px; right: 12px; z-index: 3;
            background: linear-gradient(135deg, var(--gold), var(--accent));
            color: #fff; padding: 3px 9px; border-radius: 8px;
            font-family: var(--font-mono); font-size: 0.58rem; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            box-shadow: 0 0 10px rgba(255,0,63,0.4);
        }

        .fav-overlay {
            position: absolute; inset: 0; z-index: 2;
            background: rgba(0,0,0,0.82); backdrop-filter: blur(3px);
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
            opacity: 0; transition: opacity 0.3s;
        }
        .fav-card:hover .fav-overlay { opacity: 1; }
        .fav-overlay .btn-fav {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 20px; border-radius: 50px;
            font-size: 0.8rem; font-weight: 600;
            font-family: var(--font-body);
            border: none; cursor: pointer;
            transition: all 0.25s var(--ease);
        }
        .btn-dl {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
            box-shadow: 0 4px 14px rgba(255,0,63,0.3);
        }
        .btn-dl:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(255,0,63,0.45); color: #fff; }
        .btn-rm {
            background: transparent;
            color: var(--text2);
            border: 1px solid var(--border2) !important;
        }
        .btn-rm:hover { border-color: var(--accent) !important; color: #fff; background: rgba(255,0,63,0.1); }

        .fav-body { padding: 14px 16px 16px; }
        .fav-title {
            font-size: 0.95rem; font-weight: 600; margin-bottom: 8px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .fav-meta-row {
            display: flex; flex-wrap: wrap; gap: 10px;
            font-family: var(--font-mono); font-size: 0.7rem; color: var(--text3);
        }
        .fav-meta-row span { display: inline-flex; align-items: center; gap: 5px; }

        /* ═══ EMPTY STATE ═══ */
        .empty-favorites {
            text-align: center;
            padding: 90px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
        }
        .empty-favorites i { font-size: 3.6rem; color: var(--text3); margin-bottom: 20px; display: block; }
        .empty-favorites h3 { font-family: var(--font-display); font-size: 1.8rem; letter-spacing: 0.02em; margin-bottom: 8px; }
        .empty-favorites p { color: var(--text2); margin-bottom: 22px; }
        .empty-favorites .btn-browse {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff; padding: 12px 26px; border-radius: 50px;
            font-weight: 600; font-size: 0.9rem;
            box-shadow: 0 4px 16px rgba(255,0,63,0.3);
            transition: all 0.3s var(--ease);
        }
        .empty-favorites .btn-browse:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,0,63,0.45); color: #fff; }

        /* ═══ NOTIFICATION TOAST ═══ */
        @keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .notification {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            display: flex; align-items: center; gap: 10px;
            background: var(--glass);
            border: 1px solid var(--border2);
            border-left: 3px solid var(--accent);
            backdrop-filter: blur(16px);
            padding: 14px 22px;
            border-radius: 12px;
            color: var(--text);
            font-size: 0.85rem;
            box-shadow: var(--shadow-lg);
            animation: slideIn 0.4s var(--ease);
        }
        .notification.success { border-left-color: var(--green); }
        .notification.error { border-left-color: var(--accent); }

        /* Responsive */
        @media (max-width: 1200px) { .fav-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .fav-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .fav-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="favorites-container">
    <div class="page-hero">
        <div class="page-hero__glow"></div>
        <div class="page-hero__kicker"><span></span> YOUR COLLECTION</div>
        <h1><i class="fas fa-heart"></i> My Favorites</h1>
        <p>You have <strong><?php echo count($favorites); ?></strong> favorite <?php echo count($favorites) == 1 ? 'wallpaper' : 'wallpapers'; ?></p>
    </div>

    <?php if (count($favorites) > 0): ?>
        <div class="fav-grid">
            <?php foreach ($favorites as $wallpaper): ?>
                <div class="fav-card" data-wallpaper-id="<?php echo $wallpaper['wallpaper_id']; ?>" data-type="<?php echo htmlspecialchars($wallpaper['type']); ?>">
                    <div class="fav-thumb" onclick="window.location.href='download.php?id=<?php echo $wallpaper['wallpaper_id']; ?>&type=<?php echo $wallpaper['type']; ?>'">
                        <img src="<?php echo htmlspecialchars($wallpaper['image_path']); ?>" alt="<?php echo htmlspecialchars($wallpaper['title']); ?>" loading="lazy">

                        <div class="fav-heart"><i class="fas fa-heart"></i></div>

                        <span class="fav-type-badge <?php echo htmlspecialchars($wallpaper['type']); ?>">
                            <i class="fas <?php echo $wallpaper['type'] == 'desktop' ? 'fa-desktop' : 'fa-mobile-alt'; ?>"></i>
                            <?php echo ucfirst($wallpaper['type']); ?>
                        </span>

                        <?php if (strtotime($wallpaper['favorited_at']) > strtotime('-7 days')): ?>
                            <div class="fav-new-badge">New</div>
                        <?php endif; ?>

                        <div class="fav-overlay">
                            <a href="download.php?id=<?php echo $wallpaper['wallpaper_id']; ?>&type=<?php echo $wallpaper['type']; ?>" class="btn-fav btn-dl">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button class="btn-fav btn-rm remove-favorite" data-id="<?php echo $wallpaper['wallpaper_id']; ?>" data-type="<?php echo $wallpaper['type']; ?>">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                    <div class="fav-body">
                        <div class="fav-title"><?php echo htmlspecialchars($wallpaper['title']); ?></div>
                        <div class="fav-meta-row">
                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($wallpaper['character_name'] ?: 'Wallpaper'); ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo fmtNum((int)($wallpaper['views'] ?? 0)); ?></span>
                            <span><i class="fas fa-download"></i> <?php echo fmtNum((int)($wallpaper['downloads'] ?? 0)); ?></span>
                            <?php if (!empty($wallpaper['resolution'])): ?>
                            <span><i class="fas fa-expand"></i> <?php echo htmlspecialchars($wallpaper['resolution']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-favorites">
            <i class="fas fa-heart-broken"></i>
            <h3>No favorites yet</h3>
            <p>Start adding wallpapers to your favorites by clicking the heart icon.</p>
            <a href="index.php" class="btn-browse">
                <i class="fas fa-images"></i> Browse Wallpapers
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Disable right-click on images
    document.addEventListener('contextmenu', function(e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
            return false;
        }
    });

    // Disable drag on images
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
    });

    $(document).ready(function() {
        // Remove favorite functionality
        $('.remove-favorite').click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            const wallpaperId = $(this).data('id');
            const type = $(this).data('type');
            const card = $(this).closest('.fav-card');

            $.ajax({
                url: 'toggle_favorite.php',
                method: 'POST',
                data: { wallpaper_id: wallpaperId, type: type },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.action === 'removed') {
                        card.fadeOut(300, function() {
                            $(this).remove();
                            if ($('.fav-card').length === 0) {
                                location.reload();
                            } else {
                                const newCount = $('.fav-card').length;
                                $('.page-hero p').html('You have <strong>' + newCount + '</strong> favorite ' + (newCount === 1 ? 'wallpaper' : 'wallpapers'));
                            }
                        });

                        showNotification('Removed from favorites', 'success');
                    } else {
                        showNotification(response.message || 'Error removing from favorites', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred', 'error');
                }
            });
        });

        function showNotification(message, type) {
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const notification = $('<div>')
                .addClass('notification ' + type)
                .html('<i class="fas ' + icon + '"></i> ' + message);

            $('body').append(notification);

            setTimeout(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });
</script>

</body>
</html>