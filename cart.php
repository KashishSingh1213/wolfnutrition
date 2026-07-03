<?php
// cart.php
require_once __DIR__ . '/includes/header.php';

// Handle Direct Post Updates (e.g. Coupon actions)
$coupon_msg = '';
$coupon_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_coupon_btn'])) {
        $code = trim($_POST['coupon_code']);
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
        $_SESSION['cart_notes'] = trim($_POST['seller_notes']);
    }
}

$cart_items = get_cart_items();
$totals = get_cart_totals();
?>

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
            <div style="display:grid; grid-template-columns: 2.2fr 1.2fr; gap:40px; align-items:start;">
                
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
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <!-- Product info -->
                                        <td style="padding: 20px 0; display:flex; gap:20px; align-items:center;">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:70px; height:70px; object-fit:contain; background:#000; border-radius:4px; border:1px solid var(--border-color);">
                                            <div>
                                                <h4 style="font-size:1rem; font-weight:700; margin-bottom:5px;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <div style="font-size:0.85rem; color:var(--text-muted);"><?php echo htmlspecialchars($item['size']); ?></div>
                                                <div style="font-size:0.9rem; color:var(--gold-primary); font-weight:600; margin-top:5px;">₹<?php echo number_format($item['price'], 2); ?></div>
                                                
                                                <form action="cart_api.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="remove">
                                                    <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                    <button type="submit" style="background:none; border:none; color:var(--danger-color); font-size:0.8rem; cursor:pointer; padding:0; margin-top:8px;">
                                                        <i class="fas fa-trash-alt"></i> Remove Item
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <!-- Quantity controls -->
                                        <td style="padding: 20px 0; text-align:center;">
                                            <div style="display:inline-flex; align-items:center; border:1px solid var(--border-color); border-radius:3px; overflow:hidden;">
                                                <form action="cart_api.php" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                    <input type="hidden" name="qty" value="<?php echo $item['qty'] - 1; ?>">
                                                    <button type="submit" style="width:25px; height:25px; border:none; background:transparent; color:#fff; cursor:pointer;">-</button>
                                                </form>
                                                <span style="width:30px; text-align:center; font-weight:600;"><?php echo $item['qty']; ?></span>
                                                <form action="cart_api.php" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                    <input type="hidden" name="qty" value="<?php echo $item['qty'] + 1; ?>">
                                                    <button type="submit" style="width:25px; height:25px; border:none; background:transparent; color:#fff; cursor:pointer;">+</button>
                                                </form>
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

                    <!-- Notes & Estimation columns -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px;">
                        <!-- Notes For Seller -->
                        <div class="glass-card" style="padding:20px; border-radius:8px;">
                            <h4 style="font-size:0.95rem; text-transform:uppercase; margin-bottom:12px; color:var(--gold-muted);">Seller Note</h4>
                            <form action="cart.php" method="POST">
                                <textarea name="seller_notes" rows="3" class="form-control" placeholder="Special delivery instructions or order notes..." style="font-size:0.85rem; margin-bottom:10px;"><?php echo isset($_SESSION['cart_notes']) ? htmlspecialchars($_SESSION['cart_notes']) : ''; ?></textarea>
                                <button type="submit" name="save_notes_btn" class="btn-outline-gold" style="padding:6px 15px; font-size:0.8rem;">Save Notes</button>
                            </form>
                        </div>
                        
                        <!-- Shipping Estimator -->
                        <div class="glass-card" style="padding:20px; border-radius:8px;">
                            <h4 style="font-size:0.95rem; text-transform:uppercase; margin-bottom:12px; color:var(--gold-muted);">Pincode Delivery Check</h4>
                            <div class="pincode-estimator">
                                <input type="text" id="pincode-input" class="form-control" style="font-size:0.85rem; padding:8px 12px;" placeholder="Pincode e.g. 110001" maxlength="6">
                                <button id="pincode-check-btn" class="btn-gold" style="padding:8px 15px; font-size:0.8rem;">Verify</button>
                            </div>
                            <div id="pincode-result" class="pincode-result"></div>
                        </div>
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

                    <!-- Coupon Input -->
                    <form action="cart.php" method="POST" style="border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color); padding:15px 0; margin:15px 0;">
                        <label style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:8px;">Have a Promo Code?</label>
                        <?php if (isset($_SESSION['coupon'])): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(212,175,55,0.1); padding:8px 12px; border-radius:4px; border:1px solid rgba(212,175,55,0.2);">
                                <span style="font-weight:700; color:var(--success-color);"><?php echo htmlspecialchars($_SESSION['coupon']['code']); ?></span>
                                <button type="submit" name="remove_coupon_btn" style="background:none; border:none; color:var(--danger-color); cursor:pointer; font-weight:700;">Remove</button>
                            </div>
                        <?php else: ?>
                            <div class="coupon-box">
                                <input type="text" name="coupon_code" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. WOLF10" required>
                                <button type="submit" name="apply_coupon_btn" class="btn-gold" style="padding:8px 15px; font-size:0.85rem;">Apply</button>
                            </div>
                        <?php endif; ?>
                        <?php if ($coupon_msg): ?>
                            <div style="font-size:0.8rem; margin-top:8px; color: <?php echo $coupon_success ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                                <?php echo htmlspecialchars($coupon_msg); ?>
                            </div>
                        <?php endif; ?>
                    </form>

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
