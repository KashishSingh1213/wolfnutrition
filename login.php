<?php
// login.php
require_once __DIR__ . '/includes/header.php';

if (is_logged_in()) {
    header("Location: my-account.php");
    exit();
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_error = "Please fill in all details.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = 'admin';
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = 'customer';
                
                // If redirect back to checkout requested
                if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
                    header("Location: checkout.php");
                } else {
                    header("Location: my-account.php");
                }
                exit();
            }
        } else {
            $login_error = "Invalid Email address or Password.";
        }
    }
}
?>

    <div class="container" style="margin-top: 50px; margin-bottom: 70px; max-width:500px;">
        <div class="glass-card" style="padding: 40px; border-radius: 8px;">
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:10px; text-align:center; color:var(--gold-primary);">
                Pack Login
            </h2>
            <p style="text-align:center; color:var(--text-muted); margin-bottom:30px;">
                Enter your details below to access your orders & settings
            </p>

            <?php if ($login_error): ?>
                <div class="quantity-discount-widget" style="background-color:rgba(231,76,60,0.05); border-color:rgba(231,76,60,0.3); color:var(--danger-color); margin-bottom:20px;">
                    ❌ <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="e.g. yuvek@gmail.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <button type="submit" name="login_btn" class="btn-gold" style="width:100%; margin-top:20px; padding:12px;">
                    Login To Account
                </button>
            </form>
            
            <div style="text-align:center; margin-top:25px; font-size:0.9rem;">
                <span style="color:var(--text-muted);">New to the Pack?</span> 
                <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" style="color:var(--gold-primary); font-weight:700; margin-left:5px;">Create Account</a>
            </div>
            
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
