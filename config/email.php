<?php
// config/email.php — Brevo Email via .env

require_once __DIR__ . '/env.php';

define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
define('BREVO_API_URL', 'https://api.brevo.com/v3/smtp/email');
define('SENDER_EMAIL', getenv('SENDER_EMAIL') ?: 'noreply@wolfnutrition.in');
define('SENDER_NAME', getenv('SENDER_NAME') ?: 'Wolf Nutrition');
define('SUPPORT_EMAIL', getenv('SUPPORT_EMAIL') ?: 'support@wolfnutrition.in');

/**
 * Send email via Brevo SMTP API
 */
function send_brevo_email($to, $toName, $subject, $htmlBody, $textBody = '') {
    $apiKey = BREVO_API_KEY;

    if (empty($apiKey) || $apiKey === 'YOUR_BREVO_API_KEY_HERE') {
        error_log('[Brevo] API key not configured in .env');
        return ['success' => false, 'message' => 'Email service not configured.'];
    }

    $payload = [
        'sender' => [
            'name'  => SENDER_NAME,
            'email' => SENDER_EMAIL,
        ],
        'to' => [
            ['email' => $to, 'name' => $toName]
        ],
        'subject'     => $subject,
        'htmlContent' => $htmlBody,
        'textContent' => $textBody ?: strip_tags($htmlBody),
    ];

    $ch = curl_init(BREVO_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . $apiKey,
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log('[Brevo] cURL Error: ' . $curlError);
        return ['success' => false, 'message' => 'Email send failed.'];
    }

    if ($httpCode === 201 || $httpCode === 200) {
        return ['success' => true, 'message' => 'Email sent successfully.'];
    }

    $resp = json_decode($response, true);
    $msg = $resp['message'] ?? 'API error (HTTP ' . $httpCode . ')';
    error_log('[Brevo] API Error: ' . $msg);
    return ['success' => false, 'message' => 'Email send failed.'];
}

/**
 * Build contact confirmation email HTML
 */
function build_contact_confirmation_html($name) {
    $year = date('Y');
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#080C10;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#080C10;padding:40px 20px;"><tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#121212;border-radius:16px;overflow:hidden;border:1px solid rgba(212,175,55,0.15);">
        <tr><td style="background:linear-gradient(135deg,#D4AF37,#FFD700);padding:24px 30px;text-align:center;"><h1 style="margin:0;font-size:22px;color:#080C10;text-transform:uppercase;letter-spacing:2px;">WOLF NUTRITION</h1></td></tr>
        <tr><td style="padding:40px 35px;">
            <h2 style="color:#FFF;font-size:20px;margin:0 0 8px;text-transform:uppercase;">Message Received!</h2>
            <p style="color:rgba(255,255,255,0.7);font-size:15px;line-height:1.6;margin:0 0 24px;">Hi <strong style="color:#D4AF37;">' . htmlspecialchars($name) . '</strong>,</p>
            <p style="color:rgba(255,255,255,0.7);font-size:15px;line-height:1.6;margin:0 0 24px;">Thank you for reaching out to <strong style="color:#FFF;">Wolf Nutrition</strong>. We have received your message and our support team will get back to you within <strong style="color:#D4AF37;">24 hours</strong>.</p>
            <table width="100%" style="background:rgba(212,175,55,0.06);border:1px solid rgba(212,175,55,0.15);border-radius:10px;margin-bottom:24px;"><tr><td style="padding:18px 22px;">
                <p style="margin:0 0 6px;font-size:13px;color:#D4AF37;font-weight:700;text-transform:uppercase;letter-spacing:1px;">What happens next?</p>
                <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.6);line-height:1.5;">1. Our team reviews your query<br>2. We prepare a detailed response<br>3. You receive a reply within 24 hours</p>
            </td></tr></table>
            <p style="color:rgba(255,255,255,0.5);font-size:13px;line-height:1.5;margin:0;">Need urgent help? Chat on <a href="https://wa.me/919876543210" style="color:#D4AF37;text-decoration:none;font-weight:700;">WhatsApp</a> or call <strong style="color:#D4AF37;">+91 98765 43210</strong>.</p>
        </td></tr>
        <tr><td style="background:rgba(255,255,255,0.02);padding:20px 35px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
            <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.35);">&copy; ' . $year . ' Wolf Nutrition. All rights reserved. FSSAI Reg No: 22126022000063</p>
        </td></tr>
    </table></td></tr></table></body></html>';
}

/**
 * Build admin notification email HTML
 */
function build_admin_notification_html($name, $email, $phone, $subject, $message) {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#080C10;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#080C10;padding:40px 20px;"><tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#121212;border-radius:16px;overflow:hidden;border:1px solid rgba(212,175,55,0.15);">
        <tr><td style="background:linear-gradient(135deg,#D4AF37,#FFD700);padding:20px 30px;text-align:center;"><h1 style="margin:0;font-size:18px;color:#080C10;text-transform:uppercase;letter-spacing:2px;">NEW CONTACT MESSAGE</h1></td></tr>
        <tr><td style="padding:30px 35px;">
            <table width="100%" cellpadding="8" cellspacing="0" style="font-size:14px;">
                <tr><td style="color:#D4AF37;font-weight:700;width:120px;">Name</td><td style="color:#fff;">' . htmlspecialchars($name) . '</td></tr>
                <tr><td style="color:#D4AF37;font-weight:700;">Email</td><td style="color:#fff;">' . htmlspecialchars($email) . '</td></tr>
                <tr><td style="color:#D4AF37;font-weight:700;">Phone</td><td style="color:#fff;">' . htmlspecialchars($phone ?: 'Not provided') . '</td></tr>
                <tr><td style="color:#D4AF37;font-weight:700;">Subject</td><td style="color:#fff;">' . htmlspecialchars($subject ?: 'No subject') . '</td></tr>
            </table>
            <div style="margin-top:20px;padding:18px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;">
                <p style="margin:0 0 6px;font-size:12px;color:#D4AF37;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Message</p>
                <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.7);line-height:1.6;white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
            </div>
        </td></tr>
    </table></td></tr></table></body></html>';
}
