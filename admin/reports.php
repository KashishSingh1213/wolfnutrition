<?php
// admin/reports.php
require_once __DIR__ . '/../includes/functions.php';

// Check CSV export action first, before rendering header
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit();
    }

    // Fetch all orders
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll();

    // Set headers to trigger file download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=wolf_nutrition_sales_report_' . date('Y-m-d') . '.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write headers
    fputcsv($output, [
        'Order Number', 'Date', 'Customer Name', 'Email', 'Phone',
        'Subtotal', 'Discount', 'Shipping', 'Total Amount',
        'Payment Method', 'Payment Status', 'Shipping Status', 'Pincode', 'Address'
    ]);

    // Write rows
    foreach ($orders as $o) {
        fputcsv($output, [
            $o['order_number'],
            $o['created_at'],
            $o['customer_name'],
            $o['customer_email'],
            $o['customer_phone'],
            $o['subtotal'],
            $o['discount'],
            $o['shipping'],
            $o['total'],
            $o['payment_method'],
            $o['payment_status'],
            $o['shipping_status'],
            $o['pincode'],
            $o['shipping_address']
        ]);
    }

    fclose($output);
    exit();
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Calculate sales performance statistics
// 1. Total lifetime revenue
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE payment_status = 'paid'");
$stmt->execute();
$rev_lifetime = (float)$stmt->fetchColumn();

// 2. Average Order Value
$stmt = $pdo->prepare("SELECT AVG(total) FROM orders WHERE payment_status = 'paid'");
$stmt->execute();
$aov = (float)$stmt->fetchColumn();

// 3. Completed orders count
$stmt = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE payment_status = 'paid'");
$stmt->execute();
$completed_orders = (int)$stmt->fetchColumn();

// 4. Failed/Cancelled Orders
$stmt = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE shipping_status = 'cancelled' OR payment_status = 'failed'");
$stmt->execute();
$failed_orders = (int)$stmt->fetchColumn();

