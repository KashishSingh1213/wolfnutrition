<?php
// admin/category_add.php — Dedicated Add Category page
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $desc = trim($_POST['description']);
    $order = (int)$_POST['display_order'];
    $status = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) {
        $action_error = "Category name is required.";
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        }
        $stmt_check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt_check->execute([$slug]);
        if ($stmt_check->fetch()) {
            $action_error = "A category with this slug already exists.";
        } else {
            $stmt_i = $pdo->prepare("INSERT INTO categories (name, slug, description, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt_i->execute([$name, $slug, $desc, $order, $status]);
            header("Location: categories.php?msg=added");
            exit();
        }
    }
}

// Fetch product count for reference
$stmt_total = $pdo->prepare("SELECT COUNT(id) FROM categories");
$stmt_total->execute();
$total_cats = (int)$stmt_total->fetchColumn();
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
        .existing-cat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .existing-cat-item:last-child { border-bottom: none; }
        .badge-active {
            background: rgba(74,222,128,0.1);
            color: #4ade80;
            font-size: 0.62rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .badge-inactive {
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.4);
            font-size: 0.62rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }
    </style>

    <!-- Back Link -->
    <div style="margin-bottom:24px;">
        <a href="categories.php" style="color:rgba(255,255,255,0.45); font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h2 style="font-size:1.6rem; font-weight:800; color:#fff; margin:0 0 4px 0; letter-spacing:-0.3px;">Add New Category</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Total categories: <span style="color:#D4AF37; font-weight:600;"><?php echo $total_cats; ?></span></p>
        </div>
    </div>

    <!-- Error -->
    <?php if ($action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:0.95rem;"></i>
            <span style="color:#ef4444; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start;">
        
        <!-- Add Form -->
        <div class="form-section-card">
            <div class="form-section-title">
                <i class="fas fa-plus-circle"></i> Category Details
            </div>
            
            <form action="category_add.php" method="POST">
                <div style="margin-bottom:18px;">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Vitality" required oninput="autoSlugCat(this.value)">
                    <span class="form-hint">Used for product grouping and navigation</span>
                </div>

                <div style="margin-bottom:18px;">
                    <label class="form-label">URL Slug</label>
                    <input type="text" name="slug" id="cat-slug" class="form-input" placeholder="auto-generated-from-name">
                    <span class="form-hint">Used in URL: wolfnutrition.in/category/<strong style="color:rgba(255,255,255,0.6);">your-slug</strong></span>
                </div>

                <div style="margin-bottom:18px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Short description for this category"></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-input" value="0">
                        <span class="form-hint">Lower number = appears first</span>
                    </div>
                    <div style="display:flex; align-items:flex-end; padding-bottom:2px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:rgba(255,255,255,0.7);">
                            <input type="checkbox" name="is_active" value="1" checked style="accent-color:#D4AF37; width:16px; height:16px;">
                            <span>Active</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; gap:14px; margin-top:28px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.06);">
                    <button type="submit" name="add_category" class="btn-gold" style="padding:12px 32px; font-size:0.88rem; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-plus"></i> Create Category
                    </button>
                    <a href="categories.php" class="btn-outline-gold" style="padding:12px 24px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Existing Categories Sidebar -->
        <div class="form-section-card">
            <div class="form-section-title">
                <i class="fas fa-list"></i> Existing Categories
            </div>
            <?php
            $stmt_ex = $pdo->prepare("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.display_order ASC");
            $stmt_ex->execute();
            $existing = $stmt_ex->fetchAll();
            ?>
            <?php if (empty($existing)): ?>
                <div style="text-align:center; padding:24px 0;">
                    <i class="fas fa-folder-open" style="font-size:1.5rem; color:rgba(255,255,255,0.1); margin-bottom:8px; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.35); font-size:0.85rem; margin:0;">No categories yet.</p>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:0;">
                    <?php foreach ($existing as $cat): ?>
                        <div class="existing-cat-item">
                            <div>
                                <span style="color:#fff; font-weight:600; font-size:0.85rem;"><?php echo htmlspecialchars($cat['name']); ?></span>
                                <span style="font-size:0.68rem; color:rgba(255,255,255,0.3); display:block; font-family:monospace; margin-top:2px;"><?php echo htmlspecialchars($cat['slug']); ?></span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:0.75rem; color:rgba(255,255,255,0.4);"><?php echo $cat['product_count']; ?> prod.</span>
                                <span class="<?php echo $cat['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $cat['is_active'] ? 'Active' : 'Off'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function autoSlugCat(val) {
        var slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('cat-slug').value = slug;
    }
    </script>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
