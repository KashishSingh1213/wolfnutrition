<?php
// contact.php — Secure Contact Form
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';  // loads .env via db.php -> env.php
require_once __DIR__ . '/config/email.php';

// ── Session & CSRF ──
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['contact_csrf'];

// ── Rate Limiting (5 messages per IP per hour) ──
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'];
}

function is_rate_limited($pdo, $ip) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$ip]);
    return $stmt->fetchColumn() >= 5;
}

function log_contact_attempt($pdo, $ip) {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (ip_address, created_at) VALUES (?, NOW())");
    $stmt->execute([$ip]);
}

// ── Create table if not exists ──
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(15) DEFAULT NULL,
        subject VARCHAR(100) DEFAULT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        is_spam TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, created_at)
    ) ENGINE=InnoDB");
} catch (PDOException $e) { /* table exists */ }

// ── Server-Side Validation & Processing ──
$success = '';
$error = '';
$ip = get_client_ip();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {

    // 1. CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = "Invalid form submission. Please try again.";
    }
    // 2. Honeypot Check (bot trap)
    elseif (!empty($_POST['website_url'])) {
        $error = "Spam detected.";
    }
    // 3. Rate Limit Check
    elseif (is_rate_limited($pdo, $ip)) {
        $error = "Too many messages. Please wait an hour before sending again.";
    }
    else {
        // 4. Sanitize & Validate Inputs
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Name: 2-100 chars, letters spaces hyphens apostrophes only
        if (empty($name) || !preg_match("/^[a-zA-Z\s\-\']{2,100}$/", $name)) {
            $error = "Name must be 2-100 characters (letters, spaces, hyphens only).";
        }
        // Email: valid format + DNS check
        elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        }
        elseif (strlen($email) > 255) {
            $error = "Email address is too long.";
        }
        // Phone: optional but if provided, must be valid Indian 10-digit
        elseif (!empty($phone) && !preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            $error = "Phone number must be a valid 10-digit Indian number starting with 6-9.";
        }
        // Subject: max 100 chars
        elseif (strlen($subject) > 100) {
            $error = "Subject is too long (max 100 characters).";
        }
        // Message: 10-2000 chars
        elseif (empty($message) || strlen($message) < 10) {
            $error = "Message must be at least 10 characters.";
        }
        elseif (strlen($message) > 2000) {
            $error = "Message is too long (max 2000 characters).";
        }
        // Spam keyword check
        elseif (preg_match('/(viagra|casino|crypto|loan|winner|congratulations|click here|buy now|free money)/i', $message)) {
            $error = "Your message was flagged as potential spam.";
        }
        // Too many links (spam indicator)
        elseif (preg_match_all('/https?:\/\//', $message) > 3) {
            $error = "Too many links in message. Please reduce.";
        }
        else {
            // All checks passed — save to database
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $ip]);
            log_contact_attempt($pdo, $ip);

            // Send confirmation email to user
            $userHtml = build_contact_confirmation_html($name);
            $userResult = send_brevo_email($email, $name, 'We received your message — Wolf Nutrition', $userHtml);

            // Send notification email to support
            $adminHtml = build_admin_notification_html($name, $email, $phone, $subject, $message);
            $adminResult = send_brevo_email(SUPPORT_EMAIL, 'Wolf Nutrition Support', 'New Contact: ' . ($subject ?: 'No Subject') . ' — ' . $name, $adminHtml);

            // Regenerate CSRF token
            $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
            $csrf_token = $_SESSION['contact_csrf'];

            $success = "Message sent successfully! A confirmation email has been sent to your inbox. Our team will respond within 24 hours.";
        }
    }
}

// Regenerate CSRF token if not set
if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['contact_csrf'];
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<style>
/* ── Gold Particles ── */
#goldParticles{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.3;}

