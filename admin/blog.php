<?php
// admin/blog.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add Article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = trim($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $tag = trim($_POST['category_tag']);
    $img = trim($_POST['cover_image']);
    $body = trim($_POST['body']);

    if (empty($title) || empty($body)) {
        $action_error = "Please fill in title and body.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        $stmt_i = $pdo->prepare("
            INSERT INTO blog_posts (title, slug, category_tag, cover_image, body, status) 
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt_i->execute([$title, $slug, $tag, $img, $body]);
        $action_msg = "Blog article published successfully.";
    }
}

// Handle Edit Article Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
    $post_id = (int)$_POST['post_id'];
    $title = trim($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $tag = trim($_POST['category_tag']);
    $img = trim($_POST['cover_image']);
    $body = trim($_POST['body']);

    if (empty($title) || empty($body)) {
        $action_error = "Please fill in title and body.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $post_id]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        $stmt_u = $pdo->prepare("
            UPDATE blog_posts SET title = ?, slug = ?, category_tag = ?, cover_image = ?, body = ?
            WHERE id = ?
        ");
        $stmt_u->execute([$title, $slug, $tag, $img, $body, $post_id]);
        $action_msg = "Blog article updated successfully.";
    }
}

// Handle Toggle Status
if (isset($_GET['toggle_id'])) {
    $post_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE blog_posts SET status = NOT status WHERE id = ?");
    $stmt->execute([$post_id]);
    $action_msg = "Blog article status toggled.";
}

// Handle Delete Article
if (isset($_GET['delete_id'])) {
    $post_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $action_msg = "Blog article deleted.";
}

// Fetch all posts
$stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY published_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll();

// Fetch edit post if editing
$edit_post = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_post = $stmt->fetch();
}
?>

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Blog Manager</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Author and publish wellness articles</p>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($action_error) && $action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:1rem;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 480px; gap:28px; align-items:start;">

        <!-- Articles List -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Published Articles</h3>
                    <p style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:4px;"><?php echo count($posts); ?> articles</p>
                </div>
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-blog" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
            </div>

            <?php if (empty($posts)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-pen-fancy" style="font-size:2.5rem; color:rgba(255,255,255,0.1); margin-bottom:16px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.45); font-size:0.9rem;">No articles written yet.</p>
                    <p style="color:rgba(255,255,255,0.3); font-size:0.8rem; margin-top:6px;">Start writing your first wellness article.</p>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column;">
                    <?php foreach ($posts as $index => $post): ?>
                        <div style="padding:18px 24px; border-bottom:1px solid rgba(255,255,255,0.04); <?php echo $index === 0 ? '' : ''; ?> transition:background 0.15s;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                                <div style="flex:1; min-width:0;">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                        <span class="admin-badge <?php echo $post['status'] ? 'badge-completed' : 'badge-pending'; ?>" style="flex-shrink:0;">
                                            <?php echo $post['status'] ? 'Published' : 'Draft'; ?>
                                        </span>
                                        <?php if ($post['category_tag']): ?>
                                            <span style="font-size:0.65rem; font-weight:600; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px; background:rgba(212,175,55,0.08); padding:2px 8px; border-radius:4px;">
                                                <?php echo htmlspecialchars($post['category_tag']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 style="font-size:0.95rem; font-weight:700; color:#fff; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h4>
                                    <div style="font-size:0.72rem; color:rgba(255,255,255,0.35);">
                                        <i class="far fa-calendar" style="margin-right:4px;"></i>
                                        <?php echo date('M d, Y', strtotime($post['published_at'])); ?>
                                    </div>
                                </div>
                                <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
                                    <a href="blog.php?edit_id=<?php echo $post['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem;">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="blog.php?toggle_id=<?php echo $post['id']; ?>" title="<?php echo $post['status'] ? 'Unpublish' : 'Publish'; ?>" style="width:30px; height:30px; border-radius:6px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem;">
                                        <i class="fas fa-<?php echo $post['status'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </a>
                                    <a href="blog.php?delete_id=<?php echo $post['id']; ?>" title="Delete" onclick="return confirm('Delete this blog post?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-<?php echo $edit_post ? 'edit' : 'pen-fancy'; ?>" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">
                    <?php echo $edit_post ? 'Edit Article' : 'Write Article'; ?>
                </h3>
            </div>

            <?php if ($edit_post): ?>
                <form action="blog.php" method="POST" style="padding:24px;">
                    <input type="hidden" name="post_id" value="<?php echo $edit_post['id']; ?>">
            <?php else: ?>
                <form action="blog.php" method="POST" style="padding:24px;">
            <?php endif; ?>

                <div class="form-group">
                    <label for="title" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Article Title *</label>
                    <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Shilajit Health Benefits"
                        value="<?php echo htmlspecialchars($edit_post ? $edit_post['title'] : ''); ?>">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="tag" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Category Tag</label>
                        <input type="text" name="category_tag" id="tag" class="form-control" placeholder="e.g. Vitality"
                            value="<?php echo htmlspecialchars($edit_post ? $edit_post['category_tag'] : 'Wellness'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="img" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Cover Image Path</label>
                        <input type="text" name="cover_image" id="img" class="form-control" placeholder="e.g. assets/images/blog/..."
                            value="<?php echo htmlspecialchars($edit_post ? $edit_post['cover_image'] : ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="body" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Article Content *</label>
                    <textarea name="body" id="body" rows="12" class="form-control" required><?php echo htmlspecialchars($edit_post ? $edit_post['body'] : ''); ?></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" name="<?php echo $edit_post ? 'edit_post' : 'add_post'; ?>" class="btn-gold" style="flex:1; padding:12px 20px;">
                        <i class="fas fa-<?php echo $edit_post ? 'save' : 'paper-plane'; ?>"></i>
                        <?php echo $edit_post ? 'Update Article' : 'Publish Article'; ?>
                    </button>
                    <?php if ($edit_post): ?>
                        <a href="blog.php" class="btn-outline-gold" style="padding:12px 20px; text-align:center;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

<!-- Trumbowyg Editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#body').trumbowyg({
        btns: [
            ['viewHTML'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['foreColor', 'backColor'],
            ['superscript', 'subscript'],
            ['link'],
            ['insertImage'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen']
        ],
        autogrow: true,
        urlPrefix: '../',
        file: {
            serverPath: 'upload_handler.php'
        }
    });
});
</script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
