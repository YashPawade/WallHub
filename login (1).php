<?php
// login.php - Clean Version (Works with password_hash only)
session_start();
include('includes/db.php');

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $query = "SELECT * FROM users WHERE email = '$email' OR username = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Check password using password_hash only
        $valid = false;
        
        if (isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            $valid = true;
        }
        
        if ($valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            
            // Set remember me cookie (30 days)
            if ($remember) {
                $cookie_value = base64_encode($user['id'] . ':' . $user['password_hash']);
                setcookie('wallhub_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/');
            }
            
            // Reset daily downloads if new day
            $today = date('Y-m-d');
            if (isset($user['last_download_date']) && $user['last_download_date'] != $today && $user['role'] == 'member') {
                mysqli_query($conn, "UPDATE users SET daily_downloads = 0, last_download_date = '$today' WHERE id = " . $user['id']);
            }
            
            header('Location: index');
            exit();
        } else {
            $error = "Invalid email/username or password.";
        }
    } else {
        $error = "Invalid email/username or password.";
    }
}

$pageTitle = "Login | WallHub";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Shared CSS -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --dark-bg: #121212;
            --card-bg: rgba(30, 30, 30, 0.9);
            --text-color: #f5f6fa;
            --placeholder-color: #b2bec3;
            --success-color: #00b894;
            --warning-color: #fdcb6e;
            --error-color: #d63031;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(135deg, #2d3436 0%, #000000 100%);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            padding: 20px;
            margin: 0;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg, 
                rgba(109, 92, 231, 0.15) 0%, 
                rgba(253, 121, 168, 0.1) 100%
            );
            z-index: -1;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 40px;
            background-color: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-origin: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                transparent 0%,
                transparent 50%,
                rgba(109, 92, 231, 0.1) 50%,
                rgba(109, 92, 231, 0.1) 100%
            );
            transform: rotate(30deg);
            z-index: -1;
            animation: shine 8s infinite linear;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
            animation: fadeInUp 0.5s ease;
        }
        
        .alert-danger {
            background-color: rgba(214, 48, 49, 0.2);
            color: #ff6b6b;
            border-left: 4px solid var(--error-color);
        }
        
        .alert-success {
            background-color: rgba(0, 184, 148, 0.2);
            color: #55efc4;
            border-left: 4px solid var(--success-color);
        }
        
        @keyframes shine {
            0% { transform: rotate(30deg) translate(-10%, -10%); }
            100% { transform: rotate(30deg) translate(10%, 10%); }
        }
        
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        /* ========================================== */
        /* LOGO STYLES - MATCHING HEADER              */
        /* ========================================== */
        .logo {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .logo-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            gap: 12px;
            transition: all 0.3s ease;
        }
        
        .logo-link:hover {
            transform: scale(1.02);
        }
        
        .logo-img {
            height: 60px;
            width: auto;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .logo-link:hover .logo-img {
            transform: scale(1.05);
        }
        
        .logo-tagline {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.6rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.18em;
            text-transform: uppercase;
            display: block;
            margin-top: 2px;
            text-align: left;
        }
        
        .logo-text-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        /* Hide old logo elements */
        .logo-icon {
            display: none !important;
        }
        .logo-text {
            display: none !important;
        }
        .logo-dot {
            display: none !important;
        }
        
        .input-group-text {
            background-color: rgba(45, 45, 45, 0.8);
            border: 1px solid rgba(68, 68, 68, 0.5);
            border-right: none;
            color: var(--primary-color);
        }
        
        .form-control {
            background-color: rgba(45, 45, 45, 0.8);
            color: var(--text-color);
            border: 1px solid rgba(68, 68, 68, 0.5);
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 15px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .form-control::placeholder {
            color: var(--placeholder-color);
            opacity: 1;
        }
        
        .form-control:focus {
            background-color: rgba(51, 51, 51, 0.9);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(108, 92, 231, 0.25);
            transform: translateY(-2px);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.4s ease;
            margin-top: 10px;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #5a4bd1 0%, #8c7af9 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.6);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #777;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }
        
        .social-btn:hover {
            transform: translateY(-5px) scale(1.1);
            color: white;
        }
        
        .facebook {
            background: linear-gradient(135deg, #3b5998 0%, #4c70ba 100%);
        }
        
        .google {
            background: linear-gradient(135deg, #db4437 0%, #f4b400 100%);
        }
        
        .twitter {
            background: linear-gradient(135deg, #1da1f2 0%, #0d8ecf 100%);
        }
        
        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .footer-links a {
            color: var(--secondary-color);
            text-decoration: none;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .footer-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-links a:hover::after {
            width: 100%;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me label {
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 8px;
        }
        
        .forgot-password a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .forgot-password a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .forgot-password a:hover::after {
            width: 100%;
        }
        
        h2 {
            color: var(--text-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }
        
        .floating {
            animation: floating 6s infinite ease-in-out;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .forgot-password-btn {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .forgot-password-btn a {
            display: inline-block;
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(108, 92, 231, 0.1);
        }
        
        .forgot-password-btn a:hover {
            background: rgba(108, 92, 231, 0.2);
            color: white;
            transform: translateY(-2px);
        }
        
        .forgot-password-btn i {
            margin-right: 8px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px;
                max-width: 90%;
            }
            
            .logo-img {
                height: 45px !important;
            }
            
            .logo-tagline {
                font-size: 0.5rem !important;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .social-login {
                gap: 15px;
            }
            
            .social-btn {
                width: 45px;
                height: 45px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="particles" id="particles"></div>
        
        <div class="login-container">
            <!-- ========================================== -->
            <!-- LOGO - IMAGE VERSION (MATCHING HEADER)     -->
            <!-- ========================================== -->
            <div class="logo">
                <a href="index" class="logo-link">
                    <img src="/images/aaaa.png" 
                         alt="WallHub - Premium Wallpapers" 
                         class="logo-img"
                         style="height: 120px; width: auto; display: block;">
                   
                </a>
            </div>
            
            <h2>Welcome Back!</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="text" class="form-control" name="email" placeholder="Email or Username" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input"
                               <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot_password">Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <span class="login-text">Login</span>
                    <i class="fas fa-arrow-right login-icon" style="margin-left: 8px;"></i>
                </button>
                
                <div class="alert alert-info mt-3" style="background-color: rgba(45, 52, 54, 0.5); color: #b2bec3;">
                    <small><i class="fas fa-info-circle me-2"></i>
                    Demo: admin@wallhub.com / Admin@123 | member@wallhub.com / Member@123</small>
                </div>
                
                <div class="divider">or continue with</div>
                
                <div class="social-login">
                    <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn google"><i class="fab fa-google"></i></a>
                    <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                </div>
                
                <div class="footer-links">
                    Don't have an account? <a href="register">Sign up</a>
                </div>
                
                <div class="forgot-password-btn">
                    <a href="forgot_password">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Create animated particles
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 5 + 2;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 10 + 10;
                const opacity = Math.random() * 0.5 + 0.1;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                particle.style.opacity = opacity;
                particle.style.animation = `floating ${duration}s infinite ${delay}s ease-in-out`;
                
                particlesContainer.appendChild(particle);
            }
            
            const loginBtn = document.querySelector('.btn-login');
            if (loginBtn) {
                loginBtn.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('.login-icon');
                    if (icon) icon.style.transform = 'translateX(5px)';
                });
                
                loginBtn.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('.login-icon');
                    if (icon) icon.style.transform = 'translateX(0)';
                });
            }
            
            const emailField = document.querySelector('input[name="email"]');
            if (emailField) emailField.focus();
        });
        
        // Social login placeholder
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Social login coming soon!');
            });
        });
    </script>
</body>
</html>