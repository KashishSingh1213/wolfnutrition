<?php
// admin/blog_tags.php — Blog Tags CRUD
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $pdo->prepare("DELETE FROM blog_tags WHERE id = ?")->execute([$del_id]);
    $action_msg = "Tag deleted.";
}

// Handle Toggle
if (isset($_GET['toggle_id'])) {
    $tog_id = (int)$_GET['toggle_id'];
    $pdo->prepare("UPDATE blog_tags SET status = NOT status WHERE id = ?")->execute([$tog_id]);
    $action_msg = "Tag status toggled.";
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    if (empty($name)) {
        $action_error = "Tag name is required.";
    } elseif (isset($_POST['edit_tag'])) {
        $eid = (int)$_POST['edit_id'];
        $pdo->prepare("UPDATE blog_tags SET name=?, slug=? WHERE id=?")->execute([$name, $slug, $eid]);
        $action_msg = "Tag updated.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM blog_tags WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) { $action_error = "Tag already exists."; }
        else {
            $pdo->prepare("INSERT INTO blog_tags (name, slug, status) VALUES (?, ?, 1)")->execute([$name, $slug]);
            $action_msg = "Tag added.";
        }
    }
}

// Fetch edit data
$edit_tag = null;
if (isset($_GET['edit_id'])) {
    $edit_tag = $pdo->prepare("SELECT * FROM blog_tags WHERE id = ?");
    $edit_tag->execute([(int)$_GET['edit_id']]);
    $edit_tag = $edit_tag->fetch();
}

$tags = $pdo->prepare("SELECT * FROM blog_tags ORDER BY name ASC");
$tags->execute();
$tags = $tags->fetchAll();
?>

    <style>
        @media (max-width: 1024px) {
            .btag-grid { grid-template-columns: 1fr !important; }
            .btag-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 8px; }
        }
        @media (max-width: 768px) {
            .btag-grid { grid-template-columns: 1fr !important; }
            .btag-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 8px; }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="blog.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Blog
        </a>
    </div>

    <div class="btag-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Blog Tags</h2>
        <span style="font-size:0.85rem; color:var(--text-muted);"><?php echo count($tags); ?> tags</span>
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

    <div class="btag-grid" style="display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start;">

        <!-- Tags List -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06);">
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">All Tags</h3>
            </div>
            <?php if (empty($tags)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-tags" style="font-size:2rem; color:rgba(255,255,255,0.1); margin-bottom:12px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.4); font-size:0.88rem;">No tags yet.</p>
                </div>
            <?php else: ?>
                <div style="padding:20px 24px; display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($tags as $tag): ?>
                        <div style="display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border-radius:20px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.12); <?php echo !$tag['status'] ? 'opacity:0.4;' : ''; ?>">
                            <span style="font-size:0.82rem; color:#fff; font-weight:600;">#<?php echo htmlspecialchars($tag['name']); ?></span>
                            <div style="display:flex; gap:4px;">
                                <a href="blog_tags.php?edit_id=<?php echo $tag['id']; ?>" title="Edit" style="width:22px; height:22px; border-radius:4px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.65rem; text-decoration:none;"><i class="fas fa-pen"></i></a>
                                <a href="blog_tags.php?toggle_id=<?php echo $tag['id']; ?>" title="Toggle" style="width:22px; height:22px; border-radius:4px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.65rem; text-decoration:none;"><i class="fas fa-toggle-on"></i></a>
                                <a href="blog_tags.php?delete_id=<?php echo $tag['id']; ?>" title="Delete" onclick="return confirm('Delete this tag?')" style="width:22px; height:22px; border-radius:4px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.65rem; text-decoration:none;"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:18px 22px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:10px;">
                <i class="fas fa-<?php echo $edit_tag ? 'pen' : 'plus'; ?>" style="color:#D4AF37; font-size:0.85rem;"></i>
                <h3 style="font-size:0.9rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">
                    <?php echo $edit_tag ? 'Edit Tag' : 'New Tag'; ?>
                </h3>
            </div>
            <form action="blog_tags.php" method="POST" style="padding:22px;">
                <?php if ($edit_tag): ?><input type="hidden" name="edit_id" value="<?php echo $edit_tag['id']; ?>"><?php endif; ?>
                <div class="form-group" style="margin-bottom:18px;">
                    <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Tag Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. shilajit" value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['name']) : ''; ?>">
                </div>
                <button type="submit" name="<?php echo $edit_tag ? 'edit_tag' : 'add_tag'; ?>" class="btn-gold" style="width:100%; padding:12px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fas fa-<?php echo $edit_tag ? 'save' : 'plus'; ?>"></i>
                    <?php echo $edit_tag ? 'Update' : 'Save Tag'; ?>
                </button>
                <?php if ($edit_tag): ?>
                    <a href="blog_tags.php" style="display:block; text-align:center; margin-top:10px; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); font-size:0.8rem; text-decoration:none;"><i class="fas fa-times"></i> Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
