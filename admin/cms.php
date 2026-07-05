<?php
// admin/cms.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$page_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Handle Create New Page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_page'])) {
    $slug = trim($_POST['slug']);
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);

    if (empty($slug) || empty($title) || empty($body)) {
        $action_error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM cms_pages WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $action_error = "A page with this slug already exists.";
        } else {
            $stmt_i = $pdo->prepare("INSERT INTO cms_pages (slug, title, body) VALUES (?, ?, ?)");
            $stmt_i->execute([$slug, $title, $body]);
            $action_msg = "New page created successfully.";
            $page_slug = $slug;
        }
    }
}

// Handle Page Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $slug = $_POST['page_slug'];
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);

    $stmt_u = $pdo->prepare("UPDATE cms_pages SET title = ?, body = ? WHERE slug = ?");
    $stmt_u->execute([$title, $body, $slug]);
    $action_msg = "Static page content updated successfully.";
    $page_slug = $slug;
}

// Handle Delete Page
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM cms_pages WHERE id = ?");
    $stmt->execute([$del_id]);
    $action_msg = "Page deleted successfully.";
    $page_slug = '';
}

// Fetch single page if selected
$page = null;
if (!empty($page_slug)) {
    $stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = ?");
    $stmt->execute([$page_slug]);
    $page = $stmt->fetch();
}

// Fetch all CMS pages list
$stmt = $pdo->prepare("SELECT id, slug, title, updated_at FROM cms_pages ORDER BY title ASC");
$stmt->execute();
$pages_list = $stmt->fetchAll();
?>

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Static Policy Pages</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Manage CMS text, terms & refund guidelines</p>
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

    <div style="display:grid; grid-template-columns: 260px 1fr; gap:28px; align-items:start;">

        <!-- Sidebar Page List -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-file-alt" style="color:#D4AF37; font-size:0.85rem;"></i>
                    <h3 style="font-size:0.85rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Policy Pages</h3>
                </div>
                <a href="cms.php?new=1" title="Create New Page" style="width:28px; height:28px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.7rem; text-decoration:none;">
                    <i class="fas fa-plus"></i>
                </a>
            </div>

            <div style="display:flex; flex-direction:column; padding:8px;">
                <?php foreach ($pages_list as $p): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:8px; margin-bottom:2px; <?php echo $page_slug === $p['slug'] ? 'background:rgba(212,175,55,0.08);' : 'transition:background 0.15s;'; ?>">
                        <a href="cms.php?slug=<?php echo $p['slug']; ?>" style="flex:1; min-width:0; display:block; text-decoration:none;">
                            <div style="font-size:0.82rem; font-weight:<?php echo $page_slug === $p['slug'] ? '700' : '500'; ?>; color:<?php echo $page_slug === $p['slug'] ? '#D4AF37' : 'rgba(255,255,255,0.6)'; ?>; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($p['title']); ?>
                            </div>
                            <div style="font-size:0.65rem; color:rgba(255,255,255,0.25); font-family:monospace; margin-top:2px;">
                                /<?php echo htmlspecialchars($p['slug']); ?>
                            </div>
                        </a>
                        <a href="cms.php?delete_id=<?php echo $p['id']; ?>" onclick="return confirm('Delete this page? This cannot be undone.');" title="Delete" style="width:24px; height:24px; border-radius:4px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.2); font-size:0.65rem; text-decoration:none; transition:all 0.15s; flex-shrink:0; margin-left:8px;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Editor Panel -->
        <div>
            <?php if ($page): ?>
                <div class="glass-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-edit" style="color:#D4AF37; font-size:0.9rem;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Edit Page</h3>
                            <p style="font-size:0.72rem; color:rgba(255,255,255,0.35); margin-top:2px;"><?php echo htmlspecialchars($page['title']); ?></p>
                        </div>
                    </div>

                    <form action="cms.php?slug=<?php echo htmlspecialchars($page_slug); ?>" method="POST" style="padding:24px;">
                        <input type="hidden" name="page_slug" value="<?php echo htmlspecialchars($page['slug']); ?>">

                        <div class="form-group">
                            <label for="title" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Page Title *</label>
                            <input type="text" name="title" id="title" class="form-control" required
                                value="<?php echo htmlspecialchars($page['title']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="body" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Page HTML Body Content *</label>
                            <textarea name="body" id="body" rows="14" class="form-control" required><?php echo htmlspecialchars($page['body']); ?></textarea>
                        </div>

                        <div style="display:flex; gap:10px; margin-top:8px;">
                            <button type="submit" name="update_page" class="btn-gold" style="flex:1; padding:12px 20px;">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

            <?php elseif (isset($_GET['new'])): ?>
                <div class="glass-card" style="padding:0; overflow:hidden;">
                    <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-plus" style="color:#D4AF37; font-size:0.9rem;"></i>
                        </div>
                        <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Create New Page</h3>
                    </div>

                    <form action="cms.php" method="POST" style="padding:24px;">
                        <div class="form-group">
                            <label for="slug" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Page Slug (URL identifier) *</label>
                            <input type="text" name="slug" id="slug" class="form-control" placeholder="e.g. shipping-policy" required style="font-family:monospace;">
                        </div>

                        <div class="form-group">
                            <label for="title" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Page Title *</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Shipping Policy" required>
                        </div>

                        <div class="form-group">
                            <label for="body" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Page HTML Body Content *</label>
                            <textarea name="body" id="body" rows="14" class="form-control" required placeholder="<p>Page content goes here...</p>"></textarea>
                        </div>

                        <div style="display:flex; gap:10px; margin-top:8px;">
                            <button type="submit" name="create_page" class="btn-gold" style="flex:1; padding:12px 20px;">
                                <i class="fas fa-plus"></i> Create Page
                            </button>
                            <a href="cms.php" class="btn-outline-gold" style="padding:12px 20px; text-align:center;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <div class="glass-card" style="padding:64px 32px; text-align:center;">
                    <div style="width:64px; height:64px; border-radius:16px; background:rgba(255,255,255,0.03); display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                        <i class="fas fa-file-edit" style="font-size:1.8rem; color:rgba(255,255,255,0.12);"></i>
                    </div>
                    <h3 style="font-size:1rem; font-weight:600; color:rgba(255,255,255,0.6); margin-bottom:8px;">No Page Selected</h3>
                    <p style="font-size:0.85rem; color:rgba(255,255,255,0.3); margin-bottom:24px;">Select a policy page from the left menu to start editing.</p>
                    <a href="cms.php?new=1" class="btn-gold" style="display:inline-flex; padding:12px 28px;">
                        <i class="fas fa-plus"></i> Create New Page
                    </a>
                </div>
            <?php endif; ?>
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
