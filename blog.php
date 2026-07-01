<?php
// blog.php
require_once __DIR__ . '/includes/header.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 1 ORDER BY published_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>The Wellness Pack Blog</h2>
            <p>Scientific insights, biohacking strategies and Ayurvedic guides</p>
        </div>

        <?php if (empty($posts)): ?>
            <div class="glass-card" style="padding:40px; text-align:center; border-radius:8px;">
                <p style="color:var(--text-muted);">No blog posts published yet.</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $blog): ?>
                    <div class="blog-card">
                        <div class="blog-card-image">
                            <img src="<?php echo htmlspecialchars($blog['cover_image'] ? $blog['cover_image'] : 'assets/images/blog/default.png'); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                            <span class="blog-card-badge"><?php echo htmlspecialchars($blog['category_tag']); ?></span>
                        </div>
                        <div class="blog-card-content">
                            <div class="blog-card-date"><?php echo date('M d, Y', strtotime($blog['published_at'])); ?></div>
                            <a href="blog-post.php?slug=<?php echo $blog['slug']; ?>">
                                <h3 class="blog-card-title"><?php echo htmlspecialchars($blog['title']); ?></h3>
                            </a>
                            <p class="blog-card-excerpt">
                                <?php 
                                $text = strip_tags($blog['body']);
                                echo htmlspecialchars(strlen($text) > 120 ? substr($text, 0, 117) . '...' : $text); 
                                ?>
                            </p>
                            <a href="blog-post.php?slug=<?php echo $blog['slug']; ?>" class="blog-card-link">
                                Read Article <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
