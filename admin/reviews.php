<?php
// admin/reviews.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle approvals
if (isset($_GET['approve_id'])) {
    $rev_id = (int)$_GET['approve_id'];
    $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
    $stmt->execute([$rev_id]);
    $action_msg = "Review approved and published to product page.";
}

// Handle feature toggling
if (isset($_GET['feature_id'])) {
    $rev_id = (int)$_GET['feature_id'];
    $stmt = $pdo->prepare("UPDATE reviews SET is_featured = NOT is_featured WHERE id = ?");
    $stmt->execute([$rev_id]);
    $action_msg = "Review featured status toggled.";
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $rev_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$rev_id]);
    $action_msg = "Review deleted successfully.";
}

// Fetch all reviews
$stmt = $pdo->prepare("
    SELECT r.*, p.name as p_name 
    FROM reviews r 
    JOIN products p ON r.product_id = p.id 
    ORDER BY r.is_approved ASC, r.created_at DESC
");
$stmt->execute();
$reviews = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Reviews Moderation</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage storefront customer feedback</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="padding: 25px; border-radius:6px;">
        <?php if (empty($reviews)): ?>
            <p style="color:var(--text-muted); text-align:center; padding:30px 0;">No product reviews submitted yet.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Reviewer Details</th>
                        <th>Rating</th>
                        <th>Review content</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td><strong style="color:#fff;"><?php echo htmlspecialchars($rev['p_name']); ?></strong></td>
                            <td>
                                <div style="font-weight:600; color:#ddd;"><?php echo htmlspecialchars($rev['user_name']); ?></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></div>
                            </td>
                            <td style="color:var(--gold-light);">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="<?php echo $i <= $rev['rating'] ? 'fas' : 'far'; ?> fa-star" style="font-size:0.8rem;"></i>
                                <?php endfor; ?>
                            </td>
                            <td style="max-width:300px;">
                                <div style="font-weight:700; color:#eee;"><?php echo htmlspecialchars($rev['title']); ?></div>
                                <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:4px;"><?php echo htmlspecialchars($rev['review_text']); ?></div>
                            </td>
                            <td>
                                <span class="admin-badge <?php echo $rev['is_approved'] ? 'badge-completed' : 'badge-pending'; ?>">
                                    <?php echo $rev['is_approved'] ? 'Approved' : 'Pending'; ?>
                                </span>
                                <?php if ($rev['is_featured']): ?>
                                    <div style="font-size:0.65rem; background:rgba(212,175,55,0.2); color:var(--gold-primary); font-weight:800; display:inline-block; padding:2px 6px; border-radius:3px; margin-top:5px; text-transform:uppercase;">Featured</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; flex-direction:column; gap:6px;">
                                    <?php if (!$rev['is_approved']): ?>
                                        <a href="reviews.php?approve_id=<?php echo $rev['id']; ?>" class="btn-gold" style="padding:4px 8px; font-size:0.7rem; text-align:center;">Approve</a>
                                    <?php endif; ?>
                                    <a href="reviews.php?feature_id=<?php echo $rev['id']; ?>" class="btn-outline-gold" style="padding:4px 8px; font-size:0.7rem; text-align:center;">
                                        <?php echo $rev['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                    </a>
                                    <a href="reviews.php?delete_id=<?php echo $rev['id']; ?>" class="btn-outline-gold" style="padding:4px 8px; font-size:0.7rem; text-align:center; color:var(--danger-color); border-color:var(--danger-color);" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
