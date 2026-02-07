<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="description" content="Choose your VulnForge Academy subscription plan. From free beginner training to enterprise-level workshops.">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="profile.php">Profile</a>
                <a href="pricing.php">Pricing</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="pricing.php">Pricing</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <section class="pricing-hero" style="text-align: center; padding: 60px 0;">
            <h1 style="font-size: 3rem; margin-bottom: 20px;">Choose Your Learning Path</h1>
            <p style="font-size: 1.3rem; color: var(--text-secondary); margin-bottom: 40px;">
                From free starter challenges to enterprise-level training programs
            </p>
            
            <!-- Billing Toggle -->
            <div class="billing-toggle" style="display: flex; justify-content: center; gap: 20px; margin-bottom: 40px;">
                <span style="color: var(--text-primary);">Monthly</span>
                <label class="switch">
                    <input type="checkbox" id="billingToggle">
                    <span class="slider"></span>
                </label>
                <span style="color: var(--text-primary);">Annual <span style="color: var(--accent-green); font-weight: bold;">Save 20%</span></span>
            </div>
        </section>

        <section class="pricing-plans">
            <div class="pricing-grid">
                <!-- Free Plan -->
                <div class="pricing-card">
                    <div class="plan-header">
                        <h3>Free</h3>
                        <div class="price">
                            <span class="monthly-price">$0</span>
                            <span class="annual-price" style="display: none;">$0</span>
                            <span>/month</span>
                        </div>
                        <p style="color: var(--text-secondary);">Perfect for beginners</p>
                    </div>
                    <ul class="features-list">
                        <li>✓ First 10 vulnerability levels</li>
                        <li>✓ Basic progress tracking</li>
                        <li>✓ Community forum access</li>
                        <li>✓ Basic achievement system</li>
                        <li>✓ Mobile app access</li>
                        <li>✗ Advanced levels (11-20)</li>
                        <li>✗ Premium challenges</li>
                        <li>✗ Email support</li>
                        <li>✗ Certificates</li>
                        <li>✗ Live workshops</li>
                    </ul>
                    <div class="plan-cta">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" class="btn btn-outline">Start Free</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-outline">Continue Free</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="pricing-card featured">
                    <div class="plan-badge">Most Popular</div>
                    <div class="plan-header">
                        <h3>Pro</h3>
                        <div class="price">
                            <span class="monthly-price">$19.99</span>
                            <span class="annual-price" style="display: none;">$15.99</span>
                            <span>/month</span>
                        </div>
                        <p style="color: var(--text-secondary);">For serious learners</p>
                    </div>
                    <ul class="features-list">
                        <li>✓ All 20 vulnerability levels</li>
                        <li>✓ All difficulty categories</li>
                        <li>✓ Premium exclusive challenges</li>
                        <li>✓ Advanced analytics & reporting</li>
                        <li>✓ Email support (24h response)</li>
                        <li>✓ Course completion certificate</li>
                        <li>✓ Downloadable resources</li>
                        <li>✓ Priority bug reports</li>
                        <li>✓ Ad-free experience</li>
                        <li>✓ API access</li>
                    </ul>
                    <div class="plan-cta">
                        <a href="subscribe.php?tier=pro" class="btn btn-primary">Start Pro Trial</a>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 10px;">7-day free trial, cancel anytime</p>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="pricing-card">
                    <div class="plan-header">
                        <h3>Enterprise</h3>
                        <div class="price">
                            <span class="monthly-price">$99.99</span>
                            <span class="annual-price" style="display: none;">$79.99</span>
                            <span>/month</span>
                        </div>
                        <p style="color: var(--text-secondary);">For teams & organizations</p>
                    </div>
                    <ul class="features-list">
                        <li>✓ Everything in Pro</li>
                        <li>✓ Team management dashboard</li>
                        <li>✓ Custom challenge creation</li>
                        <li>✓ Live virtual workshops</li>
                        <li>✓ Dedicated success manager</li>
                        <li>✓ SSO integration</li>
                        <li>✓ Advanced reporting & analytics</li>
                        <li>✓ Custom branding options</li>
                        <li>✓ Phone support</li>
                        <li>✓ SLA guarantees</li>
                    </ul>
                    <div class="plan-cta">
                        <a href="contact.php?type=enterprise" class="btn btn-secondary">Contact Sales</a>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 10px;">Volume discounts available</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Comparison Table -->
        <section class="feature-comparison" style="margin: 80px 0;">
            <h2 style="text-align: center; margin-bottom: 40px;">Detailed Feature Comparison</h2>
            <div class="comparison-table" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 10px; overflow: hidden;">
                    <thead>
                        <tr style="background: var(--bg-input);">
                            <th style="padding: 20px; text-align: left; border-bottom: 1px solid var(--border-color);">Feature</th>
                            <th style="padding: 20px; text-align: center; border-bottom: 1px solid var(--border-color);">Free</th>
                            <th style="padding: 20px; text-align: center; border-bottom: 1px solid var(--border-color); background: rgba(0,153,255,0.1);">Pro</th>
                            <th style="padding: 20px; text-align: center; border-bottom: 1px solid var(--border-color);">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid var(--border-color);">Available Levels</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">1-10</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color); background: rgba(0,153,255,0.05);">1-20</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">1-20</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid var(--border-color);">Progress Tracking</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">Basic</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color); background: rgba(0,153,255,0.05);">Advanced</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">Team Analytics</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid var(--border-color);">Support</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">Community</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color); background: rgba(0,153,255,0.05);">Email (24h)</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">Phone + Dedicated</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid var(--border-color);">Certificates</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">❌</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color); background: rgba(0,153,255,0.05);">✓</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">✓ Custom</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid var(--border-color);">Live Workshops</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">❌</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color); background: rgba(0,153,255,0.05);">Monthly</td>
                            <td style="padding: 15px; text-align: center; border-bottom: 1px solid var(--border-color);">Unlimited</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px;">API Access</td>
                            <td style="padding: 15px; text-align: center;">❌</td>
                            <td style="padding: 15px; text-align: center; background: rgba(0,153,255,0.05);">✓</td>
                            <td style="padding: 15px; text-align: center;">✓ Advanced</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq" style="margin: 80px 0;">
            <h2 style="text-align: center; margin-bottom: 40px;">Frequently Asked Questions</h2>
            <div class="faq-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
                <div class="faq-item">
                    <h4>Can I switch plans anytime?</h4>
                    <p>Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we'll prorate any billing differences.</p>
                </div>
                <div class="faq-item">
                    <h4>What payment methods do you accept?</h4>
                    <p>We accept all major credit cards, PayPal, and bank transfers for enterprise accounts. All payments are processed securely through Stripe.</p>
                </div>
                <div class="faq-item">
                    <h4>Is there a free trial for Pro?</h4>
                    <p>Yes! We offer a 7-day free trial for Pro plans. No credit card required to start, and you can cancel anytime during the trial.</p>
                </div>
                <div class="faq-item">
                    <h4>Do you offer refunds?</h4>
                    <p>We offer a 30-day money-back guarantee for all paid plans. If you're not satisfied, contact us for a full refund.</p>
                </div>
                <div class="faq-item">
                    <h4>Can I use this for corporate training?</h4>
                    <p>Absolutely! Our Enterprise plan includes team management, custom challenges, and dedicated support perfect for corporate security training.</p>
                </div>
                <div class="faq-item">
                    <h4>Are the challenges updated regularly?</h4>
                    <p>Yes! We add new challenges monthly and update existing ones to reflect the latest vulnerability trends and attack techniques.</p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="pricing-cta" style="text-align: center; padding: 60px 0; background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-input) 100%); border-radius: 20px; margin: 40px 0;">
            <h2 style="margin-bottom: 20px;">Ready to Start Your Cybersecurity Journey?</h2>
            <p style="font-size: 1.2rem; color: var(--text-secondary); margin-bottom: 30px;">
                Join thousands of security professionals who've advanced their careers with VulnForge Academy
            </p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="register.php" class="btn btn-primary">Start Free Today</a>
                <a href="contact.php" class="btn btn-secondary">Talk to Sales</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>VulnForge Academy</h4>
                <p>Master ethical hacking through hands-on challenges</p>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="support.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 VulnForge Academy - For Educational Purposes Only</p>
        </div>
    </footer>

    <script>
        // Billing toggle functionality
        const billingToggle = document.getElementById('billingToggle');
        const monthlyPrices = document.querySelectorAll('.monthly-price');
        const annualPrices = document.querySelectorAll('.annual-price');

        billingToggle.addEventListener('change', function() {
            if (this.checked) {
                // Show annual prices
                monthlyPrices.forEach(price => price.style.display = 'none');
                annualPrices.forEach(price => price.style.display = 'inline');
            } else {
                // Show monthly prices
                monthlyPrices.forEach(price => price.style.display = 'inline');
                annualPrices.forEach(price => price.style.display = 'none');
            }
        });

        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>