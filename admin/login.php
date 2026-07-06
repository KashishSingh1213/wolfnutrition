<?php
// admin/login.php — Secure admin login

// --- Security: Session hardening ---
require_once __DIR__ . '/../includes/security.php';
secure_session_start();

// --- Security: Send security headers before any output ---
send_security_headers();

// --- Auth check ---
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in as admin
if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$login_error = '';
$is_locked_out = false;

// --- Handle POST login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF verification
    if (!verify_csrf_token()) {
        $login_error = "Invalid security token. Please refresh and try again.";
        log_security_event('CSRF_TOKEN_MISMATCH', ['reason' => 'CSRF token validation failed']);
    } else {
        // Sanitize inputs
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($email) || empty($password)) {
            $login_error = "Please fill in all details.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $login_error = "Invalid email format.";
        } else {
            // Rate limiting check
            $rate_check = check_rate_limit($email, 5, 15);

            if (!$rate_check['allowed']) {
                $login_error = $rate_check['message'];
                $is_locked_out = true;
                log_security_event('RATE_LIMIT_HIT', ['email' => $email, 'reason' => 'Too many failed attempts']);
            } else {
                // Prepare and execute query (parameterized — safe from SQL injection)
                $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_active FROM users WHERE email = :email AND role = 'admin' AND is_active = 1");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Login success — clear failed attempts
                    clear_failed_attempts($email);

                    // Regenerate session ID to prevent session fixation
                    regenerate_session();

                    // Set session variables
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_role'] = 'admin';
                    $_SESSION['admin_ip'] = get_client_ip();
                    $_SESSION['admin_login_time'] = time();
                    $_SESSION['last_activity'] = time();

                    // Regenerate CSRF token after login
                    unset($_SESSION['csrf_token']);

                    log_security_event('ADMIN_LOGIN_SUCCESS', ['email' => $email]);

                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Login failed — record attempt
                    record_failed_attempt($email);
                    $login_error = "Invalid admin email or password.";
                    log_security_event('ADMIN_LOGIN_FAILED', ['email' => $email, 'reason' => 'Invalid credentials']);
                }
            }
        }
    }

    // Regenerate CSRF token after any POST attempt
    unset($_SESSION['csrf_token']);
}

