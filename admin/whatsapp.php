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

    <!-- Page Header -->
    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">WhatsApp Chat Widget</h1>
        <p style="font-size:0.85rem; color:rgba(255,255,255,0.45); font-weight:400;">Configure floating chat support settings</p>
    </div>

    <?php if ($action_msg): ?>
        <div style="background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle" style="color:#4ade80; font-size:1rem;"></i>
            <span style="color:#4ade80; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($action_error) && $action_error): ?>
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:1rem;"></i>
            <span style="color:#ef4444; font-size:0.875rem; font-weight:500;"><?php echo htmlspecialchars($action_error); ?></span>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 420px; gap:28px; align-items:start;">

        <!-- Config Form -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(37,211,102,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fab fa-whatsapp" style="color:#25D366; font-size:1rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Widget Configuration</h3>
            </div>

            <form action="whatsapp.php" method="POST" style="padding:24px;">
                <div class="form-group">
                    <label for="phone" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">WhatsApp Business Number *</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.3); font-size:0.85rem;">
                            <i class="fas fa-phone"></i>
                        </span>
                        <input type="text" name="phone_number" id="phone" class="form-control" required placeholder="e.g. +919876543210"
                            value="<?php echo htmlspecialchars($settings ? $settings['phone_number'] : ''); ?>"
                            style="padding-left:40px; font-family:monospace; letter-spacing:0.5px;">
                    </div>
                    <small style="color:rgba(255,255,255,0.3); font-size:0.72rem; margin-top:6px; display:block;">Include country code (e.g. +91 for India) without spaces or hyphens.</small>
                </div>

                <div class="form-group">
                    <label for="greet" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Default Greeting Message</label>
                    <textarea name="greeting_message" id="greet" rows="4" class="form-control" placeholder="Pre-filled message when customer clicks widget..."><?php echo htmlspecialchars($settings ? $settings['greeting_message'] : ''); ?></textarea>
                    <small style="color:rgba(255,255,255,0.3); font-size:0.72rem; margin-top:6px; display:block;">This will appear as a suggestion bubble in the chat preview.</small>
                </div>

                <div class="form-group" style="margin-top:20px;">
                    <label style="display:flex; align-items:center; gap:12px; cursor:pointer; padding:14px 18px; border-radius:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); transition:all 0.2s;">
                        <input type="checkbox" name="status" value="1" <?php echo ($settings && $settings['status'] == 1) ? 'checked' : ''; ?> style="accent-color:#25D366; width:18px; height:18px;">
                        <div>
                            <span style="font-weight:600; color:#fff; font-size:0.9rem; display:block;">Enable Floating Chat Widget</span>
                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.35);">Show floating WhatsApp chat button</span>
                        </div>
                    </label>
                </div>

                <button type="submit" name="update_whatsapp" class="btn-gold" style="width:100%; margin-top:16px; padding:12px 24px;">
                    <i class="fas fa-save"></i> Save Widget Settings
                </button>
            </form>
        </div>

        <!-- Live Preview -->
        <div class="glass-card" style="padding:0; overflow:hidden; position:sticky; top:96px;">
            <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-eye" style="color:#D4AF37; font-size:0.9rem;"></i>
                </div>
                <h3 style="font-size:1rem; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:0.5px;">Live Preview</h3>
            </div>

            <!-- Phone Mockup -->
            <div style="padding:24px; display:flex; justify-content:center;">
                <div style="width:280px; background:#1a1a1a; border-radius:24px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.08);">
                    <!-- Phone Header -->
                    <div style="background:#075E54; padding:16px 16px 12px; display:flex; align-items:center; gap:10px;">
                        <div style="width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-store" style="color:#fff; font-size:0.75rem;"></i>
                        </div>
                        <div>
                            <div style="color:#fff; font-size:0.8rem; font-weight:600;">Wolf Nutrition</div>
                            <div style="color:rgba(255,255,255,0.6); font-size:0.6rem;">Online</div>
                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div style="background:#0B141A; padding:16px; min-height:160px; display:flex; flex-direction:column; gap:8px;">
                        <!-- Incoming message -->
                        <div style="background:#1F2C34; color:rgba(255,255,255,0.9); padding:10px 12px; border-radius:0 10px 10px 10px; max-width:85%; font-size:0.78rem; line-height:1.5; align-self:flex-start;">
                            Hello! Welcome to Wolf Nutrition. How can we help you today?
                        </div>

                        <!-- Pre-filled greeting suggestion -->
                        <?php if ($settings && $settings['greeting_message']): ?>
                            <div style="background:rgba(37,211,102,0.12); color:#25D366; padding:10px 12px; border-radius:10px 10px 0 10px; max-width:85%; font-size:0.78rem; line-height:1.5; align-self:flex-end; border:1px solid rgba(37,211,102,0.15); font-style:italic;">
                                <?php echo htmlspecialchars($settings['greeting_message']); ?>
                            </div>
                        <?php else: ?>
                            <div style="background:rgba(37,211,102,0.12); color:rgba(255,255,255,0.4); padding:10px 12px; border-radius:10px 10px 0 10px; max-width:85%; font-size:0.72rem; line-height:1.5; align-self:flex-end; border:1px dashed rgba(255,255,255,0.08); font-style:italic;">
                                Set a greeting message above to see it here...
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Input Bar -->
                    <div style="background:#1F2C34; padding:10px 12px; display:flex; align-items:center; gap:8px;">
                        <div style="flex:1; background:rgba(255,255,255,0.06); border-radius:20px; padding:8px 14px; color:rgba(255,255,255,0.3); font-size:0.72rem;">
                            Type a message
                        </div>
                        <div style="width:32px; height:32px; border-radius:50%; background:#25D366; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-paper-plane" style="color:#fff; font-size:0.65rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Info -->
            <div style="padding:0 24px 24px;">
                <div style="padding:16px; border-radius:8px; background:rgba(37,211,102,0.05); border:1px solid rgba(37,211,102,0.1);">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                        <i class="fab fa-whatsapp" style="color:#25D366; font-size:0.85rem;"></i>
                        <span style="font-size:0.75rem; font-weight:700; color:#25D366; text-transform:uppercase; letter-spacing:0.5px;">Chat Preview</span>
                    </div>
                    <p style="font-size:0.78rem; color:rgba(255,255,255,0.4); line-height:1.6;">
                        The preview above simulates how customers will see the chat bubble when they tap the WhatsApp floating launcher on their phone.
                    </p>
                </div>
            </div>
        </div>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
