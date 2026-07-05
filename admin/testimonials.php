<?php
// admin/testimonials.php — Testimonial List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $t_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE testimonials SET status = NOT status WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Testimonial status toggled.";
}

// Handle Featured Toggle
if (isset($_GET['feature_id'])) {
    $t_id = (int)$_GET['feature_id'];
    $stmt = $pdo->prepare("UPDATE testimonials SET is_featured = NOT is_featured WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Featured status toggled.";
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $t_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Testimonial deleted.";
}

// Success msg from add/edit redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'created') {
    $action_msg = "Testimonial added successfully.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $action_msg = "Testimonial updated successfully.";
}

// Fetch all testimonials
$stmt = $pdo->prepare("SELECT * FROM testimonials ORDER BY display_order ASC");
$stmt->execute();
$testimonials = $stmt->fetchAll();

$total_count = count($testimonials);
$active_count = 0;
$featured_count = 0;
foreach ($testimonials as $t) {
    if ($t['status']) $active_count++;
    if ($t['is_featured']) $featured_count++;
}
$inactive_count = $total_count - $active_count;
?>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Testimonials</h2>
            <p style="font-size:0.85rem; color:var(--text-muted);">Manage customer reviews and testimonials</p>
        </div>
        <a href="testimonial_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> Add Testimonial
        </a>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:28px;">
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-quote-left" style="color:#D4AF37; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $total_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Total</div>
            </div>
        </div>
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-toggle-on" style="color:#4ade80; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $active_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Active</div>
            </div>
        </div>
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-toggle-off" style="color:#ef4444; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $inactive_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Inactive</div>
            </div>
        </div>
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(250,204,21,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-star" style="color:#facc15; font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.6rem; font-weight:800; color:#fff; line-height:1;"><?php echo $featured_count; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;">Featured</div>
            </div>
        </div>
    </div>

    <?php if (empty($testimonials)): ?>
        <div class="glass-card" style="padding:60px 40px; text-align:center; border:2px dashed rgba(212,175,55,0.15);">
            <div style="width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                <i class="fas fa-quote-left" style="font-size:1.8rem; color:rgba(212,175,55,0.3);"></i>
            </div>
            <h3 style="font-size:1.2rem; color:#fff; margin-bottom:8px;">No Testimonials</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:20px;">Add your first customer testimonial.</p>
            <a href="testimonial_add.php" class="btn-gold" style="padding:12px 28px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.88rem;">
                <i class="fas fa-plus"></i> Add Testimonial
            </a>
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(420px, 1fr)); gap:16px;">
            <?php foreach ($testimonials as $t): ?>
                <div class="glass-card" style="padding:0; overflow:hidden; <?php echo !$t['status'] ? 'opacity:0.5;' : ''; ?>">
                    <div style="padding:22px 24px;">
                        <div style="display:flex; align-items:start; justify-content:space-between; gap:12px; margin-bottom:14px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:44px; height:44px; border-radius:50%; background:rgba(212,175,55,0.1); border:2px solid rgba(212,175,55,0.2); display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;">
                                    <?php if (!empty($t['avatar_url'])): ?>
                                        <img src="../<?php echo htmlspecialchars($t['avatar_url']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user" style="color:#D4AF37; font-size:0.9rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight:700; color:#fff; font-size:0.9rem;"><?php echo htmlspecialchars($t['customer_name']); ?></div>
                                    <?php if (!empty($t['customer_title'])): ?>
                                        <div style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:2px;"><?php echo htmlspecialchars($t['customer_title']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="display:flex; gap:2px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="font-size:0.65rem; color:<?php echo $i <= $t['rating'] ? '#facc15' : 'rgba(255,255,255,0.15)'; ?>;"></i>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <p style="font-size:0.85rem; color:rgba(255,255,255,0.7); line-height:1.7; margin:0 0 16px 0; font-style:italic;">
                            "<?php echo htmlspecialchars($t['testimonial_text']); ?>"
                        </p>

                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.06);">
                            <div style="display:flex; gap:6px; align-items:center;">
                                <span class="admin-badge <?php echo $t['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                    <?php echo $t['status'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <?php if ($t['is_featured']): ?>
                                    <span class="admin-badge" style="background:rgba(250,204,21,0.1); color:#facc15; border:1px solid rgba(250,204,21,0.15);">Featured</span>
                                <?php endif; ?>
                                <span style="font-size:0.65rem; color:rgba(255,255,255,0.3);">Order: <?php echo $t['display_order']; ?></span>
                            </div>
                            <div style="display:flex; gap:5px; flex-shrink:0;">
                                <a href="testimonial_edit.php?id=<?php echo $t['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.7rem; text-decoration:none;">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="testimonials.php?toggle_id=<?php echo $t['id']; ?>" title="Toggle Status" style="width:30px; height:30px; border-radius:6px; background:<?php echo $t['status'] ? 'rgba(74,222,128,0.08)' : 'rgba(255,255,255,0.04)'; ?>; border:1px solid <?php echo $t['status'] ? 'rgba(74,222,128,0.15)' : 'rgba(255,255,255,0.08)'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $t['status'] ? '#4ade80' : 'rgba(255,255,255,0.35)'; ?>; font-size:0.7rem; text-decoration:none;">
                                    <i class="fas fa-<?php echo $t['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                </a>
                                <a href="testimonials.php?feature_id=<?php echo $t['id']; ?>" title="Toggle Featured" style="width:30px; height:30px; border-radius:6px; background:<?php echo $t['is_featured'] ? 'rgba(250,204,21,0.08)' : 'rgba(255,255,255,0.04)'; ?>; border:1px solid <?php echo $t['is_featured'] ? 'rgba(250,204,21,0.15)' : 'rgba(255,255,255,0.08)'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $t['is_featured'] ? '#facc15' : 'rgba(255,255,255,0.35)'; ?>; font-size:0.7rem; text-decoration:none;">
                                    <i class="fas fa-star"></i>
                                </a>
                                <a href="testimonials.php?delete_id=<?php echo $t['id']; ?>" title="Delete" onclick="return confirm('Delete this testimonial?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.12); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.7rem; text-decoration:none;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
