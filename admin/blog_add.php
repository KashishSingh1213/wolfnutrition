<?php
// admin/blog_add.php — Add Blog Article
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Fetch categories
$stmt_cat = $pdo->prepare("SELECT * FROM blog_categories WHERE status = 1 ORDER BY display_order ASC");
$stmt_cat->execute();
$categories = $stmt_cat->fetchAll();

// Fetch system users for author dropdown
$stmt_users = $pdo->prepare("SELECT id, name, email FROM users WHERE is_active = 1 ORDER BY name ASC");
$stmt_users->execute();
$users = $stmt_users->fetchAll();

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $article_type = trim($_POST['article_type']);
    $category_tag = trim($_POST['category_tag']);
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
    $author_user_id = !empty($_POST['author_user_id']) ? (int)$_POST['author_user_id'] : null;
    $custom_author = trim($_POST['custom_author']);
    $editor_name = trim($_POST['editor_name']);
    $publish_at = trim($_POST['publish_at']);
    $reading_time = (int)$_POST['reading_time'];
    $excerpt = trim($_POST['excerpt']);
    $body = trim($_POST['body']);
    $alt_text = trim($_POST['alt_text']);
    $tags = trim($_POST['tags']);

    // Auto-generate slug if blank
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }

    // Handle cover image upload
    $cover_image = '';
    if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_image_file'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/blog/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'blog_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $cover_image = 'uploads/blog/' . $filename;
            }
        }
    }

    if (empty($title) || empty($body)) {
        $action_error = "Title and Content Body are required.";
    } else {
        // Check slug uniqueness
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) $slug .= '-' . time();

        $publish_dt = !empty($publish_at) ? date('Y-m-d H:i:s', strtotime($publish_at)) : date('Y-m-d H:i:s');

        $stmt_i = $pdo->prepare("INSERT INTO blog_posts (title, slug, category_tag, article_type, cover_image, body, status, author_user_id, custom_author, editor_name, published_at, reading_time, excerpt, alt_text, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_i->execute([$title, $slug, $category_tag, $article_type, $cover_image, $body, $status, $author_user_id, $custom_author, $editor_name, $publish_dt, $reading_time, $excerpt, $alt_text, $tags]);
        header("Location: blog.php?msg=created");
        exit();
    }
}

$total_posts = (int)$pdo->query("SELECT COUNT(id) FROM blog_posts")->fetchColumn();
?>

    <style>
        @media (max-width: 1024px) {
            .badd-grid { grid-template-columns: 1fr !important; }
            .badd-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 8px; }
        }
        @media (max-width: 768px) {
            .badd-grid { grid-template-columns: 1fr !important; }
            .badd-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 8px; }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="blog.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Blog
        </a>
    </div>

    <div class="badd-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">New Article</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total articles: <strong style="color:var(--gold-primary);"><?php echo $total_posts; ?></strong></div>
    </div>

    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <form action="blog_add.php" method="POST" enctype="multipart/form-data">
    <div class="badd-grid" style="display:grid; grid-template-columns:1fr 340px; gap:28px; align-items:start;">

        <!-- Main Content -->
        <div>
            <!-- Article Details Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:20px;">
                <div style="padding:18px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-pen-fancy" style="color:#D4AF37; font-size:0.85rem;"></i>
                    <h3 style="font-size:0.9rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">Article Details</h3>
                </div>
                <div style="padding:24px;">
                    <div class="form-group" style="margin-bottom:18px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Article Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="Enter full article title...">
                    </div>
                    <div class="form-group" style="margin-bottom:18px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">URL Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="Auto-generated if blank">
                    </div>
                </div>
            </div>

            <!-- Content Body Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:20px;">
                <div style="padding:18px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-align-left" style="color:#D4AF37; font-size:0.85rem;"></i>
                    <h3 style="font-size:0.9rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">Content Body</h3>
                </div>
                <div style="padding:24px;">
                    <textarea name="body" id="body" rows="16" class="form-control" required placeholder="Write your article content here..."></textarea>
                </div>
            </div>

            <!-- Excerpt Card -->
            <div class="glass-card" style="padding:0; overflow:hidden;">
                <div style="padding:18px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-align-center" style="color:#D4AF37; font-size:0.85rem;"></i>
                    <h3 style="font-size:0.9rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">Excerpt</h3>
                </div>
                <div style="padding:24px;">
                    <textarea name="excerpt" id="excerpt" rows="3" class="form-control" maxlength="300" placeholder="Brief summary (max 300 characters)" oninput="document.getElementById('excerpt-count').textContent=this.value.length"></textarea>
                    <div style="text-align:right; font-size:0.72rem; color:rgba(255,255,255,0.3); margin-top:4px;">
                        <span id="excerpt-count">0</span>/300
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="position:sticky; top:96px;">
            <!-- Publish Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:16px;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Publish</h4>
                </div>
                <div style="padding:20px;">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="0">Draft</option>
                            <option value="1">Published</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Publish At</label>
                        <input type="datetime-local" name="publish_at" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>
            </div>

            <!-- Category Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:16px;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Category</h4>
                </div>
                <div style="padding:20px;">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Article Type</label>
                        <select name="article_type" class="form-control">
                            <option value="Blog">Blog</option>
                            <option value="News">News</option>
                            <option value="Guide">Guide</option>
                            <option value="Review">Review</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Category</label>
                        <select name="category_tag" class="form-control">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Author Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:16px;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Author</h4>
                </div>
                <div style="padding:20px;">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">System User</label>
                        <select name="author_user_id" class="form-control">
                            <option value="">-- Select --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">OR Custom Author Name</label>
                        <input type="text" name="custom_author" class="form-control" placeholder="E.g. Guest Contributor">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Editor</label>
                        <input type="text" name="editor_name" class="form-control" placeholder="Editor name">
                    </div>
                </div>
            </div>

            <!-- Featured Image Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:16px;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Featured Image</h4>
                </div>
                <div style="padding:20px;">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Image</label>
                        <input type="file" name="cover_image_file" class="form-control" accept="image/*" style="padding:10px;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Alt Text</label>
                        <input type="text" name="alt_text" class="form-control" placeholder="Describe the image">
                    </div>
                </div>
            </div>

            <!-- Tags & Reading Card -->
            <div class="glass-card" style="padding:0; overflow:hidden; margin-bottom:16px;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Tags & Reading</h4>
                </div>
                <div style="padding:20px;">
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Tags (comma separated)</label>
                        <input type="text" name="tags" class="form-control" placeholder="e.g. shilajit, stamina, health">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:6px; display:block;">Reading Time (mins)</label>
                        <input type="number" name="reading_time" class="form-control" min="1" value="5">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="create_post" class="btn-gold" style="width:100%; padding:14px 20px; font-size:0.9rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fas fa-paper-plane"></i> Publish Article
            </button>
        </div>
    </div>
    </form>

<!-- Trumbowyg Editor -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/langs/fr.min.js"></script>
<script>
$(document).ready(function() {
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
        file: { serverPath: 'upload_handler.php' }
    });

    // Auto-generate slug from title
    var titleInput = $('input[name="title"]');
    var slugInput = $('input[name="slug"]');
    titleInput.on('input', function() {
        slugInput.val($(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
