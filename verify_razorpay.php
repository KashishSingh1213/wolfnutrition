<?php
// verify_razorpay.php — Verify Razorpay payment signature
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

$razorpay_key_secret = getenv('RAZORPAY_KEY_SECRET');

if (empty($razorpay_key_secret) || $razorpay_key_secret === 'YOUR_KEY_SECRET_HERE') {
    echo json_encode(['success' => false, 'message' => 'Razorpay key secret not configured.']);
    exit();
}

$razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_signature = $_POST['razorpay_signature'] ?? '';

if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
    echo json_encode(['success' => false, 'message' => 'Missing payment details.']);
    exit();
}

// Verify signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $razorpay_key_secret);

if (hash_equals($generated_signature, $razorpay_signature)) {
    // Payment verified successfully
    echo json_encode([
        'success' => true,
        'razorpay_payment_id' => $razorpay_payment_id,
        'razorpay_order_id' => $razorpay_order_id,
        'message' => 'Payment verified successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed. Invalid signature.'
    ]);
}
