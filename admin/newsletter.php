<?php
// admin/newsletter.php — Newsletter Subscribers List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $sub_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$sub_id]);
    $action_msg = "Subscriber status toggled.";
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $sub_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
    $stmt->execute([$sub_id]);
    $action_msg = "Subscriber removed.";
}

// Handle Bulk Delete Inactive
if (isset($_GET['purge_inactive'])) {
    $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE is_active = 0");
    $stmt->execute();
    $action_msg = "Inactive subscribers purged.";
}

// Fetch all subscribers
$stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
$stmt->execute();
$subscribers = $stmt->fetchAll();

$total_count = count($subscribers);
$active_count = 0;
$inactive_count = 0;
foreach ($subscribers as $s) {
    if ($s['is_active']) $active_count++;
    else $inactive_count++;
}
?>

    <style>
        @media (max-width: 1024px) {
            .nl-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 12px; }
        }
        @media (max-width: 768px) {
            .nl-page-header { flex-direction: column !important; align-items: flex-start !important; gap: 12px; }
            .nl-stats-grid { grid-template-columns: 1fr !important; gap: 10px !important; }
            .nl-table thead { display: none !important; }
            .nl-table, .nl-table tbody, .nl-table tr, .nl-table td { display: block !important; width: 100% !important; }
            .nl-table tbody tr { background: rgba(18,18,18,0.4); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; padding: 14px 16px; margin: 0 16px 10px 16px; }
            .nl-table tbody tr:first-child { margin-top: 10px; }
            .nl-table tbody td { padding: 3px 0 !important; border-bottom: none !important; font-size: 0.85rem; }
            .nl-table tbody td::before { content: attr(data-label); display: block; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: rgba(255,255,255,0.3); margin-bottom: 1px; }
            .nl-table tbody td.nl-td-email::before { display: none; }
            .nl-table tbody td.nl-td-email { font-size: 0.95rem; padding-bottom: 6px !important; border-bottom: 1px solid rgba(255,255,255,0.04) !important; }
            .nl-table tbody td.nl-td-actions::before { display: none; }
            .nl-table tbody td.nl-td-actions { padding-top: 8px !important; border-top: 1px solid rgba(255,255,255,0.04); }
            .nl-table tbody td.nl-td-actions .nl-action-btns { width: 100% !important; }
            .nl-table tbody td.nl-td-actions .nl-action-btns a { flex: 1 !important; justify-content: center !important; }
        }
    </style>

    <!-- Page Header -->
    <div class="nl-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Newsletter Subscribers</h2>
            <p style="font-size:0.85rem; color:var(--text-muted);">Manage email subscriptions from the Wolf Pack</p>
        </div>
        <?php if ($inactive_count > 0): ?>
            <a href="newsletter.php?purge_inactive=1" class="btn-outline-gold" style="padding:8px 16px; font-size:0.8rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border-radius:8px;" onclick="return confirm('Remove all inactive subscribers?')">
                <i class="fas fa-trash"></i> Purge Inactive (<?php echo $inactive_count; ?>)
            </a>
        <?php endif; ?>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="nl-stats-grid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:28px;">
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-envelope" style="color:#D4AF37; font-size:1rem;"></i>
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

    <?php if (empty($subscribers)): ?>
        <div class="glass-card" style="padding:60px 40px; text-align:center; border:2px dashed rgba(212,175,55,0.15);">
            <div style="width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                <i class="fas fa-envelope-open" style="font-size:1.8rem; color:rgba(212,175,55,0.3);"></i>
            </div>
            <h3 style="font-size:1.2rem; color:#fff; margin-bottom:8px;">No Subscribers Yet</h3>
            <p style="color:var(--text-muted); font-size:0.85rem;">Subscribers will appear here when they sign up from the homepage.</p>
        </div>
    <?php else: ?>
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06);">
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">All Subscribers</h3>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table nl-table" style="margin-top:0; border:none;">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>IP Address</th>
                            <th>Subscribed On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td data-label="" class="nl-td-email" style="font-weight:600; color:#fff; font-size:0.88rem;">
                                    <i class="fas fa-envelope" style="color:rgba(255,255,255,0.3); margin-right:8px;"></i><?php echo htmlspecialchars($sub['email']); ?>
                                </td>
                                <td data-label="IP">
                                    <span style="font-size:0.78rem; color:rgba(255,255,255,0.4); font-family:monospace;"><?php echo htmlspecialchars($sub['ip_address'] ?? '—'); ?></span>
                                </td>
                                <td data-label="Date">
                                    <span style="font-size:0.82rem; color:rgba(255,255,255,0.5);"><?php echo date('M d, Y \a\t h:i A', strtotime($sub['created_at'])); ?></span>
                                </td>
                                <td data-label="Status">
                                    <span class="admin-badge <?php echo $sub['is_active'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $sub['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td data-label="" class="nl-td-actions">
                                    <div class="nl-action-btns" style="display:flex; gap:6px;">
                                        <a href="newsletter.php?toggle_id=<?php echo $sub['id']; ?>" title="<?php echo $sub['is_active'] ? 'Unsubscribe' : 'Reactivate'; ?>" style="width:32px; height:32px; border-radius:8px; background:<?php echo $sub['is_active'] ? 'rgba(74,222,128,0.08)' : 'rgba(255,255,255,0.04)'; ?>; border:1px solid <?php echo $sub['is_active'] ? 'rgba(74,222,128,0.15)' : 'rgba(255,255,255,0.08)'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $sub['is_active'] ? '#4ade80' : 'rgba(255,255,255,0.35)'; ?>; font-size:0.75rem; text-decoration:none;">
                                            <i class="fas fa-<?php echo $sub['is_active'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                        </a>
                                        <a href="newsletter.php?delete_id=<?php echo $sub['id']; ?>" title="Delete" onclick="return confirm('Remove this subscriber?')" style="width:32px; height:32px; border-radius:8px; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.12); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem; text-decoration:none;">
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

<?php
require_once __DIR__ . '/includes/footer.php';
?>
