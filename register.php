<?php
// register.php - Beautiful Design with Secure Password Hashing
session_start();
include('includes/db.php');

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index');
    exit();
}

// Initialize variables
$errors = [];
$form_data = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'username' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name'] ?? ''));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $username = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $agree_terms = isset($_POST['agree_terms']);
    
    // Store form data for re-display
    $form_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'username' => $username
    ];
    
    // Validation
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    } elseif (strlen($first_name) < 2) {
        $errors[] = 'First name must be at least 2 characters.';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    } elseif (strlen($last_name) < 2) {
        $errors[] = 'Last name must be at least 2 characters.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!$agree_terms) {
        $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Check if email already exists
        $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors[] = 'Email address is already registered.';
        } else {
            // Check if username already exists
            $checkUsername = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
            if (mysqli_num_rows($checkUsername) > 0) {
                $errors[] = 'Username is already taken.';
            } else {
                // FIXED: Store ONLY hashed password (no plain text)
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // FIXED: Removed plain_password column from INSERT
                $query = "INSERT INTO users (first_name, last_name, email, username, password_hash, role, created_at) 
                          VALUES ('$first_name', '$last_name', '$email', '$username', '$password_hash', 'member', NOW())";
                
                if (mysqli_query($conn, $query)) {
                    // Registration successful
                    $user_id = mysqli_insert_id($conn);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['role'] = 'member';
                    $_SESSION['logged_in'] = true;
                    
                    // Redirect to homepage
                    header('Location: index?registered=1');
                    exit();
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$pageTitle = "Register | WallHub";
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
    
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --dark-bg: #121212;
            --card-bg: rgba(30, 30, 30, 0.9);
            --text-color: #f5f6fa;
            --accent-color: #fd79a8;
            --success-color: #00b894;
            --error-color: #d63031;
            --warning-color: #fdcb6e;
            --placeholder-color: #b2bec3;
            --glow-color: rgba(108, 92, 231, 0.6);
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('https://source.unsplash.com/random/1920x1080/?anime,dark,abstract');
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
            background: linear-gradient(135deg, rgba(18, 18, 18, 0.9) 0%, rgba(33, 33, 33, 0.8) 100%);
            z-index: -1;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            padding: 40px;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            transform-origin: center;
            transition: transform 0.3s ease;
        }
        
        .register-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
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
            margin-bottom: 25px;
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
            font-size: 0.55rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.35);
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
        
        .form-control {
            background-color: rgba(45, 45, 45, 0.7);
            color: var(--text-color);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .form-control::placeholder {
            color: var(--placeholder-color);
            opacity: 1;
        }
        
        .form-control:focus {
            background-color: rgba(51, 51, 51, 0.8);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background-color: rgba(51, 51, 51, 0.8);
            color: var(--secondary-color);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-right: none;
            border-radius: 10px 0 0 10px !important;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0 !important;
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.4s ease;
            margin-top: 10px;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-register:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #777;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .social-register {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }
        
        .social-btn:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .facebook {
            background: linear-gradient(135deg, #3b5998, #4c70ba);
        }
        
        .google {
            background: linear-gradient(135deg, #db4437, #e57368);
        }
        
        .twitter {
            background: linear-gradient(135deg, #1da1f2, #4ab8f8);
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
        }
        
        .footer-links a:hover {
            color: white;
            text-decoration: underline;
        }
        
        .password-strength {
            margin-bottom: 15px;
        }
        
        .strength-meter {
            height: 5px;
            background-color: rgba(45, 45, 45, 0.7);
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
            position: relative;
        }
        
        .strength-meter-fill {
            height: 100%;
            width: 0;
            border-radius: 5px;
            transition: width 0.5s ease, background-color 0.5s ease;
            position: relative;
            z-index: 1;
        }
        
        .terms-agreement {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .terms-agreement input {
            margin-top: 2px;
        }
        
        .terms-agreement label {
            font-size: 13px;
            cursor: pointer;
            margin-left: 8px;
        }
        
        .terms-agreement a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .terms-agreement a:hover {
            text-decoration: underline;
        }
        
        h2 {
            color: var(--text-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
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
        
        .name-fields {
            display: flex;
            gap: 15px;
        }
        
        .name-fields .mb-3 {
            flex: 1;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .text-muted {
            color: #888 !important;
            font-size: 0.75rem;
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 30px;
                max-width: 95%;
            }
            
            .logo-img {
                height: 45px !important;
            }
            
            .logo-tagline {
                font-size: 0.45rem !important;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .name-fields {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="register-container floating">
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
            
            <h2>Join Our Creative Community</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0" style="padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" novalidate>
                <div class="name-fields">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="first_name" 
                                   placeholder="First Name" required
                                   value="<?php echo htmlspecialchars($form_data['first_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="last_name" 
                                   placeholder="Last Name" required
                                   value="<?php echo htmlspecialchars($form_data['last_name']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                        <input type="text" class="form-control" name="username" 
                               placeholder="Username" required
                               value="<?php echo htmlspecialchars($form_data['username']); ?>">
                    </div>
                    <small class="text-muted">3-20 characters, letters, numbers, and underscores only</small>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email" 
                               placeholder="Email Address" required
                               value="<?php echo htmlspecialchars($form_data['email']); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required>
                    </div>
                    <div class="password-strength">
                        <small>Password Strength</small>
                        <div class="strength-meter">
                            <div class="strength-meter-fill"></div>
                        </div>
                    </div>
                    <small class="text-muted">Minimum 8 characters with uppercase, lowercase, and number</small>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="confirm_password" 
                               placeholder="Confirm Password" required>
                    </div>
                </div>
                
                <div class="terms-agreement">
                    <input type="checkbox" id="agree_terms" name="agree_terms" class="form-check-input" required
                           <?php echo (isset($_POST['agree_terms']) || empty($_POST)) ? 'checked' : ''; ?>>
                    <label for="agree_terms" class="form-check-label">
                        I agree to the <a href="terms">Terms of Service</a> and <a href="privacy">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <span class="register-text">Create Account</span>
                    <i class="fas fa-user-plus ms-2"></i>
                </button>
                
                <div class="divider">or sign up with</div>
                
                <div class="social-register">
                    <button type="button" class="social-btn facebook" onclick="socialLogin('facebook')">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button type="button" class="social-btn google" onclick="socialLogin('google')">
                        <i class="fab fa-google"></i>
                    </button>
                    <button type="button" class="social-btn twitter" onclick="socialLogin('twitter')">
                        <i class="fab fa-twitter"></i>
                    </button>
                </div>
                
                <div class="footer-links">
                    Already have an account? <a href="login">Sign in</a>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.querySelector('.strength-meter-fill');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                
                const width = Math.min(strength * 20, 100);
                let color;
                
                if (strength <= 2) {
                    color = '#ff4757';
                } else if (strength <= 4) {
                    color = '#ffa502';
                } else {
                    color = '#2ed573';
                }
                
                strengthMeter.style.width = `${width}%`;
                strengthMeter.style.backgroundColor = color;
            });
        }

        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const password = document.querySelector('input[name="password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                const terms = document.querySelector('input[name="agree_terms"]');
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match. Please check and try again.');
                    return false;
                }
                
                if (!terms.checked) {
                    e.preventDefault();
                    alert('You must agree to the Terms of Service and Privacy Policy.');
                    return false;
                }
                
                const submitBtn = document.querySelector('.btn-register');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating Account...';
                submitBtn.disabled = true;
                
                return true;
            });
        }

        function socialLogin(provider) {
            alert(`${provider.charAt(0).toUpperCase() + provider.slice(1)} login will be available soon!`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const firstField = document.querySelector('input[name="first_name"]');
            if (firstField) firstField.focus();
        });
    </script>
</body>
</html>