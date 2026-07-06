<?php
// admin/blog_categories.php — Blog Categories CRUD
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $pdo->prepare("DELETE FROM blog_categories WHERE id = ?")->execute([$del_id]);
    $action_msg = "Category deleted.";
}

// Handle Toggle
if (isset($_GET['toggle_id'])) {
    $tog_id = (int)$_GET['toggle_id'];
    $pdo->prepare("UPDATE blog_categories SET status = NOT status WHERE id = ?")->execute([$tog_id]);
    $action_msg = "Category status toggled.";
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    $order = (int)$_POST['display_order'];

    if (empty($name)) {
        $action_error = "Category name is required.";
    } elseif (isset($_POST['edit_cat'])) {
        $eid = (int)$_POST['edit_id'];
        $pdo->prepare("UPDATE blog_categories SET name=?, slug=?, display_order=? WHERE id=?")->execute([$name, $slug, $order, $eid]);
        $action_msg = "Category updated.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM blog_categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) { $action_error = "Category already exists."; }
        else {
            $pdo->prepare("INSERT INTO blog_categories (name, slug, display_order, status) VALUES (?, ?, ?, 1)")->execute([$name, $slug, $order]);
            $action_msg = "Category added.";
        }
    }
}

// Fetch edit data
$edit_cat = null;
if (isset($_GET['edit_id'])) {
    $edit_cat = $pdo->prepare("SELECT * FROM blog_categories WHERE id = ?");
    $edit_cat->execute([(int)$_GET['edit_id']]);
    $edit_cat = $edit_cat->fetch();
}

$categories = $pdo->prepare("SELECT * FROM blog_categories ORDER BY display_order ASC");
$categories->execute();
$categories = $categories->fetchAll();
?>

    <style>
        @media (max-width: 1024px) {
            .bcat-grid { grid-template-columns: 1fr !important; }
            .bcat-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 8px; }
        }
        @media (max-width: 768px) {
            .bcat-grid { grid-template-columns: 1fr !important; }
            .bcat-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 8px; }
            .bcat-table thead { display: none !important; }
            .bcat-table, .bcat-table tbody, .bcat-table tr, .bcat-table td { display: block !important; width: 100% !important; }
            .bcat-table tbody tr { background: rgba(18,18,18,0.4); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; padding: 14px 16px; margin: 0 16px 10px 16px; }
            .bcat-table tbody tr:first-child { margin-top: 10px; }
            .bcat-table tbody td { padding: 3px 0 !important; border-bottom: none !important; font-size: 0.85rem; }
            .bcat-table tbody td::before { content: attr(data-label); display: block; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: rgba(255,255,255,0.3); margin-bottom: 1px; }
            .bcat-table tbody td.bcat-td-name::before { display: none; }
            .bcat-table tbody td.bcat-td-name { font-size: 0.95rem; padding-bottom: 6px !important; border-bottom: 1px solid rgba(255,255,255,0.04) !important; }
            .bcat-table tbody td.bcat-td-actions::before { display: none; }
            .bcat-table tbody td.bcat-td-actions { padding-top: 8px !important; border-top: 1px solid rgba(255,255,255,0.04); }
            .bcat-table tbody td.bcat-td-actions .bcat-action-btns { width: 100% !important; }
            .bcat-table tbody td.bcat-td-actions .bcat-action-btns a { flex: 1 !important; justify-content: center !important; }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="blog.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Blog
        </a>
    </div>

    <div class="bcat-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Blog Categories</h2>
        <span style="font-size:0.85rem; color:var(--text-muted);"><?php echo count($categories); ?> categories</span>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($action_error)): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div class="bcat-grid" style="display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start;">

        <!-- Categories List -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06);">
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">All Categories</h3>
            </div>
            <?php if (empty($categories)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-folder-open" style="font-size:2rem; color:rgba(255,255,255,0.1); margin-bottom:12px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.4); font-size:0.88rem;">No categories yet.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table bcat-table" style="margin-top:0; border:none;">
                        <thead>
                            <tr>
                                <th style="width:60px;">Order</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th style="width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td data-label="Order"><span style="font-weight:700; color:#D4AF37; font-size:0.85rem;"><?php echo $cat['display_order']; ?></span></td>
                                    <td data-label="" class="bcat-td-name" style="font-weight:600; color:#fff; font-size:0.88rem;"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td data-label="Slug"><span style="font-size:0.72rem; color:rgba(255,255,255,0.35); font-family:monospace;"><?php echo htmlspecialchars($cat['slug']); ?></span></td>
                                    <td data-label="Status"><span class="admin-badge <?php echo $cat['status'] ? 'badge-completed' : 'badge-pending'; ?>"><?php echo $cat['status'] ? 'Active' : 'Inactive'; ?></span></td>
                                    <td data-label="" class="bcat-td-actions">
                                        <div class="bcat-action-btns" style="display:flex; gap:6px;">
                                            <a href="blog_categories.php?edit_id=<?php echo $cat['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem;"><i class="fas fa-pen"></i></a>
                                            <a href="blog_categories.php?toggle_id=<?php echo $cat['id']; ?>" title="Toggle" style="width:30px; height:30px; border-radius:6px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem;"><i class="fas fa-toggle-on"></i></a>
                                            <a href="blog_categories.php?delete_id=<?php echo $cat['id']; ?>" title="Delete" onclick="return confirm('Delete this category?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem;"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:18px 22px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:10px;">
                <i class="fas fa-<?php echo $edit_cat ? 'pen' : 'plus'; ?>" style="color:#D4AF37; font-size:0.85rem;"></i>
                <h3 style="font-size:0.9rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">
                    <?php echo $edit_cat ? 'Edit Category' : 'New Category'; ?>
                </h3>
            </div>
            <form action="blog_categories.php" method="POST" style="padding:22px;">
                <?php if ($edit_cat): ?><input type="hidden" name="edit_id" value="<?php echo $edit_cat['id']; ?>"><?php endif; ?>
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo $edit_cat ? htmlspecialchars($edit_cat['name']) : ''; ?>">
                </div>
                <div class="form-group" style="margin-bottom:18px;">
                    <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Display Order</label>
                    <input type="number" name="display_order" class="form-control" min="0" value="<?php echo $edit_cat ? $edit_cat['display_order'] : '0'; ?>">
                </div>
                <button type="submit" name="<?php echo $edit_cat ? 'edit_cat' : 'add_cat'; ?>" class="btn-gold" style="width:100%; padding:12px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fas fa-<?php echo $edit_cat ? 'save' : 'plus'; ?>"></i>
                    <?php echo $edit_cat ? 'Update' : 'Save Category'; ?>
                </button>
                <?php if ($edit_cat): ?>
                    <a href="blog_categories.php" style="display:block; text-align:center; margin-top:10px; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); font-size:0.8rem; text-decoration:none;"><i class="fas fa-times"></i> Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
