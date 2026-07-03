<?php
// checkout.php
require_once __DIR__ . '/includes/header.php';

$cart_items = get_cart_items();
if (empty($cart_items)) {
    header("Location: index.php");
    exit();
}

$user = get_logged_in_user();
$saved_addresses = [];
if ($user) {
    // Fetch saved addresses
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
    $stmt->execute([$user['id']]);
    $saved_addresses = $stmt->fetchAll();
}

// Default payment method
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'UPI';
$totals = get_cart_totals($payment_method);

$checkout_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $cust_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $cust_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
    $cust_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
    $address_line1 = isset($_POST['address_line1']) ? trim($_POST['address_line1']) : '';
    $address_line2 = isset($_POST['address_line2']) ? trim($_POST['address_line2']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $note = isset($_SESSION['cart_notes']) ? $_SESSION['cart_notes'] : '';
    
    // Address selection from saved
    if ($user && isset($_POST['selected_address_id']) && $_POST['selected_address_id'] !== 'new') {
        $addr_id = (int)$_POST['selected_address_id'];
        $stmt_a = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt_a->execute([$addr_id, $user['id']]);
        $addr = $stmt_a->fetch();
        if ($addr) {
            $cust_name = $addr['name'];
            $cust_phone = $addr['phone'];
            $pincode = $addr['pincode'];
            $address_line1 = $addr['address_line1'];
            $address_line2 = $addr['address_line2'];
            $city = $addr['city'];
            $state = $addr['state'];
        }
    }

    // Input Validations
    if (empty($cust_name) || empty($cust_email) || empty($cust_phone) || empty($pincode) || empty($address_line1) || empty($city) || empty($state)) {
        $checkout_error = "Please fill in all required shipping details.";
    } elseif (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
        $checkout_error = "Please enter a valid 6-digit India Pincode.";
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', $cust_phone)) {
        $checkout_error = "Please enter a valid 10-digit mobile number.";
    } else {
        // Run final calculations
        $totals = get_cart_totals($payment_method);
        
        try {
            $pdo->beginTransaction();
            
            // Generate Unique Order ID
            $order_number = 'WN-' . time() . '-' . rand(1000, 9999);
            
            // Full address string
            $full_address = $address_line1;
            if (!empty($address_line2)) $full_address .= ', ' . $address_line2;
            $full_address .= ", {$city}, {$state} - {$pincode}";
            
            // Insert Order
            $stmt_o = $pdo->prepare("
                INSERT INTO orders (user_id, order_number, subtotal, discount, shipping, total, payment_method, payment_status, customer_name, customer_email, customer_phone, shipping_address, pincode, note) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $user_id = $user ? $user['id'] : null;
            
            // Payment status is 'paid' for mock UPI/Card online gateway, 'pending' for COD
            $pay_status = ($payment_method === 'COD') ? 'pending' : 'paid';
            
            $stmt_o->execute([
                $user_id,
                $order_number,
                $totals['subtotal'],
                $totals['quantity_discount'] + $totals['coupon_discount'],
                $totals['shipping'],
                $totals['total'],
                $payment_method,
                $pay_status,
                $cust_name,
                $cust_email,
                $cust_phone,
                $full_address,
                $pincode,
                $note
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Insert Items and deduct inventory stock
            foreach ($cart_items as $item) {
                // If bundle, variant_id can be null or we map individual items
                $variant_id = isset($item['variant_id']) ? $item['variant_id'] : null;
                $bundle_id = isset($item['bundle_id']) ? $item['bundle_id'] : null;
                
                $stmt_i = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, variant_id, bundle_id, product_name, variant_name, price, quantity) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_i->execute([
                    $order_id,
                    isset($item['product_id']) ? $item['product_id'] : 0,
                    $variant_id,
                    $bundle_id,
                    $item['name'],
                    $item['size'],
                    $item['price'],
                    $item['qty']
                ]);
                
                // Deduct stock for normal variant
                if ($variant_id) {
                    $stmt_u = $pdo->prepare("UPDATE product_variants SET stock_qty = stock_qty - ? WHERE id = ?");
                    $stmt_u->execute([$item['qty'], $variant_id]);
                }
                
                // Deduct stock for bundle elements
                if ($bundle_id) {
                    $stmt_b = $pdo->prepare("
                        SELECT variant_id FROM bundle_items WHERE bundle_id = ?
                    ");
                    $stmt_b->execute([$bundle_id]);
                    $b_items = $stmt_b->fetchAll();
                    foreach ($b_items as $bi) {
                        $stmt_u = $pdo->prepare("UPDATE product_variants SET stock_qty = stock_qty - ? WHERE id = ?");
                        $stmt_u->execute([$item['qty'], $bi['variant_id']]);
                    }
                }
            }
            
            // Update Coupon used counts
            if ($totals['coupon_code']) {
                $stmt_c = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
                $stmt_c->execute([$totals['coupon_code']]);
            }
            
            $pdo->commit();
            
            // Clear Cart & Notes
            clear_cart();
            unset($_SESSION['cart_notes']);
            
            // Redirect
            header("Location: order-confirmation.php?order_number=" . $order_number);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $checkout_error = "Failed to process order: " . $e->getMessage();
        }
    }
}
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>Secure Checkout</h2>
            <p>Complete your shipping and billing details below</p>
        </div>

        <?php if ($checkout_error): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.15); color:var(--danger-color); margin-bottom:20px;">
                ❌ <?php echo htmlspecialchars($checkout_error); ?>
            </div>
        <?php endif; ?>

        <form action="checkout.php" method="POST" id="checkout-form">
            <div class="checkout-grid">
                
                <!-- Shipping Address Form -->
                <div>
                    <!-- Address Selector for Logged In users -->
                    <?php if ($user && !empty($saved_addresses)): ?>
                        <div class="checkout-section-box">
                            <h3>Select Shipping Address</h3>
                            <div style="display:flex; flex-direction:column; gap:15px; margin-bottom:20px;">
                                <?php foreach ($saved_addresses as $addr): ?>
                                    <label style="display:flex; align-items:start; gap:12px; padding:15px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-primary); cursor:pointer;">
                                        <input type="radio" name="selected_address_id" value="<?php echo $addr['id']; ?>" <?php echo $addr['is_default'] ? 'checked' : ''; ?> style="margin-top:4px; accent-color:var(--gold-primary);">
                                        <div>
                                            <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($addr['name']); ?> (<?php echo htmlspecialchars($addr['phone']); ?>)</div>
                                            <div style="font-size:0.85rem; margin-top:4px;"><?php echo htmlspecialchars($addr['address_line1'] . ', ' . $addr['address_line2']); ?></div>
                                            <div style="font-size:0.85rem;"><?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                                <label style="display:flex; align-items:center; gap:12px; padding:15px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-primary); cursor:pointer;">
                                    <input type="radio" name="selected_address_id" value="new" style="accent-color:var(--gold-primary);">
                                    <span style="font-weight:600; color:var(--gold-primary);">+ Add New Shipping Address</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="checkout-section-box" id="new-address-form-box" style="<?php echo ($user && !empty($saved_addresses)) ? 'display:none;' : ''; ?>">
                        <h3>Shipping Details</h3>
                        
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control" value="<?php echo $user ? htmlspecialchars($user['name']) : ''; ?>" placeholder="Enter recipient's full name">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_email">Email Address *</label>
                                <input type="email" name="customer_email" id="customer_email" class="form-control" value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>" placeholder="For order receipt & tracking link">
                            </div>
                            <div class="form-group">
                                <label for="customer_phone">Phone Number *</label>
                                <input type="text" name="customer_phone" id="customer_phone" class="form-control" value="<?php echo $user ? htmlspecialchars($user['phone']) : ''; ?>" placeholder="10-digit mobile number" maxlength="10">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address_line1">Street Address *</label>
                            <input type="text" name="address_line1" id="address_line1" class="form-control" placeholder="House number, apartment, street name" style="margin-bottom:10px;">
                            <input type="text" name="address_line2" id="address_line2" class="form-control" placeholder="Landmark, suite, floor etc (optional)">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City / Town *</label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="e.g. Jalandhar">
                            </div>
                            <div class="form-group">
                                <label for="state">State *</label>
                                <input type="text" name="state" id="state" class="form-control" placeholder="e.g. Punjab">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="pincode">Pincode *</label>
                                <input type="text" name="pincode" id="pincode" class="form-control" placeholder="6-digit India Pincode" maxlength="6">
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" class="form-control" value="India" readonly disabled style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.15);">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="checkout-section-box">
                        <h3>Select Payment Method</h3>
                        
                        <div style="display:flex; flex-direction:column; gap:15px;">
                            <!-- UPI payment -->
                            <label style="display:flex; align-items:center; gap:12px; padding:15px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-primary); cursor:pointer;">
                                <input type="radio" name="payment_method" value="UPI" <?php echo $payment_method === 'UPI' ? 'checked' : ''; ?> onchange="updatePaymentSelection(this)" style="accent-color:var(--gold-primary);">
                                <span style="font-size:1rem; font-weight:700; color:#fff;">
                                    <i class="fas fa-qrcode" style="color:var(--gold-primary); margin-right:10px;"></i> UPI Payment (Instant 10% auto discount on cart)
                                </span>
                            </label>
                            
                            <!-- Card payment -->
                            <label style="display:flex; align-items:center; gap:12px; padding:15px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-primary); cursor:pointer;">
                                <input type="radio" name="payment_method" value="CARD" <?php echo $payment_method === 'CARD' ? 'checked' : ''; ?> onchange="updatePaymentSelection(this)" style="accent-color:var(--gold-primary);">
                                <span style="font-size:1rem; font-weight:700; color:#fff;">
                                    <i class="fas fa-credit-card" style="color:var(--gold-primary); margin-right:10px;"></i> Credit / Debit Cards
                                </span>
                            </label>
                            
                            <!-- Cash on Delivery -->
                            <label style="display:flex; align-items:center; gap:12px; padding:15px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-primary); cursor:pointer;">
                                <input type="radio" name="payment_method" value="COD" <?php echo $payment_method === 'COD' ? 'checked' : ''; ?> onchange="updatePaymentSelection(this)" style="accent-color:var(--gold-primary);">
                                <span style="font-size:1rem; font-weight:700; color:#fff;">
                                    <i class="fas fa-truck-loading" style="color:var(--gold-primary); margin-right:10px;"></i> Cash on Delivery (COD) - ₹99 shipping applies
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary Sidebar -->
                <aside class="summary-sidebar">
                    <h3>Order Summary</h3>
                    
                    <div style="max-height:180px; overflow-y:auto; border-bottom:1px solid var(--border-color); padding-bottom:15px; margin-bottom:15px;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-size:0.85rem;">
                                <div>
                                    <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div style="color:var(--text-muted); font-size:0.75rem;"><?php echo htmlspecialchars($item['size']); ?> &times; <?php echo $item['qty']; ?></div>
                                </div>
                                <span style="font-weight:700;">₹<?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-line-item">
                        <span>Cart Subtotal:</span>
                        <span>₹<?php echo number_format($totals['subtotal'], 2); ?></span>
                    </div>

                    <?php if ($totals['quantity_discount'] > 0 || $totals['coupon_discount'] > 0): ?>
                        <div class="summary-line-item discount">
                            <span>Discounts Applied:</span>
                            <span>-₹<?php echo number_format($totals['quantity_discount'] + $totals['coupon_discount'], 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-line-item" id="summary-shipping-row">
                        <span>Shipping Charges:</span>
                        <span id="summary-shipping-val"><?php echo $totals['shipping'] > 0 ? "₹" . number_format($totals['shipping'], 2) : "FREE"; ?></span>
                    </div>

                    <div class="summary-total">
                        <span>Grand Total:</span>
                        <span id="summary-total-val">₹<?php echo number_format($totals['total'], 2); ?></span>
                    </div>

                    <button type="submit" name="place_order" class="btn-gold" style="width:100%; margin-top:25px; padding:15px; font-size:1.1rem;">
                        PLACE ORDER NOW
                    </button>
                    <p style="text-align:center; font-size:0.75rem; color:var(--text-muted); margin-top:10px;">
                        <i class="fas fa-shield-alt"></i> SSL secure payments. Formulated under strict FSSAI guidelines.
                    </p>
                </aside>
            </div>
        </form>
    </div>

    <!-- Toggle Address and update shipping values script -->
    <script>
        function updatePaymentSelection(element) {
            // Submit form to refresh calculations in php variables
            const form = document.getElementById('checkout-form');
            
            // Create a dummy hidden input to simulate recalculating
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'recalculate';
            hiddenInput.value = '1';
            form.appendChild(hiddenInput);
            
            // Save state of input fields before reload
            form.submit();
        }

        // Toggle visibility of new address block based on selection
        const savedAddrRadios = document.querySelectorAll('input[name="selected_address_id"]');
        const newAddressFormBox = document.getElementById('new-address-form-box');

        if (savedAddrRadios.length > 0 && newAddressFormBox) {
            savedAddrRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'new') {
                        newAddressFormBox.style.display = 'block';
                    } else {
                        newAddressFormBox.style.display = 'none';
                    }
                });
            });
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
