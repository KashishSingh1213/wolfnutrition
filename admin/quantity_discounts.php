<?php
// admin/quantity_discounts.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add Tier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tier'])) {
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $min_qty = (int)$_POST['min_qty'];
    $disc = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;

    if ($min_qty <= 1 || $disc <= 0 || $disc > 100) {
        $action_error = "Invalid inputs. Min quantity must be > 1, discount must be 1-100%.";
    } else {
        $stmt_i = $pdo->prepare("INSERT INTO quantity_discounts (product_id, min_qty, discount_percent, status) VALUES (?, ?, ?, ?)");
        $stmt_i->execute([$product_id, $min_qty, $disc, $status]);
        $action_msg = "Discount tier created successfully.";
    }
}

// Handle Edit Tier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tier'])) {
    $eid = (int)$_POST['edit_id'];
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $min_qty = (int)$_POST['min_qty'];
    $disc = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;

    if ($min_qty <= 1 || $disc <= 0 || $disc > 100) {
        $action_error = "Invalid inputs. Min quantity must be > 1, discount must be 1-100%.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE quantity_discounts SET product_id = ?, min_qty = ?, discount_percent = ?, status = ? WHERE id = ?");
        $stmt_u->execute([$product_id, $min_qty, $disc, $status, $eid]);
        $action_msg = "Discount tier updated successfully.";
    }
}

// Handle Delete Tier
if (isset($_GET['delete_id'])) {
    $t_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM quantity_discounts WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Discount tier removed.";
}

// Fetch edit tier data
$edit_tier = null;
if (isset($_GET['edit_id'])) {
    $e_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM quantity_discounts WHERE id = ?");
    $stmt->execute([$e_id]);
    $edit_tier = $stmt->fetch();
}

// Fetch products for dropdown
$stmt = $pdo->prepare("SELECT id, name FROM products ORDER BY name ASC");
$stmt->execute();
$products = $stmt->fetchAll();

// Fetch all tiers with product name
$stmt = $pdo->prepare("
    SELECT qd.*, p.name AS product_name
    FROM quantity_discounts qd
    LEFT JOIN products p ON qd.product_id = p.id
    ORDER BY qd.min_qty ASC
");
$stmt->execute();
$tiers = $stmt->fetchAll();
?>

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Quantity Discounts</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Manage automatic volume-tier discount percentages</p>
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

        <!-- Tiers Table -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Active Tiers</h3>
                    <p style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:4px;"><?php echo count($tiers); ?> discount tiers configured</p>
                </div>
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-layer-group" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
            </div>

            <?php if (empty($tiers)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-layer-group" style="font-size:2.5rem; color:rgba(255,255,255,0.1); margin-bottom:16px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.45); font-size:0.9rem;">No discount tiers created yet.</p>
                    <p style="color:rgba(255,255,255,0.3); font-size:0.8rem; margin-top:6px;">Use the form to set up volume-based pricing.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin-top:0; border:none; border-radius:0;">
                        <thead>
                            <tr>
                                <th>Min. Qty</th>
                                <th>Discount</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiers as $t): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="width:40px; height:40px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; font-weight:800; color:#D4AF37; font-size:1rem;">
                                                <?php echo htmlspecialchars($t['min_qty']); ?>+
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="color:#4ade80; font-weight:800; font-size:1.1rem;">
                                            <?php echo htmlspecialchars($t['discount_percent']); ?>% OFF
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($t['product_id']): ?>
                                            <span style="font-size:0.8rem; color:rgba(255,255,255,0.8); background:rgba(255,255,255,0.05); padding:4px 10px; border-radius:20px;">
                                                <?php echo htmlspecialchars($t['product_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.35); font-style:italic;">Store-wide</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="admin-badge <?php echo $t['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                            <?php echo $t['status'] ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <a href="quantity_discounts.php?edit_id=<?php echo $t['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem; transition:all 0.2s;">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="quantity_discounts.php?delete_id=<?php echo $t['id']; ?>" title="Remove" onclick="return confirm('Remove this volume tier?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem; transition:all 0.2s;">
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
                    <i class="fas fa-<?php echo $edit_tier ? 'edit' : 'plus'; ?>" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">
                    <?php echo $edit_tier ? 'Edit Discount Tier' : 'Setup Discount Tier'; ?>
                </h3>
            </div>

            <form action="quantity_discounts.php" method="POST" style="padding:24px;">
                <?php if ($edit_tier): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_tier['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="product_id" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Product (optional)</label>
                    <select name="product_id" id="product_id" class="form-control">
                        <option value="">Store-wide (all products)</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($edit_tier && $edit_tier['product_id'] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="min_qty" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Min Quantity *</label>
                        <input type="number" name="min_qty" id="min_qty" class="form-control" min="2" required placeholder="e.g. 2"
                            value="<?php echo $edit_tier ? htmlspecialchars($edit_tier['min_qty']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="disc_val" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Discount (%) *</label>
                        <input type="number" step="0.01" name="discount_percent" id="disc_val" class="form-control" required placeholder="e.g. 10"
                            value="<?php echo $edit_tier ? htmlspecialchars($edit_tier['discount_percent']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-top:8px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.85rem; color:rgba(255,255,255,0.7);">
                        <input type="checkbox" name="status" value="1" <?php echo (!$edit_tier || $edit_tier['status']) ? 'checked' : ''; ?> style="accent-color:#D4AF37; width:16px; height:16px;">
                        <span>Enable Tier Immediately</span>
                    </label>
                </div>

                <div style="display:flex; gap:10px; margin-top:12px;">
                    <button type="submit" name="<?php echo $edit_tier ? 'edit_tier' : 'add_tier'; ?>" class="btn-gold" style="flex:1; padding:12px 20px;">
                        <i class="fas fa-<?php echo $edit_tier ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_tier ? 'Update Tier' : 'Save Tier Settings'; ?>
                    </button>
                </div>

                <?php if ($edit_tier): ?>
                    <a href="quantity_discounts.php" style="display:flex; align-items:center; justify-content:center; gap:6px; margin-top:12px; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); font-size:0.8rem; font-weight:500; transition:all 0.2s; text-decoration:none;">
                        <i class="fas fa-times"></i> Cancel Edit
                    </a>
                <?php endif; ?>
            </form>

            <!-- Strategy Tip -->
            <div style="margin:0 24px 24px; padding:16px; border-radius:8px; background:rgba(212,175,55,0.05); border:1px solid rgba(212,175,55,0.1);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.8rem;"></i>
                    <span style="font-size:0.75rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">Strategy Tip</span>
                </div>
                <p style="font-size:0.78rem; color:rgba(255,255,255,0.45); line-height:1.6;">
                    Volume discounts incentivize customers to buy more. Assign tiers to specific products for targeted promotions or leave blank for store-wide offers.
                </p>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
