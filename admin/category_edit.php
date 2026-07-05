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

    <div style="margin-bottom:20px;">
        <a href="categories.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Edit Category</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Editing: <strong style="color:var(--gold-primary);"><?php echo htmlspecialchars($category['name']); ?></strong></div>
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
        
        <!-- Edit Form -->
        <div class="glass-card" style="padding:30px; border-radius:8px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:20px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <i class="fas fa-edit" style="margin-right:8px;"></i> Category Details
            </h3>
            
            <form action="category_edit.php?id=<?php echo $edit_id; ?>" method="POST">
                <input type="hidden" name="category_id" value="<?php echo $edit_id; ?>">

                <div class="form-group">
                    <label for="cat-name">Category Name *</label>
                    <input type="text" name="name" id="cat-name" class="form-control" 
                        value="<?php echo htmlspecialchars($category['name']); ?>" 
                        required oninput="autoSlugCat(this.value)">
                </div>

                <div class="form-group">
                    <label for="cat-slug">URL Slug</label>
                    <input type="text" name="slug" id="cat-slug" class="form-control" 
                        value="<?php echo htmlspecialchars($category['slug']); ?>">
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">wolfnutrition.in/category/<strong id="slug-preview"><?php echo htmlspecialchars($category['slug']); ?></strong></small>
                </div>

                <div class="form-group">
                    <label for="cat-desc">Description</label>
                    <textarea name="description" id="cat-desc" class="form-control" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label for="cat-order">Display Order</label>
                        <input type="number" name="display_order" id="cat-order" class="form-control" 
                            value="<?php echo $category['display_order']; ?>">
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; padding-bottom:10px;">
                            <input type="checkbox" name="is_active" value="1" 
                                <?php echo $category['is_active'] ? 'checked' : ''; ?> 
                                style="accent-color:var(--gold-primary); width:18px; height:18px;">
                            <span>Active (visible on storefront)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top:25px; padding-top:20px; border-top:1px solid var(--border-color);">
                    <button type="submit" name="save_category" class="btn-gold" style="padding:12px 35px; font-size:0.9rem; font-weight:700;">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="categories.php" class="btn-outline-gold" style="padding:12px 25px; font-size:0.9rem; text-decoration:none; display:flex; align-items:center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Category Info -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Stats -->
            <div class="glass-card" style="padding:25px; border-radius:6px;">
                <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    Category Stats
                </h3>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span style="color:var(--text-muted);">Products in category</span>
                        <strong style="color:#fff; font-size:1.1rem;"><?php echo $product_count; ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span style="color:var(--text-muted);">Status</span>
                        <span class="admin-badge <?php echo $category['is_active'] ? 'badge-completed' : 'badge-failed'; ?>">
                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span style="color:var(--text-muted);">Display Order</span>
                        <strong style="color:#fff;"><?php echo $category['display_order']; ?></strong>
                    </div>
                </div>
            </div>

            <!-- Products in this category -->
            <div class="glass-card" style="padding:25px; border-radius:6px;">
                <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    Products (<?php echo $product_count; ?>)
                </h3>
                <?php if (empty($cat_products)): ?>
                    <p style="color:var(--text-muted); text-align:center; padding:15px 0; font-size:0.85rem;">No products in this category yet.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($cat_products as $prod): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px dashed rgba(255,255,255,0.05); font-size:0.85rem;">
                                <a href="product_edit.php?id=<?php echo $prod['id']; ?>" style="color:#fff; text-decoration:none; font-weight:600;">
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                </a>
                                <span class="admin-badge <?php echo $prod['is_active'] ? 'badge-completed' : 'badge-failed'; ?>" style="font-size:0.6rem;">
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
