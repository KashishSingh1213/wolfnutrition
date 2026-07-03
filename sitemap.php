<?php
// sitemap.php
require_once __DIR__ . '/includes/functions.php';

header("Content-Type: application/xml; charset=utf-8");

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = "http://" . $host . "/wolfnutrition";

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static Pages
$static_pages = [
    '/index.php',
    '/about.php',
    '/contact.php',
    '/certificates.php'
];

foreach ($static_pages as $page) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($base_url . $page) . '</loc>' . "\n";
    echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    echo '    <changefreq>weekly</changefreq>' . "\n";
    echo '    <priority>0.80</priority>' . "\n";
    echo '  </url>' . "\n";
}

// Categories
$categories = ['vitality', 'liver-detox'];
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
    $stmt = $pdo->query("SELECT slug FROM products");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '  <url>' . "\n";
        echo '    <loc>' . htmlspecialchars($base_url . '/product.php?slug=' . $row['slug']) . '</loc>' . "\n";
        echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        echo '    <changefreq>daily</changefreq>' . "\n";
        echo '    <priority>1.00</priority>' . "\n";
        echo '  </url>' . "\n";
    }
} catch (Exception $e) {
    // Fail silently in production
}

echo '</urlset>' . "\n";
?>
