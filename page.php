<?php
// page.php
require_once __DIR__ . '/includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
} catch (PDOException $e) {
    $page = null;
}

if (!$page) {
    header("Location: index.php");
    exit();
}
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width:850px;">
        <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:25px;">
            <a href="index.php">Home</a> &nbsp;/&nbsp; 
            <span style="color:var(--text-primary);"><?php echo htmlspecialchars($page['title']); ?></span>
        </div>

        <div class="glass-card" style="padding: 45px; border-radius: 8px;">
            <h1 style="font-size:2.2rem; text-transform:uppercase; margin-bottom:25px; border-bottom:1px solid var(--border-color); padding-bottom:15px; color:var(--gold-primary);">
                <?php echo htmlspecialchars($page['title']); ?>
            </h1>
            
            <div class="cms-page-content" style="line-height:1.8; font-size:1.02rem; color:#ddd;">
                <?php echo $page['body']; ?>
            </div>
        </div>
    </div>

    <!-- Custom CSS styles for CMS content list items and headings -->
    <style>
        .cms-page-content h3 {
            font-size: 1.3rem;
            margin-top: 25px;
            margin-bottom: 12px;
            text-transform: uppercase;
            color: #fff;
        }
        .cms-page-content h4 {
            font-size: 1.1rem;
            margin-top: 20px;
            margin-bottom: 10px;
            color: var(--gold-muted);
        }
        .cms-page-content p {
            margin-bottom: 15px;
        }
        .cms-page-content ul, .cms-page-content ol {
            margin-left: 25px;
            margin-bottom: 20px;
        }
        .cms-page-content li {
            margin-bottom: 8px;
        }
    </style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
