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

    <div style="margin-bottom:20px;">
        <a href="products.php" style="color:var(--gold-muted); font-size:0.9rem; text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Back to Products List
        </a>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Add New Product</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Fill in all product details below</div>
    </div>

    <?php if ($action_error): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(255,50,50,0.05); border-color:rgba(255,50,50,0.3); color:#ff6b6b; margin-bottom:25px;">
            ❌ <?php echo htmlspecialchars($action_error); ?>
        </div>
    <?php endif; ?>

    <!-- Trumbowyg Editor CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/colors/ui/trumbowyg.colors.min.css">

    <div class="glass-card" style="padding:30px; border-radius:8px;">
        <form action="product_add.php" method="POST" enctype="multipart/form-data">
            
            <!-- Basic Info -->
            <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
                <i class="fas fa-info-circle"></i> Basic Information
            </h3>
            
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="form-group">
                    <label for="p-name">Product Name *</label>
                    <input type="text" name="name" id="p-name" class="form-control" 
                        placeholder="e.g. WOLFPACK - UNLEASH THE ALPHA WITHIN" 
                        required oninput="autoSlug(this.value)">
                </div>
                <div class="form-group">
                    <label for="p-slug">URL Slug *</label>
                    <input type="text" name="slug" id="p-slug" class="form-control" 
                        placeholder="auto-generated-from-name" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="form-group">
                    <label for="p-cat">Category</label>
                    <select name="category_id" id="p-cat" class="form-control">
                        <option value="">-- No Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.9rem; padding-bottom:10px;">
                        <input type="checkbox" name="is_active" value="1" checked style="accent-color:var(--gold-primary); width:18px; height:18px;">
                        <span>Active (visible on storefront)</span>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label for="p-short-desc">Short Description (tagline)</label>
                <textarea name="short_description" id="p-short-desc" class="form-control" rows="2" 
                    placeholder="One-line product tagline for product cards"></textarea>
            </div>

            <div class="form-group" style="margin-bottom:25px;">
                <label for="p-desc">Full Description</label>
                <textarea name="description" id="p-desc" class="form-control" rows="8" 
                    placeholder="Detailed product description for the product page"></textarea>
            </div>

            <!-- Media -->
            <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px; margin-top:30px;">
                <i class="fas fa-image"></i> Product Images
            </h3>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="form-group">
                    <label>Main Product Image</label>
                    <input type="file" name="main_image" accept="image/*" class="form-control" style="padding:8px;">
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">JPG, PNG, WEBP — Max 5MB</small>
                </div>
                <div class="form-group">
                    <label>Gallery Images (multiple)</label>
                    <input type="file" name="gallery_images[]" accept="image/*" class="form-control" multiple style="padding:8px;">
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:4px; display:block;">Hold Ctrl/Cmd to select multiple files</small>
                </div>
            </div>

            <!-- Details -->
            <h3 style="font-size:1.1rem; text-transform:uppercase; color:var(--gold-primary); margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:8px; margin-top:30px;">
                <i class="fas fa-list-alt"></i> Product Details
            </h3>

            <div class="form-group" style="margin-bottom:20px;">
                <label for="p-benefits">Key Benefits</label>
                <textarea name="benefits" id="p-benefits" class="form-control" rows="4" 
                    placeholder="Add bullet points for key benefits"></textarea>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label for="p-ingredients">Ingredients</label>
                <textarea name="ingredients" id="p-ingredients" class="form-control" rows="3" 
                    placeholder="Full ingredient list with dosages"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="form-group">
                    <label for="p-howto">How to Use</label>
                    <textarea name="how_to_use" id="p-howto" class="form-control" rows="3" 
                        placeholder="Usage instructions"></textarea>
                </div>
                <div class="form-group">
                    <label for="p-disclaimer">Disclaimer</label>
                    <textarea name="disclaimer" id="p-disclaimer" class="form-control" rows="3" 
                        placeholder="Safety warnings / disclaimers"></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div style="display:flex; gap:15px; margin-top:30px; padding-top:20px; border-top:1px solid var(--border-color);">
                <button type="submit" name="add_product" class="btn-gold" style="padding:12px 40px; font-size:0.95rem; font-weight:700;">
                    <i class="fas fa-plus"></i> Create Product
                </button>
                <a href="products.php" class="btn-outline-gold" style="padding:12px 30px; font-size:0.9rem; text-decoration:none; display:flex; align-items:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

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