/* ── Contact Hero ── */
.contact-hero{position:relative;padding:60px 50px;border-radius:24px;margin-top:20px;margin-bottom:50px;overflow:hidden;border:1px solid rgba(212,175,55,0.12);box-shadow:0 25px 60px rgba(8,12,16,0.6);background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,#080C10 50%,rgba(212,175,55,0.05) 100%);display:flex;align-items:center;justify-content:space-between;gap:40px;min-height:240px;}
.contact-hero::before{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;background:radial-gradient(circle,rgba(212,175,55,0.1) 0%,transparent 70%);pointer-events:none;}
.contact-hero-badge{display:inline-block;font-size:0.7rem;font-weight:800;letter-spacing:2px;background:var(--gold-gradient);color:#080C10;padding:5px 16px;border-radius:20px;text-transform:uppercase;margin-bottom:16px;}
.contact-hero h1{font-size:clamp(2rem,4vw,3.2rem);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;color:#fff;font-family:var(--font-heading);font-weight:800;line-height:1.1;text-shadow:0 2px 10px rgba(0,0,0,0.5);}
.contact-hero p{font-size:1.05rem;color:rgba(255,255,255,0.8);max-width:500px;line-height:1.7;margin:0;}
.contact-hero-visual{position:relative;width:220px;height:220px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.contact-hero-visual::before{content:'';position:absolute;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,var(--gold-primary) 0%,transparent 70%);opacity:0.12;filter:blur(30px);z-index:1;}
.contact-hero-icon{font-size:5rem;color:var(--gold-primary);opacity:0.15;position:absolute;z-index:1;}
.contact-hero-stats{display:flex;gap:16px;margin-top:24px;}
.contact-hero-stat{text-align:center;padding:14px 18px;background:rgba(255,255,255,0.03);border:1px solid rgba(212,175,55,0.1);border-radius:12px;min-width:80px;}
.contact-hero-stat-num{font-size:1rem;font-weight:800;color:var(--gold-primary);font-family:var(--font-heading);line-height:1;}
.contact-hero-stat-label{font-size:0.6rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-top:4px;font-weight:600;}

/* ── Grid ── */
.contact-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:35px;align-items:start;}

/* ── Form Card ── */
.contact-form-card{background:rgba(15,16,20,0.7);backdrop-filter:blur(16px);border:1px solid rgba(212,175,55,0.1);border-radius:20px;padding:36px 32px;position:relative;overflow:hidden;}
.contact-form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--gold-gradient);}
.contact-form-card h3{font-size:1.3rem;text-transform:uppercase;margin-bottom:24px;color:#fff;font-family:var(--font-heading);font-weight:800;letter-spacing:0.5px;}
.contact-field{margin-bottom:18px;}
.contact-field label{display:block;font-size:0.75rem;font-weight:700;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:7px;}
.contact-field input,.contact-field textarea,.contact-field select{width:100%;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:13px 16px;color:#fff;font-size:0.92rem;outline:none;transition:border-color 0.25s,box-shadow 0.25s;font-family:var(--font-body);resize:vertical;}
.contact-field input:focus,.contact-field textarea:focus,.contact-field select:focus{border-color:rgba(212,175,55,0.4);box-shadow:0 0 0 3px rgba(212,175,55,0.08);}
.contact-field input::placeholder,.contact-field textarea::placeholder{color:rgba(255,255,255,0.3);}
.contact-field textarea{min-height:120px;}
.contact-field-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.char-count{font-size:0.72rem;color:rgba(255,255,255,0.35);text-align:right;margin-top:4px;}

/* ── Info Cards ── */
.contact-info-card{background:rgba(15,16,20,0.7);backdrop-filter:blur(16px);border:1px solid rgba(212,175,55,0.08);border-radius:18px;padding:28px 24px;transition:all 0.3s;position:relative;overflow:hidden;}
.contact-info-card:hover{border-color:rgba(212,175,55,0.2);transform:translateY(-3px);box-shadow:0 12px 30px rgba(8,12,16,0.3);}
.contact-info-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--gold-gradient);opacity:0;transition:opacity 0.3s;}
.contact-info-card:hover::before{opacity:1;}
.contact-info-card h3{font-size:1.05rem;text-transform:uppercase;margin-bottom:16px;color:#fff;font-family:var(--font-heading);font-weight:700;letter-spacing:0.5px;}
.contact-info-row{display:flex;align-items:flex-start;gap:14px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.04);}
.contact-info-row:last-child{border-bottom:none;}
.contact-info-icon{width:40px;height:40px;border-radius:10px;background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.18);display:flex;align-items:center;justify-content:center;color:var(--gold-primary);font-size:1rem;flex-shrink:0;}
.contact-info-text h5{font-size:0.92rem;color:#fff;margin-bottom:3px;font-weight:700;}
.contact-info-text p{font-size:0.82rem;color:rgba(255,255,255,0.6);margin:0;line-height:1.5;}

/* ── Hours ── */
.hours-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);}
.hours-row:last-child{border-bottom:none;}
.hours-day{font-size:0.88rem;color:rgba(255,255,255,0.7);font-weight:600;}
.hours-time{font-size:0.85rem;color:var(--gold-primary);font-weight:700;}
.hours-closed{color:rgba(255,255,255,0.35);}

