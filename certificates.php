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
    </div>

    <!-- Modal for certificate viewing -->
    <div id="cert-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(8,12,16,0.9); z-index:1000; opacity:0; pointer-events:none; transition:0.3s; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px;">
        <div style="position:absolute; top:30px; right:40px; font-size:2rem; color:#fff; cursor:pointer;" onclick="closeCertModal()">&times;</div>
        <img id="cert-modal-img" src="" alt="Enlarged Certificate" style="max-width:90%; max-height:80%; border-radius:4px; box-shadow:0 0 30px rgba(8,12,16,0.8); border:1px solid var(--border-color);">
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
