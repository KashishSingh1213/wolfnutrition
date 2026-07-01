<?php
// contact.php
require_once __DIR__ . '/includes/header.php';
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>Contact Us</h2>
            <p>Get in touch with the Pack. We're here to help.</p>
        </div>

        <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:40px; align-items:start;">
            
            <!-- Contact Form -->
            <div class="glass-card" style="padding: 30px; border-radius: 8px;">
                <h3 style="font-size:1.3rem; text-transform:uppercase; margin-bottom:20px; color:var(--gold-primary);">Send Us a Message</h3>
                
                <form onsubmit="event.preventDefault(); alert('Message sent successfully. Our support team will get back to you shortly.'); this.reset();">
                    <div class="form-group">
                        <label for="contact_name">Full Name *</label>
                        <input type="text" id="contact_name" class="form-control" placeholder="e.g. Yuvek Verma" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_email">Email Address *</label>
                            <input type="email" id="contact_email" class="form-control" placeholder="e.g. yuvek@gmail.com" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_phone">Phone Number</label>
                            <input type="text" id="contact_phone" class="form-control" placeholder="10-digit number" maxlength="10">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_subject">Subject</label>
                        <input type="text" id="contact_subject" class="form-control" placeholder="e.g. Order Delivery Status">
                    </div>

                    <div class="form-group">
                        <label for="contact_message">Your Message *</label>
                        <textarea id="contact_message" rows="5" class="form-control" placeholder="Type your query in detail..." required></textarea>
                    </div>

                    <button type="submit" class="btn-gold" style="padding:12px 30px;">Send Message</button>
                </form>
            </div>

            <!-- Contact Details / Info -->
            <div style="display:flex; flex-direction:column; gap:25px;">
                <div class="glass-card" style="padding: 25px; border-radius: 8px;">
                    <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; color:#fff;">Support Coordinates</h3>
                    <div style="display:flex; flex-direction:column; gap:12px; font-size:0.95rem;">
                        <div>
                            <i class="fas fa-envelope" style="color:var(--gold-primary); margin-right:10px;"></i>
                            <span>Email: <strong style="color:#fff;">support@wolfnutrition.in</strong></span>
                        </div>
                        <div>
                            <i class="fas fa-phone-alt" style="color:var(--gold-primary); margin-right:10px;"></i>
                            <span>Helpline: <strong style="color:#fff;">+91 98765 43210</strong></span>
                        </div>
                        <div>
                            <i class="fas fa-map-marker-alt" style="color:var(--gold-primary); margin-right:10px;"></i>
                            <span>Address: <strong style="color:#fff;">Kaki Pind, Hoshiarpur Road, Rama Mandi, Jalandhar, Punjab - 144005</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Business hours -->
                <div class="glass-card" style="padding: 25px; border-radius: 8px;">
                    <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; color:#fff;">Operational Hours</h3>
                    <p style="font-size:0.95rem;">Monday to Saturday: 10:00 AM to 6:00 PM IST<br>Sunday: Closed</p>
                </div>

                <!-- FSSAI Details -->
                <div class="glass-card" style="padding: 25px; border-radius: 8px;">
                    <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; color:#fff;">Wholesaler Credentials</h3>
                    <p style="font-size:0.95rem;">Licensed Wholesaler in Jalandhar District, Punjab.<br><strong>FSSAI License No: 22126022000063</strong></p>
                </div>
            </div>

        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
