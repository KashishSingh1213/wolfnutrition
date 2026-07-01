<?php
// blog-post.php
require_once __DIR__ . '/includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: blog.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} catch (PDOException $e) {
    $post = null;
}

if (!$post) {
    header("Location: blog.php");
    exit();
}

// Fetch other posts for sidebar
try {
    $stmt = $pdo->prepare("SELECT title, slug, published_at FROM blog_posts WHERE slug != ? AND status = 1 ORDER BY published_at DESC LIMIT 4");
    $stmt->execute([$slug]);
    $recent = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent = [];
}
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:25px;">
            <a href="index.php">Home</a> &nbsp;/&nbsp; 
            <a href="blog.php">Blog</a> &nbsp;/&nbsp; 
            <span style="color:var(--text-primary);"><?php echo htmlspecialchars($post['title']); ?></span>
        </div>

        <div style="display:grid; grid-template-columns: 2.2fr 1fr; gap:40px; align-items:start;">
            <!-- Article Body -->
            <article class="glass-card" style="padding: 40px; border-radius: 8px;">
                <span class="blog-card-badge" style="position:static; margin-bottom:15px; display:inline-block;"><?php echo htmlspecialchars($post['category_tag']); ?></span>
                
                <h1 style="font-size:2.4rem; line-height:1.2; margin: 10px 0 15px 0; color:#fff;"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:30px;">
                    <i class="far fa-calendar-alt" style="margin-right:5px;"></i> Published on <?php echo date('F d, Y', strtotime($post['published_at'])); ?>
                </div>

                <?php if ($post['cover_image']): ?>
                    <div style="width:100%; height:380px; overflow:hidden; border-radius:6px; margin-bottom:35px; border:1px solid var(--border-color);">
                        <img src="<?php echo htmlspecialchars($post['cover_image']); ?>" alt="Cover Image" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                <?php endif; ?>

                <!-- Article Content -->
                <div class="blog-content-body" style="line-height:1.8; font-size:1.05rem; color:#ddd;">
                    <?php echo $post['body']; ?>
                </div>
            </article>

            <!-- Sidebar -->
            <aside style="display:flex; flex-direction:column; gap:25px;">
                <!-- Recent Articles -->
                <?php if (!empty($recent)): ?>
                    <div class="glass-card" style="padding: 25px; border-radius:8px;">
                        <h3 style="font-size:1.1rem; text-transform:uppercase; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:10px; color:var(--gold-primary);">Recent Articles</h3>
                        <div style="display:flex; flex-direction:column; gap:15px;">
                            <?php foreach ($recent as $rec): ?>
                                <div>
                                    <a href="blog-post.php?slug=<?php echo $rec['slug']; ?>" style="font-weight:700; font-size:0.95rem; color:#fff; display:block; line-height:1.3; margin-bottom:4px;">
                                        <?php echo htmlspecialchars($rec['title']); ?>
                                    </a>
                                    <span style="font-size:0.75rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($rec['published_at'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Promotional banner inside blog -->
                <div class="glass-card" style="padding:25px; border-radius:8px; text-align:center; background:linear-gradient(135deg, rgba(212,175,55,0.05) 0%, rgba(0,0,0,0.9) 100%);">
                    <h4 style="font-size:1.2rem; text-transform:uppercase; margin-bottom:10px; color:var(--gold-primary);">Wolfpack Supplement</h4>
                    <p style="font-size:0.85rem; margin-bottom:15px;">Increase testosterone, stamina & peak vitality naturally.</p>
                    <a href="product.php?slug=wolfpack-unleash-the-alpha-within" class="btn-gold" style="padding:8px 20px; font-size:0.8rem;">View Pack</a>
                </div>
            </aside>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
