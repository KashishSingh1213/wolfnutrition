<?php
// admin/testimonial_add.php — Add Testimonial
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_testimonial'])) {
    $name = trim($_POST['customer_name']);
    $title = trim($_POST['customer_title']);
    $text = trim($_POST['testimonial_text']);
    $rating = (int)$_POST['rating'];
    $order = (int)$_POST['display_order'];
    $featured = isset($_POST['is_featured']) ? 1 : 0;

    // Handle avatar upload
    $avatar_url = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/testimonials/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $avatar_url = 'uploads/testimonials/' . $filename;
            }
        }
    }

    if (empty($name) || empty($text)) {
        $action_error = "Customer name and testimonial text are required.";
    } else {
        $stmt_i = $pdo->prepare("INSERT INTO testimonials (customer_name, customer_title, testimonial_text, rating, avatar_url, display_order, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt_i->execute([$name, $title, $text, $rating, $avatar_url, $order, $featured]);
        header("Location: testimonials.php?msg=created");
        exit();
    }
}

// Total count
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM testimonials");
$stmt_total->execute();
$total_testimonials = (int)$stmt_total->fetchColumn();
?>

    <style>
        @media (max-width: 768px) {
            .test-add-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 8px;
            }
            .test-add-form-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <div style="margin-bottom:20px;">
        <a href="testimonials.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Testimonials
        </a>
    </div>

    <div class="test-add-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">New Testimonial</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total testimonials: <strong style="color:var(--gold-primary);"><?php echo $total_testimonials; ?></strong></div>
    </div>

    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div class="glass-card" style="max-width:650px; padding:0; overflow:hidden;">
        <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
            <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-plus" style="color:#D4AF37; font-size:0.85rem;"></i>
            </div>
            <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Create Testimonial</h3>
        </div>

        <form action="testimonial_add.php" method="POST" enctype="multipart/form-data" style="padding:28px;">
            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Customer Name *</label>
                <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Rahul Sharma">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Title / Designation</label>
                <input type="text" name="customer_title" class="form-control" placeholder="e.g. Fitness Trainer, Mumbai">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Testimonial Text *</label>
                <textarea name="testimonial_text" class="form-control" rows="5" required placeholder="Write the customer's testimonial here..."></textarea>
            </div>

            <div class="test-add-form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div class="form-group">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Rating</label>
                    <select name="rating" class="form-control">
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Display Order</label>
                    <input type="number" name="display_order" class="form-control" min="0" value="0">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px; display:block;">Avatar Photo</label>
                <div style="border:1px dashed rgba(212,175,55,0.25); border-radius:8px; padding:20px; text-align:center; background:rgba(212,175,55,0.02); cursor:pointer;" onclick="document.getElementById('avatar-input').click();">
                    <i class="fas fa-user-circle" style="font-size:1.5rem; color:rgba(212,175,55,0.3); margin-bottom:8px; display:block;"></i>
                    <p style="font-size:0.8rem; color:rgba(255,255,255,0.4); margin:0;">Click to upload avatar (optional)</p>
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display:none;" onchange="previewAvatar(this);">
                </div>
                <div id="avatar-preview" style="margin-top:10px; display:none;">
                    <img id="preview-avatar" src="" alt="Preview" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid rgba(212,175,55,0.2);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:24px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:12px 16px; border-radius:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
                    <input type="checkbox" name="is_featured" value="1" style="accent-color:#D4AF37; width:18px; height:18px;">
                    <div>
                        <span style="font-weight:600; color:#fff; font-size:0.88rem;">Mark as Featured</span>
                        <span style="font-size:0.75rem; color:rgba(255,255,255,0.35); display:block;">Show prominently on homepage</span>
                    </div>
                </label>
            </div>

            <button type="submit" name="create_testimonial" class="btn-gold" style="width:100%; padding:13px 20px; font-size:0.88rem; font-weight:700; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fas fa-plus"></i> Save Testimonial
            </button>
        </form>
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
