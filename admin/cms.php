<?php
// admin/cms.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$page_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Handle Page Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $slug = $_POST['page_slug'];
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);

    $stmt_u = $pdo->prepare("UPDATE cms_pages SET title = ?, body = ? WHERE slug = ?");
    $stmt_u->execute([$title, $body, $slug]);
    $action_msg = "Static page content updated successfully.";
    $page_slug = $slug; // keep current view
}

// Fetch single page if selected
$page = null;
if (!empty($page_slug)) {
    $stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = ?");
    $stmt->execute([$page_slug]);
    $page = $stmt->fetch();
}

// Fetch all CMS pages list
$stmt = $pdo->prepare("SELECT slug, title, updated_at FROM cms_pages");
$stmt->execute();
$pages_list = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Static Policy Pages</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage CMS text, terms & refund guidelines</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 200px 1fr; gap:30px; align-items:start;">
        
        <!-- Sidebar Navigation to pages -->
        <aside class="glass-card" style="padding:15px; border-radius:6px;">
            <h4 style="font-size:0.85rem; text-transform:uppercase; color:var(--gold-muted); margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:8px;">Policy Pages</h4>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:8px; font-size:0.9rem;">
                <?php foreach ($pages_list as $p): ?>
                    <li>
                        <a href="cms.php?slug=<?php echo $p['slug']; ?>" 
                           style="color: <?php echo $page_slug === $p['slug'] ? 'var(--gold-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo $page_slug === $p['slug'] ? '700' : 'normal'; ?>;">
                            <?php echo htmlspecialchars($p['title']); ?>
                        </a>
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
            <?php else: ?>
                <div class="glass-card" style="padding:40px; text-align:center; border-radius:6px; color:var(--text-muted);">
                    <i class="fas fa-edit" style="font-size:3rem; margin-bottom:15px; color:rgba(255,255,255,0.15);"></i>
                    <p>Select a policy page from the left menu to start editing its content.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
