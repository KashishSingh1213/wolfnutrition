<?php
// admin/customers.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Status Toggle (block/unblock)
if (isset($_GET['toggle_id'])) {
    $c_id = (int)$_GET['toggle_id'];
    if ($c_id !== (int)$_SESSION['admin_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$c_id]);
        $action_msg = "Customer login authorization toggled.";
    }
}

// Customer Detail View
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    
    // Fetch customer info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch();
    
    if ($customer) {
        // Fetch customer addresses
        $stmt_addr = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
        $stmt_addr->execute([$user_id]);
        $addresses = $stmt_addr->fetchAll();
        
        // Fetch order history
        $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt_orders->execute([$user_id]);
        $orders = $stmt_orders->fetchAll();
    }
} else {
    // Fetch all customers along with their lifetime spend
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.phone, u.is_active, u.created_at, 
               COALESCE(SUM(o.total), 0) as total_spend, COUNT(o.id) as total_orders
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
        WHERE u.role = 'customer'
        GROUP BY u.id
        ORDER BY total_spend DESC, u.created_at DESC
    ");
    $stmt->execute();
    $customers = $stmt->fetchAll();
}
?>

    <?php if (isset($_GET['user_id']) && isset($customer) && $customer): ?>
        <!-- Customer Detail View -->
        <div style="margin-bottom:15px;">
            <a href="customers.php" style="color:rgba(255,255,255,0.45); font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">
                <i class="fas fa-arrow-left" style="font-size:0.75rem;"></i> Back to Customer List
            </a>
        </div>

        <!-- Page Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:35px;">
            <div>
                <h2 style="font-size:1.8rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">Customer Profile</h2>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">Account details and order history</p>
            </div>
            <span class="admin-badge <?php echo $customer['is_active'] ? 'badge-completed' : 'badge-failed'; ?>" style="font-size:0.7rem; padding:6px 14px;">
                <?php echo $customer['is_active'] ? 'Active' : 'Blocked'; ?>
            </span>
        </div>

        <?php if ($action_msg): ?>
            <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
                <span style="color:#4ade80; font-weight:600; font-size:0.88rem;"><?php echo htmlspecialchars($action_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Customer Info Card -->
        <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden; margin-bottom:30px;">
            <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06);">
                <h3 style="font-size:0.95rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                    <i class="fas fa-user" style="margin-right:8px;"></i>Personal Information
                </h3>
            </div>
            <div style="padding:25px 28px;">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:25px;">
                    <div>
                        <div style="font-size:0.65rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Full Name</div>
                        <div style="color:#fff; font-weight:700; font-size:1.05rem; font-family:var(--font-heading);"><?php echo htmlspecialchars($customer['name']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.65rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Email Address</div>
                        <div style="color:rgba(255,255,255,0.8); font-weight:600; font-size:0.92rem;"><?php echo htmlspecialchars($customer['email']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.65rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Phone Number</div>
                        <div style="color:rgba(255,255,255,0.8); font-weight:600; font-size:0.92rem;"><?php echo htmlspecialchars($customer['phone']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.65rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Joined Date</div>
                        <div style="color:rgba(255,255,255,0.8); font-weight:600; font-size:0.92rem;"><?php echo date('d M Y', strtotime($customer['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses & Order History Grid -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; align-items:start;">

            <!-- Saved Addresses -->
            <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
                <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="font-size:0.95rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                        <i class="fas fa-location-dot" style="margin-right:8px;"></i>Saved Addresses
                    </h3>
                    <span style="font-size:0.7rem; color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.04); padding:3px 10px; border-radius:20px; border:1px solid rgba(255,255,255,0.06);">
                        <?php echo count($addresses); ?> saved
                    </span>
                </div>

                <?php if (empty($addresses)): ?>
                    <p style="color:rgba(255,255,255,0.45); text-align:center; padding:40px 20px;">
                        <i class="fas fa-map-marker-alt" style="font-size:1.5rem; display:block; margin-bottom:8px; color:rgba(255,255,255,0.15);"></i>
                        No saved addresses
                    </p>
                <?php else: ?>
                    <div style="padding:15px 28px;">
                        <?php foreach ($addresses as $index => $addr): ?>
                            <div style="padding:18px; border:1px solid <?php echo $addr['is_default'] ? 'rgba(212,175,55,0.3)' : 'rgba(255,255,255,0.06)'; ?>; border-radius:10px; position:relative; <?php echo $index > 0 ? 'margin-top:12px;' : ''; ?> background:<?php echo $addr['is_default'] ? 'rgba(212,175,55,0.03)' : 'transparent'; ?>;">
                                <?php if ($addr['is_default']): ?>
                                    <span class="admin-badge badge-completed" style="position:absolute; top:12px; right:12px; font-size:0.6rem; background:rgba(212,175,55,0.1); color:#D4AF37; border:1px solid rgba(212,175,55,0.2);">Default</span>
                                <?php endif; ?>
                                <div style="color:#fff; font-weight:700; margin-bottom:6px; font-size:0.92rem;"><?php echo htmlspecialchars($addr['name']); ?></div>
                                <div style="font-size:0.82rem; color:rgba(255,255,255,0.6); line-height:1.6;">
                                    <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                    <?php if (!empty($addr['address_line2'])): ?>
                                        <?php echo htmlspecialchars($addr['address_line2']); ?><br>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($addr['city']) . ', ' . htmlspecialchars($addr['state']) . ' - ' . htmlspecialchars($addr['pincode']); ?><br>
                                    <?php echo htmlspecialchars($addr['country']); ?>
                                </div>
                                <div style="font-size:0.78rem; color:rgba(255,255,255,0.45); margin-top:8px;">
                                    <i class="fas fa-phone" style="margin-right:5px;"></i><?php echo htmlspecialchars($addr['phone']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order History -->
            <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
                <div style="padding:20px 28px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="font-size:0.95rem; text-transform:uppercase; letter-spacing:1px; color:#D4AF37; margin:0;">
                        <i class="fas fa-receipt" style="margin-right:8px;"></i>Order History
                    </h3>
                    <span style="font-size:0.7rem; color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.04); padding:3px 10px; border-radius:20px; border:1px solid rgba(255,255,255,0.06);">
                        <?php echo count($orders); ?> orders
                    </span>
                </div>

                <?php if (empty($orders)): ?>
                    <p style="color:rgba(255,255,255,0.45); text-align:center; padding:40px 20px;">
                        <i class="fas fa-shopping-bag" style="font-size:1.5rem; display:block; margin-bottom:8px; color:rgba(255,255,255,0.15);"></i>
                        No orders yet
                    </p>
                <?php else: ?>
                    <div style="padding:15px 28px;">
                        <?php foreach ($orders as $index => $order): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:16px; border:1px solid rgba(255,255,255,0.06); border-radius:10px; <?php echo $index > 0 ? 'margin-top:10px;' : ''; ?> transition:background 0.2s; cursor:default;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                                <div>
                                    <div style="color:#fff; font-weight:700; font-size:0.92rem; font-family:var(--font-heading);">
                                        #<?php echo htmlspecialchars($order['order_number']); ?>
                                    </div>
                                    <div style="font-size:0.75rem; color:rgba(255,255,255,0.45); margin-top:4px;">
                                        <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="color:#D4AF37; font-weight:700; font-size:0.95rem;">
                                        ₹<?php echo number_format($order['total'], 2); ?>
                                    </div>
                                    <?php 
                                    $status = $order['status'] ?? $order['shipping_status'];
                                    $badge_style = match($status) {
                                        'delivered' => 'background:rgba(74,222,128,0.1); color:#4ade80; border:1px solid rgba(74,222,128,0.2);',
                                        'cancelled' => 'background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);',
                                        'shipped' => 'background:rgba(96,165,250,0.1); color:#60a5fa; border:1px solid rgba(96,165,250,0.2);',
                                        default => 'background:rgba(212,175,55,0.1); color:#D4AF37; border:1px solid rgba(212,175,55,0.2);',
                                    };
                                    ?>
                                    <span class="admin-badge" style="font-size:0.62rem; margin-top:6px; <?php echo $badge_style; ?>">
                                        <?php echo ucfirst(htmlspecialchars($status)); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    <?php else: ?>
        <!-- Customer List View -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:35px;">
            <div>
                <h2 style="font-size:1.8rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">Customer Management</h2>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); margin:0;">View profiles and lifetime spend records</p>
            </div>
            <div style="font-size:0.8rem; color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.03); padding:6px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.06);">
                <?php echo count($customers); ?> customers
            </div>
        </div>

        <?php if ($action_msg): ?>
            <div style="background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15); border-radius:10px; padding:14px 20px; margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
                <span style="color:#4ade80; font-weight:600; font-size:0.88rem;"><?php echo htmlspecialchars($action_msg); ?></span>
            </div>
        <?php endif; ?>

        <div class="glass-card" style="padding:0; border-radius:12px; overflow:hidden;">
            <?php if (empty($customers)): ?>
                <p style="color:rgba(255,255,255,0.45); text-align:center; padding:50px 20px;">
                    <i class="fas fa-users" style="font-size:2rem; display:block; margin-bottom:12px; color:rgba(255,255,255,0.15);"></i>
                    No customers registered in the database.
                </p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="admin-table" style="margin:0; border:none; border-radius:0;">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Orders</th>
                                <th>Lifetime Spend</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#fff; font-size:0.92rem; font-family:var(--font-heading);"><?php echo htmlspecialchars($c['name']); ?></div>
                                    </td>
                                    <td style="color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td style="color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars($c['phone']); ?></td>
                                    <td style="color:rgba(255,255,255,0.5);"><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                                    <td style="text-align:center;">
                                        <span style="color:#fff; font-weight:700; font-size:0.92rem;"><?php echo $c['total_orders']; ?></span>
                                    </td>
                                    <td style="font-weight:700; color:#D4AF37;">₹<?php echo number_format($c['total_spend'], 2); ?></td>
                                    <td>
                                        <?php if ($c['is_active']): ?>
                                            <span class="admin-badge badge-completed" style="background:rgba(74,222,128,0.1); color:#4ade80; border:1px solid rgba(74,222,128,0.2);">Active</span>
                                        <?php else: ?>
                                            <span class="admin-badge badge-failed" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);">Blocked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:8px;">
                                            <a href="customers.php?user_id=<?php echo $c['id']; ?>" class="btn-outline-gold" style="padding:5px 12px; font-size:0.72rem; border-radius:6px;">View</a>
                                            <a href="customers.php?toggle_id=<?php echo $c['id']; ?>" class="btn-outline-gold" style="padding:5px 12px; font-size:0.72rem; border-radius:6px; <?php echo !$c['is_active'] ? 'color:#4ade80; border-color:rgba(74,222,128,0.3);' : 'color:#ef4444; border-color:rgba(239,68,68,0.3);'; ?>">
                                                <?php echo $c['is_active'] ? 'Block' : 'Authorize'; ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>