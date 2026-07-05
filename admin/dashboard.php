<?php
// admin/dashboard.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Fetch stats
// 1. Total Revenue
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE payment_status = 'paid'");
$stmt->execute();
$revenue = (float)$stmt->fetchColumn();

// 2. Pending Orders
$stmt = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE shipping_status = 'pending'");
$stmt->execute();
$pending_orders = (int)$stmt->fetchColumn();

// 3. Registered Customers
$stmt = $pdo->prepare("SELECT COUNT(id) FROM users WHERE role = 'customer'");
$stmt->execute();
$customers = (int)$stmt->fetchColumn();

// 4. Pending Reviews
$stmt = $pdo->prepare("SELECT COUNT(id) FROM reviews WHERE is_approved = 0");
$stmt->execute();
$pending_reviews = (int)$stmt->fetchColumn();

// Fetch Low Stock Warnings (stock_qty <= 10)
$stmt = $pdo->prepare("
    SELECT pv.sku, p.name as p_name, pv.size_capsules, pv.stock_qty 
    FROM product_variants pv 
    JOIN products p ON pv.product_id = p.id 
    WHERE pv.stock_qty <= 10 AND p.is_active = 1
");
$stmt->execute();
$low_stock = $stmt->fetchAll();

// Fetch Recent Orders (last 5)
$stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_orders = $stmt->fetchAll();
?>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:35px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">Dashboard Overview</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Real-time business metrics and alerts</p>
        </div>
        <div style="font-size:0.8rem; color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.03); padding:6px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.06);">
            <i class="fas fa-clock" style="margin-right:5px; color:#D4AF37;"></i> Live Dashboard
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="admin-card-grid">
        <div class="admin-card glass-card" style="position:relative; overflow:hidden;">
            <div style="position:absolute; top:-10px; right:-10px; width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-indian-rupee-sign" style="font-size:1.4rem; color:#D4AF37;"></i>
            </div>
            <h4 style="font-size:0.75rem; letter-spacing:1.2px;">Total Revenue</h4>
            <div class="val" style="font-size:2.2rem;">₹<?php echo number_format($revenue, 2); ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Paid orders only</div>
        </div>
        <div class="admin-card glass-card" style="position:relative; overflow:hidden;">
            <div style="position:absolute; top:-10px; right:-10px; width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-boxes-stacked" style="font-size:1.4rem; color:#D4AF37;"></i>
            </div>
            <h4 style="font-size:0.75rem; letter-spacing:1.2px;">Pending Orders</h4>
            <div class="val" style="font-size:2.2rem; background:linear-gradient(135deg, #ef4444, #f97316); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"><?php echo $pending_orders; ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Awaiting fulfillment</div>
        </div>
        <div class="admin-card glass-card" style="position:relative; overflow:hidden;">
            <div style="position:absolute; top:-10px; right:-10px; width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-users" style="font-size:1.4rem; color:#D4AF37;"></i>
            </div>
            <h4 style="font-size:0.75rem; letter-spacing:1.2px;">Registered Pack</h4>
            <div class="val" style="font-size:2.2rem;"><?php echo $customers; ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Active customers</div>
        </div>
        <div class="admin-card glass-card" style="position:relative; overflow:hidden;">
            <div style="position:absolute; top:-10px; right:-10px; width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-star" style="font-size:1.4rem; color:#D4AF37;"></i>
            </div>
            <h4 style="font-size:0.75rem; letter-spacing:1.2px;">Pending Reviews</h4>
            <div class="val" style="font-size:2.2rem; background:linear-gradient(135deg, #facc15, #eab308); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"><?php echo $pending_reviews; ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Awaiting approval</div>
        </div>
    </div>

    <!-- Two Column Grid -->
    <div style="display:grid; grid-template-columns:2fr 1.1fr; gap:30px; align-items:start;">

        <!-- Recent Orders Table -->
        <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
            <div style="padding:22px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:1rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                    <i class="fas fa-receipt" style="margin-right:8px;"></i>Recent Orders
                </h3>
                <a href="orders.php" style="font-size:0.75rem; color:rgba(255,255,255,0.45); text-decoration:underline; text-underline-offset:2px;">View All &rarr;</a>
            </div>

            <?php if (empty($recent_orders)): ?>
                <p style="color:rgba(255,255,255,0.45); text-align:center; padding:40px 20px;">No orders placed yet.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin:0; border:none; border-radius:0;">
                        <thead>
                            <tr>
                                <th>Order No.</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Shipping</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $ord): ?>
                                <tr>
                                    <td style="font-weight:700; color:#fff; font-family:var(--font-heading);">#<?php echo htmlspecialchars($ord['order_number']); ?></td>
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
                                        <a href="orders.php?order_id=<?php echo $ord['id']; ?>" class="btn-outline-gold" style="padding:5px 12px; font-size:0.72rem; border-radius:6px;">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Alerts -->
        <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
            <div style="padding:22px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:1rem; text-transform:uppercase; letter-spacing:1px; color:#ef4444; margin:0;">
                    <i class="fas fa-triangle-exclamation" style="margin-right:8px;"></i>Low Stock Alerts
                </h3>
                <span style="font-size:0.7rem; color:rgba(255,255,255,0.45); background:rgba(239,68,68,0.08); padding:3px 10px; border-radius:20px; border:1px solid rgba(239,68,68,0.15);">
                    <?php echo count($low_stock); ?> items
                </span>
            </div>

            <?php if (empty($low_stock)): ?>
                <p style="color:rgba(255,255,255,0.45); text-align:center; padding:40px 20px;">
                    <i class="fas fa-check-circle" style="color:#4ade80; display:block; font-size:1.5rem; margin-bottom:10px;"></i>
                    All stock levels healthy
                </p>
            <?php else: ?>
                <div style="padding:10px 0;">
                    <?php foreach ($low_stock as $index => $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 28px; <?php echo $index < count($low_stock) - 1 ? 'border-bottom:1px solid rgba(255,255,255,0.04);' : ''; ?> transition:background 0.2s; cursor:default;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                            <div style="min-width:0;">
                                <div style="font-weight:700; color:#fff; font-size:0.88rem; font-family:var(--font-heading);"><?php echo htmlspecialchars($item['sku']); ?></div>
                                <div style="font-size:0.72rem; color:rgba(255,255,255,0.45); margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($item['p_name']); ?> &mdash; <?php echo htmlspecialchars($item['size_capsules']); ?></div>
                            </div>
                            <div style="text-align:right; flex-shrink:0; margin-left:12px;">
                                <div style="font-weight:800; color:#ef4444; font-size:1.05rem; font-family:var(--font-heading);"><?php echo $item['stock_qty']; ?></div>
                                <div style="font-size:0.6rem; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:0.5px;">units left</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>