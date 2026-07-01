<?php
// admin/quantity_discounts.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add/Edit Tier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tier'])) {
    $min_qty = (int)$_POST['min_qty'];
    $disc = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    if ($min_qty <= 1 || $disc <= 0 || $disc > 100) {
        $action_error = "Invalid inputs. Minimum quantity must be greater than 1, discount percentage must be between 1 and 100.";
    } else {
        // check duplicate minimum qty
        $stmt = $pdo->prepare("SELECT id FROM quantity_discounts WHERE min_qty = ?");
        $stmt->execute([$min_qty]);
        $exist = $stmt->fetch();
        
        if ($exist) {
            $stmt_u = $pdo->prepare("UPDATE quantity_discounts SET discount_percent = ?, status = ? WHERE min_qty = ?");
            $stmt_u->execute([$disc, $status, $min_qty]);
            $action_msg = "Discount tier updated.";
        } else {
            $stmt_i = $pdo->prepare("INSERT INTO quantity_discounts (product_id, min_qty, discount_percent, status) VALUES (NULL, ?, ?, ?)");
            $stmt_i->execute([$min_qty, $disc, $status]);
            $action_msg = "New discount tier created successfully.";
        }
    }
}

// Handle Delete Tier
if (isset($_GET['delete_id'])) {
    $t_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM quantity_discounts WHERE id = ?");
    $stmt->execute([$t_id]);
    $action_msg = "Discount tier removed.";
}

// Fetch all tiers
$stmt = $pdo->prepare("SELECT * FROM quantity_discounts ORDER BY min_qty ASC");
$stmt->execute();
$tiers = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Quantity Discounts</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage automatic volume-tier discount percents</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:30px; align-items:start;">
        <!-- Tiers list -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Active Quantity Tiers
            </h3>
            
            <?php if (empty($tiers)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No automatic volume tiers created yet.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Min. Quantity in Cart</th>
                            <th>Discount Percentage</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiers as $t): ?>
                            <tr>
                                <td><strong style="color:#fff; font-size:1.1rem;"><?php echo htmlspecialchars($t['min_qty']); ?>+ items</strong></td>
                                <td><strong style="color:var(--success-color); font-size:1.1rem;"><?php echo htmlspecialchars($t['discount_percent']); ?>% OFF</strong></td>
                                <td>
                                    <span class="admin-badge <?php echo $t['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $t['status'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="quantity_discounts.php?delete_id=<?php echo $t['id']; ?>" style="color:var(--danger-color); font-weight:700;" onclick="return confirm('Remove this volume tier?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add Tier Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Setup Discount Tier
            </h3>
            
            <form action="quantity_discounts.php" method="POST">
                <div class="form-group">
                    <label for="min_qty">Min Quantity *</label>
                    <input type="number" name="min_qty" id="min_qty" class="form-control" style="font-size:0.85rem; padding:8px;" min="2" required placeholder="e.g. 2">
                </div>
                <div class="form-group">
                    <label for="disc_val">Discount Value (%) *</label>
                    <input type="number" step="0.01" name="discount_percent" id="disc_val" class="form-control" style="font-size:0.85rem; padding:8px;" required placeholder="e.g. 10.00">
                </div>
                
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem;">
                        <input type="checkbox" name="status" value="1" checked style="accent-color:var(--gold-primary); width:18px; height:18px;">
                        <span>Enable Tier Immediately</span>
                    </label>
                </div>
                
                <button type="submit" name="save_tier" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                    Save Tier Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Admin Guide Panel -->
    <div class="glass-card" style="padding:25px; border-radius:8px; margin-top:35px; border-left:4px solid var(--gold-primary); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h3 style="font-size:1.1rem; color:var(--gold-primary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">
            <i class="fas fa-lightbulb" style="margin-right:8px;"></i> Volume-Tier Pricing Strategies
        </h3>
        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:12px;">
            Volume-based discounts incentivize customers to buy more units of any product in a single transaction. For example, buying **2+ items triggers a 10% discount** and **3+ items triggers a 15% discount**. These thresholds are computed automatically at checkout.
        </p>
        <ul style="font-size:0.8rem; color:var(--text-muted); padding-left:20px; line-height:1.7;">
            <li>Promote these offers inside the rotating announcement bar (e.g., "Buy 2 products, get 10% off automatically!").</li>
            <li>These tiers stack with coupons (`WOLF10`) unless restricted in the checkout settings, which helps boost customer conversions.</li>
            <li>Monitor customer cart metrics on the dashboard to adjust these tiers for peak performance.</li>
        </ul>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
