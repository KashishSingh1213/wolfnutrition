<?php
// admin/orders.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Update Order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $ord_id = (int)$_POST['order_id'];
    $pay_status = $_POST['payment_status'];
    $ship_status = $_POST['shipping_status'];
    $tracking = trim($_POST['tracking_number']);
    $courier = trim($_POST['courier_name']);

    $stmt_u = $pdo->prepare("
        UPDATE orders 
        SET payment_status = ?, shipping_status = ?, tracking_number = ?, courier_name = ? 
        WHERE id = ?
    ");
    $stmt_u->execute([$pay_status, $ship_status, $tracking, $courier, $ord_id]);
    $action_msg = "Order credentials updated successfully.";
}

if ($order_id > 0) {
    // Fetch individual order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt_i->execute([$order['id']]);
        $items = $stmt_i->fetchAll();
    }
} else {
    // Fetch all orders
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll();
}
?>

    <?php if ($order_id > 0 && $order): ?>
        <!-- Order Detail View -->
        <div style="margin-bottom: 20px;">
            <a href="orders.php" style="color:var(--gold-muted);"><i class="fas fa-arrow-left"></i> Back to Orders List</a>
        </div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-size:1.8rem; text-transform:uppercase;">Manage Order: <?php echo htmlspecialchars($order['order_number']); ?></h2>
            <div style="font-size:0.85rem; color:var(--text-muted);">Placed on <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
        </div>

        <?php if ($action_msg): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
                ✅ <?php echo htmlspecialchars($action_msg); ?>
            </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px; align-items:start;">
            <!-- Left Panel: Details -->
            <div>
                <!-- Customer info -->
                <div class="glass-card" style="padding:25px; border-radius:8px; margin-bottom:25px;">
                    <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">Customer & Shipping</h3>
                    <table style="width:100%; font-size:0.9rem; line-height:1.6; color:rgba(255,255,255,0.6);">
                        <tr>
                            <td style="color:var(--text-muted); font-weight:600; width:30%;">Name</td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted); font-weight:600;">Email</td>
                            <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted); font-weight:600;">Phone</td>
                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted); font-weight:600;">Address</td>
                            <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                        </tr>
                        <?php if ($order['note']): ?>
                            <tr>
                                <td style="color:var(--text-muted); font-weight:600;">Seller Notes</td>
                                <td style="color:orange; font-style:italic;"><?php echo htmlspecialchars($order['note']); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Items list -->
                <div class="glass-card" style="padding:25px; border-radius:8px;">
                    <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">Ordered Items</h3>
                    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.05); text-align:left; color:var(--text-muted);">
                                <th style="padding-bottom:10px;">Item</th>
                                <th style="padding-bottom:10px;">Price</th>
                                <th style="padding-bottom:10px; text-align:center;">Qty</th>
                                <th style="padding-bottom:10px; text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom:1px dashed rgba(255,255,255,0.05);">
                                    <td style="padding:12px 0;">
                                        <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($item['variant_name']); ?></div>
                                    </td>
                                    <td style="padding:12px 0;">₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td style="padding:12px 0; text-align:center;"><?php echo $item['quantity']; ?></td>
                                    <td style="padding:12px 0; text-align:right; font-weight:700; color:#fff;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" style="padding:15px 0 5px 0; color:var(--text-muted);">Subtotal</td>
                                <td style="padding:15px 0 5px 0; text-align:right;">₹<?php echo number_format($order['subtotal'], 2); ?></td>
                            </tr>
                            <?php if ($order['discount'] > 0): ?>
                                <tr>
                                    <td colspan="3" style="padding:5px 0; color:var(--success-color);">Discounts</td>
                                    <td style="padding:5px 0; text-align:right; color:var(--success-color);">-₹<?php echo number_format($order['discount'], 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" style="padding:5px 0; color:var(--text-muted);">Shipping</td>
                                <td style="padding:5px 0; text-align:right;"><?php echo $order['shipping'] > 0 ? "₹" . number_format($order['shipping'], 2) : "FREE"; ?></td>
                            </tr>
                            <tr style="font-size:1.1rem; font-weight:800; color:#fff; border-top:1px solid var(--border-color);">
                                <td colspan="3" style="padding:15px 0 0 0;">Grand Total</td>
                                <td style="padding:15px 0 0 0; text-align:right; color:var(--gold-primary);">₹<?php echo number_format($order['total'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel: Fulfill / Update -->
            <div class="glass-card" style="padding:25px; border-radius:8px;">
                <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:20px; color:var(--gold-primary); border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">Fulfillment Status</h3>
                
                <form action="orders.php?order_id=<?php echo $order['id']; ?>" method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    
                    <div class="form-group">
                        <label for="payment_status">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-control">
                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid (Completed)</option>
                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="shipping_status">Shipping Fulfillment</label>
                        <select name="shipping_status" id="shipping_status" class="form-control">
                            <option value="pending" <?php echo $order['shipping_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="shipped" <?php echo $order['shipping_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['shipping_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['shipping_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="courier_name">Courier Partner</label>
                        <input type="text" name="courier_name" id="courier_name" class="form-control" value="<?php echo htmlspecialchars($order['courier_name']); ?>" placeholder="e.g. Bluedart Express">
                    </div>

                    <div class="form-group">
                        <label for="tracking_number">Tracking Code</label>
                        <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="<?php echo htmlspecialchars($order['tracking_number']); ?>" placeholder="AWB tracking number">
                    </div>

                    <button type="submit" name="update_order" class="btn-gold" style="width:100%; margin-top:15px; padding:10px;">
                        Save Status & Notify Customer
                    </button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Orders List View -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-size:1.8rem; text-transform:uppercase;">Order Management</h2>
            <div style="font-size:0.85rem; color:var(--text-muted);">Pending orders and fulfillments</div>
        </div>

        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <?php if (empty($orders)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:30px 0;">No customer orders placed yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Date</th>
                            <th>Customer details</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Shipping</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($ord['order_number']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($ord['created_at'])); ?></td>
                                <td>
                                    <div style="font-weight:600; color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars($ord['customer_name']); ?></div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($ord['customer_phone']); ?></div>
                                </td>
                                <td>₹<?php echo number_format($ord['total'], 2); ?></td>
                                <td>
                                    <span class="admin-badge <?php echo $ord['payment_status'] === 'paid' ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo htmlspecialchars($ord['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-badge <?php echo $ord['shipping_status'] === 'delivered' ? 'badge-completed' : ($ord['shipping_status'] === 'cancelled' ? 'badge-failed' : 'badge-pending'); ?>">
                                        <?php echo htmlspecialchars($ord['shipping_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="orders.php?order_id=<?php echo $ord['id']; ?>" class="btn-outline-gold" style="padding:4px 10px; font-size:0.85rem;">Fulfill</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
