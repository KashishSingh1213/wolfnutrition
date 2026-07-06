<?php
// admin/quantity_discounts.php — Discount Tiers List
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $t_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM quantity_discounts WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Discount tier removed.";
}

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $t_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE quantity_discounts SET status = NOT status WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Tier status toggled.";
}

// Fetch all tiers with product name
$stmt = $pdo->prepare("
    SELECT qd.*, p.name AS product_name
    FROM quantity_discounts qd
    LEFT JOIN products p ON qd.product_id = p.id
    ORDER BY qd.min_qty ASC
");
$stmt->execute();
$tiers = $stmt->fetchAll();

$total_count = count($tiers);
$active_count = 0;
$inactive_count = 0;
$highest_disc = 0;
foreach ($tiers as $t) {
    if ($t['status']) $active_count++; else $inactive_count++;
    if ($t['discount_percent'] > $highest_disc) $highest_disc = $t['discount_percent'];
}
?>

<style>
.qd-tier-row{display:grid; grid-template-columns:80px 1fr 140px 130px 120px; align-items:center; gap:0; padding:0; border-bottom:1px solid rgba(255,255,255,0.04); transition:background 0.2s;}
.qd-tier-row:hover{background:rgba(212,175,55,0.03);}
.qd-tier-row:last-child{border-bottom:none;}
.qd-disc-badge{width:64px; height:64px; border-radius:14px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1px; font-weight:800;}
.qd-pill{display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:20px; font-size:0.72rem; font-weight:600; white-space:nowrap;}
.qd-action-btn{width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:0.78rem; text-decoration:none; transition:all 0.2s; border:1px solid transparent;}
.qd-action-btn:hover{transform:translateY(-1px);}
.qd-action-btn.edit{background:rgba(212,175,55,0.08); border-color:rgba(212,175,55,0.15); color:#D4AF37;}
.qd-action-btn.toggle{background:rgba(74,222,128,0.08); border-color:rgba(74,222,128,0.15); color:#4ade80;}
.qd-action-btn.toggle.off{background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.08); color:rgba(255,255,255,0.35);}
.qd-action-btn.delete{background:rgba(239,68,68,0.06); border-color:rgba(239,68,68,0.12); color:#ef4444;}
.qd-action-btn.delete:hover{background:rgba(239,68,68,0.12);}

/* ── Responsive: Tablet ── */
@media (max-width: 1024px) {
    .qd-page-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px;
    }
    .qd-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .qd-tier-row {
        grid-template-columns: 70px 1fr 120px !important;
    }
    .qd-tier-row .qd-col-product,
    .qd-tier-row .qd-col-status {
        display: none !important;
    }
    .qd-col-label-product,
    .qd-col-label-status {
        display: none !important;
    }
}

/* ── Responsive: Mobile ── */
@media (max-width: 768px) {
    .qd-page-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px;
    }
    .qd-stats-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
    }
    /* Hide column labels */
    .qd-col-labels {
        display: none !important;
    }
    /* Tier rows become cards */
    .qd-tier-row {
        grid-template-columns: 1fr !important;
        padding: 16px !important;
        gap: 10px !important;
        border-radius: 10px;
        margin: 0 16px 10px 16px;
        border: 1px solid rgba(255,255,255,0.06);
        background: rgba(18,18,18,0.4);
    }
    .qd-tier-row:first-of-type {
        margin-top: 10px;
    }
    /* Show hidden columns again but stacked */
    .qd-tier-row .qd-col-product,
    .qd-tier-row .qd-col-status {
        display: flex !important;
    }
    .qd-tier-row .qd-col-status {
        justify-content: flex-start !important;
    }
    /* Discount badge centered */
    .qd-tier-row > div:first-child {
        display: flex;
        justify-content: center;
    }
    /* Actions row */
    .qd-tier-row .qd-col-actions {
        justify-content: flex-start !important;
        padding-top: 8px;
        border-top: 1px solid rgba(255,255,255,0.04);
    }
    .qd-tier-row .qd-col-actions .qd-action-btn {
        width: 38px !important;
        height: 38px !important;
    }
    .qd-tier-row .qd-col-rule {
        padding-left: 0 !important;
    }
    .qd-bottom-tip {
        flex-direction: column !important;
        text-align: center;
        gap: 8px;
    }
}
</style>

