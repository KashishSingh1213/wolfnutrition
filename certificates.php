<?php
// certificates.php
require_once __DIR__ . '/includes/header.php';

$certs = get_certificates();
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>Quality & Trust Certificates</h2>
            <p>100% verified wholesaler registrations and ingredient test reports</p>
        </div>

        <!-- Certificates Gallery Grid -->
        <?php if (!empty($certs)): ?>
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:30px; margin-bottom:50px;">
                <?php foreach ($certs as $cert): ?>
                    <div class="glass-card" style="padding: 20px; border-radius:8px; text-align:center;">
                        <img src="<?php echo htmlspecialchars($cert['image_url']); ?>" alt="<?php echo htmlspecialchars($cert['title']); ?>" style="width:100%; border-radius:4px; cursor:zoom-in; border:1px solid var(--border-color);" onclick="showCertModal('<?php echo htmlspecialchars($cert['image_url']); ?>', '<?php echo htmlspecialchars($cert['title']); ?>')">
                        <h4 style="margin-top:15px; font-size:0.95rem; color:#fff;"><?php echo htmlspecialchars($cert['title']); ?></h4>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- FSSAI Details Transcript Table -->
        <div class="glass-card" style="padding: 40px; border-radius: 12px; max-width: 900px; margin: 0 auto;">
            <h3 style="font-size:1.4rem; text-transform:uppercase; margin-bottom:20px; color:var(--gold-primary); text-align:center; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
                FSSAI Registration Details (Transcribed)
            </h3>
            
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.95rem; line-height:1.6;">
                <tbody>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600; width:30%;">Registration Number</td>
                        <td style="padding:12px 10px; color:#fff; font-weight:700; font-size:1.1rem; letter-spacing:0.5px;">22126022000063</td>
                    </tr>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600;">FBO Name</td>
                        <td style="padding:12px 10px; color:#fff;">WOLF NUTRITION</td>
                    </tr>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600;">Premises Address</td>
                        <td style="padding:12px 10px; color:#ddd;">Kaki Pind, Hoshiarpur Road, Rama Mandi, Jalandhar, Ramamandi, Jalandhar-2, Jalandhar, Punjab - 144005</td>
                    </tr>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600;">Kind of Business</td>
                        <td style="padding:12px 10px; color:#fff;">Wholesaler</td>
                    </tr>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600;">Authority State</td>
                        <td style="padding:12px 10px; color:#fff;">Government of Punjab, Department of Food Safety</td>
                    </tr>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600;">Date Issued</td>
                        <td style="padding:12px 10px; color:#fff;">06-02-2026</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 10px; color:var(--text-muted); font-weight:600;">Validity Period</td>
                        <td style="padding:12px 10px; color:var(--success-color); font-weight:700;">06-02-2026 to 05-02-2031 (Valid)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for certificate viewing -->
    <div id="cert-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:1000; opacity:0; pointer-events:none; transition:0.3s; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px;">
        <div style="position:absolute; top:30px; right:40px; font-size:2rem; color:#fff; cursor:pointer;" onclick="closeCertModal()">&times;</div>
        <img id="cert-modal-img" src="" alt="Enlarged Certificate" style="max-width:90%; max-height:80%; border-radius:4px; box-shadow:0 0 30px rgba(0,0,0,0.8); border:1px solid var(--border-color);">
        <h3 id="cert-modal-title" style="margin-top:20px; color:#fff;"></h3>
    </div>

    <script>
        function showCertModal(src, title) {
            const modal = document.getElementById('cert-modal');
            const img = document.getElementById('cert-modal-img');
            const heading = document.getElementById('cert-modal-title');
            
            img.src = src;
            heading.textContent = title;
            modal.style.opacity = '1';
            modal.style.pointerEvents = 'auto';
            document.body.style.overflow = 'hidden';
        }

        function closeCertModal() {
            const modal = document.getElementById('cert-modal');
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
            document.body.style.overflow = 'auto';
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
