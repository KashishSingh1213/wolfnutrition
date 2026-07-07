<?php
// create_razorpay_order.php — Server-side Razorpay order creation
session_start();
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/env.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// CSRF check
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request token']);
    exit();
}

$razorpay_key_id = getenv('RAZORPAY_KEY_ID');
$razorpay_key_secret = getenv('RAZORPAY_KEY_SECRET');

if (empty($razorpay_key_id) || empty($razorpay_key_secret) || $razorpay_key_id === 'rzp_test_YOUR_KEY_ID_HERE') {
    echo json_encode(['success' => false, 'message' => 'Razorpay keys not configured. Please add your test keys to .env file.']);
    exit();
}

$amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
$receipt = isset($_POST['receipt']) ? sanitize_string($_POST['receipt']) : 'order_' . time();

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit();
}

// Amount in paise (Razorpay expects paise)
$amount_paise = round($amount * 100);

// Create order via Razorpay API
$payload = json_encode([
    'amount' => $amount_paise,
    'currency' => 'INR',
    'receipt' => $receipt,
    'payment_capture' => 1
]);

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_USERPWD => $razorpay_key_id . ':' . $razorpay_key_secret,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(['success' => false, 'message' => 'Failed to create Razorpay order. Please try again.']);
    exit();
}

$order_data = json_decode($response, true);

if (isset($order_data['error'])) {
    echo json_encode(['success' => false, 'message' => $order_data['error']['description'] ?? 'Razorpay error']);
    exit();
}

echo json_encode([
    'success' => true,
    'razorpay_order_id' => $order_data['id'],
    'amount' => $order_data['amount'],
    'currency' => $order_data['currency'],
    'key_id' => $razorpay_key_id
]);
