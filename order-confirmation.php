<?php
// order-confirmation.php
require_once __DIR__ . '/includes/header.php';

$order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';

if (empty($order_number)) {
    header("Location: index.php");
    exit();
}

// Validate order number format (ORD-XXXX pattern)
if (!preg_match('/^ORD-\d{4,}$/', $order_number)) {
    header("Location: index.php");
    exit();
}

// Fetch Order info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$order_number]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit();
}

// CRITICAL: Ownership check — only the order owner (or admin) can view
$is_owner = false;
if (is_logged_in()) {
    if (isset($_SESSION['admin_id'])) {
        $is_owner = true; // Admins can view any order
    } elseif (isset($_SESSION['user_id'])) {
        // Check via user_id column (logged-in users)
        if ($order['user_id'] == $_SESSION['user_id']) {
            $is_owner = true;
        } else {
            // Fallback: check via email match
            $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt_user->execute([$_SESSION['user_id']]);
            $current_user = $stmt_user->fetch();
            if ($current_user && $current_user['email'] === $order['customer_email']) {
                $is_owner = true;
            }
        }
    }
}

if (!$is_owner) {
    header("Location: my-account.php");
    exit();
}

// Fetch Items
$stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_i->execute([$order['id']]);
$items = $stmt_i->fetchAll();

// Delivery estimate
$est = estimate_shipping_by_pincode($order['pincode']);
$delivery_date = date('d M, Y', strtotime('+4 days'));
if ($est['valid']) {
    if (strpos($est['estimate'], '2 - 3') !== false) {
        $delivery_date = date('d M, Y', strtotime('+3 days'));
    } elseif (strpos($est['estimate'], '3 - 4') !== false) {
        $delivery_date = date('d M, Y', strtotime('+4 days'));
    } else {
        $delivery_date = date('d M, Y', strtotime('+6 days'));
    }
}
?>

    <div class="container" style="margin-top: 50px; margin-bottom: 70px; max-width:800px;">
        <div class="glass-card" style="padding:40px; border-radius:12px; text-align:center;">
            
            <!-- Green check animation circle -->
            <div style="width:90px; height:90px; border-radius:50%; border:4px solid var(--gold-primary); display:flex; align-items:center; justify-content:center; margin: 0 auto 20px auto; background:rgba(212,175,55,0.05); box-shadow:var(--gold-glow-hover);">
                <i class="fas fa-check" style="font-size:3rem; color:var(--gold-primary);"></i>
            </div>
            
            <h1 style="font-size:2.4rem; text-transform:uppercase; margin-bottom:10px; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                Order Confirmed!
            </h1>
            <p style="font-size:1.1rem; color:var(--text-primary); margin-bottom:30px;">
                Welcome to the pack. We are processing your request.
            </p>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; text-align:left; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color); padding:25px 0; margin-bottom:35px;">
                <!-- Left column -->
                <div>
                    <div style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:5px;">Order Number</div>
                    <div style="font-weight:700; color:#fff; font-size:1.1rem; margin-bottom:15px;"><?php echo htmlspecialchars($order['order_number']); ?></div>

                    <div style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:5px;">Payment Method</div>
                    <div style="font-weight:700; color:#fff; margin-bottom:15px;">
                        <?php echo htmlspecialchars($order['payment_method']); ?> 
                        <span style="font-size:0.8rem; color:var(--gold-primary); font-weight:600;">(<?php echo strtoupper($order['payment_status']); ?>)</span>
                    </div>

                    <div style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:5px;">Estimated Delivery Date</div>
                    <div style="font-weight:700; color:var(--success-color);"><?php echo $delivery_date; ?></div>
                </div>
                <!-- Right column -->
                <div>
                    <div style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:5px;">Delivery Contact</div>
                    <div style="font-weight:700; color:#fff; margin-bottom:15px;"><?php echo htmlspecialchars($order['customer_name']); ?> (<?php echo htmlspecialchars($order['customer_phone']); ?>)</div>

                    <div style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:5px;">Shipping Address</div>
                    <div style="font-size:0.9rem; line-height:1.4; color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                </div>
            </div>

            <!-- Ordered items details -->
            <h3 style="text-align:left; font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-muted);">Ordered Items</h3>
            <div style="text-align:left; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:6px; padding:20px; margin-bottom:35px;">
                <?php foreach ($items as $item): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:0.9rem; border-bottom:1px dashed rgba(255,255,255,0.05); padding-bottom:10px;">
                        <div>
                            <span style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                            <div style="color:var(--text-muted); font-size:0.8rem; margin-top:3px;"><?php echo htmlspecialchars($item['variant_name']); ?> &times; <?php echo $item['quantity']; ?></div>
                        </div>
                        <span style="font-weight:700; color:var(--gold-primary);">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.05rem; padding-top:10px; color:#fff;">
                    <span>Grand Total:</span>
                    <span style="color:var(--gold-primary);">₹<?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>

            <div style="display:flex; gap:15px; justify-content:center;">
                <a href="index.php" class="btn-outline-gold" style="padding:12px 25px;">Back to Shop</a>
                <a href="my-account.php" class="btn-gold" style="padding:12px 25px;">Track Order in My Account</a>
            </div>
            
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
