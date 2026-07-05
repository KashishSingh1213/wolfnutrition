<?php
// admin/certificate_add.php — Add Certificate
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_cert'])) {
    $title = trim($_POST['title']);
    $url = '';
    $order = (int)$_POST['display_order'];

    // Handle file upload
    if (isset($_FILES['cert_image']) && $_FILES['cert_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cert_image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/certificates/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'cert_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $url = 'uploads/certificates/' . $filename;
            }
        }
    }

    if (empty($title) || empty($url)) {
        $action_error = "Please fill in title and upload an image.";
    } else {
        $stmt_i = $pdo->prepare("INSERT INTO certificates (title, image_url, display_order, status) VALUES (?, ?, ?, 1)");
        $stmt_i->execute([$title, $url, $order]);
        header("Location: certificates.php?msg=created");
        exit();
    }
}

// Total count
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM certificates");
$stmt_total->execute();
$total_certs = (int)$stmt_total->fetchColumn();
?>

    <div style="margin-bottom:20px;">
        <a href="certificates.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Certificates
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">New Certificate</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total certificates: <strong style="color:var(--gold-primary);"><?php echo $total_certs; ?></strong></div>
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
            <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Create Certificate</h3>
        </div>

        <form action="certificate_add.php" method="POST" enctype="multipart/form-data" style="padding:28px;">
            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Certificate Title *</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. ISO 9001:2015 stamp">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Upload Image *</label>
                <div style="border:1px dashed rgba(212,175,55,0.25); border-radius:8px; padding:24px; text-align:center; background:rgba(212,175,55,0.02); cursor:pointer; position:relative;" id="upload-zone" onclick="document.getElementById('cert-image-input').click();">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem; color:rgba(212,175,55,0.3); margin-bottom:10px; display:block;"></i>
                    <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0 0 4px 0;">Click to upload or drag & drop</p>
                    <p style="font-size:0.72rem; color:rgba(255,255,255,0.3); margin:0;">JPG, PNG, GIF, WEBP (Max 5MB)</p>
                    <input type="file" name="cert_image" id="cert-image-input" accept="image/*" style="display:none;" required onchange="previewImage(this);">
                </div>
                <div id="image-preview" style="margin-top:12px; display:none;">
                    <img id="preview-img" src="" alt="Preview" style="width:100%; max-height:180px; object-fit:contain; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:24px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Display Order</label>
                <input type="number" name="display_order" class="form-control" min="0" value="0">
            </div>

            <button type="submit" name="create_cert" class="btn-gold" style="width:100%; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fas fa-plus"></i> Save Certificate
            </button>
        </form>
    </div>

    <!-- Tip -->
    <div style="max-width:600px; margin-top:16px; padding:16px; border-radius:10px; background:rgba(212,175,55,0.04); border:1px solid rgba(212,175,55,0.08);">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.8rem;"></i>
            <span style="font-size:0.72rem; font-weight:700; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5px;">Upload Tip</span>
        </div>
        <p style="font-size:0.78rem; color:rgba(255,255,255,0.4); line-height:1.6; margin:0;">
            Save square icons (200x200px to 400x400px) for the best visual presentation in the gallery.
        </p>
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
