<?php
// admin/bundle_add.php — Dedicated Add Combo page
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Handle CREATE bundle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_bundle'])) {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $combo_price = (float)$_POST['combo_price'];
    $discount_percent = (float)$_POST['discount_percent'];
    $display_order = (int)$_POST['display_order'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Handle banner image upload
    $banner_image = '';
    if (isset($_FILES['banner_image_file']) && $_FILES['banner_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['banner_image_file'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'combo_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $banner_image = 'uploads/products/' . $filename;
            }
        }
    }

    if (empty($title)) {
        $action_error = "Combo title is required.";
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }
        $stmt_check = $pdo->prepare("SELECT id FROM bundles WHERE slug = ?");
        $stmt_check->execute([$slug]);
        if ($stmt_check->fetch()) {
            $action_error = "A combo with this slug already exists.";
        } else {
            $stmt_i = $pdo->prepare("
                INSERT INTO bundles (title, slug, description, banner_image, combo_price, discount_percent, display_order, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_i->execute([$title, $slug, $description, $banner_image, $combo_price, $discount_percent, $display_order, $status]);
            $new_id = $pdo->lastInsertId();
            header("Location: bundle_edit.php?id=" . $new_id);
            exit();
        }
    }
}

// Fetch existing bundles count
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM bundles");
$stmt_total->execute();
$total_bundles = (int)$stmt_total->fetchColumn();
?>

    <div style="margin-bottom:20px;">
        <a href="bundles.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Combos
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Create New Combo</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Total combos: <strong style="color:var(--gold-primary);"><?php echo $total_bundles; ?></strong></div>
    </div>

    <?php if ($action_error): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(255,50,50,0.05); border-color:rgba(255,50,50,0.3); color:#ff6b6b; margin-bottom:25px;">
            ❌ <?php echo htmlspecialchars($action_error); ?>
        </div>
    <?php endif; ?>

    <!-- Trumbowyg CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">

    <div class="glass-card" style="padding:30px; border-radius:8px;">
        <form action="bundle_add.php" method="POST" enctype="multipart/form-data">
            
            <!-- Basic Info -->
            <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
                <i class="fas fa-info-circle"></i> Combo Details
            </h3>

            <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="form-group">
                    <label for="b-title">Combo Title *</label>
                    <input type="text" name="title" id="b-title" class="form-control" 
                        placeholder="e.g. Wolfpack + Wolftox Combo" required oninput="autoSlugBundle(this.value)">
                </div>
                <div class="form-group">
                    <label for="b-slug">URL Slug</label>
                    <input type="text" name="slug" id="b-slug" class="form-control" 
                        placeholder="auto-generated-from-title">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label for="b-desc">Description</label>
                <textarea name="description" id="b-desc" class="form-control" rows="4"></textarea>
            </div>

            <!-- Pricing -->
            <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px; margin-top:30px;">
                <i class="fas fa-tag"></i> Pricing & Display
            </h3>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="form-group">
                    <label for="b-price">Combo Price (₹) *</label>
                    <input type="number" step="0.01" name="combo_price" id="b-price" class="form-control" value="0" required>
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">Should be less than sum of individual prices</small>
                </div>
                <div class="form-group">
                    <label for="b-disc">Discount Display (%)</label>
                    <input type="number" step="0.01" name="discount_percent" id="b-disc" class="form-control" value="0">
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">Shown as discount badge</small>
                </div>
                <div class="form-group">
                    <label for="b-order">Display Order</label>
                    <input type="number" name="display_order" id="b-order" class="form-control" value="0">
                </div>
            </div>

            <!-- Media -->
            <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px; margin-top:30px;">
                <i class="fas fa-image"></i> Banner Image
            </h3>

            <div class="form-group" style="margin-bottom:20px; max-width:400px;">
                <label>Upload Banner Image</label>
                <input type="file" name="banner_image_file" accept="image/*" class="form-control" style="padding:8px;" required>
                <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">JPG, PNG, WEBP — Max 5MB. Stored in uploads/products/</small>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem;">
                    <input type="checkbox" name="status" value="1" checked style="accent-color:var(--gold-primary); width:18px; height:18px;">
                    <span>Active</span>
                </label>
            </div>

            <!-- Submit -->
            <div style="display:flex; gap:15px; margin-top:30px; padding-top:20px; border-top:1px solid var(--border-color);">
                <button type="submit" name="create_bundle" class="btn-gold" style="padding:12px 40px; font-size:0.95rem; font-weight:700;">
                    <i class="fas fa-plus"></i> Create Combo
                </button>
                <a href="bundles.php" class="btn-outline-gold" style="padding:12px 30px; font-size:0.9rem; text-decoration:none; display:flex; align-items:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
    function autoSlugBundle(val) {
        var slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('b-slug').value = slug;
    }
    </script>

    <!-- Trumbowyg JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/upload/trumbowyg.upload.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#b-desc').trumbowyg({
            btns: [
                ['viewHTML'],
                ['formatting'],
                ['strong', 'em', 'del'],
                ['link'],
                ['insertImage'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen']
            ],
            plugins: {
                upload: {
                    serverPath: 'upload_handler.php',
                    fileFieldName: 'file',
                    urlPropertyName: 'url'
                }
            },
            autogrow: true
        });
    });
    </script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
