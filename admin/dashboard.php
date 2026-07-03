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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Dashboard Overview</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Real-time metrics</div>
    </div>

    <!-- Stats Cards -->
    <div class="admin-card-grid">
        <div class="admin-card glass-card">
            <h4>Total Revenue</h4>
            <div class="val">₹<?php echo number_format($revenue, 2); ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Pending Orders</h4>
            <div class="val" style="color:orange;"><?php echo $pending_orders; ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Registered Pack</h4>
            <div class="val" style="color:var(--text-primary);"><?php echo $customers; ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Pending Reviews</h4>
            <div class="val" style="color:yellow;"><?php echo $pending_reviews; ?></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:30px; align-items:start;">
        <!-- Recent Orders -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Recent Orders
            </h3>
            
            <?php if (empty($recent_orders)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No orders placed yet.</p>
            <?php else: ?>
                <table class="admin-table">
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
                                <td style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($ord['order_number']); ?></td>
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
                                    <a href="orders.php?order_id=<?php echo $ord['id']; ?>" class="btn-outline-gold" style="padding:4px 10px; font-size:0.75rem;">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Inventory Alerts -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--danger-color); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
            </h3>
            
            <?php if (empty($low_stock)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">All product stock quantities are healthy.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($low_stock as $item): ?>
                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; border-bottom:1px dashed rgba(255,255,255,0.05); padding-bottom:8px;">
                            <div>
                                <span style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($item['sku']); ?></span>
                                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;"><?php echo htmlspecialchars($item['p_name']); ?> - <?php echo htmlspecialchars($item['size_capsules']); ?></div>
                            </div>
                            <span style="font-weight:800; color:var(--danger-color); font-size:1.1rem;"><?php echo $item['stock_qty']; ?> left</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
