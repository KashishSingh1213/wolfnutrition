<?php
// cart.php
require_once __DIR__ . '/includes/header.php';

// Handle Direct Post Updates (e.g. Coupon actions)
$coupon_msg = '';
$coupon_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check for all cart mutations
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $coupon_msg = "Invalid form submission. Please try again.";
        $coupon_success = false;
    } else {
        if (isset($_POST['apply_coupon_btn'])) {
            $code = preg_replace('/[^A-Za-z0-9\-_]/', '', trim($_POST['coupon_code']));
            if (strlen($code) > 50) $code = substr($code, 0, 50);
            $res = apply_coupon($code);
            $coupon_msg = $res['message'];
            $coupon_success = $res['success'];
        }

        if (isset($_POST['remove_coupon_btn'])) {
            unset($_SESSION['coupon']);
            $coupon_msg = "Coupon removed.";
            $coupon_success = true;
        }

        if (isset($_POST['save_notes_btn'])) {
            $_SESSION['cart_notes'] = sanitize_string(trim($_POST['seller_notes']));
        }
    }
}

$cart_items = get_cart_items();
$totals = get_cart_totals();
?>

<style>
/* ── Cart Responsive ── */
.cart-item-row{display:grid; grid-template-columns:1fr auto auto; gap:20px; align-items:center; padding:20px 0; border-bottom:1px solid rgba(255,255,255,0.05);}
.cart-item-info{display:flex; gap:16px; align-items:center; overflow:hidden; min-width:0;}
.cart-item-img{width:70px; height:70px; object-fit:contain; background:#000; border-radius:8px; border:1px solid var(--border-color); flex-shrink:0;}
.cart-item-details{overflow:hidden; min-width:0;}
.cart-item-name{font-size:1rem; font-weight:700; margin-bottom:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
.cart-item-size{font-size:0.82rem; color:var(--text-muted);}
.cart-item-price{font-size:0.9rem; color:var(--gold-primary); font-weight:600; margin-top:4px;}
.cart-item-remove{background:none; border:none; color:var(--danger-color); font-size:0.78rem; cursor:pointer; padding:0; margin-top:6px;}
.cart-item-qty{text-align:center; flex-shrink:0;}
.cart-item-subtotal{text-align:right; font-weight:700; color:var(--text-primary); font-size:1.05rem; white-space:nowrap; flex-shrink:0;}
@media(max-width:900px){
    body{overflow-x:hidden;}
    .cart-grid{grid-template-columns:1fr !important; gap:24px !important;}
    .summary-sidebar{position:static !important;}
    .cart-header-row{display:none !important;}
    .section-header h2{font-size:1.5rem !important; letter-spacing:0.5px !important;}
    .section-header{margin-bottom:20px !important;}
    .cart-item-row{grid-template-columns:1fr auto; gap:12px; padding:16px 0;}
    .cart-item-qty{grid-column:2;}
    .cart-item-subtotal{grid-column:2; text-align:right;}
    .cart-item-info{gap:12px;}
    .cart-item-img{width:60px; height:60px;}
    .cart-item-name{font-size:0.88rem; white-space:normal !important; line-height:1.3;}
}
@media(max-width:600px){
    .cart-grid{gap:16px !important;}
    .summary-sidebar{padding:20px !important;border-radius:14px !important;}
    .cart-item-row{padding:14px 0;gap:10px;}
    .cart-item-img{width:55px; height:55px;}
    .cart-item-name{font-size:0.82rem;}
    .cart-item-subtotal{font-size:0.9rem;}
}
</style>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>Your Shopping Cart</h2>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="glass-card" style="padding: 50px; text-align:center; max-width:600px; margin: 40px auto; border-radius:8px;">
                <i class="fas fa-shopping-basket" style="font-size:4rem; color:var(--text-muted); margin-bottom:20px;"></i>
                <h3>Your Cart is Empty</h3>
                <p style="margin: 15px 0;">You have no items in your shopping pack. Browse our supplements to start building your stack.</p>
                <a href="index.php" class="btn-gold" style="padding:12px 30px;">Shop Supplements</a>
            </div>
        <?php else: ?>
            <div class="cart-grid" style="display:grid; grid-template-columns: 2.2fr 1.2fr; gap:40px; align-items:start;">
                
                <!-- Main Items List -->
                <div>
                    <div class="glass-card" style="padding:10px 20px; border-radius:8px;">
                        <div class="cart-header-row" style="display:grid; grid-template-columns:1fr auto auto; gap:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color); color:var(--gold-muted); font-family:var(--font-heading); font-size:0.82rem; text-transform:uppercase; font-weight:700;">
                            <span>Product Details</span>
                            <span style="text-align:center;">Quantity</span>
                            <span style="text-align:right;">Subtotal</span>
                        </div>
                        <?php foreach ($cart_items as $key => $item): ?>
                            <div class="cart-item-row" data-key="<?php echo htmlspecialchars($key); ?>">
                                    <div class="cart-item-info">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                                    <div class="cart-item-details">
                                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="cart-item-size"><?php echo htmlspecialchars($item['size']); ?></div>
                                        <div class="cart-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                        <button type="button" onclick="removeCartItem('<?php echo htmlspecialchars($key); ?>')" class="cart-item-remove">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="cart-item-qty">
                                    <div style="display:inline-flex; align-items:center; border:1px solid var(--border-color); border-radius:6px; overflow:hidden;">
                                        <button type="button" onclick="updateCartQty('<?php echo htmlspecialchars($key); ?>', <?php echo $item['qty'] - 1; ?>)" style="width:30px; height:30px; border:none; background:transparent; color:#fff; cursor:pointer; font-size:0.9rem;">-</button>
                                        <span class="cart-qty-val" style="width:32px; text-align:center; font-weight:700; font-size:0.9rem;"><?php echo $item['qty']; ?></span>
                                        <button type="button" onclick="updateCartQty('<?php echo htmlspecialchars($key); ?>', <?php echo $item['qty'] + 1; ?>)" style="width:30px; height:30px; border:none; background:transparent; color:#fff; cursor:pointer; font-size:0.9rem;">+</button>
                                    </div>
                                </div>
                                <div class="cart-item-subtotal">
                                    ₹<?php echo number_format($item['price'] * $item['qty'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sidebar Summary -->
                <aside class="summary-sidebar">
                    <h3>Order Summary</h3>
                    
                    <div class="summary-line-item">
                        <span>Cart Subtotal:</span>
                        <span>₹<?php echo number_format($totals['subtotal'], 2); ?></span>
                    </div>

                    <?php if ($totals['quantity_discount'] > 0): ?>
                        <div class="summary-line-item discount">
                            <span>Stack Savings:</span>
                            <span>-₹<?php echo number_format($totals['quantity_discount'], 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($totals['coupon_discount'] > 0): ?>
                        <div class="summary-line-item discount">
                            <span>Coupon Discount (<?php echo htmlspecialchars($totals['coupon_code']); ?>):</span>
                            <span>-₹<?php echo number_format($totals['coupon_discount'], 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-line-item">
                        <span>Estimated Shipping:</span>
                        <span><?php echo $totals['shipping'] > 0 ? "₹" . number_format($totals['shipping'], 2) : "FREE"; ?></span>
                    </div>

                    <div class="summary-total">
                        <span>Grand Total:</span>
                        <span>₹<?php echo number_format($totals['total'], 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn-gold" style="width:100%; margin-top:20px; padding:15px;">
                        Proceed to Checkout <i class="fas fa-lock" style="margin-left:5px;"></i>
                    </a>
                </aside>

            </div>
        <?php endif; ?>
    </div>

    <script>
    var cartCsrfToken = '<?php echo generate_csrf_token(); ?>';

    function updateCartQty(key, qty) {
        if (qty < 1) {
            removeCartItem(key);
            return;
        }
        var fd = new URLSearchParams();
        fd.append('action', 'update');
        fd.append('key', key);
        fd.append('qty', qty);
        fd.append('csrf_token', cartCsrfToken);

        fetch('cart_api.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    location.reload();
                } else {
                    alert(d.message || 'Update failed');
                }
            })
            .catch(function() { alert('Network error'); });
    }

    function removeCartItem(key) {
        if (!confirm('Remove this item from cart?')) return;
        var fd = new URLSearchParams();
        fd.append('action', 'remove');
        fd.append('key', key);
        fd.append('csrf_token', cartCsrfToken);

        fetch('cart_api.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    location.reload();
                } else {
                    alert(d.message || 'Remove failed');
                }
            })
            .catch(function() { alert('Network error'); });
    }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
