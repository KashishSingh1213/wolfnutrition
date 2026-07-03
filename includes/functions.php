<?php
// includes/functions.php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Initialize Cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --------------------------------------------------------
// AUTHENTICATION HELPERS
// --------------------------------------------------------

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    global $pdo;
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, is_active FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        return null;
    }
    return $user;
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_role'] === 'admin';
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// --------------------------------------------------------
// CART FUNCTIONS
// --------------------------------------------------------

// Add item to cart
function add_to_cart($product_id, $variant_id, $qty = 1) {
    global $pdo;
    $qty = (int)$qty;
    if ($qty <= 0) return false;

    // Get product and variant details
    $stmt = $pdo->prepare("
        SELECT p.name as p_name, p.image_url, pv.size_capsules, pv.sale_price, pv.price as mrp, pv.stock_qty 
        FROM products p 
        JOIN product_variants pv ON p.id = pv.product_id 
        WHERE p.id = ? AND pv.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$product_id, $variant_id]);
    $item = $stmt->fetch();
    
    if (!$item) return false;
    
    $cart_key = "product_{$product_id}_variant_{$variant_id}";
    
    // Check stock limit
    $current_qty = isset($_SESSION['cart'][$cart_key]) ? $_SESSION['cart'][$cart_key]['qty'] : 0;
    if (($current_qty + $qty) > $item['stock_qty']) {
        $qty = $item['stock_qty'] - $current_qty;
        if ($qty <= 0) return false;
    }

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'type' => 'product',
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'name' => $item['p_name'],
            'size' => $item['size_capsules'],
            'price' => (float)$item['sale_price'],
            'mrp' => (float)$item['mrp'],
            'image' => $item['image_url'],
            'qty' => $qty,
            'max_stock' => $item['stock_qty']
        ];
    }
    return true;
}

// Add Bundle to cart
function add_bundle_to_cart($bundle_id, $qty = 1) {
    global $pdo;
    $qty = (int)$qty;
    if ($qty <= 0) return false;

    // Fetch bundle details
    $stmt = $pdo->prepare("SELECT id, title, combo_price, banner_image FROM bundles WHERE id = ? AND status = 1");
    $stmt->execute([$bundle_id]);
    $bundle = $stmt->fetch();
    if (!$bundle) return false;

    // Fetch bundle items to verify stock
    $stmt = $pdo->prepare("
        SELECT bi.product_id, bi.variant_id, pv.stock_qty 
        FROM bundle_items bi
        JOIN product_variants pv ON bi.variant_id = pv.id
        WHERE bi.bundle_id = ?
    ");
    $stmt->execute([$bundle_id]);
    $items = $stmt->fetchAll();

    if (empty($items)) return false;

    // Check if any product in the bundle is out of stock
    foreach ($items as $item) {
        if ($item['stock_qty'] < $qty) {
            return false; // Can't add bundle due to insufficient variant stock
        }
    }

    $cart_key = "bundle_{$bundle_id}";

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'type' => 'bundle',
            'bundle_id' => $bundle_id,
            'name' => $bundle['title'],
            'size' => 'Combo Pack',
            'price' => (float)$bundle['combo_price'],
            'mrp' => (float)$bundle['combo_price'], // No separate MRP, sold as package
            'image' => $bundle['banner_image'] ? $bundle['banner_image'] : 'assets/images/products/bundle_default.png',
            'qty' => $qty,
            'max_stock' => 10 // Arbitrary safety limit for bundle additions
        ];
    }

    // Deduct quantity discounts of bundles
    return true;
}

// Update Cart Quantity
function update_cart_qty($cart_key, $qty) {
    $qty = (int)$qty;
    if (isset($_SESSION['cart'][$cart_key])) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$cart_key]);
        } else {
            // Check stock limits if normal product
            if ($_SESSION['cart'][$cart_key]['type'] === 'product') {
                if ($qty > $_SESSION['cart'][$cart_key]['max_stock']) {
                    $qty = $_SESSION['cart'][$cart_key]['max_stock'];
                }
            }
            $_SESSION['cart'][$cart_key]['qty'] = $qty;
        }
        return true;
    }
    return false;
}

// Remove item from cart
function remove_from_cart($cart_key) {
    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
        return true;
    }
    return false;
}

// Clear Cart
function clear_cart() {
    $_SESSION['cart'] = [];
    unset($_SESSION['coupon']);
}

// Get Cart Item Count
function get_cart_count() {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['qty'];
    }
    return $count;
}

// Get Cart Items
function get_cart_items() {
    return $_SESSION['cart'];
}

// Get Cart Subtotal (Calculated from variant sale prices / bundle prices)
function get_cart_subtotal() {
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    return $subtotal;
}

// --------------------------------------------------------
// DISCOUNT & PRICING ENGINE
// --------------------------------------------------------

// Calculate automatic tier quantity discounts
function get_quantity_discount($subtotal) {
    global $pdo;
    
    // Count total products in cart (regular products only or total quantity of items)
    $total_qty = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_qty += $item['qty'];
    }
    
    if ($total_qty <= 0) return 0;
    
    // Fetch matching tier discount from database
    $stmt = $pdo->prepare("
        SELECT discount_percent 
        FROM quantity_discounts 
        WHERE min_qty <= ? AND status = 1 
        ORDER BY min_qty DESC LIMIT 1
    ");
    $stmt->execute([$total_qty]);
    $discount = $stmt->fetch();
    
    if ($discount) {
        return ($subtotal * ($discount['discount_percent'] / 100));
    }
    
    return 0;
}

