<?php
// admin/category_edit.php — Dedicated Edit Category page
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: categories.php");
    exit();
}

$stmt_cat = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt_cat->execute([$edit_id]);
$category = $stmt_cat->fetch();
if (!$category) {
    header("Location: categories.php");
    exit();
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $desc = trim($_POST['description']);
    $order = (int)$_POST['display_order'];
    $status = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) {
        $action_error = "Category name is required.";
    } else {
        // Check slug uniqueness (exclude current)
        $stmt_slug = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt_slug->execute([$slug, $edit_id]);
        if ($stmt_slug->fetch()) {
            $action_error = "A category with this slug already exists.";
        } else {
            $stmt_u = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt_u->execute([$name, $slug, $desc, $order, $status, $edit_id]);
            
            // Refresh data
            $stmt_cat2 = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt_cat2->execute([$edit_id]);
            $category = $stmt_cat2->fetch();
            
            $action_msg = "Category updated successfully.";
        }
    }
}

// Get product count in this category
$stmt_count = $pdo->prepare("SELECT COUNT(id) FROM products WHERE category_id = ?");
$stmt_count->execute([$edit_id]);
$product_count = (int)$stmt_count->fetchColumn();

// Get products in this category
$stmt_products = $pdo->prepare("SELECT id, name, slug, is_active FROM products WHERE category_id = ? ORDER BY name ASC");
$stmt_products->execute([$edit_id]);
$cat_products = $stmt_products->fetchAll();
?>

    <style>
        .form-section-card {
            background: rgba(18,18,18,0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 28px;
        }
        .form-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: #D4AF37;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            display: block;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            color: #fff;
            font-size: 0.88rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: rgba(212,175,55,0.5);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.08);
        }
        .form-input::placeholder { color: rgba(255,255,255,0.25); }
        .form-hint {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.35);
            margin-top: 5px;
            display: block;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .stat-row:last-child { border-bottom: none; }
        .stat-label {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.45);
        }
        .stat-value {
            font-weight: 700;
            color: #fff;
            font-size: 0.92rem;
        }
        .badge-active {
            background: rgba(74,222,128,0.1);
            color: #4ade80;
            font-size: 0.62rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .badge-inactive {
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.4);
            font-size: 0.62rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .product-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .product-list-item:last-child { border-bottom: none; }
    </style>

    <!-- Back Link -->
    <div style="margin-bottom:24px;">
        <a href="categories.php" style="color:rgba(255,255,255,0.45); font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h2 style="font-size:1.6rem; font-weight:800; color:#fff; margin:0 0 4px 0; letter-spacing:-0.3px;">Edit Category</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Editing: <span style="color:#D4AF37; font-weight:600;"><?php echo htmlspecialchars($category['name']); ?></span></p>
        </div>
        <span style="font-size:0.75rem; color:rgba(255,255,255,0.3); background:rgba(255,255,255,0.04); padding:5px 12px; border-radius:6px;">ID: #<?php echo $edit_id; ?></span>
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

    <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start;">
        
        <!-- Edit Form -->
        <div class="form-section-card">
            <div class="form-section-title">
                <i class="fas fa-pen"></i> Category Details
            </div>
            
            <form action="category_edit.php?id=<?php echo $edit_id; ?>" method="POST">
                <input type="hidden" name="category_id" value="<?php echo $edit_id; ?>">

                <div style="margin-bottom:18px;">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($category['name']); ?>" required oninput="autoSlugCat(this.value)">
                </div>

                <div style="margin-bottom:18px;">
                    <label class="form-label">URL Slug</label>
                    <input type="text" name="slug" id="cat-slug" class="form-input" value="<?php echo htmlspecialchars($category['slug']); ?>">
                    <span class="form-hint">wolfnutrition.in/category/<strong id="slug-preview" style="color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars($category['slug']); ?></strong></span>
                </div>

                <div style="margin-bottom:18px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-input" value="<?php echo $category['display_order']; ?>">
                    </div>
                    <div style="display:flex; align-items:flex-end; padding-bottom:2px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:rgba(255,255,255,0.7);">
                            <input type="checkbox" name="is_active" value="1" <?php echo $category['is_active'] ? 'checked' : ''; ?> style="accent-color:#D4AF37; width:16px; height:16px;">
                            <span>Active</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:14px; margin-top:28px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.06);">
                    <button type="submit" name="save_category" class="btn-gold" style="padding:12px 32px; font-size:0.88rem; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="categories.php" class="btn-outline-gold" style="padding:12px 24px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Right Sidebar -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Stats -->
            <div class="form-section-card">
                <div class="form-section-title">
                    <i class="fas fa-chart-bar"></i> Category Stats
                </div>
                <div style="display:flex; flex-direction:column; gap:0;">
                    <div class="stat-row">
                        <span class="stat-label">Products in category</span>
                        <span class="stat-value"><?php echo $product_count; ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Status</span>
                        <span class="<?php echo $category['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Display Order</span>
                        <span class="stat-value"><?php echo $category['display_order']; ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Slug</span>
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.5); font-family:monospace;"><?php echo htmlspecialchars($category['slug']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Products in this category -->
            <div class="form-section-card">
                <div class="form-section-title">
                    <i class="fas fa-box"></i> Products (<?php echo $product_count; ?>)
                </div>
                <?php if (empty($cat_products)): ?>
                    <div style="text-align:center; padding:20px 0;">
                        <i class="fas fa-inbox" style="font-size:1.3rem; color:rgba(255,255,255,0.1); margin-bottom:8px; display:block;"></i>
                        <p style="color:rgba(255,255,255,0.35); font-size:0.82rem; margin:0;">No products in this category yet.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:0;">
                        <?php foreach ($cat_products as $prod): ?>
                            <div class="product-list-item">
                                <a href="product_edit.php?id=<?php echo $prod['id']; ?>" style="color:#fff; text-decoration:none; font-weight:600; font-size:0.82rem; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='#fff'">
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                </a>
                                <span class="<?php echo $prod['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $prod['is_active'] ? 'Active' : 'Off'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function autoSlugCat(val) {
        var slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('cat-slug').value = slug;
        document.getElementById('slug-preview').textContent = slug;
    }
    </script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
