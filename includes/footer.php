<?php
// includes/footer.php
require_once __DIR__ . '/functions.php';
?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <!-- About Info -->
                <div class="footer-about">
                    <a href="index.php" class="logo" style="margin-bottom: 15px;">
                        <img src="assets/images/logo.png" alt="Wolf Nutrition Logo">
                    </a>
                    <p>Ancient Ayurvedic wisdom engineered for modern male peak performance. Clean, raw formulations with zero hidden fillers.</p>
                </div>

                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="category.php?slug=vitality">Supplements</a></li>
                        <li><a href="category.php?slug=liver-detox">Liver & Detox</a></li>
                        <li><a href="about.php">Our Brand Story</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="certificates.php">Quality Certificates</a></li>
                    </ul>
                </div>

                <!-- Policy Links -->
                <div class="footer-links">
                    <h4>Our Policies</h4>
                    <ul>
                        <li><a href="page.php?slug=shipping-policy">Shipping & Delivery</a></li>
                        <li><a href="page.php?slug=refund-policy">Refund & Return Policy</a></li>
                        <li><a href="page.php?slug=privacy-policy">Privacy Policy</a></li>
                        <li><a href="page.php?slug=terms-of-service">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Newsletter Signup -->
                <div class="footer-newsletter">
                    <h4>Join the Pack</h4>
                    <p>Subscribe to receive exclusive discounts, stack guides, and early access to new releases.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Welcome to the pack! You have subscribed successfully.'); this.reset();">
                        <input type="email" placeholder="Your Email Address" required>
                        <button type="submit" class="btn-gold" style="padding:10px 15px;"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer-bottom">
                <div>
                    &copy; <?php echo date('Y'); ?> Wolf Nutrition. All Rights Reserved. 
                    <span style="margin-left:15px; color: var(--gold-muted);">FSSAI Reg No: 22126022000063</span>
                </div>
                <!-- Social Icons -->
                <div class="footer-socials">
                    <a href="https://instagram.com" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                    <a href="https://facebook.com" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main Script JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
