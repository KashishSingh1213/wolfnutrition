<?php
// admin/category_add.php — Dedicated Add Category page
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $desc = trim($_POST['description']);
    $order = (int)$_POST['display_order'];
    $status = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) {
        $action_error = "Category name is required.";
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        }
        $stmt_check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt_check->execute([$slug]);
        if ($stmt_check->fetch()) {
            $action_error = "A category with this slug already exists.";
        } else {
            $stmt_i = $pdo->prepare("INSERT INTO categories (name, slug, description, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt_i->execute([$name, $slug, $desc, $order, $status]);
            header("Location: categories.php?msg=added");
            exit();
        }
    }
}

// Fetch product count for reference
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM categories");
$stmt_total->execute();
$total_cats = (int)$stmt_total->fetchColumn();
?>

    <div style="margin-bottom:20px;">
        <a href="categories.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Add New Category</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total categories: <strong style="color:var(--gold-primary);"><?php echo $total_cats; ?></strong></div>
    </div>

    <?php if ($action_error): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(255,50,50,0.05); border-color:rgba(255,50,50,0.3); color:#ff6b6b; margin-bottom:25px;">
            ❌ <?php echo htmlspecialchars($action_error); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px; align-items:start;">
        
        <!-- Add Form -->
        <div class="glass-card" style="padding:30px; border-radius:8px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:20px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <i class="fas fa-plus-circle" style="margin-right:8px;"></i> Category Details
            </h3>
            
            <form action="category_add.php" method="POST">
                <div class="form-group">
                    <label for="cat-name">Category Name *</label>
                    <input type="text" name="name" id="cat-name" class="form-control" 
                        placeholder="e.g. Vitality" required oninput="autoSlugCat(this.value)">
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">This appears on storefront navigation and product filters</small>
                </div>

                <div class="form-group">
                    <label for="cat-slug">URL Slug</label>
                    <input type="text" name="slug" id="cat-slug" class="form-control" 
                        placeholder="auto-generated-from-name">
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">Used in URL: wolfnutrition.in/category/<strong>your-slug</strong></small>
                </div>

                <div class="form-group">
                    <label for="cat-desc">Description</label>
                    <textarea name="description" id="cat-desc" class="form-control" rows="3" 
                        placeholder="Short description for this category"></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label for="cat-order">Display Order</label>
                        <input type="number" name="display_order" id="cat-order" class="form-control" value="0">
                        <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">Lower number = appears first</small>
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; padding-bottom:10px;">
                            <input type="checkbox" name="is_active" value="1" checked style="accent-color:var(--gold-primary); width:18px; height:18px;">
                            <span>Active (visible on storefront)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top:25px; padding-top:20px; border-top:1px solid var(--border-color);">
                    <button type="submit" name="add_category" class="btn-gold" style="padding:12px 35px; font-size:0.9rem; font-weight:700;">
                        <i class="fas fa-plus"></i> Create Category
                    </button>
                    <a href="categories.php" class="btn-outline-gold" style="padding:12px 25px; font-size:0.9rem; text-decoration:none; display:flex; align-items:center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Existing Categories -->
        <div class="glass-card" style="padding:25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Existing Categories
            </h3>
            <?php
            $stmt_ex = $pdo->prepare("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.display_order ASC");
            $stmt_ex->execute();
            $existing = $stmt_ex->fetchAll();
            ?>
            <?php if (empty($existing)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:15px 0;">No categories yet.</p>
            <?php else: ?>
                <table class="admin-table" style="font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existing as $cat): ?>
                            <tr>
                                <td>
                                    <strong style="color:#fff;"><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <div style="font-size:0.7rem; color:var(--text-muted);"><?php echo htmlspecialchars($cat['slug']); ?></div>
                                </td>
                                <td><?php echo $cat['product_count']; ?></td>
                                <td>
                                    <span class="admin-badge <?php echo $cat['is_active'] ? 'badge-completed' : 'badge-failed'; ?>">
                                        <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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
    }
    </script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
