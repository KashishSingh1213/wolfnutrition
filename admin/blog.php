<?php
// admin/blog.php — Blog Posts List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Toggle Status
if (isset($_GET['toggle_id'])) {
    $post_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE blog_posts SET status = NOT status WHERE id = ?");
    $stmt->execute([$post_id]);
    $action_msg = "Article status toggled.";
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $post_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $action_msg = "Article deleted.";
}

// Fetch all posts with author info
$stmt = $pdo->prepare("
    SELECT bp.*, u.name AS author_name
    FROM blog_posts bp
    LEFT JOIN users u ON bp.author_user_id = u.id
    ORDER BY bp.published_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();

$total_count = count($posts);
$published_count = 0;
$draft_count = 0;
foreach ($posts as $p) { if ($p['status']) $published_count++; else $draft_count++; }
?>

    <style>
        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .blog-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .blog-header-actions {
                width: 100% !important;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .blog-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .blog-header-actions {
                width: 100% !important;
                flex-wrap: wrap !important;
            }
            .blog-header-actions a {
                flex: 1 !important;
                justify-content: center !important;
                min-width: 0 !important;
            }
            .blog-stats-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            /* Table → cards */
            .blog-table thead {
                display: none !important;
            }
            .blog-table,
            .blog-table tbody,
            .blog-table tr,
            .blog-table td {
                display: block !important;
                width: 100% !important;
            }
            .blog-table tbody tr {
                background: rgba(18,18,18,0.4);
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 10px;
                padding: 14px 16px;
                margin: 0 16px 10px 16px;
            }
            .blog-table tbody tr:first-child {
                margin-top: 10px;
            }
            .blog-table tbody td {
                padding: 3px 0 !important;
                border-bottom: none !important;
                font-size: 0.85rem;
            }
            .blog-table tbody td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.62rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.7px;
                color: rgba(255,255,255,0.3);
                margin-bottom: 1px;
            }
            .blog-table tbody td.blog-td-title::before { display: none; }
            .blog-table tbody td.blog-td-title {
                font-size: 0.95rem;
                padding-bottom: 6px !important;
                border-bottom: 1px solid rgba(255,255,255,0.04) !important;
            }
            .blog-table tbody td.blog-td-title img,
            .blog-table tbody td.blog-td-title div > img {
                width: 44px !important;
                height: 44px !important;
            }
            .blog-table tbody td.blog-td-actions::before { display: none; }
            .blog-table tbody td.blog-td-actions {
                padding-top: 8px !important;
                border-top: 1px solid rgba(255,255,255,0.04);
            }
            .blog-table tbody td.blog-td-actions .blog-action-btns {
                width: 100% !important;
            }
            .blog-table tbody td.blog-td-actions .blog-action-btns a {
                flex: 1 !important;
                justify-content: center !important;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="blog-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Blog Manager</h2>
            <p style="font-size:0.85rem; color:var(--text-muted);">Author and publish wellness articles</p>
        </div>
        <div class="blog-header-actions" style="display:flex; gap:10px;">
            <a href="blog_categories.php" style="padding:10px 18px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; border:1px solid rgba(212,175,55,0.2); color:var(--gold-primary); border-radius:10px; background:rgba(212,175,55,0.06);">
                <i class="fas fa-folder"></i> Categories
            </a>
            <a href="blog_tags.php" style="padding:10px 18px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; border:1px solid rgba(212,175,55,0.2); color:var(--gold-primary); border-radius:10px; background:rgba(212,175,55,0.06);">
                <i class="fas fa-tags"></i> Tags
            </a>
            <a href="blog_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-plus"></i> New Article
            </a>
        </div>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="blog-stats-grid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:28px;">
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-newspaper" style="color:#D4AF37; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $total_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Total</div>
            </div>
        </div>
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-eye" style="color:#4ade80; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $published_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Published</div>
            </div>
        </div>
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-eye-slash" style="color:#ef4444; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $draft_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Drafts</div>
            </div>
        </div>
    </div>

    <?php if (empty($posts)): ?>
        <div class="glass-card" style="padding:60px 40px; text-align:center; border:2px dashed rgba(212,175,55,0.15);">
            <div style="width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                <i class="fas fa-pen-fancy" style="font-size:1.8rem; color:rgba(212,175,55,0.3);"></i>
            </div>
            <h3 style="font-size:1.2rem; color:#fff; margin-bottom:8px;">No Articles Yet</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:20px;">Write your first wellness article to share with customers.</p>
            <a href="blog_add.php" class="btn-gold" style="padding:12px 28px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.88rem;">
                <i class="fas fa-plus"></i> Write First Article
            </a>
        </div>
    <?php else: ?>
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-newspaper" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">All Articles</h3>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table blog-table" style="margin-top:0; border:none; border-radius:0;">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th style="width:130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td data-label="" class="blog-td-title">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <?php if ($post['cover_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($post['cover_image']); ?>" alt="" style="width:52px; height:52px; border-radius:8px; object-fit:cover; border:1px solid rgba(255,255,255,0.08);">
                                        <?php else: ?>
                                            <div style="width:52px; height:52px; border-radius:8px; background:rgba(212,175,55,0.06); border:1px solid rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:center;">
                                                <i class="fas fa-image" style="color:rgba(255,255,255,0.15);"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div style="min-width:0;">
                                            <div style="font-weight:700; color:#fff; font-size:0.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:260px;">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:8px; margin-top:3px;">
                                                <?php if ($post['article_type']): ?>
                                                    <span style="font-size:0.62rem; font-weight:600; color:#3b82f6; background:rgba(59,130,246,0.1); padding:2px 7px; border-radius:4px;"><?php echo htmlspecialchars($post['article_type']); ?></span>
                                                <?php endif; ?>
                                                <?php if ($post['tags']): ?>
                                                    <span style="font-size:0.62rem; color:rgba(255,255,255,0.3);"><?php echo htmlspecialchars($post['tags']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Category">
                                    <span style="font-size:0.72rem; font-weight:600; color:#D4AF37; background:rgba(212,175,55,0.08); padding:4px 10px; border-radius:20px; border:1px solid rgba(212,175,55,0.12);">
                                        <?php echo htmlspecialchars($post['category_tag']); ?>
                                    </span>
                                </td>
                                <td data-label="Author">
                                    <div style="font-size:0.82rem; color:rgba(255,255,255,0.6);">
                                        <?php echo htmlspecialchars($post['author_name'] ?? $post['custom_author'] ?? 'Admin'); ?>
                                    </div>
                                </td>
                                <td data-label="Date">
                                    <div style="font-size:0.8rem; color:rgba(255,255,255,0.5);"><?php echo date('M d, Y', strtotime($post['published_at'])); ?></div>
                                </td>
                                <td data-label="Status">
                                    <span class="admin-badge <?php echo $post['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $post['status'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td data-label="" class="blog-td-actions">
                                    <div class="blog-action-btns" style="display:flex; gap:6px;">
                                        <a href="blog_edit.php?id=<?php echo $post['id']; ?>" title="Edit" style="width:32px; height:32px; border-radius:8px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem; text-decoration:none;">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="blog.php?toggle_id=<?php echo $post['id']; ?>" title="<?php echo $post['status'] ? 'Unpublish' : 'Publish'; ?>" style="width:32px; height:32px; border-radius:8px; background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.15); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem; text-decoration:none;">
                                            <i class="fas fa-<?php echo $post['status'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="blog.php?delete_id=<?php echo $post['id']; ?>" title="Delete" onclick="return confirm('Delete this article?')" style="width:32px; height:32px; border-radius:8px; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.12); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem; text-decoration:none;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
