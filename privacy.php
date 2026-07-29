<?php
// privacy.php - Privacy Policy Page
session_start();

$pageTitle = "Privacy Policy - WallHub";
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
        
        .privacy-container {
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
        
        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .data-table th {
            background: rgba(225, 29, 29, 0.2);
            color: #fff;
            font-weight: 600;
        }
        
        .data-table td {
            color: #ccc;
        }
        
        .data-table tr:hover td {
            background: rgba(225, 29, 29, 0.05);
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .privacy-container {
                padding: 15px;
            }
            .page-header h1 {
                font-size: 1.8rem;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .data-table {
                font-size: 0.8rem;
            }
            .data-table th,
            .data-table td {
                padding: 8px 10px;
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
    
    <div class="privacy-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-lock"></i> Privacy Policy
            </h1>
            <p>How WallHub collects, uses, and protects your personal information</p>
            <div class="last-updated">
                <i class="fas fa-calendar-alt"></i> Last Updated: June 6, 2026
            </div>
        </div>
        
        <!-- Table of Contents -->
        <div class="toc">
            <h3><i class="fas fa-list"></i> Table of Contents</h3>
            <ul>
                <li><a href="#intro">1. Introduction</a></li>
                <li><a href="#info">2. Information We Collect</a></li>
                <li><a href="#usage">3. How We Use Your Information</a></li>
                <li><a href="#cookies">4. Cookies & Tracking</a></li>
                <li><a href="#sharing">5. Information Sharing</a></li>
                <li><a href="#security">6. Data Security</a></li>
                <li><a href="#retention">7. Data Retention</a></li>
                <li><a href="#rights">8. Your Rights</a></li>
                <li><a href="#children">9. Children's Privacy</a></li>
                <li><a href="#thirdparty">10. Third-Party Links</a></li>
                <li><a href="#international">11. International Users</a></li>
                <li><a href="#changes">12. Changes to Policy</a></li>
                <li><a href="#contact">13. Contact Us</a></li>
            </ul>
        </div>
        
        <!-- Section 1 - Introduction -->
        <div class="content-card" id="intro">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> 1. Introduction</h2>
            </div>
            <div class="card-body">
                <p>Welcome to WallHub ("we", "us", "our", "the Website"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website wallhub.online.</p>
                <p>We are committed to protecting your privacy and ensuring that your personal information is handled responsibly. By using WallHub, you consent to the data practices described in this policy.</p>
                
                <div class="notice-box">
                    <h4><i class="fas fa-building"></i> Data Controller</h4>
                    <p>WallHub is the data controller for your information. If you have any questions about this policy, please contact us at support@wallhub.online</p>
                </div>
            </div>
        </div>
        
        <!-- Section 2 - Information We Collect -->
        <div class="content-card" id="info">
            <div class="card-header">
                <h2><i class="fas fa-database"></i> 2. Information We Collect</h2>
            </div>
            <div class="card-body">
                <p>We collect several types of information to provide and improve our services:</p>
                
                <h3><i class="fas fa-user"></i> Personal Information You Provide</h3>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, username, password</li>
                    <li><strong>Profile Information:</strong> Preferences, favorite wallpapers, download history</li>
                    <li><strong>Communication:</strong> Messages sent through contact forms, support requests</li>
                    <li><strong>Payment Information:</strong> If you purchase Premium membership (processed by secure payment providers)</li>
                </ul>
                
                <h3><i class="fas fa-chart-line"></i> Automatically Collected Information</h3>
                <ul>
                    <li><strong>Usage Data:</strong> Pages visited, wallpapers viewed, downloads, search queries</li>
                    <li><strong>Device Information:</strong> IP address, browser type, operating system, device type</li>
                    <li><strong>Location Data:</strong> Approximate geographic location based on IP address</li>
                    <li><strong>Referral Information:</strong> How you found WallHub (search engine, social media, etc.)</li>
                </ul>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>Examples</th>
                            <th>Legal Basis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Account Data</strong></td>
                            <td>Name, Email, Username</td>
                            <td>Contract (account creation)</td>
                        </tr>
                        <tr>
                            <td><strong>Usage Data</strong></td>
                            <td>Downloads, Favorites, Search</td>
                            <td>Legitimate interest (improving service)</td>
                        </tr>
                        <tr>
                            <td><strong>Technical Data</strong></td>
                            <td>IP, Browser, Device</td>
                            <td>Legitimate interest (security)</td>
                        </tr>
                        <tr>
                            <td><strong>Communications</strong></td>
                            <td>Messages, Support tickets</td>
                            <td>Consent / Contract</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Section 3 - How We Use Information -->
        <div class="content-card" id="usage">
            <div class="card-header">
                <h2><i class="fas fa-cogs"></i> 3. How We Use Your Information</h2>
            </div>
            <div class="card-body">
                <p>We use your information for the following purposes:</p>
                <ul>
                    <li><strong>To Provide Services:</strong> Create and manage your account, process downloads, save favorites</li>
                    <li><strong>To Improve WallHub:</strong> Analyze usage patterns, fix bugs, develop new features</li>
                    <li><strong>To Communicate:</strong> Send account notifications, respond to inquiries, provide support</li>
                    <li><strong>To Ensure Security:</strong> Detect and prevent fraud, abuse, or unauthorized access</li>
                    <li><strong>To Enforce Terms:</strong> Monitor compliance with our Terms of Service</li>
                    <li><strong>For Analytics:</strong> Understand how users interact with the Website</li>
                    <li><strong>For Marketing (with consent):</strong> Send newsletters or promotional offers (opt-out available)</li>
                </ul>
                
                <div class="notice-box">
                    <h4><i class="fas fa-envelope"></i> Marketing Communications</h4>
                    <p>You may opt out of marketing emails at any time by clicking the "unsubscribe" link in any email or by contacting us. We will never sell your email address to third parties.</p>
                </div>
            </div>
        </div>
        
        <!-- Section 4 - Cookies -->
        <div class="content-card" id="cookies">
            <div class="card-header">
                <h2><i class="fas fa-cookie-bite"></i> 4. Cookies & Tracking Technologies</h2>
            </div>
            <div class="card-body">
                <p>WallHub uses cookies and similar tracking technologies to enhance your experience. For detailed information, please see our <a href="cookies.php">Cookie Policy</a>.</p>
                
                <h3>Types of Cookies We Use:</h3>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required for login, session management, and core functionality</li>
                    <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors use the Website</li>
                    <li><strong>Functional Cookies:</strong> Enable features like favorites and download history</li>
                </ul>
                
                <p>You can manage cookie preferences through your browser settings or our cookie consent tool.</p>
            </div>
        </div>
        
        <!-- Section 5 - Information Sharing -->
        <div class="content-card" id="sharing">
            <div class="card-header">
                <h2><i class="fas fa-share-alt"></i> 5. Information Sharing</h2>
            </div>
            <div class="card-body">
                <p>We do not sell your personal information. We may share your information in the following circumstances:</p>
                <ul>
                    <li><strong>Service Providers:</strong> Third parties who help us operate the Website (hosting, analytics, payment processing)</li>
                    <li><strong>Legal Requirements:</strong> If required by law, court order, or government regulation</li>
                    <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                    <li><strong>Protection of Rights:</strong> To protect the security, property, or rights of WallHub or its users</li>
                    <li><strong>With Your Consent:</strong> When you have explicitly agreed to the sharing</li>
                </ul>
                
                <h3>Third-Party Services We Use:</h3>
                <ul>
                    <li><strong>Hosting Provider:</strong> GoViralHost (server logs, data storage)</li>
                    <li><strong>Payment Processors:</strong> For Premium membership payments (if applicable)</li>
                    <li><strong>Analytics:</strong> Google Analytics for anonymous usage tracking (opt-out available)</li>
                </ul>
            </div>
        </div>
        
        <!-- Section 6 - Data Security -->
        <div class="content-card" id="security">
            <div class="card-header">
                <h2><i class="fas fa-shield-alt"></i> 6. Data Security</h2>
            </div>
            <div class="card-body">
                <p>We implement appropriate technical and organizational measures to protect your information:</p>
                <ul>
                    <li><strong>Encryption:</strong> Passwords are hashed using bcrypt; SSL/TLS encryption for data transmission</li>
                    <li><strong>Access Controls:</strong> Limited access to personal information on a need-to-know basis</li>
                    <li><strong>Regular Audits:</strong> We review our security practices regularly</li>
                    <li><strong>Secure Hosting:</strong> Our hosting provider implements industry-standard security measures</li>
                </ul>
                
                <div class="notice-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> No Method is 100% Secure</h4>
                    <p>While we strive to protect your information, no method of transmission over the Internet is completely secure. We cannot guarantee absolute security.</p>
                </div>
            </div>
        </div>
        
        <!-- Section 7 - Data Retention -->
        <div class="content-card" id="retention">
            <div class="card-header">
                <h2><i class="fas fa-clock"></i> 7. Data Retention</h2>
            </div>
            <div class="card-body">
                <p>We retain your information for as long as necessary to provide services and fulfill the purposes outlined in this policy:</p>
                <ul>
                    <li><strong>Account Information:</strong> Until you delete your account or request removal</li>
                    <li><strong>Usage Data:</strong> For up to 24 months for analytics purposes</li>
                    <li><strong>Communication Records:</strong> For up to 3 years to maintain support history</li>
                    <li><strong>Download History:</strong> As long as your account is active</li>
                </ul>
                <p>After account deletion, we will anonymize or delete your personal information within 30 days, except where retention is required by law.</p>
            </div>
        </div>
        
        <!-- Section 8 - Your Rights -->
        <div class="content-card" id="rights">
            <div class="card-header">
                <h2><i class="fas fa-gavel"></i> 8. Your Rights</h2>
            </div>
            <div class="card-body">
                <p>Depending on your location, you may have the following rights regarding your personal information:</p>
                <div class="info-grid">
                    <div class="info-card">
                        <i class="fas fa-eye"></i>
                        <h4>Right to Access</h4>
                        <p>Request a copy of your personal data</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-pen"></i>
                        <h4>Right to Rectify</h4>
                        <p>Correct inaccurate or incomplete data</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-trash"></i>
                        <h4>Right to Delete</h4>
                        <p>Request deletion of your data (subject to exceptions)</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-ban"></i>
                        <h4>Right to Object</h4>
                        <p>Object to processing of your data</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-download"></i>
                        <h4>Right to Portability</h4>
                        <p>Receive your data in a portable format</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-toggle-off"></i>
                        <h4>Right to Withdraw Consent</h4>
                        <p>Withdraw consent at any time</p>
                    </div>
                </div>
                
                <p>To exercise these rights, please contact us at <a href="mailto:support@wallhub.online">support@wallhub.online</a>. We will respond within 30 days.</p>
            </div>
        </div>
        
        <!-- Section 9 - Children's Privacy -->
        <div class="content-card" id="children">
            <div class="card-header">
                <h2><i class="fas fa-child"></i> 9. Children's Privacy</h2>
            </div>
            <div class="card-body">
                <p>WallHub is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If we learn we have collected information from a child under 13, we will delete it immediately.</p>
                <p>If you believe a child under 13 has provided us with personal information, please contact us.</p>
            </div>
        </div>
        
        <!-- Section 10 - Third-Party Links -->
        <div class="content-card" id="thirdparty">
            <div class="card-header">
                <h2><i class="fas fa-external-link-alt"></i> 10. Third-Party Links</h2>
            </div>
            <div class="card-body">
                <p>WallHub may contain links to third-party websites. We are not responsible for the privacy practices or content of these sites. We encourage you to read the privacy policies of any linked websites you visit.</p>
            </div>
        </div>
        
        <!-- Section 11 - International Users -->
        <div class="content-card" id="international">
            <div class="card-header">
                <h2><i class="fas fa-globe"></i> 11. International Users</h2>
            </div>
            <div class="card-body">
                <p>WallHub is hosted in India and Germany. If you access the Website from outside these countries, your information may be transferred to, stored, and processed in these countries where our servers are located.</p>
                <p>For users in the European Economic Area (EEA), we comply with GDPR requirements. For users in India, we comply with the Information Technology Act, 2000 and its rules.</p>
            </div>
        </div>
        
        <!-- Section 12 - Changes -->
        <div class="content-card" id="changes">
            <div class="card-header">
                <h2><i class="fas fa-sync-alt"></i> 12. Changes to This Privacy Policy</h2>
            </div>
            <div class="card-body">
                <p>We may update this Privacy Policy from time to time. Changes will be effective immediately upon posting to the Website. We will notify users of material changes via:</p>
                <ul>
                    <li>Website notification banner</li>
                    <li>Email (if you have provided one)</li>
                    <li>Updating the "Last Updated" date at the top of this page</li>
                </ul>
                <p>We encourage you to review this policy periodically.</p>
            </div>
        </div>
        
        <!-- Section 13 - Contact -->
        <div class="content-card" id="contact">
            <div class="card-header">
                <h2><i class="fas fa-envelope"></i> 13. Contact Us</h2>
            </div>
            <div class="card-body">
                <p>If you have questions about this Privacy Policy or wish to exercise your privacy rights, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:support@wallhub.online">support@wallhub.online</a></li>
                    <li><strong>Via Contact Form:</strong> <a href="contact.php">Contact Page</a></li>
                </ul>
                <p>For DMCA copyright notices, please use our <a href="dmca.php">DMCA page</a>.</p>
                
                <div class="notice-box">
                    <h4><i class="fas fa-clock"></i> Response Time</h4>
                    <p>We aim to respond to all privacy-related inquiries within 30 days as required by law.</p>
                </div>
            </div>
        </div>
        
        <!-- Acceptance Box -->
        <div class="acceptance-box" style="background: linear-gradient(135deg, rgba(225, 29, 29, 0.15), rgba(225, 29, 29, 0.05)); border: 1px solid rgba(225, 29, 29, 0.3); border-radius: 15px; padding: 25px; text-align: center; margin-top: 30px;">
            <i class="fas fa-shield-alt" style="color: #e11d1d; font-size: 2rem; margin-bottom: 15px;"></i>
            <p><strong>By using WallHub, you acknowledge that you have read and understood this Privacy Policy.</strong></p>
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