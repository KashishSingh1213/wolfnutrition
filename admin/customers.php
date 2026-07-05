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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <a href="customers.php" style="color:var(--gold-muted); font-size:0.85rem; text-decoration:none;">← Back to Customer List</a>
                <h2 style="font-size:1.8rem; text-transform:uppercase; margin-top:5px;">Customer Profile</h2>
            </div>
            <div>
                <span class="admin-badge <?php echo $customer['is_active'] ? 'badge-completed' : 'badge-failed'; ?>">
                    <?php echo $customer['is_active'] ? 'Active' : 'Blocked'; ?>
                </span>
            </div>
        </div>

        <?php if ($action_msg): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
                ✅ <?php echo htmlspecialchars($action_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Customer Info Card -->
        <div class="glass-card" style="padding:25px; border-radius:6px; margin-bottom:25px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:20px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Personal Information
            </h3>
            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:20px;">
                <div>
                    <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:5px;">Full Name</label>
                    <span style="color:#fff; font-weight:700; font-size:1.05rem;"><?php echo htmlspecialchars($customer['name']); ?></span>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:5px;">Email Address</label>
                    <span style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($customer['email']); ?></span>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:5px;">Phone Number</label>
                    <span style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($customer['phone']); ?></span>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:5px;">Joined Date</label>
                    <span style="color:#fff; font-weight:600;"><?php echo date('d M Y', strtotime($customer['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Addresses & Orders Grid -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; align-items:start;">
            
            <!-- Saved Addresses -->
            <div class="glass-card" style="padding:25px; border-radius:6px;">
                <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    Saved Addresses (<?php echo count($addresses); ?>)
                </h3>
                
                <?php if (empty($addresses)): ?>
                    <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No saved addresses.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <?php foreach ($addresses as $addr): ?>
                            <div style="padding:15px; border:1px solid <?php echo $addr['is_default'] ? 'var(--gold-primary)' : 'var(--border-color)'; ?>; border-radius:6px; position:relative;">
                                <?php if ($addr['is_default']): ?>
                                    <span class="admin-badge badge-completed" style="position:absolute; top:10px; right:10px; font-size:0.65rem;">Default</span>
                                <?php endif; ?>
                                <div style="color:#fff; font-weight:600; margin-bottom:5px;"><?php echo htmlspecialchars($addr['name']); ?></div>
                                <div style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5;">
                                    <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                    <?php if (!empty($addr['address_line2'])): ?>
                                        <?php echo htmlspecialchars($addr['address_line2']); ?><br>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($addr['city']) . ', ' . htmlspecialchars($addr['state']) . ' - ' . htmlspecialchars($addr['pincode']); ?><br>
                                    <?php echo htmlspecialchars($addr['country']); ?>
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:8px;">
                                    <i class="fas fa-phone" style="margin-right:5px;"></i><?php echo htmlspecialchars($addr['phone']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order History -->
            <div class="glass-card" style="padding:25px; border-radius:6px;">
                <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    Order History (<?php echo count($orders); ?>)
                </h3>
                
                <?php if (empty($orders)): ?>
                    <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No orders yet.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php foreach ($orders as $order): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid var(--border-color); border-radius:6px;">
                                <div>
                                    <div style="color:#fff; font-weight:700; font-size:0.95rem;">
                                        Order #<?php echo htmlspecialchars($order['order_number']); ?>
                                    </div>
                                    <div style="font-size:0.8rem; color:var(--text-muted); margin-top:3px;">
                                        <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="color:var(--gold-primary); font-weight:700; font-size:0.95rem;">
                                        ₹<?php echo number_format($order['total'], 2); ?>
                                    </div>
                                    <span class="admin-badge <?php 
                                        echo match($order['status']) {
                                            'delivered' => 'badge-completed',
                                            'cancelled' => 'badge-failed',
                                            default => 'badge-pending'
                                        }; 
                                    ?>" style="font-size:0.65rem;">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-size:1.8rem; text-transform:uppercase;">Customer Management</h2>
            <div style="font-size:0.85rem; color:var(--text-muted);">View profiles and lifetime spend records</div>
        </div>

        <?php if ($action_msg): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:25px;">
                ✅ <?php echo htmlspecialchars($action_msg); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <?php if (empty($customers)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No customers registered in the database.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Joined Date</th>
                            <th>Orders</th>
                            <th>Lifetime Spend</th>
                            <th>Authorization</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><strong style="color:#fff; font-size:1rem;"><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['phone']); ?></td>
                                <td><?php echo date('d-M-Y', strtotime($c['created_at'])); ?></td>
                                <td><?php echo $c['total_orders']; ?> orders</td>
                                <td style="font-weight:700; color:var(--gold-primary);">₹<?php echo number_format($c['total_spend'], 2); ?></td>
                                <td>
                                    <span class="admin-badge <?php echo $c['is_active'] ? 'badge-completed' : 'badge-failed'; ?>">
                                        <?php echo $c['is_active'] ? 'Authorized' : 'Blocked'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:10px;">
                                        <a href="customers.php?user_id=<?php echo $c['id']; ?>" class="btn-outline-gold" style="padding:4px 10px; font-size:0.75rem;">
                                            View Profile
                                        </a>
                                        <a href="customers.php?toggle_id=<?php echo $c['id']; ?>" class="btn-outline-gold" style="padding:4px 10px; font-size:0.75rem;">
                                            <?php echo $c['is_active'] ? 'Block User' : 'Authorize'; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
