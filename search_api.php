<?php
// search_api.php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.slug, p.image_url, c.name as category_name, MIN(pv.sale_price) as min_price
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_variants pv ON p.id = pv.product_id
        WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
        GROUP BY p.id
        LIMIT 5
    ");
    $searchTerm = "%{$query}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    $response = [];
    foreach ($results as $row) {
        $response[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'image' => $row['image_url'] ? $row['image_url'] : 'assets/images/products/default.png',
            'category' => $row['category_name'] ? $row['category_name'] : 'Wellness',
            'price' => number_format($row['min_price'], 2)
        ];
    }
    
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query error']);
}
?>
