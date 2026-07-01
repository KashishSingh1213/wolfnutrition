<?php
// admin/certificates.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$action_msg = '';

// Handle Add Certificate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cert'])) {
    $title = trim($_POST['title']);
    $url = trim($_POST['image_url']);
    $order = (int)$_POST['display_order'];

    if (empty($title) || empty($url)) {
        $action_error = "Please fill in title and image path.";
    } else {
        $stmt_i = $pdo->prepare("
            INSERT INTO certificates (title, image_url, display_order, status) 
            VALUES (?, ?, ?, 1)
        ");
        $stmt_i->execute([$title, $url, $order]);
        $action_msg = "Certificate uploaded and saved successfully.";
    }
}

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $c_id = (int)$_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE certificates SET status = NOT status WHERE id = ?");
    $stmt->execute([$c_id]);
    $action_msg = "Certificate active status toggled.";
}

// Handle Delete Certificate
if (isset($_GET['delete_id'])) {
    $c_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$c_id]);
    $action_msg = "Certificate deleted.";
}

// Fetch all certificates
$stmt = $pdo->prepare("SELECT * FROM certificates ORDER BY display_order ASC");
$stmt->execute();
$certs = $stmt->fetchAll();
?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-size:1.8rem; text-transform:uppercase;">Quality Certificates Gallery</h2>
        <div style="font-size:0.85rem; color:var(--text-muted);">Manage FSSAI, purity certificates and lab stamps</div>
    </div>

    <?php if ($action_msg): ?>
        <div class="quantity-discount-widget" style="background-color:rgba(46,204,113,0.05); border-color:rgba(46,204,113,0.3); color:var(--success-color); margin-bottom:25px;">
            ✅ <?php echo htmlspecialchars($action_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:30px; align-items:start;">
        <!-- Certificates list -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Seeded Quality Certificates
            </h3>
            
            <?php if (empty($certs)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:20px 0;">No certificates loaded in the database.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Certificate Details</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certs as $c): ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo htmlspecialchars($c['image_url']); ?>" alt="Certificate preview" style="height:55px; border-radius:3px; background:#000; border:1px solid var(--border-color);">
                                </td>
                                <td><span style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($c['title']); ?></span></td>
                                <td><?php echo $c['display_order']; ?></td>
                                <td>
                                    <span class="admin-badge <?php echo $c['status'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $c['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:10px;">
                                        <a href="certificates.php?toggle_id=<?php echo $c['id']; ?>" style="color:var(--gold-primary); font-weight:700;">Toggle</a>
                                        <a href="certificates.php?delete_id=<?php echo $c['id']; ?>" style="color:var(--danger-color); font-weight:700;" onclick="return confirm('Delete this certificate record?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Add Certificate Form -->
        <div class="glass-card" style="padding: 25px; border-radius:6px;">
            <h3 style="font-size:1.15rem; text-transform:uppercase; margin-bottom:15px; color:var(--gold-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                Add Quality Stamp
            </h3>
            
            <form action="certificates.php" method="POST">
                <div class="form-group">
                    <label for="c-title">Certificate Title *</label>
                    <input type="text" name="title" id="c-title" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. ISO 9001:2015 stamp" required>
                </div>
                <div class="form-group">
                    <label for="c-url">Image File Path *</label>
                    <input type="text" name="image_url" id="c-url" class="form-control" style="font-size:0.85rem; padding:8px;" placeholder="e.g. assets/images/certs/fssai_cert.png" required>
                </div>
                <div class="form-group">
                    <label for="c-order">Display Order</label>
                    <input type="number" name="display_order" id="c-order" class="form-control" style="font-size:0.85rem; padding:8px;" value="0">
                </div>
                
                <button type="submit" name="add_cert" class="btn-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.85rem;">
                    Save Certificate
                </button>
            </form>
        </div>
    </div>

    <!-- Admin Guide Panel -->
    <div class="glass-card" style="padding:25px; border-radius:8px; margin-top:35px; border-left:4px solid var(--gold-primary); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h3 style="font-size:1.1rem; color:var(--gold-primary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">
            <i class="fas fa-lightbulb" style="margin-right:8px;"></i> E-Commerce Trust Badges & Conversion Rates
        </h3>
        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:12px;">
            Displaying high-quality trust stamps (like FSSAI certifications, 100% Ayurvedic Sourced badges, and GMP manufacturing seals) is key to breaking customer friction points during their purchase decisions.
        </p>
        <ul style="font-size:0.8rem; color:var(--text-muted); padding-left:20px; line-height:1.7;">
            <li>**FSSAI License Validation**: Keep the FSSAI license number clearly visible (`No. 22126022000063`) to build regulatory credibility.</li>
            <li>**Placement Details**: These certificates automatically show up on the storefront homepage inside the certificates block, as well as on checkout summaries.</li>
            <li>**Uploading New Images**: Save square icons (`200x200px` to `400x400px` with clear, transparent backgrounds) inside `assets/images/certs/` for the best visual presentation.</li>
        </ul>
    </div>

<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>
