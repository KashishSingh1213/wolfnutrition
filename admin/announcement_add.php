<?php
// admin/announcement_add.php — Add Announcement
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ann'])) {
    $msg = trim($_POST['message']);
    $link = trim($_POST['link']);
    $order = (int)$_POST['display_order'];

    if (empty($msg)) {
        $action_error = "Announcement message cannot be blank.";
    } else {
        $stmt_i = $pdo->prepare("INSERT INTO announcements (message, link, display_order, status) VALUES (?, ?, ?, 1)");
        $stmt_i->execute([$msg, $link, $order]);
        header("Location: announcements.php?msg=created");
        exit();
    }
}

// Total count
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM announcements");
$stmt_total->execute();
$total_ann = (int)$stmt_total->fetchColumn();
?>

    <style>
        @media (max-width: 768px) {
            .ann-add-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 8px;
            }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="announcements.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Announcements
        </a>
    </div>

    <div class="ann-add-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">New Announcement</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total announcements: <strong style="color:var(--gold-primary);"><?php echo $total_ann; ?></strong></div>
    </div>

    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="max-width:600px; padding:0; overflow:hidden;">
        <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
            <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-plus" style="color:#D4AF37; font-size:0.85rem;"></i>
            </div>
            <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Create Announcement</h3>
        </div>

        <form action="announcement_add.php" method="POST" style="padding:28px;">
            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Message *</label>
                <input type="text" name="message" class="form-control" required placeholder="e.g. Free shipping on prepaid orders">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Link (optional)</label>
                <input type="text" name="link" class="form-control" placeholder="e.g. /certificates.php">
            </div>

            <div class="form-group" style="margin-bottom:24px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Display Order</label>
                <input type="number" name="display_order" class="form-control" min="0" value="0">
            </div>

            <button type="submit" name="create_ann" class="btn-gold" style="width:100%; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fas fa-plus"></i> Save Announcement
            </button>
        </form>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
