<?php
// sitemap.php
require_once __DIR__ . '/includes/functions.php';

header("Content-Type: application/xml; charset=utf-8");

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$base_url = $scheme . "://" . $host . "/wolfnutrition";

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static Pages
$static_pages = [
    ['/index.php', '1.00', 'daily'],
    ['/about.php', '0.80', 'weekly'],
    ['/contact.php', '0.70', 'weekly'],
    ['/certificates.php', '0.70', 'monthly'],
    ['/blog.php', '0.80', 'weekly'],
    ['/cart.php', '0.30', 'monthly'],
    ['/login.php', '0.30', 'monthly'],
    ['/register.php', '0.30', 'monthly'],
];

foreach ($static_pages as [$page, $priority, $freq]) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($base_url . $page) . '</loc>' . "\n";
    echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    echo '    <changefreq>' . $freq . '</changefreq>' . "\n";
    echo '    <priority>' . $priority . '</priority>' . "\n";
    echo '  </url>' . "\n";
}

// Categories
$categories = ['vitality', 'liver-detox', 'all'];
foreach ($categories as $cat) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($base_url . '/category.php?slug=' . $cat) . '</loc>' . "\n";
    echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    echo '    <changefreq>weekly</changefreq>' . "\n";
    echo '    <priority>0.85</priority>' . "\n";
    echo '  </url>' . "\n";
}

// Products
try {
    global $pdo;
    $stmt = $pdo->query("SELECT slug, updated_at FROM products WHERE is_active = 1");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lastmod = !empty($row['updated_at']) ? date('Y-m-d', strtotime($row['updated_at'])) : date('Y-m-d');
        echo '  <url>' . "\n";
        echo '    <loc>' . htmlspecialchars($base_url . '/product.php?slug=' . $row['slug']) . '</loc>' . "\n";
        echo '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        echo '    <changefreq>daily</changefreq>' . "\n";
        echo '    <priority>1.00</priority>' . "\n";
        echo '  </url>' . "\n";
    }
} catch (Exception $e) {
    // Fail silently in production
}

// Blog Posts
try {
    global $pdo;
    $stmt = $pdo->query("SELECT slug, published_at FROM blog_posts WHERE status = 1");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lastmod = !empty($row['published_at']) ? date('Y-m-d', strtotime($row['published_at'])) : date('Y-m-d');
        echo '  <url>' . "\n";
        echo '    <loc>' . htmlspecialchars($base_url . '/blog-post.php?slug=' . $row['slug']) . '</loc>' . "\n";
        echo '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        echo '    <changefreq>monthly</changefreq>' . "\n";
        echo '    <priority>0.75</priority>' . "\n";
        echo '  </url>' . "\n";
    }
} catch (Exception $e) {
    // Fail silently
}

// CMS Pages
try {
    global $pdo;
    $stmt = $pdo->query("SELECT slug FROM cms_pages WHERE is_active = 1");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '  <url>' . "\n";
        echo '    <loc>' . htmlspecialchars($base_url . '/page.php?slug=' . $row['slug']) . '</loc>' . "\n";
        echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        echo '    <changefreq>monthly</changefreq>' . "\n";
        echo '    <priority>0.60</priority>' . "\n";
        echo '  </url>' . "\n";
    }
} catch (Exception $e) {
    // Fail silently
}

echo '</urlset>' . "\n";
?>
