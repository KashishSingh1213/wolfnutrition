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
@media(max-width:900px){
    .cart-grid{grid-template-columns:1fr !important; gap:24px !important;}
    .summary-sidebar{position:static !important;}
}
@media(max-width:600px){
    .cart-grid{gap:16px !important;}
    .summary-sidebar{padding:20px !important;}
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
                    <div class="glass-card" style="padding:20px; border-radius:8px;">
                        <table style="width:100%; border-collapse:collapse; text-align:left;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color); color:var(--gold-muted); font-family:var(--font-heading); font-size:0.9rem; text-transform:uppercase;">
                                    <th style="padding-bottom:15px;">Product Details</th>
                                    <th style="padding-bottom:15px; text-align:center;">Quantity</th>
                                    <th style="padding-bottom:15px; text-align:right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $key => $item): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);" data-key="<?php echo htmlspecialchars($key); ?>">
                                        <!-- Product info -->
                                        <td style="padding: 20px 0; display:flex; gap:20px; align-items:center;">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:70px; height:70px; object-fit:contain; background:#000; border-radius:4px; border:1px solid var(--border-color);">
                                            <div>
                                                <h4 style="font-size:1rem; font-weight:700; margin-bottom:5px;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <div style="font-size:0.85rem; color:var(--text-muted);"><?php echo htmlspecialchars($item['size']); ?></div>
                                                <div style="font-size:0.9rem; color:var(--gold-primary); font-weight:600; margin-top:5px;">₹<?php echo number_format($item['price'], 2); ?></div>
                                                <button type="button" onclick="removeCartItem('<?php echo htmlspecialchars($key); ?>')" style="background:none; border:none; color:var(--danger-color); font-size:0.8rem; cursor:pointer; padding:0; margin-top:8px;">
                                                    <i class="fas fa-trash-alt"></i> Remove Item
                                                </button>
                                            </div>
                                        </td>
                                        <!-- Quantity controls -->
                                        <td style="padding: 20px 0; text-align:center;">
                                            <div style="display:inline-flex; align-items:center; border:1px solid var(--border-color); border-radius:3px; overflow:hidden;">
                                                <button type="button" onclick="updateCartQty('<?php echo htmlspecialchars($key); ?>', <?php echo $item['qty'] - 1; ?>)" style="width:25px; height:25px; border:none; background:transparent; color:#fff; cursor:pointer;">-</button>
                                                <span class="cart-qty-val" style="width:30px; text-align:center; font-weight:600;"><?php echo $item['qty']; ?></span>
                                                <button type="button" onclick="updateCartQty('<?php echo htmlspecialchars($key); ?>', <?php echo $item['qty'] + 1; ?>)" style="width:25px; height:25px; border:none; background:transparent; color:#fff; cursor:pointer;">+</button>
                                            </div>
                                        </td>
                                        <!-- Item subtotal -->
                                        <td style="padding: 20px 0; text-align:right; font-weight:700; color:var(--text-primary); font-size:1.05rem;">
                                            ₹<?php echo number_format($item['price'] * $item['qty'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
