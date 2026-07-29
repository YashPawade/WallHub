<?php
// anime-detail.php - DYNAMIC ANIME DETAIL PAGE (MATCHES CATEGORIES PAGE DESIGN)
session_start();
include('includes/db.php');

// Get anime slug from URL (e.g., ?slug=solo-leveling)
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';

if (empty($slug)) {
    header('Location: anime.php');
    exit();
}

// Get category details (the anime series)
$query = "SELECT * FROM categories WHERE slug = '$slug'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: anime.php');
    exit();
}

$category = mysqli_fetch_assoc($result);
$categoryId = $category['id'];
$categoryName = $category['name'];

// ============================================================
// CUSTOM COLORS & EMOJIS FOR EACH ANIME
// ============================================================
$animeColors = [
    'sololeveling' => '#06b6d4',      // Cyan/Blue
    'dandadan' => '#f97316',           // Orange
    'bluelock' => '#1e3a5f',          // Dark Blue
    'mydressupdarling' => '#ff69b4',   // Pink
    'onepiece' => '#e8000d',          // Red


];

$animeEmojis = [
    'sololeveling' => '⚔️',
    'dandadan' => '👻',
    'bluelock' => '⚽',
    'mydressupdarling' => '🎀',
    'onepiece' => '🏴‍☠️',
    
    
];

// Split anime name into two lines for display
$titleParts = [
    'sololeveling' => ['SOLO', 'LEVELING'],
    'dandadan' => ['DANDADAN', 'WALLPAPERS'],
    'bluelock' => ['BLUE', 'LOCK'],
    'mydressupdarling' => ['MY DRESS-UP', 'DARLING'],
    'onepiece' => ['ONE', 'PIECE'],
    
];

$firstLine = $titleParts[$slug][0] ?? strtoupper($categoryName);
$secondLine = $titleParts[$slug][1] ?? 'WALLPAPERS';
$categoryColor = $animeColors[$slug] ?? '#dc2626';
$categoryEmoji = $animeEmojis[$slug] ?? '✦';

// ============================================================
// CUSTOM DESCRIPTIONS FOR EACH ANIME
// ============================================================
$animeDescriptions = [
    'sololeveling' => 'Arise, Shadow Monarch! Premium Solo Leveling wallpapers featuring Sung Jin-Woo, Beru, and the strongest hunters — high resolution, cinematic, and ready for your screen.',
    'dandadan' => 'Believe in aliens and ghosts! Premium Dandadan wallpapers featuring Ken Takakura, Momo Ayase, Seiko Ayase, Turbo Granny, and the supernatural chaos — high resolution, cinematic, and ready for your screen.',
    'bluelock' => 'Become the world\'s greatest egoist! Premium Blue Lock wallpapers featuring Isagi Yoichi, Bachira Meguru, Nagi Seishiro, and the Blue Lock facility — high resolution, cinematic, and ready for your screen.',
    'mydressupdarling' => 'Beautiful cosplay-inspired wallpapers featuring Marin Kitagawa and Gojo Wakana — high resolution, vibrant colors, and ready for your screen.',
    'onepiece' => 'Set sail with the Straw Hat Pirates! Premium One Piece wallpapers featuring Luffy, Zoro, Sanji, and the entire crew — high resolution, cinematic, and ready for your screen.',
    
    
];

$animeDesc = $animeDescriptions[$slug] ?? "Premium $categoryName wallpapers — high resolution, cinematic, and ready for your screen.";

$pageTitle = "$categoryName 4K Wallpapers - WallHub";

// Get ALL wallpapers for this anime
$query = "SELECT w.*, c.name as category_name 
          FROM desktop_wallpapers w
          LEFT JOIN categories c ON w.category_id = c.id
          WHERE w.category_id = $categoryId 
          ORDER BY w.id DESC";
$result = mysqli_query($conn, $query);

$wallpapers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $wallpapers[] = $row;
}

// ============================================================
// CHARACTER MAPPING
// ============================================================

