<?php
// admin/dashboard.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Fetch stats
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE payment_status = 'paid'");
$stmt->execute();
$revenue = (float)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE shipping_status = 'pending'");
$stmt->execute();
$pending_orders = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(id) FROM users WHERE role = 'customer'");
$stmt->execute();
$customers = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(id) FROM reviews WHERE is_approved = 0");
$stmt->execute();
$pending_reviews = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(id) FROM products WHERE is_active = 1");
$stmt->execute();
$total_products = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$today_orders = (int)$stmt->fetchColumn();

// Low Stock
$stmt = $pdo->prepare("
    SELECT pv.sku, p.name as p_name, pv.size_capsules, pv.stock_qty 
    FROM product_variants pv 
    JOIN products p ON pv.product_id = p.id 
    WHERE pv.stock_qty <= 10 AND p.is_active = 1
    ORDER BY pv.stock_qty ASC
");
$stmt->execute();
$low_stock = $stmt->fetchAll();

// Recent Orders
$stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

$greeting = 'Good ' . ((date('H') < 12) ? 'morning' : ((date('H') < 17) ? 'afternoon' : 'evening'));
?>

<style>
    /* ═══════════════════════════════════════════════
       DASHBOARD — Responsive Styles
       ═══════════════════════════════════════════════ */

    /* ── Welcome Banner ── */
    .dash-welcome {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(212,175,55,0.08) 0%, rgba(212,175,55,0.02) 100%);
        border: 1px solid rgba(212,175,55,0.12);
        border-radius: 14px;
        gap: 16px;
    }
    .dash-welcome-text h2 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .dash-welcome-text h2 span { color: #D4AF37; }
    .dash-welcome-text p {
        font-size: 0.82rem;
        color: rgba(255,255,255,0.4);
    }
    .dash-welcome-date {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.35);
        background: rgba(255,255,255,0.04);
        padding: 8px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.06);
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ── Stat Cards ── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: rgba(18,18,18,0.6);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 14px;
        padding: 20px 22px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .stat-card:hover {
        border-color: rgba(212,175,55,0.2);
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    .stat-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .stat-icon.gold { background: rgba(212,175,55,0.1); color: #D4AF37; }
    .stat-icon.red { background: rgba(239,68,68,0.1); color: #ef4444; }
    .stat-icon.green { background: rgba(74,222,128,0.1); color: #4ade80; }
    .stat-icon.blue { background: rgba(96,165,250,0.1); color: #60a5fa; }
    .stat-badge {
        font-size: 0.62rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .stat-badge.warn { background: rgba(251,191,36,0.1); color: #fbbf24; }
    .stat-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: rgba(255,255,255,0.4);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 6px;
    }
    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .stat-value.gold {
        background: linear-gradient(135deg, #D4AF37, #F2D06B);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .stat-value.white { color: #fff; }
    .stat-value.red { color: #ef4444; }
    .stat-value.green { color: #4ade80; }
    .stat-sub {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.35);
        margin-top: 6px;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        opacity: 0.03;
        pointer-events: none;
    }
    .stat-card.gold-glow::after { background: #D4AF37; }
    .stat-card.red-glow::after { background: #ef4444; }
    .stat-card.green-glow::after { background: #4ade80; }
    .stat-card.blue-glow::after { background: #60a5fa; }

    /* ── Quick Actions ── */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 28px;
    }
    .quick-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        background: rgba(18,18,18,0.6);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 12px;
        text-decoration: none;
        color: rgba(255,255,255,0.6);
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .quick-card:hover {
        border-color: rgba(212,175,55,0.25);
        background: rgba(212,175,55,0.04);
        color: #D4AF37;
    }
    .quick-card i {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: rgba(212,175,55,0.08);
        color: #D4AF37;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    /* ── Content Grid ── */
    .content-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 20px;
        align-items: start;
    }

    /* ── Panels ── */
    .panel {
        background: rgba(18,18,18,0.6);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 14px;
        overflow: hidden;
    }
    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 22px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #fff;
    }
    .panel-title i { font-size: 0.82rem; }
    .panel-link {
        font-size: 0.72rem;
        color: rgba(255,255,255,0.35);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .panel-link:hover { color: #D4AF37; }

    /* ── Order Rows (Desktop Grid) ── */
    .order-row {
        display: grid;
        grid-template-columns: 0.8fr 1.2fr 0.7fr 0.7fr 0.7fr 0.6fr;
        align-items: center;
        padding: 12px 22px;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        transition: background 0.15s;
        gap: 10px;
    }
    .order-row:last-child { border-bottom: none; }
    .order-row:hover { background: rgba(255,255,255,0.02); }
    .order-row-head {
        padding: 9px 22px;
        background: rgba(18,18,18,0.8);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgba(255,255,255,0.35);
    }
    .order-num { font-weight: 700; color: #fff; font-size: 0.82rem; }
    .order-customer { font-size: 0.8rem; color: rgba(255,255,255,0.7); }
    .order-phone { font-size: 0.68rem; color: rgba(255,255,255,0.3); margin-top: 2px; }
    .order-total { font-weight: 700; color: #D4AF37; font-size: 0.85rem; }
    .manage-btn {
        display: inline-flex;
        padding: 4px 12px;
        font-size: 0.7rem;
        font-weight: 600;
        color: #D4AF37;
        background: transparent;
        border: 1px solid rgba(212,175,55,0.25);
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .manage-btn:hover {
        background: rgba(212,175,55,0.1);
        border-color: #D4AF37;
    }

    /* ── Stock Items ── */
    .stock-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 22px;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        transition: background 0.15s;
        gap: 12px;
    }
    .stock-item:last-child { border-bottom: none; }
    .stock-item:hover { background: rgba(255,255,255,0.02); }
    .stock-sku { font-weight: 700; color: #fff; font-size: 0.82rem; margin-bottom: 2px; }
    .stock-name { font-size: 0.7rem; color: rgba(255,255,255,0.35); }
    .stock-qty { font-weight: 800; font-size: 1rem; text-align: right; flex-shrink: 0; }
    .stock-qty.critical { color: #ef4444; }
    .stock-qty.low { color: #fbbf24; }
    .stock-unit { font-size: 0.58rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.5px; }

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255,255,255,0.3);
    }
    .empty-state i { font-size: 1.8rem; margin-bottom: 10px; display: block; }
    .empty-state p { font-size: 0.82rem; }

    /* ── Utility ── */
    .d-hide-sm { display: block; }
    .d-mobile-row { display: none; }

    /* ═══════════════════════════════════════════════
       RESPONSIVE — Laptop (≤1200px)
       ═══════════════════════════════════════════════ */
    @media (max-width: 1200px) {
        .stat-grid { grid-template-columns: repeat(2, 1fr); }
        .content-grid { grid-template-columns: 1fr; }
    }

    /* ═══════════════════════════════════════════════
       RESPONSIVE — Tablet (≤1024px)
       ═══════════════════════════════════════════════ */
    @media (max-width: 1024px) {
        .dash-welcome { padding: 20px 22px; }
        .dash-welcome-text h2 { font-size: 1.15rem; }
        .stat-grid { gap: 12px; margin-bottom: 22px; }
        .stat-card { padding: 18px 20px; }
        .stat-value { font-size: 1.4rem; }
        .quick-grid { gap: 10px; margin-bottom: 22px; }
        .content-grid { gap: 16px; }
        .order-row { grid-template-columns: 1fr 1fr 0.7fr 0.7fr; }
        .order-row > span:nth-child(5),
        .order-row > span:nth-child(6),
        .order-row-head > span:nth-child(5),
        .order-row-head > span:nth-child(6) { display: none; }
    }

    /* ═══════════════════════════════════════════════
       RESPONSIVE — Small Tablet (≤768px)
       ═══════════════════════════════════════════════ */
    @media (max-width: 768px) {
        .dash-welcome {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            padding: 18px 20px;
        }
        .dash-welcome-text h2 { font-size: 1.05rem; }

        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .stat-card { padding: 16px; }
        .stat-icon { width: 36px; height: 36px; font-size: 0.85rem; }
        .stat-value { font-size: 1.25rem; }
        .stat-label { font-size: 0.65rem; }

        .quick-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .quick-card { padding: 12px 16px; font-size: 0.78rem; }

        /* Orders → card layout */
        .order-row-head { display: none; }
        .d-hide-sm { display: none; }
        .order-row {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 14px 18px;
            border-radius: 10px;
            margin: 0 12px 8px;
            border: 1px solid rgba(255,255,255,0.04);
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .order-row:hover { background: rgba(255,255,255,0.02); }
        .d-mobile-row {
            display: flex;
            grid-column: 1 / -1;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.04);
            margin-top: 4px;
        }

        .stock-item { padding: 12px 18px; }
    }

    /* ═══════════════════════════════════════════════
       RESPONSIVE — Mobile (≤480px)
       ═══════════════════════════════════════════════ */
    @media (max-width: 480px) {
        .dash-welcome { padding: 16px; }
        .dash-welcome-text h2 { font-size: 0.95rem; }
        .dash-welcome-date { font-size: 0.7rem; padding: 6px 12px; }

        .stat-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .stat-card { padding: 14px 14px; border-radius: 12px; }
        .stat-icon { width: 32px; height: 32px; font-size: 0.8rem; border-radius: 9px; }
        .stat-card-top { margin-bottom: 10px; }
        .stat-label { font-size: 0.6rem; margin-bottom: 4px; }
        .stat-value { font-size: 1.1rem; }
        .stat-sub { font-size: 0.62rem; margin-top: 4px; }

        .quick-grid { gap: 6px; margin-bottom: 20px; }
        .quick-card {
            padding: 12px 14px;
            font-size: 0.75rem;
            gap: 10px;
            border-radius: 10px;
        }
        .quick-card i { width: 30px; height: 30px; font-size: 0.8rem; border-radius: 8px; }

        .panel-header { padding: 14px 16px; }
        .panel-title { font-size: 0.82rem; }

        .order-row {
            grid-template-columns: 1fr;
            gap: 6px;
            padding: 14px 14px;
            margin: 0 10px 6px;
        }
        .order-num { font-size: 0.85rem; }
        .order-total { font-size: 0.9rem; }
        .manage-btn { padding: 6px 12px; }

        .stock-item { padding: 10px 14px; flex-wrap: wrap; }
        .stock-sku { font-size: 0.78rem; }
        .stock-name { font-size: 0.65rem; }
        .d-mobile-row { padding-top: 6px; }
    }
</style>

<!-- Welcome Banner -->
<div class="dash-welcome">
    <div class="dash-welcome-text">
        <h2><?php echo $greeting; ?>, <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></h2>
        <p>Here's what's happening with your store today.</p>
    </div>
    <div class="dash-welcome-date">
        <i class="fas fa-calendar-day" style="margin-right:6px; color:#D4AF37;"></i>
        <?php echo date('l, F j, Y'); ?>
    </div>
</div>

<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-card gold-glow">
        <div class="stat-card-top">
            <div class="stat-icon gold"><i class="fas fa-indian-rupee-sign"></i></div>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value gold">₹<?php echo number_format($revenue, 0); ?></div>
        <div class="stat-sub">From paid orders</div>
    </div>
    <div class="stat-card red-glow">
        <div class="stat-card-top">
            <div class="stat-icon red"><i class="fas fa-boxes-stacked"></i></div>
            <?php if ($pending_orders > 0): ?>
                <span class="stat-badge warn"><?php echo $pending_orders; ?> new</span>
            <?php endif; ?>
        </div>
        <div class="stat-label">Pending Orders</div>
        <div class="stat-value red"><?php echo $pending_orders; ?></div>
        <div class="stat-sub">Awaiting fulfillment</div>
    </div>
    <div class="stat-card green-glow">
        <div class="stat-card-top">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-label">Customers</div>
        <div class="stat-value green"><?php echo number_format($customers); ?></div>
        <div class="stat-sub">Registered users</div>
    </div>
    <div class="stat-card blue-glow">
        <div class="stat-card-top">
            <div class="stat-icon blue"><i class="fas fa-bag-shopping"></i></div>
        </div>
        <div class="stat-label">Today's Orders</div>
        <div class="stat-value white"><?php echo $today_orders; ?></div>
        <div class="stat-sub"><?php echo $total_products; ?> products live</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-grid">
    <a href="product_add.php" class="quick-card">
        <i class="fas fa-plus"></i>
        Add Product
    </a>
    <a href="orders.php" class="quick-card">
        <i class="fas fa-eye"></i>
        View Orders
    </a>
    <a href="reports.php" class="quick-card">
        <i class="fas fa-chart-line"></i>
        Sales Reports
    </a>
</div>

<!-- Two Column Layout -->
<div class="content-grid">

    <!-- Recent Orders -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-receipt" style="color:#D4AF37;"></i>
                Recent Orders
            </div>
            <a href="orders.php" class="panel-link">View all &rarr;</a>
        </div>

        <?php if (empty($recent_orders)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                No orders placed yet
            </div>
        <?php else: ?>
            <div class="order-row order-row-head">
                <span>Order</span>
                <span>Customer</span>
                <span>Total</span>
                <span>Payment</span>
                <span>Shipping</span>
                <span></span>
            </div>
            <?php foreach ($recent_orders as $ord): ?>
                <div class="order-row">
                    <span class="order-num">#<?php echo htmlspecialchars($ord['order_number']); ?></span>
                    <div>
                        <div class="order-customer"><?php echo htmlspecialchars($ord['customer_name']); ?></div>
                        <div class="order-phone"><?php echo htmlspecialchars($ord['customer_phone']); ?></div>
                    </div>
                    <span class="order-total">₹<?php echo number_format($ord['total'], 0); ?></span>
                    <span>
                        <?php if ($ord['payment_status'] === 'paid'): ?>
                            <span class="admin-badge badge-completed">Paid</span>
                        <?php elseif ($ord['payment_status'] === 'failed'): ?>
                            <span class="admin-badge badge-failed">Failed</span>
                        <?php else: ?>
                            <span class="admin-badge badge-pending">Pending</span>
                        <?php endif; ?>
                    </span>
                    <span class="d-hide-sm">
                        <?php if ($ord['shipping_status'] === 'delivered'): ?>
                            <span class="admin-badge badge-completed">Delivered</span>
                        <?php elseif ($ord['shipping_status'] === 'cancelled'): ?>
                            <span class="admin-badge badge-failed">Cancelled</span>
                        <?php elseif ($ord['shipping_status'] === 'shipped'): ?>
                            <span class="admin-badge" style="background:rgba(96,165,250,0.1); color:#60a5fa; border:1px solid rgba(96,165,250,0.2);">Shipped</span>
                        <?php else: ?>
                            <span class="admin-badge badge-pending">Pending</span>
                        <?php endif; ?>
                    </span>
                    <a href="orders.php?order_id=<?php echo $ord['id']; ?>" class="manage-btn d-hide-sm">Manage</a>
                    <!-- Mobile: shipping + manage row -->
                    <div class="d-mobile-row">
                        <span>
                            <?php if ($ord['shipping_status'] === 'delivered'): ?>
                                <span class="admin-badge badge-completed">Delivered</span>
                            <?php elseif ($ord['shipping_status'] === 'cancelled'): ?>
                                <span class="admin-badge badge-failed">Cancelled</span>
                            <?php elseif ($ord['shipping_status'] === 'shipped'): ?>
                                <span class="admin-badge" style="background:rgba(96,165,250,0.1); color:#60a5fa; border:1px solid rgba(96,165,250,0.2);">Shipped</span>
                            <?php else: ?>
                                <span class="admin-badge badge-pending">Pending</span>
                            <?php endif; ?>
                        </span>
                        <a href="orders.php?order_id=<?php echo $ord['id']; ?>" class="manage-btn">Manage</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Low Stock Alerts -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i>
                Low Stock
            </div>
            <?php if (count($low_stock) > 0): ?>
                <span style="font-size:0.68rem; color:rgba(255,255,255,0.35); background:rgba(239,68,68,0.08); padding:3px 10px; border-radius:20px; border:1px solid rgba(239,68,68,0.15);">
                    <?php echo count($low_stock); ?> items
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($low_stock)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color:#4ade80;"></i>
                All stock levels healthy
            </div>
        <?php else: ?>
            <?php foreach ($low_stock as $item): ?>
                <div class="stock-item">
                    <div>
                        <div class="stock-sku"><?php echo htmlspecialchars($item['sku']); ?></div>
                        <div class="stock-name"><?php echo htmlspecialchars($item['p_name']); ?> &mdash; <?php echo htmlspecialchars($item['size_capsules']); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div class="stock-qty <?php echo $item['stock_qty'] <= 3 ? 'critical' : 'low'; ?>"><?php echo $item['stock_qty']; ?></div>
                        <div class="stock-unit">left</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
