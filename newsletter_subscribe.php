<?php
// newsletter_subscribe.php — Newsletter subscription with email confirmation
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/config/email.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) session_start();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

// Must be logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to subscribe.']);
    exit();
}

// Get logged-in user's email
$user = get_logged_in_user();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User session expired. Please log in again.']);
    exit();
}

$email = $user['email'];
$ip = get_client_ip();

// Rate limiting: max 3 subscriptions per IP per hour
$rate_stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$rate_stmt->execute([$ip]);
if ($rate_stmt->fetchColumn() >= 3) {
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please try again later.']);
    exit();
}

// Check if already subscribed
$check_stmt = $pdo->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
$check_stmt->execute([$email]);
$existing = $check_stmt->fetch();

if ($existing) {
    if ($existing['is_active']) {
        echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
        exit();
    } else {
        // Reactivate subscription
        $reactivate = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 1 WHERE id = ?");
        $reactivate->execute([$existing['id']]);
        
        // Send reactivation email
        send_welcome_email($user['name'], $email);
        
        echo json_encode(['success' => true, 'message' => 'Welcome back! Your subscription has been reactivated. Check your inbox.']);
        exit();
    }
}

// Insert new subscriber
$insert_stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, ip_address) VALUES (?, ?)");
$insert_stmt->execute([$email, $ip]);

// Send welcome email
send_welcome_email($user['name'], $email);

echo json_encode(['success' => true, 'message' => 'Welcome to the pack! Check your inbox for a confirmation.']);

/**
 * Send welcome email to new subscriber
 */
function send_welcome_email($name, $email) {
    $subject = "Welcome to the Wolf Pack! \xF0\x9F\x90\xBA";
    $html = build_newsletter_welcome_html($name);
    
    $result = send_brevo_email($email, $name, $subject, $html);
    
    if (!$result['success']) {
        error_log('[Newsletter] Failed to send welcome email to ' . $email . ': ' . $result['message']);
    }
    
    return $result;
}

/**
 * Build newsletter welcome email HTML
 */
function build_newsletter_welcome_html($name) {
    $year = date('Y');
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#080C10;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#080C10;padding:40px 20px;"><tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#121212;border-radius:16px;overflow:hidden;border:1px solid rgba(212,175,55,0.15);">
        <tr><td style="background:linear-gradient(135deg,#D4AF37,#FFD700);padding:24px 30px;text-align:center;"><h1 style="margin:0;font-size:22px;color:#080C10;text-transform:uppercase;letter-spacing:2px;">WOLF NUTRITION</h1></td></tr>
        <tr><td style="padding:40px 35px;">
            <h2 style="color:#FFF;font-size:20px;margin:0 0 8px;text-transform:uppercase;">Welcome to the Pack!</h2>
            <p style="color:rgba(255,255,255,0.7);font-size:15px;line-height:1.6;margin:0 0 24px;">Hi <strong style="color:#D4AF37;">' . htmlspecialchars($name) . '</strong>,</p>
            <p style="color:rgba(255,255,255,0.7);font-size:15px;line-height:1.6;margin:0 0 24px;">You are now part of the <strong style="color:#FFF;">Wolf Pack</strong>! Get ready for exclusive discounts, stack guides, and early access to new products.</p>
            <table width="100%" style="background:rgba(212,175,55,0.06);border:1px solid rgba(212,175,55,0.15);border-radius:10px;margin-bottom:24px;"><tr><td style="padding:18px 22px;">
                <p style="margin:0 0 6px;font-size:13px;color:#D4AF37;font-weight:700;text-transform:uppercase;letter-spacing:1px;">What to expect:</p>
                <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.6);line-height:1.5;">
                    &#x2714; Exclusive member-only discounts<br>
                    &#x2714; Expert stack guides & nutrition tips<br>
                    &#x2714; Early access to new product launches<br>
                    &#x2714; No spam — only gains
                </p>
            </td></tr></table>
            <p style="color:rgba(255,255,255,0.5);font-size:13px;line-height:1.5;margin:0;">Have questions? Reply to this email or chat on <a href="https://wa.me/919876543210" style="color:#D4AF37;text-decoration:none;font-weight:700;">WhatsApp</a>.</p>
        </td></tr>
        <tr><td style="background:rgba(255,255,255,0.02);padding:20px 35px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
            <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.35);">&copy; ' . $year . ' Wolf Nutrition. All rights reserved. FSSAI Reg No: 22126022000063</p>
        </td></tr>
    </table></td></tr></table></body></html>';
}
?>
