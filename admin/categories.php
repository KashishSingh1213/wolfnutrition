<?php
// admin/categories.php — Category List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Delete Category
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt_count = $pdo->prepare("SELECT COUNT(id) FROM products WHERE category_id = ?");
    $stmt_count->execute([$del_id]);
    $product_count = (int)$stmt_count->fetchColumn();
    
    if ($product_count > 0) {
        $action_msg = "Cannot delete — category has $product_count product(s). Remove them first.";
    } else {
        $stmt_d = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt_d->execute([$del_id]);
        header("Location: categories.php?msg=deleted");
        exit();
    }
}

// Success messages
if (isset($_GET['msg'])) {
    $msgs = ['added' => 'Category added successfully.', 'updated' => 'Category updated.', 'deleted' => 'Category deleted.'];
    $action_msg = $msgs[$_GET['msg']] ?? $action_msg;
}

// Fetch categories with product count
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.display_order ASC
");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Category Management</h2>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="category_add.php" class="btn-gold" style="padding:8px 16px; font-size:0.8rem; text-decoration:none;">
                <i class="fas fa-plus"></i> Add Category
            </a>
            <div style="font-size:0.85rem; color:var(--text-muted);"><?php echo count($categories); ?> categories</div>
        </div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:<?php echo strpos($action_msg, 'Cannot') !== false ? '#ff6b6b' : 'var(--success-color)'; ?>; margin-bottom:25px;">
            <?php echo strpos($action_msg, 'Cannot') !== false ? '❌' : '✅'; ?> <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="padding:25px; border-radius:6px;">
        <?php if (empty($categories)): ?>
            <p style="color:var(--text-muted); text-align:center; padding:30px 0;">No categories created yet. Click "Add Category" to create one.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong style="color:#fff;"><?php echo $cat['display_order']; ?></strong></td>
                            <td>
                                <a href="category_edit.php?id=<?php echo $cat['id']; ?>" style="color:#fff; text-decoration:none; font-weight:700; font-size:1rem;">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </td>
                            <td style="font-size:0.85rem; color:var(--gold-muted);"><?php echo htmlspecialchars($cat['slug']); ?></td>
                            <td style="font-size:0.85rem; color:var(--text-secondary); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo htmlspecialchars($cat['description'] ?: '—'); ?>
                            </td>
                            <td>
                                <strong style="color:#fff; font-size:1rem;"><?php echo $cat['product_count']; ?></strong>
                            </td>
                            <td>
                                <span class="admin-badge <?php echo $cat['is_active'] ? 'badge-completed' : 'badge-failed'; ?>">
                                    <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:12px;">
                                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" style="color:var(--gold-primary); font-weight:700;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categories.php?delete_id=<?php echo $cat['id']; ?>" 
                                       style="color:var(--danger-color); font-weight:700;" 
                                       onclick="return confirm('Delete this category?')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
