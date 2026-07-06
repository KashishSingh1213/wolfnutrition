<?php
// admin/announcement_edit.php — Edit Announcement
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: announcements.php");
    exit();
}

$stmt_a = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt_a->execute([$edit_id]);
$ann = $stmt_a->fetch();
if (!$ann) {
    header("Location: announcements.php");
    exit();
}

// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ann'])) {
    $msg = trim($_POST['message']);
    $link = trim($_POST['link']);
    $order = (int)$_POST['display_order'];
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($msg)) {
        $action_error = "Announcement message cannot be blank.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE announcements SET message = ?, link = ?, display_order = ?, status = ? WHERE id = ?");
        $stmt_u->execute([$msg, $link, $order, $status, $edit_id]);
        $action_msg = "Announcement updated successfully.";
        // Refresh data
        $stmt_a->execute([$edit_id]);
        $ann = $stmt_a->fetch();
    }
}
?>

    <style>
        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .ann-edit-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .ann-edit-grid {
                grid-template-columns: 1fr !important;
            }
            .ann-edit-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="announcements.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Announcements
        </a>
    </div>

    <div class="ann-edit-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Edit Announcement</h2>
        <span class="admin-badge <?php echo $ann['status'] ? 'badge-completed' : 'badge-pending'; ?>" style="font-size:0.75rem;">
            <?php echo $ann['status'] ? 'Active' : 'Inactive'; ?>
        </span>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div class="ann-edit-grid" style="display:grid; grid-template-columns:1fr 300px; gap:28px; align-items:start;">

        <!-- Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-pen" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Update Announcement</h3>
            </div>

            <form action="announcement_edit.php?id=<?php echo $edit_id; ?>" method="POST" style="padding:28px;">
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Message *</label>
                    <input type="text" name="message" class="form-control" required value="<?php echo htmlspecialchars($ann['message']); ?>">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Link (optional)</label>
                    <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($ann['link'] ?? ''); ?>">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Display Order</label>
                    <input type="number" name="display_order" class="form-control" min="0" value="<?php echo $ann['display_order']; ?>">
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" name="update_ann" class="btn-gold" style="flex:1; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fas fa-save"></i> Update Announcement
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div style="position:sticky; top:96px;">
            <div class="glass-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Details</h4>
                </div>
                <div style="padding:18px 20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">ID</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;">#<?php echo $ann['id']; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Status</span>
                        <span class="admin-badge <?php echo $ann['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                            <?php echo $ann['status'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Order</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;"><?php echo $ann['display_order']; ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-top:16px; padding:16px; border-radius:10px; background:rgba(212,175,55,0.04); border:1px solid rgba(212,175,55,0.08);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.8rem;"></i>
                    <span style="font-size:0.72rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">Tip</span>
                </div>
                <p style="font-size:0.78rem; color:rgba(255,255,255,0.4); line-height:1.6; margin:0;">
                    Toggle status to show or hide this announcement on the website without deleting it.
                </p>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
