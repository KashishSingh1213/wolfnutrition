<?php
// my-account.php
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user = get_logged_in_user();
if (!$user) {
    header("Location: login.php");
    exit();
}

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

    <style>
        .account-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 40px;
            align-items: start;
        }
        .account-sidebar {
            background: rgba(15,16,20,0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(212,175,55,0.15);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .account-nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0;
            margin: 0;
        }
        .account-nav-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-secondary);
            padding: 13px 16px;
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            transition: all 0.3s ease;
            text-align: left;
            outline: none;
        }
        .account-nav-btn:hover, .account-nav-btn.active {
            background: rgba(212, 175, 55, 0.06);
            color: var(--gold-primary);
            border-color: rgba(212,175,55,0.2);
            box-shadow: inset 0 0 15px rgba(212,175,55,0.02);
            transform: translateX(4px);
        }
        .account-nav-btn i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .account-nav-btn:hover i {
            transform: scale(1.15);
        }
        .account-panel-card {
            background: rgba(15,16,20,0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .status-delivered {
            background: rgba(46,204,113,0.1);
            color: #2ecc71;
            border: 1px solid rgba(46,204,113,0.2);
        }
        .status-pending {
            background: rgba(241,196,15,0.1);
            color: #f1c40f;
            border: 1px solid rgba(241,196,15,0.2);
        }
        .status-cancelled {
            background: rgba(231,76,60,0.1);
            color: #e74c3c;
            border: 1px solid rgba(231,76,60,0.2);
        }
        .order-item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .order-item-list li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-item-list li:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 900px) {
            .account-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <div class="container" style="margin-top: 50px; margin-bottom: 80px;">
        <div class="section-header" style="margin-bottom: 40px; text-align: center;">
            <h2 style="font-size: 2.3rem; text-transform: uppercase; font-family: var(--font-heading); letter-spacing: 1px;">My Account</h2>
            <p style="color: var(--text-secondary); margin-top: 5px;">Manage your orders, profile details, and shipping addresses.</p>
        </div>

        <?php if ($action_success): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.06); border: 1px solid rgba(46,204,113,0.25); color:#2ecc71; padding:12px 15px; border-radius:8px; margin-bottom:30px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-check-circle" style="font-size:1.1rem;"></i> <span><?php echo htmlspecialchars($action_success); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($action_error): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(231,76,60,0.06); border: 1px solid rgba(231,76,60,0.25); color:#ff6b6b; padding:12px 15px; border-radius:8px; margin-bottom:30px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exclamation-circle" style="font-size:1.1rem;"></i> <span><?php echo htmlspecialchars($action_error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Account Layout Grid -->
        <div class="account-grid">
            
            <!-- Side Navigation Panel -->
            <aside class="account-sidebar">
                <!-- User Initials Monogram Card -->
                <div style="text-align:center; padding-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.06); margin-bottom: 20px;">
                    <div style="width:75px; height:75px; border-radius:50%; background:var(--gold-gradient); color:#000; display:flex; justify-content:center; align-items:center; font-weight:800; font-size:1.7rem; font-family:var(--font-heading); margin: 0 auto 12px; box-shadow:0 8px 22px rgba(212,175,55,0.25);">
                        <?php 
                            $words = explode(' ', $user['name']);
                            $initials = '';
                            foreach ($words as $w) {
                                $initials .= strtoupper(substr($w, 0, 1));
                            }
                            echo htmlspecialchars(substr($initials, 0, 2));
                        ?>
                    </div>
                    <h4 style="font-size:1.15rem; color:#fff; font-weight:700; margin:0 0 4px;"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p style="font-size:0.8rem; color:var(--text-muted); margin:0 0 12px; word-break: break-all;"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span style="font-size:0.65rem; font-weight:800; letter-spacing:1px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.2); color:var(--gold-primary); padding:4px 12px; border-radius:20px; text-transform:uppercase;">Pack Member</span>
                </div>

                <ul class="account-nav-list">
                    <li>
                        <button class="account-nav-btn active" data-target="panel-orders">
                            <i class="fas fa-box"></i> Orders
                        </button>
                    </li>
                    <li>
                        <button class="account-nav-btn" data-target="panel-addresses">
                            <i class="fas fa-map-marker-alt"></i> Saved Addresses
                        </button>
                    </li>
                    <li>
                        <button class="account-nav-btn" data-target="panel-profile">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </button>
                    </li>
                    <li>
                        <button class="account-nav-btn" data-target="panel-security">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </li>
                    <li style="border-top: 1px solid rgba(255,255,255,0.06); padding-top:12px; margin-top:12px;">
                        <a href="logout.php" class="btn-outline-gold" style="width:100%; padding:10px 14px; font-size:0.85rem; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; border-radius:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Panels Content Area -->
            <div>
                <!-- Panel 1: Orders -->
                <div id="panel-orders" class="tab-pane active">
                    <div class="account-panel-card">
                        <h3 style="margin-top:0; margin-bottom:25px; text-transform:uppercase; color:var(--text-primary); font-family:var(--font-heading); font-size:1.3rem; font-weight:800; letter-spacing:0.5px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:15px;">Order History</h3>
                        
                        <?php if (empty($orders)): ?>
                            <div class="glass-card" style="padding:50px 30px; text-align:center; border-radius:12px; border-color:rgba(255,255,255,0.04);">
                                <i class="fas fa-box-open" style="font-size:3.5rem; color:var(--text-muted); margin-bottom:20px; display:block;"></i>
                                <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:20px;">You have not placed any orders yet.</p>
                                <a href="index.php" class="btn-gold" style="padding:12px 25px; font-size:0.9rem; font-weight:700; border-radius:30px; text-decoration:none; display:inline-block;">Browse Supplements</a>
                            </div>
                        <?php else: ?>
                            <div style="display:flex; flex-direction:column; gap:25px;">
                                <?php foreach ($orders as $ord): 
                                    $stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                    $stmt_i->execute([$ord['id']]);
                                    $items = $stmt_i->fetchAll();
                                ?>
                                    <div class="glass-card" style="padding:25px; border-radius:12px; border-color:rgba(255,255,255,0.08); background:rgba(255,255,255,0.01);">
                                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:15px; margin-bottom:15px; font-size:0.88rem; gap:15px;">
                                            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                                                <div>
                                                    <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Order Number</span> 
                                                    <strong style="color:#fff;"><?php echo htmlspecialchars($ord['order_number']); ?></strong>
                                                </div>
                                                <div>
                                                    <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Date Placed</span> 
                                                    <strong style="color:#fff;"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></strong>
                                                </div>
                                                <div>
                                                    <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Total Price</span> 
                                                    <strong style="color:var(--gold-primary);">₹<?php echo number_format($ord['total'], 2); ?></strong>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="status-badge <?php 
                                                    echo ($ord['shipping_status'] === 'delivered') ? 'status-delivered' : (($ord['shipping_status'] === 'cancelled') ? 'status-cancelled' : 'status-pending'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($ord['shipping_status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div style="margin-bottom:20px;">
                                            <ul class="order-item-list">
                                                <?php foreach ($items as $item): ?>
                                                    <li>
                                                        <div>
                                                            <span style="font-weight:700; color:#fff; font-size:0.95rem;"><?php echo htmlspecialchars($item['product_name']); ?></span> 
                                                            <span style="color:var(--text-muted); font-size:0.82rem; margin-left:5px;">(<?php echo htmlspecialchars($item['variant_name']); ?>)</span>
                                                        </div>
                                                        <strong style="color:var(--gold-primary);">Qty: <?php echo $item['quantity']; ?></strong>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <?php if (!empty($ord['tracking_number'])): ?>
                                            <div style="background:rgba(212,175,55,0.04); border:1px solid rgba(212,175,55,0.15); padding:12px 18px; border-radius:8px; font-size:0.88rem; margin-bottom:15px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <i class="fas fa-truck" style="color:var(--gold-primary); font-size:1rem;"></i>
                                                    <span>Shipment Tracking: <strong><?php echo htmlspecialchars($ord['tracking_number']); ?></strong> via <?php echo htmlspecialchars($ord['courier_name']); ?>.</span>
                                                </div>
                                                <a href="#" style="color:var(--gold-primary); text-decoration:none; font-weight:700; border-bottom:1px dashed var(--gold-primary); padding-bottom:1px;" onclick="alert('Tracking status details is simulated. Courier: <?php echo htmlspecialchars($ord['courier_name']); ?>')">Track Package &rarr;</a>
                                            </div>
                                        <?php endif; ?>

                                        <div style="font-size:0.82rem; color:var(--text-muted); background: rgba(0,0,0,0.15); padding:10px 15px; border-radius:6px; border: 1px solid rgba(255,255,255,0.03);">
                                            <i class="fas fa-map-marker-alt" style="margin-right:5px; color:var(--gold-primary);"></i> <strong>Delivery Address:</strong> <?php echo htmlspecialchars($ord['shipping_address']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel 2: Saved Addresses -->
                <div id="panel-addresses" class="tab-pane">
                    <div class="account-panel-card">
                        <h3 style="margin-top:0; margin-bottom:25px; text-transform:uppercase; color:var(--text-primary); font-family:var(--font-heading); font-size:1.3rem; font-weight:800; letter-spacing:0.5px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:15px;">Manage Shipping Addresses</h3>
                        
                        <div style="display:grid; grid-template-columns:1fr 1.1fr; gap:30px; align-items:start;">
                            
                            <!-- Saved Addresses List -->
                            <div style="display:flex; flex-direction:column; gap:20px;">
                                <?php if (empty($addresses)): ?>
                                    <p style="color:var(--text-secondary); text-align:center; padding: 40px 0;">No saved addresses. Add a new address on the right.</p>
                                <?php else: ?>
                                    <?php foreach ($addresses as $addr): ?>
                                        <div class="glass-card" style="padding:20px; border-radius:10px; border-color:rgba(255,255,255,0.06); position:relative; background: rgba(255,255,255,0.01);">
                                            <?php if ($addr['is_default']): ?>
                                                <span style="position:absolute; top:15px; right:15px; background:var(--gold-gradient); color:#000; font-size:0.65rem; font-weight:800; padding:3px 10px; border-radius:20px; letter-spacing:0.5px;">DEFAULT</span>
                                            <?php endif; ?>
                                            <div style="font-weight:700; color:#fff; font-size:1rem; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                                                <i class="fas fa-map-pin" style="color:var(--gold-primary); font-size:0.9rem;"></i> <?php echo htmlspecialchars($addr['name']); ?>
                                            </div>
                                            <div style="font-size:0.88rem; line-height:1.5; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                                <?php if(!empty($addr['address_line2'])) echo htmlspecialchars($addr['address_line2']) . '<br>'; ?>
                                                <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']); ?><br>
                                                <span style="display:block; margin-top:5px; color:var(--text-muted); font-size:0.8rem;"><i class="fas fa-phone-alt" style="font-size:0.75rem; margin-right:4px;"></i> Phone: <?php echo htmlspecialchars($addr['phone']); ?></span>
                                            </div>
                                            
                                            <form action="my-account.php" method="POST" style="margin-top:15px; text-align:right; border-top:1px solid rgba(255,255,255,0.04); padding-top:10px;">
                                                <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                                <button type="submit" name="delete_address" style="background:none; border:none; color:#ff6b6b; cursor:pointer; font-size:0.82rem; font-weight:700; display:inline-flex; align-items:center; gap:5px; outline:none;">
                                                    <i class="fas fa-trash-alt"></i> Delete Address
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Add New Address Form -->
                            <div class="glass-card" style="padding:25px; border-radius:12px; border-color:rgba(255,255,255,0.06); background: rgba(255,255,255,0.015);">
                                <h4 style="font-size:1.05rem; margin-top:0; margin-bottom:20px; text-transform:uppercase; color: #fff; font-family:var(--font-heading); font-weight:700; border-bottom:1px solid rgba(255,255,255,0.04); padding-bottom:10px;">Add New Address</h4>
                                <form action="my-account.php" method="POST">
                                    <div class="form-group" style="margin-bottom:15px;">
                                        <label for="address_name" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Contact Name *</label>
                                        <input type="text" name="address_name" id="address_name" class="form-control" style="border-radius:6px; font-size:0.9rem;" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom:15px;">
                                        <label for="address_phone" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Phone Number *</label>
                                        <input type="text" name="address_phone" id="address_phone" class="form-control" style="border-radius:6px; font-size:0.9rem;" maxlength="10" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom:15px;">
                                        <label for="address_line1" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Street Address *</label>
                                        <input type="text" name="address_line1" id="address_line1" class="form-control" style="border-radius:6px; font-size:0.9rem; margin-bottom:10px;" placeholder="Flat/House No, Building" required>
                                        <input type="text" name="address_line2" id="address_line2" class="form-control" style="border-radius:6px; font-size:0.9rem;" placeholder="Locality, Land Mark (optional)">
                                    </div>
                                    <div class="form-row" style="gap:15px; margin-bottom:15px;">
                                        <div class="form-group" style="flex:1; margin-bottom:0;">
                                            <label for="address_city" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">City *</label>
                                            <input type="text" name="address_city" id="address_city" class="form-control" style="border-radius:6px; font-size:0.9rem;" required>
                                        </div>
                                        <div class="form-group" style="flex:1; margin-bottom:0;">
                                            <label for="address_state" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">State *</label>
                                            <input type="text" name="address_state" id="address_state" class="form-control" style="border-radius:6px; font-size:0.9rem;" required>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom:20px;">
                                        <label for="address_pincode" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Pincode *</label>
                                        <input type="text" name="address_pincode" id="address_pincode" class="form-control" style="border-radius:6px; font-size:0.9rem;" maxlength="6" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom:20px;">
                                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; user-select:none;">
                                            <input type="checkbox" name="address_default" value="1" style="accent-color:var(--gold-primary); width:16px; height:16px;">
                                            <span style="color:var(--text-secondary);">Set as default shipping address</span>
                                        </label>
                                    </div>
                                    <button type="submit" name="add_address" class="btn-gold" style="width:100%; padding:12px; font-weight:700; font-size:0.9rem; border-radius:30px; letter-spacing:0.5px;">
                                        Save Address
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Edit Profile -->
                <div id="panel-profile" class="tab-pane">
                    <div class="account-panel-card">
                        <h3 style="margin-top:0; margin-bottom:25px; text-transform:uppercase; color:var(--text-primary); font-family:var(--font-heading); font-size:1.3rem; font-weight:800; letter-spacing:0.5px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:15px;">Edit Profile Details</h3>
                        
                        <div class="glass-card" style="padding:30px; border-radius:12px; border-color:rgba(255,255,255,0.06); max-width:600px; background: rgba(255,255,255,0.015);">
                            <form action="my-account.php" method="POST">
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label for="profile_name" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Full Name</label>
                                    <input type="text" name="name" id="profile_name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required style="border-radius:8px; font-size:0.95rem;">
                                </div>
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label for="profile_email" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Email Address</label>
                                    <input type="email" name="email" id="profile_email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required style="border-radius:8px; font-size:0.95rem;">
                                </div>
                                <div class="form-group" style="margin-bottom:25px;">
                                    <label for="profile_phone" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Phone Number</label>
                                    <input type="text" name="phone" id="profile_phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" maxlength="10" required style="border-radius:8px; font-size:0.95rem;">
                                </div>
                                <button type="submit" name="update_profile" class="btn-gold" style="padding:12px 30px; font-weight:700; font-size:0.9rem; border-radius:30px; letter-spacing:0.5px;">
                                    Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panel 4: Change Password -->
                <div id="panel-security" class="tab-pane">
                    <div class="account-panel-card">
                        <h3 style="margin-top:0; margin-bottom:25px; text-transform:uppercase; color:var(--text-primary); font-family:var(--font-heading); font-size:1.3rem; font-weight:800; letter-spacing:0.5px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:15px;">Change Password</h3>
                        
                        <div class="glass-card" style="padding:30px; border-radius:12px; border-color:rgba(255,255,255,0.06); max-width:600px; background: rgba(255,255,255,0.015);">
                            <form action="my-account.php" method="POST">
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label for="current_password" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Current Password</label>
                                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                                        <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" required style="border-radius:8px; font-size:0.95rem; padding-right:45px; width:100%;">
                                        <button type="button" onclick="togglePasswordVisibility('current_password', 'toggleCurrentIcon')" style="position: absolute; right: 16px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; outline: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                            <i id="toggleCurrentIcon" class="far fa-eye" style="font-size: 1.1rem; transition: color 0.2s;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label for="new_password" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">New Password</label>
                                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 6 characters" required style="border-radius:8px; font-size:0.95rem; padding-right:45px; width:100%;">
                                        <button type="button" onclick="togglePasswordVisibility('new_password', 'toggleNewIcon')" style="position: absolute; right: 16px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; outline: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                            <i id="toggleNewIcon" class="far fa-eye" style="font-size: 1.1rem; transition: color 0.2s;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:25px;">
                                    <label for="confirm_pwd" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Confirm New Password</label>
                                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                                        <input type="password" name="confirm_password" id="confirm_pwd" class="form-control" placeholder="Confirm new password" required style="border-radius:8px; font-size:0.95rem; padding-right:45px; width:100%;">
                                        <button type="button" onclick="togglePasswordVisibility('confirm_pwd', 'toggleConfirmIcon')" style="position: absolute; right: 16px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; outline: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                                            <i id="toggleConfirmIcon" class="far fa-eye" style="font-size: 1.1rem; transition: color 0.2s;"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" name="change_password" class="btn-gold" style="padding:12px 30px; font-weight:700; font-size:0.9rem; border-radius:30px; letter-spacing:0.5px;">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
    function togglePasswordVisibility(fieldId, iconId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
            toggleIcon.style.color = 'var(--gold-primary)';
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
            toggleIcon.style.color = 'var(--text-muted)';
        }
    }
    </script>

    <!-- Switch Account Panels Tabs script -->
    <script>
        const acctTabButtons = document.querySelectorAll('aside .account-nav-btn');
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
