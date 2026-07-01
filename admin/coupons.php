<?php
// admin/coupons.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add Coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $val = (float)$_POST['value'];
    $min = (float)$_POST['min_order_amount'];
    $max = (float)$_POST['max_discount'];
    $expiry = $_POST['expiry_date'];

    if (empty($code) || $val <= 0 || empty($expiry)) {
        $action_error = "Invalid inputs. Code, value, and expiry date are required.";
    } else {
        // check duplicate
        $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            $action_error = "Coupon code already exists.";
        } else {
            $stmt_i = $pdo->prepare("
                INSERT INTO coupons (code, type, value, min_order_amount, max_discount, expiry_date, usage_limit, status) 
                VALUES (?, ?, ?, ?, ?, ?, 1000, 1)
            ");
            $stmt_i->execute([$code, $type, $val, $min, $max, $expiry]);
            $action_msg = "Coupon code added successfully.";
        }
    }
}

// Handle Delete Coupon
if (isset($_GET['delete_id'])) {
    $c_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$c_id]);
    $action_msg = "Coupon deleted.";
}

// Fetch all coupons
$stmt = $pdo->prepare("SELECT * FROM coupons ORDER BY expiry_date DESC");
$stmt->execute();
$coupons = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Coupons & Promos</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage store discount vouchers</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:30px; align-items:start;">
        <!-- Coupons list -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Active Coupons
            </h3>
            
            <?php if (empty($coupons)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No coupon codes created yet.</p>
            <?php else: ?>
                <table class="admin-table" style="font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min. Order</th>
                            <th>Expiry</th>
                            <th>Used</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td><strong style="color:#fff; font-size:1rem;"><?php echo htmlspecialchars($c['code']); ?></strong></td>
                                <td style="text-transform:capitalize;"><?php echo htmlspecialchars($c['type']); ?></td>
                                <td><?php echo $c['type'] === 'percentage' ? htmlspecialchars($c['value']) . '%' : '₹' . number_format($c['value'], 2); ?></td>
                                <td>₹<?php echo number_format($c['min_order_amount'], 2); ?></td>
                                <td><?php echo date('d-M-Y', strtotime($c['expiry_date'])); ?></td>
                                <td><?php echo $c['used_count']; ?> times</td>
                                <td>
                                    <a href="coupons.php?delete_id=<?php echo $c['id']; ?>" style="color:var(--danger-color); font-weight:700;" onclick="return confirm('Delete this coupon code?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add Coupon Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Add New Coupon
            </h3>
            
            <form action="coupons.php" method="POST">
                <div class="form-group">
                    <label for="c-code">Coupon Code *</label>
                    <input type="text" name="code" id="c-code" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. PACK20" required>
                </div>
                <div class="form-group">
                    <label for="c-type">Discount Type</label>
                    <select name="type" id="c-type" class="form-control" style="font-size:0.85rem; padding:8px;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="flat">Flat Amount (₹)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="c-val">Value *</label>
                    <input type="number" step="0.01" name="value" id="c-val" class="form-control" style="font-size:0.85rem; padding:8px;" required placeholder="e.g. 10 or 150">
                </div>
                <div class="form-group">
                    <label for="c-min">Min. Order Value (₹)</label>
                    <input type="number" step="0.01" name="min_order_amount" id="c-min" class="form-control" style="font-size:0.85rem; padding:8px;" value="0.00">
                </div>
                <div class="form-group">
                    <label for="c-max">Max. Discount Limit (₹)</label>
                    <input type="number" step="0.01" name="max_discount" id="c-max" class="form-control" style="font-size:0.85rem; padding:8px;" value="0.00" placeholder="0 for unlimited">
                </div>
                <div class="form-group">
                    <label for="c-expiry">Expiry Date *</label>
                    <input type="date" name="expiry_date" id="c-expiry" class="form-control" style="font-size:0.85rem; padding:8px;" required>
                </div>
                
                <button type="submit" name="add_coupon" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                    Save Coupon
                </button>
            </form>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
