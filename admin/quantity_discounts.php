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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Quantity Discounts</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage automatic volume-tier discount percents</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:30px; align-items:start;">
        <!-- Tiers list -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Active Quantity Tiers
            </h3>

            <?php if (empty($tiers)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No automatic volume tiers created yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Min. Qty</th>
                            <th>Discount</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiers as $t): ?>
                            <tr>
                                <td><strong style="color:#fff; font-size:1.1rem;"><?php echo htmlspecialchars($t['min_qty']); ?>+</strong></td>
                                <td><strong style="color:var(--success-color); font-size:1.1rem;"><?php echo htmlspecialchars($t['discount_percent']); ?>% OFF</strong></td>
                                <td style="font-size:0.85rem; color:<?php echo $t['product_id'] ? 'rgba(255,255,255,0.8)' : 'var(--text-muted)'; ?>;">
                                    <?php echo $t['product_id'] ? htmlspecialchars($t['product_name']) : '<em>Store-wide</em>'; ?>
                                </td>
                                <td>
                                    <span class="admin-badge <?php echo $t['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $t['status'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:10px;">
                                        <a href="quantity_discounts.php?edit_id=<?php echo $t['id']; ?>" style="color:var(--gold-primary); font-weight:700;">Edit</a>
                                        <a href="quantity_discounts.php?delete_id=<?php echo $t['id']; ?>" style="color:var(--danger-color); font-weight:700;" onclick="return confirm('Remove this volume tier?')">Remove</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Tier Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <?php echo $edit_tier ? 'Edit Discount Tier' : 'Setup Discount Tier'; ?>
            </h3>

            <form action="quantity_discounts.php" method="POST">
                <?php if ($edit_tier): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_tier['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="product_id">Product (optional)</label>
                    <select name="product_id" id="product_id" class="form-control" style="font-size:0.85rem; padding:8px;">
                        <option value="">Store-wide (all products)</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($edit_tier && $edit_tier['product_id'] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_qty">Min Quantity *</label>
                    <input type="number" name="min_qty" id="min_qty" class="form-control" style="font-size:0.85rem; padding:8px;" min="2" required placeholder="e.g. 2"
                        value="<?php echo $edit_tier ? htmlspecialchars($edit_tier['min_qty']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="disc_val">Discount Value (%) *</label>
                    <input type="number" step="0.01" name="discount_percent" id="disc_val" class="form-control" style="font-size:0.85rem; padding:8px;" required placeholder="e.g. 10.00"
                        value="<?php echo $edit_tier ? htmlspecialchars($edit_tier['discount_percent']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem;">
                        <input type="checkbox" name="status" value="1" <?php echo (!$edit_tier || $edit_tier['status']) ? 'checked' : ''; ?> style="accent-color:var(--gold-primary); width:18px; height:18px;">
                        <span>Enable Tier Immediately</span>
                    </label>
                </div>

                <button type="submit" name="<?php echo $edit_tier ? 'edit_tier' : 'add_tier'; ?>" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                    <?php echo $edit_tier ? 'Update Tier' : 'Save Tier Settings'; ?>
                </button>

                <?php if ($edit_tier): ?>
                    <a href="quantity_discounts.php" style="display:block; text-align:center; margin-top:10px; color:var(--text-muted); font-size:0.85rem;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Admin Guide Panel -->
    <div class="glass-card" style="padding:25px; border-radius:8px; margin-top:35px; border-left:4px solid var(--gold-primary); box-shadow: 0 10px 30px rgba(8,12,16,0.3);">
        <h3 style="font-size:1.1rem; color:var(--gold-primary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">
            <i class="fas fa-lightbulb" style="margin-right:8px;"></i> Volume-Tier Pricing Strategies
        </h3>
        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:12px;">
            Volume-based discounts incentivize customers to buy more units of any product in a single transaction. For example, buying **2+ items triggers a 10% discount** and **3+ items triggers a 15% discount**. These thresholds are computed automatically at checkout.
        </p>
        <ul style="font-size:0.8rem; color:var(--text-muted); padding-left:20px; line-height:1.7;">
            <li>Assign tiers to specific products to run targeted volume promotions.</li>
            <li>Promote these offers inside the rotating announcement bar (e.g., "Buy 2 products, get 10% off automatically!").</li>
            <li>Monitor customer cart metrics on the dashboard to adjust these tiers for peak performance.</li>
        </ul>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
