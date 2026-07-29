<?php
// search.php - Advanced Search Functionality (FIXED)
session_start();
include('includes/db.php');

// Check if database connection exists
if (!isset($conn) || !$conn || is_bool($conn)) {
    die("Database connection error. Please try again later.");
}

$pageTitle = "Search Wallpapers - WallHub";
$search_query = '';
$search_type = 'all';
$results = [];
$result_count = 0;
$popular_searches = [];
$trending = [];

// Get search parameters
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_query = mysqli_real_escape_string($conn, trim($_GET['q']));
    $search_type = isset($_GET['type']) ? $_GET['type'] : 'all';
    
    // SIMPLIFIED QUERY - Removed UNION to debug
    $sql = "SELECT w.*, 
                   COALESCE(c.name, 'Uncategorized') as category_name, 
                   COALESCE(c.slug, 'uncategorized') as category_slug, 
                   'desktop' as wallpaper_type
            FROM desktop_wallpapers w 
            LEFT JOIN categories c ON w.category_id = c.id 
            WHERE 1=1";
    
    switch($search_type) {
        case 'title':
            $sql .= " AND w.title LIKE '%$search_query%'";
            break;
        case 'character':
            $sql .= " AND w.character_name LIKE '%$search_query%'";
            break;
        case 'tags':
            $sql .= " AND w.tags LIKE '%$search_query%'";
            break;
        case 'category':
            $sql .= " AND c.name LIKE '%$search_query%'";
            break;
        default: // all
            $sql .= " AND (w.title LIKE '%$search_query%' 
                        OR w.character_name LIKE '%$search_query%' 
                        OR w.tags LIKE '%$search_query%' 
                        OR c.name LIKE '%$search_query%')";
            break;
    }
    
    $sql .= " ORDER BY w.downloads DESC, w.views DESC LIMIT 50";
    
    // Execute query with error checking
    $results = mysqli_query($conn, $sql);
    if ($results && !is_bool($results)) {
        $result_count = mysqli_num_rows($results);
    } else {
        $result_count = 0;
        // Log the error for debugging
        error_log("Search query failed: " . mysqli_error($conn));
    }
}

// Get popular search terms from database (if table exists)
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'search_logs'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    $popular_searches_query = mysqli_query($conn, "
        SELECT search_term, COUNT(*) as count 
        FROM search_logs 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_term 
        ORDER BY count DESC 
        LIMIT 10
    ");
    if ($popular_searches_query && !is_bool($popular_searches_query)) {
        $popular_searches = $popular_searches_query;
    }
}

