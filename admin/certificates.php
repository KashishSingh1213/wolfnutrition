<?php
// admin/certificates.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add Certificate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cert'])) {
    $title = trim($_POST['title']);
    $url = trim($_POST['image_url']);
    $order = (int)$_POST['display_order'];

    if (empty($title) || empty($url)) {
        $action_error = "Please fill in title and image path.";
    } else {
        $stmt_i = $pdo->prepare("
            INSERT INTO certificates (title, image_url, display_order, status) 
            VALUES (?, ?, ?, 1)
        ");
        $stmt_i->execute([$title, $url, $order]);
        $action_msg = "Certificate uploaded and saved successfully.";
    }
}

// Handle Edit Certificate Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_cert'])) {
    $cert_id = (int)$_POST['cert_id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['image_url']);
    $order = (int)$_POST['display_order'];

    if (empty($title) || empty($url)) {
        $action_error = "Please fill in title and image path.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE certificates SET title = ?, image_url = ?, display_order = ? WHERE id = ?");
        $stmt_u->execute([$title, $url, $order, $cert_id]);
        $action_msg = "Certificate updated successfully.";
    }
}

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

// Fetch all certificates
$stmt = $pdo->prepare("SELECT * FROM certificates ORDER BY display_order ASC");
$stmt->execute();
$certs = $stmt->fetchAll();

// Fetch edit certificate if editing
$edit_cert = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_cert = $stmt->fetch();
}
?>

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Quality Certificates</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Manage FSSAI, purity certificates and lab stamps</p>
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

        <!-- Certificates List -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Seeded Certificates</h3>
                    <p style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:4px;"><?php echo count($certs); ?> certificates in gallery</p>
                </div>
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-certificate" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
            </div>

            <?php if (empty($certs)): ?>
                <div style="padding:48px 24px; text-align:center;">
                    <i class="fas fa-certificate" style="font-size:2.5rem; color:rgba(255,255,255,0.1); margin-bottom:16px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.45); font-size:0.9rem;">No certificates loaded in the database.</p>
                    <p style="color:rgba(255,255,255,0.3); font-size:0.8rem; margin-top:6px;">Use the form to add your first certificate.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin-top:0; border:none; border-radius:0;">
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
                                            <a href="certificates.php?edit_id=<?php echo $c['id']; ?>" title="Edit" style="width:30px; height:30px; border-radius:6px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:#D4AF37; font-size:0.75rem;">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="certificates.php?toggle_id=<?php echo $c['id']; ?>" title="Toggle" style="width:30px; height:30px; border-radius:6px; background:rgba(74,222,128,0.1); display:flex; align-items:center; justify-content:center; color:#4ade80; font-size:0.75rem;">
                                                <i class="fas fa-<?php echo $c['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                            </a>
                                            <a href="certificates.php?delete_id=<?php echo $c['id']; ?>" title="Delete" onclick="return confirm('Delete this certificate record?')" style="width:30px; height:30px; border-radius:6px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444; font-size:0.75rem;">
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
                    <i class="fas fa-<?php echo $edit_cert ? 'edit' : 'plus'; ?>" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">
                    <?php echo $edit_cert ? 'Edit Certificate' : 'Add Quality Stamp'; ?>
                </h3>
            </div>

            <?php if ($edit_cert): ?>
                <form action="certificates.php" method="POST" style="padding:24px;">
                    <input type="hidden" name="cert_id" value="<?php echo $edit_cert['id']; ?>">
            <?php else: ?>
                <form action="certificates.php" method="POST" style="padding:24px;">
            <?php endif; ?>

                <div class="form-group">
                    <label for="c-title" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Certificate Title *</label>
                    <input type="text" name="title" id="c-title" class="form-control" required placeholder="e.g. ISO 9001:2015 stamp"
                        value="<?php echo htmlspecialchars($edit_cert ? $edit_cert['title'] : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="c-url" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Image File Path *</label>
                    <input type="text" name="image_url" id="c-url" class="form-control" required placeholder="e.g. assets/images/certs/fssai_cert.png" style="font-family:monospace; font-size:0.8rem;"
                        value="<?php echo htmlspecialchars($edit_cert ? $edit_cert['image_url'] : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="c-order" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Display Order</label>
                    <input type="number" name="display_order" id="c-order" class="form-control"
                        value="<?php echo $edit_cert ? $edit_cert['display_order'] : '0'; ?>">
                </div>

                <!-- Preview -->
                <?php if ($edit_cert): ?>
                    <div style="margin-bottom:20px;">
                        <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Current Image</label>
                        <div style="width:100%; height:120px; border-radius:8px; overflow:hidden; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:center;">
                            <img src="../<?php echo htmlspecialchars($edit_cert['image_url']); ?>" alt="Preview" style="max-width:100%; max-height:100%; object-fit:contain;">
                        </div>
                    </div>
                <?php endif; ?>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" name="<?php echo $edit_cert ? 'edit_cert' : 'add_cert'; ?>" class="btn-gold" style="flex:1; padding:12px 20px;">
                        <i class="fas fa-<?php echo $edit_cert ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_cert ? 'Update Certificate' : 'Save Certificate'; ?>
                    </button>
                </div>

                <?php if ($edit_cert): ?>
                    <a href="certificates.php" style="display:flex; align-items:center; justify-content:center; gap:6px; margin-top:12px; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5); font-size:0.8rem; font-weight:500; transition:all 0.2s; text-decoration:none;">
                        <i class="fas fa-times"></i> Cancel Edit
                    </a>
                <?php endif; ?>
            </form>

            <!-- Tip -->
            <div style="margin:0 24px 24px; padding:16px; border-radius:8px; background:rgba(212,175,55,0.05); border:1px solid rgba(212,175,55,0.1);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.8rem;"></i>
                    <span style="font-size:0.75rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">Upload Tip</span>
                </div>
                <p style="font-size:0.78rem; color:rgba(255,255,255,0.45); line-height:1.6;">
                    Save square icons (200x200px to 400x400px) inside <code style="background:rgba(255,255,255,0.05); padding:1px 5px; border-radius:3px; font-size:0.72rem;">assets/images/certs/</code> for the best visual presentation.
                </p>
            </div>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
