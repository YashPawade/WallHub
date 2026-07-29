<?php
// add.php - Admin/Owner can access (For Desktop Wallpapers)
session_start();
include('includes/db.php');

// Check if user is logged in AND is admin OR owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'owner')) {
    // Not authorized, redirect to login page with error message
    header('Location: login.php?error=unauthorized');
    exit();
}

// Create desktop_wallpapers table if it doesn't exist (for first-time setup)
$create_table = "CREATE TABLE IF NOT EXISTS desktop_wallpapers LIKE wallpapers";
mysqli_query($conn, $create_table);

// Add missing columns to desktop_wallpapers if they don't exist
$alterQueries = [
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS resolution VARCHAR(50) DEFAULT '4K'",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS tags TEXT",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS type VARCHAR(50) DEFAULT 'anime'",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS source_name VARCHAR(100)",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS views INT DEFAULT 0",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS downloads INT DEFAULT 0",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS character_name VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE desktop_wallpapers ADD COLUMN IF NOT EXISTS orientation VARCHAR(20) DEFAULT 'landscape'"
];

foreach ($alterQueries as $query) {
    @mysqli_query($conn, $query);
}

// Get categories
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

// If no categories exist or missing some, add the new categories
$newCategories = [
    'Chainsaw Man' => 'chainsaw-man',
    'Hunter x Hunter' => 'hunter-x-hunter',
    'Spy x Family' => 'spy-x-family',
    'Tokyo Revengers' => 'tokyo-revengers',
    'One Punch Man' => 'one-punch-man',
    'Solo Leveling' => 'solo-leveling',
    'Dandadan' => 'dandadan',
    'Blue Lock' => 'blue-lock'
];

foreach ($newCategories as $catName => $catSlug) {
    $checkQuery = "SELECT id FROM categories WHERE name = '$catName'";
    $checkResult = mysqli_query($conn, $checkQuery);
    if (mysqli_num_rows($checkResult) == 0) {
        $insertQuery = "INSERT INTO categories (name, slug) VALUES ('$catName', '$catSlug')";
        mysqli_query($conn, $insertQuery);
    }
}

// Refresh categories after adding new ones
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $character_name = mysqli_real_escape_string($conn, $_POST['character_name']);
    $category_id = (int)$_POST['category_id'];
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $source_name = mysqli_real_escape_string($conn, $_POST['source_name']);
    $resolution = mysqli_real_escape_string($conn, $_POST['resolution']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    
    // Get category name for folder
    $catQuery = "SELECT name, slug FROM categories WHERE id = $category_id";
    $catResult = mysqli_query($conn, $catQuery);
    $category = mysqli_fetch_assoc($catResult);
    
    // Use the slug for folder name (keep hyphens)
    $category_folder = strtolower($category['slug'] ?? $category['name']);
    $category_folder = preg_replace('/[^a-zA-Z0-9-]/', '', $category_folder);
    
    // Handle image upload
    $image_path = '';
    $upload_success = false;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Create category subfolder
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/' . $category_folder . '/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Check file type
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed)) {
            $message = "❌ Invalid file type. Allowed: JPG, PNG, WEBP, GIF";
            $messageType = "danger";
        } elseif ($_FILES['image']['size'] > 52428800) { // 50MB limit
            $message = "❌ File too large. Maximum size is 50MB.";
            $messageType = "danger";
        } else {
            // ============================================================
            // FIXED: Generate UNIQUE filename using timestamp + random hash
            // This prevents files from overwriting each other!
            // ============================================================
            
            // Get original filename (clean it)
            $original_name = $_FILES['image']['name'];
            $file_name_without_ext = pathinfo($original_name, PATHINFO_FILENAME);
            $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $file_name_without_ext);
            
            // If clean name is empty, use a default
            if (empty($clean_name)) {
                $clean_name = 'wallpaper';
            }
            
            // Generate unique identifier: timestamp + random number
            // Format: luffy_20240612_143025_48291.jpg
            $timestamp = date('Ymd_His');
            $random = mt_rand(10000, 99999);
            $unique_filename = $clean_name . '_' . $timestamp . '_' . $random . '.' . $file_extension;
            
            // Alternative: Use only timestamp (simpler)
            // $unique_filename = $clean_name . '_' . time() . '_' . $random . '.' . $file_extension;
            
            $image_path_db = '/images/' . $category_folder . '/' . $unique_filename;
            $full_file_path = $upload_dir . $unique_filename;
            
            // Try to upload
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_file_path)) {
                $image_path = $image_path_db;
                $upload_success = true;
                $message = "✅ Image uploaded successfully! Filename: " . $unique_filename;
            } else {
                $message = "❌ Failed to upload image. Check folder permissions.";
                $messageType = "danger";
            }
        }
    } else {
        // Use provided image URL - FIXED: Ensure path starts with /
        $image_path = mysqli_real_escape_string($conn, $_POST['image_url']);
        if (!empty($image_path)) {
            // Ensure path starts with /
            if (!str_starts_with($image_path, '/')) {
                $image_path = '/' . $image_path;
            }
            $upload_success = true;
        } else {
            $message = "❌ Please provide an image (upload or URL).";
            $messageType = "danger";
        }
    }
    
    // Insert into desktop_wallpapers table
    if ($upload_success && !empty($image_path) && $messageType != 'danger') {
        $query = "INSERT INTO desktop_wallpapers 
                  (title, image_path, category_id, character_name, type, source_name, resolution, tags, views, downloads, orientation, created_at) 
                  VALUES 
                  ('$title', '$image_path', $category_id, '$character_name', '$type', '$source_name', '$resolution', '$tags', 0, 0, 'landscape', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $message .= " ✅ Desktop wallpaper added to database!";
            $messageType = "success";
        } else {
            $message .= " ❌ Database error: " . mysqli_error($conn);
            $messageType = "danger";
        }
    }
}

