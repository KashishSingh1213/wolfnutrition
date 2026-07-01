<?php
// admin/customers.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Status Toggle (block/unblock)
if (isset($_GET['toggle_id'])) {
    $c_id = (int)$_GET['toggle_id'];
    // Avoid blocking yourself
    if ($c_id !== (int)$_SESSION['admin_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$c_id]);
        $action_msg = "Customer login authorization toggled.";
    }
}

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
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Customer Management</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">View profiles and lifetime spend records</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
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
                                <a href="customers.php?toggle_id=<?php echo $c['id']; ?>" class="btn-outline-gold" style="padding:4px 10px; font-size:0.75rem;">
                                    <?php echo $c['is_active'] ? 'Block User' : 'Authorize'; ?>
                                </a>
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
