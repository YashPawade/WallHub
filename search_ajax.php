<?php
// search_ajax.php - AJAX endpoint for live search
session_start();
include('includes/db.php');

header('Content-Type: text/html; charset=utf-8');

if (isset($_GET['q']) && strlen($_GET['q']) > 1) {
    $query = mysqli_real_escape_string($conn, $_GET['q']);
    
    // FIXED: Changed from 'wallpapers' to 'desktop_wallpapers' (search only desktop for now)
    // Can also search mobile wallpapers by adding a UNION query
    $sql = "SELECT w.id, w.title, w.image_path, w.character_name, c.name as category_name, 'desktop' as wallpaper_type
            FROM desktop_wallpapers w
            LEFT JOIN categories c ON w.category_id = c.id
            WHERE w.title LIKE '%$query%' 
               OR w.character_name LIKE '%$query%' 
               OR w.tags LIKE '%$query%'
            
            UNION
            
            SELECT m.id, m.title, m.image_path, m.character_name, c.name as category_name, 'mobile' as wallpaper_type
            FROM mobile_wallpapers m
            LEFT JOIN categories c ON m.category_id = c.id
            WHERE m.title LIKE '%$query%' 
               OR m.character_name LIKE '%$query%' 
               OR m.tags LIKE '%$query%'
            
            LIMIT 10";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            // FIXED: Removed /wallhub/ prefix from image path
            $image_path = htmlspecialchars($row['image_path']);
            $title = htmlspecialchars($row['title']);
            $character = htmlspecialchars($row['character_name'] ?? '');
            $category = htmlspecialchars($row['category_name'] ?? '');
            $type = $row['wallpaper_type'];
            
            echo '<a href="download.php?id=' . $row['id'] . '&type=' . $type . '" class="wh-sug">';
            echo '<img src="' . $image_path . '" alt="' . $title . '">';
            echo '<div>';
            echo '<strong>' . $title . '</strong>';
            echo '<small>' . $character . ' • ' . $category . ' <span style="color:#8b5cf6;">(' . ucfirst($type) . ')</span></small>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo '<div class="wh-msg"><i class="fas fa-search"></i> No results found for "' . htmlspecialchars($query) . '"</div>';
    }
} elseif (isset($_GET['q']) && strlen($_GET['q']) == 1) {
    echo '<div class="wh-msg"><i class="fas fa-info-circle"></i> Type at least 2 characters to search</div>';
}
?>