<?php
// terms.php - Terms of Service Page
session_start();

$pageTitle = "Terms of Service - WallHub";
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
        
        .terms-container {
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
        
        /* Acceptance Box */
        .acceptance-box {
            background: linear-gradient(135deg, rgba(225, 29, 29, 0.15), rgba(225, 29, 29, 0.05));
            border: 1px solid rgba(225, 29, 29, 0.3);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin-top: 30px;
        }
        
        .acceptance-box p {
            color: #ddd;
            margin-bottom: 0;
        }
        
        .acceptance-box i {
            color: #e11d1d;
            font-size: 1.5rem;
            margin-bottom: 10px;
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
        
        /* Table of Contents */
        .toc {
            background: rgba(30, 30, 45, 0.6);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .toc h3 {
            color: #fff;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .toc ul {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .toc li {
            margin: 0;
        }
        
        .toc a {
            background: rgba(255, 255, 255, 0.05);
            padding: 5px 15px;
            border-radius: 20px;
            color: #ccc;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .toc a:hover {
            background: #e11d1d;
            color: #fff;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .terms-container {
                padding: 15px;
            }
            .page-header h1 {
                font-size: 1.8rem;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .toc ul {
                flex-direction: column;
            }
            .toc a {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="terms-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-file-contract"></i> Terms of Service
            </h1>
            <p>Please read these terms carefully before using WallHub</p>
            <div class="last-updated">
                <i class="fas fa-calendar-alt"></i> Last Updated: June 6, 2026
            </div>
        </div>
        
        <!-- Table of Contents -->
        <div class="toc">
            <h3><i class="fas fa-list"></i> Table of Contents</h3>
            <ul>
                <li><a href="#acceptance">1. Acceptance of Terms</a></li>
                <li><a href="#eligibility">2. Eligibility</a></li>
                <li><a href="#account">3. User Accounts</a></li>
                <li><a href="#content">4. User Content</a></li>
                <li><a href="#premium">5. Premium Membership</a></li>
                <li><a href="#downloads">6. Download Limits</a></li>
                <li><a href="#prohibited">7. Prohibited Conduct</a></li>
                <li><a href="#copyright">8. Copyright Policy</a></li>
                <li><a href="#termination">9. Termination</a></li>
                <li><a href="#disclaimer">10. Disclaimer of Warranties</a></li>
                <li><a href="#liability">11. Limitation of Liability</a></li>
                <li><a href="#indemnification">12. Indemnification</a></li>
                <li><a href="#changes">13. Changes to Terms</a></li>
                <li><a href="#contact">14. Contact Us</a></li>
            </ul>
        </div>
        
        <!-- Section 1 - Acceptance -->
        <div class="content-card" id="acceptance">
            <div class="card-header">
                <h2><i class="fas fa-check-circle"></i> 1. Acceptance of Terms</h2>
            </div>
            <div class="card-body">
                <p>By accessing or using WallHub ("the Website", "we", "us", "our"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, please do not use the Website.</p>
                <p>These Terms apply to all visitors, users, and others who access the Website. By using WallHub, you represent that you have read, understood, and agree to be bound by these Terms.</p>
            </div>
        </div>
        
        <!-- Section 2 - Eligibility -->
        <div class="content-card" id="eligibility">
            <div class="card-header">
                <h2><i class="fas fa-user-check"></i> 2. Eligibility</h2>
            </div>
            <div class="card-body">
                <p>To use WallHub, you must:</p>
                <ul>
                    <li>Be at least 13 years of age (or the age of digital consent in your jurisdiction)</li>
                    <li>Have the legal capacity to enter into a binding agreement</li>
                    <li>Not be prohibited from using the Website by any applicable law</li>
                    <li>Provide accurate and complete information when registering an account</li>
                </ul>
                <p>If you are under 18, you must have parental or guardian consent to use the Website.</p>
            </div>
        </div>
        
        <!-- Section 3 - User Accounts -->
        <div class="content-card" id="account">
            <div class="card-header">
                <h2><i class="fas fa-id-card"></i> 3. User Accounts</h2>
            </div>
            <div class="card-body">
                <p>To access certain features of WallHub, you may need to create an account. You are responsible for:</p>
                <ul>
                    <li>Maintaining the confidentiality of your password</li>
                    <li>All activities that occur under your account</li>
                    <li>Notifying us immediately of any unauthorized use of your account</li>
                    <li>Providing accurate and up-to-date information</li>
                </ul>
                <p>We reserve the right to suspend or terminate accounts that violate these Terms or contain inaccurate information.</p>
            </div>
        </div>
        
        <!-- Section 4 - User Content -->
        <div class="content-card" id="content">
            <div class="card-header">
                <h2><i class="fas fa-images"></i> 4. User Content</h2>
            </div>
            <div class="card-body">
                <p>WallHub allows users to download, favorite, and interact with wallpapers. By using the Website, you acknowledge that:</p>
                <ul>
                    <li>Wallpapers are provided for personal, non-commercial use only</li>
                    <li>You may not redistribute, sell, or claim ownership of wallpapers</li>
                    <li>You retain no ownership rights to wallpapers you download</li>
                    <li>All wallpapers are the property of their respective copyright holders</li>
                </ul>
                
                <h3>User-Generated Content</h3>
                <p>If you submit comments, reviews, or other content to WallHub:</p>
                <ul>
                    <li>You grant us a non-exclusive, royalty-free license to display your content</li>
                    <li>You represent that you have the right to submit the content</li>
                    <li>Your content must not violate any third-party rights or laws</li>
                    <li>We may moderate or remove content at our discretion</li>
                </ul>
            </div>
        </div>
        
        <!-- Section 5 - Premium Membership -->
        <div class="content-card" id="premium">
            <div class="card-header">
                <h2><i class="fas fa-crown"></i> 5. Premium Membership</h2>
            </div>
            <div class="card-body">
                <p>WallHub offers a Premium membership with additional features. By purchasing Premium, you agree to:</p>
                <ul>
                    <li>Pay all applicable fees as specified at the time of purchase</li>
                    <li>Provide accurate billing information</li>
                    <li>Understand that Premium features are subject to change</li>
                    <li>Premium subscriptions automatically renew unless cancelled</li>
                </ul>
                
                <div class="notice-box">
                    <h4><i class="fas fa-credit-card"></i> Refund Policy</h4>
                    <p>Premium membership fees are non-refundable except as required by law. You may cancel your subscription at any time from your account settings. Cancellation will take effect at the end of your current billing period.</p>
                </div>
            </div>
        </div>
        
        <!-- Section 6 - Download Limits -->
        <div class="content-card" id="downloads">
            <div class="card-header">
                <h2><i class="fas fa-download"></i> 6. Download Limits</h2>
            </div>
            <div class="card-body">
                <p>WallHub imposes the following download limits to ensure fair usage:</p>
                <ul>
                    <li><strong>Members:</strong> 10 downloads per day</li>
                    <li><strong>Premium Members:</strong> Unlimited downloads</li>
                    <li><strong>Admin/Owner:</strong> Unlimited downloads</li>
                </ul>
                <p>We reserve the right to adjust these limits or implement additional restrictions to prevent abuse of our services.</p>
            </div>
        </div>
        
        <!-- Section 7 - Prohibited Conduct -->
        <div class="content-card" id="prohibited">
            <div class="card-header">
                <h2><i class="fas fa-ban"></i> 7. Prohibited Conduct</h2>
            </div>
            <div class="card-body">
                <p>You agree NOT to:</p>
                <ul>
                    <li>Use automated scripts or bots to download wallpapers</li>
                    <li>Attempt to bypass download limits or security measures</li>
                    <li>Upload or distribute malicious code or viruses</li>
                    <li>Harass, abuse, or harm other users</li>
                    <li>Impersonate any person or entity</li>
                    <li>Use the Website for any illegal purpose</li>
                    <li>Reproduce, duplicate, copy, or resell any part of the Website</li>
                    <li>Interfere with or disrupt the Website's operation</li>
                    <li>Attempt to gain unauthorized access to any systems or networks</li>
                    <li>Use the Website to transmit spam or unsolicited messages</li>
                </ul>
                
                <div class="notice-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Violation Consequences</h4>
                    <p>Violation of these prohibitions may result in immediate termination of your account and legal action where applicable.</p>
                </div>
            </div>
        </div>
        
        <!-- Section 8 - Copyright Policy -->
        <div class="content-card" id="copyright">
            <div class="card-header">
                <h2><i class="fas fa-copyright"></i> 8. Copyright Policy</h2>
            </div>
            <div class="card-body">
                <p>WallHub respects intellectual property rights. We comply with the Digital Millennium Copyright Act (DMCA) and will respond to valid copyright infringement notices.</p>
                <p>If you believe your copyrighted work has been infringed, please submit a DMCA notice to <a href="mailto:support@wallhub.online">support@wallhub.online</a> or through our <a href="dmca.php">DMCA page</a>.</p>
                <p>We maintain a policy of terminating, in appropriate circumstances, accounts of repeat infringers.</p>
            </div>
        </div>
        
        <!-- Section 9 - Termination -->
        <div class="content-card" id="termination">
            <div class="card-header">
                <h2><i class="fas fa-ban"></i> 9. Termination</h2>
            </div>
            <div class="card-body">
                <p>We may terminate or suspend your account immediately, without prior notice, for any reason including:</p>
                <ul>
                    <li>Violation of these Terms</li>
                    <li>Fraudulent or illegal activity</li>
                    <li>Request by law enforcement or government agencies</li>
                    <li>Extended periods of inactivity</li>
                    <li>Technical or security issues</li>
                </ul>
                <p>Upon termination, your right to use the Website will immediately cease. You may terminate your account at any time by contacting us.</p>
            </div>
        </div>
        
        <!-- Section 10 - Disclaimer -->
        <div class="content-card" id="disclaimer">
            <div class="card-header">
                <h2><i class="fas fa-shield-alt"></i> 10. Disclaimer of Warranties</h2>
            </div>
            <div class="card-body">
                <p>THE WEBSITE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT ANY WARRANTIES OF ANY KIND. TO THE FULLEST EXTENT PERMITTED BY LAW, WE DISCLAIM ALL WARRANTIES, INCLUDING:</p>
                <ul>
                    <li>IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE</li>
                    <li>WARRANTIES OF NON-INFRINGEMENT</li>
                    <li>WARRANTIES REGARDING THE ACCURACY, RELIABILITY, OR AVAILABILITY OF THE WEBSITE</li>
                    <li>WARRANTIES THAT THE WEBSITE WILL BE UNINTERRUPTED OR ERROR-FREE</li>
                </ul>
                <p>We do not warrant that wallpapers on the Website are free from copyright restrictions. Users are responsible for ensuring their use of downloaded content complies with applicable laws.</p>
            </div>
        </div>
        
        <!-- Section 11 - Liability -->
        <div class="content-card" id="liability">
            <div class="card-header">
                <h2><i class="fas fa-gavel"></i> 11. Limitation of Liability</h2>
            </div>
            <div class="card-body">
                <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, WALLHUB AND ITS OWNERS, OFFICERS, EMPLOYEES, AND AGENTS SHALL NOT BE LIABLE FOR:</p>
                <ul>
                    <li>INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES</li>
                    <li>LOSS OF PROFITS, DATA, OR GOODWILL</li>
                    <li>UNAUTHORIZED ACCESS TO OR ALTERATION OF YOUR DATA</li>
                    <li>ANY CONDUCT OR CONTENT OF THIRD PARTIES</li>
                </ul>
                <p>Our total liability to you shall not exceed the amount you paid us, if any, in the preceding 12 months.</p>
            </div>
        </div>
        
        <!-- Section 12 - Indemnification -->
        <div class="content-card" id="indemnification">
            <div class="card-header">
                <h2><i class="fas fa-handshake"></i> 12. Indemnification</h2>
            </div>
            <div class="card-body">
                <p>You agree to indemnify, defend, and hold harmless WallHub, its owners, officers, employees, and agents from any claims, damages, losses, liabilities, costs, and expenses arising from:</p>
                <ul>
                    <li>Your use of the Website</li>
                    <li>Your violation of these Terms</li>
                    <li>Your violation of any third-party rights, including copyright</li>
                    <li>Your content or conduct on the Website</li>
                </ul>
            </div>
        </div>
        
        <!-- Section 13 - Changes -->
        <div class="content-card" id="changes">
            <div class="card-header">
                <h2><i class="fas fa-sync-alt"></i> 13. Changes to Terms</h2>
            </div>
            <div class="card-body">
                <p>We reserve the right to modify these Terms at any time. Changes will be effective immediately upon posting to the Website. Your continued use of the Website after changes constitutes acceptance of the modified Terms.</p>
                <p>We will notify users of material changes via:</p>
                <ul>
                    <li>Website notifications</li>
                    <li>Email (if you have provided one)</li>
                    <li>A notice on the Website homepage</li>
                </ul>
                <p>It is your responsibility to review these Terms periodically.</p>
            </div>
        </div>
        
        <!-- Section 14 - Contact -->
        <div class="content-card" id="contact">
            <div class="card-header">
                <h2><i class="fas fa-envelope"></i> 14. Contact Us</h2>
            </div>
            <div class="card-body">
                <p>If you have any questions about these Terms, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:support@wallhub.online">support@wallhub.online</a></li>
                    <li><strong>Via Contact Form:</strong> <a href="contact.php">Contact Page</a></li>
                </ul>
                <p>We aim to respond to all inquiries within 24-48 hours.</p>
            </div>
        </div>
        
        <!-- Acceptance Box -->
        <div class="acceptance-box">
            <i class="fas fa-check-circle"></i>
            <p><strong>By using WallHub, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</strong></p>
            <p style="font-size: 0.85rem; margin-top: 10px;">© 2026 WallHub. All rights reserved.</p>
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
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>