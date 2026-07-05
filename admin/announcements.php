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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Announcement Bar</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage header scrolling offer messages</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:30px; align-items:start;">
        <!-- Announcements list -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Active Announcements
            </h3>

            <?php if (empty($announcements)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No announcements created yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Message</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $ann): ?>
                            <tr>
                                <td><strong style="color:#fff;"><?php echo $ann['display_order']; ?></strong></td>
                                <td><span style="font-size:0.9rem; color:rgba(255,255,255,0.7);"><?php echo htmlspecialchars($ann['message']); ?></span></td>
                                <td style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($ann['link'] ? $ann['link'] : '#'); ?></td>
                                <td>
                                    <span class="admin-badge <?php echo $ann['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $ann['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:10px;">
                                        <a href="announcements.php?edit_id=<?php echo $ann['id']; ?>" style="color:var(--gold-primary); font-weight:700;">Edit</a>
                                        <a href="announcements.php?toggle_id=<?php echo $ann['id']; ?>" style="color:var(--success-color); font-weight:700;"><?php echo $ann['status'] ? 'Disable' : 'Enable'; ?></a>
                                        <a href="announcements.php?delete_id=<?php echo $ann['id']; ?>" style="color:var(--danger-color); font-weight:700;" onclick="return confirm('Delete this announcement?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add/Edit Announcement Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                <?php echo $edit_ann ? 'Edit Announcement' : 'Add Announcement'; ?>
            </h3>

            <form action="announcements.php" method="POST">
                <?php if ($edit_ann): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_ann['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <input type="text" name="message" id="message" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. Free shipping on prepaid orders" required
                        value="<?php echo htmlspecialchars($edit_ann ? $edit_ann['message'] : ''); ?>">
                </div>
                <div class="form-group">
                    <label for="link">Voucher Link</label>
                    <input type="text" name="link" id="link" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. /certificates.php"
                        value="<?php echo htmlspecialchars($edit_ann ? ($edit_ann['link'] ?? '') : ''); ?>">
                </div>
                <div class="form-group">
                    <label for="order">Display Order</label>
                    <input type="number" name="display_order" id="order" class="form-control" style="font-size:0.85rem; padding:8px;"
                        value="<?php echo $edit_ann ? $edit_ann['display_order'] : '0'; ?>">
                </div>

                <button type="submit" name="<?php echo $edit_ann ? 'edit_ann' : 'add_ann'; ?>" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                    <?php echo $edit_ann ? 'Update Announcement' : 'Save Announcement'; ?>
                </button>

                <?php if ($edit_ann): ?>
                    <a href="announcements.php" style="display:block; text-align:center; margin-top:10px; color:var(--text-muted); font-size:0.85rem;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Admin Guide Panel -->
    <div class="glass-card" style="padding:25px; border-radius:8px; margin-top:35px; border-left:4px solid var(--gold-primary); box-shadow: 0 10px 30px rgba(8,12,16,0.3);">
        <h3 style="font-size:1.1rem; color:var(--gold-primary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">
            <i class="fas fa-lightbulb" style="margin-right:8px;"></i> Announcement Copywriting Best Practices
        </h3>
        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:12px;">
            The announcements bar at the top of the storefront is the first thing customers see when landing on the website. Use this space to drive quick sales, create urgency, or direct traffic to key trust sections.
        </p>
        <ul style="font-size:0.8rem; color:var(--text-muted); padding-left:20px; line-height:1.7;">
            <li>**Prepaid Incentives**: Highlight prepaid perks (e.g., "Get FREE Shipping + 5% Off on all prepaid orders!").</li>
            <li>**Trust & Authority**: Reassure customers by adding quality links (e.g., "100% Ayurvedic Sourced | FSSAI Certified").</li>
            <li>**Interactive Redirects**: Use links like `/certificates.php` or `/products.php` so customers can click the announcements bar to navigate directly.</li>
        </ul>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
