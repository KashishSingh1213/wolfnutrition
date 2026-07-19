<?php
// certificates.php
require_once __DIR__ . '/includes/header.php';

$certs = get_certificates();
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="section-header">
            <h2>Quality & Trust Certificates</h2>
            <p>100% verified registrations and ingredient test reports</p>
        </div>

        <!-- Certificates Gallery Grid -->
        <?php if (!empty($certs)): ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:30px; margin-bottom:50px; <?php echo count($certs) === 1 ? 'max-width:500px; margin-left:auto; margin-right:auto;' : ''; ?>">
                <?php foreach ($certs as $cert):
                    $is_pdf = strtolower(pathinfo($cert['image_url'], PATHINFO_EXTENSION)) === 'pdf';
                ?>
                    <div class="glass-card" style="padding: 30px; border-radius:12px; text-align:center; border:1px solid rgba(212,175,55,0.12);">
                        <?php if ($is_pdf): ?>
                            <a href="<?php echo htmlspecialchars($cert['image_url']); ?>" target="_blank" style="display:block; text-decoration:none;">
                                <div style="width:100%; height:220px; background:linear-gradient(135deg, rgba(212,175,55,0.08) 0%, rgba(212,175,55,0.02) 100%); border:1px solid rgba(212,175,55,0.2); border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; transition:all 0.3s;" onmouseover="this.style.borderColor='rgba(212,175,55,0.4)'" onmouseout="this.style.borderColor='rgba(212,175,55,0.2)'">
                                    <div style="width:70px; height:70px; border-radius:50%; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-file-pdf" style="font-size:2rem; color:#D4AF37;"></i>
                                    </div>
                                    <span style="font-size:0.85rem; color:rgba(255,255,255,0.6); font-weight:500;">Click to view certificate</span>
                                </div>
                            </a>
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($cert['image_url']); ?>" alt="<?php echo htmlspecialchars($cert['title']); ?>" style="width:100%; border-radius:8px; cursor:zoom-in; border:1px solid var(--border-color);" onclick="showCertModal('<?php echo htmlspecialchars($cert['image_url']); ?>', '<?php echo htmlspecialchars($cert['title']); ?>')">
                        <?php endif; ?>
                        <h4 style="margin-top:18px; font-size:1rem; color:#fff;"><?php echo htmlspecialchars($cert['title']); ?></h4>
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
