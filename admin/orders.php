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
        <div style="margin-bottom:15px;">
            <a href="orders.php" style="color:rgba(255,255,255,0.45); font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">
                <i class="fas fa-arrow-left" style="font-size:0.75rem;"></i> Back to Orders List
            </a>
        </div>

        <!-- Page Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:35px;">
            <div>
                <h2 style="font-size:1.8rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">Order <span style="color:#D4AF37;">#<?php echo htmlspecialchars($order['order_number']); ?></span></h2>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Placed on <?php echo date('M d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <span class="admin-badge <?php echo $order['shipping_status'] === 'delivered' ? 'badge-completed' : ($order['shipping_status'] === 'cancelled' ? 'badge-failed' : 'badge-pending'); ?>" style="font-size:0.7rem; padding:6px 14px;">
                <?php echo ucfirst(htmlspecialchars($order['shipping_status'])); ?>
            </span>
        </div>

        <?php if ($action_msg): ?>
            <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
                <span style="color:#4ade80; font-weight:600; font-size:0.88rem;"><?php echo htmlspecialchars($action_msg); ?></span>
            </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:30px; align-items:start;">
            <!-- Left Panel -->
            <div>
                <!-- Customer Info Card -->
                <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden; margin-bottom:25px;">
                    <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06);">
                        <h3 style="font-size:0.95rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                            <i class="fas fa-user" style="margin-right:8px;"></i>Customer & Shipping
                        </h3>
                    </div>
                    <div style="padding:22px 28px;">
                        <table style="width:100%; font-size:0.88rem; line-height:1.8;">
                            <tr>
                                <td style="color:rgba(255,255,255,0.45); font-weight:600; width:30%; padding:6px 0;">Name</td>
                                <td style="color:#fff; padding:6px 0;"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            </tr>
                            <tr>
                                <td style="color:rgba(255,255,255,0.45); font-weight:600; padding:6px 0;">Email</td>
                                <td style="color:rgba(255,255,255,0.8); padding:6px 0;"><?php echo htmlspecialchars($order['customer_email']); ?></td>
                            </tr>
                            <tr>
                                <td style="color:rgba(255,255,255,0.45); font-weight:600; padding:6px 0;">Phone</td>
                                <td style="color:rgba(255,255,255,0.8); padding:6px 0;"><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                            </tr>
                            <tr>
                                <td style="color:rgba(255,255,255,0.45); font-weight:600; padding:6px 0;">Address</td>
                                <td style="color:rgba(255,255,255,0.8); padding:6px 0; line-height:1.5;"><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                            </tr>
                            <?php if ($order['note']): ?>
                                <tr>
                                    <td style="color:rgba(255,255,255,0.45); font-weight:600; padding:6px 0;">Seller Notes</td>
                                    <td style="color:#D4AF37; font-style:italic; padding:6px 0;"><?php echo htmlspecialchars($order['note']); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
                    <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06);">
                        <h3 style="font-size:0.95rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                            <i class="fas fa-bag-shopping" style="margin-right:8px;"></i>Ordered Items
                        </h3>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="admin-table" style="margin:0; border:none; border-radius:0;">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th style="text-align:center;">Qty</th>
                                    <th style="text-align:right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td style="padding:14px 20px;">
                                            <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                            <div style="font-size:0.72rem; color:rgba(255,255,255,0.45); margin-top:3px;"><?php echo htmlspecialchars($item['variant_name']); ?></div>
                                        </td>
                                        <td style="padding:14px 20px;">₹<?php echo number_format($item['price'], 2); ?></td>
                                        <td style="padding:14px 20px; text-align:center;"><?php echo $item['quantity']; ?></td>
                                        <td style="padding:14px 20px; text-align:right; font-weight:700; color:#fff;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background:rgba(212,175,55,0.02);">
                                    <td colspan="3" style="padding:14px 20px; color:rgba(255,255,255,0.5); border-top:1px solid rgba(255,255,255,0.06);">Subtotal</td>
                                    <td style="padding:14px 20px; text-align:right; border-top:1px solid rgba(255,255,255,0.06);">₹<?php echo number_format($order['subtotal'], 2); ?></td>
                                </tr>
                                <?php if ($order['discount'] > 0): ?>
                                    <tr>
                                        <td colspan="3" style="padding:10px 20px; color:#4ade80;">Discounts</td>
                                        <td style="padding:10px 20px; text-align:right; color:#4ade80;">-₹<?php echo number_format($order['discount'], 2); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="3" style="padding:10px 20px; color:rgba(255,255,255,0.5);">Shipping</td>
                                    <td style="padding:10px 20px; text-align:right;"><?php echo $order['shipping'] > 0 ? "₹" . number_format($order['shipping'], 2) : '<span style="color:#4ade80;">FREE</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="padding:16px 20px 16px 20px; font-size:1.05rem; font-weight:800; color:#fff; border-top:1px solid rgba(212,175,55,0.15);">Grand Total</td>
                                    <td style="padding:16px 20px; text-align:right; font-size:1.15rem; font-weight:800; color:#D4AF37; border-top:1px solid rgba(212,175,55,0.15);">₹<?php echo number_format($order['total'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Status Update Form -->
            <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden; position:sticky; top:20px;">
                <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h3 style="font-size:0.95rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                        <i class="fas fa-truck" style="margin-right:8px;"></i>Fulfillment Status
                    </h3>
                </div>
                <div style="padding:25px 28px;">
                    <form action="orders.php?order_id=<?php echo $order['id']; ?>" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

                        <div class="form-group" style="margin-bottom:18px;">
                            <label for="payment_status" style="display:block; font-size:0.72rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; font-weight:600;">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-control" style="width:100%; padding:11px 14px; border-radius:8px; font-size:0.88rem;">
                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid (Completed)</option>
                                <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:18px;">
                            <label for="shipping_status" style="display:block; font-size:0.72rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; font-weight:600;">Shipping Fulfillment</label>
                            <select name="shipping_status" id="shipping_status" class="form-control" style="width:100%; padding:11px 14px; border-radius:8px; font-size:0.88rem;">
                                <option value="pending" <?php echo $order['shipping_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="shipped" <?php echo $order['shipping_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['shipping_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['shipping_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:18px;">
                            <label for="courier_name" style="display:block; font-size:0.72rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; font-weight:600;">Courier Partner</label>
                            <input type="text" name="courier_name" id="courier_name" class="form-control" value="<?php echo htmlspecialchars($order['courier_name']); ?>" placeholder="e.g. Bluedart Express" style="width:100%; padding:11px 14px; border-radius:8px; font-size:0.88rem;">
                        </div>

                        <div class="form-group" style="margin-bottom:22px;">
                            <label for="tracking_number" style="display:block; font-size:0.72rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; font-weight:600;">Tracking Code</label>
                            <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="<?php echo htmlspecialchars($order['tracking_number']); ?>" placeholder="AWB tracking number" style="width:100%; padding:11px 14px; border-radius:8px; font-size:0.88rem;">
                        </div>

                        <button type="submit" name="update_order" class="btn-gold" style="width:100%; padding:13px; font-size:0.82rem; border-radius:8px; gap:8px;">
                            <i class="fas fa-save"></i> Save Status & Notify Customer
                        </button>
                    </form>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Orders List View -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:35px;">
            <div>
                <h2 style="font-size:1.8rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">Order Management</h2>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Pending orders and fulfillments</p>
            </div>
            <div style="font-size:0.8rem; color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.03); padding:6px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.06);">
                <?php echo count($orders); ?> total orders
            </div>
        </div>

        <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
            <?php if (empty($orders)): ?>
                <p style="color:rgba(255,255,255,0.45); text-align:center; padding:50px 20px;">
                    <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:12px; color:rgba(255,255,255,0.2);"></i>
                    No customer orders placed yet.
                </p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin:0; border:none; border-radius:0;">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Shipping</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td style="font-weight:700; color:#fff; font-family:var(--font-heading);">#<?php echo htmlspecialchars($ord['order_number']); ?></td>
                                    <td style="color:rgba(255,255,255,0.6);"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                    <td>
                                        <div style="font-weight:600; color:rgba(255,255,255,0.8);"><?php echo htmlspecialchars($ord['customer_name']); ?></div>
                                        <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:2px;"><?php echo htmlspecialchars($ord['customer_phone']); ?></div>
                                    </td>
                                    <td style="font-weight:700; color:#D4AF37;">₹<?php echo number_format($ord['total'], 2); ?></td>
                                    <td>
                                        <?php if ($ord['payment_status'] === 'paid'): ?>
                                            <span class="admin-badge badge-completed" style="background:rgba(74,222,128,0.1); color:#4ade80; border:1px solid rgba(74,222,128,0.2);">Paid</span>
                                        <?php elseif ($ord['payment_status'] === 'failed'): ?>
                                            <span class="admin-badge badge-failed" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);">Failed</span>
                                        <?php else: ?>
                                            <span class="admin-badge badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ord['shipping_status'] === 'delivered'): ?>
                                            <span class="admin-badge badge-completed" style="background:rgba(74,222,128,0.1); color:#4ade80; border:1px solid rgba(74,222,128,0.2);">Delivered</span>
                                        <?php elseif ($ord['shipping_status'] === 'cancelled'): ?>
                                            <span class="admin-badge badge-failed" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);">Cancelled</span>
                                        <?php elseif ($ord['shipping_status'] === 'shipped'): ?>
                                            <span class="admin-badge" style="background:rgba(96,165,250,0.1); color:#60a5fa; border:1px solid rgba(96,165,250,0.2);">Shipped</span>
                                        <?php else: ?>
                                            <span class="admin-badge badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="orders.php?order_id=<?php echo $ord['id']; ?>" class="btn-outline-gold" style="padding:6px 14px; font-size:0.75rem; border-radius:6px;">Fulfill</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>