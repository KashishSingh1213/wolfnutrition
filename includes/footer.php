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
                    <?php if (is_logged_in()): ?>
                        <?php
                            $footer_user = get_logged_in_user();
                            $footer_email = $footer_user ? htmlspecialchars($footer_user['email']) : '';
                        ?>
                        <form class="newsletter-form" id="footer-nl-form" onsubmit="return handleFooterNewsletterSubmit(event);" style="display:flex; gap:8px;">
                            <input type="email" id="footer-nl-email" value="<?php echo $footer_email; ?>" readonly required placeholder="Your Email Address" style="flex:1;">
                            <button type="submit" id="footer-nl-btn" class="btn-gold" style="padding:10px 15px;"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        <div id="footer-nl-msg" style="margin-top:8px; font-size:0.75rem; display:none;"></div>
                    <?php else: ?>
                        <a href="login.php" style="display:inline-flex; align-items:center; gap:6px; color:var(--gold-primary); font-size:0.85rem; font-weight:600; text-decoration:none; margin-top:8px;">
                            <i class="fas fa-sign-in-alt"></i> Login to Subscribe
                        </a>
                    <?php endif; ?>
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
    <script>
    function handleFooterNewsletterSubmit(e) {
        e.preventDefault();
        var email = document.getElementById('footer-nl-email').value.trim();
        var btn = document.getElementById('footer-nl-btn');
        var msg = document.getElementById('footer-nl-msg');
        if (!email) return false;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        var fd = new FormData();
        fd.append('email', email);
        fetch('newsletter_subscribe.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                msg.style.display = 'block';
                msg.style.color = data.success ? '#4ade80' : '#ef4444';
                msg.textContent = data.message;
                if (data.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.style.background = 'linear-gradient(135deg, #4ade80, #22c55e)';
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                }
                setTimeout(function() { msg.style.display = 'none'; }, 4000);
            })
            .catch(function() {
                msg.style.display = 'block';
                msg.style.color = '#ef4444';
                msg.textContent = 'Something went wrong.';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        return false;
    }
    </script>
</body>
</html>
