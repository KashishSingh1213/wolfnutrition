<?php
// admin/announcements.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ann'])) {
    $msg = trim($_POST['message']);
    $link = trim($_POST['link']);
    $order = (int)$_POST['display_order'];

    if (empty($msg)) {
        $action_error = "Announcement message cannot be blank.";
    } else {
        $stmt_i = $pdo->prepare("
            INSERT INTO announcements (message, link, display_order, status)
            VALUES (?, ?, ?, 1)
        ");
        $stmt_i->execute([$msg, $link, $order]);
        $action_msg = "Announcement added successfully.";
    }
}

// Handle Edit Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ann'])) {
    $eid = (int)$_POST['edit_id'];
    $msg = trim($_POST['message']);
    $link = trim($_POST['link']);
    $order = (int)$_POST['display_order'];

    if (empty($msg)) {
        $action_error = "Announcement message cannot be blank.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE announcements SET message = ?, link = ?, display_order = ? WHERE id = ?");
        $stmt_u->execute([$msg, $link, $order, $eid]);
        $action_msg = "Announcement updated successfully.";
    }
}

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $ann_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE announcements SET status = NOT status WHERE id = ?");
    $stmt->execute([$ann_id]);
    $action_msg = "Announcement status toggled.";
}

// Handle Delete Announcement
if (isset($_GET['delete_id'])) {
    $ann_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$ann_id]);
    $action_msg = "Announcement deleted.";
}

// Fetch edit data
$edit_ann = null;
if (isset($_GET['edit_id'])) {
    $e_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$e_id]);
    $edit_ann = $stmt->fetch();
}

// Fetch all announcements
$stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY display_order ASC");
$stmt->execute();
$announcements = $stmt->fetchAll();
?>

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Announcement Bar</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Manage header scrolling offer messages</p>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($action_error) && $action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:1rem;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 400px; gap:28px; align-items:start;">

        <!-- Announcements List -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Active Announcements</h3>
                    <p style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:4px;"><?php echo count($announcements); ?> announcements configured</p>
                </div>
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-bullhorn" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
            </div>

            <?php if (empty($announcements)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-bullhorn" style="font-size:2.5rem; color:rgba(255,255,255,0.1); margin-bottom:16px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.45); font-size:0.9rem;">No announcements created yet.</p>
                    <p style="color:rgba(255,255,255,0.3); font-size:0.8rem; margin-top:6px;">Use the form to create a scrolling bar message.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin-top:0; border:none; border-radius:0;">
                        <thead>
                            <tr>
                                <th style="width:60px;">Order</th>
                                <th>Message</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($announcements as $ann): ?>
                                <tr>
                                    <td>
                                        <span style="width:28px; height:28px; border-radius:6px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:0.8rem;">
                                            <?php echo $ann['display_order']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:0.875rem; color:rgba(255,255,255,0.8); line-height:1.4; display:block; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                            <?php echo htmlspecialchars($ann['message']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($ann['link'])): ?>
                                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.35); font-family:monospace; background:rgba(255,255,255,0.03); padding:3px 8px; border-radius:4px;">
                                                <?php echo htmlspecialchars($ann['link']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.2);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="admin-badge <?php echo $ann['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                            <?php echo $ann['status'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <a href="announcements.php?edit_id=<?php echo $ann['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem;">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="announcements.php?toggle_id=<?php echo $ann['id']; ?>" title="<?php echo $ann['status'] ? 'Disable' : 'Enable'; ?>" style="width:30px; height:30px; border-radius:6px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem;">
                                                <i class="fas fa-<?php echo $ann['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                            </a>
                                            <a href="announcements.php?delete_id=<?php echo $ann['id']; ?>" title="Delete" onclick="return confirm('Delete this announcement?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem;">
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

        <!-- Add/Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-<?php echo $edit_ann ? 'edit' : 'plus'; ?>" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">
                    <?php echo $edit_ann ? 'Edit Announcement' : 'Add Announcement'; ?>
                </h3>
            </div>

            <form action="announcements.php" method="POST" style="padding:24px;">
                <?php if ($edit_ann): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_ann['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="message" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Message *</label>
                    <input type="text" name="message" id="message" class="form-control" placeholder="e.g. Free shipping on prepaid orders" required
                        value="<?php echo htmlspecialchars($edit_ann ? $edit_ann['message'] : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="link" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Voucher Link</label>
                    <input type="text" name="link" id="link" class="form-control" placeholder="e.g. /certificates.php"
                        value="<?php echo htmlspecialchars($edit_ann ? ($edit_ann['link'] ?? '') : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="order" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Display Order</label>
                    <input type="number" name="display_order" id="order" class="form-control"
                        value="<?php echo $edit_ann ? $edit_ann['display_order'] : '0'; ?>">
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" name="<?php echo $edit_ann ? 'edit_ann' : 'add_ann'; ?>" class="btn-gold" style="flex:1; padding:12px 20px;">
                        <i class="fas fa-<?php echo $edit_ann ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_ann ? 'Update Announcement' : 'Save Announcement'; ?>
                    </button>
                </div>

                <?php if ($edit_ann): ?>
                    <a href="announcements.php" style="display:flex; align-items:center; justify-content:center; gap:6px; margin-top:12px; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); font-size:0.8rem; font-weight:500; transition:all 0.2s; text-decoration:none;">
                        <i class="fas fa-times"></i> Cancel Edit
                    </a>
                <?php endif; ?>
            </form>

            <!-- Tip -->
            <div style="margin:0 24px 24px; padding:16px; border-radius:8px; background:rgba(212,175,55,0.05); border:1px solid rgba(212,175,55,0.1);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.8rem;"></i>
                    <span style="font-size:0.75rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">Copywriting Tip</span>
                </div>
                <p style="font-size:0.78rem; color:rgba(255,255,255,0.45); line-height:1.6;">
                    Use the announcement bar to drive quick sales or create urgency. Highlight prepaid perks, quality links, or redirect customers to key trust sections.
                </p>
            </div>
        </div>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
