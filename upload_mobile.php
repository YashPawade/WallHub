<?php
session_start();
include('includes/db.php');

// Check if user is admin OR owner (FIXED)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Create mobile_wallpapers table if it doesn't exist (for first-time setup)
$create_table = "CREATE TABLE IF NOT EXISTS mobile_wallpapers LIKE wallpapers";
mysqli_query($conn, $create_table);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['wallpaper'])) {
    $category_slug = mysqli_real_escape_string($conn, $_POST['category']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $resolution = mysqli_real_escape_string($conn, $_POST['resolution']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    
    // Get category ID from database
    $cat_query = "SELECT id FROM categories WHERE slug = '$category_slug'";
    $cat_result = mysqli_query($conn, $cat_query);
    
    if (mysqli_num_rows($cat_result) > 0) {
        $cat_row = mysqli_fetch_assoc($cat_result);
        $category_id = $cat_row['id'];
    } else {
        // Create category if it doesn't exist
        $category_name = ucfirst($category_slug);
        $insert_cat = "INSERT INTO categories (name, slug) VALUES ('$category_name', '$category_slug')";
        mysqli_query($conn, $insert_cat);
        $category_id = mysqli_insert_id($conn);
    }
    
    // Create category folder in the correct location (no /wallhub/ prefix)
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/mobile/' . $category_slug . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['wallpaper'];
    
    // ============================================================
    // FIXED: Generate UNIQUE filename using timestamp + random number
    // This prevents files from overwriting each other!
    // ============================================================
    $original_name = $file['name'];
    $file_name_without_ext = pathinfo($original_name, PATHINFO_FILENAME);
    $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $file_name_without_ext);
    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    
    // If clean name is empty, use a default
    if (empty($clean_name)) {
        $clean_name = 'wallpaper';
    }
    
    // Generate unique identifier: timestamp + random number
    $timestamp = time();
    $random = mt_rand(10000, 99999);
    $file_name = $clean_name . '_' . $timestamp . '_' . $random . '.' . $file_extension;
    
    $file_path = $upload_dir . $file_name;
    // Database path - starts with /images/mobile/
    $db_path = '/images/mobile/' . $category_slug . '/' . $file_name;
    
    // Check file type
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $error = 'Invalid file type. Allowed: JPG, PNG, WEBP, GIF';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error: ' . $file['error'];
    } elseif ($file['size'] > 10485760) { // 10MB limit
        $error = 'File too large. Maximum size is 10MB.';
    } else {
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // UPDATED: Insert into mobile_wallpapers table instead of wallpapers
            $insert_query = "INSERT INTO mobile_wallpapers (title, image_path, category_id, resolution, tags, downloads, views, orientation, created_at, character_name, type, source_name, is_premium, likes) 
                            VALUES ('$title', '$db_path', $category_id, '$resolution', '$tags', 0, 0, 'portrait', NOW(), '', 'mobile', '', 0, 0)";
            
            if (mysqli_query($conn, $insert_query)) {
                $message = 'Mobile wallpaper uploaded successfully! <a href="mobile.php" style="color: #e8b923;">View Mobile Wallpapers</a>';
            } else {
                $error = 'Database error: ' . mysqli_error($conn);
            }
        } else {
            $error = 'Failed to move uploaded file. Check folder permissions.';
        }
    }
}

