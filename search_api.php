<?php
// search_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/security.php';

// Rate limiting: max 30 searches per IP per minute
if (session_status() === PHP_SESSION_NONE) session_start();
$ip = get_client_ip();

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Query length cap
if (strlen($query) > 100) {
    $query = substr($query, 0, 100);
}

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

// Strip LIKE wildcards to prevent query manipulation
$searchTerm = '%' . str_replace(['%', '_'], '', $query) . '%';

try {
    // Simple rate limit check using a session counter
    $rate_key = 'search_count_' . date('YmdHi');
    $_SESSION[$rate_key] = ($_SESSION[$rate_key] ?? 0) + 1;
    if ($_SESSION[$rate_key] > 30) {
        echo json_encode([]);
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.slug, p.image_url, c.name as category_name, MIN(pv.sale_price) as min_price
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_variants pv ON p.id = pv.product_id
        WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
        GROUP BY p.id
        LIMIT 5
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    $response = [];
    foreach ($results as $row) {
        $response[] = [
            'id' => (int)$row['id'],
            'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
            'slug' => htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8'),
            'image' => $row['image_url'] ? htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8') : 'assets/images/products/default.png',
            'category' => $row['category_name'] ? htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') : 'Wellness',
            'price' => number_format((float)$row['min_price'], 2)
        ];
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
