<?php
// admin/product_edit.php — Dedicated Edit Product page
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id <= 0) {
    header("Location: products.php");
    exit();
}

$stmt_p = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt_p->execute([$edit_id]);
$product = $stmt_p->fetch();
if (!$product) {
    header("Location: products.php?msg=notfound");
    exit();
}

$stmt_cats = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$stmt_cats->execute();
$categories = $stmt_cats->fetchAll();

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $short_description = trim($_POST['short_description']);
    $description = trim($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $benefits = trim($_POST['benefits']);
    $ingredients = trim($_POST['ingredients']);
    $how_to_use = trim($_POST['how_to_use']);
    $disclaimer = trim($_POST['disclaimer']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle main image upload
    $image_url = $product['image_url']; // Keep existing by default
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['main_image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $image_url = 'uploads/products/' . $filename;
            }
        }
    }

    // Handle gallery images upload (append to existing)
    $existing_gallery = $product['image_gallery'] ? $product['image_gallery'] : '';
    $new_gallery_paths = [];
    if (isset($_FILES['gallery_images'])) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_count = count($_FILES['gallery_images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['gallery_images']['name'][$i],
                    'type' => $_FILES['gallery_images']['type'][$i],
                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                    'size' => $_FILES['gallery_images']['size'][$i]
                ];
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                        $new_gallery_paths[] = 'uploads/products/' . $filename;
                    }
                }
            }
        }
    }
    
    // Merge existing + new gallery
    if (!empty($new_gallery_paths)) {
        $all_gallery = array_filter(array_merge([$existing_gallery], $new_gallery_paths));
        $image_gallery = implode(',', $all_gallery);
    } else {
        $image_gallery = $existing_gallery;
    }

    // Handle remove gallery image
    if (isset($_POST['remove_gallery_image'])) {
        $remove_url = trim($_POST['remove_gallery_image']);
        $gallery_arr = array_filter(explode(',', $image_gallery));
        $gallery_arr = array_filter($gallery_arr, function($v) use ($remove_url) {
            return trim($v) !== $remove_url;
        });
        $image_gallery = implode(',', $gallery_arr);
    }

    if (empty($name) || empty($slug)) {
        $action_error = "Product name and slug are required.";
    } else {
        $stmt_slug = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $stmt_slug->execute([$slug, $edit_id]);
        if ($stmt_slug->fetch()) {
            $action_error = "Product slug already exists.";
        } else {
            $stmt_u = $pdo->prepare("
                UPDATE products SET 
                name = ?, slug = ?, short_description = ?, description = ?, 
                category_id = ?, image_url = ?, image_gallery = ?, benefits = ?, 
                ingredients = ?, how_to_use = ?, disclaimer = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt_u->execute([$name, $slug, $short_description, $description, $category_id, $image_url, $image_gallery, $benefits, $ingredients, $how_to_use, $disclaimer, $is_active, $edit_id]);
            
            $stmt_p2 = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt_p2->execute([$edit_id]);
            $product = $stmt_p2->fetch();
            
            $action_msg = "Product updated successfully.";
        }
    }
}

