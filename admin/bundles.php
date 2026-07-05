<?php
// admin/bundles.php — Combo List (Enhanced UI)
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt_d = $pdo->prepare("DELETE FROM bundles WHERE id = ?");
    $stmt_d->execute([$del_id]);
    header("Location: bundles.php?msg=deleted");
    exit();
}

if (isset($_GET['msg'])) {
    $msgs = ['deleted' => 'Combo deleted successfully.'];
    $action_msg = $msgs[$_GET['msg']] ?? '';
}

// Stats
$stmt_stats = $pdo->prepare("SELECT COUNT(id) as total, SUM(status) as active FROM bundles");
$stmt_stats->execute();
$stats = $stmt_stats->fetch();

// Fetch all bundles
$stmt = $pdo->prepare("SELECT * FROM bundles ORDER BY display_order ASC");
$stmt->execute();
$bundles = $stmt->fetchAll();

// Calculate total items & savings across all bundles
$total_items_all = 0;
$total_savings_all = 0;
$bundle_details = [];
foreach ($bundles as $b) {
    $stmt_i = $pdo->prepare("
        SELECT bi.*, p.name as p_name, p.image_url, pv.size_capsules, pv.sale_price
        FROM bundle_items bi
        JOIN products p ON bi.product_id = p.id
        JOIN product_variants pv ON bi.variant_id = pv.id
        WHERE bi.bundle_id = ?
    ");
    $stmt_i->execute([$b['id']]);
    $items = $stmt_i->fetchAll();
    
    $individual_total = 0;
    foreach ($items as $item) $individual_total += $item['sale_price'];
    $savings = $individual_total - $b['combo_price'];
    
    $bundle_details[$b['id']] = [
        'items' => $items,
        'individual_total' => $individual_total,
        'savings' => $savings,
        'savings_pct' => $individual_total > 0 ? round((1 - $b['combo_price'] / $individual_total) * 100) : 0
    ];
    
    $total_items_all += count($items);
    $total_savings_all += max(0, $savings);
}
?>

    <!-- Page Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Combos</h2>
            <p style="font-size:0.85rem; color:var(--text-muted);">Manage combo packs & boost average order value</p>
        </div>
        <a href="bundle_add.php" class="btn-gold" style="padding:10px 20px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> Create Combo
        </a>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="admin-card-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom:35px;">
        <div class="admin-card glass-card">
            <h4>Total Combos</h4>
            <div class="val"><?php echo $stats['total'] ?? 0; ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Active Combos</h4>
            <div class="val" style="color:var(--success-color);"><?php echo $stats['active'] ?? 0; ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Total Combo Items</h4>
            <div class="val"><?php echo $total_items_all; ?></div>
        </div>
        <div class="admin-card glass-card">
            <h4>Total Customer Savings</h4>
            <div class="val" style="color:var(--success-color);">₹<?php echo number_format($total_savings_all, 0); ?></div>
        </div>
    </div>

    <?php if (empty($bundles)): ?>
        <!-- Empty State -->
        <div class="glass-card" style="padding:60px 40px; border-radius:12px; text-align:center; border:2px dashed rgba(212,175,55,0.2);">
            <div style="width:80px; height:80px; background:rgba(212,175,55,0.08); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <i class="fas fa-cubes" style="font-size:2rem; color:var(--gold-primary);"></i>
            </div>
            <h3 style="font-size:1.3rem; color:#fff; margin-bottom:10px;">No Combos Yet</h3>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:25px; max-width:400px; margin-left:auto; margin-right:auto;">
                Create your first combo to combine products at a special price and increase your average order value.
            </p>
            <a href="bundle_add.php" class="btn-gold" style="padding:12px 30px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.9rem;">
                <i class="fas fa-plus"></i> Create Your First Combo
            </a>
        </div>

    <?php else: ?>
        
        <!-- Combos Grid -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(480px, 1fr)); gap:25px;">
            <?php foreach ($bundles as $b): 
                $d = $bundle_details[$b['id']];
                $items = $d['items'];
                $savings = $d['savings'];
                $savings_pct = $d['savings_pct'];
            ?>
                <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden; transition:all 0.3s; position:relative;">
                    
                    <!-- Banner / Header -->
                    <div style="position:relative; height:180px; overflow:hidden; background:#0a0e13;">
                        <?php if ($b['banner_image']): ?>
                            <img src="../<?php echo htmlspecialchars($b['banner_image']); ?>" alt="" 
                                 style="width:100%; height:100%; object-fit:contain; padding:10px;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; background:linear-gradient(135deg, rgba(212,175,55,0.15) 0%, rgba(212,175,55,0.03) 100%); display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-cubes" style="font-size:3rem; color:rgba(212,175,55,0.3);"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Overlay badges -->
                        <div style="position:absolute; top:12px; left:12px; display:flex; gap:6px;">
                            <span class="admin-badge <?php echo $b['status'] ? 'badge-completed' : 'badge-failed'; ?>" style="backdrop-filter:blur(4px);">
                                <?php echo $b['status'] ? '● Active' : '● Inactive'; ?>
                            </span>
                        </div>
                        
                        <?php if ($savings > 0): ?>
                            <div style="position:absolute; top:12px; right:12px; background:var(--success-color); color:#000; font-weight:800; font-size:0.75rem; padding:4px 10px; border-radius:20px; backdrop-filter:blur(4px);">
                                SAVE <?php echo $savings_pct; ?>%
                            </div>
                        <?php endif; ?>
                        
                        <!-- Order badge -->
                        <div style="position:absolute; bottom:12px; right:12px; background:rgba(0,0,0,0.7); color:#fff; font-size:0.7rem; padding:3px 8px; border-radius:4px; backdrop-filter:blur(4px);">
                            Order: #<?php echo $b['display_order']; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div style="padding:20px;">
                        <!-- Title & Actions -->
                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                            <div style="flex:1;">
                                <h3 style="font-size:1.1rem; color:#fff; margin-bottom:4px; line-height:1.3;">
                                    <?php echo htmlspecialchars($b['title']); ?>
                                </h3>
                                <p style="font-size:0.8rem; color:var(--text-muted); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                    <?php echo htmlspecialchars($b['description']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Price Row -->
                        <div style="display:flex; align-items:baseline; gap:12px; margin-bottom:15px; padding:12px; background:rgba(212,175,55,0.05); border-radius:8px; border:1px solid rgba(212,175,55,0.1);">
                            <div>
                                <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:2px;">Combo Price</div>
                                <div style="font-size:1.5rem; font-weight:800; color:var(--gold-primary);">₹<?php echo number_format($b['combo_price'], 0); ?></div>
                            </div>
                            <?php if ($d['individual_total'] > 0): ?>
                                <div style="flex:1; text-align:right;">
                                    <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:2px;">Individual Total</div>
                                    <div style="font-size:0.95rem; color:var(--text-muted); text-decoration:line-through;">₹<?php echo number_format($d['individual_total'], 0); ?></div>
                                </div>
                                <?php if ($savings > 0): ?>
                                    <div style="text-align:right;">
                                        <div style="font-size:0.7rem; color:var(--success-color); text-transform:uppercase; margin-bottom:2px;">You Save</div>
                                        <div style="font-size:1rem; font-weight:700; color:var(--success-color);">₹<?php echo number_format($savings, 0); ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Items List -->
                        <?php if (!empty($items)): ?>
                            <div style="margin-bottom:15px;">
                                <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px; letter-spacing:0.5px;">
                                    <?php echo count($items); ?> Product<?php echo count($items) > 1 ? 's' : '' ?> in Combo
                                </div>
                                <?php foreach ($items as $item): ?>
                                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px dashed rgba(255,255,255,0.05);">
                                        <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" alt="" 
                                             style="width:35px; height:35px; object-fit:contain; background:#111; border-radius:4px; border:1px solid rgba(255,255,255,0.1);">
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-size:0.85rem; color:#fff; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <?php echo htmlspecialchars($item['p_name']); ?>
                                            </div>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">
                                                <?php echo htmlspecialchars($item['size_capsules']); ?>
                                            </div>
                                        </div>
                                        <div style="font-size:0.85rem; color:var(--text-muted);">₹<?php echo number_format($item['sale_price'], 0); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align:center; padding:15px; color:var(--text-muted); font-size:0.85rem; background:rgba(255,255,255,0.02); border-radius:6px; margin-bottom:15px;">
                                <i class="fas fa-exclamation-triangle" style="color:var(--gold-primary); margin-right:5px;"></i> No items added yet
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div style="display:flex; gap:8px;">
                            <a href="bundle_edit.php?id=<?php echo $b['id']; ?>" 
                               style="flex:1; padding:10px; text-align:center; background:rgba(212,175,55,0.1); color:var(--gold-primary); border:1px solid rgba(212,175,55,0.2); border-radius:6px; text-decoration:none; font-size:0.85rem; font-weight:600; transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:6px;">
                                <i class="fas fa-edit"></i> Edit & Manage Items
                            </a>
                            <a href="bundles.php?delete_id=<?php echo $b['id']; ?>" 
                               onclick="return confirm('Delete this combo? All items will be removed.');"
                               style="padding:10px 15px; background:rgba(220,53,69,0.08); color:#dc3545; border:1px solid rgba(220,53,69,0.2); border-radius:6px; text-decoration:none; font-size:0.85rem; transition:all 0.2s; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
