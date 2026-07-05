<?php
// admin/quantity_discount_add.php — Add Discount Tier
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Fetch products for dropdown
$stmt_products = $pdo->prepare("SELECT id, name FROM products ORDER BY name ASC");
$stmt_products->execute();
$products = $stmt_products->fetchAll();

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tier'])) {
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $min_qty = (int)$_POST['min_qty'];
    $disc = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;

    if ($min_qty <= 1 || $disc <= 0 || $disc > 100) {
        $action_error = "Invalid inputs. Min quantity must be > 1, discount must be 1-100%.";
    } else {
        $stmt_i = $pdo->prepare("INSERT INTO quantity_discounts (product_id, min_qty, discount_percent, status) VALUES (?, ?, ?, ?)");
        $stmt_i->execute([$product_id, $min_qty, $disc, $status]);
        header("Location: quantity_discounts.php?msg=created");
        exit();
    }
}

// Total tiers
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM quantity_discounts");
$stmt_total->execute();
$total_tiers = (int)$stmt_total->fetchColumn();
?>

    <div style="margin-bottom:20px;">
        <a href="quantity_discounts.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Discounts
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">New Discount Tier</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total tiers: <strong style="color:var(--gold-primary);"><?php echo $total_tiers; ?></strong></div>
    </div>

    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="max-width:600px; padding:0; overflow:hidden;">
        <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
            <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-plus" style="color:#D4AF37; font-size:0.85rem;"></i>
            </div>
            <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Create Discount Tier</h3>
        </div>

        <form action="quantity_discount_add.php" method="POST" style="padding:28px;">
            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Product (optional)</label>
                <select name="product_id" class="form-control">
                    <option value="">Store-wide (all products)</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div class="form-group">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Min Quantity *</label>
                    <input type="number" name="min_qty" class="form-control" min="2" required placeholder="e.g. 2">
                </div>
                <div class="form-group">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Discount (%) *</label>
                    <input type="number" step="0.01" name="discount_percent" class="form-control" required placeholder="e.g. 10">
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.88rem; color:rgba(255,255,255,0.7);">
                    <input type="checkbox" name="status" value="1" checked style="accent-color:#D4AF37; width:16px; height:16px;">
                    <span>Enable tier immediately</span>
                </label>
            </div>

            <button type="submit" name="create_tier" class="btn-gold" style="width:100%; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fas fa-plus"></i> Save Tier
            </button>
        </form>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
