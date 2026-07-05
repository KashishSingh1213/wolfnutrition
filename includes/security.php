<?php
// includes/security.php — Centralized security hardening

// --------------------------------------------------------
// SECURITY HEADERS (call early, before any output)
// --------------------------------------------------------
function send_security_headers() {
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    // XSS Protection (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    // Referrer Policy — don't leak full URLs to third parties
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Permissions Policy — disable unused browser features
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    // HSTS — force HTTPS for 1 year (uncomment when HTTPS is live)
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// --------------------------------------------------------
// CSRF TOKEN
// --------------------------------------------------------
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function verify_csrf_token() {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// --------------------------------------------------------
// RATE LIMITING (brute force protection)
// --------------------------------------------------------
function check_rate_limit($email, $max_attempts = 5, $lockout_minutes = 15) {
    global $pdo;

    $ip = get_client_ip();

    // Clean old attempts outside the lockout window
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->execute([$lockout_minutes]);

    // Count recent failed attempts for this email + IP combo
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM login_attempts WHERE email = ? AND ip_address = ?");
    $stmt->execute([$email, $ip]);
    $row = $stmt->fetch();

    if ($row['cnt'] >= $max_attempts) {
        return [
            'allowed' => false,
            'message' => "Too many failed attempts. Try again in {$lockout_minutes} minutes.",
            'attempts_left' => 0
        ];
    }

    return [
        'allowed' => true,
        'attempts_left' => $max_attempts - $row['cnt']
    ];
}

function record_failed_attempt($email) {
    global $pdo;
    $ip = get_client_ip();
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (?, ?, NOW())");
    $stmt->execute([$ip, $email]);
}

function clear_failed_attempts($email) {
    global $pdo;
    $ip = get_client_ip();
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ? AND ip_address = ?");
    $stmt->execute([$email, $ip]);
}

// --------------------------------------------------------
// CLIENT IP DETECTION (respects proxy headers)
// --------------------------------------------------------
function get_client_ip() {
    // Check for real IP behind proxies
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// --------------------------------------------------------
// INPUT SANITIZATION
// --------------------------------------------------------
function sanitize_email($email) {
    $email = trim($email);
    $email = stripslashes($email);
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function sanitize_string($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function sanitize_int($input) {
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

// --------------------------------------------------------
// SESSION SECURITY
// --------------------------------------------------------
function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session cookie settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0);   // Set to 1 when HTTPS is live
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);   // Prevent session ID in URL
        session_start();
    }
}

function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// --------------------------------------------------------
// ACCOUNT LOCKOUT NOTIFICATION (optional: log to file)
// --------------------------------------------------------
function log_security_event($event, $details = []) {
    $log_dir = dirname(__DIR__) . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0750, true);
    }

    $log_entry = date('Y-m-d H:i:s') . ' | ' . $event . ' | IP: ' . get_client_ip();
    if (!empty($details['email'])) {
        $log_entry .= ' | Email: ' . $details['email'];
    }
    if (!empty($details['reason'])) {
        $log_entry .= ' | Reason: ' . $details['reason'];
    }
    $log_entry .= PHP_EOL;

    file_put_contents($log_dir . '/security.log', $log_entry, FILE_APPEND | LOCK_EX);
}
