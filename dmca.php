<?php
// dmca.php - DMCA Policy Page
session_start();

$pageTitle = "DMCA Policy - WallHub";
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
        
        .dmca-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Section */
        .page-header {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(225, 29, 29, 0.3);
            text-align: center;
        }
        
        .page-header h1 {
            color: #fff;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .page-header h1 i {
            color: #e11d1d;
            margin-right: 15px;
        }
        
        .page-header p {
            color: #aaa;
            font-size: 1rem;
        }
        
        .last-updated {
            background: rgba(225, 29, 29, 0.1);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            color: #ffd166;
            font-size: 0.85rem;
            margin-top: 15px;
        }
        
        /* Content Cards */
        .content-card {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(225, 29, 29, 0.3);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(225, 29, 29, 0.4), rgba(225, 29, 29, 0.15));
            border-bottom: 2px solid #e11d1d;
            padding: 18px 25px;
        }
        
        .card-header h2 {
            color: #fff;
            font-size: 1.5rem;
            margin: 0;
        }
        
        .card-header h2 i {
            color: #e11d1d;
            margin-right: 12px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .card-body p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .card-body h3 {
            color: #ffd166;
            font-size: 1.2rem;
            margin: 20px 0 10px;
        }
        
        .card-body h3:first-of-type {
            margin-top: 0;
        }
        
        .card-body ul, .card-body ol {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
            padding-left: 20px;
        }
        
        .card-body li {
            margin-bottom: 8px;
        }
        
        .card-body strong {
            color: #e11d1d;
        }
        
        .card-body a {
            color: #e11d1d;
            text-decoration: none;
        }
        
        .card-body a:hover {
            text-decoration: underline;
        }
        
        /* Notice Box */
        .notice-box {
            background: rgba(225, 29, 29, 0.1);
            border-left: 4px solid #e11d1d;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .notice-box h4 {
            color: #e11d1d;
            margin-bottom: 10px;
        }
        
        .notice-box p {
            margin-bottom: 0;
        }
        
        /* DMCA Form */
        .dmca-form {
            background: rgba(30, 30, 45, 0.8);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            color: #fff;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-group label .required {
            color: #e11d1d;
        }
        
        .form-control {
            background: #f5f5f5;
            border: 2px solid #e11d1d;
            border-radius: 10px;
            padding: 12px 15px;
            width: 100%;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(225, 29, 29, 0.3);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(225, 29, 29, 0.4);
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
        
        .info-card i {
            font-size: 2rem;
            color: #e11d1d;
            margin-bottom: 15px;
        }
        
        .info-card h4 {
            color: #fff;
            margin-bottom: 10px;
        }
        
        .info-card p {
            color: #aaa;
            font-size: 0.85rem;
            margin: 0;
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 99;
        }
        
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(225, 29, 29, 0.4);
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .dmca-container {
                padding: 15px;
            }
            .page-header h1 {
                font-size: 1.8rem;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="dmca-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-copyright"></i> DMCA Policy
            </h1>
            <p>Digital Millennium Copyright Act - Copyright Infringement Notification</p>
            <div class="last-updated">
                <i class="fas fa-calendar-alt"></i> Last Updated: June 6, 2026
            </div>
        </div>
        
        <!-- Introduction -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Introduction</h2>
            </div>
            <div class="card-body">
                <p>WallHub respects the intellectual property rights of others and expects its users to do the same. In accordance with the Digital Millennium Copyright Act (DMCA), we will respond promptly to claims of copyright infringement that are reported to our designated copyright agent.</p>
                <p>If you believe that your copyrighted work has been copied in a way that constitutes copyright infringement and is accessible on WallHub, please notify our copyright agent as set forth in the DMCA.</p>
            </div>
        </div>
        
        <!-- What is DMCA -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-gavel"></i> What is the DMCA?</h2>
            </div>
            <div class="card-body">
                <p>The Digital Millennium Copyright Act (DMCA) is a United States copyright law that provides a framework for copyright owners to protect their work online. It establishes a notification and takedown process for copyrighted material that appears on websites without permission.</p>
                <p>While WallHub operates globally, we comply with DMCA requirements to protect both copyright owners and our users.</p>
            </div>
        </div>
        
        <!-- How to File a Complaint -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-file-alt"></i> How to File a DMCA Complaint</h2>
            </div>
            <div class="card-body">
                <p>To file a copyright infringement notification with us, you must submit a written communication that includes substantially the following:</p>
                
                <div class="notice-box">
                    <h4><i class="fas fa-list-check"></i> Required Information:</h4>
                    <ol style="margin-top: 10px;">
                        <li><strong>Identification of the copyrighted work</strong> you claim has been infringed, or if multiple works, a representative list.</li>
                        <li><strong>Identification of the material</strong> that is claimed to be infringing and that is to be removed, with sufficient detail (e.g., URL links).</li>
                        <li><strong>Your contact information</strong> including name, address, telephone number, and email address.</li>
                        <li><strong>A statement</strong> that you have a good faith belief that use of the material is not authorized by the copyright owner.</li>
                        <li><strong>A statement</strong> that the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the copyright owner.</li>
                        <li><strong>Your physical or electronic signature</strong> (typing your full name is sufficient).</li>
                    </ol>
                </div>
                
                <p><strong>Submit your DMCA notice to:</strong></p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:support@wallhub.online">support@wallhub.online</a></li>
                    <li><strong>Contact Form:</strong> <a href="contact.php">Contact Page</a></li>
                </ul>
            </div>
        </div>
        
        <!-- DMCA Submission Form -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-envelope"></i> Submit DMCA Notice</h2>
            </div>
            <div class="card-body">
                <p>Use the form below to submit a DMCA takedown notice. Please fill out all required fields accurately.</p>
                
                <?php
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_dmca'])) {
                    $name = trim($_POST['name']);
                    $email = trim($_POST['email']);
                    $copyright_work = trim($_POST['copyright_work']);
                    $infringing_url = trim($_POST['infringing_url']);
                    $message = trim($_POST['message']);
                    
                    $errors = [];
                    
                    if (empty($name)) $errors[] = "Name is required";
                    if (empty($email)) $errors[] = "Email is required";
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
                    if (empty($copyright_work)) $errors[] = "Copyrighted work description is required";
                    if (empty($infringing_url)) $errors[] = "Infringing URL is required";
                    
                    if (empty($errors)) {
                        // Send email to admin
                        $to = "support@wallhub.online";
                        $subject = "DMCA Takedown Notice - WallHub";
                        $headers = "From: " . $email . "\r\n";
                        $headers .= "Reply-To: " . $email . "\r\n";
                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                        
                        $email_body = "
                        <html>
                        <head><title>DMCA Takedown Notice</title></head>
                        <body>
                        <h2>DMCA Copyright Infringement Notice</h2>
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Copyrighted Work:</strong><br>" . nl2br(htmlspecialchars($copyright_work)) . "</p>
                        <p><strong>Infringing URL:</strong><br>" . htmlspecialchars($infringing_url) . "</p>
                        <p><strong>Additional Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
                        <hr>
                        <p><em>This DMCA notice was submitted from wallhub.online/dmca.php</em></p>
                        </body>
                        </html>
                        ";
                        
                        if (mail($to, $subject, $email_body, $headers)) {
                            echo '<div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Your DMCA notice has been submitted. We will review it within 24-48 hours.
                            </div>';
                        } else {
                            echo '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> There was an error sending your notice. Please email us directly at support@wallhub.online
                            </div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> Please fix the following errors:<br>
                            <ul>';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                }
                ?>
                
                <form method="POST" class="dmca-form">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description of Copyrighted Work <span class="required">*</span></label>
                        <textarea name="copyright_work" class="form-control" placeholder="Please describe the original work that has been infringed..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Infringing Material URL(s) <span class="required">*</span></label>
                        <textarea name="infringing_url" class="form-control" placeholder="Enter the URL(s) where the infringing material is located on WallHub..." required></textarea>
                        <small style="color:#aaa;">You can list multiple URLs, one per line.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Additional Information (Optional)</label>
                        <textarea name="message" class="form-control" placeholder="Any additional details you would like to provide..."></textarea>
                    </div>
                    
                    <div class="notice-box" style="margin-top: 20px;">
                        <p><strong><i class="fas fa-check-circle"></i> By submitting this notice, you confirm:</strong></p>
                        <ul style="margin-top: 10px;">
                            <li>You have a good faith belief that use of the material is not authorized.</li>
                            <li>The information in this notice is accurate.</li>
                            <li>You are authorized to act on behalf of the copyright owner.</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="submit_dmca" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit DMCA Notice
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Counter-Notification -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-balance-scale"></i> Counter-Notification</h2>
            </div>
            <div class="card-body">
                <p>If you believe that your material was removed or disabled as a result of mistake or misidentification, you may submit a counter-notification. To file a counter-notification, please provide:</p>
                <ul>
                    <li><strong>Identification of the material</strong> that was removed and its location before removal.</li>
                    <li><strong>A statement under penalty of perjury</strong> that you have a good faith belief the material was removed due to mistake.</li>
                    <li><strong>Your contact information</strong> including name, address, telephone number, and email address.</li>
                    <li><strong>A statement</strong> consenting to the jurisdiction of the federal court in your district.</li>
                    <li><strong>Your physical or electronic signature</strong> (typing your full name is sufficient).</li>
                </ul>
                <p>Submit counter-notifications to <a href="mailto:support@wallhub.online">support@wallhub.online</a></p>
            </div>
        </div>
        
        <!-- Repeat Infringers -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-repeat"></i> Repeat Infringers</h2>
            </div>
            <div class="card-body">
                <p>WallHub maintains a policy of terminating, in appropriate circumstances, user accounts that are repeat infringers of copyright. We may also limit access to our website and/or terminate the accounts of any users who infringe any intellectual property rights of others.</p>
            </div>
        </div>
        
        <!-- Important Information -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Important Notes</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-card">
                        <i class="fas fa-clock"></i>
                        <h4>Response Time</h4>
                        <p>We typically respond within 24-48 hours</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-gavel"></i>
                        <h4>Legal Consequences</h4>
                        <p>False claims may result in legal liability</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Safe Harbor</h4>
                        <p>We comply with DMCA Safe Harbor provisions</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-globe"></i>
                        <h4>International Users</h4>
                        <p>International copyright laws also respected</p>
                    </div>
                </div>
                
                <div class="notice-box">
                    <h4><i class="fas fa-info-circle"></i> Disclaimer</h4>
                    <p>This information is not legal advice. If you are unsure whether material infringes your copyright, please consult with an attorney before submitting a notice. WallHub may disclose DMCA notices to the public or to the alleged infringer as required by law.</p>
                </div>
            </div>
        </div>
        
        <!-- Contact -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-address-card"></i> Designated Copyright Agent</h2>
            </div>
            <div class="card-body">
                <p><strong>DMCA Agent Contact:</strong></p>
                <ul>
                    <li><strong>Name:</strong> Copyright Agent - WallHub</li>
                    <li><strong>Email:</strong> <a href="mailto:support@wallhub.online">support@wallhub.online</a></li>
                    <li><strong>Via Contact Form:</strong> <a href="contact.php">Contact Page</a></li>
                </ul>
                <p>For faster processing, please use the DMCA submission form above or email directly with the subject line "DMCA Takedown Notice".</p>
            </div>
        </div>
    </div>
    
    <!-- Back to Top Button -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script>
        // Back to Top button
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        if (backToTop) {
            backToTop.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    </script>
</body>
</html>