/* ── Alerts ── */
.alert-success{background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.2);color:var(--gold-primary);padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:0.9rem;font-weight:600;display:flex;align-items:center;gap:10px;animation:slideDown 0.35s ease;}
.alert-error{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:0.9rem;font-weight:600;display:flex;align-items:center;gap:10px;animation:slideDown 0.35s ease;}
@keyframes slideDown{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}

/* ── Tilt & Spotlight ── */
.tilt-card{transform-style:preserve-3d;perspective:1000px;}
.tilt-card .tilt-shine{position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,0.06) 0%,transparent 60%);pointer-events:none;opacity:0;transition:opacity 0.3s;}
.tilt-card:hover .tilt-shine{opacity:1;}
.spotlight-card{position:relative;overflow:hidden;}
.spotlight-card::after{content:'';position:absolute;top:var(--mouse-y,50%);left:var(--mouse-x,50%);width:250px;height:250px;background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%);transform:translate(-50%,-50%);pointer-events:none;opacity:0;transition:opacity 0.4s;z-index:0;}
.spotlight-card:hover::after{opacity:1;}

@media(max-width:1024px){
    .contact-grid{grid-template-columns:1fr;}
    .contact-hero{flex-direction:column;text-align:center;padding:40px 30px;}
    .contact-hero-stats{justify-content:center;}
    .contact-field-row{grid-template-columns:1fr;}
}
</style>

<canvas id="goldParticles"></canvas>

