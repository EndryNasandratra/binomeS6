<?php
require_once __DIR__ . '/auth_controller.php';

ensureSessionStarted();
if (!isAdminAuthenticated()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$tmpPath = $_FILES['file']['tmp_name'];
$originalName = $_FILES['file']['name'] ?? '';
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/articles';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Cannot create upload directory']);
    exit;
}

$fileName = 'body_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$destination = $uploadDir . '/' . $fileName;

if (!move_uploaded_file($tmpPath, $destination)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'location' => '/uploads/articles/' . $fileName,
]);
