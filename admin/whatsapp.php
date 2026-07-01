<?php
// admin/whatsapp.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_whatsapp'])) {
    $phone = trim($_POST['phone_number']);
    $msg = trim($_POST['greeting_message']);
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($phone)) {
        $action_error = "WhatsApp phone number is required.";
    } else {
        $stmt_u = $pdo->prepare("
            UPDATE whatsapp_settings 
            SET phone_number = ?, greeting_message = ?, status = ? 
            WHERE id = 1
        ");
        $stmt_u->execute([$phone, $msg, $status]);
        $action_msg = "WhatsApp widget settings updated successfully.";
    }
}

// Fetch settings
$settings = get_whatsapp_settings();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">WhatsApp Chat Widget</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Configure floating chat support settings</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:30px; align-items:start;">
        <!-- Left Column: Config Form -->
        <div class="glass-card" style="padding:30px; border-radius:8px;">
            <h3 style="font-size:1.25rem; color:#fff; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:10px;">
                Widget Configurations
            </h3>
            
            <form action="whatsapp.php" method="POST">
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="phone" style="font-size:0.9rem; color:var(--gold-muted); margin-bottom:8px; display:block; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">WhatsApp Business Number *</label>
                    <input type="text" name="phone_number" id="phone" class="form-control" value="<?php echo htmlspecialchars($settings ? $settings['phone_number'] : ''); ?>" placeholder="e.g. +919876543210 (include country code)" required>
                    <small style="color:var(--text-muted); font-size:0.75rem; margin-top:6px; display:block;">Ensure country code is prefix (e.g. +91 for India) without spaces or hyphens.</small>
                </div>
                
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="greet" style="font-size:0.9rem; color:var(--gold-muted); margin-bottom:8px; display:block; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Default Greeting Message</label>
                    <textarea name="greeting_message" id="greet" rows="4" class="form-control" placeholder="Pre-filled message when customer clicks widget..."><?php echo htmlspecialchars($settings ? $settings['greeting_message'] : ''); ?></textarea>
                </div>

                <div class="form-group" style="margin:25px 0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="status" value="1" <?php echo ($settings && $settings['status'] == 1) ? 'checked' : ''; ?> style="accent-color:var(--gold-primary); width:18px; height:18px;">
                        <span style="font-weight:600; color:#fff; font-size:0.95rem;">Enable Floating Chat Widget on Storefront</span>
                    </label>
                </div>

                <button type="submit" name="update_whatsapp" class="btn-gold" style="padding:10px 25px; font-weight:700;">
                    Save Widget Settings
                </button>
            </form>
        </div>

        <!-- Right Column: Mockup Live Preview -->
        <div class="glass-card" style="padding:30px; border-radius:8px;">
             <h3 style="font-size:1.25rem; color:var(--gold-primary); margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:10px;">
                 <i class="fab fa-whatsapp"></i> Chat Support Preview
             </h3>
             <div style="background:#075e54; color:#fff; border-radius:8px; overflow:hidden; font-family:sans-serif; box-shadow:0 10px 25px rgba(0,0,0,0.35);">
                 <div style="background:#075e54; padding:15px; display:flex; align-items:center; gap:10px; border-bottom:1px solid rgba(255,255,255,0.1);">
                     <div style="width:10px; height:10px; background:#25d366; border-radius:50%;"></div>
                     <span style="font-weight:700; font-size:0.9rem;">Wolf Nutrition Support</span>
                 </div>
                 <div style="background:#ece5dd; padding:20px; min-height:150px; display:flex; flex-direction:column; gap:10px;">
                     <div style="background:#fff; color:#333; padding:10px 14px; border-radius:0 8px 8px 8px; max-width:85%; font-size:0.85rem; line-height:1.4; align-self:flex-start; box-shadow:0 1px 2px rgba(0,0,0,0.15);">
                         Hello there! How can we help you unleash the alpha within today?
                     </div>
                     <?php if ($settings && $settings['greeting_message']): ?>
                         <div style="background:#e1ffc7; color:#333; padding:10px 14px; border-radius:8px 8px 0 8px; max-width:85%; font-size:0.85rem; line-height:1.4; align-self:flex-end; box-shadow:0 1px 2px rgba(0,0,0,0.15); font-style:italic;">
                             <?php echo htmlspecialchars($settings['greeting_message']); ?>
                         </div>
                     <?php endif; ?>
                 </div>
             </div>
             <p style="font-size:0.8rem; color:var(--text-muted); margin-top:20px; line-height:1.5;">
                 The preview box above simulates how the chat bubble will be filled out by the user on their phone when they touch the WhatsApp floating launcher.
             </p>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
