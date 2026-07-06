<?php
// admin/categories.php — Category List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Delete Category
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt_count = $pdo->prepare("SELECT COUNT(id) FROM products WHERE category_id = ?");
    $stmt_count->execute([$del_id]);
    $product_count = (int)$stmt_count->fetchColumn();
    
    if ($product_count > 0) {
        $action_msg = "Cannot delete — category has $product_count product(s). Remove them first.";
    } else {
        $stmt_d = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt_d->execute([$del_id]);
        header("Location: categories.php?msg=deleted");
        exit();
    }
}

// Success messages
if (isset($_GET['msg'])) {
    $msgs = ['added' => 'Category added successfully.', 'updated' => 'Category updated.', 'deleted' => 'Category deleted.'];
    $action_msg = $msgs[$_GET['msg']] ?? $action_msg;
}

// Fetch categories with product count
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.display_order ASC
");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

    <style>
        .cat-table-wrapper {
            background: rgba(18,18,18,0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            overflow: hidden;
        }
        .cat-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cat-table thead th {
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: rgba(255,255,255,0.4);
            padding: 14px 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .cat-table tbody td {
            padding: 16px 20px;
            font-size: 0.88rem;
            color: rgba(255,255,255,0.7);
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .cat-table tbody tr:last-child td {
            border-bottom: none;
        }
        .cat-table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }
        .badge-active {
            background: rgba(74,222,128,0.1);
            color: #4ade80;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-inactive {
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.4);
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .action-btn:hover { transform: scale(1.1); }

        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .cat-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .cat-page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
            }
            .cat-table-wrapper {
                border: none !important;
                background: transparent !important;
                backdrop-filter: none !important;
            }
            .cat-table thead {
                display: none !important;
            }
            .cat-table,
            .cat-table tbody,
            .cat-table tr,
            .cat-table td {
                display: block !important;
                width: 100% !important;
            }
            .cat-table tbody tr {
                background: rgba(18,18,18,0.6);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 12px;
            }
            .cat-table tbody td {
                padding: 4px 0 !important;
                border-bottom: none !important;
                font-size: 0.85rem;
            }
            .cat-table tbody td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.65rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.8px;
                color: rgba(255,255,255,0.3);
                margin-bottom: 2px;
            }
            .cat-table tbody td.td-name {
                padding-top: 8px !important;
                padding-bottom: 6px !important;
                border-bottom: 1px solid rgba(255,255,255,0.04) !important;
                margin-bottom: 4px;
            }
            .cat-table tbody td.td-name::before {
                display: none;
            }
            .cat-table tbody td.td-actions {
                padding-top: 10px !important;
                border-top: 1px solid rgba(255,255,255,0.04);
                margin-top: 4px;
            }
            .cat-table tbody td.td-actions::before {
                display: none;
            }
            .cat-table tbody td .action-btn {
                width: 36px !important;
                height: 36px !important;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="cat-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h2 style="font-size:1.6rem; font-weight:800; color:#fff; margin:0 0 4px 0; letter-spacing:-0.3px;">Category Management</h2>
            <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;"><?php echo count($categories); ?> categor<?php echo count($categories) !== 1 ? 'ies' : 'y'; ?> total</p>
        </div>
        <a href="category_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-weight:600;">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    <!-- Flash Message -->
    <?php if ($action_msg): ?>
        <?php $is_error = strpos($action_msg, 'Cannot') !== false; ?>
        <div style="background:<?php echo $is_error ? 'rgba(239,68,68,0.08)' : 'rgba(74,222,128,0.08)'; ?>; border:1px solid <?php echo $is_error ? 'rgba(239,68,68,0.25)' : 'rgba(74,222,128,0.25)'; ?>; border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas <?php echo $is_error ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>" style="color:<?php echo $is_error ? '#ef4444' : '#4ade80'; ?>; font-size:0.95rem;"></i>
            <span style="color:<?php echo $is_error ? '#ef4444' : '#4ade80'; ?>; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="cat-table-wrapper">
        <?php if (empty($categories)): ?>
            <div style="text-align:center; padding:48px 20px;">
                <i class="fas fa-folder-open" style="font-size:2rem; color:rgba(255,255,255,0.15); margin-bottom:12px; display:block;"></i>
                <p style="color:rgba(255,255,255,0.4); font-size:0.9rem; margin:0 0 16px 0;">No categories created yet.</p>
                <a href="category_add.php" class="btn-gold" style="padding:10px 24px; font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-plus"></i> Create First Category
                </a>
            </div>
        <?php else: ?>
            <table class="cat-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Order</th>
                        <th>Name</th>
                        <th style="width:140px;">Slug</th>
                        <th>Description</th>
                        <th style="width:90px; text-align:center;">Products</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:100px; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td data-label="Order">
                                <span style="background:rgba(212,175,55,0.1); color:#D4AF37; font-size:0.78rem; font-weight:700; padding:3px 8px; border-radius:4px; min-width:24px; display:inline-block; text-align:center;"><?php echo $cat['display_order']; ?></span>
                            </td>
                            <td data-label="Category" class="td-name">
                                <a href="category_edit.php?id=<?php echo $cat['id']; ?>" style="color:#fff; text-decoration:none; font-weight:700; font-size:0.92rem; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='#fff'">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </td>
                            <td data-label="Slug" style="font-size:0.8rem; color:rgba(255,255,255,0.35); font-family:monospace;"><?php echo htmlspecialchars($cat['slug']); ?></td>
                            <td data-label="Description" style="font-size:0.82rem; color:rgba(255,255,255,0.5); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo htmlspecialchars($cat['description'] ?: '—'); ?>
                            </td>
                            <td data-label="Products" style="text-align:center;">
                                <span style="font-weight:700; font-size:1rem; color:#fff;"><?php echo $cat['product_count']; ?></span>
                            </td>
                            <td data-label="Status">
                                <span class="<?php echo $cat['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td data-label="" class="td-actions">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" class="action-btn" style="color:#D4AF37; text-decoration:none;" title="Edit">
                                        <i class="fas fa-pen" style="font-size:0.78rem;"></i>
                                    </a>
                                    <a href="categories.php?delete_id=<?php echo $cat['id']; ?>" class="action-btn" style="color:#ef4444; text-decoration:none;" onclick="return confirm('Delete this category?')" title="Delete">
                                        <i class="fas fa-trash" style="font-size:0.78rem;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
