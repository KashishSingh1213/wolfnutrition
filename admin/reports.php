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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Financial Reports</h2>
        <!-- Export button -->
        <a href="reports.php?export=csv" class="btn-gold" style="padding:10px 20px; font-size:0.85rem;">
            <i class="fas fa-file-csv"></i> Export Orders registry to CSV
        </a>
    </div>

    <!-- Reports Cards -->
    <div class="admin-card-grid">
        <div class="admin-card glass-card">
            <h4>Lifetime Paid Revenue</h4>
            <div class="val">₹<?php echo number_format($rev_lifetime, 2); ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Average Order Value</h4>
            <div class="val" style="color:var(--gold-muted);">₹<?php echo number_format($aov, 2); ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Completed Paid Orders</h4>
            <div class="val" style="color:var(--success-color);"><?php echo $completed_orders; ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Cancelled / Failed Orders</h4>
            <div class="val" style="color:var(--danger-color);"><?php echo $failed_orders; ?></div>
        </div>
    </div>

    <!-- Bundle sales vs single sales -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; align-items:start;">
        <!-- Stack Combos Performance -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Bundle Combos performance
            </h3>
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem; line-height:2;">
                <tbody>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="color:var(--text-muted); font-weight:600; width:50%;">Combos Sold</td>
                        <td style="color:#fff; font-weight:700;"><?php echo $bundle_sales_count; ?> combos</td>
                    </tr>
                    <tr>
                        <td style="color:var(--text-muted); font-weight:600;">Combo Revenue generated</td>
                        <td style="color:var(--success-color); font-weight:700;">₹<?php echo number_format($bundle_revenue_sum, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <p style="font-size:0.8rem; color:var(--text-muted); margin-top:15px; line-height:1.4;">
                Combos are high average order value (AOV) multipliers. Monitor performance ratios to schedule home banner promotions.
            </p>
        </div>

        <!-- Sales Analytics explanation -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:#fff; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                CSV Export Schema
            </h3>
            <p style="font-size:0.85rem; line-height:1.6; margin-bottom:10px;">
                The downloadable CSV file contains all client registrations and full shipment fields for direct integration into shipping networks (such as Shiprocket, Bluedart, or Delhivery).
            </p>
            <p style="font-size:0.85rem; line-height:1.6;">
                Use the exported registry to compile monthly sales tax returns and warehouse dispatch labels.
            </p>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
