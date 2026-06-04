<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();
if (!isPublisher()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'message' => 'Akses ditolak.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

if (empty($_FILES['image']['name'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'File tidak ditemukan.']);
    exit;
}

$tmpName = $_FILES['image']['tmp_name'];
$fileInfo = @getimagesize($tmpName);
if ($fileInfo === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'File gambar tidak valid.']);
    exit;
}

if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Ukuran gambar maksimal 2MB.']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Format gambar harus JPG, PNG, atau WEBP.']);
    exit;
}

$uploadDir = __DIR__ . '/../foto/';
$gambarName = 'info_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

if (!move_uploaded_file($tmpName, $uploadDir . $gambarName)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Gagal mengunggah gambar.']);
    exit;
}

echo json_encode(['ok' => true, 'url' => '/foto/' . $gambarName]);
