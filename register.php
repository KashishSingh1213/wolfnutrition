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

    // Server-Side Validations
    $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $register_error = "Please fill in all details.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $register_error = "Please enter a valid 10-digit India mobile number (starting with 6-9).";
    } elseif (!preg_match($password_regex, $password)) {
        $register_error = "Password must be at least 8 characters, containing 1 uppercase, 1 lowercase, 1 number, and 1 special symbol (@$!%*?&).";
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
                // Register User (Bcrypt Hashing)
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

    <div class="container" style="margin-top: 60px; margin-bottom: 90px; max-width:480px;">
        <div class="glass-card" style="padding: 45px 35px; border-radius: 16px; border: 1px solid rgba(212,175,55,0.15); box-shadow: 0 15px 35px rgba(0,0,0,0.4); background: rgba(15,16,20,0.65); backdrop-filter: blur(12px);">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="assets/images/logo.png" alt="Wolf Nutrition Logo" style="height: 65px; margin-bottom: 15px;">
                <h2 style="font-size:1.9rem; text-transform:uppercase; color:var(--text-primary); font-family:var(--font-heading); font-weight:800; letter-spacing:0.5px; margin:0;">
                    Create Account
                </h2>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-top:5px; margin-bottom:0;">
                    Join the pack to track stacks, orders & checkouts.
                </p>
            </div>

            <?php if ($register_error): ?>
                <div class="quantity-discount-widget" style="background-color:rgba(231,76,60,0.08); border: 1px solid rgba(231,76,60,0.25); color:#ff6b6b; padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.1rem;"></i> <span><?php echo htmlspecialchars($register_error); ?></span>
                </div>
            <?php endif; ?>

            <form action="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" method="POST" style="margin-top: 10px;">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="name" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Yuvek Verma" required style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 16px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="e.g. yuvek@gmail.com" required style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 16px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="phone" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="10-digit mobile number" maxlength="10" required pattern="^[6-9][0-9]{9}$" title="Please enter a valid 10-digit India mobile number starting with 6, 7, 8, or 9." style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 16px;">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Password</label>
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Min 8 characters" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" title="Password must be at least 8 characters, with 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character." style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 45px 13px 16px; width: 100%;">
                        <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" style="position: absolute; right: 16px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; outline: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                            <i id="togglePasswordIcon" class="far fa-eye" style="font-size: 1.15rem; transition: color 0.2s;"></i>
                        </button>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 6px; font-size: 0.78rem;">Must contain at least 8 characters, 1 uppercase, 1 lowercase, 1 digit, and 1 special symbol (@$!%*?&).</small>
                </div>

                <button type="submit" name="register_btn" class="btn-gold" style="width:100%; margin-top:5px; padding:14px; font-weight: 700; font-size: 1rem; border-radius: 30px; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(212,175,55,0.2);">
                    Create Account
                </button>
            </form>
            
            <div style="text-align:center; margin-top:30px; font-size:0.92rem; border-top:1px solid rgba(255,255,255,0.06); padding-top:20px;">
                <span style="color:var(--text-muted);">Already in the Pack?</span> 
                <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" style="color:var(--gold-primary); font-weight:700; margin-left:6px; text-decoration:none; transition: color 0.2s;">Login Here</a>
            </div>
            
        </div>
    </div>

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
