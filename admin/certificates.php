<?php
// admin/certificates.php — Certificate List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $c_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE certificates SET status = NOT status WHERE id = ?");
    $stmt->execute([$c_id]);
    $action_msg = "Certificate active status toggled.";
}

// Handle Delete Certificate
if (isset($_GET['delete_id'])) {
    $c_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$c_id]);
    $action_msg = "Certificate deleted.";
}

// Success msg from add/edit redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'created') {
    $action_msg = "Certificate uploaded and saved successfully.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $action_msg = "Certificate updated successfully.";
}

// Fetch all certificates
$stmt = $pdo->prepare("SELECT * FROM certificates ORDER BY display_order ASC");
$stmt->execute();
$certs = $stmt->fetchAll();

$total_count = count($certs);
$active_count = 0;
foreach ($certs as $c) { if ($c['status']) $active_count++; }
$inactive_count = $total_count - $active_count;
?>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Quality Certificates</h2>
            <p style="font-size:0.85rem; color:var(--text-muted);">Manage FSSAI, purity certificates and lab stamps</p>
        </div>
        <a href="certificate_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> Add Certificate
        </a>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:28px;">
        <div class="glass-card" style="padding:18px 22px; display:flex; align-items:center; gap:14px;">
            <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-certificate" style="color:#D4AF37; font-size:1rem;"></i>
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

    <?php if (empty($certs)): ?>
        <div class="glass-card" style="padding:60px 40px; text-align:center; border:2px dashed rgba(212,175,55,0.15);">
            <div style="width:70px; height:70px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                <i class="fas fa-certificate" style="font-size:1.8rem; color:rgba(212,175,55,0.3);"></i>
            </div>
            <h3 style="font-size:1.2rem; color:#fff; margin-bottom:8px;">No Certificates</h3>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:20px;">Add your first quality certificate.</p>
            <a href="certificate_add.php" class="btn-gold" style="padding:12px 28px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.88rem;">
                <i class="fas fa-plus"></i> Add Certificate
            </a>
        </div>
    <?php else: ?>
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="admin-table" style="margin-top:0;">
                    <thead>
                        <tr>
                            <th style="width:80px;">Preview</th>
                            <th>Certificate Details</th>
                            <th style="width:70px;">Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certs as $c): ?>
                            <tr>
                                <td>
                                    <div style="width:56px; height:56px; border-radius:8px; overflow:hidden; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:center;">
                                        <img src="../<?php echo htmlspecialchars($c['image_url']); ?>" alt="Certificate" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight:600; color:#fff; font-size:0.875rem;"><?php echo htmlspecialchars($c['title']); ?></span>
                                </td>
                                <td>
                                    <span style="width:28px; height:28px; border-radius:6px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:0.8rem;">
                                        <?php echo $c['display_order']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-badge <?php echo $c['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $c['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px; align-items:center;">
                                        <a href="certificate_edit.php?id=<?php echo $c['id']; ?>" title="Edit" style="width:34px; height:34px; border-radius:8px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.8rem; text-decoration:none;">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="certificates.php?toggle_id=<?php echo $c['id']; ?>" title="Toggle" style="width:34px; height:34px; border-radius:8px; background:<?php echo $c['status'] ? 'rgba(74,222,128,0.08)' : 'rgba(255,255,255,0.04)'; ?>; border:1px solid <?php echo $c['status'] ? 'rgba(74,222,128,0.15)' : 'rgba(255,255,255,0.08)'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $c['status'] ? '#4ade80' : 'rgba(255,255,255,0.35)'; ?>; font-size:0.8rem; text-decoration:none;">
                                            <i class="fas fa-<?php echo $c['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                        </a>
                                        <a href="certificates.php?delete_id=<?php echo $c['id']; ?>" title="Delete" onclick="return confirm('Delete this certificate record?')" style="width:34px; height:34px; border-radius:8px; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.12); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.8rem; text-decoration:none;">
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
