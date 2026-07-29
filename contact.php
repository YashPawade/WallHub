<?php
// contact.php - User Contact Page
session_start();
include('includes/db.php');

$pageTitle = "Contact Us - WallHub";

// Make sure the attachment column exists (safe to run every load)
@mysqli_query($conn, "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS attachment_path VARCHAR(255) DEFAULT NULL");

$success_msg = '';
$error_msg = '';

// Sticky values so a failed submission doesn't wipe what the user typed
$sticky_subject = '';
$sticky_message = '';

// ============================================================
// Show a success banner after redirect (Post/Redirect/Get).
// This means refreshing the page after sending never re-submits
// the form or shows a "confirm resubmission" browser prompt.
// ============================================================
if (isset($_GET['sent']) && $_GET['sent'] == '1') {
    $success_msg = "✅ Your message has been sent to the admin. You will receive a reply soon!";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $subject = mysqli_real_escape_string($conn, $_POST['subject'] ?? '');
    $message = mysqli_real_escape_string($conn, trim($_POST['message'] ?? ''));
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    // Sticky values to re-populate the form if something goes wrong
    $sticky_subject = $_POST['subject'] ?? '';
    $sticky_message = $_POST['message'] ?? '';

    // Honeypot: real visitors never fill this hidden field, bots often do
    $honeypot = trim($_POST['website'] ?? '');

    if ($honeypot !== '') {
        // Silently pretend success to not tip off the bot
        header('Location: contact.php?sent=1');
        exit();
    }

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } elseif (mb_strlen($message) > 1000) {
        $error_msg = "Your message is too long. Please keep it under 1000 characters.";
    } else {
        // Optional screenshot attachment (helps a lot for bug reports)
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error_msg = "❌ Attachment must be an image (JPG, PNG, WEBP, or GIF).";
            } elseif ($_FILES['attachment']['size'] > 5242880) { // 5MB
                $error_msg = "❌ Attachment is too large. Maximum size is 5MB.";
            } else {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/contact_attachments/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $unique_name = 'msg_' . date('Ymd_His') . '_' . mt_rand(10000, 99999) . '.' . $ext;
                $full_path = $upload_dir . $unique_name;

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $full_path)) {
                    $attachment_path = '/uploads/contact_attachments/' . $unique_name;
                } else {
                    $error_msg = "❌ Could not upload the attachment. Your message was not sent — please try again.";
                }
            }
        }

        // Only insert if there wasn't an attachment-related error above
        if (empty($error_msg)) {
            $user_id_value = $user_id !== null ? $user_id : 'NULL';
            $attachment_value = $attachment_path !== null
                ? "'" . mysqli_real_escape_string($conn, $attachment_path) . "'"
                : 'NULL';

            $query = "INSERT INTO contact_messages
                      (user_id, name, email, subject, message, status, created_at, is_read_by_user, attachment_path)
                      VALUES
                      ($user_id_value, '$name', '$email', '$subject', '$message', 'unread', NOW(), 1, $attachment_value)";

            if (mysqli_query($conn, $query)) {
                // Post/Redirect/Get - avoids duplicate sends on refresh
                header('Location: contact.php?sent=1');
                exit();
            } else {
                $error_msg = "❌ Failed to send message. Please try again.";
            }
        }
    }
}

// Get user info for auto-fill if logged in
$user_name = '';
$user_email = '';
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT username, first_name, last_name, email FROM users WHERE id = $user_id");
    if ($user_data = mysqli_fetch_assoc($user_query)) {
        if (!empty($user_data['username'])) {
            $user_name = $user_data['username'];
        } else {
            $user_name = trim($user_data['first_name'] . ' ' . ($user_data['last_name'] ?? ''));
        }
        $user_email = $user_data['email'];
    }
}

