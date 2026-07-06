<?php
// admin/announcements.php — Announcement List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $ann_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE announcements SET status = NOT status WHERE id = ?");
    $stmt->execute([$ann_id]);
    $action_msg = "Announcement status toggled.";
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $ann_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$ann_id]);
    $action_msg = "Announcement deleted.";
}

// Fetch all announcements
$stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY display_order ASC");
$stmt->execute();
$announcements = $stmt->fetchAll();

$total_count = count($announcements);
$active_count = 0;
foreach ($announcements as $a) { if ($a['status']) $active_count++; }
$inactive_count = $total_count - $active_count;
?>

    <style>
        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .ann-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .ann-stats-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
            .ann-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .ann-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .ann-stats-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            .ann-grid {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }
            .ann-card-inner {
                flex-direction: column !important;
            }
            .ann-card-order {
                width: 100% !important;
                min-height: auto !important;
                flex-direction: row !important;
                padding: 10px 16px !important;
                gap: 8px !important;
                border-right: none !important;
                border-bottom: 1px solid rgba(255,255,255,0.05) !important;
            }
            .ann-card-order span:first-child {
                font-size: 1rem !important;
            }
            .ann-card-content {
                padding: 14px 16px !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
            }
            .ann-card-msg {
                white-space: normal !important;
            }
            .ann-card-actions {
                width: 100% !important;
                justify-content: flex-end !important;
            }
            .ann-card-actions a {
                width: 38px !important;
                height: 38px !important;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="ann-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Announcements</h2>
            <p style="font-size:0.85rem; color:var(--text-muted);">Manage scrolling header bar messages</p>
        </div>
        <a href="announcement_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> Add Announcement
        </a>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="ann-stats-grid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:28px;">
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-bullhorn" style="color:#D4AF37; font-size:1rem;"></i>
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
    </div>

    <?php if (empty($announcements)): ?>
        <div class="glass-card" style="padding:60px 40px; text-align:center; border:2px dashed rgba(212,175,55,0.15);">
            <div style="width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                <i class="fas fa-bullhorn" style="font-size:1.8rem; color:rgba(212,175,55,0.3);"></i>
            </div>
            <h3 style="font-size:1.2rem; color:#fff; margin-bottom:8px;">No Announcements</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:20px;">Create your first scrolling bar message.</p>
            <a href="announcement_add.php" class="btn-gold" style="padding:12px 28px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.88rem;">
                <i class="fas fa-plus"></i> Create Announcement
            </a>
        </div>
    <?php else: ?>
        <div class="ann-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(480px, 1fr)); gap:16px;">
            <?php foreach ($announcements as $ann): ?>
                <div class="glass-card" style="padding:0; overflow:hidden; <?php echo !$ann['status'] ? 'opacity:0.5;' : ''; ?>">
                    <div class="ann-card-inner" style="display:flex; align-items:stretch;">
                        <!-- Order Badge -->
                        <div class="ann-card-order" style="width:56px; min-height:100%; background:rgba(212,175,55,0.06); border-right:1px solid rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:2px;">
                            <span style="font-size:1.3rem; font-weight:800; color:var(--gold-primary); line-height:1;"><?php echo $ann['display_order']; ?></span>
                            <span style="font-size:0.55rem; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:0.5px;">Order</span>
                        </div>

                        <!-- Content -->
                        <div class="ann-card-content" style="flex:1; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
                            <div style="flex:1; min-width:0;">
                                <p class="ann-card-msg" style="font-size:0.92rem; color:#fff; font-weight:600; margin:0 0 6px 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?php echo htmlspecialchars($ann['message']); ?>
                                </p>
                                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                    <span class="admin-badge <?php echo $ann['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $ann['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <?php if (!empty($ann['link'])): ?>
                                        <span style="font-size:0.72rem; color:rgba(255,255,255,0.3); font-family:monospace;">
                                            <i class="fas fa-link" style="margin-right:4px;"></i><?php echo htmlspecialchars($ann['link']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="ann-card-actions" style="display:flex; gap:6px; flex-shrink:0;">
                                <a href="announcement_edit.php?id=<?php echo $ann['id']; ?>" title="Edit" style="width:34px; height:34px; border-radius:8px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.8rem; text-decoration:none;">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="announcements.php?toggle_id=<?php echo $ann['id']; ?>" title="<?php echo $ann['status'] ? 'Deactivate' : 'Activate'; ?>" style="width:34px; height:34px; border-radius:8px; background:<?php echo $ann['status'] ? 'rgba(74,222,128,0.08)' : 'rgba(255,255,255,0.04)'; ?>; border:1px solid <?php echo $ann['status'] ? 'rgba(74,222,128,0.15)' : 'rgba(255,255,255,0.08)'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $ann['status'] ? '#4ade80' : 'rgba(255,255,255,0.35)'; ?>; font-size:0.8rem; text-decoration:none;">
                                    <i class="fas fa-<?php echo $ann['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                </a>
                                <a href="announcements.php?delete_id=<?php echo $ann['id']; ?>" title="Delete" onclick="return confirm('Delete this announcement?')" style="width:34px; height:34px; border-radius:8px; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.12); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.8rem; text-decoration:none;">
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
