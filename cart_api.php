<?php
// cart_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) session_start();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// CSRF check for mutating actions
$mutating_actions = ['add', 'add_bundle', 'update', 'remove'];
if (in_array($action, $mutating_actions, true)) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
        exit();
    }
}

// Validate cart key format: product_N_variant_N or bundle_N
function validate_cart_key($key) {
    return preg_match('/^(product|bundle)_\d+_variant_\d+$/', $key) ||
           preg_match('/^bundle_\d+$/', $key);
}

// Quantity bounds
function clamp_quantity($qty) {
    $qty = (int)$qty;
    return max(1, min(99, $qty));
}

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $variant_id = (int)($_POST['variant_id'] ?? 0);
        $qty = clamp_quantity($_POST['quantity'] ?? 1);

        if ($product_id <= 0 || $variant_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product or variant choice.']);
            exit();
        }

        $res = add_to_cart($product_id, $variant_id, $qty);
        if ($res) {
            echo json_encode([
                'success' => true,
                'cart_count' => get_cart_count(),
                'message' => 'Product added to cart.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product is out of stock or unavailable.']);
        }
        break;

    case 'add_bundle':
        $bundle_id = (int)($_POST['bundle_id'] ?? 0);
        $qty = clamp_quantity($_POST['quantity'] ?? 1);

        if ($bundle_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid bundle choice.']);
            exit();
        }

        $res = add_bundle_to_cart($bundle_id, $qty);
        if ($res) {
            echo json_encode([
                'success' => true,
                'cart_count' => get_cart_count(),
                'message' => 'Combo Pack added to cart.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Combo pack variant(s) are out of stock.']);
        }
        break;

    case 'update':
        $key = $_POST['key'] ?? '';
        $qty = clamp_quantity($_POST['qty'] ?? 1);

        if (empty($key) || !validate_cart_key($key)) {
            echo json_encode(['success' => false, 'message' => 'Invalid key.']);
            exit();
        }

        $res = update_cart_qty($key, $qty);
        echo json_encode([
            'success' => $res,
            'cart_count' => get_cart_count(),
            'totals' => get_cart_totals()
        ]);
        break;

    case 'remove':
        $key = $_POST['key'] ?? '';

        if (empty($key) || !validate_cart_key($key)) {
            echo json_encode(['success' => false, 'message' => 'Invalid key.']);
            exit();
        }

        $res = remove_from_cart($key);
        echo json_encode([
            'success' => $res,
            'cart_count' => get_cart_count(),
            'totals' => get_cart_totals()
        ]);
        break;

    case 'get':
    default:
        echo json_encode([
            'items' => get_cart_items(),
            'cart_count' => get_cart_count(),
            'totals' => get_cart_totals()
        ]);
        break;
}
?>