// 5. Bundle Sales Performance
$stmt = $pdo->prepare("
    SELECT COUNT(oi.id) as bundle_sales, SUM(oi.price * oi.quantity) as bundle_rev
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.bundle_id IS NOT NULL AND o.payment_status = 'paid'
");
$stmt->execute();
$b_performance = $stmt->fetch();
$bundle_sales_count = (int)$b_performance['bundle_sales'];
$bundle_revenue_sum = (float)$b_performance['bundle_rev'];
?>

    <style>
        @media (max-width: 1024px) {
            .rpt-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 12px; }
            .rpt-two-col { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 768px) {
            .rpt-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 12px; }
            .rpt-two-col { grid-template-columns: 1fr !important; }
        }
    </style>

    <!-- Page Header -->
    <div class="rpt-page-header" style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:35px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">Financial Reports</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Revenue analytics and sales export tools</p>
        </div>
        <a href="reports.php?export=csv" class="btn-gold" style="padding:10px 22px; font-size:0.8rem; border-radius:8px; display:inline-flex; align-items:center; gap:8px; text-decoration:none;">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="admin-card-grid">
        <div class="admin-card glass-card">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <i class="fas fa-indian-rupee-sign" style="font-size:1rem; color:#D4AF37;"></i>
            </div>
            <h4 style="font-size:0.72rem; letter-spacing:1px; color:rgba(255,255,255,0.45); margin-bottom:8px;">LIFETIME REVENUE</h4>
            <div class="val" style="font-size:2rem;">₹<?php echo number_format($rev_lifetime, 2); ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">All paid orders</div>
        </div>
        <div class="admin-card glass-card">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <i class="fas fa-chart-line" style="font-size:1rem; color:#D4AF37;"></i>
            </div>
            <h4 style="font-size:0.72rem; letter-spacing:1px; color:rgba(255,255,255,0.45); margin-bottom:8px;">AVG ORDER VALUE</h4>
            <div class="val" style="font-size:2rem;">₹<?php echo number_format($aov, 2); ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Per transaction avg</div>
        </div>
        <div class="admin-card glass-card">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <i class="fas fa-circle-check" style="font-size:1rem; color:#4ade80;"></i>
            </div>
            <h4 style="font-size:0.72rem; letter-spacing:1px; color:rgba(255,255,255,0.45); margin-bottom:8px;">COMPLETED ORDERS</h4>
            <div class="val" style="font-size:2rem; background:linear-gradient(135deg, #4ade80, #22c55e); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"><?php echo $completed_orders; ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Paid & fulfilled</div>
        </div>
        <div class="admin-card glass-card">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <i class="fas fa-circle-xmark" style="font-size:1rem; color:#ef4444;"></i>
            </div>
            <h4 style="font-size:0.72rem; letter-spacing:1px; color:rgba(255,255,255,0.45); margin-bottom:8px;">FAILED ORDERS</h4>
            <div class="val" style="font-size:2rem; background:linear-gradient(135deg, #ef4444, #dc2626); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"><?php echo $failed_orders; ?></div>
            <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); margin-top:6px;">Cancelled or failed</div>
        </div>
    </div>

    <!-- Two Column Grid -->
    <div class="rpt-two-col" style="display:grid; grid-template-columns:1fr 1fr; gap:28px; align-items:start;">

        <!-- Combo Performance -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:22px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-cubes" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">Combo Performance</h3>
            </div>
            <div style="padding:28px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div style="background:rgba(212,175,55,0.04); border:1px solid rgba(212,175,55,0.08); border-radius:10px; padding:20px; text-align:center;">
                        <div style="font-size:0.65rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Combos Sold</div>
                        <div style="font-size:2rem; font-weight:800; color:#fff;"><?php echo $bundle_sales_count; ?></div>
                        <div style="font-size:0.7rem; color:rgba(255,255,255,0.35); margin-top:4px;">bundle items</div>
                    </div>
                    <div style="background:rgba(74,222,128,0.04); border:1px solid rgba(74,222,128,0.08); border-radius:10px; padding:20px; text-align:center;">
                        <div style="font-size:0.65rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Combo Revenue</div>
                        <div style="font-size:2rem; font-weight:800; color:#4ade80;">₹<?php echo number_format($bundle_revenue_sum, 2); ?></div>
                        <div style="font-size:0.7rem; color:rgba(255,255,255,0.35); margin-top:4px;">generated from bundles</div>
                    </div>
                </div>
                <div style="background:rgba(212,175,55,0.03); border:1px dashed rgba(212,175,55,0.12); border-radius:8px; padding:14px 18px;">
                    <p style="font-size:0.78rem; color:rgba(255,255,255,0.5); line-height:1.6; margin:0;">
                        <i class="fas fa-lightbulb" style="color:#D4AF37; margin-right:6px;"></i>
                        Combos are high AOV multipliers. Monitor performance ratios to schedule home banner promotions.
                    </p>
                </div>
            </div>
        </div>

        <!-- CSV Export Info -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:22px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-file-export" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">CSV Export Details</h3>
            </div>
            <div style="padding:28px;">
                <div style="margin-bottom:20px;">
                    <div style="font-size:0.75rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">Included Fields</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        <?php
                        $fields = ['Order Number', 'Date', 'Customer Name', 'Email', 'Phone', 'Subtotal', 'Discount', 'Shipping', 'Total', 'Payment Method', 'Payment Status', 'Shipping Status', 'Pincode', 'Address'];
                        foreach ($fields as $field): ?>
                            <span style="font-size:0.68rem; color:rgba(255,255,255,0.6); background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06); padding:4px 10px; border-radius:4px;"><?php echo $field; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,0.06); padding-top:18px;">
                    <p style="font-size:0.82rem; line-height:1.7; color:rgba(255,255,255,0.5); margin-bottom:12px;">
                        The downloadable CSV contains all order records with full shipment fields, ready for direct integration with shipping networks (Shiprocket, Bluedart, Delhivery).
                    </p>
                    <p style="font-size:0.82rem; line-height:1.7; color:rgba(255,255,255,0.5); margin:0;">
                        Use the exported data to compile monthly sales tax returns and generate warehouse dispatch labels.
                    </p>
                </div>
                <div style="margin-top:22px;">
                    <a href="reports.php?export=csv" class="btn-gold" style="width:100%; padding:12px; font-size:0.8rem; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:8px; text-decoration:none;">
                        <i class="fas fa-download"></i> Download Sales Report
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
