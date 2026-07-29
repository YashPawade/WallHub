<?php
// support.php - WallHub Support & Contact Page
session_start();
include('includes/db.php');

$pageTitle = "Support & Contact - WallHub";

$success_message = '';
$error_message = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // Save to database
        $user_id = $_SESSION['user_id'] ?? null;
        $status = 'unread';
        $is_read_by_user = 0;
        
        $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (user_id, name, email, subject, message, status, is_read_by_user, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "isssssi", $user_id, $name, $email, $subject, $message, $status, $is_read_by_user);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "✅ Thank you for contacting us! We'll get back to you within 24 hours.";
        } else {
            $error_message = "❌ Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = implode('<br>', $errors);
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Cinzel:wght@400;600;700&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --support-red: #FF003F;
            --support-dark: #0a0a0a;
            --support-darker: #05050d;
            --support-gray: #1a1a1a;
            --support-light: #f5f5f5;
            --support-muted: #9ca3af;
            --support-border: rgba(255,255,255,0.08);
            --support-card: rgba(255,255,255,0.03);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: linear-gradient(135deg, var(--support-darker) 0%, var(--support-dark) 100%);
            color: var(--support-light);
            font-family: 'Raleway', sans-serif;
            overflow-x: hidden;
            padding-top: 80px;
            min-height: 100vh;
        }

        /* Hero Section */
        .support-hero {
            position: relative;
            text-align: center;
            padding: 60px 20px 50px;
            overflow: hidden;
        }
        .support-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 60% at 50% 0%, rgba(255,0,63,0.12) 0%, transparent 65%);
            pointer-events: none;
        }
        .hero-eyecatcher {
            font-family: 'Cinzel', serif;
            font-size: 0.75rem;
            letter-spacing: 0.5em;
            color: var(--support-red);
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .hero-title {
            font-family: 'Cinzel Decorative', serif;
            font-size: clamp(2.5rem, 7vw, 4.5rem);
            font-weight: 900;
            color: var(--support-light);
            line-height: 1;
        }
        .hero-title .accent { color: var(--support-red); }
        .hero-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 30px auto;
            max-width: 600px;
        }
        .hero-divider::before,
        .hero-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--support-red));
        }
        .hero-divider::after {
            background: linear-gradient(90deg, var(--support-red), transparent);
        }
        .hero-tagline {
            font-size: 1rem;
            font-weight: 300;
            letter-spacing: 0.06em;
            color: var(--support-muted);
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Support Cards Grid */
        .support-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .support-card {
            background: var(--support-card);
            border: 1px solid var(--support-border);
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .support-card:hover {
            transform: translateY(-8px);
            border-color: var(--support-red);
            box-shadow: 0 20px 40px rgba(255,0,63,0.1);
        }
        .support-card-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,0,63,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .support-card-icon i {
            font-size: 2rem;
            color: var(--support-red);
        }
        .support-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 1.3rem;
            margin-bottom: 12px;
        }
        .support-card p {
            color: var(--support-muted);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .support-card .btn-small {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--support-red);
            text-decoration: none;
            font-weight: 600;
            transition: gap 0.3s;
        }
        .support-card .btn-small:hover { gap: 12px; }

        /* FAQ Section */
        .faq-section {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .section-title {
            text-align: center;
            font-family: 'Cinzel Decorative', serif;
            font-size: 2rem;
            margin-bottom: 40px;
        }
        .section-title span { color: var(--support-red); }
        .faq-item {
            background: var(--support-card);
            border: 1px solid var(--support-border);
            border-radius: 16px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .faq-question {
            width: 100%;
            padding: 20px 24px;
            background: transparent;
            border: none;
            text-align: left;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--support-light);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        .faq-question:hover { background: rgba(255,255,255,0.03); }
        .faq-question i { color: var(--support-red); transition: transform 0.3s; }
        .faq-question.active i { transform: rotate(180deg); }
        .faq-answer {
            max-height: 0;
            padding: 0 24px;
            color: var(--support-muted);
            line-height: 1.6;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .faq-answer.show {
            max-height: 300px;
            padding: 0 24px 20px;
        }

        /* Contact Form */
        .contact-section {
            max-width: 1000px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            background: var(--support-card);
            border: 1px solid var(--support-border);
            border-radius: 24px;
            overflow: hidden;
        }
        .contact-info {
            background: rgba(255,0,63,0.05);
            padding: 32px;
        }
        .contact-info h3 {
            font-family: 'Cinzel', serif;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }
        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }
        .contact-info-item i {
            width: 40px;
            height: 40px;
            background: rgba(255,0,63,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--support-red);
        }
        .contact-info-item div strong { display: block; margin-bottom: 4px; }
        .contact-info-item div span { color: var(--support-muted); font-size: 0.85rem; }
        .contact-form { padding: 32px; }
        .form-group { margin-bottom: 20px; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--support-border);
            border-radius: 10px;
            color: var(--support-light);
            font-family: 'Raleway', sans-serif;
            transition: all 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--support-red);
            background: rgba(255,0,63,0.05);
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder { color: var(--support-muted); }
        .btn-submit {
            background: linear-gradient(135deg, var(--support-red), #cc0033);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255,0,63,0.3);
        }
        .subject-select,
.subject-select option {
    color: black !important;
    background: white !important;
}
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #10b981; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid #ef4444; color: #ef4444; }

        @media (max-width: 900px) {
            .support-cards { grid-template-columns: 1fr; max-width: 500px; }
            .contact-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

<?php include('header.php'); ?>

<!-- Hero Section -->
<section class="support-hero">
    <p class="hero-eyecatcher">WallHub Support</p>
    <h1 class="hero-title">
        <span class="accent">✦</span> How Can We <span class="accent">Help?</span>
    </h1>
    <div class="hero-divider"><span>⚡</span></div>
    <p class="hero-tagline">Fast, friendly support for all WallHub users. Get answers to your questions, report issues, or just say hello.</p>
</section>

<!-- Support Options Cards -->
<div class="support-cards">
    <div class="support-card">
        <div class="support-card-icon"><i class="fas fa-question-circle"></i></div>
        <h3>FAQs</h3>
        <p>Quick answers to the most common questions about downloads, premium membership, and more.</p>
        <a href="#faq" class="btn-small">Browse FAQs <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="support-card">
        <div class="support-card-icon"><i class="fas fa-envelope"></i></div>
        <h3>Email Support</h3>
        <p>Send us a message and our team will get back to you within 24 hours.</p>
        <a href="#contact" class="btn-small">Contact Us <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="support-card">
        <div class="support-card-icon"><i class="fab fa-telegram"></i></div>
        <h3>Telegram Community</h3>
        <p>Join our Telegram channels for updates, announcements, and community discussions.</p>
        <a href="telegram.php" class="btn-small">Join Now <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

<!-- FAQ Section -->
<div class="faq-section" id="faq">
    <h2 class="section-title">Frequently Asked <span>Questions</span></h2>
    
    <div class="faq-item">
        <button class="faq-question">
            How do I download wallpapers?
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="faq-answer">
            Simply browse to any wallpaper, click on it, and press the "Download" button. Your wallpaper will be saved to your device in original 4K quality.
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">
            What's the difference between free and premium?
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="faq-answer">
            Free users get 10 downloads per day. Premium members get unlimited downloads, early access to new wallpapers, priority support, and the ability to request custom wallpapers of your favorite characters.
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">
            How do I request a wallpaper?
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="faq-answer">
            Premium members can request wallpapers through the premium request form. Tell us your favorite character, anime, or theme, and our team will create it within 7-14 days.
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">
            Are the wallpapers really 4K?
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="faq-answer">
            Yes! All desktop wallpapers are available in true 4K resolution (3840x2160) or higher. Mobile wallpapers are optimized for phone screens.
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">
            Can I cancel my premium subscription?
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="faq-answer">
            Absolutely. You can cancel anytime from your profile settings. No questions asked. You'll retain premium access until the end of your billing period.
        </div>
    </div>
    
    <div class="faq-item">
        <button class="faq-question">
            How do I report a broken or inappropriate wallpaper?
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="faq-answer">
            Use the contact form below or email us directly at support@wallhub.online. Please include the wallpaper title or URL for quick resolution.
        </div>
    </div>
</div>

<!-- Contact Form Section -->
<div class="contact-section" id="contact">
    <div class="contact-grid">
        <div class="contact-info">
            <h3>Get in Touch</h3>
            <div class="contact-info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong>Email Us</strong>
                    <span>support@wallhub.online</span>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="fab fa-telegram"></i>
                <div>
                    <strong>Telegram</strong>
                    <span>@ElementVoxNews</span>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Response Time</strong>
                    <span>Within 24 hours</span>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>DMCA & Legal</strong>
                    <span>dmca@wallhub.online</span>
                </div>
            </div>
        </div>
        
        <div class="contact-form">
            <h3>Send us a Message</h3>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <select name="subject" class="subject-select" required>
                        <option value="">Select Subject</option>
                        <option value="General Inquiry">General Inquiry</option>
                        <option value="Technical Issue">Technical Issue</option>
                        <option value="Premium Membership">Premium Membership</option>
                        <option value="DMCA / Copyright">DMCA / Copyright</option>
                        <option value="Partnership">Partnership / Business</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <textarea name="message" rows="5" placeholder="Your Message..." required></textarea>
                </div>
                <button type="submit" name="submit_contact" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</div>

<div style="height: 60px;"></div>

<?php include('footer.php'); ?>

<script>
    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const answer = button.nextElementSibling;
            const isActive = button.classList.contains('active');
            
            // Close all
            document.querySelectorAll('.faq-question').forEach(btn => {
                btn.classList.remove('active');
                btn.nextElementSibling.classList.remove('show');
            });
            
            // Open clicked if wasn't active
            if (!isActive) {
                button.classList.add('active');
                answer.classList.add('show');
            }
        });
    });
</script>

</body>
</html>