<!-- Page Header -->
<div class="qd-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
    <div>
        <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:5px;">Quantity Discounts</h2>
        <p style="font-size:0.85rem; color:var(--text-muted);">Manage automatic volume-tier pricing</p>
    </div>
    <a href="quantity_discount_add.php" class="btn-gold" style="padding:11px 22px; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; border-radius:10px;">
        <i class="fas fa-plus"></i> Add Tier
    </a>
</div>

<?php if ($action_msg): ?>
    <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
        <i class="fas fa-check-circle" style="color:#4ade80;"></i>
        <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
    </div>
<?php endif; ?>

<!-- Stats Row -->
<div class="qd-stats-grid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:14px; margin-bottom:28px;">
    <div class="glass-card" style="padding:20px; text-align:center; border:1px solid rgba(212,175,55,0.1);">
        <div style="width:42px; height:42px; border-radius:12px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
            <i class="fas fa-layer-group" style="color:#D4AF37; font-size:1rem;"></i>
        </div>
        <div style="font-size:1.8rem; font-weight:800; color:#fff; line-height:1; margin-bottom:4px;"><?php echo $total_count; ?></div>
        <div style="font-size:0.68rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.8px;">Total Tiers</div>
    </div>
    <div class="glass-card" style="padding:20px; text-align:center; border:1px solid rgba(74,222,128,0.1);">
        <div style="width:42px; height:42px; border-radius:12px; background:rgba(74,222,128,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
            <i class="fas fa-toggle-on" style="color:#4ade80; font-size:1rem;"></i>
        </div>
        <div style="font-size:1.8rem; font-weight:800; color:#fff; line-height:1; margin-bottom:4px;"><?php echo $active_count; ?></div>
        <div style="font-size:0.68rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.8px;">Active</div>
    </div>
    <div class="glass-card" style="padding:20px; text-align:center; border:1px solid rgba(239,68,68,0.1);">
        <div style="width:42px; height:42px; border-radius:12px; background:rgba(239,68,68,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
            <i class="fas fa-toggle-off" style="color:#ef4444; font-size:1rem;"></i>
        </div>
        <div style="font-size:1.8rem; font-weight:800; color:#fff; line-height:1; margin-bottom:4px;"><?php echo $inactive_count; ?></div>
        <div style="font-size:0.68rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.8px;">Inactive</div>
    </div>
    <div class="glass-card" style="padding:20px; text-align:center; border:1px solid rgba(168,85,247,0.1);">
        <div style="width:42px; height:42px; border-radius:12px; background:rgba(168,85,247,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
            <i class="fas fa-fire" style="color:#a855f7; font-size:1rem;"></i>
        </div>
        <div style="font-size:1.8rem; font-weight:800; color:#fff; line-height:1; margin-bottom:4px;"><?php echo $highest_disc; ?>%</div>
        <div style="font-size:0.68rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.8px;">Highest Discount</div>
    </div>
</div>

<?php if (empty($tiers)): ?>
    <!-- Empty State -->
    <div class="glass-card" style="padding:70px 40px; text-align:center; border:2px dashed rgba(212,175,55,0.15);">
        <div style="width:80px; height:80px; background:rgba(212,175,55,0.06); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <i class="fas fa-layer-group" style="font-size:2rem; color:rgba(212,175,55,0.3);"></i>
        </div>
        <h3 style="font-size:1.3rem; color:#fff; margin-bottom:8px;">No Discount Tiers Yet</h3>
        <p style="color:var(--text-muted); font-size:0.88rem; margin-bottom:24px; max-width:400px; margin-left:auto; margin-right:auto;">
            Create volume-based pricing tiers to incentivize customers to buy more and boost your average order value.
        </p>
        <a href="quantity_discount_add.php" class="btn-gold" style="padding:13px 30px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.9rem; border-radius:10px;">
            <i class="fas fa-plus"></i> Create First Tier
        </a>
    </div>