<div class="container" style="margin-top:20px; margin-bottom:60px; position:relative; z-index:2;">

    <!-- ═══ HERO ═══ -->
    <div class="contact-hero">
        <div style="position:relative; z-index:2; flex:1;">
            <span class="contact-hero-badge">Get In Touch</span>
            <h1>Contact Us</h1>
            <p>Got a question about your stack, order, or need a free dietitian consultation? We're here to help.</p>
            <div class="contact-hero-stats">
                <div class="contact-hero-stat"><div class="contact-hero-stat-num"><i class="fas fa-clock" style="font-size:0.9rem;"></i></div><div class="contact-hero-stat-label">24hr Reply</div></div>
                <div class="contact-hero-stat"><div class="contact-hero-stat-num"><i class="fas fa-phone" style="font-size:0.9rem;"></i></div><div class="contact-hero-stat-label">Helpline</div></div>
            </div>
        </div>
        <div class="contact-hero-visual"><i class="fas fa-headset contact-hero-icon"></i></div>
    </div>

    <!-- ═══ GRID ═══ -->
    <div class="contact-grid">

        <!-- Form -->
        <div class="contact-form-card tilt-card spotlight-card">
            <div class="tilt-shine"></div>
            <h3><i class="fas fa-paper-plane" style="color:var(--gold-primary); margin-right:10px;"></i> Send Us a Message</h3>

            <?php if ($success): ?>
                <div class="alert-success"><i class="fas fa-check-circle" style="font-size:1.1rem;"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle" style="font-size:1.1rem;"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="contact.php" method="POST" id="contactForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <!-- Honeypot -->
                <div style="position:absolute; left:-9999px; opacity:0;" aria-hidden="true">
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                <div class="contact-field">
                    <label for="c_name">Full Name *</label>
                    <input type="text" id="c_name" name="name" placeholder="e.g. Yuvek Verma" required minlength="2" maxlength="100" pattern="[a-zA-Z\s\-']{2,100}">
                    <div id="name-err" class="field-error" style="display:none;"></div>
                </div>
                <div class="contact-field-row">
                    <div class="contact-field">
                        <label for="c_email">Email Address *</label>
                        <input type="email" id="c_email" name="email" placeholder="e.g. yuvek@gmail.com" required maxlength="255">
                        <div id="email-err" class="field-error" style="display:none;"></div>
                    </div>
                    <div class="contact-field">
                        <label for="c_phone">Phone Number</label>
                        <input type="tel" id="c_phone" name="phone" placeholder="10-digit number" maxlength="10" pattern="[6-9][0-9]{9}">
                        <div id="phone-err" class="field-error" style="display:none;"></div>
                    </div>
                </div>
                <div class="contact-field">
                    <label for="c_subject">Subject</label>
                    <select id="c_subject" name="subject">
                        <option value="" style="background:#121212;">Select a topic...</option>
                        <option value="Order & Delivery" style="background:#121212;">Order & Delivery</option>
                        <option value="Product Inquiry" style="background:#121212;">Product Inquiry</option>
                        <option value="Return & Refund" style="background:#121212;">Return & Refund</option>
                        <option value="Wholesale / Business" style="background:#121212;">Wholesale / Business</option>
                        <option value="Free Dietitian Consult" style="background:#121212;">Free Dietitian Consult</option>
                        <option value="Other" style="background:#121212;">Other</option>
                    </select>
                </div>
                <div class="contact-field">
                    <label for="c_message">Your Message *</label>
                    <textarea id="c_message" name="message" rows="5" placeholder="Type your query in detail..." required minlength="10" maxlength="2000"></textarea>
                    <div style="display:flex; justify-content:space-between;">
                        <div id="msg-err" class="field-error" style="display:none;"></div>
                        <div class="char-count"><span id="msg-count">0</span>/2000</div>
                    </div>
                </div>
                <button type="submit" name="contact_submit" class="btn-gold" id="submitBtn" style="width:100%; padding:14px; font-size:0.92rem; border-radius:12px;">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
                <p style="font-size:0.72rem; color:rgba(255,255,255,0.3); text-align:center; margin-top:12px;">
                    <i class="fas fa-lock" style="margin-right:4px;"></i> Your data is secure. We never share your information.
                </p>
            </form>
        </div>

        <!-- Info -->
        <div style="display:flex; flex-direction:column; gap:22px;">
            <div class="contact-info-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <h3><i class="fas fa-address-card" style="color:var(--gold-primary); margin-right:8px;"></i> Support Coordinates</h3>
                <div class="contact-info-row"><div class="contact-info-icon"><i class="fas fa-envelope"></i></div><div class="contact-info-text"><h5>Email Us</h5><p>support@wolfnutrition.in</p></div></div>
                <div class="contact-info-row"><div class="contact-info-icon"><i class="fas fa-phone-alt"></i></div><div class="contact-info-text"><h5>Call Us</h5><p>+91 98765 43210</p></div></div>
                <div class="contact-info-row"><div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div><div class="contact-info-text"><h5>Visit Us</h5><p>Kaki Pind, Hoshiarpur Road, Rama Mandi, Jalandhar, Punjab - 144005</p></div></div>
            </div>

            <div class="contact-info-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <h3><i class="fas fa-clock" style="color:var(--gold-primary); margin-right:8px;"></i> Operational Hours</h3>
                <div class="hours-row"><span class="hours-day">Monday - Friday</span><span class="hours-time">10:00 AM - 6:00 PM</span></div>
                <div class="hours-row"><span class="hours-day">Saturday</span><span class="hours-time">10:00 AM - 4:00 PM</span></div>
                <div class="hours-row"><span class="hours-day">Sunday</span><span class="hours-time hours-closed">Closed</span></div>
            </div>

            <div class="contact-info-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <h3><i class="fas fa-certificate" style="color:var(--gold-primary); margin-right:8px;"></i> Credentials</h3>
                <div style="padding:10px 16px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.12); border-radius:10px;">
                    <span style="font-size:0.75rem; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.5px;">FSSAI License</span>
                    <div style="font-size:0.95rem; color:var(--gold-primary); font-weight:700; margin-top:2px;">22126022000063</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// ── Gold Particles ──
