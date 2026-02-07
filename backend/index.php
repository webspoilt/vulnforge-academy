<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VulnForge Academy - Learn Ethical Hacking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="description" content="Master bug bounty hunting with VulnForge Academy. 20 levels from beginner to expert. Free training with premium options.">
    <meta name="keywords" content="ethical hacking, bug bounty, cybersecurity, penetration testing, SQL injection, XSS">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php">VulnForge Academy</a>
        </div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="profile.php">Profile</a>
                <a href="pricing.php">Pricing</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="pricing.php">Pricing</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <!-- Hero Section -->
        <section class="hero">
            <h1>VulnForge Academy</h1>
            <p class="subtitle">Master Bug Bounty Hunting - From Beginner to Expert</p>

            <!-- Monetization: Donation Buttons -->
            <div class="support-section" style="background: linear-gradient(135deg, #1a1a25 0%, #0f0f15 100%); padding: 20px; border-radius: 15px; margin: 30px 0; text-align: center;">
                <h3 style="color: var(--accent-green); margin-bottom: 15px;">Support Our Mission</h3>
                <p style="margin-bottom: 20px;">Help us keep this platform free and add more challenges!</p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo GITHUB_SPONSORS_URL; ?>" target="_blank" class="btn btn-sponsor" style="background: #333; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub Sponsors
                    </a>
                    <a href="<?php echo BUY_ME_COFFEE_URL; ?>" target="_blank" class="btn btn-coffee" style="background: #FF813F; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.211.375-.445.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                        Buy Me a Coffee
                    </a>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="features-grid">
                <div class="feature-card">
                    <h3 style="color: #00cc66;">20 Levels</h3>
                    <p>From Easy to Nightmare</p>
                </div>
                <div class="feature-card">
                    <h3 style="color: #ff9900;">Progress Saving</h3>
                    <p>Continue anytime</p>
                </div>
                <div class="feature-card">
                    <h3 style="color: #9933ff;">Leaderboard</h3>
                    <p>Compete globally</p>
                </div>
                <div class="feature-card">
                    <h3 style="color: #00cc66;">Free to Start</h3>
                    <p>Upgrade anytime</p>
                </div>
            </div>

            <!-- Difficulty Levels Preview -->
            <div class="levels-preview">
                <h2>Difficulty Levels</h2>
                <div class="difficulty-badges">
                    <span class="difficulty easy">EASY (1-5)</span>
                    <span class="difficulty moderate">MODERATE (6-10)</span>
                    <span class="difficulty hard">HARD (11-15)</span>
                    <span class="difficulty expert">EXPERT (16-18)</span>
                    <span class="difficulty nightmare">NIGHTMARE (19-20)</span>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="cta-buttons">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-primary">Start Hacking - It's Free!</a>
                    <a href="pricing.php" class="btn btn-secondary">View Premium Plans</a>
                    <p style="margin-top: 15px; color: var(--text-secondary);">
                        Already have an account? <a href="login.php">Login</a>
                    </p>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Continue Training</a>
                    <a href="pricing.php" class="btn btn-secondary">Upgrade Plan</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Learning Path Section -->
        <section class="learning-path">
            <h2>What You'll Learn</h2>
            <div class="learning-grid">
                <div class="learning-card">
                    <h4 style="color: var(--accent-red);">SQL Injection</h4>
                    <p>Basic to blind, WAF bypass techniques</p>
                    <div class="course-affiliate">
                        <small>Related Course: <a href="<?php echo AFFILIATE_BASE_URL; ?>/sql-injection-mastery" target="_blank">SQL Injection Mastery</a></small>
                    </div>
                </div>
                <div class="learning-card">
                    <h4 style="color: var(--accent-orange);">XSS Attacks</h4>
                    <p>Reflected, stored, DOM-based, filter evasion</p>
                    <div class="course-affiliate">
                        <small>Related Course: <a href="<?php echo AFFILIATE_BASE_URL; ?>/xss-exploitation" target="_blank">XSS Exploitation Course</a></small>
                    </div>
                </div>
                <div class="learning-card">
                    <h4 style="color: var(--accent-blue);">IDOR & Access Control</h4>
                    <p>Privilege escalation, authorization bypass</p>
                    <div class="course-affiliate">
                        <small>Related Course: <a href="<?php echo AFFILIATE_BASE_URL; ?>/access-control-testing" target="_blank">Access Control Testing</a></small>
                    </div>
                </div>
                <div class="learning-card">
                    <h4 style="color: var(--accent-purple);">File Vulnerabilities</h4>
                    <p>Upload bypass, LFI, path traversal</p>
                    <div class="course-affiliate">
                        <small>Related Course: <a href="<?php echo AFFILIATE_BASE_URL; ?>/file-inclusion-attacks" target="_blank">File Inclusion Attacks</a></small>
                    </div>
                </div>
                <div class="learning-card">
                    <h4 style="color: var(--accent-green);">Advanced Attacks</h4>
                    <p>SSRF, XXE, deserialization, race conditions</p>
                    <div class="course-affiliate">
                        <small>Related Course: <a href="<?php echo AFFILIATE_BASE_URL; ?>/advanced-web-exploitation" target="_blank">Advanced Web Exploitation</a></small>
                    </div>
                </div>
                <div class="learning-card">
                    <h4 style="color: #ff6699;">Real-World Skills</h4>
                    <p>Bug bounty techniques from HackerOne</p>
                    <div class="course-affiliate">
                        <small>Related Course: <a href="<?php echo AFFILIATE_BASE_URL; ?>/bug-bounty-mastery" target="_blank">Bug Bounty Mastery</a></small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Preview -->
        <section class="pricing-preview">
            <h2>Choose Your Learning Path</h2>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <h3>Free</h3>
                    <div class="price">$0<span>/month</span></div>
                    <ul>
                        <li>✓ First 10 levels</li>
                        <li>✓ Community support</li>
                        <li>✓ Basic progress tracking</li>
                        <li>✗ Advanced levels</li>
                        <li>✗ Premium content</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline">Start Free</a>
                </div>
                <div class="pricing-card featured">
                    <h3>Pro</h3>
                    <div class="price">$19.99<span>/month</span></div>
                    <ul>
                        <li>✓ All 20 levels</li>
                        <li>✓ Premium challenges</li>
                        <li>✓ Email support</li>
                        <li>✓ Detailed analytics</li>
                        <li>✓ Certificate upon completion</li>
                    </ul>
                    <a href="pricing.php" class="btn btn-primary">Start Pro Trial</a>
                </div>
                <div class="pricing-card">
                    <h3>Enterprise</h3>
                    <div class="price">$99.99<span>/month</span></div>
                    <ul>
                        <li>✓ Everything in Pro</li>
                        <li>✓ Live workshops</li>
                        <li>✓ Corporate training</li>
                        <li>✓ Custom challenges</li>
                        <li>✓ Priority support</li>
                    </ul>
                    <a href="pricing.php" class="btn btn-outline">Contact Sales</a>
                </div>
            </div>
        </section>

        <!-- Success Stories -->
        <section class="testimonials">
            <h2>Success Stories</h2>
            <div class="testimonial-grid">
                <div class="testimonial">
                    <p>"VulnForge helped me land my first bug bounty on HackerOne. The practical challenges are spot on!"</p>
                    <cite>- Sarah K., Bug Bounty Hunter</cite>
                </div>
                <div class="testimonial">
                    <p>"The progression from easy to nightmare levels is perfect for learning."</p>
                    <cite>- Mike R., Security Engineer</cite>
                </div>
                <div class="testimonial">
                    <p>"Finally a platform that teaches real-world vulnerabilities without the theoretical fluff."</p>
                    <cite>- Alex T., Penetration Tester</cite>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>VulnForge Academy</h4>
                <p>Master ethical hacking through hands-on challenges</p>
                <div class="social-links">
                    <a href="#" aria-label="GitHub">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="pricing.php">Pricing</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <li><a href="certificates.php">Certificates</a></li>
                    <li><a href="workshops.php">Workshops</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="support.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Stay Updated</h4>
                <p>Subscribe to our newsletter for new challenges and tips</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 VulnForge Academy - For Educational Purposes Only</p>
            <p>Made with ❤️ for the cybersecurity community</p>
        </div>
    </footer>
</body>
</html>