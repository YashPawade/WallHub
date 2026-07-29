<?php
// cookies.php - Cookie Policy Page
session_start();

$pageTitle = "Cookie Policy - WallHub";
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
        
        .cookies-container {
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
        
        /* Cookie Table */
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .cookie-table th,
        .cookie-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cookie-table th {
            background: rgba(225, 29, 29, 0.2);
            color: #fff;
            font-weight: 600;
        }
        
        .cookie-table td {
            color: #ccc;
        }
        
        .cookie-table tr:hover td {
            background: rgba(225, 29, 29, 0.05);
        }
        
        /* Consent Banner (if not already accepted) */
        .consent-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(10, 10, 20, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(225, 29, 29, 0.3);
            padding: 20px;
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .consent-banner.show {
            transform: translateY(0);
        }
        
        .consent-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .consent-text {
            flex: 1;
            color: #ccc;
        }
        
        .consent-text p {
            margin: 0;
        }
        
        .consent-text a {
            color: #e11d1d;
            text-decoration: none;
        }
        
        .consent-text a:hover {
            text-decoration: underline;
        }
        
        .consent-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn-accept {
            background: linear-gradient(135deg, #e11d1d, #8b0000);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(225, 29, 29, 0.4);
        }
        
        .btn-settings {
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-settings:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #e11d1d;
        }
        
        /* Cookie Settings Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1001;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: rgba(20, 20, 30, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(225, 29, 29, 0.3);
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            color: #fff;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: #aaa;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .modal-close:hover {
            color: #e11d1d;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .cookie-option {
            margin-bottom: 20px;
        }
        
        .cookie-option label {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            cursor: pointer;
        }
        
        .cookie-option input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .cookie-option p {
            color: #aaa;
            font-size: 0.85rem;
            margin: 5px 0 0 30px;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .cookies-container {
                padding: 15px;
            }
            .page-header h1 {
                font-size: 1.8rem;
            }
            .cookie-table {
                font-size: 0.8rem;
            }
            .cookie-table th,
            .cookie-table td {
                padding: 8px 10px;
            }
            .consent-content {
                flex-direction: column;
                text-align: center;
            }
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
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="cookies-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-cookie-bite"></i> Cookie Policy
            </h1>
            <p>Learn about how WallHub uses cookies to enhance your browsing experience</p>
            <div class="last-updated">
                <i class="fas fa-calendar-alt"></i> Last Updated: June 6, 2026
            </div>
        </div>
        
        <!-- What Are Cookies -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-question-circle"></i> What Are Cookies?</h2>
            </div>
            <div class="card-body">
                <p>Cookies are small text files that are stored on your device (computer, tablet, or mobile) when you visit websites. They help websites remember information about your visit, making your experience more personalized and efficient.</p>
                <p>Cookies are widely used to make websites work properly, improve performance, and provide useful features to visitors. They cannot harm your device or access personal information stored on it.</p>
            </div>
        </div>
        
        <!-- How We Use Cookies -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> How WallHub Uses Cookies</h2>
            </div>
            <div class="card-body">
                <p>WallHub uses cookies for the following purposes:</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required for the website to function properly (login sessions, shopping cart, security).</li>
                    <li><strong>Preference Cookies:</strong> Remember your preferences like theme, language, and layout settings.</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our website to improve performance.</li>
                    <li><strong>Functional Cookies:</strong> Enable enhanced features like remembering your favorites and download history.</li>
                </ul>
            </div>
        </div>
        
        <!-- Types of Cookies -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Types of Cookies We Use</h2>
            </div>
            <div class="card-body">
                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Cookie Name</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>PHPSESSID</code></td>
                            <td>Maintains user session and login state</td>
                            <td>Session</td>
                        </tr>
                        <tr>
                            <td><code>user_preferences</code></td>
                            <td>Stores user display preferences</td>
                            <td>1 year</td>
                        </tr>
                        <tr>
                            <td><code>favorites</code></td>
                            <td>Remembers favorited wallpapers for non-logged-in users</td>
                            <td>30 days</td>
                        </tr>
                        <tr>
                            <td><code>cookie_consent</code></td>
                            <td>Records your cookie consent preference</td>
                            <td>1 year</td>
                        </tr>
                        <tr>
                            <td><code>analytics_id</code></td>
                            <td>Tracks anonymous website usage statistics</td>
                            <td>2 years</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Third-Party Cookies -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-share-alt"></i> Third-Party Cookies</h2>
            </div>
            <div class="card-body">
                <p>We may also use third-party services that set their own cookies:</p>
                <ul>
                    <li><strong>Google Analytics:</strong> Helps us analyze website traffic and user behavior anonymously.</li>
                    <li><strong>Social Media Plugins:</strong> If you share content on social media platforms, those platforms may set cookies.</li>
                    <li><strong>Payment Processors:</strong> When making premium payments, payment providers may set cookies for security.</li>
                </ul>
                <p>We do not control these third-party cookies. Please review the respective privacy policies for more information.</p>
            </div>
        </div>
        
        <!-- Managing Cookies -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-sliders-h"></i> Managing Your Cookie Preferences</h2>
            </div>
            <div class="card-body">
                <p>You can control and manage cookies in several ways:</p>
                <h3><i class="fas fa-browser"></i> Browser Settings</h3>
                <p>Most browsers allow you to:</p>
                <ul>
                    <li>View what cookies are stored on your device</li>
                    <li>Delete all cookies</li>
                    <li>Block cookies from specific websites</li>
                    <li>Block all third-party cookies</li>
                    <li>Set your browser to notify you when a cookie is set</li>
                </ul>
                <p>Visit your browser's help section to learn how to manage cookies:</p>
                <ul>
                    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" style="color:#e11d1d;">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/en-US/kb/cookies-information-websites-store-on-your-computer" target="_blank" style="color:#e11d1d;">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471/mac" target="_blank" style="color:#e11d1d;">Safari</a></li>
                    <li><a href="https://support.microsoft.com/en-us/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" style="color:#e11d1d;">Microsoft Edge</a></li>
                </ul>
                
                <h3><i class="fas fa-cookie"></i> Cookie Consent</h3>
                <p>When you first visit WallHub, you'll see a cookie consent banner. You can:</p>
                <ul>
                    <li><strong>Accept All:</strong> Allow all cookies for the best experience</li>
                    <li><strong>Customize:</strong> Choose which types of cookies to accept</li>
                    <li><strong>Reject Non-Essential:</strong> Only accept essential cookies</li>
                </ul>
            </div>
        </div>
        
        <!-- Your Rights -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-gavel"></i> Your Rights</h2>
            </div>
            <div class="card-body">
                <p>Under applicable privacy laws (including GDPR and India's IT Act), you have the right to:</p>
                <ul>
                    <li>Know what cookies are being used on our website</li>
                    <li>Withdraw consent at any time</li>
                    <li>Delete cookies stored on your device</li>
                    <li>Opt-out of non-essential cookies</li>
                </ul>
                <p>To exercise these rights, you can adjust your browser settings or contact us at <strong>support@wallhub.online</strong>.</p>
            </div>
        </div>
        
        <!-- Updates to This Policy -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-sync-alt"></i> Updates to This Policy</h2>
            </div>
            <div class="card-body">
                <p>We may update this Cookie Policy from time to time to reflect changes in technology, regulations, or our practices. When we make changes, we will update the "Last Updated" date at the top of this page.</p>
                <p>We encourage you to review this policy periodically to stay informed about how we use cookies. Your continued use of WallHub after any changes constitutes acceptance of the updated policy.</p>
            </div>
        </div>
        
        <!-- Contact Us -->
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-envelope"></i> Contact Us</h2>
            </div>
            <div class="card-body">
                <p>If you have any questions about our use of cookies or this Cookie Policy, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> support@wallhub.online</li>
                    <li><strong>Via Contact Form:</strong> <a href="contact.php" style="color:#e11d1d;">Contact Page</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Cookie Consent Banner (if not accepted) -->
    <?php if (!isset($_COOKIE['cookie_consent']) || $_COOKIE['cookie_consent'] !== 'accepted'): ?>
    <div class="consent-banner" id="consentBanner">
        <div class="consent-content">
            <div class="consent-text">
                <p><i class="fas fa-cookie-bite" style="color: #e11d1d;"></i> <strong>We value your privacy.</strong> We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies. <a href="cookies.php">Learn more</a></p>
            </div>
            <div class="consent-buttons">
                <button class="btn-settings" id="customizeBtn">Customize</button>
                <button class="btn-accept" id="acceptBtn">Accept All</button>
            </div>
        </div>
    </div>
    
    <!-- Cookie Settings Modal -->
    <div class="modal" id="cookieModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-sliders-h"></i> Cookie Preferences</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="essentialCookies" checked disabled>
                        <strong>Essential Cookies</strong>
                    </label>
                    <p>Required for the website to function. Cannot be disabled.</p>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="preferenceCookies">
                        <strong>Preference Cookies</strong>
                    </label>
                    <p>Remember your settings and preferences.</p>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="analyticsCookies">
                        <strong>Analytics Cookies</strong>
                    </label>
                    <p>Help us improve our website by analyzing usage.</p>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="functionalCookies">
                        <strong>Functional Cookies</strong>
                    </label>
                    <p>Enable features like favorites and download history.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-settings" id="savePreferences">Save Preferences</button>
                <button class="btn-accept" id="acceptAllBtn">Accept All</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Back to Top Button -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script>
        // Cookie consent management
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
        }
        
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        
        function acceptAllCookies() {
            setCookie('cookie_consent', 'accepted', 365);
            setCookie('preference_cookies', 'enabled', 365);
            setCookie('analytics_cookies', 'enabled', 365);
            setCookie('functional_cookies', 'enabled', 365);
            
            // Hide banner
            const banner = document.getElementById('consentBanner');
            if (banner) {
                banner.classList.remove('show');
                setTimeout(() => banner.style.display = 'none', 300);
            }
            
            // Close modal if open
            const modal = document.getElementById('cookieModal');
            if (modal) modal.classList.remove('show');
        }
        
        function savePreferences() {
            const preferenceEnabled = document.getElementById('preferenceCookies')?.checked ? 'enabled' : 'disabled';
            const analyticsEnabled = document.getElementById('analyticsCookies')?.checked ? 'enabled' : 'disabled';
            const functionalEnabled = document.getElementById('functionalCookies')?.checked ? 'enabled' : 'disabled';
            
            setCookie('cookie_consent', 'customized', 365);
            setCookie('preference_cookies', preferenceEnabled, 365);
            setCookie('analytics_cookies', analyticsEnabled, 365);
            setCookie('functional_cookies', functionalEnabled, 365);
            
            // Hide banner
            const banner = document.getElementById('consentBanner');
            if (banner) {
                banner.classList.remove('show');
                setTimeout(() => banner.style.display = 'none', 300);
            }
            
            // Close modal
            const modal = document.getElementById('cookieModal');
            if (modal) modal.classList.remove('show');
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Show consent banner with animation
            const banner = document.getElementById('consentBanner');
            if (banner) {
                setTimeout(() => banner.classList.add('show'), 500);
            }
            
            // Accept All button
            const acceptBtn = document.getElementById('acceptBtn');
            if (acceptBtn) acceptBtn.addEventListener('click', acceptAllCookies);
            
            const acceptAllBtn = document.getElementById('acceptAllBtn');
            if (acceptAllBtn) acceptAllBtn.addEventListener('click', acceptAllCookies);
            
            // Customize button
            const customizeBtn = document.getElementById('customizeBtn');
            const modal = document.getElementById('cookieModal');
            const closeModal = document.getElementById('closeModal');
            
            if (customizeBtn) {
                customizeBtn.addEventListener('click', function() {
                    if (modal) modal.classList.add('show');
                });
            }
            
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    if (modal) modal.classList.remove('show');
                });
            }
            
            // Save Preferences button
            const savePrefs = document.getElementById('savePreferences');
            if (savePrefs) savePrefs.addEventListener('click', savePreferences);
            
            // Close modal when clicking outside
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) modal.classList.remove('show');
                });
            }
            
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
        });
    </script>
</body>
</html>