$characterConfigs = [
    'sololeveling' => [
        'sung' => ['name' => 'Sung Jin-Woo', 'icon' => 'fa-crown', 'color' => '#06b6d4', 'description' => 'The Shadow Monarch', 'filter' => ['Sung', 'Jin-Woo']],
        'cha' => ['name' => 'Cha Hae-In', 'icon' => 'fa-fist-raised', 'color' => '#8b5cf6', 'description' => "Korea's S-Rank Hunter", 'filter' => ['Cha', 'Hae-In']],
        'yoo' => ['name' => 'Yoo Jin-Ho', 'icon' => 'fa-user-friends', 'color' => '#4f46e5', 'description' => 'The Loyal Friend', 'filter' => ['Yoo', 'Jin-Ho']],
        'beru' => ['name' => 'Beru', 'icon' => 'fa-bug', 'color' => '#fbbf24', 'description' => 'The Ant King', 'filter' => ['Beru']]
    ],
    'dandadan' => [
        'ken' => ['name' => 'Ken Takakura', 'icon' => 'fa-bolt', 'color' => '#f97316', 'description' => 'The Turbo-Ghost Possessed', 'filter' => ['Ken', 'Takakura', 'Okarun']],
        'momo' => ['name' => 'Momo Ayase', 'icon' => 'fa-star', 'color' => '#ec4899', 'description' => 'The Spiritual Medium', 'filter' => function($n) { return stripos($n, 'Momo') !== false && stripos($n, 'Seiko') === false; }],
        'seiko' => ['name' => 'Seiko Ayase', 'icon' => 'fa-crystal-ball', 'color' => '#8b5cf6', 'description' => 'The Powerful Medium', 'filter' => ['Seiko']],
        'turbo' => ['name' => 'Turbo Granny', 'icon' => 'fa-ghost', 'color' => '#ef4444', 'description' => 'The Turbo-Granny', 'filter' => ['Turbo', 'Granny']]
    ],
    'bluelock' => [
        'isagi' => ['name' => 'Isagi Yoichi', 'icon' => 'fa-eye', 'color' => '#1e3a5f', 'description' => 'The Spatial Awareness Ace', 'filter' => ['Isagi']],
        'bachira' => ['name' => 'Bachira Meguru', 'icon' => 'fa-dragon', 'color' => '#06b6d4', 'description' => 'The Monster Dribbler', 'filter' => ['Bachira']],
        'nagi' => ['name' => 'Nagi Seishiro', 'icon' => 'fa-brain', 'color' => '#ef4444', 'description' => 'The Genius Trapper', 'filter' => ['Nagi']],
        'rin' => ['name' => 'Itoshi Rin', 'icon' => 'fa-crown', 'color' => '#f97316', 'description' => 'The Prodigy Striker', 'filter' => ['Rin', 'Itoshi']]
    ],
    'mydressupdarling' => [
        'marin' => ['name' => 'Marin Kitagawa', 'icon' => 'fa-crown', 'color' => '#ff69b4', 'description' => 'The Ultimate Cosplayer', 'filter' => ['Marin', 'Kitagawa']],
        'gojo' => ['name' => 'Gojo Wakana', 'icon' => 'fa-user-astronaut', 'color' => '#9b59b6', 'description' => 'The Hina Doll Artisan', 'filter' => ['Gojo', 'Wakana']]
    ],
    'naruto' => [
        'naruto' => [
            'name' => 'Naruto Uzumaki',
            'icon' => 'fa-fist-raised',
            'color' => '#ff8c00',
            'description' => 'The Seventh Hokage',
            'filter' => ['Naruto']
        ],
        'sasuke' => [
            'name' => 'Sasuke Uchiha',
            'icon' => 'fa-bolt',
            'color' => '#1a5f8b',
            'description' => 'The Last Uchiha',
            'filter' => ['Sasuke']
        ],
        'kakashi' => [
            'name' => 'Kakashi Hatake',
            'icon' => 'fa-book',
            'color' => '#8b0000',
            'description' => 'The Copy Ninja',
            'filter' => ['Kakashi']
        ],
        'itachi' => [
            'name' => 'Itachi Uchiha',
            'icon' => 'fa-eye',
            'color' => '#8b0000',
            'description' => 'The Crimson Itachi',
            'filter' => ['Itachi']
        ],
         'hinata' => [
        'name' => 'Hinata Hyuga',
        'icon' => 'fa-eye',
        'color' => '#8b5cf6',        // Purple
        'description' => 'The Gentle Fist · Byakugan Princess',
        'filter' => ['Hinata', 'Hyuga']
    ],
         'sakura' => [
        'name' => 'Sakura Haruno',
        'icon' => 'fa-heart',
        'color' => '#ff69b4',        // Pink
        'description' => 'The Medical Ninja · Tsunade\'s Apprentice',
        'filter' => ['Sakura', 'Haruno']
    ],
        ],
          
];

