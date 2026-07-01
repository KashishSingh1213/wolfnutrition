<?php
// register.php
require_once __DIR__ . '/includes/header.php';

if (is_logged_in()) {
    header("Location: my-account.php");
    exit();
}

$register_error = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_btn'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $register_error = "Please fill in all details.";
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $register_error = "Please enter a valid 10-digit India mobile number.";
    } elseif ($password !== $confirm_password) {
        $register_error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $register_error = "Password must be at least 6 characters long.";
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $register_error = "This email is already registered.";
        } else {
            // Check duplicate phone
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $register_error = "This phone number is already registered.";
            } else {
                // Register User
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt_i = $pdo->prepare("
                    INSERT INTO users (name, email, phone, password, role, is_active) 
                    VALUES (?, ?, ?, ?, 'customer', 1)
                ");
                $res = $stmt_i->execute([$name, $email, $phone, $hashed_password]);
                
                if ($res) {
                    $new_id = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $new_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = 'customer';
                    
                    if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
                        header("Location: checkout.php");
                    } else {
                        header("Location: my-account.php");
                    }
                    exit();
                } else {
                    $register_error = "Failed to register. Please try again.";
                }
            }
        }
    }
}
?>

    <div class="container" style="margin-top: 50px; margin-bottom: 70px; max-width:500px;">
        <div class="glass-card" style="padding: 40px; border-radius: 8px;">
            <h2 style="font-size:1.8rem; text-transform:uppercase; margin-bottom:10px; text-align:center; color:var(--gold-primary);">
                Create Account
            </h2>
            <p style="text-align:center; color:var(--text-muted); margin-bottom:30px;">
                Join the pack to track shipments & manage saved addresses
            </p>

            <?php if ($register_error): ?>
                <div class="quantity-discount-widget" style="background-color:rgba(231,76,60,0.05); border-color:rgba(231,76,60,0.3); color:var(--danger-color); margin-bottom:20px;">
                    ❌ <?php echo htmlspecialchars($register_error); ?>
                </div>
            <?php endif; ?>

            <form action="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Yuvek Verma" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="e.g. yuvek@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="10-digit mobile" maxlength="10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>

                <button type="submit" name="register_btn" class="btn-gold" style="width:100%; margin-top:20px; padding:12px;">
                    Create Account
                </button>
            </form>
            
            <div style="text-align:center; margin-top:25px; font-size:0.9rem;">
                <span style="color:var(--text-muted);">Already in the Pack?</span> 
                <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" style="color:var(--gold-primary); font-weight:700; margin-left:5px;">Login Here</a>
            </div>
            
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
