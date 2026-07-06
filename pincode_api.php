<?php
// pincode_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

// Rate limiting: max 30 requests per IP per minute
if (session_status() === PHP_SESSION_NONE) session_start();
$rate_key = 'pincode_count_' . date('YmdHi');
$_SESSION[$rate_key] = ($_SESSION[$rate_key] ?? 0) + 1;
if ($_SESSION[$rate_key] > 30) {
    echo json_encode(['valid' => false, 'message' => 'Too many requests. Please wait.']);
    exit();
}

$pincode = isset($_GET['pincode']) ? trim($_GET['pincode']) : '';

// Validate format at entry point: exactly 6 digits, no leading zero
if (empty($pincode) || !preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
    echo json_encode(['valid' => false, 'message' => 'Please enter a valid 6-digit pincode.']);
    exit();
}

$result = estimate_shipping_by_pincode($pincode);
echo json_encode($result);
?>
