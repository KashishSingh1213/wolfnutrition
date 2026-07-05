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

// Handle Edit Coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_coupon'])) {
    $eid = (int)$_POST['edit_id'];
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $val = (float)$_POST['value'];
    $min = (float)$_POST['min_order_amount'];
    $max = (float)$_POST['max_discount'];
    $expiry = $_POST['expiry_date'];

    if (empty($code) || $val <= 0 || empty($expiry)) {
        $action_error = "Invalid inputs. Code, value, and expiry date are required.";
    } else {
        // check duplicate code excluding self
        $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
        $stmt->execute([$code, $eid]);
        if ($stmt->fetch()) {
            $action_error = "Coupon code already exists.";
        } else {
            $stmt_u = $pdo->prepare("
                UPDATE coupons SET code = ?, type = ?, value = ?, min_order_amount = ?, max_discount = ?, expiry_date = ?
                WHERE id = ?
            ");
            $stmt_u->execute([$code, $type, $val, $min, $max, $expiry, $eid]);
            $action_msg = "Coupon updated successfully.";
        }
    }
}

// Handle Toggle Status
if (isset($_GET['toggle_id'])) {
    $t_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE coupons SET status = NOT status WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Coupon status toggled.";
}

// Handle Delete Coupon
if (isset($_GET['delete_id'])) {
    $c_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$c_id]);
    $action_msg = "Coupon deleted.";
}

// Fetch edit coupon data
$edit_coupon = null;
if (isset($_GET['edit_id'])) {
    $e_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$e_id]);
    $edit_coupon = $stmt->fetch();
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
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
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
                            <th>Status</th>
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
                                    <span class="admin-badge <?php echo $c['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $c['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:10px;">
                                        <a href="coupons.php?edit_id=<?php echo $c['id']; ?>" style="color:var(--gold-primary); font-weight:700;">Edit</a>
                                        <a href="coupons.php?toggle_id=<?php echo $c['id']; ?>" style="color:var(--success-color); font-weight:700;"><?php echo $c['status'] ? 'Disable' : 'Enable'; ?></a>
                                        <a href="coupons.php?delete_id=<?php echo $c['id']; ?>" style="color:var(--danger-color); font-weight:700;" onclick="return confirm('Delete this coupon code?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Coupon Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <?php echo $edit_coupon ? 'Edit Coupon' : 'Add New Coupon'; ?>
            </h3>

            <form action="coupons.php" method="POST">
                <?php if ($edit_coupon): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_coupon['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="c-code">Coupon Code *</label>
                    <input type="text" name="code" id="c-code" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. PACK20"
                        value="<?php echo htmlspecialchars($edit_coupon ? $edit_coupon['code'] : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="c-type">Discount Type</label>
                    <select name="type" id="c-type" class="form-control" style="font-size:0.85rem; padding:8px;">
                        <option value="percentage" <?php echo ($edit_coupon && $edit_coupon['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                        <option value="flat" <?php echo ($edit_coupon && $edit_coupon['type'] === 'flat') ? 'selected' : ''; ?>>Flat Amount (₹)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="c-val">Value *</label>
                    <input type="number" step="0.01" name="value" id="c-val" class="form-control" style="font-size:0.85rem; padding:8px;" required placeholder="e.g. 10 or 150"
                        value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['value']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="c-min">Min. Order Value (₹)</label>
                    <input type="number" step="0.01" name="min_order_amount" id="c-min" class="form-control" style="font-size:0.85rem; padding:8px;"
                        value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['min_order_amount']) : '0.00'; ?>">
                </div>
                <div class="form-group">
                    <label for="c-max">Max. Discount Limit (₹)</label>
                    <input type="number" step="0.01" name="max_discount" id="c-max" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="0 for unlimited"
                        value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['max_discount']) : '0.00'; ?>">
                </div>
                <div class="form-group">
                    <label for="c-expiry">Expiry Date *</label>
                    <input type="date" name="expiry_date" id="c-expiry" class="form-control" style="font-size:0.85rem; padding:8px;" required
                        value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['expiry_date']) : ''; ?>">
                </div>

                <button type="submit" name="<?php echo $edit_coupon ? 'edit_coupon' : 'add_coupon'; ?>" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                    <?php echo $edit_coupon ? 'Update Coupon' : 'Save Coupon'; ?>
                </button>

                <?php if ($edit_coupon): ?>
                    <a href="coupons.php" style="display:block; text-align:center; margin-top:10px; color:var(--text-muted); font-size:0.85rem;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