// Get user's unread reply count for header badge (for logged in users)
$user_unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $unread_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE user_id = $user_id AND status = 'replied' AND admin_reply IS NOT NULL AND is_read_by_user = 0");
    $user_unread_count = mysqli_fetch_assoc($unread_result)['count'];
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: radial-gradient(ellipse at top, #14142b 0%, #05050c 60%);
            font-family: 'DM Sans', sans-serif;
            padding-top: 80px;
            min-height: 100vh;
        }

        .container {
            max-width: 1040px;
            margin: 0 auto;
            padding: 20px;
        }

        .contact-layout {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 24px;
            align-items: start;
        }

        .contact-card, .side-card {
            position: relative;
            background: rgba(14, 14, 26, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 36px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            animation: fadeInUp 0.5s ease;
            overflow: hidden;
        }

        .contact-card::before, .side-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #ff6b35 25%, #ff4757 55%, #a855f7 80%, transparent);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contact-card h2 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .contact-card h2 i {
            background: linear-gradient(135deg, #ff6b35, #ff4757);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-right: 10px;
        }

        .contact-card > p {
            color: rgba(255,255,255,0.55);
            margin-bottom: 28px;
        }

        label.field-label {
            display: block;
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.25s;
            font-family: 'DM Sans', sans-serif;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.12);
            color: #fff;
            outline: none;
        }
        .form-select {
    background: #050505 !important;
    color: #ffffff !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
}

