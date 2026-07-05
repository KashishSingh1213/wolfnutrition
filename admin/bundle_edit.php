<?php
// admin/bundle_edit.php — Dedicated Edit Combo + Manage Items
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: bundles.php");
    exit();
}

$stmt_b = $pdo->prepare("SELECT * FROM bundles WHERE id = ?");
$stmt_b->execute([$edit_id]);
$bundle = $stmt_b->fetch();
if (!$bundle) {
    header("Location: bundles.php");
    exit();
}

// Fetch all products for item selection
$stmt_products = $pdo->prepare("SELECT id, name FROM products ORDER BY name ASC");
$stmt_products->execute();
$all_products = $stmt_products->fetchAll();

// Fetch all variants grouped by product
$stmt_variants = $pdo->prepare("SELECT id, product_id, size_capsules, sale_price FROM product_variants ORDER BY product_id, price ASC");
$stmt_variants->execute();
$all_variants = $stmt_variants->fetchAll();
$variants_by_product = [];
foreach ($all_variants as $v) {
    $variants_by_product[$v['product_id']][] = $v;
}

// Fetch current items
$stmt_items = $pdo->prepare("
    SELECT bi.*, p.name as p_name, pv.size_capsules, pv.sale_price
    FROM bundle_items bi
    JOIN products p ON bi.product_id = p.id
    JOIN product_variants pv ON bi.variant_id = pv.id
    WHERE bi.bundle_id = ?
");
$stmt_items->execute([$edit_id]);
$items = $stmt_items->fetchAll();

// Handle UPDATE bundle details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bundle'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $combo_price = (float)$_POST['combo_price'];
    $discount_percent = (float)$_POST['discount_percent'];
    $display_order = (int)$_POST['display_order'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Keep existing banner by default
    $banner_image = $bundle['banner_image'];

    // Handle banner image upload (only if new file provided)
    if (isset($_FILES['banner_image_file']) && $_FILES['banner_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['banner_image_file'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'combo_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $banner_image = 'uploads/products/' . $filename;
            }
        }
    }

    $stmt_u = $pdo->prepare("
        UPDATE bundles SET title = ?, description = ?, banner_image = ?, combo_price = ?, discount_percent = ?, display_order = ?, status = ? WHERE id = ?
    ");
    $stmt_u->execute([$title, $description, $banner_image, $combo_price, $discount_percent, $display_order, $status, $edit_id]);
    
    // Refresh
    $stmt_b2 = $pdo->prepare("SELECT * FROM bundles WHERE id = ?");
    $stmt_b2->execute([$edit_id]);
    $bundle = $stmt_b2->fetch();
    
    $action_msg = "Combo updated successfully.";
}

// Handle ADD bundle item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bundle_item'])) {
    $product_id = (int)$_POST['product_id'];
    $variant_id = (int)$_POST['variant_id'];

    $stmt_check = $pdo->prepare("SELECT id FROM bundle_items WHERE bundle_id = ? AND product_id = ? AND variant_id = ?");
    $stmt_check->execute([$edit_id, $product_id, $variant_id]);
    if ($stmt_check->fetch()) {
        $action_error = "This product variant is already in the combo.";
    } else {
        $stmt_ai = $pdo->prepare("INSERT INTO bundle_items (bundle_id, product_id, variant_id) VALUES (?, ?, ?)");
        $stmt_ai->execute([$edit_id, $product_id, $variant_id]);
        header("Location: bundle_edit.php?id=" . $edit_id . "&msg=item_added");
        exit();
    }
}

// Handle REMOVE bundle item
if (isset($_GET['remove_item_id'])) {
    $remove_id = (int)$_GET['remove_item_id'];
    $stmt_ri = $pdo->prepare("DELETE FROM bundle_items WHERE id = ?");
    $stmt_ri->execute([$remove_id]);
    header("Location: bundle_edit.php?id=" . $edit_id . "&msg=item_removed");
    exit();
}

// Success messages
if (isset($_GET['msg'])) {
    $msgs = ['item_added' => 'Item added to combo.', 'item_removed' => 'Item removed from combo.'];
    $action_msg = $msgs[$_GET['msg']] ?? $action_msg;
}