<?php else: ?>
    <!-- Tiers Table Card -->
    <div class="glass-card" style="padding:0; overflow:hidden;">
        <!-- Table Header -->
        <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(212,175,55,0.08); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-percentage" style="color:#D4AF37; font-size:0.85rem;"></i>
                </div>
                <div>
                    <h3 style="font-size:0.95rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px; margin:0;">All Discount Tiers</h3>
                    <p style="font-size:0.72rem; color:rgba(255,255,255,0.4); margin:2px 0 0 0;"><?php echo $total_count; ?> tier<?php echo $total_count !== 1 ? 's' : '' ?> configured</p>
                </div>
            </div>
        </div>

        <!-- Column Labels -->
        <div class="qd-tier-row qd-col-labels" style="padding:12px 28px; background:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.06);">
            <div style="font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:1px;">Discount</div>
            <div style="font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:1px;">Tier Rule</div>
            <div class="qd-col-label-product" style="font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:1px;">Product</div>
            <div class="qd-col-label-status" style="font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:1px;">Status</div>
            <div style="font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:1px; text-align:right;">Actions</div>
        </div>

        <!-- Tier Rows -->
        <?php foreach ($tiers as $t): ?>
            <div class="qd-tier-row" style="padding:14px 28px; <?php echo !$t['status'] ? 'opacity:0.45;' : ''; ?>">
                <!-- Discount Badge -->
                <div>
                    <div class="qd-disc-badge" style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.15);">
                        <span style="font-size:1.1rem; color:#4ade80; line-height:1;"><?php echo $t['discount_percent']; ?>%</span>
                        <span style="font-size:0.5rem; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:0.5px;">OFF</span>
                    </div>
                </div>

                <!-- Tier Rule -->
                <div class="qd-col-rule">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center;">
                            <span style="font-size:0.85rem; font-weight:800; color:#D4AF37;"><?php echo $t['min_qty']; ?>+</span>
                        </div>
                        <div>
                            <div style="font-size:0.88rem; font-weight:700; color:#fff;">Buy <?php echo $t['min_qty']; ?> or more items</div>
                            <div style="font-size:0.72rem; color:rgba(255,255,255,0.35);">Auto-applied at checkout</div>
                        </div>
                    </div>
                </div>

                <!-- Product -->
                <div class="qd-col-product">
                    <?php if ($t['product_id']): ?>
                        <div class="qd-pill" style="background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.15); color:#a855f7;">
                            <i class="fas fa-box" style="font-size:0.65rem;"></i>
                            <span style="max-width:100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($t['product_name']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="qd-pill" style="background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.15); color:#3b82f6;">
                            <i class="fas fa-store" style="font-size:0.65rem;"></i>
                            Store-wide
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Status -->
                <div class="qd-col-status">
                    <?php if ($t['status']): ?>
                        <div class="qd-pill" style="background:rgba(74,222,128,0.1); border:1px solid rgba(74,222,128,0.15); color:#4ade80;">
                            <i class="fas fa-circle" style="font-size:0.4rem;"></i> Active
                        </div>
                    <?php else: ?>
                        <div class="qd-pill" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.4);">
                            <i class="fas fa-circle" style="font-size:0.4rem;"></i> Inactive
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="qd-col-actions" style="display:flex; gap:6px; justify-content:flex-end;">
                    <a href="quantity_discount_edit.php?id=<?php echo $t['id']; ?>" class="qd-action-btn edit" title="Edit">
                        <i class="fas fa-pen"></i>
                    </a>
                    <a href="quantity_discounts.php?toggle_id=<?php echo $t['id']; ?>" class="qd-action-btn toggle <?php echo !$t['status'] ? 'off' : ''; ?>" title="<?php echo $t['status'] ? 'Disable' : 'Enable'; ?>">
                        <i class="fas fa-<?php echo $t['status'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                    </a>
                    <a href="quantity_discounts.php?delete_id=<?php echo $t['id']; ?>" class="qd-action-btn delete" title="Delete" onclick="return confirm('Remove this discount tier?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Tip -->
    <div class="qd-bottom-tip" style="margin-top:20px; padding:16px 20px; border-radius:10px; background:rgba(212,175,55,0.03); border:1px solid rgba(212,175,55,0.08); display:flex; align-items:center; gap:12px;">
        <i class="fas fa-lightbulb" style="color:#D4AF37; font-size:0.9rem;"></i>
        <p style="font-size:0.8rem; color:rgba(255,255,255,0.4); margin:0;">
            Volume discounts auto-apply when cart quantity meets the minimum. Assign to a specific product or leave blank for store-wide offers.
        </p>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