// Parse gallery for preview
$gallery_images = array_filter(explode(',', $product['image_gallery'] ?? ''));
$gallery_images = array_map('trim', $gallery_images);
$gallery_images = array_filter($gallery_images);
?>

    <style>
        .form-section-card {
            background: rgba(18,18,18,0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 28px;
        }
        .form-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: #D4AF37;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            display: block;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            color: #fff;
            font-size: 0.88rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: rgba(212,175,55,0.5);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.08);
        }
        .form-input::placeholder { color: rgba(255,255,255,0.25); }
        .form-hint {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.35);
            margin-top: 5px;
            display: block;
        }
        .image-preview-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .image-preview-item {
            position: relative;
            display: inline-block;
        }
        .image-preview-item img {
            height: 72px;
            width: 72px;
            object-fit: contain;
            background: rgba(0,0,0,0.4);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.06);
        }
    </style>

    <!-- Back Link -->
    <div style="margin-bottom:24px;">
        <a href="products.php" style="color:rgba(255,255,255,0.45); font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h2 style="font-size:1.6rem; font-weight:800; color:#fff; margin:0 0 4px 0; letter-spacing:-0.3px;">Edit Product</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Editing: <span style="color:#D4AF37; font-weight:600;"><?php echo htmlspecialchars($product['name']); ?></span></p>
        </div>
        <span style="font-size:0.75rem; color:rgba(255,255,255,0.3); background:rgba(255,255,255,0.04); padding:5px 12px; border-radius:6px;">ID: #<?php echo $edit_id; ?></span>
    </div>

    <!-- Flash Messages -->
    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.25); border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:0.95rem;"></i>
            <span style="color:#4ade80; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:0.95rem;"></i>
            <span style="color:#ef4444; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Trumbowyg Editor CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/colors/ui/trumbowyg.colors.min.css">

    <form action="product_edit.php?id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo $edit_id; ?>">
        
        <!-- Section: Basic Info -->
        <div class="form-section-card" style="margin-bottom:20px;">
            <div class="form-section-title">
                <i class="fas fa-info-circle"></i> Basic Information
            </div>
            
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:18px;">
                <div>
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($product['name']); ?>" required oninput="autoSlug(this.value)">
                </div>
                <div>
                    <label class="form-label">URL Slug *</label>
                    <input type="text" name="slug" id="p-slug" class="form-input" value="<?php echo htmlspecialchars($product['slug']); ?>" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                <div>
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-input" style="appearance:auto;">
                        <option value="">-- No Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; align-items:flex-end; padding-bottom:2px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:rgba(255,255,255,0.7);">
                        <input type="checkbox" name="is_active" value="1" <?php echo $product['is_active'] ? 'checked' : ''; ?> style="accent-color:#D4AF37; width:16px; height:16px;">
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Short Description (tagline)</label>
                <input type="text" name="short_description" class="form-input" value="<?php echo htmlspecialchars($product['short_description']); ?>">
            </div>

            <div>
                <label class="form-label">Full Description</label>
                <textarea name="description" id="p-desc" class="form-input" rows="6"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
        </div>

        <!-- Section: Images -->
        <div class="form-section-card" style="margin-bottom:20px;">
            <div class="form-section-title">
                <i class="fas fa-image"></i> Product Images
            </div>

            <!-- Current main image -->
            <?php if ($product['image_url']): ?>
                <div style="margin-bottom:16px; display:flex; align-items:center; gap:14px;">
                    <span style="font-size:0.78rem; color:rgba(255,255,255,0.45); font-weight:600;">Current main image:</span>
                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="" style="height:72px; width:72px; object-fit:contain; background:rgba(0,0,0,0.4); border-radius:8px; border:1px solid rgba(255,255,255,0.06);">
                </div>
            <?php endif; ?>

            <div style="margin-bottom:18px;">
                <label class="form-label">Replace Main Image (leave empty to keep current)</label>
                <input type="file" name="main_image" accept="image/*" class="form-input" style="padding:10px;">
                <span class="form-hint">JPG, PNG, WEBP — Max 5MB</span>
            </div>

            <!-- Current gallery preview -->
            <?php if (!empty($gallery_images)): ?>
                <div style="margin-bottom:16px;">
                    <span class="form-label">Current gallery images:</span>
                    <div class="image-preview-grid">
                        <?php foreach ($gallery_images as $g_img): ?>
                            <div class="image-preview-item">
                                <img src="../<?php echo htmlspecialchars($g_img); ?>" alt="">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div>
                <label class="form-label">Add More Gallery Images</label>
                <input type="file" name="gallery_images[]" accept="image/*" class="form-input" multiple style="padding:10px;">
                <span class="form-hint">Hold Ctrl/Cmd to select multiple. New images are added to existing gallery.</span>
            </div>
        </div>

        <!-- Section: Product Details -->
        <div class="form-section-card" style="margin-bottom:20px;">
            <div class="form-section-title">
                <i class="fas fa-list-alt"></i> Product Details
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Key Benefits</label>
                <textarea name="benefits" id="p-benefits" class="form-input" rows="4"><?php echo htmlspecialchars($product['benefits']); ?></textarea>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Ingredients</label>
                <textarea name="ingredients" id="p-ingredients" class="form-input" rows="3"><?php echo htmlspecialchars($product['ingredients']); ?></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label class="form-label">How to Use</label>
                    <textarea name="how_to_use" id="p-howto" class="form-input" rows="3"><?php echo htmlspecialchars($product['how_to_use']); ?></textarea>
                </div>
                <div>
                    <label class="form-label">Disclaimer</label>
                    <textarea name="disclaimer" id="p-disclaimer" class="form-input" rows="3"><?php echo htmlspecialchars($product['disclaimer']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div style="display:flex; gap:14px; margin-top:8px;">
            <button type="submit" name="edit_product" class="btn-gold" style="padding:12px 36px; font-size:0.88rem; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-save"></i> Update Product
            </button>
            <a href="products.php" class="btn-outline-gold" style="padding:12px 28px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center;">
                Cancel
            </a>
        </div>
    </form>

    <!-- Trumbowyg JS + Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/colors/trumbowyg.colors.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/upload/trumbowyg.upload.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/resizimg/trumbowyg.resizimg.min.js"></script>

    <script>
    function autoSlug(val) {
        var slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('p-slug').value = slug;
    }

    var trumbowygConfig = {
        btns: [
            ['viewHTML'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
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
                headers: {},
                urlPropertyName: 'url'
            },
            resizimg: {
                minSize: 100,
                maxSize: 2000
            }
        },
        autogrow: true,
        autogrowOnEnter: true,
        removeformatPasted: true
    };

    $(document).ready(function() {
        $('#p-desc').trumbowyg(trumbowygConfig);
        $('#p-benefits').trumbowyg(trumbowygConfig);
        $('#p-ingredients').trumbowyg(trumbowygConfig);
        $('#p-howto').trumbowyg(trumbowygConfig);
        $('#p-disclaimer').trumbowyg(trumbowygConfig);
    });
    </script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
