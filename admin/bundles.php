<?php
// admin/bundles.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle bundle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bundle'])) {
    $b_id = (int)$_POST['bundle_id'];
    $price = (float)$_POST['combo_price'];
    $disc = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;

    $stmt_u = $pdo->prepare("
        UPDATE bundles 
        SET combo_price = ?, discount_percent = ?, status = ? 
        WHERE id = ?
    ");
    $stmt_u->execute([$price, $disc, $status, $b_id]);
    $action_msg = "Bundle stack details updated successfully.";
}

// Fetch all bundles
$stmt = $pdo->prepare("SELECT * FROM bundles ORDER BY display_order ASC");
$stmt->execute();
$bundles = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Bundle Stacks Management</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage wellness combos and discount rates</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; flex-direction:column; gap:30px;">
        <?php foreach ($bundles as $b): 
            // Fetch items inside this bundle
            $stmt_i = $pdo->prepare("
                SELECT bi.*, p.name as p_name, pv.size_capsules, pv.sale_price
                FROM bundle_items bi
                JOIN products p ON bi.product_id = p.id
                JOIN product_variants pv ON bi.variant_id = pv.id
                WHERE bi.bundle_id = ?
            ");
            $stmt_i->execute([$b['id']]);
            $items = $stmt_i->fetchAll();
        ?>
            <div class="glass-card" style="padding:25px; border-radius:8px;">
                <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px; align-items:start;">
                    
                    <!-- Left: Bundle details -->
                    <div>
                        <h3 style="font-size:1.25rem; color:#fff; margin-bottom:8px;"><?php echo htmlspecialchars($b['title']); ?></h3>
                        <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:20px;"><?php echo htmlspecialchars($b['description']); ?></p>
                        
                        <h4 style="font-size:0.9rem; text-transform:uppercase; margin-bottom:10px; color:var(--gold-muted);">Combo Products Included</h4>
                        <ul style="list-style:none; padding:0;">
                            <?php foreach ($items as $item): ?>
                                <li style="padding:8px 0; border-bottom:1px dashed rgba(255,255,255,0.05); font-size:0.9rem; display:flex; justify-content:space-between;">
                                    <span>
                                        <i class="fas fa-check" style="color:var(--gold-primary); margin-right:8px;"></i>
                                        <strong><?php echo htmlspecialchars($item['p_name']); ?></strong> 
                                        <span style="color:var(--text-muted); font-size:0.8rem;">(<?php echo htmlspecialchars($item['size_capsules']); ?>)</span>
                                    </span>
                                    <span>₹<?php echo number_format($item['sale_price'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Right: Edit Form -->
                    <div style="background:var(--bg-primary); border:1px solid var(--border-color); padding:20px; border-radius:6px;">
                        <form action="bundles.php" method="POST">
                            <input type="hidden" name="bundle_id" value="<?php echo $b['id']; ?>">
                            
                            <div class="form-group">
                                <label for="b-price">Combo Sale Price (₹)</label>
                                <input type="number" step="0.01" name="combo_price" id="b-price" class="form-control" value="<?php echo $b['combo_price']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="b-disc">Display Discount (%)</label>
                                <input type="number" step="0.01" name="discount_percent" id="b-disc" class="form-control" value="<?php echo $b['discount_percent']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                    <input type="checkbox" name="status" value="1" <?php echo $b['status'] ? 'checked' : ''; ?> style="accent-color:var(--gold-primary); width:18px; height:18px;">
                                    <span>Enable Bundle Combo</span>
                                </label>
                            </div>

                            <button type="submit" name="update_bundle" class="btn-gold" style="width:100%; margin-top:10px; padding:10px;">
                                Update Combo settings
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Admin Guide Panel -->
    <div class="glass-card" style="padding:25px; border-radius:8px; margin-top:35px; border-left:4px solid var(--gold-primary); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h3 style="font-size:1.1rem; color:var(--gold-primary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">
            <i class="fas fa-lightbulb" style="margin-right:8px;"></i> Combo & Bundle Sales Strategy
        </h3>
        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:12px;">
            Bundling complementary items like **Wolfpack (Vitality)** and **Wolftox (Liver Support & Detox)** is a proven method to increase your storefront's Average Order Value (AOV). Customers receive a consolidated stack, and you save on combined shipping costs.
        </p>
        <ul style="font-size:0.8rem; color:var(--text-muted); padding-left:20px; line-height:1.7;">
            <li>Ensure combo pricing is set lower than the sum of individual variant prices to incentivize checkout.</li>
            <li>Promote the Combo Stack on the homepage banners to drive visibility.</li>
            <li>The discount percentage configured here is highlighted as a promotional badge on the product list grid.</li>
        </ul>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
