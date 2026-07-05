<?php
// admin/product_add.php — Dedicated Add Product page
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';
$action_error = '';

// Fetch categories for dropdown
$stmt_cats = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$stmt_cats->execute();
$categories = $stmt_cats->fetchAll();

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
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
    $image_url = '';
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

    // Handle gallery images upload
    $gallery_paths = [];
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
                        $gallery_paths[] = 'uploads/products/' . $filename;
                    }
                }
            }
        }
    }
    $image_gallery = implode(',', $gallery_paths);

    if (empty($name) || empty($slug)) {
        $action_error = "Product name and slug are required.";
    } else {
        // Check slug uniqueness
        $stmt_slug = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt_slug->execute([$slug]);
        if ($stmt_slug->fetch()) {
            $action_error = "Product slug already exists. Please choose a unique slug.";
        } else {
            $stmt_i = $pdo->prepare("
                INSERT INTO products (name, slug, short_description, description, category_id, image_url, image_gallery, benefits, ingredients, how_to_use, disclaimer, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_i->execute([$name, $slug, $short_description, $description, $category_id, $image_url, $image_gallery, $benefits, $ingredients, $how_to_use, $disclaimer, $is_active]);
            header("Location: products.php?msg=added");
            exit();
        }
    }
}
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
        .upload-zone {
            border: 1px dashed rgba(212,175,55,0.25);
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            background: rgba(212,175,55,0.02);
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .upload-zone:hover { border-color: rgba(212,175,55,0.5); }
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
            <h2 style="font-size:1.6rem; font-weight:800; color:#fff; margin:0 0 4px 0; letter-spacing:-0.3px;">Add New Product</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Fill in all product details below</p>
        </div>
    </div>

    <!-- Error -->
    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:0.95rem;"></i>
            <span style="color:#ef4444; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Trumbowyg Editor CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/colors/ui/trumbowyg.colors.min.css">

    <form action="product_add.php" method="POST" enctype="multipart/form-data">
        
        <!-- Section: Basic Info -->
        <div class="form-section-card" style="margin-bottom:20px;">
            <div class="form-section-title">
                <i class="fas fa-info-circle"></i> Basic Information
            </div>
            
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:18px;">
                <div>
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. WOLFPACK - UNLEASH THE ALPHA WITHIN" required oninput="autoSlug(this.value)">
                </div>
                <div>
                    <label class="form-label">URL Slug *</label>
                    <input type="text" name="slug" id="p-slug" class="form-input" placeholder="auto-generated-from-name" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                <div>
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-input" style="appearance:auto;">
                        <option value="">-- No Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; align-items:flex-end; padding-bottom:2px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:rgba(255,255,255,0.7);">
                        <input type="checkbox" name="is_active" value="1" checked style="accent-color:#D4AF37; width:16px; height:16px;">
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Short Description (tagline)</label>
                <input type="text" name="short_description" class="form-input" placeholder="One-line product tagline for product cards">
            </div>

            <div>
                <label class="form-label">Full Description</label>
                <textarea name="description" id="p-desc" class="form-input" rows="6" placeholder="Detailed product description for the product page"></textarea>
            </div>
        </div>

        <!-- Section: Images -->
        <div class="form-section-card" style="margin-bottom:20px;">
            <div class="form-section-title">
                <i class="fas fa-image"></i> Product Images
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label class="form-label">Main Product Image</label>
                    <div class="upload-zone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem; color:rgba(212,175,55,0.4); margin-bottom:8px; display:block;"></i>
                        <input type="file" name="main_image" accept="image/*" style="width:100%; font-size:0.82rem; color:rgba(255,255,255,0.6);">
                        <span class="form-hint">JPG, PNG, WEBP — Max 5MB</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Gallery Images (multiple)</label>
                    <div class="upload-zone">
                        <i class="fas fa-images" style="font-size:1.5rem; color:rgba(212,175,55,0.4); margin-bottom:8px; display:block;"></i>
                        <input type="file" name="gallery_images[]" accept="image/*" multiple style="width:100%; font-size:0.82rem; color:rgba(255,255,255,0.6);">
                        <span class="form-hint">Hold Ctrl/Cmd to select multiple files</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Product Details -->
        <div class="form-section-card" style="margin-bottom:20px;">
            <div class="form-section-title">
                <i class="fas fa-list-alt"></i> Product Details
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Key Benefits</label>
                <textarea name="benefits" id="p-benefits" class="form-input" rows="4" placeholder="Add bullet points for key benefits"></textarea>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label">Ingredients</label>
                <textarea name="ingredients" id="p-ingredients" class="form-input" rows="3" placeholder="Full ingredient list with dosages"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label class="form-label">How to Use</label>
                    <textarea name="how_to_use" id="p-howto" class="form-input" rows="3" placeholder="Usage instructions"></textarea>
                </div>
                <div>
                    <label class="form-label">Disclaimer</label>
                    <textarea name="disclaimer" id="p-disclaimer" class="form-input" rows="3" placeholder="Safety warnings / disclaimers"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div style="display:flex; gap:14px; margin-top:8px;">
            <button type="submit" name="add_product" class="btn-gold" style="padding:12px 36px; font-size:0.88rem; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-plus"></i> Create Product
            </button>
            <a href="products.php" class="btn-outline-gold" style="padding:12px 28px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center;">
                Cancel
            </a>
        </div>
    </form>

    <!-- Trumbowyg JS + Image Upload Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/colors/trumbowyg.colors.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/upload/trumbowyg.upload.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/resizimg/trumbowyg.resizimg.min.js"></script>

    <script>
    // Auto-generate slug
    function autoSlug(val) {
        var slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('p-slug').value = slug;
    }

    // Trumbowyg editor config
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

    // Initialize Trumbowyg on all rich text fields
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
