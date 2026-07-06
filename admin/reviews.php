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

    <style>
        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .rev-stats-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .rev-stats-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            /* Table → card layout */
            .rev-table thead {
                display: none !important;
            }
            .rev-table,
            .rev-table tbody,
            .rev-table tr,
            .rev-table td {
                display: block !important;
                width: 100% !important;
            }
            .rev-table tbody tr {
                background: rgba(18,18,18,0.4);
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 10px;
                padding: 14px 16px;
                margin: 0 16px 10px 16px;
            }
            .rev-table tbody tr:first-child {
                margin-top: 10px;
            }
            .rev-table tbody td {
                padding: 3px 0 !important;
                border-bottom: none !important;
                font-size: 0.85rem;
            }
            .rev-table tbody td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.62rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.7px;
                color: rgba(255,255,255,0.3);
                margin-bottom: 1px;
            }
            .rev-table tbody td.rev-td-product::before { display: none; }
            .rev-table tbody td.rev-td-product {
                font-size: 0.95rem;
                padding-bottom: 6px !important;
                border-bottom: 1px solid rgba(255,255,255,0.04) !important;
            }
            .rev-table tbody td.rev-td-review {
                max-width: none !important;
            }
            .rev-table tbody td.rev-td-actions::before { display: none; }
            .rev-table tbody td.rev-td-actions {
                padding-top: 8px !important;
                border-top: 1px solid rgba(255,255,255,0.04);
            }
            .rev-table tbody td.rev-td-actions .rev-action-btns {
                width: 100% !important;
            }
            .rev-table tbody td.rev-td-actions .rev-action-btns a {
                flex: 1 !important;
                justify-content: center !important;
            }
        }
    </style>

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Reviews Moderation</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Manage customer feedback and ratings</p>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Bar -->
    <div class="rev-stats-grid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:28px;">
        <?php
        $total = count($reviews);
        $approved = 0;
        $pending = 0;
        foreach ($reviews as $r) {
            if ($r['is_approved']) $approved++;
            else $pending++;
        }
        ?>
        <div class="glass-card" style="padding:16px 20px; display:flex; align-items:center; gap:14px;">
            <div style="width:40px; height:40px; border-radius:10px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-star" style="color:#D4AF37; font-size:0.9rem;"></i>
            </div>
            <div>
                <div style="font-size:1.5rem; font-weight:800; color:#fff; line-height:1;"> <?php echo $total; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:2px;">Total Reviews</div>
            </div>
        </div>
        <div class="glass-card" style="padding:16px 20px; display:flex; align-items:center; gap:14px;">
            <div style="width:40px; height:40px; border-radius:10px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-check-circle" style="color:#4ade80; font-size:0.9rem;"></i>
            </div>
            <div>
                <div style="font-size:1.5rem; font-weight:800; color:#fff; line-height:1;"> <?php echo $approved; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:2px;">Approved</div>
            </div>
        </div>
        <div class="glass-card" style="padding:16px 20px; display:flex; align-items:center; gap:14px;">
            <div style="width:40px; height:40px; border-radius:10px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-clock" style="color:#ef4444; font-size:0.9rem;"></i>
            </div>
            <div>
                <div style="font-size:1.5rem; font-weight:800; color:#fff; line-height:1;"> <?php echo $pending; ?></div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-top:2px;">Pending</div>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="glass-card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Customer Reviews</h3>
            </div>
        </div>

        <?php if (empty($reviews)): ?>
            <div style="padding:48px 24px; text-align:center;">
                <i class="fas fa-star" style="font-size:2.5rem; color:rgba(255,255,255,0.1); margin-bottom:16px; display:block;"></i>
                <p style="color:rgba(255,255,255,0.45); font-size:0.9rem;">No product reviews submitted yet.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="admin-table rev-table" style="margin-top:0; border:none; border-radius:0;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Reviewer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $rev): ?>
                            <tr>
                                <td data-label="" class="rev-td-product">
                                    <span style="font-weight:600; color:#fff; font-size:0.85rem;">
                                        <?php echo htmlspecialchars($rev['p_name']); ?>
                                    </span>
                                </td>
                                <td data-label="Reviewer">
                                    <div style="font-weight:600; color:rgba(255,255,255,0.8); font-size:0.85rem;"><?php echo htmlspecialchars($rev['user_name']); ?></div>
                                    <div style="font-size:0.72rem; color:rgba(255,255,255,0.35); margin-top:2px;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></div>
                                </td>
                                <td data-label="Rating">
                                    <div style="display:flex; gap:2px;">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?php echo $i <= $rev['rating'] ? 'fas' : 'far'; ?> fa-star" style="font-size:0.75rem; color:<?php echo $i <= $rev['rating'] ? '#D4AF37' : 'rgba(255,255,255,0.15)'; ?>;"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td data-label="Review" class="rev-td-review" style="max-width:280px;">
                                    <div style="font-weight:600; color:rgba(255,255,255,0.8); font-size:0.82rem; margin-bottom:3px;"><?php echo htmlspecialchars($rev['title']); ?></div>
                                    <div style="font-size:0.78rem; color:rgba(255,255,255,0.4); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                        <?php echo htmlspecialchars($rev['review_text']); ?>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <div style="display:flex; flex-direction:column; gap:4px;">
                                        <span class="admin-badge <?php echo $rev['is_approved'] ? 'badge-completed' : 'badge-pending'; ?>">
                                            <?php echo $rev['is_approved'] ? 'Approved' : 'Pending'; ?>
                                        </span>
                                        <?php if ($rev['is_featured']): ?>
                                            <span style="font-size:0.6rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">
                                                <i class="fas fa-crown" style="margin-right:3px;"></i> Featured
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="" class="rev-td-actions">
                                    <div class="rev-action-btns" style="display:flex; gap:6px; align-items:center;">
                                        <?php if (!$rev['is_approved']): ?>
                                            <a href="reviews.php?approve_id=<?php echo $rev['id']; ?>" title="Approve" style="width:30px; height:30px; border-radius:6px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem;">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="reviews.php?feature_id=<?php echo $rev['id']; ?>" title="<?php echo $rev['is_featured'] ? 'Unfeature' : 'Feature'; ?>" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem;">
                                            <i class="fas fa-crown"></i>
                                        </a>
                                        <a href="reviews.php?delete_id=<?php echo $rev['id']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this review?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
