<?php
// admin/products.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle status toggle
if (isset($_GET['toggle_id'])) {
    $p_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$p_id]);
    $action_msg = "Product active status toggled.";
}

// Handle variant update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_variant'])) {
    $v_id = (int)$_POST['variant_id'];
    $price = (float)$_POST['price'];
    $sale_price = (float)$_POST['sale_price'];
    $stock = (int)$_POST['stock_qty'];

    if ($price <= 0 || $sale_price <= 0 || $stock < 0) {
        $action_error = "Invalid variant input. Check prices and stock.";
    } else {
        $stmt_u = $pdo->prepare("
            UPDATE product_variants 
            SET price = ?, sale_price = ?, stock_qty = ? 
            WHERE id = ?
        ");
        $stmt_u->execute([$price, $sale_price, $stock, $v_id]);
        $action_msg = "Variant details updated successfully.";
    }
}

// Fetch all products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
$stmt->execute();
$products = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Catalog Management</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage products and pack variations</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Products List -->
    <div style="display:flex; flex-direction:column; gap:30px;">
        <?php foreach ($products as $prod): 
            // Fetch variants for this product
            $stmt_v = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC");
            $stmt_v->execute([$prod['id']]);
            $variants = $stmt_v->fetchAll();
        ?>
            <div class="glass-card" style="padding:25px; border-radius:8px;">
                <div style="display:flex; gap:20px; align-items:center; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:15px;">
                    <img src="../<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" style="width:65px; height:65px; object-fit:contain; background:#000; border-radius:4px; border:1px solid var(--border-color);">
                    <div>
                        <h3 style="font-size:1.2rem; color:#fff;"><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <span style="font-size:0.8rem; color:var(--gold-muted); font-weight:600;"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                    </div>
                    
                    <div style="margin-left:auto; display:flex; align-items:center; gap:15px;">
                        <span>Status: <strong><?php echo $prod['is_active'] ? 'Active' : 'Inactive'; ?></strong></span>
                        <a href="products.php?toggle_id=<?php echo $prod['id']; ?>" class="btn-outline-gold" style="padding:6px 12px; font-size:0.8rem;">
                            Toggle Active
                        </a>
                    </div>
                </div>

                <!-- Variants Grid -->
                <h4 style="font-size:0.9rem; text-transform:uppercase; margin-bottom:12px; color:var(--gold-muted); font-weight:700; letter-spacing:0.5px;">Pack Variations & Stock</h4>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap:25px;">
                    <?php foreach ($variants as $v): ?>
                        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.15); padding:22px; border-radius:8px; box-shadow:0 8px 25px rgba(8,12,16,0.3); transition:all 0.3s;">
                            <form action="products.php" method="POST">
                                <input type="hidden" name="variant_id" value="<?php echo $v['id']; ?>">
                                
                                <div style="font-weight:700; color:#fff; font-size:0.9rem; margin-bottom:10px; display:flex; justify-content:space-between;">
                                    <span><?php echo htmlspecialchars($v['size_capsules']); ?> (SKU: <?php echo htmlspecialchars($v['sku']); ?>)</span>
                                    <?php if ($v['is_default']): ?>
                                        <span style="background:var(--gold-primary); color:#000; font-size:0.65rem; font-weight:800; padding:1px 6px; border-radius:3px; align-self:center;">DEFAULT QUICK ADD</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:12px;">
                                    <div>
                                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">MRP (₹)</label>
                                        <input type="number" step="0.01" name="price" class="form-control" style="font-size:0.85rem; padding:6px;" value="<?php echo $v['price']; ?>" required>
                                    </div>
                                    <div>
                                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Sale Price (₹)</label>
                                        <input type="number" step="0.01" name="sale_price" class="form-control" style="font-size:0.85rem; padding:6px;" value="<?php echo $v['sale_price']; ?>" required>
                                    </div>
                                    <div>
                                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Stock Qty</label>
                                        <input type="number" name="stock_qty" class="form-control" style="font-size:0.85rem; padding:6px;" value="<?php echo $v['stock_qty']; ?>" required>
                                    </div>
                                </div>

                                <button type="submit" name="update_variant" class="btn-gold" style="width:100%; padding:6px; font-size:0.8rem;">
                                    Update Variant
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
