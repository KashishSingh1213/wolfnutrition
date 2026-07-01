<?php
// my-account.php
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user = get_logged_in_user();

$action_success = '';
$action_error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Update Profile
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        if (empty($name) || empty($email) || empty($phone)) {
            $action_error = "Profile details cannot be blank.";
        } else {
            // check email duplicates
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $action_error = "This email is registered to another account.";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
                $stmt->execute([$phone, $user['id']]);
                if ($stmt->fetch()) {
                    $action_error = "This phone number is registered to another account.";
                } else {
                    $stmt_u = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                    $stmt_u->execute([$name, $email, $phone, $user['id']]);
                    $action_success = "Profile details updated successfully.";
                    // Refresh user data
                    $user = get_logged_in_user();
                }
            }
        }
    }

    // 2. Change Password
    if (isset($_POST['change_password'])) {
        $current = trim($_POST['current_password']);
        $new = trim($_POST['new_password']);
        $confirm = trim($_POST['confirm_password']);

        // get current hashed password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $pwd = $stmt->fetchColumn();

        if (empty($current) || empty($new) || empty($confirm)) {
            $action_error = "Please fill in all password fields.";
        } elseif (!password_verify($current, $pwd)) {
            $action_error = "Current password is incorrect.";
        } elseif ($new !== $confirm) {
            $action_error = "New passwords do not match.";
        } elseif (strlen($new) < 6) {
            $action_error = "New password must be at least 6 characters long.";
        } else {
            $stmt_u = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_u->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);
            $action_success = "Password changed successfully.";
        }
    }

    // 3. Add Shipping Address
    if (isset($_POST['add_address'])) {
        $a_name = trim($_POST['address_name']);
        $a_phone = trim($_POST['address_phone']);
        $a_line1 = trim($_POST['address_line1']);
        $a_line2 = trim($_POST['address_line2']);
        $a_city = trim($_POST['address_city']);
        $a_state = trim($_POST['address_state']);
        $a_pincode = trim($_POST['address_pincode']);
        $a_default = isset($_POST['address_default']) ? 1 : 0;

        if (empty($a_name) || empty($a_phone) || empty($a_line1) || empty($a_city) || empty($a_state) || empty($a_pincode)) {
            $action_error = "Please complete all address details.";
        } elseif (!preg_match('/^[1-9][0-9]{5}$/', $a_pincode)) {
            $action_error = "Invalid 6-digit India Pincode.";
        } else {
            if ($a_default) {
                // Remove default flag from all other addresses
                $stmt_c = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt_c->execute([$user['id']]);
            }

            $stmt_i = $pdo->prepare("
                INSERT INTO user_addresses (user_id, name, phone, address_line1, address_line2, city, state, pincode, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_i->execute([$user['id'], $a_name, $a_phone, $a_line1, $a_line2, $a_city, $a_state, $a_pincode, $a_default]);
            $action_success = "Shipping address added successfully.";
        }
    }

    // 4. Delete Address
    if (isset($_POST['delete_address'])) {
        $addr_id = (int)$_POST['address_id'];
        $stmt_d = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt_d->execute([$addr_id, $user['id']]);
        $action_success = "Address deleted.";
    }
}

// Fetch Order History
$stmt_o = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_o->execute([$user['id']]);
$orders = $stmt_o->fetchAll();

// Fetch Saved Addresses
$stmt_a = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt_a->execute([$user['id']]);
$addresses = $stmt_a->fetchAll();
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>My Account</h2>
            <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! Manage your orders & profile details.</p>
        </div>

        <?php if ($action_success): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
                ✅ <?php echo htmlspecialchars($action_success); ?>
            </div>
        <?php endif; ?>
        <?php if ($action_error): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(231,76,60,0.05); border-color:rgba(231,76,60,0.3); color:var(--danger-color); margin-bottom:25px;">
                ❌ <?php echo htmlspecialchars($action_error); ?>
            </div>
        <?php endif; ?>

        <!-- Account Layout -->
        <div style="display:grid; grid-template-columns: 250px 1fr; gap:40px; align-items:start;">
            
            <!-- Side Navigation -->
            <aside class="glass-card" style="padding:20px; border-radius:8px;">
                <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                    <li>
                        <button class="tab-btn active" data-target="panel-orders" style="width:100%; text-align:left;">
                            <i class="fas fa-box" style="margin-right:8px;"></i> Orders
                        </button>
                    </li>
                    <li>
                        <button class="tab-btn" data-target="panel-addresses" style="width:100%; text-align:left;">
                            <i class="fas fa-map-marker-alt" style="margin-right:8px;"></i> Saved Addresses
                        </button>
                    </li>
                    <li>
                        <button class="tab-btn" data-target="panel-profile" style="width:100%; text-align:left;">
                            <i class="fas fa-user-edit" style="margin-right:8px;"></i> Edit Profile
                        </button>
                    </li>
                    <li>
                        <button class="tab-btn" data-target="panel-security" style="width:100%; text-align:left;">
                            <i class="fas fa-key" style="margin-right:8px;"></i> Change Password
                        </button>
                    </li>
                    <li style="border-top: 1px solid var(--border-color); padding-top:10px; margin-top:10px;">
                        <a href="logout.php" class="btn-outline-gold" style="width:100%; padding:8px; font-size:0.85rem; text-align:center;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Panels Content -->
            <div>
                <!-- Panel 1: Orders -->
                <div id="panel-orders" class="tab-pane active">
                    <h3 style="margin-bottom:20px; text-transform:uppercase; color:var(--gold-primary); font-size:1.25rem;">Order History</h3>
                    
                    <?php if (empty($orders)): ?>
                        <div class="glass-card" style="padding:40px; text-align:center; border-radius:8px;">
                            <p style="color:var(--text-muted);">You have not placed any orders yet.</p>
                            <a href="index.php" class="btn-gold" style="margin-top:15px; padding:10px 20px;">Browse Supplements</a>
                        </div>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:20px;">
                            <?php foreach ($orders as $ord): 
                                // Fetch items for invoice preview
                                $stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                $stmt_i->execute([$ord['id']]);
                                $items = $stmt_i->fetchAll();
                            ?>
                                <div class="glass-card" style="padding:20px; border-radius:8px;">
                                    <div style="display:flex; justify-content:between; flex-wrap:wrap; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:12px; margin-bottom:12px; font-size:0.85rem; gap:15px;">
                                        <div>
                                            <span style="color:var(--text-muted);">Order Number:</span> 
                                            <strong style="color:#fff;"><?php echo htmlspecialchars($ord['order_number']); ?></strong>
                                        </div>
                                        <div>
                                            <span style="color:var(--text-muted);">Date:</span> 
                                            <strong><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></strong>
                                        </div>
                                        <div>
                                            <span style="color:var(--text-muted);">Total:</span> 
                                            <strong style="color:var(--gold-primary);">₹<?php echo number_format($ord['total'], 2); ?></strong>
                                        </div>
                                        <div style="margin-left:auto;">
                                            <span style="color:var(--text-muted);">Status:</span> 
                                            <strong style="text-transform:uppercase; color:<?php 
                                                echo ($ord['shipping_status'] === 'delivered') ? 'var(--success-color)' : (($ord['shipping_status'] === 'cancelled') ? 'var(--danger-color)' : 'orange'); 
                                            ?>;"><?php echo htmlspecialchars($ord['shipping_status']); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div style="font-size:0.9rem; margin-bottom:15px;">
                                        <ul style="list-style:none;">
                                            <?php foreach ($items as $item): ?>
                                                <li style="margin-bottom:5px;">
                                                    <span style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($item['product_name']); ?></span> 
                                                    <span style="color:var(--text-muted);"> (<?php echo htmlspecialchars($item['variant_name']); ?>)</span>
                                                    <strong> &times; <?php echo $item['quantity']; ?></strong>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <?php if (!empty($ord['tracking_number'])): ?>
                                        <div style="background:rgba(212,175,55,0.05); border:1px solid var(--border-color); padding:10px 15px; border-radius:4px; font-size:0.85rem; margin-bottom:15px;">
                                            <i class="fas fa-truck" style="color:var(--gold-primary); margin-right:8px;"></i>
                                            <span>Shipment tracking: <strong><?php echo htmlspecialchars($ord['tracking_number']); ?></strong> via <?php echo htmlspecialchars($ord['courier_name']); ?>.</span>
                                            <a href="#" style="color:var(--gold-light); text-decoration:underline; font-weight:700; margin-left:10px;" onclick="alert('Tracking status details is simulated. Courier: <?php echo htmlspecialchars($ord['courier_name']); ?>')">Track Status &rarr;</a>
                                        </div>
                                    <?php endif; ?>

                                    <div style="font-size:0.8rem; color:var(--text-muted);">
                                        <span>Shipping Address: <?php echo htmlspecialchars($ord['shipping_address']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Panel 2: Saved Addresses -->
                <div id="panel-addresses" class="tab-pane">
                    <h3 style="margin-bottom:20px; text-transform:uppercase; color:var(--gold-primary); font-size:1.25rem;">Manage Shipping Addresses</h3>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">
                        <!-- Addresses List -->
                        <div style="display:flex; flex-direction:column; gap:15px;">
                            <?php if (empty($addresses)): ?>
                                <p style="color:var(--text-muted);">No saved addresses. Add one on the right.</p>
                            <?php else: ?>
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="glass-card" style="padding:15px; border-radius:6px; position:relative;">
                                        <?php if ($addr['is_default']): ?>
                                            <span style="position:absolute; top:12px; right:15px; background:var(--gold-primary); color:#000; font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:20px;">DEFAULT</span>
                                        <?php endif; ?>
                                        <div style="font-weight:700; color:#fff; font-size:0.95rem; margin-bottom:5px;">
                                            <?php echo htmlspecialchars($addr['name']); ?>
                                        </div>
                                        <div style="font-size:0.85rem; line-height:1.4;">
                                            <?php echo htmlspecialchars($addr['address_line1'] . ', ' . $addr['address_line2']); ?><br>
                                            <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']); ?><br>
                                            Phone: <?php echo htmlspecialchars($addr['phone']); ?>
                                        </div>
                                        
                                        <form action="my-account.php" method="POST" style="margin-top:10px; text-align:right;">
                                            <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                            <button type="submit" name="delete_address" style="background:none; border:none; color:var(--danger-color); cursor:pointer; font-size:0.8rem; font-weight:600;">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Add New Address Form -->
                        <div class="glass-card" style="padding:20px; border-radius:6px;">
                            <h4 style="font-size:1rem; margin-bottom:15px; text-transform:uppercase;">Add New Address</h4>
                            <form action="my-account.php" method="POST">
                                <div class="form-group">
                                    <label for="address_name">Contact Name *</label>
                                    <input type="text" name="address_name" id="address_name" class="form-control" style="font-size:0.85rem; padding:8px;" required>
                                </div>
                                <div class="form-group">
                                    <label for="address_phone">Phone Number *</label>
                                    <input type="text" name="address_phone" id="address_phone" class="form-control" style="font-size:0.85rem; padding:8px;" maxlength="10" required>
                                </div>
                                <div class="form-group">
                                    <label for="address_line1">Street Address *</label>
                                    <input type="text" name="address_line1" id="address_line1" class="form-control" style="font-size:0.85rem; padding:8px; margin-bottom:8px;" placeholder="Flat/House No, Building" required>
                                    <input type="text" name="address_line2" id="address_line2" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="Locality, Land Mark (optional)">
                                </div>
                                <div class="form-row" style="gap:10px;">
                                    <div class="form-group">
                                        <label for="address_city">City *</label>
                                        <input type="text" name="address_city" id="address_city" class="form-control" style="font-size:0.85rem; padding:8px;" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address_state">State *</label>
                                        <input type="text" name="address_state" id="address_state" class="form-control" style="font-size:0.85rem; padding:8px;" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="address_pincode">Pincode *</label>
                                    <input type="text" name="address_pincode" id="address_pincode" class="form-control" style="font-size:0.85rem; padding:8px;" maxlength="6" required>
                                </div>
                                <div class="form-group">
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem;">
                                        <input type="checkbox" name="address_default" value="1" style="accent-color:var(--gold-primary);">
                                        <span>Set as default shipping address</span>
                                    </label>
                                </div>
                                <button type="submit" name="add_address" class="btn-gold" style="width:100%; padding:10px; font-size:0.85rem; margin-top:10px;">
                                    Save Address
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Edit Profile -->
                <div id="panel-profile" class="tab-pane">
                    <h3 style="margin-bottom:20px; text-transform:uppercase; color:var(--gold-primary); font-size:1.25rem;">Edit Profile Details</h3>
                    
                    <div class="glass-card" style="padding:25px; border-radius:6px; max-width:600px;">
                        <form action="my-account.php" method="POST">
                            <div class="form-group">
                                <label for="profile_name">Full Name</label>
                                <input type="text" name="name" id="profile_name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="profile_email">Email Address</label>
                                <input type="email" name="email" id="profile_email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="profile_phone">Phone Number</label>
                                <input type="text" name="phone" id="profile_phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" maxlength="10" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn-gold" style="padding:10px 25px; margin-top:10px;">
                                Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Panel 4: Change Password -->
                <div id="panel-security" class="tab-pane">
                    <h3 style="margin-bottom:20px; text-transform:uppercase; color:var(--gold-primary); font-size:1.25rem;">Change Password</h3>
                    
                    <div class="glass-card" style="padding:25px; border-radius:6px; max-width:600px;">
                        <form action="my-account.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_pwd">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_pwd" class="form-control" placeholder="Confirm new password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn-gold" style="padding:10px 25px; margin-top:10px;">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Switch Account Panels Tabs script -->
    <script>
        const acctTabButtons = document.querySelectorAll('aside .tab-btn');
        const acctTabPanes = document.querySelectorAll('div.tab-pane');

        acctTabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.dataset.target;
                
                acctTabButtons.forEach(b => b.classList.remove('active'));
                acctTabPanes.forEach(p => p.classList.remove('active'));
                
                this.classList.add('active');
                const targetPane = document.getElementById(targetId);
                if (targetPane) targetPane.classList.add('active');
            });
        });
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
