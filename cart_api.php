<?php
// cart_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/functions.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'add':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
        $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

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
        $bundle_id = isset($_POST['bundle_id']) ? (int)$_POST['bundle_id'] : 0;
        $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

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
        $key = isset($_POST['key']) ? $_POST['key'] : '';
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

        if (empty($key)) {
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
        $key = isset($_POST['key']) ? $_POST['key'] : '';

        if (empty($key)) {
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