// ============================================================
// STICKY FORM VALUES
// Keep everything the user typed/selected so the next upload
// (usually same category/source/type/resolution/tags) doesn't
// need to be re-typed. Only the file input clears itself
// (browsers won't let us pre-fill it), and the image URL field
// clears after a successful DB insert.
// ============================================================
$sticky_title          = htmlspecialchars($_POST['title'] ?? '');
$sticky_character_name = htmlspecialchars($_POST['character_name'] ?? '');
$sticky_category_id    = $_POST['category_id'] ?? '';
$sticky_type           = $_POST['type'] ?? 'anime';
$sticky_source_name    = htmlspecialchars($_POST['source_name'] ?? 'One Piece');
$sticky_resolution     = $_POST['resolution'] ?? '4K';
$sticky_tags           = htmlspecialchars($_POST['tags'] ?? '');
$sticky_image_url      = ($messageType == 'success') ? '' : htmlspecialchars($_POST['image_url'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Desktop Wallpaper - Admin Panel</title>
    
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
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-card {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(225, 29, 29, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .form-card h2 {
            color: #fff;
            font-size: 2rem;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .form-card h2 i {
            color: #e11d1d;
            margin-right: 10px;
        }
        
        .admin-badge {
            background: #e11d1d;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .info-note {
            background: rgba(232, 185, 35, 0.1);
            border-left: 3px solid #e8b923;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #e8b923;
        }
        
        .form-label {
            color: #fff;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-label i {
            color: #e11d1d;
            margin-right: 8px;
        }
        
        .form-label .required {
            color: #e11d1d;
        }
        
        .form-control, .form-select {
            background: rgba(30, 30, 40, 0.8);
            border: 1px solid rgba(225, 29, 29, 0.3);
            color: #fff;
            border-radius: 10px;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(30, 30, 40, 0.9);
            border-color: #e11d1d;
            box-shadow: 0 0 10px rgba(225, 29, 29, 0.3);
            color: #fff;
            outline: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 50px;
            width: 100%;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(225, 29, 29, 0.3);
        }
        
        .image-preview {
            margin-top: 10px;
            text-align: center;
            display: none;
        }
        
        .image-preview img {
            max-width: 200px;
            border-radius: 10px;
            border: 2px solid #e11d1d;
        }
        
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
        
        .info-text {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }
        
        .character-hint {
            background: rgba(225, 29, 29, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 0.8rem;
        }
        
        .character-hint span {
            display: inline-block;
            background: #e11d1d;
            color: white;
            padding: 2px 8px;
            border-radius: 5px;
            margin: 2px;
            font-size: 0.7rem;
        }
        
        .folder-hint {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 0.8rem;
        }
        
        .filename-badge {
            background: #2d2d3d;
            padding: 8px 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.85rem;
            margin-top: 8px;
        }
        
        .filename-badge strong {
            color: #e11d1d;
        }
        
        hr {
            border-color: rgba(225, 29, 29, 0.3);
            margin: 20px 0;
        }
        
        .row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .col {
            flex: 1;
            min-width: 200px;
        }
        
        .unique-name-preview {
            background: rgba(0, 180, 216, 0.1);
            border-left: 3px solid #00b4d8;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            margin-top: 5px;
        }
        
        .unique-name-preview i {
            color: #00b4d8;
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .form-card {
                padding: 20px;
            }
            .form-card h2 {
                font-size: 1.5rem;
            }
            .row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="form-card">
            <div style="text-align: center;">
                <span class="admin-badge">
                    <i class="fas fa-crown"></i> Admin & Owner Access
                </span>
            </div>
            <h2>
                <i class="fas fa-desktop"></i> 
                Add Desktop Wallpaper
            </h2>
            <p style="text-align: center; color: #aaa; margin-bottom: 10px;">For computer screens (landscape orientation)</p>
            
            <div class="info-note">
                <i class="fas fa-info-circle"></i> 
                <strong>Desktop Wallpaper</strong> - Images will be stored in <code>/images/category/</code> 
                and will appear on category pages like <strong>animal.php, nature.php, etc.</strong>
            </div>
            
            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">

                <!-- Image Upload (now first) -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-image"></i> Upload Image <span class="required">*</span>
                    </label>
                    <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp,image/jpg,image/gif" id="imageInput">
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Unique filename generated automatically!</strong> Files will never overwrite each other.
                        <br>Format: <code>name_YYYYMMDD_HHMMSS_random.jpg</code> (e.g., <code>luffy_20240612_143025_48291.jpg</code>)
                        <br>Max file size: 50MB. Allowed: JPG, PNG, WEBP, GIF
                    </div>
                    <div class="unique-name-preview" id="uniqueNamePreview" style="display: none;">
                        <i class="fas fa-magic"></i> Will be saved as: <strong id="uniqueFileName"></strong>
                    </div>
                    <div class="filename-badge" id="filenamePreview" style="display: none;">
                        <i class="fas fa-save"></i> Original name: <strong id="originalFileName"></strong>
                    </div>
                    <div class="image-preview" id="imagePreviewDiv">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>

                <div class="text-center my-3">
                    <span class="text-muted">— OR —</span>
                </div>

                <!-- Image URL -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-link"></i> Or Image URL
                    </label>
                    <input type="text" class="form-control" name="image_url" 
                           placeholder="/images/chainsaw-man/denji.png"
                           value="<?php echo $sticky_image_url; ?>">
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i> 
                        For images already in folder: <strong>/images/chainsaw-man/denji.png</strong> (must start with /)
                    </div>
                </div>

                <hr>

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-heading"></i> Wallpaper Title <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" name="title" required 
                           placeholder="e.g., Luffy Gear 5 Epic Scene" 
                           value="<?php echo $sticky_title; ?>">
                </div>
                
                <!-- Character Name -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Character Name <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" name="character_name" required 
                           placeholder="e.g., Monkey D. Luffy, Roronoa Zoro, Shanks" 
                           value="<?php echo $sticky_character_name; ?>">
                    <div class="character-hint">
                        <i class="fas fa-info-circle"></i> For filtering, use names containing:
                        <span>Luffy</span> <span>Zoro</span> <span>Shanks</span>
                        <span>Denji</span> <span>Aki</span> <span>Reze</span>
                        <span>Makima</span> <span>Gon</span> <span>Killua</span>
                        <span>Loid</span> <span>Anya</span> <span>Yor</span>
                        <span>Takemichi</span> <span>Saitama</span> <span>Sung Jin-Woo</span>
                        <span>Okarun</span> <span>Isagi</span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">
                            <i class="fas fa-folder"></i> Category <span class="required">*</span>
                        </label>
                        <select class="form-select" name="category_id" required id="categorySelect">
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" data-slug="<?php echo $cat['slug']; ?>" <?php echo ((string)$sticky_category_id === (string)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="folder-hint" id="folderHint" style="display: none;">
                            <i class="fas fa-folder-open"></i> Images will be saved to: <strong id="folderName">images/</strong>
                        </div>
                    </div>
                    
                    <div class="col mb-3">
                        <label class="form-label">
                            <i class="fas fa-tag"></i> Type
                        </label>
                        <select class="form-select" name="type">
                            <option value="anime" <?php echo ($sticky_type === 'anime') ? 'selected' : ''; ?>>Anime</option>
                            <option value="manga" <?php echo ($sticky_type === 'manga') ? 'selected' : ''; ?>>Manga</option>
                            <option value="movie" <?php echo ($sticky_type === 'movie') ? 'selected' : ''; ?>>Movie</option>
                            <option value="series" <?php echo ($sticky_type === 'series') ? 'selected' : ''; ?>>Series</option>
                            <option value="general" <?php echo ($sticky_type === 'general') ? 'selected' : ''; ?>>General</option>
                            <option value="fanart" <?php echo ($sticky_type === 'fanart') ? 'selected' : ''; ?>>Fanart</option>
                            <option value="official" <?php echo ($sticky_type === 'official') ? 'selected' : ''; ?>>Official Art</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">
                            <i class="fas fa-tv"></i> Source/Anime
                        </label>
                        <input type="text" class="form-control" name="source_name" 
                               placeholder="e.g., One Piece" value="<?php echo $sticky_source_name; ?>">
                    </div>
                    
                    <div class="col mb-3">
                        <label class="form-label">
                            <i class="fas fa-desktop"></i> Resolution
                        </label>
                        <select class="form-select" name="resolution">
                            <option value="HD" <?php echo ($sticky_resolution === 'HD') ? 'selected' : ''; ?>>HD (1920x1080)</option>
                            <option value="2K" <?php echo ($sticky_resolution === '2K') ? 'selected' : ''; ?>>2K (2560x1440)</option>
                            <option value="4K" <?php echo ($sticky_resolution === '4K') ? 'selected' : ''; ?>>4K (3840x2160)</option>
                            <option value="5K" <?php echo ($sticky_resolution === '5K') ? 'selected' : ''; ?>>5K (5120x2880)</option>
                            <option value="8K" <?php echo ($sticky_resolution === '8K') ? 'selected' : ''; ?>>8K (7680x4320)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Tags -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-hashtag"></i> Tags
                    </label>
                    <input type="text" class="form-control" name="tags" 
                           placeholder="luffy, gear5, onepiece, anime, 4k"
                           value="<?php echo $sticky_tags; ?>">
                </div>
                
                <hr>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Add Desktop Wallpaper
                </button>
            </form>
        </div>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script>
        const imageInput = document.getElementById('imageInput');
        const imagePreviewDiv = document.getElementById('imagePreviewDiv');
        const previewImg = document.getElementById('previewImg');
        const filenamePreview = document.getElementById('filenamePreview');
        const originalFileNameSpan = document.getElementById('originalFileName');
        const uniqueNamePreview = document.getElementById('uniqueNamePreview');
        const uniqueFileNameSpan = document.getElementById('uniqueFileName');
        const categorySelect = document.getElementById('categorySelect');
        const folderHint = document.getElementById('folderHint');
        const folderName = document.getElementById('folderName');
        
        // Show folder hint on load if a category is already selected (sticky value),
        // and whenever the category changes
        function updateFolderHint() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categorySlug = selectedOption ? selectedOption.getAttribute('data-slug') : null;
            if (categorySlug) {
                const folder = '/images/' + categorySlug + '/';
                folderName.textContent = folder;
                folderHint.style.display = 'block';
            } else {
                folderHint.style.display = 'none';
            }
        }
        categorySelect.addEventListener('change', updateFolderHint);
        updateFolderHint();
        
        // Generate unique filename preview
        function generateUniqueFilename(originalName) {
            if (!originalName) return '';
            
            // Get file extension
            const lastDot = originalName.lastIndexOf('.');
            const extension = lastDot > 0 ? originalName.substring(lastDot + 1).toLowerCase() : '';
            let nameWithoutExt = lastDot > 0 ? originalName.substring(0, lastDot) : originalName;
            
            // Clean the name (remove special characters)
            nameWithoutExt = nameWithoutExt.replace(/[^a-zA-Z0-9_-]/g, '');
            if (nameWithoutExt === '') nameWithoutExt = 'wallpaper';
            
            // Generate timestamp
            const now = new Date();
            const timestamp = now.getFullYear() + 
                String(now.getMonth() + 1).padStart(2, '0') + 
                String(now.getDate()).padStart(2, '0') + '_' +
                String(now.getHours()).padStart(2, '0') + 
                String(now.getMinutes()).padStart(2, '0') + 
                String(now.getSeconds()).padStart(2, '0');
            
            // Generate random number
            const random = Math.floor(Math.random() * 90000) + 10000;
            
            // Create unique filename
            const uniqueFilename = nameWithoutExt + '_' + timestamp + '_' + random + '.' + extension;
            
            return uniqueFilename;
        }
        
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (50MB)
                if (file.size > 52428800) {
                    alert('File is too large! Maximum size is 50MB.');
                    this.value = '';
                    filenamePreview.style.display = 'none';
                    uniqueNamePreview.style.display = 'none';
                    imagePreviewDiv.style.display = 'none';
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type! Only JPG, PNG, WEBP, and GIF are allowed.');
                    this.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreviewDiv.style.display = 'block';
                }
                reader.readAsDataURL(file);
                
                // Show original filename
                originalFileNameSpan.textContent = file.name;
                filenamePreview.style.display = 'block';
                
                // Generate and show unique filename
                const uniqueName = generateUniqueFilename(file.name);
                uniqueFileNameSpan.textContent = uniqueName;
                uniqueNamePreview.style.display = 'block';
            } else {
                imagePreviewDiv.style.display = 'none';
                filenamePreview.style.display = 'none';
                uniqueNamePreview.style.display = 'none';
            }
        });
    </script>
</body>
</html>