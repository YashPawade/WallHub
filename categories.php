<?php
// categories.php - Modern Categories Page (Dark Red Theme)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

$pageTitle = "Categories | WallHub - Browse Premium Wallpapers";

// Define categories with DARK RED color scheme
$categories = [
    ['name' => 'Anime', 'slug' => 'anime', 'icon' => 'fa-robot', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'One Piece, Naruto, Demon Slayer, JJK & more', 'image' => '/images/dragonball/_son_goku_dragon_ball_and_1_more_drawn_by_horang4628_e82f50e279.png', 'type' => 'multi_category', 'link' => 'anime.php'],
    ['name' => 'Movies & TV', 'slug' => 'movies', 'icon' => 'fa-film', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Hollywood, Bollywood, Marvel, DC & Series', 'image' => '/images/the-batman/23.jpg', 'type' => 'multi_category', 'link' => 'movies.php'],
    ['name' => 'Phone', 'slug' => 'phone', 'icon' => 'fa-mobile-alt', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Mobile wallpapers optimized for your phone screen', 'image' => '/images/mobile/movie/1779615115_uPbBFH_357794.jpg', 'type' => 'mobile', 'link' => 'mobile.php'],
    ['name' => 'Actress', 'slug' => 'actress', 'icon' => 'fa-star', 'color' => '#ec489a', 'glow' => 'rgba(236,72,153,0.3)', 'desc' => 'Tamannaah Bhatia,Sydney Sweeney,Priyanka Chopra & more', 'image' => '/images/internationalactress/american-actress-3840x2160-16623_20260614_161847_58398.jpg', 'type' => 'multi_category', 'link' => 'actress'],
    ['name' => 'Gaming', 'slug' => 'gaming', 'icon' => 'fa-gamepad', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Valorant, GTA, Fortnite, CS:GO & more', 'image' => '/images/gaming/bvcdfgwe.jpg', 'type' => 'single', 'link' => 'gaming.php'],
    ['name' => 'Nature', 'slug' => 'nature', 'icon' => 'fa-mountain', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Mountains, forests, sunsets & landscapes', 'image' => '/images/nature/guitar-island-3840x2160-25355.jpg', 'type' => 'single', 'link' => 'nature.php'],
    ['name' => 'Animal', 'slug' => 'animal', 'icon' => 'fa-paw', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Wildlife, pets & majestic creatures', 'image' => '/images/animal/650147.jpg', 'type' => 'single', 'link' => 'animal.php'],
    ['name' => 'Birds', 'slug' => 'bird', 'icon' => 'fa-dove', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Eagles, parrots & beautiful bird photography', 'image' => '/images/bird/zwergeule-owl-bokeh-portrait-blur-background-wildlife-orange-3840x2160-4307.jpg', 'type' => 'single', 'link' => 'bird.php'],
    ['name' => 'Vehicles', 'slug' => 'vehicle', 'icon' => 'fa-car', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Cars, bikes, supercars & aviation', 'image' => '/images/vehicle/aston-martin-db12-s-3840x2160-26242.jpg', 'type' => 'single', 'link' => 'vehicle.php'],
    ['name' => 'Fantasy', 'slug' => 'fantasy', 'icon' => 'fa-dragon', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Magic, dragons, mythical worlds & art', 'image' => '/images/fantasy/256100.jpg', 'type' => 'single', 'link' => 'fantasy.php'],
    ['name' => 'Space', 'slug' => 'space', 'icon' => 'fa-space-shuttle', 'color' => '#dc2626', 'glow' => 'rgba(220,38,38,0.3)', 'desc' => 'Galaxies, planets, stars & cosmic art', 'image' => '/images/space/gargantua-black-3840x2160-9659.jpg', 'type' => 'single', 'link' => 'space.php'],
];

// List of all anime category slugs in your database
$anime_categories = [
    'onepiece', 'naruto', 'bleach', 'jjk', 'demonslayer', 'aot', 'mha', 
    'chainsawman', 'hunterxhunter', 'tokyorevengers', 'spyxfamily', 
    'onepunchman', 'sololeveling', 'dandadan', 'bluelock', 'dragonball'
];

// List of all movies/TV category slugs in your database
$movies_categories = [
    'houseofthedragon', 'spider-man', 'game-of-thrones', 'avatar', 'stranger-things', 'ne-zha', 'interstellar', 'john-wick', 
    'the-witcher', 'breaking-bad', 'the-mandalorian', 'inception', 'the-batman'
];

// Fetch real category counts from database
$category_counts = [];

foreach ($categories as $cat) {
    $slug = $cat['slug'];
    $type = $cat['type'];
    $total_count = 0;
    
    // For ANIME category - count from ALL anime sub-categories
    if ($slug === 'anime') {
        $anime_slugs_string = "'" . implode("','", $anime_categories) . "'";
        $query = "SELECT COUNT(*) as count FROM desktop_wallpapers w 
                  LEFT JOIN categories c ON w.category_id = c.id 
                  WHERE c.slug IN ($anime_slugs_string)";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $count = mysqli_fetch_assoc($result)['count'];
            $total_count = $count;
        }
    }
    // For MOVIES & TV category - count from ALL movie/show sub-categories
    elseif ($slug === 'movies') {
        $movies_slugs_string = "'" . implode("','", $movies_categories) . "'";
        $query = "SELECT COUNT(*) as count FROM desktop_wallpapers w 
                  LEFT JOIN categories c ON w.category_id = c.id 
                  WHERE c.slug IN ($movies_slugs_string)";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $count = mysqli_fetch_assoc($result)['count'];
            $total_count = $count;
        }
    }
    // For 'phone' category, count from mobile_wallpapers
    elseif ($slug === 'phone') {
        $query = "SELECT COUNT(*) as count FROM mobile_wallpapers";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $count = mysqli_fetch_assoc($result)['count'];
            $total_count = $count;
        }
    } 
    // For all other categories, count from desktop_wallpapers
    else {
        $query = "SELECT COUNT(*) as count FROM desktop_wallpapers w 
                  LEFT JOIN categories c ON w.category_id = c.id 
                  WHERE c.slug = '$slug'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $count = mysqli_fetch_assoc($result)['count'];
            $total_count = $count;
        } else {
            $total_count = 0;
        }
    }
    
    $category_counts[$slug] = $total_count;
}

// Calculate total wallpapers (Desktop + Mobile)
$total_desktop_query = "SELECT COUNT(*) as count FROM desktop_wallpapers";
$total_desktop_result = mysqli_query($conn, $total_desktop_query);
$total_desktop = $total_desktop_result ? mysqli_fetch_assoc($total_desktop_result)['count'] : 0;

$total_mobile_query = "SELECT COUNT(*) as count FROM mobile_wallpapers";
$total_mobile_result = mysqli_query($conn, $total_mobile_query);
$total_mobile = $total_mobile_result ? mysqli_fetch_assoc($total_mobile_result)['count'] : 0;

$total_wallpapers = $total_desktop + $total_mobile;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Shared CSS -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    
    <style>
        /* ─────────────────────────────────────────────────────────────
           DESIGN TOKENS — DARK RED THEME
           ───────────────────────────────────────────────────────────── */
        :root {
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
            --accent: #dc2626;
            --accent2: #991b1b;
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background: var(--ink);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 28px;
        }

        /* ─────────────────────────────────────────────────────────────
           HERO SECTION — CATEGORIES
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
            background-image: linear-gradient(rgba(220,38,38,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(220,38,38,0.04) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(ellipse at 50% 50%, black 20%, transparent 80%);
            pointer-events: none;
        }

        .hero__bg-glow {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 80% 50%, rgba(220,38,38,0.12) 0%, transparent 60%),
                        radial-gradient(ellipse 50% 50% at 10% 80%, rgba(153,27,27,0.09) 0%, transparent 60%);
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
            color: var(--accent);
            margin-bottom: 28px;
            opacity: 0;
            animation: slideUp 0.6s var(--ease) 0.1s forwards;
        }

        .hero__kicker-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 14px var(--accent);
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
        .hero__h1-line:last-child span { animation-delay: 0.35s; color: transparent; -webkit-text-stroke: 2px var(--accent); }

        @keyframes revealLine { to { opacity:1; transform:translateY(0); } }
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

        /* Search Box */
        .search-wrapper {
            max-width: 550px;
            margin: 0 auto;
            opacity: 0;
            animation: slideUp 0.6s var(--ease) 0.7s forwards;
        }

        .search-box {
            display: flex;
            gap: 12px;
            background: var(--surface);
            border: 1px solid var(--border2);
            border-radius: 60px;
            padding: 6px 6px 6px 24px;
            backdrop-filter: blur(12px);
            transition: all 0.3s var(--ease);
        }

        .search-box:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 30px rgba(220,38,38,0.15);
        }

        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text);
            font-size: 0.95rem;
            font-family: var(--font-body);
            outline: none;
        }

        .search-box input::placeholder {
            color: var(--text3);
        }

        .search-box button {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            border-radius: 50px;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s var(--ease);
        }

        .search-box button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(220,38,38,0.4);
        }

        /* Stats Counter */
        .stats-pill {
            display: inline-flex;
            align-items: center;
            gap: 28px;
            background: var(--glass);
            border: 1px solid var(--border);
            backdrop-filter: blur(20px);
            border-radius: 60px;
            padding: 12px 28px;
            margin-top: 40px;
        }

        .stat-item {
            display: flex;
            align-items: baseline;
            gap: 8px;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--text3);
        }

        .stat-number {
            font-family: var(--font-display);
            font-size: 1.6rem;
            color: var(--accent);
            line-height: 1;
        }

        /* ─────────────────────────────────────────────────────────────
           CATEGORIES GRID
           ───────────────────────────────────────────────────────────── */
        .categories-section {
            padding: 80px 0 120px;
            background: var(--ink1);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 14px;
        }

        .section-tag::before {
            content: '';
            display: block;
            width: 30px;
            height: 1px;
            background: var(--gold);
            opacity: 0.6;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            letter-spacing: 0.02em;
            color: var(--text);
        }

        .section-title em {
            font-style: normal;
            -webkit-text-stroke: 1px rgba(255,255,255,0.3);
            color: transparent;
        }

        /* Categories Grid - 3 columns */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .category-card {
            position: relative;
            border-radius: var(--r-xl);
            overflow: hidden;
            text-decoration: none;
            background: var(--ink3);
            border: 1px solid var(--border);
            transition: all 0.4s var(--ease);
            animation: cardReveal 0.5s var(--ease) both;
            animation-delay: calc(var(--i, 0) * 60ms);
            display: block;
        }

        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .category-card:hover {
            transform: translateY(-8px);
            border-color: var(--c, var(--accent));
            box-shadow: 0 25px 50px rgba(0,0,0,0.6), 0 0 30px var(--c-glow, transparent);
        }

        .category-card__img {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
        }

        .category-card__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s var(--ease);
            pointer-events: none;
            user-select: none;
            -webkit-user-drag: none;
        }

        .category-card:hover .category-card__img img {
            transform: scale(1.08);
        }

        .category-card__gradient {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(3,3,10,0.95) 0%, rgba(3,3,10,0.3) 50%, transparent 80%);
        }

        .category-card__content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 28px 24px 24px;
        }

        .category-icon {
            font-size: 2.2rem;
            color: var(--c, var(--accent));
            margin-bottom: 12px;
            filter: drop-shadow(0 0 12px var(--c-glow, transparent));
            transition: transform 0.3s var(--ease-back);
        }

        .category-card:hover .category-icon {
            transform: scale(1.1);
        }

        .category-title {
            font-family: var(--font-display);
            font-size: 1.8rem;
            letter-spacing: 0.02em;
            color: var(--text);
            margin-bottom: 8px;
        }

        .category-desc {
            font-size: 0.85rem;
            color: var(--text2);
            line-height: 1.5;
            margin-bottom: 16px;
            opacity: 0.9;
        }

        .category-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .category-count {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 30px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--c, var(--text2));
        }

        .category-arrow {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s var(--ease);
            opacity: 0;
            transform: translateX(-10px);
        }

        .category-card:hover .category-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        .category-arrow i {
            font-size: 0.8rem;
            color: var(--c, var(--text));
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 80px 20px;
            background: var(--ink2);
            border-radius: var(--r-xl);
            border: 1px solid var(--border);
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 3rem;
            color: var(--text3);
            margin-bottom: 16px;
            display: block;
        }

        .no-results p {
            color: var(--text2);
            font-size: 1rem;
        }

        /* Disable right-click on images container */
        .category-card__img {
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .categories-hero { padding: 100px 0 40px; min-height: auto; }
            .categories-grid { grid-template-columns: 1fr; gap: 20px; }
            .categories-section { padding: 60px 0 80px; }
            .stats-pill { flex-wrap: wrap; justify-content: center; gap: 16px; }
            .hero__h1 { font-size: 3rem; }
            .category-title { font-size: 1.5rem; }
            .search-box { padding: 4px 4px 4px 18px; }
            .search-box button { width: 42px; height: 42px; }
        }

        @media (max-width: 480px) {
            .category-card__content { padding: 20px 18px 18px; }
            .category-title { font-size: 1.3rem; }
            .category-icon { font-size: 1.8rem; }
            .stats-pill { padding: 10px 20px; }
            .stat-number { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<?php include('header.php'); ?>

<!-- Hero Section -->
<section class="categories-hero">
    <div class="hero__bg-grid"></div>
    <div class="hero__bg-glow"></div>
    
    <div class="container">
        <div class="categories-hero__content">
            <div class="hero__kicker">
                <span class="hero__kicker-dot"></span>
                Explore Collections
            </div>
            
            <h1 class="hero__h1">
                <div class="hero__h1-line"><span>Browse</span></div>
                <div class="hero__h1-line"><span>Categories</span></div>
            </h1>
            
            <p class="hero__desc">
                Discover thousands of premium wallpapers organized by theme — from anime and gaming to nature and space.
            </p>
            
            <div class="search-wrapper">
                <div class="search-box">
                    <input type="text" id="categorySearch" placeholder="Search categories...">
                    <button id="searchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="stats-pill">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($categories); ?>+</span>
                    <span>Categories</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($total_wallpapers); ?>+</span>
                    <span>Wallpapers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4K</span>
                    <span>Ultra HD</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Grid Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">Curated Collections</div>
            <h2 class="section-title">Explore by <em>Theme</em></h2>
        </div>
        
        <div class="categories-grid" id="categoriesGrid">
            <?php foreach ($categories as $index => $cat): 
                $count = $category_counts[$cat['slug']] ?? 0;
                // Use the custom link from the array
                $url = $cat['link'];
            ?>
            <a href="<?= $url ?>" class="category-card" 
               style="--c:<?= $cat['color'] ?>; --c-glow:<?= $cat['glow'] ?>; --i:<?= $index ?>"
               data-name="<?= strtolower($cat['name']) ?>"
               data-desc="<?= strtolower($cat['desc']) ?>">
                <div class="category-card__img">
                    <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= $cat['name'] ?>" loading="lazy"
                         onerror="this.src='https:\/\/placehold.co\/800x450\/1a1a2e\/dc2626?text=<?= urlencode($cat['name']) ?>'">
                    <div class="category-card__gradient"></div>
                </div>
                <div class="category-card__content">
                    <div class="category-icon">
                        <i class="fas <?= $cat['icon'] ?>"></i>
                    </div>
                    <h3 class="category-title"><?= $cat['name'] ?></h3>
                    <p class="category-desc"><?= $cat['desc'] ?></p>
                    <div class="category-meta">
                        <span class="category-count">
                            <i class="fas fa-image" style="font-size:0.65rem;"></i> <?= number_format($count) ?> Wallpapers
                        </span>
                        <div class="category-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>

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

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('categorySearch');
        const searchBtn = document.getElementById('searchBtn');
        const categoriesGrid = document.getElementById('categoriesGrid');
        const allCards = document.querySelectorAll('.category-card');
        const noResultsTemplate = `
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <p>No categories found matching your search.</p>
            </div>
        `;
        
        function filterCategories() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            allCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const desc = card.getAttribute('data-desc') || '';
                const matches = searchTerm === '' || name.includes(searchTerm) || desc.includes(searchTerm);
                
                if (matches) {
                    card.style.display = '';
                    visibleCount++;
                    // Re-trigger animation
                    card.style.animation = 'none';
                    setTimeout(() => {
                        card.style.animation = '';
                    }, 10);
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            let noResultsDiv = document.querySelector('.no-results');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResultsDiv) {
                    categoriesGrid.insertAdjacentHTML('beforeend', noResultsTemplate);
                }
            } else {
                if (noResultsDiv) {
                    noResultsDiv.remove();
                }
            }
        }
        
        searchInput.addEventListener('input', filterCategories);
        searchBtn.addEventListener('click', filterCategories);
        
        // Keyboard Enter support
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterCategories();
            }
        });
        
        // Add tilt effect to category cards
        const cards = document.querySelectorAll('.category-card');
        cards.forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                this.style.transform = `perspective(1000px) rotateY(${x * 5}deg) rotateX(${y * -5}deg) translateY(-8px)`;
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    });
</script>

</body>
</html>