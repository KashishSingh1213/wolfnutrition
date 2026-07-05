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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Static Policy Pages</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage CMS text, terms & refund guidelines</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($action_error) && $action_error): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(220,53,69,0.05); border-color:rgba(220,53,69,0.3); color:var(--danger-color); margin-bottom:25px;">
            ⚠️ <?php echo htmlspecialchars($action_error); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 200px 1fr; gap:30px; align-items:start;">
        
        <!-- Sidebar Navigation to pages -->
        <aside class="glass-card" style="padding:15px; border-radius:6px;">
            <h4 style="font-size:0.85rem; text-transform:uppercase; color:var(--gold-muted); margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">Policy Pages</h4>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:8px; font-size:0.9rem;">
                <?php foreach ($pages_list as $p): ?>
                    <li style="display:flex; justify-content:space-between; align-items:center;">
                        <a href="cms.php?slug=<?php echo $p['slug']; ?>" 
                           style="color: <?php echo $page_slug === $p['slug'] ? 'var(--gold-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo $page_slug === $p['slug'] ? '700' : 'normal'; ?>;">
                            <?php echo htmlspecialchars($p['title']); ?>
                        </a>
                        <a href="cms.php?delete_id=<?php echo $p['id']; ?>" 
                           style="color:var(--danger-color); font-size:0.7rem; font-weight:700;" 
                           onclick="return confirm('Delete this page? This cannot be undone.');" title="Delete">✕</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Editor panel -->
        <div>
            <?php if ($page): ?>
                <div class="glass-card" style="padding:25px; border-radius:6px;">
                    <h3 style="font-size:1.25rem; color:#fff; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:10px;">
                        Edit Page: <?php echo htmlspecialchars($page['title']); ?>
                    </h3>
                    
                    <form action="cms.php?slug=<?php echo htmlspecialchars($page_slug); ?>" method="POST">
                        <input type="hidden" name="page_slug" value="<?php echo htmlspecialchars($page['slug']); ?>">
                        
                        <div class="form-group">
                            <label for="title">Page Title *</label>
                            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($page['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="body">Page HTML Body Content *</label>
                            <textarea name="body" id="body" rows="12" class="form-control" style="font-family:monospace; font-size:0.85rem; line-height:1.5;" required><?php echo htmlspecialchars($page['body']); ?></textarea>
                        </div>

                        <button type="submit" name="update_page" class="btn-gold" style="padding:10px 25px;">
                            Save Changes
                        </button>
                    </form>
                </div>
            <?php elseif (isset($_GET['new'])): ?>
                <!-- Create New Page Form -->
                <div class="glass-card" style="padding:25px; border-radius:6px;">
                    <h3 style="font-size:1.25rem; color:#fff; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:10px;">
                        Create New Page
                    </h3>
                    
                    <form action="cms.php" method="POST">
                        <div class="form-group">
                            <label for="slug">Page Slug (URL-friendly identifier) *</label>
                            <input type="text" name="slug" id="slug" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. shipping-policy" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Page Title *</label>
                            <input type="text" name="title" id="title" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. Shipping Policy" required>
                        </div>
                        <div class="form-group">
                            <label for="body">Page HTML Body Content *</label>
                            <textarea name="body" id="body" rows="12" class="form-control" style="font-family:monospace; font-size:0.85rem; line-height:1.5;" required placeholder="<p>Page content goes here...</p>"></textarea>
                        </div>

                        <div style="display:flex; gap:10px; margin-top:10px;">
                            <button type="submit" name="create_page" class="btn-gold" style="padding:10px 25px;">
                                Create Page
                            </button>
                            <a href="cms.php" class="btn-outline-gold" style="padding:10px 20px; font-size:0.85rem; text-align:center;">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="glass-card" style="padding:40px; text-align:center; border-radius:6px; color:var(--text-muted);">
                    <i class="fas fa-edit" style="font-size:3rem; margin-bottom:15px; color:rgba(255,255,255,0.15);"></i>
                    <p>Select a policy page from the left menu to start editing its content.</p>
                    <a href="cms.php?new=1" class="btn-gold" style="display:inline-block; margin-top:20px; padding:10px 25px; font-size:0.85rem;">
                        + Create New Page
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