(function(){var c=document.getElementById('goldParticles');if(!c)return;var ctx=c.getContext('2d'),p=[];function r(){c.width=window.innerWidth;c.height=window.innerHeight;}r();window.addEventListener('resize',r);for(var i=0;i<30;i++)p.push({x:Math.random()*c.width,y:Math.random()*c.height,r:Math.random()*1.8+0.4,dx:(Math.random()-0.5)*0.25,dy:(Math.random()-0.5)*0.25,o:Math.random()*0.4+0.1});function d(){ctx.clearRect(0,0,c.width,c.height);for(var i=0;i<p.length;i++){var v=p[i];ctx.beginPath();ctx.arc(v.x,v.y,v.r,0,Math.PI*2);ctx.fillStyle='rgba(212,175,55,'+v.o+')';ctx.fill();v.x+=v.dx;v.y+=v.dy;if(v.x<0||v.x>c.width)v.dx*=-1;if(v.y<0||v.y>c.height)v.dy*=-1;}requestAnimationFrame(d);}d();})();

// ── Client-Side Validation ──
(function(){
    var form=document.getElementById('contactForm');
    if(!form) return;

    function showError(id,msg){var el=document.getElementById(id);el.textContent=msg;el.style.display='block';}
    function clearError(id){var el=document.getElementById(id);el.textContent='';el.style.display='none';}

    function valName(){var v=document.getElementById('c_name').value.trim();if(v.length<2||v.length>100||!/^[a-zA-Z\s\-']{2,100}$/.test(v)){showError('name-err','Name: 2-100 chars, letters/spaces/hyphens only.');return false;}clearError('name-err');return true;}
    function valEmail(){var v=document.getElementById('c_email').value.trim();if(!v||! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)||v.length>255){showError('email-err','Enter a valid email address.');return false;}clearError('email-err');return true;}
    function valPhone(){var v=document.getElementById('c_phone').value.trim();if(v&&!/^[6-9][0-9]{9}$/.test(v)){showError('phone-err','Valid 10-digit Indian number required.');return false;}clearError('phone-err');return true;}
    function valMsg(){var v=document.getElementById('c_message').value;if(v.length<10){showError('msg-err','Message must be at least 10 characters.');return false;}if(v.length>2000){showError('msg-err','Message max 2000 characters.');return false;}clearError('msg-err');return true;}

    document.getElementById('c_name').addEventListener('blur',valName);
    document.getElementById('c_name').addEventListener('input',function(){if(document.getElementById('name-err').style.display==='block')valName();});
    document.getElementById('c_email').addEventListener('blur',valEmail);
    document.getElementById('c_email').addEventListener('input',function(){if(document.getElementById('email-err').style.display==='block')valEmail();});
    document.getElementById('c_phone').addEventListener('blur',valPhone);
    document.getElementById('c_phone').addEventListener('input',function(){this.value=this.value.replace(/[^0-9]/g,'').substring(0,10);if(document.getElementById('phone-err').style.display==='block')valPhone();});
    document.getElementById('c_message').addEventListener('input',function(){document.getElementById('msg-count').textContent=this.value.length;valMsg();});

    form.addEventListener('submit',function(e){if(!valName()||!valEmail()||!valPhone()||!valMsg())e.preventDefault();});
})();

// ── 3D Tilt ──
(function(){document.querySelectorAll('.tilt-card').forEach(function(c){c.addEventListener('mousemove',function(e){var r=c.getBoundingClientRect();var x=(e.clientX-r.left)/r.width-0.5;var y=(e.clientY-r.top)/r.height-0.5;c.style.transform='rotateY('+(x*5)+'deg) rotateX('+(-y*5)+'deg) scale(1.01)';c.style.setProperty('--mouse-x',((e.clientX-r.left)/r.width*100)+'%');c.style.setProperty('--mouse-y',((e.clientY-r.top)/r.height*100)+'%');});c.addEventListener('mouseleave',function(){c.style.transform='';});});})();
</script>

<style>.field-error{font-size:0.75rem;color:rgba(255,255,255,0.55);margin-top:5px;font-weight:500;}</style>
