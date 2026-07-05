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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Blog Manager</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Author and publish wellness articles</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px; align-items:start;">
        <!-- Articles list -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Published Articles
            </h3>
            
            <?php if (empty($posts)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No articles written yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <?php foreach ($posts as $post): ?>
                        <div style="border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong style="color:#fff; font-size:1rem; display:block;"><?php echo htmlspecialchars($post['title']); ?></strong>
                                    <span style="font-size:0.75rem; color:var(--gold-muted); font-weight:600; text-transform:uppercase;"><?php echo htmlspecialchars($post['category_tag']); ?></span>
                                    <span style="font-size:0.75rem; color:var(--text-muted); margin-left:15px;"><?php echo date('M d, Y', strtotime($post['published_at'])); ?></span>
                                </div>
                                <span class="admin-badge <?php echo $post['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                    <?php echo $post['status'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </div>
                            <div style="display:flex; gap:12px; margin-top:8px;">
                                <a href="blog.php?edit_id=<?php echo $post['id']; ?>" style="color:var(--gold-primary); font-weight:700; font-size:0.85rem;">Edit</a>
                                <a href="blog.php?toggle_id=<?php echo $post['id']; ?>" style="color:var(--text-secondary); font-weight:700; font-size:0.85rem;"><?php echo $post['status'] ? 'Unpublish' : 'Publish'; ?></a>
                                <a href="blog.php?delete_id=<?php echo $post['id']; ?>" style="color:var(--danger-color); font-weight:700; font-size:0.85rem;" onclick="return confirm('Delete this blog post?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Article Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <?php echo $edit_post ? 'Edit Article' : 'Write Article'; ?>
            </h3>
            
            <?php if ($edit_post): ?>
                <form action="blog.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $edit_post['id']; ?>">
                    <div class="form-group">
                        <label for="title">Article Title *</label>
                        <input type="text" name="title" id="title" class="form-control" style="font-size:0.85rem; padding:8px;" value="<?php echo htmlspecialchars($edit_post['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tag">Category Tag</label>
                        <input type="text" name="category_tag" id="tag" class="form-control" style="font-size:0.85rem; padding:8px;" value="<?php echo htmlspecialchars($edit_post['category_tag']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="img">Cover Image Path</label>
                        <input type="text" name="cover_image" id="img" class="form-control" style="font-size:0.85rem; padding:8px;" value="<?php echo htmlspecialchars($edit_post['cover_image']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="body">Article Content (HTML Allowed) *</label>
                        <textarea name="body" id="body" rows="8" class="form-control" style="font-size:0.85rem; padding:8px;" required><?php echo htmlspecialchars($edit_post['body']); ?></textarea>
                    </div>
                    
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <button type="submit" name="edit_post" class="btn-gold" style="flex:1; padding:10px; font-size:0.85rem;">
                            Update Article
                        </button>
                        <a href="blog.php" class="btn-outline-gold" style="padding:10px 20px; font-size:0.85rem; text-align:center;">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <form action="blog.php" method="POST">
                    <div class="form-group">
                        <label for="title">Article Title *</label>
                        <input type="text" name="title" id="title" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. Shilajit Health Benefits" required>
                    </div>
                    <div class="form-group">
                        <label for="tag">Category Tag</label>
                        <input type="text" name="category_tag" id="tag" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. Vitality or Detox" value="Wellness">
                    </div>
                    <div class="form-group">
                        <label for="img">Cover Image Path</label>
                        <input type="text" name="cover_image" id="img" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. assets/images/blog/shilajit_blog.png">
                    </div>
                    <div class="form-group">
                        <label for="body">Article Content (HTML Allowed) *</label>
                        <textarea name="body" id="body" rows="8" class="form-control" style="font-size:0.85rem; padding:8px;" required placeholder="<p>Article body content goes here...</p>"></textarea>
                    </div>
                    
                    <button type="submit" name="add_post" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                        Publish Article
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