$characters = $characterConfigs[$slug] ?? ['all' => ['name' => 'All Wallpapers', 'icon' => 'fa-image', 'color' => '#ffffff', 'description' => 'All Wallpapers', 'filter' => []]];

foreach ($characters as $key => &$char) {
    $charWallpapers = [];
    foreach ($wallpapers as $wp) {
        $matched = false;
        $charName = $wp['character_name'];
        if (isset($char['filter']) && is_callable($char['filter'])) {
            $matched = $char['filter']($charName);
        } elseif (is_array($char['filter'])) {
            foreach ($char['filter'] as $filter) {
                if (stripos($charName, $filter) !== false) { $matched = true; break; }
            }
        } elseif (empty($char['filter'])) { $matched = true; }
        if ($matched) $charWallpapers[] = $wp;
    }
    usort($charWallpapers, fn($a, $b) => $b['id'] - $a['id']);
    $char['wallpapers'] = $charWallpapers;
    $char['count'] = count($charWallpapers);
}
unset($char);

// ============================================================
// FUNCTIONS
// ============================================================

function renderRows($items, $characterClass, $startIndex = 0) {
    // Renders wallpapers as rows of 4 (a plain grid, no horizontal scroll)
    $html = '';
    $total = count($items);
    $rowsNeeded = ceil($total / 4);
    for ($row = 0; $row < $rowsNeeded; $row++) {
        $html .= '<div class="grid-row">';
        $startIdx = $row * 4;
        $endIdx = min($startIdx + 4, $total);
        for ($i = $startIdx; $i < $endIdx; $i++) {
            $wp = $items[$i];
            $html .= renderCard($wp, $startIndex + $i, $characterClass);
        }
        $html .= '</div>';
    }
    return $html;
}

function renderCharacterGrid($wallpapers, $characterClass) {
    $INITIAL_COUNT = 8; // 2 rows x 4
    $total = count($wallpapers);
    $visible = array_slice($wallpapers, 0, $INITIAL_COUNT);
    $rest    = array_slice($wallpapers, $INITIAL_COUNT);

    $safeClass = preg_replace('/[^a-zA-Z0-9_-]/', '-', $characterClass);

    $html  = '<div class="char-grid">';
    $html .= renderRows($visible, $characterClass, 0);
    $html .= '</div>';

    if (!empty($rest)) {
        $html .= '<div class="char-grid char-grid-more" id="more-' . $safeClass . '" style="display:none;">';
        $html .= renderRows($rest, $characterClass, $INITIAL_COUNT);
        $html .= '</div>';

        $html .= '<div class="view-all-wrap">
            <button class="view-all-btn" data-target="more-' . $safeClass . '" data-total="' . $total . '">
                <span class="view-all-text">View All ' . $total . '</span>
                <span class="view-all-arrow"><i class="fas fa-chevron-down"></i></span>
            </button>
        </div>';
    }

    return $html;
}

