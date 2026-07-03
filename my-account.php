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

// Count stats
$order_count = count($orders);
$address_count = count($addresses);
?>

    <style>
        .account-wrapper {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 40px;
            align-items: start;
        }

        /* ── Sidebar ── */
        .acct-sidebar {
            background: rgba(18,18,18,0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(212,175,55,0.1);
            border-radius: 20px;
            padding: 32px 24px;
            box-shadow: 0 20px 50px rgba(8,12,16,0.5);
            position: sticky;
            top: 100px;
        }

        /* Avatar */
        .acct-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: var(--gold-gradient);
            color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 800;
            font-size: 2rem;
            font-family: var(--font-heading);
            margin: 0 auto 16px;
            box-shadow: 0 0 0 4px rgba(18,18,18,0.8), 0 0 0 6px rgba(212,175,55,0.3);
            transition: box-shadow 0.3s;
        }
        .acct-avatar:hover {
            box-shadow: 0 0 0 4px rgba(18,18,18,0.8), 0 0 0 6px rgba(212,175,55,0.6), 0 0 20px rgba(212,175,55,0.2);
        }

        .acct-user-name {
            font-size: 1.2rem;
            color: #fff;
            font-weight: 700;
            text-align: center;
            margin: 0 0 4px;
            font-family: var(--font-heading);
        }
        .acct-user-email {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-align: center;
            margin: 0 0 14px;
            word-break: break-all;
            line-height: 1.4;
        }
        .acct-badge {
            display: block;
            text-align: center;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            background: rgba(212,175,55,0.06);
            border: 1px solid rgba(212,175,55,0.18);
            color: var(--gold-primary);
            padding: 5px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        /* Stats row */
        .acct-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .acct-stat {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 12px;
            padding: 14px 10px;
            text-align: center;
        }
        .acct-stat-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gold-primary);
            font-family: var(--font-heading);
            display: block;
            line-height: 1;
        }
        .acct-stat-label {
            font-size: 0.65rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
            display: block;
        }

        /* Nav */
        .acct-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .acct-nav-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-secondary);
            padding: 14px 18px;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            transition: all 0.25s ease;
            text-align: left;
            outline: none;
        }
        .acct-nav-btn i {
            font-size: 1rem;
            width: 22px;
            text-align: center;
            transition: transform 0.2s;
        }
        .acct-nav-btn:hover {
            background: rgba(212,175,55,0.05);
            color: var(--gold-primary);
            border-color: rgba(212,175,55,0.12);
            transform: translateX(6px);
        }
        .acct-nav-btn:hover i { transform: scale(1.15); }
        .acct-nav-btn.active {
            background: rgba(212,175,55,0.08);
            color: var(--gold-primary);
            border-color: rgba(212,175,55,0.2);
            box-shadow: inset 0 0 20px rgba(212,175,55,0.03);
            font-weight: 700;
        }
        .acct-nav-btn.active i { transform: scale(1.1); }

        .acct-divider {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 10px 0;
        }

        .acct-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--text-muted);
            padding: 12px 18px;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            transition: all 0.25s ease;
            text-align: center;
            justify-content: center;
            text-decoration: none;
        }
        .acct-logout:hover {
            color: var(--gold-primary);
            border-color: rgba(212,175,55,0.3);
            background: rgba(212,175,55,0.05);
        }

        /* ── Main Panels ── */
        .acct-panel-card {
            background: rgba(18,18,18,0.55);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(8,12,16,0.3);
        }
        .acct-panel-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .acct-panel-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(212,175,55,0.08);
            border: 1px solid rgba(212,175,55,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold-primary);
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .acct-panel-title {
            font-size: 1.25rem;
            font-family: var(--font-heading);
            font-weight: 800;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
        }
        .acct-panel-subtitle {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin: 2px 0 0;
        }

        /* ── Tab Panes ── */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── Alert Banners ── */
        .acct-alert {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.35s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .acct-alert-success {
            background: rgba(212,175,55,0.06);
            border: 1px solid rgba(212,175,55,0.2);
            color: var(--gold-primary);
        }
        .acct-alert-error {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.8);
        }

        /* ── Order Cards ── */
        .order-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 0;
            transition: border-color 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        .order-card:hover {
            border-color: rgba(212,175,55,0.15);
            box-shadow: 0 8px 30px rgba(8,12,16,0.2);
        }
        .order-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            flex-wrap: wrap;
            gap: 14px;
        }
        .order-meta {
            display: flex;
            gap: 28px;
            flex-wrap: wrap;
        }
        .order-meta-item {
            display: flex;
            flex-direction: column;
        }
        .order-meta-label {
            font-size: 0.65rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .order-meta-value {
            font-size: 0.95rem;
            color: #fff;
            font-weight: 700;
        }
        .order-meta-value.gold { color: var(--gold-primary); }
        .order-card-body { padding: 20px 24px; }
        .order-items-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .order-items-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        .order-items-list li:last-child { border-bottom: none; }
        .order-item-name {
            font-weight: 600;
            color: #fff;
            font-size: 0.92rem;
        }
        .order-item-variant {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-left: 6px;
        }
        .order-item-qty {
            color: var(--gold-primary);
            font-weight: 700;
            font-size: 0.85rem;
            background: rgba(212,175,55,0.06);
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid rgba(212,175,55,0.12);
        }

        .order-tracking {
            background: rgba(212,175,55,0.03);
            border: 1px solid rgba(212,175,55,0.12);
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 0.88rem;
            margin: 0 24px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-tracking-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .order-tracking a {
            color: var(--gold-primary);
            text-decoration: none;
            font-weight: 700;
            border-bottom: 1px dashed var(--gold-primary);
            padding-bottom: 1px;
        }
        .order-tracking a:hover { opacity: 0.8; }

        .order-address {
            font-size: 0.82rem;
            color: var(--text-muted);
            background: rgba(8,12,16,0.2);
            padding: 12px 20px;
            margin: 0 24px 20px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.03);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .order-address i { color: var(--gold-primary); }

        /* Status badges */
        .status-badge {
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .status-delivered {
            background: rgba(212,175,55,0.1);
            color: var(--gold-primary);
            border: 1px solid rgba(212,175,55,0.2);
        }
        .status-pending {
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .status-cancelled {
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* ── Empty state ── */
        .acct-empty {
            text-align: center;
            padding: 60px 30px;
        }
        .acct-empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--text-muted);
            font-size: 2rem;
        }
        .acct-empty p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 24px;
        }

        /* ── Address Cards ── */
        .addr-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 30px;
            align-items: start;
        }
        .addr-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 22px;
            position: relative;
            transition: border-color 0.3s;
        }
        .addr-card:hover { border-color: rgba(212,175,55,0.15); }
        .addr-default-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: var(--gold-gradient);
            color: #000;
            font-size: 0.6rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.8px;
        }
        .addr-name {
            font-weight: 700;
            color: #fff;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .addr-name i { color: var(--gold-primary); font-size: 0.85rem; }
        .addr-detail {
            font-size: 0.88rem;
            line-height: 1.6;
            color: var(--text-secondary);
        }
        .addr-phone {
            display: block;
            margin-top: 8px;
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        .addr-phone i { font-size: 0.72rem; margin-right: 4px; }
        .addr-delete {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,0.04);
            text-align: right;
        }
        .addr-delete button {
            background: none;
            border: none;
            color: rgba(255,255,255,0.5);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
            padding: 4px 0;
        }
        .addr-delete button:hover { opacity: 0.7; }

        /* ── Forms ── */
        .acct-form-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 28px;
        }
        .acct-form-title {
            font-size: 1rem;
            font-family: var(--font-heading);
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .acct-form-title i { color: var(--gold-primary); }

        .acct-field { margin-bottom: 18px; }
        .acct-field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 7px;
        }
        .acct-field input[type="text"],
        .acct-field input[type="email"],
        .acct-field input[type="password"] {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 13px 16px;
            color: #fff;
            font-size: 0.92rem;
            font-family: var(--font-body);
            transition: border-color 0.25s, box-shadow 0.25s;
            outline: none;
        }
        .acct-field input:focus {
            border-color: rgba(212,175,55,0.4);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.08);
        }
        .acct-field input.error {
            border-color: rgba(255,255,255,0.25);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.04);
        }
        .acct-field input.valid {
            border-color: rgba(212,175,55,0.35);
        }
        .acct-field input::placeholder { color: var(--text-muted); }
        .field-error {
            display: block;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            margin-top: 5px;
            font-weight: 500;
        }

        .acct-field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .acct-pwd-wrap {
            position: relative;
        }
        .acct-pwd-wrap input { padding-right: 48px; }
        .acct-pwd-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            outline: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }
        .acct-pwd-toggle:hover { color: var(--gold-primary); }

        .acct-check {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 0.85rem;
            user-select: none;
        }
        .acct-check input {
            accent-color: var(--gold-primary);
            width: 16px;
            height: 16px;
        }
        .acct-check span { color: var(--text-secondary); }

        /* ── Profile form specifics ── */
        .profile-form { max-width: 560px; }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .addr-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 900px) {
            .account-wrapper {
                grid-template-columns: 1fr !important;
            }
            .acct-sidebar {
                position: static;
            }
            .acct-panel-card { padding: 28px 20px; }
            .order-card-top { padding: 16px; }
            .order-card-body { padding: 16px; }
            .order-tracking, .order-address { margin-left: 16px; margin-right: 16px; }
        }
        @media (max-width: 600px) {
            .acct-field-row { grid-template-columns: 1fr; }
            .order-meta { gap: 16px; }
        }
    </style>

    <div class="container" style="margin-top: 50px; margin-bottom: 80px;">

        <!-- Page Header -->
        <div class="section-header" style="margin-bottom: 44px;">
            <h2 style="font-size: 2.4rem; text-transform: uppercase; font-family: var(--font-heading); letter-spacing: 1.5px;">
                My Account
            </h2>
            <p style="color: var(--text-muted); margin-top: 8px; font-size: 1.02rem;">
                Welcome back, <span style="color: var(--gold-primary); font-weight: 600;"><?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?></span>. Manage your profile, orders &amp; addresses.
            </p>
        </div>

        <!-- Alerts -->
        <?php if ($action_success): ?>
            <div class="acct-alert acct-alert-success">
                <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                <span><?php echo htmlspecialchars($action_success); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($action_error): ?>
            <div class="acct-alert acct-alert-error">
                <i class="fas fa-exclamation-circle" style="font-size: 1.1rem;"></i>
                <span><?php echo htmlspecialchars($action_error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Account Layout -->
        <div class="account-wrapper">

            <!-- ═══ SIDEBAR ═══ -->
            <aside class="acct-sidebar">
                <!-- Avatar & User Info -->
                <div style="text-align:center; margin-bottom: 20px;">
                    <div class="acct-avatar">
                        <?php 
                            $words = explode(' ', $user['name']);
                            $initials = '';
                            foreach ($words as $w) {
                                $initials .= strtoupper(substr($w, 0, 1));
                            }
                            echo htmlspecialchars(substr($initials, 0, 2));
                        ?>
                    </div>
                    <h4 class="acct-user-name"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="acct-user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="acct-badge"><i class="fas fa-wolf-pack" style="margin-right:4px;"></i> Pack Member</span>
                </div>

                <!-- Stats -->
                <div class="acct-stats">
                    <div class="acct-stat">
                        <span class="acct-stat-num"><?php echo $order_count; ?></span>
                        <span class="acct-stat-label">Orders</span>
                    </div>
                    <div class="acct-stat">
                        <span class="acct-stat-num"><?php echo $address_count; ?></span>
                        <span class="acct-stat-label">Addresses</span>
                    </div>
                </div>

                <!-- Navigation -->
                <ul class="acct-nav">
                    <li>
                        <button class="acct-nav-btn active" data-target="panel-orders">
                            <i class="fas fa-box"></i> Order History
                        </button>
                    </li>
                    <li>
                        <button class="acct-nav-btn" data-target="panel-addresses">
                            <i class="fas fa-map-marker-alt"></i> Saved Addresses
                        </button>
                    </li>
                    <li>
                        <button class="acct-nav-btn" data-target="panel-profile">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </button>
                    </li>
                    <li>
                        <button class="acct-nav-btn" data-target="panel-security">
                            <i class="fas fa-shield-alt"></i> Security
                        </button>
                    </li>
                    <li><div class="acct-divider"></div></li>
                    <li>
                        <a href="logout.php" class="acct-logout">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- ═══ PANELS ═══ -->
            <div>

                <!-- ── Panel: Orders ── -->
                <div id="panel-orders" class="tab-pane active">
                    <div class="acct-panel-card">
                        <div class="acct-panel-header">
                            <div class="acct-panel-icon"><i class="fas fa-box"></i></div>
                            <div>
                                <h3 class="acct-panel-title">Order History</h3>
                                <p class="acct-panel-subtitle">Track and review all your past orders</p>
                            </div>
                        </div>

                        <?php if (empty($orders)): ?>
                            <div class="acct-empty">
                                <div class="acct-empty-icon"><i class="fas fa-box-open"></i></div>
                                <p>You haven't placed any orders yet.<br>Start shopping to see your orders here.</p>
                                <a href="index.php" class="btn-gold" style="padding: 12px 28px; font-size: 0.88rem; border-radius: 30px;">
                                    <i class="fas fa-shopping-bag"></i> Browse Supplements
                                </a>
                            </div>
                        <?php else: ?>
                            <div style="display:flex; flex-direction:column; gap: 20px;">
                                <?php foreach ($orders as $ord): 
                                    $stmt_i = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                    $stmt_i->execute([$ord['id']]);
                                    $items = $stmt_i->fetchAll();
                                ?>
                                    <div class="order-card">
                                        <!-- Top Bar -->
                                        <div class="order-card-top">
                                            <div class="order-meta">
                                                <div class="order-meta-item">
                                                    <span class="order-meta-label">Order</span>
                                                    <span class="order-meta-value">#<?php echo htmlspecialchars($ord['order_number']); ?></span>
                                                </div>
                                                <div class="order-meta-item">
                                                    <span class="order-meta-label">Placed</span>
                                                    <span class="order-meta-value"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></span>
                                                </div>
                                                <div class="order-meta-item">
                                                    <span class="order-meta-label">Total</span>
                                                    <span class="order-meta-value gold">₹<?php echo number_format($ord['total'], 2); ?></span>
                                                </div>
                                            </div>
                                            <span class="status-badge <?php 
                                                echo ($ord['shipping_status'] === 'delivered') ? 'status-delivered' : (($ord['shipping_status'] === 'cancelled') ? 'status-cancelled' : 'status-pending'); 
                                            ?>">
                                                <?php echo htmlspecialchars($ord['shipping_status']); ?>
                                            </span>
                                        </div>

                                        <!-- Items -->
                                        <div class="order-card-body">
                                            <ul class="order-items-list">
                                                <?php foreach ($items as $item): ?>
                                                    <li>
                                                        <div>
                                                            <span class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                            <span class="order-item-variant">(<?php echo htmlspecialchars($item['variant_name']); ?>)</span>
                                                        </div>
                                                        <span class="order-item-qty">Qty: <?php echo $item['quantity']; ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <div class="order-address">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><strong>Delivery:</strong> <?php echo htmlspecialchars($ord['shipping_address']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Panel: Addresses ── -->
                <div id="panel-addresses" class="tab-pane">
                    <div class="acct-panel-card">
                        <div class="acct-panel-header">
                            <div class="acct-panel-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h3 class="acct-panel-title">Shipping Addresses</h3>
                                <p class="acct-panel-subtitle">Manage your saved delivery addresses</p>
                            </div>
                        </div>

                        <div class="addr-grid">
                            <!-- Saved Addresses -->
                            <div style="display:flex; flex-direction:column; gap:16px;">
                                <?php if (empty($addresses)): ?>
                                    <div class="acct-empty" style="padding:40px 20px;">
                                        <div class="acct-empty-icon"><i class="fas fa-map-marked-alt"></i></div>
                                        <p>No addresses saved yet.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($addresses as $addr): ?>
                                        <div class="addr-card">
                                            <?php if ($addr['is_default']): ?>
                                                <span class="addr-default-badge">DEFAULT</span>
                                            <?php endif; ?>
                                            <div class="addr-name">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($addr['name']); ?>
                                            </div>
                                            <div class="addr-detail">
                                                <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                                <?php if(!empty($addr['address_line2'])) echo htmlspecialchars($addr['address_line2']) . '<br>'; ?>
                                                <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']); ?>
                                                <span class="addr-phone">
                                                    <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($addr['phone']); ?>
                                                </span>
                                            </div>
                                            <div class="addr-delete">
                                                <form action="my-account.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                                    <button type="submit" name="delete_address">
                                                        <i class="fas fa-trash-alt"></i> Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Add New Address Form -->
                            <div class="acct-form-card">
                                <div class="acct-form-title"><i class="fas fa-plus-circle"></i> Add New Address</div>
                                <form action="my-account.php" method="POST">
                                    <div class="acct-field">
                                        <label for="address_name">Contact Name *</label>
                                        <input type="text" name="address_name" id="address_name" required>
                                    </div>
                                    <div class="acct-field">
                                        <label for="address_phone">Phone Number *</label>
                                        <input type="text" name="address_phone" id="address_phone" maxlength="10" required>
                                    </div>
                                    <div class="acct-field">
                                        <label for="address_line1">Street Address *</label>
                                        <input type="text" name="address_line1" id="address_line1" placeholder="Flat/House No, Building" required style="margin-bottom:10px;">
                                        <input type="text" name="address_line2" id="address_line2" placeholder="Locality, Landmark (optional)">
                                    </div>
                                    <div class="acct-field-row">
                                        <div class="acct-field">
                                            <label for="address_city">City *</label>
                                            <input type="text" name="address_city" id="address_city" required>
                                        </div>
                                        <div class="acct-field">
                                            <label for="address_state">State *</label>
                                            <input type="text" name="address_state" id="address_state" required>
                                        </div>
                                    </div>
                                    <div class="acct-field">
                                        <label for="address_pincode">Pincode *</label>
                                        <input type="text" name="address_pincode" id="address_pincode" maxlength="6" required>
                                    </div>
                                    <div class="acct-field" style="margin-bottom:22px;">
                                        <label class="acct-check">
                                            <input type="checkbox" name="address_default" value="1">
                                            <span>Set as default shipping address</span>
                                        </label>
                                    </div>
                                    <button type="submit" name="add_address" class="btn-gold" style="width:100%; padding:13px; border-radius:30px; font-size:0.9rem;">
                                        <i class="fas fa-plus"></i> Save Address
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Panel: Edit Profile ── -->
                <div id="panel-profile" class="tab-pane">
                    <div class="acct-panel-card">
                        <div class="acct-panel-header">
                            <div class="acct-panel-icon"><i class="fas fa-user-edit"></i></div>
                            <div>
                                <h3 class="acct-panel-title">Edit Profile</h3>
                                <p class="acct-panel-subtitle">Update your personal information</p>
                            </div>
                        </div>

                        <div class="acct-form-card profile-form">
                            <form action="my-account.php" method="POST" id="profileForm" novalidate>
                                <div class="acct-field">
                                    <label for="profile_name">Full Name</label>
                                    <input type="text" name="name" id="profile_name" value="<?php echo htmlspecialchars($user['name']); ?>" required minlength="2" maxlength="100">
                                    <small id="name-error" class="field-error" style="display:none;"></small>
                                </div>
                                <div class="acct-field">
                                    <label for="profile_email">Email Address</label>
                                    <input type="email" name="email" id="profile_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    <small id="email-error" class="field-error" style="display:none;"></small>
                                </div>
                                <div class="acct-field" style="margin-bottom:26px;">
                                    <label for="profile_phone">Phone Number</label>
                                    <input type="text" name="phone" id="profile_phone" value="<?php echo htmlspecialchars($user['phone']); ?>" maxlength="10" pattern="[6-9][0-9]{9}" required>
                                    <small id="phone-error" class="field-error" style="display:none;"></small>
                                </div>
                                <button type="submit" name="update_profile" class="btn-gold" style="padding: 13px 32px; border-radius: 30px; font-size: 0.9rem;">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ── Panel: Change Password ── -->
                <div id="panel-security" class="tab-pane">
                    <div class="acct-panel-card">
                        <div class="acct-panel-header">
                            <div class="acct-panel-icon"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <h3 class="acct-panel-title">Change Password</h3>
                                <p class="acct-panel-subtitle">Keep your account secure with a strong password</p>
                            </div>
                        </div>

                        <div class="acct-form-card profile-form">
                            <form action="my-account.php" method="POST">
                                <div class="acct-field">
                                    <label for="current_password">Current Password</label>
                                    <div class="acct-pwd-wrap">
                                        <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
                                        <button type="button" class="acct-pwd-toggle" onclick="togglePwd('current_password','iconCurrent')">
                                            <i id="iconCurrent" class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="acct-field">
                                    <label for="new_password">New Password</label>
                                    <div class="acct-pwd-wrap">
                                        <input type="password" name="new_password" id="new_password" placeholder="Minimum 6 characters" required>
                                        <button type="button" class="acct-pwd-toggle" onclick="togglePwd('new_password','iconNew')">
                                            <i id="iconNew" class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="acct-field" style="margin-bottom:26px;">
                                    <label for="confirm_pwd">Confirm New Password</label>
                                    <div class="acct-pwd-wrap">
                                        <input type="password" name="confirm_password" id="confirm_pwd" placeholder="Confirm new password" required>
                                        <button type="button" class="acct-pwd-toggle" onclick="togglePwd('confirm_pwd','iconConfirm')">
                                            <i id="iconConfirm" class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" name="change_password" class="btn-gold" style="padding: 13px 32px; border-radius: 30px; font-size: 0.9rem;">
                                    <i class="fas fa-lock"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    function togglePwd(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
            icon.style.color = 'var(--gold-primary)';
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
            icon.style.color = '';
        }
    }

    document.querySelectorAll('.acct-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.acct-nav-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const target = document.getElementById(this.dataset.target);
            if (target) target.classList.add('active');
        });
    });

    // ── Profile Form Validation ──
    (function() {
        const form = document.getElementById('profileForm');
        if (!form) return;

        const nameInput = document.getElementById('profile_name');
        const emailInput = document.getElementById('profile_email');
        const phoneInput = document.getElementById('profile_phone');

        function showError(id, msg) {
            const el = document.getElementById(id);
            const input = el.closest('.acct-field').querySelector('input');
            el.textContent = msg;
            el.style.display = 'block';
            input.classList.add('error');
            input.classList.remove('valid');
        }
        function clearError(id) {
            const el = document.getElementById(id);
            const input = el.closest('.acct-field').querySelector('input');
            el.textContent = '';
            el.style.display = 'none';
            input.classList.remove('error');
            input.classList.add('valid');
        }

        function validateName() {
            const v = nameInput.value.trim();
            if (v.length < 2) { showError('name-error', 'Name must be at least 2 characters.'); return false; }
            if (v.length > 100) { showError('name-error', 'Name cannot exceed 100 characters.'); return false; }
            clearError('name-error'); return true;
        }
        function validateEmail() {
            const v = emailInput.value.trim();
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!v) { showError('email-error', 'Email is required.'); return false; }
            if (!re.test(v)) { showError('email-error', 'Please enter a valid email address.'); return false; }
            clearError('email-error'); return true;
        }
        function validatePhone() {
            const v = phoneInput.value.trim();
            const re = /^[6-9][0-9]{9}$/;
            if (!v) { showError('phone-error', 'Phone number is required.'); return false; }
            if (!re.test(v)) { showError('phone-error', 'Enter a valid 10-digit Indian number starting with 6-9.'); return false; }
            clearError('phone-error'); return true;
        }

        nameInput.addEventListener('blur', validateName);
        nameInput.addEventListener('input', function() {
            if (this.classList.contains('error')) validateName();
        });
        emailInput.addEventListener('blur', validateEmail);
        emailInput.addEventListener('input', function() {
            if (this.classList.contains('error')) validateEmail();
        });
        phoneInput.addEventListener('blur', validatePhone);
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.classList.contains('error')) validatePhone();
        });

        form.addEventListener('submit', function(e) {
            const a = validateName();
            const b = validateEmail();
            const c = validatePhone();
            if (!a || !b || !c) { e.preventDefault(); }
        });
    })();
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
