<?php
// pincode_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';

$pincode = isset($_GET['pincode']) ? trim($_GET['pincode']) : '';

if (empty($pincode)) {
    echo json_encode(['valid' => false, 'message' => 'Pincode is required.']);
    exit();
}

$result = estimate_shipping_by_pincode($pincode);
echo json_encode($result);
?>