function renderCard($wp, $index, $characterClass) {
    $isNew = $index < 3 ? '<span class="wcard-new"><i class="fas fa-fire"></i> New</span>' : '';
    $safeClass = preg_replace('/[^a-zA-Z0-9_-]/', '-', $characterClass);
    return '
    <div class="wcard ' . $safeClass . '-card">
        ' . $isNew . '
        <button class="fav-btn favorite-btn" data-id="' . $wp['id'] . '" data-type="desktop">
            <i class="fas fa-heart"></i>
        </button>
        <div class="wcard-img-wrap">
            <a href="download.php?id=' . $wp['id'] . '&type=desktop">
                <div class="wcard-zoom"><i class="fas fa-expand-alt"></i></div>
                <img src="serve_image.php?id=' . $wp['id'] . '&type=desktop" alt="' . htmlspecialchars($wp['title']) . '" loading="lazy" decoding="async" data-retries="0" data-src="serve_image.php?id=' . $wp['id'] . '&type=desktop" onerror="window.__handleImgError && window.__handleImgError(this)">
            </a>
            <div class="img-retry-overlay">
                <button type="button" class="img-retry-btn" onclick="window.__retryImg && window.__retryImg(this)">
                    <i class="fas fa-rotate-right"></i> Retry
                </button>
            </div>
        </div>
        <div class="wcard-body">
            <p class="wcard-title">' . htmlspecialchars($wp['title']) . '</p>
            <div class="wcard-stats">
                <span><i class="fas fa-eye"></i> ' . number_format($wp['views'] ?? 0) . '</span>
                <span><i class="fas fa-download"></i> ' . number_format($wp['downloads'] ?? 0) . '</span>
            </div>
        </div>
        <a href="download.php?id=' . $wp['id'] . '&type=desktop" class="wcard-dl">
            <span>Download</span>
            <span class="dl-arrow"><i class="fas fa-arrow-down"></i></span>
        </a>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@400;600;700&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ─────────────────────────────────────────────────────────────
           DESIGN TOKENS — CUSTOM COLOR PER ANIME
           ───────────────────────────────────────────────────────────── */
        :root {
            --anime-color: <?php echo $categoryColor; ?>;
            --anime-color-rgb: <?php 
                $rgb = sscanf($categoryColor, "#%02x%02x%02x");
                echo $rgb ? implode(',', $rgb) : '220,38,38'; 
            ?>;
            --ink: #03030a;
            --ink1: #07071a;
            --ink2: #0d0d23;
            --ink3: #131332;
            --surface: rgba(255,255,255,0.032);
            --surface2: rgba(255,255,255,0.055);
            --glass: rgba(13,13,35,0.72);
            --border: rgba(255,255,255,0.06);
            --border2: rgba(255,255,255,0.12);
            --text: #e8eaf6;
            --text2: #9ea3c0;
            --text3: #525780;
            --gold: #f59e0b;
            --gold2: #d97706;
            --cyan: #00e5ff;
            --green: #00e676;
            --red: #ff1744;
            --r: 14px;
            --r-lg: 22px;
            --r-xl: 32px;
            --shadow-sm: 0 4px 20px rgba(0,0,0,0.4);
            --shadow: 0 12px 50px rgba(0,0,0,0.6);
            --shadow-lg: 0 30px 80px rgba(0,0,0,0.7);
            --ease: cubic-bezier(0.23, 1, 0.32, 1);
            --ease-back: cubic-bezier(0.34, 1.56, 0.64, 1);
            --font-display: 'Bebas Neue', cursive;
            --font-body: 'DM Sans', system-ui, sans-serif;
            --font-mono: 'DM Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            background: var(--ink);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            padding-top: 80px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 28px;
        }

        /* ─────────────────────────────────────────────────────────────
           HERO SECTION — WITH EMOJI AND TWO-LINE DESIGN
           ───────────────────────────────────────────────────────────── */
        .categories-hero {
            position: relative;
            min-height: 55vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: var(--ink);
            padding: 120px 0 60px;
        }

        .hero__bg-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(var(--anime-color-rgb), 0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(var(--anime-color-rgb), 0.04) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(ellipse at 50% 50%, black 20%, transparent 80%);
            pointer-events: none;
        }

        .hero__bg-glow {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 80% 50%, rgba(var(--anime-color-rgb), 0.12) 0%, transparent 60%),
                        radial-gradient(ellipse 50% 50% at 10% 80%, rgba(var(--anime-color-rgb), 0.09) 0%, transparent 60%);
            pointer-events: none;
            animation: bgBreath 8s ease-in-out infinite;
        }

        @keyframes bgBreath { 0%,100%{opacity:1} 50%{opacity:0.6} }

        .categories-hero__content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero__kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--anime-color);
            margin-bottom: 28px;
            opacity: 0;
            animation: slideUp 0.6s var(--ease) 0.1s forwards;
        }

        .hero__kicker-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--anime-color);
            box-shadow: 0 0 14px var(--anime-color);
            animation: live 1.8s infinite;
        }

        @keyframes live { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(0.7)} }

        .hero__h1 {
            font-family: var(--font-display);
            font-size: clamp(3rem, 8vw, 6rem);
            line-height: 0.92;
            letter-spacing: 0.02em;
            margin-bottom: 20px;
        }

        .hero__h1-line {
            display: block;
            overflow: hidden;
        }

        .hero__h1-line span {
            display: block;
            opacity: 0;
            transform: translateY(100%);
            animation: revealLine 0.7s var(--ease) forwards;
        }

        .hero__h1-line:first-child span { animation-delay: 0.2s; color: var(--text); }
        .hero__h1-line:last-child span { 
            animation-delay: 0.35s; 
            color: transparent; 
            -webkit-text-stroke: 2px var(--anime-color);
        }

        /* Emoji accent styling */
        .hero__emoji {
            font-size: clamp(2.5rem, 6vw, 4rem);
            margin-bottom: 15px;
            display: block;
            animation: emojiBounce 1s ease-in-out infinite;
        }

        @keyframes emojiBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @keyframes revealLine { to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

        .hero__desc {
            font-size: 1.05rem;
            font-weight: 300;
            line-height: 1.7;
            color: var(--text2);
            margin-bottom: 32px;
            opacity: 0;
            animation: slideUp 0.6s var(--ease) 0.5s forwards;
        }

        /* ─────────────────────────────────────────────────────────────
           SECTION STYLES
           ───────────────────────────────────────────────────────────── */
        .section-wrap {
            padding: 20px 0 60px;
            position: relative;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 0 40px;
            margin-bottom: 36px;
            flex-wrap: wrap;
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .section-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            border: 2px solid;
            background: rgba(0,0,0,0.3);
            border-color: var(--char-color);
            color: var(--char-color);
            box-shadow: 0 0 20px var(--char-color);
        }

        .section-title-wrap { flex: 1; }

        .section-eyebrow {
            font-family: 'Raleway', sans-serif;
            font-size: 0.65rem;
            letter-spacing: 0.4em;
            font-weight: 600;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .section-title {
            font-family: 'Cinzel Decorative', serif;
            font-size: clamp(1.6rem, 4vw, 2.6rem);
            font-weight: 700;
            line-height: 1;
            color: var(--char-color);
            text-shadow: 0 0 20px var(--char-color);
        }

        .section-count {
            font-family: 'Cinzel', serif;
            font-size: 0.8rem;
            letter-spacing: 0.15em;
            padding: 4px 14px;
            border-radius: 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #9ca3af;
            flex-shrink: 0;
        }

        .section-line {
            height: 1px;
            margin: 0 40px 36px;
            background: linear-gradient(90deg, rgba(var(--anime-color-rgb), 0.3), transparent);
        }

        /* ─────────────────────────────────────────────────────────────
           STATIC GRID — 4 PER ROW, 8 SHOWN BY DEFAULT, "VIEW ALL" REVEALS REST
           ───────────────────────────────────────────────────────────── */
        .char-grid { padding: 0 40px; margin-bottom: 20px; }
        .char-grid-more { margin-bottom: 0; }

        .grid-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .grid-row:last-child { margin-bottom: 0; }

        .view-all-wrap {
            display: flex;
            justify-content: center;
            padding: 4px 40px 30px;
        }

        .view-all-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 26px;
            border-radius: 30px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--anime-color);
            color: var(--anime-color);
            font-family: var(--font-mono);
            font-size: 0.78rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .view-all-btn:hover {
            background: var(--anime-color);
            color: #000;
        }

        .view-all-btn.expanded .view-all-arrow i { transform: rotate(180deg); }
        .view-all-arrow i { transition: transform 0.3s; }

        /* WALLPAPER CARDS */
        .wcard {
            background: #1c1c1c;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.07);
            overflow: hidden;
            transition: transform 0.35s, border-color 0.35s, box-shadow 0.35s;
            position: relative;
            cursor: pointer;
        }

        .wcard:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: var(--anime-color);
            box-shadow: 0 20px 50px rgba(0,0,0,0.7), 0 0 0 1px var(--anime-color);
        }

        .wcard::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            z-index: 5;
            opacity: 0;
            transition: opacity 0.3s;
            background: linear-gradient(90deg, transparent, var(--anime-color), transparent);
        }

        .wcard:hover::before { opacity: 1; }

        .wcard-img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background: #111;
        }

        .wcard-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
            filter: brightness(0.92) contrast(1.05) saturate(1.1);
            pointer-events: none;
            user-select: none;
            -webkit-user-drag: none;
        }

        .wcard:hover .wcard-img-wrap img {
            transform: scale(1.08);
            filter: brightness(1.0) contrast(1.08) saturate(1.2);
        }

        .wcard-img-wrap::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 60px;
            background: linear-gradient(to top, rgba(26,18,42,0.9), transparent);
            pointer-events: none;
        }

        .wcard-zoom {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0);
            transition: background 0.3s;
            z-index: 3;
        }

        .wcard-zoom i {
            font-size: 2rem;
            color: white;
            opacity: 0;
            transform: scale(0.6);
            transition: all 0.3s;
        }

        /* Image failed-to-load fallback + manual retry */
        .img-retry-overlay {
            display: none;
            position: absolute;
            inset: 0;
            align-items: center;
            justify-content: center;
            background: #161616;
            z-index: 4;
        }

        .wcard-img-wrap.img-failed .img-retry-overlay { display: flex; }
        .wcard-img-wrap.img-failed img { visibility: hidden; }

        .img-retry-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 30px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.18);
            color: var(--text2, #9ea3c0);
            font-family: var(--font-mono, monospace);
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.25s;
        }

        .img-retry-btn:hover {
            background: var(--anime-color);
            border-color: var(--anime-color);
            color: #000;
        }

        .wcard:hover .wcard-zoom {
            background: rgba(0,0,0,0.25);
        }

        .wcard:hover .wcard-zoom i {
            opacity: 1;
            transform: scale(1);
        }

        .wcard-new {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: white;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            z-index: 6;
            box-shadow: 0 2px 12px rgba(0,0,0,0.5);
            animation: pulseBadge 2s infinite;
        }

        @keyframes pulseBadge {
            0%,100% { box-shadow: 0 2px 12px rgba(0,0,0,0.5); }
            50% { box-shadow: 0 2px 20px rgba(0,0,0,0.8); }
        }

        .fav-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 6;
        }

        .fav-btn i { font-size: 1rem; color: rgba(255,255,255,0.8); }
        .fav-btn:hover { background: #ff4757; transform: scale(1.15); }
        .fav-btn:hover i { color: white; }
        .fav-btn.active { background: #ff4757; }
        .fav-btn.active i { color: white; animation: heartPop 0.3s ease; }

        @keyframes heartPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }

        .wcard-body { padding: 14px 16px 0; }
        .wcard-title {
            font-family: 'Cinzel', serif;
            font-size: 0.82rem;
            font-weight: 600;
            color: #f5f0e8;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 36px;
        }

        .wcard-stats {
            display: flex;
            gap: 16px;
            margin: 10px 0 0;
            font-size: 0.72rem;
            color: #9ca3af;
        }

        .wcard-stats span { display: inline-flex; align-items: center; gap: 5px; }
        .wcard-stats i { font-size: 0.65rem; }

        .wcard-dl {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            margin-top: 12px;
            background: rgba(255,255,255,0.03);
            border-top: 1px solid rgba(255,255,255,0.06);
            text-decoration: none;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: all 0.3s;
            border-radius: 0 0 16px 16px;
        }

        .wcard-dl:hover { background: rgba(0,0,0,0.2); color: var(--anime-color); }
        .wcard-dl .dl-arrow {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1px solid currentColor;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            transition: all 0.3s;
        }

        .wcard-dl:hover .dl-arrow {
            background: var(--anime-color);
            border-color: var(--anime-color);
            color: #000;
            transform: translateX(3px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            background: rgba(255,255,255,0.02);
            border-radius: 16px;
            border: 1px dashed rgba(255,255,255,0.08);
            margin: 0 40px;
        }

        .empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: 0.5; }

        .notif {
            position: fixed;
            bottom: 28px;
            right: 28px;
            padding: 12px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            z-index: 9999;
            background: #333;
            animation: notifIn 0.35s;
        }

        @keyframes notifIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ─────────────────────────────────────────────────────────────
           CHARACTER FILTER MODE — "View All" isolates one character
           ───────────────────────────────────────────────────────────── */
        .filter-bar {
            display: none;
            position: sticky;
            top: 0;
            z-index: 500;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 40px;
            background: rgba(7,7,26,0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border2);
        }

        .filter-bar.active { display: flex; }

        .filter-bar-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            color: var(--text2);
        }

        .filter-bar-label strong {
            color: var(--anime-color);
            font-family: 'Cinzel', serif;
            font-size: 0.95rem;
        }

        .filter-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 30px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border2);
            color: var(--text);
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.25s;
        }

        .filter-back-btn:hover {
            background: var(--anime-color);
            border-color: var(--anime-color);
            color: #000;
        }

        body.filtering .section-wrap { display: none; }
        body.filtering .section-wrap.filter-visible { display: block; }
        body.filtering .view-all-wrap { display: none; }

        @media (max-width: 768px) {
            .filter-bar { padding: 12px 20px; }
        }

        /* Responsive */
        @media (max-width: 1000px) {
            .grid-row { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .char-grid { padding: 0 20px; }
            .section-header { padding: 0 20px; }
            .section-line { margin: 0 20px 28px; }
            .view-all-wrap { padding: 4px 20px 24px; }
            .grid-row { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .categories-hero { padding: 100px 0 40px; min-height: auto; }
        }
        @media (max-width: 480px) {
            .grid-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="filter-bar" id="filterBar">
        <div class="filter-bar-label">
            <i class="fas fa-filter"></i>
            <span>Showing only <strong id="filterBarCharName">—</strong></span>
        </div>
        <button class="filter-back-btn" id="filterBackBtn">
            <i class="fas fa-arrow-left"></i>
            <span>Show All Characters</span>
        </button>
    </div>

    <main>
        <!-- HERO SECTION - WITH EMOJI AND TWO-LINE DESIGN -->
        <section class="categories-hero" id="heroSection">
            <div class="hero__bg-grid"></div>
            <div class="hero__bg-glow"></div>
            
            <div class="container">
                <div class="categories-hero__content">
                    <div class="hero__emoji"><?php echo $categoryEmoji; ?></div>
                    
                    <div class="hero__kicker">
                        <span class="hero__kicker-dot"></span>
                        Explore Collection
                    </div>
                    
                    <h1 class="hero__h1">
                        <div class="hero__h1-line"><span><?php echo $firstLine; ?></span></div>
                        <div class="hero__h1-line"><span><?php echo $secondLine; ?></span></div>
                    </h1>
                    
                    <p class="hero__desc"><?php echo htmlspecialchars($animeDesc); ?></p>
                </div>
            </div>
        </section>

        <?php foreach ($characters as $key => $char): ?>
            <?php if ($char['count'] > 0): ?>
            <?php $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '-', $key); ?>
            <div class="section-wrap" id="section-<?php echo $safeKey; ?>" data-char-name="<?php echo htmlspecialchars($char['name']); ?>">
                <div class="section-header">
                    <div class="section-header-left">
                        <div class="section-icon" style="--char-color: <?php echo $char['color']; ?>">
                            <i class="fas <?php echo $char['icon']; ?>"></i>
                        </div>
                        <div class="section-title-wrap">
                            <p class="section-eyebrow"><?php echo htmlspecialchars($char['description']); ?></p>
                            <h2 class="section-title" style="--char-color: <?php echo $char['color']; ?>">
                                <?php echo htmlspecialchars($char['name']); ?>
                            </h2>
                        </div>
                    </div>
                    <span class="section-count"><?php echo $char['count']; ?> wallpapers</span>
                </div>
                <div class="section-line"></div>

                <?php echo renderCharacterGrid($char['wallpapers'], $key); ?>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (empty($characters) || array_sum(array_column($characters, 'count')) == 0): ?>
            <div class="empty-state">
                <i class="fas fa-image"></i>
                <p>No wallpapers found for <?php echo htmlspecialchars($categoryName); ?> yet. Check back soon!</p>
            </div>
        <?php endif; ?>

        <div style="height: 80px;"></div>
    </main>

    <?php include('footer.php'); ?>

    <script>
        document.addEventListener('contextmenu', function(e) {
            if (e.target.tagName === 'IMG') { e.preventDefault(); return false; }
        });
        
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('dragstart', function(e) { e.preventDefault(); return false; });
        });

        // ─────────────────────────────────────────────────────────
        // WALLPAPER IMAGE LOAD RESILIENCE
        // If an image request fails (dropped connection, server
        // timeout, too many concurrent requests on desktop, etc.)
        // retry it automatically a couple of times with backoff.
        // If it still fails, show a manual "Retry" button instead
        // of silently leaving just the card title visible.
        // ─────────────────────────────────────────────────────────
        const MAX_AUTO_RETRIES = 3;

        window.__handleImgError = function (img) {
            const wrap = img.closest('.wcard-img-wrap');
            const retries = parseInt(img.dataset.retries || '0', 10);

            if (retries < MAX_AUTO_RETRIES) {
                img.dataset.retries = retries + 1;
                const delay = 500 * Math.pow(2, retries); // 500ms, 1s, 2s
                setTimeout(function () {
                    const baseSrc = img.dataset.src;
                    img.src = baseSrc + (baseSrc.indexOf('?') > -1 ? '&' : '?') + '_r=' + Date.now();
                }, delay);
            } else {
                if (wrap) wrap.classList.add('img-failed');
                console.warn('Wallpaper image failed to load after ' + MAX_AUTO_RETRIES + ' retries:', img.dataset.src);
            }
        };

        window.__retryImg = function (btn) {
            const wrap = btn.closest('.wcard-img-wrap');
            const img = wrap ? wrap.querySelector('img') : null;
            if (!img) return;
            wrap.classList.remove('img-failed');
            img.dataset.retries = '0';
            img.src = img.dataset.src + '?_r=' + Date.now();
        };

        $(document).ready(function () {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

            if (isLoggedIn) {
                $.getJSON('get_favorites.php', function (ids) {
                    if (ids && Array.isArray(ids)) {
                        ids.forEach(id => $(`.favorite-btn[data-id="${id}"]`).addClass('active'));
                    }
                }).fail(function() { console.log('Could not load favorites'); });
            }

            $('.favorite-btn').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (!isLoggedIn) {
                    if (confirm('Login to save favorites?')) window.location.href = 'login.php';
                    return;
                }

                const btn = $(this);
                const id = btn.data('id');
                const type = btn.data('type') || 'desktop';
                
                btn.prop('disabled', true).css('opacity', '0.5');

                $.ajax({
                    url: 'toggle_favorite.php',
                    method: 'POST',
                    data: { wallpaper_id: id, type: type },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const added = response.action === 'added';
                            btn.toggleClass('active', added);
                            notify(added ? '❤️ Added to favorites' : '💔 Removed from favorites', added ? 'success' : 'info');
                        } else {
                            notify(response.message || 'Something went wrong', 'error');
                        }
                    },
                    error: function() { notify('Network error. Please try again.', 'error'); },
                    complete: function() { btn.prop('disabled', false).css('opacity', '1'); }
                });
            });

            // "View All" — expands the character's full grid AND isolates
            // that character's section on the page (hides every other section).
            $('.view-all-btn').on('click', function () {
                const btn = $(this);
                const target = $('#' + btn.data('target'));
                const section = btn.closest('.section-wrap');
                const charName = section.data('char-name');

                // Expand this character's hidden wallpapers
                target.show();
                btn.addClass('expanded');
                btn.find('.view-all-text').text('Show Less');

                // Hide the hero + every other character section, keep only this one
                $('#heroSection').slideUp(250);
                $('.section-wrap').not(section).slideUp(250);
                section.addClass('filter-visible');
                $('body').addClass('filtering');

                // Show the sticky filter bar
                $('#filterBarCharName').text(charName);
                $('#filterBar').addClass('active');

                // Scroll to top of the isolated section
                $('html, body').animate({ scrollTop: section.offset().top - 90 }, 300);
            });

            // Back button — restore all sections, collapse expanded grids
            $('#filterBackBtn').on('click', function () {
                $('body').removeClass('filtering');
                $('.section-wrap').removeClass('filter-visible').show();
                $('#heroSection').show();
                $('#filterBar').removeClass('active');

                // Collapse any expanded "more" grids back to the default 8
                $('.view-all-btn.expanded').each(function () {
                    const btn = $(this);
                    const target = $('#' + btn.data('target'));
                    target.hide();
                    btn.removeClass('expanded');
                    btn.find('.view-all-text').text('View All ' + btn.data('total'));
                });

                $('html, body').animate({ scrollTop: 0 }, 300);
            });

            function notify(message, type) {
                const colors = { success: '#22c55e', info: '#3b82f6', error: '#ef4444' };
                const notification = $('<div class="notif">').css('background', colors[type] || '#333').html(`<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`);
                $('body').append(notification);
                setTimeout(() => notification.fadeOut(400, function() { $(this).remove(); }), 3000);
            }
        });
    </script>
</body>
</html>