// Get categories for dropdown
$categories = ['animal', 'anime', 'cartoon', 'dark', 'movie', 'nature', 'show', 'abstract', 'fantasy', 'gaming', 'space', 'vehicle'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Mobile Wallpaper - WallHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@400;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #080808 0%, #1a1a1a 100%);
            font-family: 'Raleway', sans-serif;
            color: #f5f0e8;
            padding-top: 80px;
        }
        .upload-container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(28, 28, 28, 0.95);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(232, 185, 35, 0.3);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        .upload-title {
            font-family: 'Cinzel Decorative', serif;
            color: #e8b923;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #e8b923;
        }
        .form-control, .form-select {
            background: #2c2c2e;
            border: 1px solid rgba(232, 185, 35, 0.3);
            color: #f5f0e8;
        }
        .form-control:focus, .form-select:focus {
            background: #3c3c3e;
            border-color: #e8b923;
            box-shadow: 0 0 10px rgba(232, 185, 35, 0.3);
            color: #f5f0e8;
        }
        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }
        .btn-upload {
            background: linear-gradient(135deg, #e8b923, #f5d060);
            color: #000;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 40px;
            font-family: 'Cinzel', serif;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(232, 185, 35, 0.3);
        }
        .alert-success {
            background: rgba(46, 168, 64, 0.2);
            border-color: #2ea840;
            color: #2ea840;
        }
        .alert-danger {
            background: rgba(232, 0, 13, 0.2);
            border-color: #e8000d;
            color: #e8000d;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #e8b923;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .tag-examples {
            font-size: 0.75rem;
            color: #888070;
            margin-top: 5px;
        }
        .tag-examples i {
            margin-right: 5px;
        }
        .file-info {
            font-size: 0.7rem;
            color: #888070;
            margin-top: 5px;
        }
        .preview-area {
            margin-top: 15px;
            text-align: center;
            display: none;
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            border: 1px solid rgba(232, 185, 35, 0.3);
        }
        .info-note {
            background: rgba(232, 185, 35, 0.1);
            border-left: 3px solid #e8b923;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="upload-container">
        <h2 class="upload-title">
            <i class="fas fa-cloud-upload-alt"></i> Upload Mobile Wallpaper
        </h2>
        
        <div class="info-note">
            <i class="fas fa-info-circle"></i> 
            <strong>Mobile Wallpaper Upload</strong> - Images will be stored in <code>/images/mobile/category/</code> 
            and will appear on the <a href="mobile.php" style="color: #e8b923;">Mobile Wallpapers page</a>.
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-folder"></i> Category
                </label>
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-heading"></i> Title
                </label>
                <input type="text" name="title" class="form-control" required placeholder="Enter wallpaper title (e.g., Beautiful Mountain Sunset)">
            </div>
            
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-tags"></i> Tags (comma separated)
                </label>
                <textarea name="tags" class="form-control" placeholder="e.g., nature, sunset, mountains, 4k, landscape, forest, trees"></textarea>
                <div class="tag-examples">
                    <i class="fas fa-lightbulb"></i> Examples: 
                    "anime, dragon ball, goku, 4k" | 
                    "nature, forest, waterfall, hd" | 
                    "dark, cyberpunk, neon, gothic"
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-expand"></i> Resolution
                </label>
                <select name="resolution" class="form-select" required>
                    <option value="1080x1920">1080x1920 (Full HD - Recommended)</option>
                    <option value="1440x2560">1440x2560 (2K)</option>
                    <option value="2160x3840">2160x3840 (4K)</option>
                    <option value="720x1280">720x1280 (HD)</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-image"></i> Select Image
                </label>
                <input type="file" name="wallpaper" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" required id="imageInput">
                <div class="file-info">
                    <i class="fas fa-info-circle"></i> Allowed formats: JPG, PNG, WEBP, GIF. Max size: 10MB
                </div>
            </div>
            
            <!-- Image Preview -->
            <div class="preview-area" id="previewArea">
                <img id="previewImage" class="preview-image" alt="Preview">
                <p class="file-info mt-2"><i class="fas fa-check-circle"></i> Image ready for upload</p>
            </div>
            
            <button type="submit" class="btn btn-upload">
                <i class="fas fa-upload"></i> Upload Mobile Wallpaper
            </button>
        </form>
        
        <div class="back-link">
            <a href="mobile.php">
                <i class="fas fa-arrow-left"></i> Back to Mobile Wallpapers
            </a>
        </div>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script>
        // Image preview functionality
        const imageInput = document.getElementById('imageInput');
        const previewArea = document.getElementById('previewArea');
        const previewImage = document.getElementById('previewImage');
        
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewArea.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewArea.style.display = 'none';
            }
        });
        
        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('imageInput');
            const file = fileInput.files[0];
            
            if (file) {
                // Check file size (10MB = 10485760 bytes)
                if (file.size > 10485760) {
                    e.preventDefault();
                    alert('File is too large! Maximum size is 10MB.');
                    return false;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Invalid file type! Only JPG, PNG, WEBP, and GIF are allowed.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>

