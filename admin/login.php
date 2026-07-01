<?php
// admin/login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in as admin
if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_error = "Please fill in all details.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_role'] = 'admin';
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "Invalid admin email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wolf Nutrition | Admin Portal Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: var(--bg-primary); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div class="container" style="max-width:520px; width:100%; padding:0 20px;">
        <div class="glass-card" style="padding: 50px 45px; border-radius: 12px; border-top: 4px solid var(--gold-primary); box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 30px rgba(212,175,55,0.05);">
            <div style="text-align:center; margin-bottom:30px;">
                <img src="../assets/images/logo.png" alt="Wolf Logo" style="height:70px; margin-bottom:12px;">
                <h2 style="font-size:1.8rem; text-transform:uppercase; color:#fff; letter-spacing:1px; margin-bottom:6px;">WOLF NUTRITION</h2>
                <span style="font-size:0.75rem; font-weight:800; letter-spacing:1.5px; background:var(--gold-gradient); color:#000; padding:3px 12px; border-radius:4px; text-transform:uppercase;">ADMIN CONTROL PORTAL</span>
            </div>

            <?php if ($login_error): ?>
                <div class="quantity-discount-widget" style="background-color:rgba(231,76,60,0.05); border-color:rgba(231,76,60,0.3); color:var(--danger-color); margin-bottom:25px; padding:12px; font-weight:600;">
                    ❌ <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group" style="margin-bottom:22px;">
                    <label for="email" style="font-size:0.9rem; color:var(--gold-muted); margin-bottom:8px; display:block; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Admin Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" style="padding:13px 16px; font-size:0.95rem;" placeholder="admin@wolfnutrition.in" required>
                </div>

                <div class="form-group" style="margin-bottom:26px;">
                    <label for="password" style="font-size:0.9rem; color:var(--gold-muted); margin-bottom:8px; display:block; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Password</label>
                    <input type="password" name="password" id="password" class="form-control" style="padding:13px 16px; font-size:0.95rem;" placeholder="Enter password" required>
                </div>

                <button type="submit" name="admin_login_btn" class="btn-gold" style="width:100%; padding:14px; font-weight:800; font-size:0.95rem; text-transform:uppercase; letter-spacing:1.5px; border-radius:6px; box-shadow:0 4px 15px rgba(212,175,55,0.15);">
                    Authenticate Admin
                </button>
            </form>
            
            <div style="text-align:center; margin-top:30px; font-size:0.85rem;">
                <a href="../index.php" style="color:var(--text-muted); font-weight:600; transition:color 0.3s;" onmouseover="this.style.color='var(--gold-primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Return to Front Storefront
                </a>
            </div>
        </div>
    </div>

</body>
</html>
