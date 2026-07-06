<?php
// admin/testimonial_edit.php — Edit Testimonial
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: testimonials.php");
    exit();
}

$stmt_t = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
$stmt_t->execute([$edit_id]);
$testimonial = $stmt_t->fetch();
if (!$testimonial) {
    header("Location: testimonials.php");
    exit();
}

// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_testimonial'])) {
    $name = trim($_POST['customer_name']);
    $title = trim($_POST['customer_title']);
    $text = trim($_POST['testimonial_text']);
    $rating = (int)$_POST['rating'];
    $order = (int)$_POST['display_order'];
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $avatar_url = $testimonial['avatar_url'];

    // Handle new avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/testimonials/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                // Delete old avatar
                if (!empty($testimonial['avatar_url'])) {
                    $old_path = __DIR__ . '/../' . $testimonial['avatar_url'];
                    if (file_exists($old_path)) unlink($old_path);
                }
                $avatar_url = 'uploads/testimonials/' . $filename;
            }
        }
    }

    if (empty($name) || empty($text)) {
        $action_error = "Customer name and testimonial text are required.";
    } else {
        $stmt_u = $pdo->prepare("UPDATE testimonials SET customer_name = ?, customer_title = ?, testimonial_text = ?, rating = ?, avatar_url = ?, display_order = ?, is_featured = ? WHERE id = ?");
        $stmt_u->execute([$name, $title, $text, $rating, $avatar_url, $order, $featured, $edit_id]);
        $action_msg = "Testimonial updated successfully.";
        // Refresh data
        $stmt_t->execute([$edit_id]);
        $testimonial = $stmt_t->fetch();
    }
}
?>

    <style>
        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .test-edit-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .test-edit-grid {
                grid-template-columns: 1fr !important;
            }
            .test-edit-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .test-edit-form-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="testimonials.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Testimonials
        </a>
    </div>

    <div class="test-edit-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Edit Testimonial</h2>
        <span class="admin-badge <?php echo $testimonial['status'] ? 'badge-completed' : 'badge-pending'; ?>" style="font-size:0.75rem;">
            <?php echo $testimonial['status'] ? 'Active' : 'Inactive'; ?>
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

    <div class="test-edit-grid" style="display:grid; grid-template-columns:1fr 320px; gap:28px; align-items:start;">

        <!-- Edit Form -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-pen" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Update Testimonial</h3>
            </div>

            <form action="testimonial_edit.php?id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data" style="padding:28px;">
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" required value="<?php echo htmlspecialchars($testimonial['customer_name']); ?>">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Title / Designation</label>
                    <input type="text" name="customer_title" class="form-control" value="<?php echo htmlspecialchars($testimonial['customer_title'] ?? ''); ?>">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Testimonial Text *</label>
                    <textarea name="testimonial_text" class="form-control" rows="5" required><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></textarea>
                </div>

                <div class="test-edit-form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div class="form-group">
                        <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Rating</label>
                        <select name="rating" class="form-control">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $testimonial['rating'] == $i ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Display Order</label>
                        <input type="number" name="display_order" class="form-control" min="0" value="<?php echo $testimonial['display_order']; ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Avatar Photo</label>
                    <div style="border:1px dashed rgba(212,175,55,0.25); border-radius:8px; padding:20px; text-align:center; background:rgba(212,175,55,0.02); cursor:pointer;" onclick="document.getElementById('avatar-input').click();">
                        <i class="fas fa-user-circle" style="font-size:1.5rem; color:rgba(212,175,55,0.3); margin-bottom:8px; display:block;"></i>
                        <p style="font-size:0.8rem; color:rgba(255,255,255,0.4); margin:0;">Upload new avatar (optional)</p>
                        <p style="font-size:0.7rem; color:rgba(255,255,255,0.25); margin:4px 0 0 0;">Leave empty to keep current</p>
                        <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display:none;" onchange="previewAvatar(this);">
                    </div>
                    <div id="avatar-preview" style="margin-top:10px; display:none;">
                        <img id="preview-avatar" src="" alt="Preview" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid rgba(212,175,55,0.2);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:12px 16px; border-radius:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
                        <input type="checkbox" name="is_featured" value="1" <?php echo $testimonial['is_featured'] ? 'checked' : ''; ?> style="accent-color:#D4AF37; width:18px; height:18px;">
                        <div>
                            <span style="font-weight:600; color:#fff; font-size:0.88rem;">Mark as Featured</span>
                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.35); display:block;">Show prominently on homepage</span>
                        </div>
                    </label>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" name="update_testimonial" class="btn-gold" style="flex:1; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fas fa-save"></i> Update Testimonial
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div style="position:sticky; top:96px;">
            <div class="glass-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Details</h4>
                </div>
                <div style="padding:18px 20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">ID</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;">#<?php echo $testimonial['id']; ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Status</span>
                        <span class="admin-badge <?php echo $testimonial['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                            <?php echo $testimonial['status'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Rating</span>
                        <div style="display:flex; gap:2px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" style="font-size:0.65rem; color:<?php echo $i <= $testimonial['rating'] ? '#facc15' : 'rgba(255,255,255,0.15)'; ?>;"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.4);">Order</span>
                        <span style="font-size:0.78rem; color:#fff; font-weight:600;"><?php echo $testimonial['display_order']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Current Avatar -->
            <div class="glass-card" style="margin-top:16px; padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <h4 style="font-size:0.8rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Current Avatar</h4>
                </div>
                <div style="padding:20px; display:flex; align-items:center; justify-content:center;">
                    <div style="width:80px; height:80px; border-radius:50%; overflow:hidden; background:rgba(212,175,55,0.06); border:2px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center;">
                        <?php if (!empty($testimonial['avatar_url'])): ?>
                            <img src="../<?php echo htmlspecialchars($testimonial['avatar_url']); ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user" style="font-size:1.8rem; color:rgba(212,175,55,0.3);"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-avatar').src = e.target.result;
                document.getElementById('avatar-preview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