// Coupon validation
function validate_coupon($code, $subtotal) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND status = 1 AND expiry_date >= CURDATE()
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
    }
    
    if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
        return ['valid' => false, 'message' => 'This coupon usage limit has been reached.'];
    }
    
    if ($subtotal < $coupon['min_order_amount']) {
        return ['valid' => false, 'message' => 'Minimum order amount for this coupon is ₹' . number_format($coupon['min_order_amount'], 2) . '.'];
    }
    
    // Calculate coupon discount value
    $discount_val = 0;
    if ($coupon['type'] === 'percentage') {
        $discount_val = $subtotal * ($coupon['value'] / 100);
        if ($coupon['max_discount'] > 0 && $discount_val > $coupon['max_discount']) {
            $discount_val = $coupon['max_discount'];
        }
    } else { // flat
        $discount_val = $coupon['value'];
    }
    
    return [
        'valid' => true,
        'id' => $coupon['id'],
        'code' => $coupon['code'],
        'discount_amount' => $discount_val,
        'message' => 'Coupon applied successfully!'
    ];
}

// Apply Coupon to Session
function apply_coupon($code) {
    $subtotal = get_cart_subtotal();
    $res = validate_coupon($code, $subtotal);
    if ($res['valid']) {
        $_SESSION['coupon'] = [
            'id' => $res['id'],
            'code' => $res['code'],
            'discount_amount' => $res['discount_amount']
        ];
        return ['success' => true, 'message' => $res['message']];
    }
    unset($_SESSION['coupon']);
    return ['success' => false, 'message' => $res['message']];
}

// Get Cart Calculation Breakdown
function get_cart_totals($payment_method = 'UPI') {
    $subtotal = get_cart_subtotal();
    
    // 1. Calculate Quantity Tier Discount
    $qty_discount = get_quantity_discount($subtotal);
    $after_qty_discount = $subtotal - $qty_discount;
    
    // 2. Calculate Coupon Discount (if applied)
    $coupon_discount = 0;
    $coupon_code = '';
    if (isset($_SESSION['coupon'])) {
        // Re-validate coupon in case cart items changed
        $res = validate_coupon($_SESSION['coupon']['code'], $after_qty_discount);
        if ($res['valid']) {
            $coupon_discount = $res['discount_amount'];
            $coupon_code = $_SESSION['coupon']['code'];
            // Update session values
            $_SESSION['coupon']['discount_amount'] = $coupon_discount;
        } else {
            unset($_SESSION['coupon']);
        }
    }
    
    $after_coupon_discount = $after_qty_discount - $coupon_discount;
    
    // 3. Shipping Fee Logic:
    // Shipping is FREE if:
    // - Subtotal after discounts is >= 999, OR
    // - Payment method is prepaid (UPI or CARD)
    $shipping = 99.00;
    if ($after_coupon_discount >= 999.00 || $payment_method !== 'COD') {
        $shipping = 0.00;
    }
    
    $total = $after_coupon_discount + $shipping;
    if ($total < 0) $total = 0;
    
    return [
        'subtotal' => $subtotal,
        'quantity_discount' => $qty_discount,
        'coupon_discount' => $coupon_discount,
        'coupon_code' => $coupon_code,
        'shipping' => $shipping,
        'total' => $total
    ];
}

// --------------------------------------------------------
// DATA FETCHING HELPERS
// --------------------------------------------------------

function get_announcements() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT message, link FROM announcements WHERE status = 1 ORDER BY display_order ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_whatsapp_settings() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT phone_number, greeting_message, status FROM whatsapp_settings WHERE id = 1");
    $stmt->execute();
    return $stmt->fetch();
}

function get_certificates() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT image_url, title FROM certificates WHERE status = 1 ORDER BY display_order ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_categories() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_active_bundles() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM bundles WHERE status = 1 ORDER BY display_order ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// --------------------------------------------------------
// PINCODE ESTIMATOR
// --------------------------------------------------------

function estimate_shipping_by_pincode($pincode) {
    $pincode = trim($pincode);
    if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
        return ['valid' => false, 'message' => 'Invalid Pincode format. Must be 6 digits.'];
    }
    
    // Basic India pincode zone logic:
    // 11xxxx to 13xxxx: Delhi NCR / Haryana (Super Fast Delivery, 2-3 Days)
    // 14xxxx to 16xxxx: Punjab (Super Fast, 2-3 Days)
    // 40xxxx: Mumbai/Maharashtra (3-4 Days)
    // Other: Rest of India (4-6 Days)
    $zone = (int)substr($pincode, 0, 2);
    
    if ($zone >= 11 && $zone <= 16) {
        $est = "2 - 3 Days (Express Delivery)";
    } elseif ($zone >= 40 && $zone <= 44) {
        $est = "3 - 4 Days (Fast Delivery)";
    } else {
        $est = "4 - 6 Days (Standard Delivery)";
    }
    
    return [
        'valid' => true,
        'estimate' => $est,
        'cod_available' => true
    ];
}
?>
