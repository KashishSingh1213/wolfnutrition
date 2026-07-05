<?php
// admin/upload_handler.php — AJAX image upload handler
require_once __DIR__ . '/includes/header.php';

header('Content-Type: application/json');

if (!is_admin_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit();
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['file'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP, SVG allowed.']);
    exit();
}

// Max 5MB
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large. Max 5MB allowed.']);
    exit();
}

// Create uploads directory if not exists
$upload_dir = __DIR__ . '/../uploads/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'product_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$filepath = $upload_dir . $filename;
$relative_path = 'uploads/products/' . $filename;

// Move file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode([
        'success' => true, 
        'url' => $relative_path,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
}
