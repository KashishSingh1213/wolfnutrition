<?php
// admin/categories.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle category updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $cat_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $order = (int)$_POST['display_order'];
    $status = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) {
        $action_error = "Category name cannot be blank.";
    } else {
        $stmt_u = $pdo->prepare("
            UPDATE categories 
            SET name = ?, description = ?, display_order = ?, is_active = ? 
            WHERE id = ?
        ");
        $stmt_u->execute([$name, $desc, $order, $status, $cat_id]);
        $action_msg = "Category updated successfully.";
    }
}

// Fetch all categories
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY display_order ASC");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Category Management</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Configure product grouping filters</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; flex-direction:column; gap:20px;">
        <?php foreach ($categories as $cat): ?>
            <div class="glass-card" style="padding:20px; border-radius:8px;">
                <form action="categories.php" method="POST" style="display:grid; grid-template-columns: 2fr 1fr 1fr 120px; gap:20px; align-items:end;">
                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                    
                    <div class="form-group" style="margin:0;">
                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Category Name *</label>
                        <input type="text" name="name" class="form-control" style="font-size:0.85rem; padding:8px;" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Description</label>
                        <input type="text" name="description" class="form-control" style="font-size:0.85rem; padding:8px;" value="<?php echo htmlspecialchars($cat['description']); ?>">
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label style="font-size:0.75rem; color:var(--text-muted); display:block; margin-bottom:4px;">Display Sort Order</label>
                        <input type="number" name="display_order" class="form-control" style="font-size:0.85rem; padding:8px;" value="<?php echo $cat['display_order']; ?>">
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px; height:100%; justify-content:space-between; padding-bottom:5px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem;">
                            <input type="checkbox" name="is_active" value="1" <?php echo $cat['is_active'] ? 'checked' : ''; ?> style="accent-color:var(--gold-primary);">
                            <span>Active</span>
                        </label>
                        <button type="submit" name="save_category" class="btn-gold" style="padding:6px; font-size:0.8rem; width:100%;">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
