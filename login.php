<?php
// login.php
require_once __DIR__ . '/includes/header.php';

if (is_logged_in()) {
    header("Location: my-account.php");
    exit();
}

$login_error = '';

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_error = "Please fill in all details.";
    } else {
        $ip = get_client_ip();
        
        // 1. Purge expired login attempts (> 15 minutes)
        $stmt_purge = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL 15 MINUTE");
        $stmt_purge->execute();
        
        // 2. Check if rate-limited (limit: 5 attempts per IP or Email)
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? OR email = ?");
        $stmt_count->execute([$ip, $email]);
        $failed_attempts = $stmt_count->fetchColumn();
        
        if ($failed_attempts >= 5) {
            $login_error = "Too many failed login attempts. Account temporarily locked. Please try again after 15 minutes.";
        } else {
            // 3. Query User
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Reset rate limiting attempts on success
                $stmt_reset = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ? OR email = ?");
                $stmt_reset->execute([$ip, $email]);

                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_role'] = 'admin';
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = 'customer';
                    
                    if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
                        header("Location: checkout.php");
                    } else {
                        header("Location: my-account.php");
                    }
                    exit();
                }
            } else {
                // Record failed login attempt for rate limiting
                $stmt_fail = $pdo->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)");
                $stmt_fail->execute([$ip, $email]);
                
                $login_error = "Invalid Email address or Password.";
            }
        }
    }
}
?>

    <div class="container" style="margin-top: 60px; margin-bottom: 90px; max-width:480px;">
        <div class="glass-card" style="padding: 45px 35px; border-radius: 16px; border: 1px solid rgba(212,175,55,0.15); box-shadow: 0 15px 35px rgba(8,12,16,0.4); background: rgba(18,18,18,0.65); backdrop-filter: blur(12px);">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="assets/images/logo.png" alt="Wolf Nutrition Logo" style="height: 65px; margin-bottom: 15px;">
                <h2 style="font-size:1.9rem; text-transform:uppercase; color:var(--text-primary); font-family:var(--font-heading); font-weight:800; letter-spacing:0.5px; margin:0;">
                    Pack Login
                </h2>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-top:5px; margin-bottom:0;">
                    Access your personalized stacks, profile & orders.
                </p>
            </div>

            <?php if ($login_error): ?>
                <div class="quantity-discount-widget" style="background-color:rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); color:rgba(255,255,255,0.8); padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.1rem;"></i> <span><?php echo htmlspecialchars($login_error); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" method="POST" style="margin-top: 10px;">
                <div class="form-group" style="margin-bottom: 22px;">
                    <label for="email" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="e.g. yuvek@gmail.com" required style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 16px;">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Password</label>
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 45px 13px 16px; width: 100%;">
                        <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" style="position: absolute; right: 16px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; outline: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                            <i id="togglePasswordIcon" class="far fa-eye" style="font-size: 1.15rem; transition: color 0.2s;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login_btn" class="btn-gold" style="width:100%; margin-top:5px; padding:14px; font-weight: 700; font-size: 1rem; border-radius: 30px; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(212,175,55,0.2);">
                    Login To Account
                </button>
            </form>
            
            <div style="text-align:center; margin-top:30px; font-size:0.92rem; border-top:1px solid rgba(255,255,255,0.06); padding-top:20px;">
                <span style="color:var(--text-muted);">New to the Pack?</span> 
                <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" style="color:var(--gold-primary); font-weight:700; margin-left:6px; text-decoration:none; transition: color 0.2s;">Create Account</a>
            </div>
            
        </div>
    </div>

<script>
function togglePasswordVisibility(fieldId, iconId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
        toggleIcon.style.color = 'var(--gold-primary)';
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
        toggleIcon.style.color = 'var(--text-muted)';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