// Re-fetch items after changes
$stmt_items2 = $pdo->prepare("
    SELECT bi.*, p.name as p_name, pv.size_capsules, pv.sale_price
    FROM bundle_items bi
    JOIN products p ON bi.product_id = p.id
    JOIN product_variants pv ON bi.variant_id = pv.id
    WHERE bi.bundle_id = ?
");
$stmt_items2->execute([$edit_id]);
$items = $stmt_items2->fetchAll();
?>

    <div style="margin-bottom:20px;">
        <a href="bundles.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Combos
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Edit Combo</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Editing: <strong style="color:var(--gold-primary);"><?php echo htmlspecialchars($bundle['title']); ?></strong></div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>
    <?php if ($action_error): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(255,50,50,0.05); border-color:rgba(255,50,50,0.3); color:#ff6b6b; margin-bottom:25px;">
            ❌ <?php echo htmlspecialchars($action_error); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px; align-items:start;">
        
        <!-- Left: Edit Details + Items -->
        <div style="display:flex; flex-direction:column; gap:25px;">
            
            <!-- Edit Combo Details -->
            <div class="glass-card" style="padding:25px; border-radius:8px;">
                <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
                    <i class="fas fa-edit"></i> Combo Details
                </h3>
                <form action="bundle_edit.php?id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="bundle_id" value="<?php echo $edit_id; ?>">

                    <div class="form-group">
                        <label for="b-title">Title *</label>
                        <input type="text" name="title" id="b-title" class="form-control" 
                            value="<?php echo htmlspecialchars($bundle['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="b-desc">Description</label>
                        <textarea name="description" id="b-desc" class="form-control" rows="3"><?php echo htmlspecialchars($bundle['description']); ?></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div class="form-group">
                            <label for="b-price">Combo Price (₹) *</label>
                            <input type="number" step="0.01" name="combo_price" id="b-price" class="form-control" 
                                value="<?php echo $bundle['combo_price']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="b-disc">Discount (%)</label>
                            <input type="number" step="0.01" name="discount_percent" id="b-disc" class="form-control" 
                                value="<?php echo $bundle['discount_percent']; ?>">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div class="form-group">
                            <label for="b-order">Display Order</label>
                            <input type="number" name="display_order" id="b-order" class="form-control" 
                                value="<?php echo $bundle['display_order']; ?>">
                        </div>
                        <div class="form-group" style="display:flex; align-items:flex-end;">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; padding-bottom:10px;">
                                <input type="checkbox" name="status" value="1" <?php echo $bundle['status'] ? 'checked' : ''; ?> style="accent-color:var(--gold-primary); width:18px; height:18px;">
                                <span>Active</span>
                            </label>
                        </div>
                    </div>

                    <!-- Banner Image -->
                    <?php if ($bundle['banner_image']): ?>
                        <div style="margin-bottom:15px; display:flex; align-items:center; gap:15px;">
                            <span style="font-size:0.85rem; color:var(--text-muted);">Current banner:</span>
                            <img src="../<?php echo htmlspecialchars($bundle['banner_image']); ?>" alt="" style="height:60px; width:auto; max-width:200px; object-fit:contain; background:#111; border-radius:6px; border:1px solid var(--border-color);">
                        </div>
                    <?php endif; ?>

                    <div class="form-group" style="margin-bottom:20px; max-width:400px;">
                        <label><?php echo $bundle['banner_image'] ? 'Replace Banner Image' : 'Upload Banner Image'; ?></label>
                        <input type="file" name="banner_image_file" accept="image/*" class="form-control" style="padding:8px;">
                        <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">JPG, PNG, WEBP — Max 5MB. Stored in uploads/products/</small>
                    </div>

                    <button type="submit" name="update_bundle" class="btn-gold" style="padding:10px 30px; font-size:0.9rem;">
                        <i class="fas fa-save"></i> Update Combo
                    </button>
                </form>
            </div>

            <!-- Combo Items -->
            <div class="glass-card" style="padding:25px; border-radius:8px;">
                <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
                    <i class="fas fa-boxes"></i> Combo Items (<?php echo count($items); ?>)
                </h3>

                <?php if (empty($items)): ?>
                    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;">No items in this combo yet. Add products below.</p>
                <?php else: ?>
                    <div style="margin-bottom:25px;">
                        <?php foreach ($items as $item): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 15px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:0.9rem;">
                                <span>
                                    <i class="fas fa-check-circle" style="color:var(--gold-primary); margin-right:8px;"></i>
                                    <strong><?php echo htmlspecialchars($item['p_name']); ?></strong>
                                    <span style="color:var(--text-muted); font-size:0.8rem;">(<?php echo htmlspecialchars($item['size_capsules']); ?>)</span>
                                    <span style="color:var(--text-muted); font-size:0.8rem; margin-left:8px;">— ₹<?php echo number_format($item['sale_price'], 2); ?></span>
                                </span>
                                <a href="bundle_edit.php?id=<?php echo $edit_id; ?>&remove_item_id=<?php echo $item['id']; ?>" 
                                   onclick="return confirm('Remove this item from the combo?');"
                                   style="color:#dc3545; font-size:0.8rem; text-decoration:none;">
                                    <i class="fas fa-times-circle"></i> Remove
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Item Form -->
                <div style="background:var(--bg-primary); border:1px solid var(--border-color); padding:20px; border-radius:6px;">
                    <h4 style="font-size:0.9rem; color:var(--text-muted); margin-bottom:15px; text-transform:uppercase;">Add Item to Combo</h4>
                    <form action="bundle_edit.php?id=<?php echo $edit_id; ?>" method="POST" style="display:grid; grid-template-columns: 2fr 2fr auto; gap:15px; align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Select Product</label>
                            <select name="product_id" id="product_select" class="form-control" required onchange="updateVariants(this.value);">
                                <option value="">-- Choose Product --</option>
                                <?php foreach ($all_products as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Select Variant</label>
                            <select name="variant_id" id="variant_select" class="form-control" required>
                                <option value="">-- Choose Product First --</option>
                            </select>
                        </div>

                        <button type="submit" name="add_bundle_item" class="btn-gold" style="padding:10px 16px; font-size:0.85rem;">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Combo Stats -->
        <div class="glass-card" style="padding:25px; border-radius:6px; border-left:4px solid var(--gold-primary);">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Combo Summary
            </h3>
            
            <div style="display:flex; flex-direction:column; gap:15px;">
                <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                    <span style="color:var(--text-muted);">Items in combo</span>
                    <strong style="color:#fff; font-size:1.1rem;"><?php echo count($items); ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                    <span style="color:var(--text-muted);">Combo price</span>
                    <strong style="color:var(--success-color); font-size:1.1rem;">₹<?php echo number_format($bundle['combo_price'], 2); ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                    <span style="color:var(--text-muted);">Individual total</span>
                    <strong style="color:#fff;">
                        ₹<?php 
                        $total_individual = 0;
                        foreach ($items as $item) $total_individual += $item['sale_price'];
                        echo number_format($total_individual, 2);
                        ?>
                    </strong>
                </div>
                <?php if ($total_individual > 0): ?>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; padding-top:10px; border-top:1px solid var(--border-color);">
                    <span style="color:var(--text-muted);">Customer saves</span>
                    <strong style="color:var(--success-color); font-size:1rem;">
                        ₹<?php echo number_format($total_individual - $bundle['combo_price'], 2); ?>
                        (<?php echo round((1 - $bundle['combo_price'] / $total_individual) * 100); ?>%)
                    </strong>
                </div>
                <?php endif; ?>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                    <span style="color:var(--text-muted);">Status</span>
                    <span class="admin-badge <?php echo $bundle['status'] ? 'badge-completed' : 'badge-failed'; ?>">
                        <?php echo $bundle['status'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                    <span style="color:var(--text-muted);">Slug</span>
                    <span style="color:var(--gold-muted); font-size:0.85rem;"><?php echo htmlspecialchars($bundle['slug']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- JS for variant dropdown -->
    <script>
    const variantsByProduct = <?php echo json_encode($variants_by_product); ?>;

    function updateVariants(productId) {
        const variantSelect = document.getElementById('variant_select');
        variantSelect.innerHTML = '<option value="">-- Choose Variant --</option>';
        
        if (productId && variantsByProduct[productId]) {
            variantsByProduct[productId].forEach(function(v) {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.size_capsules + ' — ₹' + parseFloat(v.sale_price).toFixed(2);
                variantSelect.appendChild(opt);
            });
        }
    }
    </script>

    <!-- Trumbowyg CSS + JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/upload/trumbowyg.upload.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#b-desc').trumbowyg({
            btns: [
                ['viewHTML'],
                ['formatting'],
                ['strong', 'em', 'del'],
                ['link'],
                ['insertImage'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen']
            ],
            plugins: {
                upload: {
                    serverPath: 'upload_handler.php',
                    fileFieldName: 'file',
                    urlPropertyName: 'url'
                }
            },
            autogrow: true
        });
    });
    </script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
