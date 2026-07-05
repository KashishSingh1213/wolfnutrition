<?php
// admin/quantity_discount_edit.php — Edit Discount Tier
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: quantity_discounts.php");
    exit();
}

$stmt_t = $pdo->prepare("SELECT * FROM quantity_discounts WHERE id = ?");
$stmt_t->execute([$edit_id]);
$tier = $stmt_t->fetch();
if (!$tier) {
    header("Location: quantity_discounts.php");
    exit();
}

// Fetch products for dropdown
$stmt_products = $pdo->prepare("SELECT id, name FROM products ORDER BY name ASC");
$stmt_products->execute();
$products = $stmt_products->fetchAll();

// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tier'])) {
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $min_qty = (int)$_POST['min_qty'];
    $disc = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;

    if ($min_qty <= 1 || $disc <= 0 || $disc > 100) {
        $action_error = "Invalid inputs. Min quantity must be > 1, discount must be 1-100%.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE quantity_discounts SET product_id = ?, min_qty = ?, discount_percent = ?, status = ? WHERE id = ?");
        $stmt_u->execute([$product_id, $min_qty, $disc, $status, $edit_id]);
        $action_msg = "Discount tier updated successfully.";
        $stmt_t->execute([$edit_id]);
        $tier = $stmt_t->fetch();
    }
}

// Fetch product name for assigned product
$product_name = 'Store-wide';
if ($tier['product_id']) {
    $stmt_pn = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt_pn->execute([$tier['product_id']]);
    $pn = $stmt_pn->fetch();
    if ($pn) $product_name = $pn['name'];
}
?>

    <div style="margin-bottom:20px;">
        <a href="quantity_discounts.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Discounts
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Edit Discount Tier</h2>
        <span class="admin-badge <?php echo $tier['status'] ? 'badge-completed' : 'badge-pending'; ?>" style="font-size:0.75rem;">
            <?php echo $tier['status'] ? 'Active' : 'Inactive'; ?>
        </span>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 300px; gap:28px; align-items:start;">

        <!-- Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-pen" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Update Tier</h3>
            </div>

            <form action="quantity_discount_edit.php?id=<?php echo $edit_id; ?>" method="POST" style="padding:28px;">
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Product (optional)</label>
                    <select name="product_id" class="form-control">
                        <option value="">Store-wide (all products)</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($tier['product_id'] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div class="form-group">
                        <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Min Quantity *</label>
                        <input type="number" name="min_qty" class="form-control" min="2" required value="<?php echo $tier['min_qty']; ?>">
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Discount (%) *</label>
                        <input type="number" step="0.01" name="discount_percent" class="form-control" required value="<?php echo $tier['discount_percent']; ?>">
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.88rem; color:rgba(255,255,255,0.7);">
                        <input type="checkbox" name="status" value="1" <?php echo $tier['status'] ? 'checked' : ''; ?> style="accent-color:#D4AF37; width:16px; height:16px;">
                        <span>Enable tier</span>
                    </label>
                </div>

                <button type="submit" name="update_tier" class="btn-gold" style="width:100%; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fas fa-save"></i> Update Tier
                </button>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div style="position:sticky; top:96px;">
            <div class="glass-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Tier Details</h4>
                </div>
                <div style="padding:18px 20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">ID</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;">#<?php echo $tier['id']; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Status</span>
                        <span class="admin-badge <?php echo $tier['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                            <?php echo $tier['status'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Min Qty</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;"><?php echo $tier['min_qty']; ?>+</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Discount</span>
                        <span style="font-size:0.78rem; color:#4ade80; font-weight:700;"><?php echo $tier['discount_percent']; ?>% OFF</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Product</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600; text-align:right; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($product_name); ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-top:16px; padding:16px; border-radius:10px; background:rgba(212,175,55,0.04); border:1px solid rgba(212,175,55,0.08);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.8rem;"></i>
                    <span style="font-size:0.72rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">Tip</span>
                </div>
                <p style="font-size:0.78rem; color:rgba(255,255,255,0.4); line-height:1.6; margin:0;">
                    Volume discounts incentivize customers to buy more. Set product-specific tiers or leave blank for store-wide offers.
                </p>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
