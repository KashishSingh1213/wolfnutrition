<?php
// admin/certificate_edit.php — Edit Certificate
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: certificates.php");
    exit();
}

$stmt_c = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
$stmt_c->execute([$edit_id]);
$cert = $stmt_c->fetch();
if (!$cert) {
    header("Location: certificates.php");
    exit();
}

// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cert'])) {
    $title = trim($_POST['title']);
    $url = $cert['image_url']; // default to existing
    $order = (int)$_POST['display_order'];

    // Handle new file upload
    if (isset($_FILES['cert_image']) && $_FILES['cert_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cert_image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/certificates/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'cert_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                // Delete old image
                $old_path = __DIR__ . '/../' . $cert['image_url'];
                if (file_exists($old_path)) unlink($old_path);
                $url = 'uploads/certificates/' . $filename;
            }
        }
    }

    if (empty($title) || empty($url)) {
        $action_error = "Please fill in title and upload an image.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE certificates SET title = ?, image_url = ?, display_order = ? WHERE id = ?");
        $stmt_u->execute([$title, $url, $order, $edit_id]);
        $action_msg = "Certificate updated successfully.";
        // Refresh data
        $stmt_c->execute([$edit_id]);
        $cert = $stmt_c->fetch();
    }
}
?>

    <div style="margin-bottom:20px;">
        <a href="certificates.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Certificates
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Edit Certificate</h2>
        <span class="admin-badge <?php echo $cert['status'] ? 'badge-completed' : 'badge-pending'; ?>" style="font-size:0.75rem;">
            <?php echo $cert['status'] ? 'Active' : 'Inactive'; ?>
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

    <div style="display:grid; grid-template-columns:1fr 300px; gap:28px; align-items:start;">

        <!-- Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-pen" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Update Certificate</h3>
            </div>

            <form action="certificate_edit.php?id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data" style="padding:28px;">
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Certificate Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($cert['title']); ?>">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Upload New Image</label>
                    <div style="border:1px dashed rgba(212,175,55,0.25); border-radius:8px; padding:24px; text-align:center; background:rgba(212,175,55,0.02); cursor:pointer; position:relative;" onclick="document.getElementById('cert-image-input').click();">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem; color:rgba(212,175,55,0.3); margin-bottom:10px; display:block;"></i>
                        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0 0 4px 0;">Click to upload new image (optional)</p>
                        <p style="font-size:0.72rem; color:rgba(255,255,255,0.3); margin:0;">Leave empty to keep current image</p>
                        <input type="file" name="cert_image" id="cert-image-input" accept="image/*" style="display:none;" onchange="previewImage(this);">
                    </div>
                    <div id="image-preview" style="margin-top:12px; display:none;">
                        <img id="preview-img" src="" alt="Preview" style="width:100%; max-height:180px; object-fit:contain; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Display Order</label>
                    <input type="number" name="display_order" class="form-control" min="0" value="<?php echo $cert['display_order']; ?>">
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" name="update_cert" class="btn-gold" style="flex:1; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fas fa-save"></i> Update Certificate
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
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;">#<?php echo $cert['id']; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Status</span>
                        <span class="admin-badge <?php echo $cert['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                            <?php echo $cert['status'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Order</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;"><?php echo $cert['display_order']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Current Image Preview -->
            <div class="glass-card" style="margin-top:16px; padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Current Image</h4>
                </div>
                <div style="padding:16px 20px; display:flex; align-items:center; justify-content:center;">
                    <div style="width:100%; height:140px; border-radius:8px; overflow:hidden; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:center;">
                        <img src="../<?php echo htmlspecialchars($cert['image_url']); ?>" alt="Preview" style="max-width:100%; max-height:100%; object-fit:contain;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