// Get trending wallpapers
$trending_query = mysqli_query($conn, "
    SELECT w.*, COALESCE(c.name, 'Uncategorized') as category_name, 'desktop' as wallpaper_type
    FROM desktop_wallpapers w 
    LEFT JOIN categories c ON w.category_id = c.id 
    ORDER BY w.views DESC, w.downloads DESC 
    LIMIT 8
");
if ($trending_query && !is_bool($trending_query)) {
    $trending = $trending_query;
}
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
        
        .search-hero {
            background: linear-gradient(135deg, rgba(225, 29, 29, 0.2), rgba(108, 92, 231, 0.2));
            padding: 50px 0;
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(225, 29, 29, 0.3);
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .search-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .search-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, #e11d1d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .search-title p {
            color: #aaa;
            margin-top: 10px;
        }
        
        .search-box {
            background: rgba(20, 20, 30, 0.95);
            border-radius: 60px;
            padding: 5px;
            border: 1px solid rgba(225, 29, 29, 0.3);
            transition: all 0.3s;
        }
        
        .search-box:focus-within {
            border-color: #e11d1d;
            box-shadow: 0 0 20px rgba(225, 29, 29, 0.3);
        }
        
        .search-input {
            background: transparent;
            border: none;
            padding: 15px 20px;
            font-size: 1.1rem;
            color: #fff;
            width: 100%;
        }
        
        .search-input:focus {
            outline: none;
        }
        
        .search-input::placeholder {
            color: #666;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(225, 29, 29, 0.4);
        }
        
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: rgba(20, 20, 30, 0.8);
            border: 1px solid rgba(225, 29, 29, 0.3);
            padding: 8px 20px;
            border-radius: 30px;
            color: #aaa;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            color: white;
            border-color: transparent;
        }
        
        .results-section {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .results-count {
            color: #aaa;
            font-size: 0.9rem;
        }
        
        .results-count strong {
            color: #e11d1d;
            font-size: 1.2rem;
        }
        
        .sort-options select {
            background: rgba(20, 20, 30, 0.95);
            border: 1px solid rgba(225, 29, 29, 0.3);
            color: #fff;
            padding: 8px 15px;
            border-radius: 10px;
            cursor: pointer;
        }
        
        .wallpapers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .wallpaper-card {
            background: rgba(20, 20, 30, 0.95);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(225, 29, 29, 0.3);
            transition: all 0.3s;
            position: relative;
        }
        
        .wallpaper-card:hover {
            transform: translateY(-10px);
            border-color: #e11d1d;
            box-shadow: 0 10px 30px rgba(225, 29, 29, 0.2);
        }
        
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
        
        .wallpaper-image {
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        
        .wallpaper-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .wallpaper-card:hover .wallpaper-image img {
            transform: scale(1.1);
        }
        
        .wallpaper-overlay {
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
        
        .wallpaper-card:hover .wallpaper-overlay {
            opacity: 1;
        }
        
        .download-btn {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .download-btn:hover {
            transform: scale(1.05);
            color: white;
        }
        
        .wallpaper-info {
            padding: 15px;
        }
        
        .wallpaper-title {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .wallpaper-character {
            font-size: 0.8rem;
            color: #e11d1d;
            margin-bottom: 10px;
        }
        
        .wallpaper-stats {
            display: flex;
            gap: 15px;
            font-size: 0.7rem;
            color: #aaa;
        }
        
        .wallpaper-stats i {
            margin-right: 5px;
        }
        
        .badge-new {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #e11d1d, #ff6b6b);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 1;
        }
        
        .search-highlight {
            background: rgba(225, 29, 29, 0.3);
            color: #ff6b6b;
            padding: 0 2px;
            border-radius: 3px;
        }
        
        .popular-searches {
            max-width: 1400px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }
        
        .popular-title {
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .popular-title i {
            color: #e11d1d;
        }
        
        .popular-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .popular-tag {
            background: rgba(20, 20, 30, 0.8);
            border: 1px solid rgba(225, 29, 29, 0.3);
            padding: 8px 18px;
            border-radius: 30px;
            color: #aaa;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .popular-tag:hover {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            color: white;
            transform: translateY(-2px);
        }
        
        .trending-section {
            max-width: 1400px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #e11d1d;
        }
        
        .trending-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .trending-item {
            background: rgba(20, 20, 30, 0.95);
            border-radius: 10px;
            overflow: hidden;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .trending-item:hover {
            transform: translateY(-5px);
        }
        
        .trending-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .trending-info {
            padding: 10px;
        }
        
        .trending-info h4 {
            font-size: 0.9rem;
            color: #fff;
            margin: 0;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #e11d1d;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            color: #fff;
            margin-bottom: 10px;
        }
        
        .no-results p {
            color: #aaa;
        }
        
        .suggestion-tags {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .wallpapers-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .filter-tabs {
                gap: 8px;
            }
            
            .filter-btn {
                padding: 5px 12px;
                font-size: 0.8rem;
            }
            
            .search-title h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="search-hero">
        <div class="search-container">
            <div class="search-title">
                <h1><i class="fas fa-search"></i> Search Wallpapers</h1>
                <p>Find your favorite anime wallpapers by title, character, tags, or category</p>
            </div>
            
            <form method="GET" action="search.php">
                <div class="search-box">
                    <div class="row g-0">
                        <div class="col-10">
                            <input type="text" name="q" class="search-input" 
                                   placeholder="Search by title, character, tags, or category..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
                        </div>
                        <div class="col-2">
                            <button type="submit" class="search-btn w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="filter-tabs">
                    <a href="?q=<?php echo urlencode($search_query); ?>&type=all" 
                       class="filter-btn <?php echo $search_type == 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-globe"></i> All
                    </a>
                    <a href="?q=<?php echo urlencode($search_query); ?>&type=title" 
                       class="filter-btn <?php echo $search_type == 'title' ? 'active' : ''; ?>">
                        <i class="fas fa-heading"></i> Title
                    </a>
                    <a href="?q=<?php echo urlencode($search_query); ?>&type=character" 
                       class="filter-btn <?php echo $search_type == 'character' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Character
                    </a>
                    <a href="?q=<?php echo urlencode($search_query); ?>&type=tags" 
                       class="filter-btn <?php echo $search_type == 'tags' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i> Tags
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if(empty($search_query)): ?>
        <?php if($popular_searches && mysqli_num_rows($popular_searches) > 0): ?>
        <div class="popular-searches">
            <div class="popular-title">
                <i class="fas fa-fire"></i> Popular Searches
            </div>
            <div class="popular-tags">
                <?php while($term = mysqli_fetch_assoc($popular_searches)): ?>
                    <a href="search.php?q=<?php echo urlencode($term['search_term']); ?>&type=all" class="popular-tag">
                        <?php echo htmlspecialchars($term['search_term']); ?>
                        <span class="badge bg-danger ms-1"><?php echo $term['count']; ?></span>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="trending-section">
            <div class="section-title">
                <i class="fas fa-chart-line"></i> Trending Wallpapers
            </div>
            <div class="trending-grid">
                <?php if ($trending && mysqli_num_rows($trending) > 0): ?>
                <?php while($wallpaper = mysqli_fetch_assoc($trending)): ?>
                    <a href="download.php?id=<?php echo $wallpaper['id']; ?>&type=desktop" class="trending-item">
                        <img src="<?php echo htmlspecialchars($wallpaper['image_path']); ?>" alt="<?php echo $wallpaper['title']; ?>">
                        <div class="trending-info">
                            <h4><?php echo htmlspecialchars($wallpaper['title']); ?></h4>
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> <?php echo number_format($wallpaper['views']); ?> views
                            </small>
                        </div>
                    </a>
                <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No wallpapers found. Please add some wallpapers.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    Found <strong><?php echo $result_count; ?></strong> results for 
                    "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <?php if($search_type != 'all'): ?>
                        <span class="text-muted">in <span class="text-danger"><?php echo ucfirst($search_type); ?></span></span>
                    <?php endif; ?>
                </div>
                <div class="sort-options">
                    <select id="sortBy" onchange="sortResults(this.value)">
                        <option value="newest">Newest First</option>
                        <option value="popular">Most Popular</option>
                        <option value="downloads">Most Downloaded</option>
                        <option value="views">Most Viewed</option>
                    </select>
                </div>
            </div>
            
            <?php if($result_count > 0 && $results): ?>
                <div class="wallpapers-grid" id="resultsGrid">
                    <?php 
                    while($wallpaper = mysqli_fetch_assoc($results)): 
                        $is_new = (strtotime($wallpaper['created_at']) > strtotime('-7 days'));
                    ?>
                        <div class="wallpaper-card" data-date="<?php echo $wallpaper['created_at']; ?>" 
                             data-downloads="<?php echo $wallpaper['downloads']; ?>" 
                             data-views="<?php echo $wallpaper['views']; ?>">
                            <span class="type-badge type-<?php echo $wallpaper['wallpaper_type']; ?>">
                                <i class="fas <?php echo $wallpaper['wallpaper_type'] == 'desktop' ? 'fa-desktop' : 'fa-mobile-alt'; ?>"></i>
                                <?php echo ucfirst($wallpaper['wallpaper_type']); ?>
                            </span>
                            <?php if($is_new): ?>
                                <div class="badge-new">NEW</div>
                            <?php endif; ?>
                            <div class="wallpaper-image">
                                <img src="<?php echo htmlspecialchars($wallpaper['image_path']); ?>" alt="<?php echo $wallpaper['title']; ?>" loading="lazy">
                                <div class="wallpaper-overlay">
                                    <a href="download.php?id=<?php echo $wallpaper['id']; ?>&type=<?php echo $wallpaper['wallpaper_type']; ?>" class="download-btn">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                            <div class="wallpaper-info">
                                <div class="wallpaper-title">
                                    <?php 
                                    $title = htmlspecialchars($wallpaper['title']);
                                    if(!empty($search_query)) {
                                        $title = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="search-highlight">$1</span>', $title);
                                    }
                                    echo $title;
                                    ?>
                                </div>
                                <div class="wallpaper-character">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($wallpaper['character_name'] ?: 'Various'); ?>
                                </div>
                                <div class="wallpaper-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($wallpaper['views']); ?></span>
                                    <span><i class="fas fa-download"></i> <?php echo number_format($wallpaper['downloads']); ?></span>
                                    <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($wallpaper['category_name']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No results found for "<?php echo htmlspecialchars($search_query); ?>"</h3>
                    <p>Try searching for something else or browse our popular wallpapers below.</p>
                    <div class="suggestion-tags">
                        <a href="search.php?q=luffy" class="popular-tag">Luffy</a>
                        <a href="search.php?q=zoro" class="popular-tag">Zoro</a>
                        <a href="search.php?q=anime" class="popular-tag">Anime</a>
                        <a href="search.php?q=4k" class="popular-tag">4K</a>
                    </div>
                </div>
                
                <div class="trending-section mt-4">
                    <div class="section-title">
                        <i class="fas fa-fire"></i> Popular Wallpapers You Might Like
                    </div>
                    <div class="trending-grid">
                        <?php 
                        if ($trending && mysqli_num_rows($trending) > 0):
                        mysqli_data_seek($trending, 0);
                        while($wallpaper = mysqli_fetch_assoc($trending)): 
                        ?>
                            <a href="download.php?id=<?php echo $wallpaper['id']; ?>&type=desktop" class="trending-item">
                                <img src="<?php echo htmlspecialchars($wallpaper['image_path']); ?>" alt="<?php echo $wallpaper['title']; ?>">
                                <div class="trending-info">
                                    <h4><?php echo htmlspecialchars($wallpaper['title']); ?></h4>
                                </div>
                            </a>
                        <?php endwhile; 
                        endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php include('footer.php'); ?>
    
    <script>
        function sortResults(sortBy) {
            const grid = document.getElementById('resultsGrid');
            if (!grid) return;
            const cards = Array.from(grid.getElementsByClassName('wallpaper-card'));
            
            cards.sort((a, b) => {
                if (sortBy === 'newest') {
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
                } else if (sortBy === 'popular') {
                    const aValue = parseInt(a.dataset.downloads) + parseInt(a.dataset.views);
                    const bValue = parseInt(b.dataset.downloads) + parseInt(b.dataset.views);
                    return bValue - aValue;
                } else if (sortBy === 'downloads') {
                    return parseInt(b.dataset.downloads) - parseInt(a.dataset.downloads);
                } else if (sortBy === 'views') {
                    return parseInt(b.dataset.views) - parseInt(a.dataset.views);
                }
                return 0;
            });
            
            cards.forEach(card => grid.appendChild(card));
        }
    </script>
</body>
</html>
