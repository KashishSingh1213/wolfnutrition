<?php
// register.php
require_once __DIR__ . '/config/db.php';

// Handle Real-Time AJAX validations (runs before header to avoid HTML output corruption)
if (isset($_GET['check_field']) && isset($_GET['value'])) {
    header('Content-Type: application/json');
    $field = $_GET['check_field'];
    $val = trim($_GET['value']);
    $response = ['exists' => false];
    
    if ($field === 'email' && filter_var($val, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$val]);
        if ($stmt->fetch()) {
            $response['exists'] = true;
        }
    } elseif ($field === 'phone' && preg_match('/^[6-9][0-9]{9}$/', $val)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$val]);
        if ($stmt->fetch()) {
            $response['exists'] = true;
        }
    }
    echo json_encode($response);
    exit();
}

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
        <div class="glass-card" style="padding: 45px 35px; border-radius: 16px; border: 1px solid rgba(212,175,55,0.15); box-shadow: 0 15px 35px rgba(8,12,16,0.4); background: rgba(18,18,18,0.65); backdrop-filter: blur(12px);">
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
                <div class="quantity-discount-widget" style="background-color:rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); color:rgba(255,255,255,0.8); padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:8px;">
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
                    <small id="email-error" style="color:rgba(255,255,255,0.6); display:none; margin-top:6px; font-weight:600; font-size:0.8rem;"></small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="phone" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="10-digit mobile number" maxlength="10" required style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 16px;">
                    <small id="phone-error" style="color:rgba(255,255,255,0.6); display:none; margin-top:6px; font-weight:600; font-size:0.8rem;"></small>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">Password</label>
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Min 8 characters" required style="border-radius: 8px; border-color: rgba(255,255,255,0.08); font-size: 0.95rem; height: auto; padding: 13px 45px 13px 16px; width: 100%;">
                        <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" style="position: absolute; right: 16px; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; outline: none; display: flex; align-items: center; justify-content: center; z-index: 10;">
                            <i id="togglePasswordIcon" class="far fa-eye" style="font-size: 1.15rem; transition: color 0.2s;"></i>
                        </button>
                    </div>
                    
                    <!-- Real-Time Password Checklist -->
                    <div id="password-checklist" style="margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px 15px; background: rgba(8,12,16,0.18); padding: 12px 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.03);">
                        <div id="rule-length" style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; transition: color 0.2s;">
                            <i class="far fa-circle" id="icon-length"></i> 8+ Characters
                        </div>
                        <div id="rule-upper" style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; transition: color 0.2s;">
                            <i class="far fa-circle" id="icon-upper"></i> 1 Uppercase
                        </div>
                        <div id="rule-lower" style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; transition: color 0.2s;">
                            <i class="far fa-circle" id="icon-lower"></i> 1 Lowercase
                        </div>
                        <div id="rule-number" style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; transition: color 0.2s;">
                            <i class="far fa-circle" id="icon-number"></i> 1 Number
                        </div>
                        <div id="rule-special" style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; transition: color 0.2s; grid-column: span 2;">
                            <i class="far fa-circle" id="icon-special"></i> 1 Special Icon (@$!%*?&)
                        </div>
                    </div>
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

document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.querySelector('button[name="register_btn"]');
    
    const emailError = document.getElementById('email-error');
    const phoneError = document.getElementById('phone-error');
    
    let isEmailValid = false;
    let isPhoneValid = false;
    let isPasswordValid = false;
    
    function validateFormState() {
        if (nameInput.value.trim() !== '' && isEmailValid && isPhoneValid && isPasswordValid) {
            submitBtn.removeAttribute('disabled');
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        } else {
            submitBtn.setAttribute('disabled', 'true');
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        }
    }
    
    let emailTimeout = null;
    let phoneTimeout = null;
    
    // Real-Time Email Check
    emailInput.addEventListener('input', function() {
        const email = emailInput.value.trim();
        emailError.style.display = 'none';
        isEmailValid = false;
        validateFormState();
        
        if (emailTimeout) clearTimeout(emailTimeout);
        
        if (email === '') return;
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            emailError.textContent = "Please enter a valid email address.";
            emailError.style.display = 'block';
            return;
        }
        
        emailTimeout = setTimeout(() => {
            fetch(`register.php?check_field=email&value=${encodeURIComponent(email)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        emailError.textContent = "This email is already registered.";
                        emailError.style.display = 'block';
                        isEmailValid = false;
                    } else {
                        isEmailValid = true;
                    }
                    validateFormState();
                });
        }, 350);
    });
    
    // Real-Time Phone Check
    phoneInput.addEventListener('input', function() {
        const phone = phoneInput.value.trim();
        phoneError.style.display = 'none';
        isPhoneValid = false;
        validateFormState();
        
        if (phoneTimeout) clearTimeout(phoneTimeout);
        
        if (phone === '') return;
        
        const phoneRegex = /^[6-9][0-9]{9}$/;
        if (!phoneRegex.test(phone)) {
            phoneError.textContent = "Please enter a valid 10-digit Indian phone number.";
            phoneError.style.display = 'block';
            return;
        }
        
        phoneTimeout = setTimeout(() => {
            fetch(`register.php?check_field=phone&value=${encodeURIComponent(phone)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        phoneError.textContent = "This phone number is already registered.";
                        phoneError.style.display = 'block';
                        isPhoneValid = false;
                    } else {
                        isPhoneValid = true;
                    }
                    validateFormState();
                });
        }, 350);
    });
    
    // Real-Time Password Strength Checker
    passwordInput.addEventListener('input', function() {
        const val = passwordInput.value;
        
        const hasLength = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSpecial = /[@$!%*?&]/.test(val);
        
        function toggleRuleState(ruleId, iconId, isValid) {
            const ruleEl = document.getElementById(ruleId);
            const iconEl = document.getElementById(iconId);
            if (isValid) {
                ruleEl.style.color = '#D4AF37';
                iconEl.className = 'fas fa-check-circle';
                iconEl.style.color = '#D4AF37';
            } else {
                ruleEl.style.color = 'var(--text-muted)';
                iconEl.className = 'far fa-circle';
                iconEl.style.color = 'var(--text-muted)';
            }
        }
        
        toggleRuleState('rule-length', 'icon-length', hasLength);
        toggleRuleState('rule-upper', 'icon-upper', hasUpper);
        toggleRuleState('rule-lower', 'icon-lower', hasLower);
        toggleRuleState('rule-number', 'icon-number', hasNumber);
        toggleRuleState('rule-special', 'icon-special', hasSpecial);
        
        isPasswordValid = hasLength && hasUpper && hasLower && hasNumber && hasSpecial;
        validateFormState();
    });
    
    nameInput.addEventListener('input', validateFormState);
    
    // Initial State Check
    validateFormState();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
