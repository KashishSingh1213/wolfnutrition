<?php
// admin/products.php — Product List & Variant Management
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

// Handle success messages from redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'added') {
    $action_msg = "Product added successfully. Now add variants below.";
}

// Handle Add Variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    $product_id = (int)$_POST['product_id'];
    $sku = trim($_POST['sku']);
    $size_capsules = trim($_POST['size_capsules']);
    $price = (float)$_POST['price'];
    $sale_price = (float)$_POST['sale_price'];
    $stock_qty = (int)$_POST['stock_qty'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if (empty($sku) || empty($size_capsules) || $price <= 0 || $sale_price <= 0) {
        $action_error = "Variant SKU, size, and valid prices are required.";
    } else {
        // Check SKU uniqueness
        $stmt_sku = $pdo->prepare("SELECT id FROM product_variants WHERE sku = ?");
        $stmt_sku->execute([$sku]);
        if ($stmt_sku->fetch()) {
            $action_error = "Variant SKU already exists. Please use a unique SKU.";
        } else {
            // If setting as default, unset other defaults for this product
            if ($is_default) {
                $stmt_def = $pdo->prepare("UPDATE product_variants SET is_default = 0 WHERE product_id = ?");
                $stmt_def->execute([$product_id]);
            }
            $stmt_iv = $pdo->prepare("
                INSERT INTO product_variants (product_id, sku, size_capsules, price, sale_price, stock_qty, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_iv->execute([$product_id, $sku, $size_capsules, $price, $sale_price, $stock_qty, $is_default]);
            $action_msg = "Variant added successfully.";
        }
    }
}

// Handle Edit Variant (inline update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_variant'])) {
    $v_id = (int)$_POST['variant_id'];
    $price = (float)$_POST['price'];
    $sale_price = (float)$_POST['sale_price'];
    $stock = (int)$_POST['stock_qty'];

    if ($price <= 0 || $sale_price <= 0 || $stock < 0) {
        $action_error = "Invalid variant input. Check prices and stock.";
    } else {
        $stmt_uv = $pdo->prepare("
            UPDATE product_variants 
            SET price = ?, sale_price = ?, stock_qty = ? 
            WHERE id = ?
        ");
        $stmt_uv->execute([$price, $sale_price, $stock, $v_id]);
        $action_msg = "Variant updated.";
    }
}

// Handle Delete Variant
if (isset($_GET['delete_variant_id'])) {
    $v_id = (int)$_GET['delete_variant_id'];
    $stmt_dv = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
    $stmt_dv->execute([$v_id]);
    $action_msg = "Variant deleted.";
}

// Handle Delete Product
if (isset($_GET['delete_id'])) {
    $p_id = (int)$_GET['delete_id'];
    $stmt_dp = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt_dp->execute([$p_id]);
    $action_msg = "Product and its variants deleted.";
}

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $p_id = (int)$_GET['toggle_id'];
    $stmt_t = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
    $stmt_t->execute([$p_id]);
    $action_msg = "Product active status toggled.";
}

// Fetch all products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
$stmt->execute();
$products = $stmt->fetchAll();
?>

    <style>
        .product-card {
            background: rgba(18,18,18,0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 24px;
            transition: border-color 0.3s ease;
        }
        .product-card:hover {
            border-color: rgba(212,175,55,0.2);
        }
        .variant-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(212,175,55,0.15);
            border-radius: 8px;
            padding: 20px;
        }
        .variant-add-card {
            background: rgba(212,175,55,0.03);
            border: 1px dashed rgba(212,175,55,0.3);
            border-radius: 8px;
            padding: 20px;
        }
        .product-img-thumb {
            width: 60px;
            height: 60px;
            object-fit: contain;
            background: rgba(0,0,0,0.4);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .status-active {
            color: #4ade80;
        }
        .status-inactive {
            color: rgba(255,255,255,0.45);
        }

        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .product-header-row {
                flex-wrap: wrap;
                gap: 12px;
            }
            .product-actions {
                width: 100%;
                justify-content: flex-start;
                padding-top: 8px;
                border-top: 1px solid rgba(255,255,255,0.04);
                margin-top: 4px;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .variant-grid {
                grid-template-columns: 1fr !important;
            }
            .variant-fields {
                grid-template-columns: 1fr !important;
            }
            .add-variant-fields-top {
                grid-template-columns: 1fr !important;
            }
            .add-variant-fields-bottom {
                grid-template-columns: 1fr 1fr !important;
            }
            .product-card {
                padding: 16px !important;
            }
            .variant-card {
                padding: 14px !important;
            }
            .variant-add-card {
                padding: 14px !important;
            }
            .product-img-thumb {
                width: 48px !important;
                height: 48px !important;
            }
            .product-actions .btn-outline-gold {
                padding: 5px 10px !important;
                font-size: 0.7rem !important;
            }
            .variant-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 6px;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h2 style="font-size:1.6rem; font-weight:800; color:#fff; margin:0 0 4px 0; letter-spacing:-0.3px;">Catalog Management</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Manage products, variants & stock levels</p>
        </div>
        <a href="product_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-weight:600;">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.25); border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:0.95rem;"></i>
            <span style="color:#4ade80; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:0.95rem;"></i>
            <span style="color:#ef4444; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Products List -->
    <div style="display:flex; flex-direction:column; gap:24px;">
        <?php foreach ($products as $prod): 
            // Fetch variants for this product
            $stmt_v = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY is_default DESC, price ASC");
            $stmt_v->execute([$prod['id']]);
            $variants = $stmt_v->fetchAll();
        ?>
            <div class="product-card">
                <!-- Product Header -->
                <div class="product-header-row" style="display:flex; gap:16px; align-items:center; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.05);">
                    <img src="../<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-img-thumb">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                            <h3 style="font-size:1.05rem; font-weight:700; color:#fff; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <?php if ($prod['category_name']): ?>
                                <span style="background:rgba(212,175,55,0.12); color:#D4AF37; font-size:0.68rem; font-weight:700; padding:3px 8px; border-radius:4px; white-space:nowrap; text-transform:uppercase; letter-spacing:0.4px;"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <p style="font-size:0.8rem; color:rgba(255,255,255,0.45); margin:0;">ID: #<?php echo $prod['id']; ?> &middot; <?php echo count($variants); ?> variant<?php echo count($variants) !== 1 ? 's' : ''; ?></p>
                    </div>

                    <!-- Actions -->
                    <div class="product-actions" style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                        <span style="font-size:0.78rem; font-weight:600; padding:4px 10px; border-radius:6px; <?php echo $prod['is_active'] ? 'background:rgba(74,222,128,0.1); color:#4ade80;' : 'background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.45);'; ?>">
                            <?php echo $prod['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                        <a href="products.php?toggle_id=<?php echo $prod['id']; ?>" class="btn-outline-gold" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; font-weight:600; border-radius:6px;">
                            <i class="fas fa-toggle-on"></i> Toggle
                        </a>
                        <a href="product_edit.php?id=<?php echo $prod['id']; ?>" class="btn-outline-gold" style="padding:6px 12px; font-size:0.75rem; text-decoration:none; font-weight:600; border-radius:6px;">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <a href="products.php?delete_id=<?php echo $prod['id']; ?>" style="color:#ef4444; font-size:0.75rem; font-weight:600; text-decoration:none; padding:6px 10px; border-radius:6px; transition:background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'" onclick="return confirm('Delete this product and all its variants?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>

                <!-- Variants Section -->
                <div style="margin-bottom:8px;">
                    <h4 style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.8px; font-weight:700; color:rgba(255,255,255,0.45); margin:0 0 14px 0;">Pack Variations & Stock</h4>
                    <div class="variant-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:16px;">
                        <?php foreach ($variants as $v): ?>
                            <div class="variant-card">
                                <form action="products.php" method="POST">
                                    <input type="hidden" name="variant_id" value="<?php echo $v['id']; ?>">
                                    
                                    <div class="variant-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                        <div>
                                            <span style="font-weight:700; color:#fff; font-size:0.88rem;"><?php echo htmlspecialchars($v['size_capsules']); ?></span>
                                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-left:6px;">SKU: <?php echo htmlspecialchars($v['sku']); ?></span>
                                        </div>
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <?php if ($v['is_default']): ?>
                                                <span style="background:rgba(212,175,55,0.15); color:#D4AF37; font-size:0.62rem; font-weight:800; padding:2px 7px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px;">DEFAULT</span>
                                            <?php endif; ?>
                                            <a href="products.php?delete_variant_id=<?php echo $v['id']; ?>" style="color:#ef4444; font-size:0.72rem; text-decoration:none; padding:2px 4px;" onclick="return confirm('Delete this variant?')" title="Delete variant">
                                                <i class="fas fa-times-circle"></i>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="variant-fields" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:12px;">
                                        <div>
                                            <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">MRP (&#8377;)</label>
                                            <input type="number" step="0.01" name="price" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" value="<?php echo $v['price']; ?>" required>
                                        </div>
                                        <div>
                                            <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">Sale Price (&#8377;)</label>
                                            <input type="number" step="0.01" name="sale_price" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" value="<?php echo $v['sale_price']; ?>" required>
                                        </div>
                                        <div>
                                            <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">Stock Qty</label>
                                            <input type="number" name="stock_qty" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" value="<?php echo $v['stock_qty']; ?>" required>
                                        </div>
                                    </div>

                                    <button type="submit" name="update_variant" class="btn-gold" style="width:100%; padding:8px; font-size:0.78rem; border-radius:6px; font-weight:600;">
                                        Update Variant
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                        <!-- Add New Variant -->
                        <div class="variant-add-card">
                            <form action="products.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                <div style="font-weight:700; color:#D4AF37; font-size:0.85rem; margin-bottom:14px; display:flex; align-items:center; gap:6px;">
                                    <i class="fas fa-plus-circle"></i> Add New Variant
                                </div>
                                <div class="add-variant-fields-top" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                                    <div>
                                        <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">SKU *</label>
                                        <input type="text" name="sku" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" placeholder="e.g. WP30" required>
                                    </div>
                                    <div>
                                        <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">Size *</label>
                                        <input type="text" name="size_capsules" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" placeholder="e.g. 30 Veggie Capsules" required>
                                    </div>
                                </div>
                                <div class="add-variant-fields-bottom" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                                    <div>
                                        <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">MRP (&#8377;) *</label>
                                        <input type="number" step="0.01" name="price" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" required>
                                    </div>
                                    <div>
                                        <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">Sale Price (&#8377;) *</label>
                                        <input type="number" step="0.01" name="sale_price" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" required>
                                    </div>
                                    <div>
                                        <label style="font-size:0.7rem; color:rgba(255,255,255,0.45); display:block; margin-bottom:4px; font-weight:600;">Stock</label>
                                        <input type="number" name="stock_qty" class="form-control" style="font-size:0.82rem; padding:7px 10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08); border-radius:6px; color:#fff;" value="0">
                                    </div>
                                </div>
                                <div style="margin-bottom:14px;">
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.78rem; color:rgba(255,255,255,0.7);">
                                        <input type="checkbox" name="is_default" value="1" style="accent-color:#D4AF37; width:15px; height:15px;">
                                        <span>Set as Default variant</span>
                                    </label>
                                </div>
                                <button type="submit" name="add_variant" class="btn-gold" style="width:100%; padding:8px; font-size:0.78rem; border-radius:6px; font-weight:600;">
                                    Save Variant
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