// Generate CSRF token for the form
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wolf Nutrition | Admin Login</title>
    <meta name="referrer" content="no-referrer">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            color: #1a1a1a;
            -webkit-font-smoothing: antialiased;
            background: #0c0e12;
        }

        /* ---------- LEFT PANEL (Branding) ---------- */
        .brand-panel {
            flex: 1;
            background: linear-gradient(145deg, #0c0e12 0%, #161a22 50%, #1a1f2a 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -40%;
            left: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(212,175,55,0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(212,175,55,0.04) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 380px;
            animation: brandFadeIn 0.8s ease-out;
        }

        @keyframes brandFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 32px;
            background: linear-gradient(135deg, #d4af37 0%, #b8942e 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(212,175,55,0.25), 0 0 60px rgba(212,175,55,0.08);
            animation: logoGlow 3s ease-in-out infinite;
        }

        @keyframes logoGlow {
            0%, 100% { box-shadow: 0 8px 32px rgba(212,175,55,0.25), 0 0 60px rgba(212,175,55,0.08); }
            50% { box-shadow: 0 8px 40px rgba(212,175,55,0.35), 0 0 80px rgba(212,175,55,0.12); }
        }

        .brand-logo img {
            height: 56px;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .brand-title span {
            color: #d4af37;
        }

        .brand-subtitle {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.4);
            font-weight: 400;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .brand-stats {
            display: flex;
            gap: 32px;
            justify-content: center;
        }

        .brand-stat {
            text-align: center;
        }

        .brand-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 4px;
        }

        .brand-stat-label {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .brand-divider {
            width: 1px;
            height: 40px;
            background: rgba(255,255,255,0.1);
        }

        /* ---------- RIGHT PANEL (Login Form) ---------- */
        .login-panel {
            width: 480px;
            min-height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            position: relative;
        }

        .login-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f0d060, #d4af37);
        }

        .login-container {
            width: 100%;
            max-width: 360px;
            animation: formFadeIn 0.6s ease-out 0.2s both;
        }

        @keyframes formFadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-top {
            margin-bottom: 36px;
        }

        .login-top h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .login-top p {
            font-size: 0.88rem;
            color: #888;
            font-weight: 400;
        }

        .login-top p span {
            color: #d4af37;
            font-weight: 600;
        }

        /* ---------- FORM FIELDS ---------- */
        .field-group {
            margin-bottom: 20px;
        }

        .field-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .field-group label svg {
            color: #bbb;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            pointer-events: none;
            transition: color 0.2s;
        }

        .field-group input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            font-size: 0.9rem;
            font-family: inherit;
            border: 1.5px solid #e2e2e2;
            border-radius: 12px;
            background: #f9f9f9;
            color: #1a1a1a;
            outline: none;
            transition: all 0.25s ease;
            autocomplete: off;
        }

        .field-group input::placeholder {
            color: #c5c5c5;
        }

        .field-group input:focus {
            border-color: #d4af37;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(212,175,55,0.1);
        }

        .field-group input:focus ~ .input-icon {
            color: #d4af37;
        }

        /* ---------- SUBMIT BUTTON ---------- */
        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 0.92rem;
            font-weight: 700;
            font-family: inherit;
            color: #0c0e12;
            background: linear-gradient(135deg, #d4af37 0%, #f0d060 50%, #d4af37 100%);
            background-size: 200% 200%;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }

        .submit-btn:hover {
            background-position: 100% 0;
            box-shadow: 0 6px 24px rgba(212,175,55,0.35);
            transform: translateY(-2px);
        }

        .submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(212,175,55,0.25);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .submit-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover::after {
            left: 100%;
        }

        /* ---------- ERROR & LOCKOUT ---------- */
        .error-msg {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #dc2626;
            color: #b91c1c;
            padding: 13px 16px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 500;
            margin-bottom: 22px;
            animation: shake 0.4s ease;
        }

        .error-msg svg { flex-shrink: 0; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            15%, 55% { transform: translateX(-5px); }
            35%, 75% { transform: translateX(5px); }
        }

        .lockout-notice {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 3px solid #f59e0b;
            color: #92400e;
            padding: 13px 16px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 500;
            margin-bottom: 22px;
        }

        /* ---------- BACK LINK ---------- */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 32px;
            font-size: 0.82rem;
            color: #aaa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover { color: #1a1a1a; }
        .back-link svg { transition: transform 0.2s; }
        .back-link:hover svg { transform: translateX(-3px); }

        /* ---------- MOBILE ---------- */
        @media (max-width: 900px) {
            body { flex-direction: column; }
            .brand-panel { display: none; }
            .login-panel {
                width: 100%;
                min-height: 100vh;
                padding: 40px 24px;
            }
            .login-container { max-width: 100%; }
        }
    </style>
</head>
<body>

    <!-- LEFT: Brand Panel -->
    <div class="brand-panel">
        <div class="brand-content">
            <div class="brand-logo">
                <img src="../assets/images/logo.png" alt="Wolf Nutrition">
            </div>
            <h1 class="brand-title">Wolf <span>Nutrition</span></h1>
            <p class="brand-subtitle">Premium Supplements &amp; Wellness.<br>Admin Control Center.</p>
            <div class="brand-stats">
                <div class="brand-stat">
                    <div class="brand-stat-value">500+</div>
                    <div class="brand-stat-label">Products</div>
                </div>
                <div class="brand-divider"></div>
                <div class="brand-stat">
                    <div class="brand-stat-value">10K+</div>
                    <div class="brand-stat-label">Customers</div>
                </div>
                <div class="brand-divider"></div>
                <div class="brand-stat">
                    <div class="brand-stat-value">4.9</div>
                    <div class="brand-stat-label">Rating</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Login Panel -->
    <div class="login-panel <?php echo $is_locked_out ? 'locked' : ''; ?>">
        <div class="login-container">
            <div class="login-top">
                <h2>Welcome back</h2>
                <p>Sign in to <span>Admin Portal</span></p>
            </div>

            <?php if ($login_error): ?>
                <div class="error-msg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <?php if ($is_locked_out): ?>
                <div class="lockout-notice">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Account temporarily locked. Please try again later.
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" id="loginForm" autocomplete="off">
                <?php echo csrf_field(); ?>

                <!-- Honeypot -->
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="form_time" value="<?php echo time(); ?>">

                <div class="field-group">
                    <label for="email">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        Email Address
                    </label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <input type="email" name="email" id="email" placeholder="admin@wolfnutrition.in"
                               required maxlength="255" autocomplete="off" autocapitalize="none" spellcheck="false">
                    </div>
                </div>

                <div class="field-group">
                    <label for="password">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" name="password" id="password" placeholder="Enter your password"
                               required maxlength="128" autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" name="admin_login_btn" class="submit-btn" id="submitBtn">
                    Sign In to Dashboard
                </button>
            </form>

            <a href="../index.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
            Back to store
        </a>
    </div>

    <!-- Security: Client-side protections -->
    <script>
    (function() {
        'use strict';

        var form = document.getElementById('loginForm');
        var btn = document.getElementById('submitBtn');
        var submitted = false;

        form.addEventListener('submit', function(e) {
            if (submitted) { e.preventDefault(); return false; }

            var honeypot = form.querySelector('input[name="website"]');
            if (honeypot && honeypot.value) { e.preventDefault(); return false; }

            var formTime = form.querySelector('input[name="form_time"]');
            if (formTime) {
                var elapsed = Math.floor(Date.now() / 1000) - parseInt(formTime.value, 10);
                if (elapsed < 2) { e.preventDefault(); return false; }
            }

            submitted = true;
            btn.disabled = true;
            btn.textContent = 'Signing in...';
        });

        window.addEventListener('load', function() {
            var pwd = document.getElementById('password');
            if (pwd) pwd.value = '';
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.href);
            }
        });
    })();
    </script>

</body>
</html>