.form-select option {
    background: #050505;
    color: #ffffff;
}

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-control.field-error, .form-select.field-error {
            border-color: #ff4757;
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.15);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 140px;
        }

        .char-counter {
            text-align: right;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.35);
            margin-top: 6px;
        }

        .char-counter.warn { color: #ffb020; }
        .char-counter.over { color: #ff4757; }

        .attachment-wrap {
            display: none;
            margin-bottom: 18px;
            animation: fadeInUp 0.25s ease;
        }

        .attachment-wrap.show { display: block; }

        .attachment-hint {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.4);
            margin-top: 6px;
        }

        /* honeypot - hidden from real users, visible to bots that fill every field */
        .hp-field {
            position: absolute !important;
            left: -9999px !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
        }

        .btn-send {
            background: linear-gradient(135deg, #ff6b35, #ff4757);
            border: none;
            padding: 13px 30px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 50px;
            width: 100%;
            color: white;
            transition: all 0.3s;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(255, 107, 53, 0.28);
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(255, 107, 53, 0.4);
        }

        .btn-send:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 12px 20px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid #28a745;
            color: #4ade80;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.12);
            border: 1px solid #ff4757;
            color: #ff8a95;
        }

        .info-box {
            background: rgba(168, 85, 247, 0.08);
            border: 1px solid rgba(168, 85, 247, 0.35);
            border-radius: 15px;
            padding: 15px 18px;
            margin-top: 25px;
        }

        .info-box .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.6);
            font-size: 0.88rem;
            margin-bottom: 8px;
        }

        .info-box .info-row:last-child { margin-bottom: 0; }

        .info-box i {
            color: #a855f7;
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        .info-box a {
            color: #ff8a65;
            text-decoration: none;
            font-weight: 500;
        }

        .info-box a:hover { text-decoration: underline; }

        hr {
            border-color: rgba(255, 255, 255, 0.08);
            margin: 24px 0;
        }

        .msg-notification {
            background: rgba(255, 107, 53, 0.1);
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid #ff6b35;
        }

        .msg-notification i { color: #ff6b35; font-size: 1.2rem; }
        .msg-notification a { color: #ffd700; text-decoration: none; font-weight: 500; }
        .msg-notification a:hover { text-decoration: underline; }

        /* SIDEBAR */
        .side-card h3 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .side-card > p.side-sub {
            color: rgba(255,255,255,0.45);
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .faq-item {
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .faq-item:last-child { border-bottom: none; }

        .faq-q {
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 14px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            gap: 10px;
        }

        .faq-q i.chev {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.35);
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .faq-item.open .faq-q i.chev { transform: rotate(180deg); color: #ff6b35; }

        .faq-a {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.25s ease;
            color: rgba(255,255,255,0.55);
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .faq-item.open .faq-a {
            max-height: 200px;
            padding-bottom: 14px;
        }

        .quick-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .quick-stat .qs-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(255,107,53,0.18), rgba(168,85,247,0.18));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff8a65;
            flex-shrink: 0;
        }

        .quick-stat .qs-text { font-size: 0.82rem; color: rgba(255,255,255,0.6); }
        .quick-stat .qs-text strong { display: block; color: #fff; font-size: 0.88rem; }

        @media (max-width: 900px) {
            .contact-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .contact-card, .side-card {
                padding: 26px;
            }
            .contact-card h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container">
        <div class="contact-layout">

            <!-- MAIN FORM -->
            <div class="contact-card">
                <h2><i class="fas fa-envelope"></i>Contact Us</h2>
                <p>Requests, bugs, upgrades — send it our way and we'll take a look.</p>

                <?php if(isset($_SESSION['user_id']) && $user_unread_count > 0): ?>
                <div class="msg-notification">
                    <i class="fas fa-bell"></i>
                    <span>You have <strong><?php echo $user_unread_count; ?></strong> new admin reply(ies)!
                    <a href="my_messages"><i class="fas fa-arrow-right"></i> View Messages</a></span>
                </div>
                <?php endif; ?>

                <?php if($success_msg): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
                <?php endif; ?>

                <?php if($error_msg): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="contactForm" enctype="multipart/form-data" novalidate>

                    <!-- Honeypot - leave empty, real visitors never see or fill this -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="field-label">Your Name</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g., Alex"
                                   value="<?php echo htmlspecialchars($user_name); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="field-label">Your Email</label>
                            <input type="email" class="form-control" name="email" placeholder="you@example.com"
                                   value="<?php echo htmlspecialchars($user_email); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="field-label">Subject</label>
                        <select class="form-select" name="subject" id="subjectSelect" required>
                            <option value="">Select Subject</option>
                            <option value="Wallpaper Request" <?php echo ($sticky_subject === 'Wallpaper Request') ? 'selected' : ''; ?>>🎨 Wallpaper Request</option>
                            <option value="Account Issue" <?php echo ($sticky_subject === 'Account Issue') ? 'selected' : ''; ?>>🔐 Account Issue</option>
                            <option value="Download Problem" <?php echo ($sticky_subject === 'Download Problem') ? 'selected' : ''; ?>>⬇️ Download Problem</option>
                            <option value="Premium Upgrade" <?php echo ($sticky_subject === 'Premium Upgrade') ? 'selected' : ''; ?>>💎 Premium Upgrade Request</option>
                            <option value="Report Wallpaper" <?php echo ($sticky_subject === 'Report Wallpaper') ? 'selected' : ''; ?>>🚫 Report Wallpaper</option>
                            <option value="Feature Request" <?php echo ($sticky_subject === 'Feature Request') ? 'selected' : ''; ?>>💡 Feature Request</option>
                            <option value="Bug Report" <?php echo ($sticky_subject === 'Bug Report') ? 'selected' : ''; ?>>🐛 Bug Report</option>
                            <option value="Other" <?php echo ($sticky_subject === 'Other') ? 'selected' : ''; ?>>❓ Other</option>
                        </select>
                    </div>

                    <!-- Screenshot attachment - only shown for bug/report subjects -->
                    <div class="attachment-wrap" id="attachmentWrap">
                        <label class="field-label">Attach a screenshot (optional)</label>
                        <input type="file" class="form-control" name="attachment" accept="image/jpeg,image/png,image/webp,image/gif" id="attachmentInput">
                        <div class="attachment-hint"><i class="fas fa-info-circle"></i> Helps us fix it faster. JPG, PNG, WEBP, or GIF, max 5MB.</div>
                    </div>

                    <div class="mb-1">
                        <label class="field-label">Message</label>
                        <textarea class="form-control" name="message" id="messageBox" maxlength="1000"
                                  placeholder="Tell us what's on your mind..." required><?php echo htmlspecialchars($sticky_message); ?></textarea>
                    </div>
                    <div class="char-counter" id="charCounter">0 / 1000</div>

                    <button type="submit" class="btn-send mt-3" id="sendBtn">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>

                <hr>

                <div class="info-box">
                    <div class="info-row"><i class="fas fa-clock"></i><span>Response time: usually within 24 hours</span></div>
                    <div class="info-row"><i class="fas fa-shield-alt"></i><span>Your message is private and only visible to admins</span></div>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="info-row"><i class="fas fa-sign-in-alt"></i><span><a href="login">Login</a> or <a href="register">Register</a> for faster support</span></div>
                    <?php else: ?>
                    <div class="info-row"><i class="fas fa-user-check"></i><span>Logged in as <strong style="color:#fff;">&nbsp;<?php echo htmlspecialchars($user_name); ?></strong>&nbsp;— we'll reply to your registered email</span></div>
                    <div class="info-row"><i class="fas fa-inbox"></i><span><a href="my_messages">View your message history</a></span></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="side-card">
                <h3>Before you send</h3>
                <p class="side-sub">Quick answers that might save you a message</p>

                <div class="faq-item">
                    <button class="faq-q" type="button">
                        Can I request a specific anime or character wallpaper?
                        <i class="fas fa-chevron-down chev"></i>
                    </button>
                    <div class="faq-a">Yes! Pick <strong>Wallpaper Request</strong> as your subject and tell us the anime, character, and scene you'd like. We add new ones regularly.</div>
                </div>

                <div class="faq-item">
                    <button class="faq-q" type="button">
                        Why does the site load slowly sometimes?
                        <i class="fas fa-chevron-down chev"></i>
                    </button>
                    <div class="faq-a">Our wallpapers are true 4K, so file sizes are large. We're always working on speeding things up — thanks for your patience!</div>
                </div>

                <div class="faq-item">
                    <button class="faq-q" type="button">
                        I found a bug or a broken image. What now?
                        <i class="fas fa-chevron-down chev"></i>
                    </button>
                    <div class="faq-a">Choose <strong>Bug Report</strong> as your subject and attach a screenshot if you can — it helps us fix it much faster.</div>
                </div>

                <div class="faq-item">
                    <button class="faq-q" type="button">
                        How long until I get a reply?
                        <i class="fas fa-chevron-down chev"></i>
                    </button>
                    <div class="faq-a">Most messages get a response within 24 hours. If you're logged in, check <strong>My Messages</strong> for replies.</div>
                </div>

                <div class="quick-stat" style="margin-top:22px;">
                    <div class="qs-icon"><i class="fas fa-bolt"></i></div>
                    <div class="qs-text"><strong>~24 hours</strong>Typical response time</div>
                </div>
                <div class="quick-stat">
                    <div class="qs-icon"><i class="fas fa-lock"></i></div>
                    <div class="qs-text"><strong>Private</strong>Only admins see your message</div>
                </div>
            </div>

        </div>
    </div>

    <?php include('footer.php'); ?>

    <script>
        // Prevent double form submission
        document.getElementById('contactForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('sendBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 500);
            });
        }, 5000);

        // Character counter
        const messageBox = document.getElementById('messageBox');
        const charCounter = document.getElementById('charCounter');
        function updateCounter() {
            const len = messageBox.value.length;
            charCounter.textContent = len + ' / 1000';
            charCounter.classList.remove('warn', 'over');
            if (len > 1000) charCounter.classList.add('over');
            else if (len > 850) charCounter.classList.add('warn');
        }
        if (messageBox) {
            messageBox.addEventListener('input', updateCounter);
            updateCounter();
        }

        // Show/hide screenshot attachment based on subject
        const subjectSelect = document.getElementById('subjectSelect');
        const attachmentWrap = document.getElementById('attachmentWrap');
        function toggleAttachment() {
            const showFor = ['Bug Report', 'Report Wallpaper'];
            if (showFor.includes(subjectSelect.value)) {
                attachmentWrap.classList.add('show');
            } else {
                attachmentWrap.classList.remove('show');
            }
        }
        if (subjectSelect) {
            subjectSelect.addEventListener('change', toggleAttachment);
            toggleAttachment();
        }

        // FAQ accordion
        document.querySelectorAll('.faq-q').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const item = btn.closest('.faq-item');
                const wasOpen = item.classList.contains('open');
                document.querySelectorAll('.faq-item').forEach(function(i) { i.classList.remove('open'); });
                if (!wasOpen) item.classList.add('open');
            });
        });
    </script>
</body>
</html>