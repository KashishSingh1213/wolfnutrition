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

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Coupons & Promos</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Manage store discount vouchers and promotional codes</p>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($action_error) && $action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:1rem;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 400px; gap:28px; align-items:start;">

        <!-- Coupons Table -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Active Coupons</h3>
                    <p style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:4px;"><?php echo count($coupons); ?> total codes</p>
                </div>
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-percent" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
            </div>

            <?php if (empty($coupons)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-ticket" style="font-size:2.5rem; color:rgba(255,255,255,0.1); margin-bottom:16px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.45); font-size:0.9rem;">No coupon codes created yet.</p>
                    <p style="color:rgba(255,255,255,0.3); font-size:0.8rem; margin-top:6px;">Use the form on the right to create your first coupon.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin-top:0; border:none; border-radius:0;">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Min. Order</th>
                                <th>Expiry</th>
                                <th>Used</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $c): ?>
                                <tr>
                                    <td>
                                        <span style="font-family:'Inter',monospace; font-weight:700; color:#fff; font-size:0.8rem; background:rgba(212,175,55,0.1); padding:3px 8px; border-radius:4px; letter-spacing:0.5px;">
                                            <?php echo htmlspecialchars($c['code']); ?>
                                        </span>
                                    </td>
                                    <td style="text-transform:capitalize; font-size:0.8rem;">
                                        <?php echo htmlspecialchars($c['type']); ?>
                                    </td>
                                    <td>
                                        <span style="color:#4ade80; font-weight:700; font-size:0.85rem;">
                                            <?php echo $c['type'] === 'percentage' ? htmlspecialchars($c['value']) . '%' : '₹' . number_format($c['value'], 2); ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.8rem;">₹<?php echo number_format($c['min_order_amount'], 2); ?></td>
                                    <td style="font-size:0.8rem;">
                                        <?php
                                        $exp = date('Y-m-d', strtotime($c['expiry_date']));
                                        $now = date('Y-m-d');
                                        $isExpired = $exp < $now;
                                        ?>
                                        <span style="color:<?php echo $isExpired ? '#ef4444' : 'rgba(255,255,255,0.7)'; ?>;">
                                            <?php echo date('d M Y', strtotime($c['expiry_date'])); ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.8rem; color:rgba(255,255,255,0.6);"><?php echo $c['used_count']; ?>x</td>
                                    <td>
                                        <span class="admin-badge <?php echo $c['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                            <?php echo $c['status'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <a href="coupons.php?edit_id=<?php echo $c['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem; transition:all 0.2s;">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="coupons.php?toggle_id=<?php echo $c['id']; ?>" title="<?php echo $c['status'] ? 'Disable' : 'Enable'; ?>" style="width:30px; height:30px; border-radius:6px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem; transition:all 0.2s;">
                                                <i class="fas fa-<?php echo $c['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                            </a>
                                            <a href="coupons.php?delete_id=<?php echo $c['id']; ?>" title="Delete" onclick="return confirm('Delete this coupon code?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem; transition:all 0.2s;">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-<?php echo $edit_coupon ? 'edit' : 'plus'; ?>" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">
                    <?php echo $edit_coupon ? 'Edit Coupon' : 'Add New Coupon'; ?>
                </h3>
            </div>

            <form action="coupons.php" method="POST" style="padding:24px;">
                <?php if ($edit_coupon): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_coupon['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="c-code" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Coupon Code *</label>
                    <input type="text" name="code" id="c-code" class="form-control" placeholder="e.g. PACK20" style="font-family:'Inter',monospace; letter-spacing:1px; font-weight:600;"
                        value="<?php echo htmlspecialchars($edit_coupon ? $edit_coupon['code'] : ''); ?>" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="c-type" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Discount Type</label>
                        <select name="type" id="c-type" class="form-control">
                            <option value="percentage" <?php echo ($edit_coupon && $edit_coupon['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                            <option value="flat" <?php echo ($edit_coupon && $edit_coupon['type'] === 'flat') ? 'selected' : ''; ?>>Flat Amount (₹)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="c-val" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Value *</label>
                        <input type="number" step="0.01" name="value" id="c-val" class="form-control" required placeholder="e.g. 10 or 150"
                            value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['value']) : ''; ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="c-min" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Min. Order (₹)</label>
                        <input type="number" step="0.01" name="min_order_amount" id="c-min" class="form-control" placeholder="0.00"
                            value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['min_order_amount']) : '0.00'; ?>">
                    </div>
                    <div class="form-group">
                        <label for="c-max" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Max. Discount (₹)</label>
                        <input type="number" step="0.01" name="max_discount" id="c-max" class="form-control" placeholder="0 = unlimited"
                            value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['max_discount']) : '0.00'; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="c-expiry" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Expiry Date *</label>
                    <input type="date" name="expiry_date" id="c-expiry" class="form-control" required
                        value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['expiry_date']) : ''; ?>">
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" name="<?php echo $edit_coupon ? 'edit_coupon' : 'add_coupon'; ?>" class="btn-gold" style="flex:1; padding:12px 20px;">
                        <i class="fas fa-<?php echo $edit_coupon ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_coupon ? 'Update Coupon' : 'Save Coupon'; ?>
                    </button>
                </div>

                <?php if ($edit_coupon): ?>
                    <a href="coupons.php" style="display:flex; align-items:center; justify-content:center; gap:6px; margin-top:12px; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); font-size:0.8rem; font-weight:500; transition:all 0.2s; text-decoration:none;">
                        <i class="fas fa-times"></i> Cancel Edit
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
